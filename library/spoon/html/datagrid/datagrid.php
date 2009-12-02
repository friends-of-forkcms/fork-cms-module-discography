<?php

/**
 * Spoon Library
 *
 * This source file is part of the Spoon Library. More information,
 * documentation and tutorials can be found @ http://www.spoon-library.be
 *
 * @package		html
 * @subpackage	datagrid
 *
 *
 * @author		Davy Hellemans <davy@spoon-library.be>
 * @author 		Tijs Verkoyen <tijs@spoon-library.be>
 * @author		Dave Lens <dave@spoon-library.be>
 * @since		1.0.0
 */


/** SpoonTemplate class */
require_once 'spoon/template/template.php';

/** SpoonFilter class */
require_once 'spoon/filter/filter.php';

/** SpoonFileSystem package */
require_once 'spoon/filesystem/filesystem.php';


/**
 * This exception is used to handle datagrid related exceptions.
 *
 * @package		html
 * @subpackage	datagrid
 *
 *
 * @author		Davy Hellemans <davy@spoon-library.be>
 * @since		0.1.1
 */
class SpoonDataGridException extends SpoonException {}


/**
 * This class is the base class used to generate datagrids
 * from database queries.
 *
 * @package			html
 * @subpackage		datagrid
 *
 *
 * @author			Davy Hellemans <davy@spoon-library.be>
 * @since			1.0.0
 */
class SpoonDataGrid
{
	/**
	 * List of columns that may be sorted in, based on the query results
	 *
	 * @var	array
	 */
	private $allowedSortingColumns = array();


	/**
	 * Html attributes
	 *
	 * @var	array
	 */
	private $attributes = array('datagrid' => array(),
								'header' => array(),
								'row' => array(),
								'row_even' => array(),
								'row_odd' => array(),
								'footer' => array());


	/**
	 * Table caption/description
	 *
	 * @var	string
	 */
	private $caption;


	/**
	 * Array of column functions
	 *
	 * @var	array
	 */
	private $columnFunctions = array();


	/**
	 * Array of column objects
	 *
	 * @var	array
	 */
	private $columns = array();


	/**
	 * Default compile directory
	 *
	 * @var	string
	 */
	private $compileDirectory;


	/**
	 * Final output
	 *
	 * @var	string
	 */
	private $content;


	/**
	 * Debug status
	 *
	 * @var	bool
	 */
	private $debug = false;


	/**
	 * Offset value
	 *
	 * @var	int
	 */
	private $offset;


	/**
	 * Offset start value
	 *
	 * @var	int
	 */
	private $offsetParameter;


	/**
	 * Order start value
	 *
	 * @var	string
	 */
	private $orderParameter;


	/**
	 * Separate results with pages
	 *
	 * @var	bool
	 */
	private $paging = true;


	/**
	 * Default number of results per page
	 *
	 * @var	int
	 */
	private $pagingLimit = 30;


	/**
	 * Class used to define paging
	 *
	 * @var	SpoonDataGridPaging
	 */
	private $pagingClass = 'SpoonDataGridPaging';


	/**
	 * Parse status
	 *
	 * @var	bool
	 */
	private $parsed = false;


	/**
	 * Default sorting column
	 *
	 * @var	string
	 */
	private $sortingColumn;


	/**
	 * Sorting columns (cached when requested)
	 *
	 * @var array
	 */
	private $sortingColumns = array();


	/**
	 * Sorting icons
	 *
	 * @var	array
	 */
	private $sortingIcons = array(	'asc' => null,
									'ascSelected' => null,
									'desc' => null,
									'descSelected' => null);


	/**
	 * Sorting Labels
	 *
	 * @var	array
	 */
	private $sortingLabels = array(	'asc' => 'Sort ascending',
									'ascSelected' => 'Sorted ascending',
									'desc' => 'Sort descending',
									'descSelected' => 'Sorted descending');


	/**
	 * Default sorting method
	 *
	 * @var	string
	 */
	private $sortParameter;


	/**
	 * Source of the datagrid
	 *
	 * @var	SpoonDataGridSource
	 */
	private $source;


	/**
	 * Datagrid summary
	 *
	 * @var	string
	 */
	private $summary;


	/**
	 * Default or custom template
	 *
	 * @var	string
	 */
	private $template;


	/**
	 * Template instance
	 *
	 * @var	SpoonTemplate
	 */
	private $tpl;


	/**
	 * Basic url
	 *
	 * @var	string
	 */
	private $url;


	/**
	 * Class constructor.
	 *
	 * @return	void
	 * @param	SpoonDataGridSource $source
	 * @param	string[optional] $template
	 */
	public function __construct(SpoonDataGridSource $source, $template = null)
	{
		// set source
		$this->setSource($source);

		// set template
		if($template !== null) $this->setTemplate($template);

		// create default columns
		$this->createColumns();
	}


	/**
	 * Adds a new column
	 *
	 * @return	void
	 * @param	string $name
	 * @param	string[optional] $label
	 * @param	string[optional] $value
	 * @param	string[optional] $url
	 * @param	string[optional] $image
	 * @param	int[optional] $sequence
	 */
	public function addColumn($name, $label = null, $value = null, $url = null, $title = null, $image = null, $sequence = null)
	{
		// redefine name
		$name = (string) $name;

		// column already exists
		if(isset($this->columns[$name])) throw new SpoonDataGridException('A column with the name "'. $name .'" already exists.');

		// redefine sequence
		if($sequence === null) $sequence = count($this->columns) + 1;

		// new column
		$this->columns[$name] = new SpoonDataGridColumn($name, $label, $value, $url, $title, $image, $sequence);
	}


	/**
	 * Builds the requested url
	 *
	 * @return	string
	 * @param	int $offset
	 * @param	string $order
	 * @param	string $sort
	 */
	private function buildURL($offset, $order, $sort)
	{
		return str_replace(array('[offset]', '[order]', '[sort]'), array($offset, $order, $sort), $this->url);
	}


	/**
	 * Clears the attributes
	 *
	 * @return	void
	 */
	public function clearAttributes()
	{
		$this->attributes['datagrid'] = array();
	}


	/**
	 * Clears the attributes for a specific column
	 *
	 * @return	void
	 * @param	string $column
	 */
	public function clearColumnAttributes($column)
	{
		// has results
		if($this->source->getNumResults() > 0)
		{
			// column doesn't exist
			if(!isset($this->columns[(string) $column])) throw new SpoonDataGridException('The column "'. (string) $column .'" doesn\'t exist and therefor no attributes can be removed.');

			// exists
			$this->columns[(string) $column]->clearAttributes();
		}
	}


	/**
	 * Clears the even row attributes
	 *
	 * @return	void
	 */
	public function clearEvenRowAttributes()
	{
		$this->attributes['row_even'] = array();
	}


	/**
	 * clears the odd row attributes
	 *
	 * @return	void
	 */
	public function clearOddRowAttributes()
	{
		$this->attributes['row_odd'] = array();
	}


	/**
	 * Clears the row attributes
	 *
	 * @return	void
	 */
	public function clearRowAttributes()
	{
		$this->attributes['row'] = array();
	}


	/**
	 * Creates the default columns, based on the query
	 *
	 * @return	void
	 */
	private function createColumns()
	{
		// has results
		if($this->source->getNumResults() != 0)
		{
			// fetch the column names
			foreach($this->source->getColumns() as $column)
			{
				// add column
				$this->addColumn($column, $column, '['. $column .']', null, null, null, (count($this->columns) +1));

				// may be sorted on
				$this->allowedSortingColumns[] = $column;
			}
		}
	}


	/**
	 * Shows the output & stops script execution
	 *
	 * @return	void
	 */
	public function display()
	{
		echo $this->getContent();
		exit;
	}


	/**
	 * Generates the array with the order columns
	 *
	 * @return	void
	 */
	private function generateOrder()
	{
		// delete current cache of sortable columns
		$this->sortingColumns = array();

		// columns present
		if(count($this->columns) != 0)
		{
			// loop columns
			foreach($this->columns as $column)
			{
				// allowed sorting, sorting enabled specifically
				if(in_array($column->getName(), $this->allowedSortingColumns) && $column->getSorting())
				{
					// add to the list
					$this->sortingColumns[] = $column->getName();

					// default
					$default = $column->getName();
				}
			}

			// no default defined
			if($this->sortingColumn === null && isset($default)) $this->sortingColumn = $default;
		}
	}


	/**
	 * Retrieve all the datagrid attributes
	 *
	 * @return	array
	 */
	public function getAttributes()
	{
		return $this->attributes['datagrid'];
	}


	/**
	 * Retrieve the columns sequence
	 *
	 * @return	array
	 */
	private function getColumnsSequence()
	{
		// loop all the columns
		foreach($this->columns as $column) $aSequence[$column->getSequence()] = $column->getName();

		// reindex
		return SpoonFilter::arraySortKeys($aSequence);
	}


	/**
	 * Retrieve the final output
	 *
	 * @return	string
	 */
	public function getContent()
	{
		// parse if needed
		if(!$this->parsed) $this->parse();

		// fetch content
		return $this->content;
	}


	/**
	 * Fetch the debug status
	 *
	 * @return	bool
	 */
	public function getDebug()
	{
		return $this->debug;
	}


	/**
	 * Returns the html attributes based on an array
	 *
	 * @return	string
	 * @param	array[optional] $array
	 */
	private function getHTMLAttributes(array $array = array())
	{
		// output
		$html = '';

		// loop elements
		foreach($array as $label => $value) $html .= ' '. $label .'="'. $value .'"';
		return $html;
	}


	/**
	 * Retrieve the offset value
	 *
	 * @return	int
	 */
	public function getOffset()
	{
		// default offset
		$offset = null;

		// paging enabled
		if($this->paging)
		{
			// has results
			if($this->source->getNumResults() != 0)
			{
				// offset parameter defined
				if($this->offsetParameter !== null) $offset = $this->offsetParameter;

				// use default
				else $offset = (isset($_GET['offset'])) ? (int) $_GET['offset'] : 0;

				// offset cant be bigger than the number of results
				if($offset >= $this->source->getNumResults()) $offset = (int) $this->source->getNumResults() - $this->pagingLimit;

				// offset divided by the per page limit should have no rest
				if(($offset % $this->pagingLimit) != 0) $offset = 0;

				// offset minus the pagina limit may not go below zero
				if(($offset - $this->pagingLimit) < 0) $offset = 0;
			}

			// no results
			else $offset = 0;
		}

		return $offset;
	}


	/**
	 * Retrieves the column that's currently being sorted on
	 *
	 * @return	string
	 */
	public function getOrder()
	{
		// default value
		$order = null;

		// sorting enabled
		if($this->getSorting())
		{
			/**
			 * First the list of columns that can be ordered on,
			 * must be re-generated
			 */
			$this->generateOrder();

			// order parameter defined
			if($this->orderParameter !== null) $order = $this->orderParameter;

			// defaut order
			else $order = (isset($_GET['order'])) ? (string) $_GET['order'] : null;

			// retrieve order
			$order = SpoonFilter::getValue($order, $this->sortingColumns, $this->sortingColumn);
		}

		return $order;
	}


	/**
	 * Retrieve the number of results for this datagrids' source
	 *
	 * @return	int
	 */
	public function getNumResults()
	{
		return $this->source->getNumResults();
	}


	/**
	 * Paging status
	 *
	 * @return	bool
	 */
	public function getPaging()
	{
		return $this->paging;
	}


	/**
	 * Fetch the paging class
	 *
	 * @return	string
	 */
	public function getPagingClass()
	{
		return $this->pagingClass;
	}


	/**
	 * Fetch the number of items per page
	 *
	 * @return	int
	 */
	public function getPagingLimit()
	{
		return ($this->paging) ? $this->pagingLimit : null;
	}


	/**
	 * Retrieve the sorting method
	 *
	 * @return	string
	 */
	public function getSort()
	{
		// sort parameter defined
		if($this->sortParameter !== null) $sort = $this->sortParameter;

		// default sort
		else $sort = (isset($_GET['sort'])) ? (string) $_GET['sort'] : null;

		// retrieve sort
		$sort = SpoonFilter::getValue($sort, array('asc', 'desc'), 'asc');

		return $sort;
	}


	/**
	 * Retrieve the sorting status
	 *
	 * @return	bool
	 */
	public function getSorting()
	{
		// generate order
		$this->generateOrder();

		// sorting columns exist?
		return (count($this->sortingColumns) != 0) ? true : false;
	}


	/**
	 * Retrieve the full template path
	 *
	 * @return	string
	 */
	private function getTemplatePath()
	{
		return ($this->template != null) ? $this->template : dirname(__FILE__) .'/datagrid.tpl';
	}


	/**
	 * Parse the final output
	 *
	 * @return	void
	 */
	private function parse()
	{
		// has results
		if($this->source->getNumResults() > 0)
		{
			// fetch records
			$aRecords = $this->source->getData($this->getOffset(), $this->getPagingLimit(), $this->getOrder(), $this->getSort());

			// has results
			if(count($aRecords) != 0)
			{
				// load template
				$this->tpl = new SpoonTemplate();

				// compile directory
				$compileDirectory = ($this->compileDirectory !== null) ? $this->compileDirectory : dirname(realpath(__FILE__));
				$this->tpl->setCompileDirectory($compileDirectory);

				// only force compiling when debug is enabled
				if($this->debug) $this->tpl->setForceCompile(true);

				// table attributes
				$this->parseAttributes();

				// table summary
				$this->parseSummary();

				// caption/description
				$this->parseCaption();

				// header row
				$this->parseHeader();

				// actual rows
				$this->parseBody($aRecords);

				// pagination
				$this->parseFooter();

				// parse to buffer
				ob_start();
				$this->tpl->display($this->getTemplatePath());
				$this->content = ob_get_clean();

			}
		}

		// update parsed status
		$this->parsed = true;
	}


	/**
	 * Parses the datagrid attributes
	 *
	 * @return	void
	 */
	private function parseAttributes()
	{
		$this->tpl->assign('attributes', $this->getHtmlAttributes($this->attributes['datagrid']));
	}


	/**
	 * Parses the body
	 *
	 * @return	void
	 * @param	array $records
	 */
	private function parseBody(array $records)
	{
		// init var
		$aRows = array();

		// columns sequence
		$aSequence = $this->getColumnsSequence();

		// loop records
		foreach($records as $i => &$record)
		{
			// special record
			$record = $this->parseRecord($record);

			// parse column functions
			$record = $this->parseColumnFunctions($record);

			// reset row
			$aRow = array('attributes' => '', 'oddAttributes' => '', 'evenAttributes' => '', 'columns' => array());

			// row attributes
			$aRow['attributes'] = str_replace($record['labels'], $record['values'], $this->getHtmlAttributes($this->attributes['row']));

			// odd row attributes (reversed since the first $i = 0)
			if(!SpoonFilter::isOdd($i)) $aRow['oddAttributes'] = str_replace($record['labels'], $record['values'], $this->getHtmlAttributes($this->attributes['row_odd']));

			// even row attributes
			else $aRow['evenAttributes'] = str_replace($record['labels'], $record['values'], $this->getHtmlAttributes($this->attributes['row_even']));

			// define the columns
			$aColumns = array();

			// loop columns
			foreach($aSequence as $name)
			{
				// column
				$column = $this->columns[$name];

				// has overwrite enabled
				if($column->getOverwrite())
				{
					// fetch key
					$iKey = array_search('['. $column->getName() .']', $record['labels']);

					// parse the actual value
					$columnValue = $record['values'][$iKey];
				}

				// no overwrite status
				else
				{
					// default value
					$columnValue = '';

					// has an url
					if($column->getUrl() !== null)
					{
						// open url tag
						$columnValue .= '<a href="'. str_replace($record['labels'], $record['values'], $column->getUrl()) .'"';

						// add title
						$columnValue .= ' title="'. str_replace($record['labels'], $record['values'], $column->getUrlTitle()) .'"';

						// confirm
						if($column->getConfirm() && $column->getConfirmMessage() !== null)
						{
							// default confirm
							if($column->getConfirmCustom() == '') $columnValue .= ' onclick="return confirm(\''. str_replace($record['labels'], $record['values'], $column->getConfirmMessage()) .'\');"';

							// custom confirm
							else
							{
								// replace the message
								$tmpValue = str_replace('%message%', $column->getConfirmMessage(), $column->getConfirmCustom());

								// make vars available
								$tmpValue = str_replace($record['labels'], $record['values'], $tmpValue);

								// add id
								$columnValue .= ' '. $tmpValue;
							}
						}

						// close start tag
						$columnValue .= '>';
					}

					// has an image
					if($column->getImage() !== null)
					{
						// open img tag
						$columnValue .= '<img src="'. str_replace($record['labels'], $record['values'], $column->getImage()) .'"';

						// add title & alt
						$columnValue .= ' alt="'. str_replace($record['labels'], $record['values'], $column->getImageTitle()) .'"';
						$columnValue .= ' title="'. str_replace($record['labels'], $record['values'], $column->getImageTitle()) .'"';

						// close img tag
						$columnValue .= ' />';
					}

					// regular value
					else
					{
						// fetch key
						$iKey = array_search('['. $column->getName() .']', $record['labels']);

						// parse value
						$columnValue .= $record['values'][$iKey];
					}

					// has an url (close the tag)
					if($column->getUrl() !== null) $columnValue .= '</a>';
				}

				// fetch column attributes
				$columnAttributes = $this->getHtmlAttributes($column->getAttributes());

				// visible & iteration
				if(!$column->getHidden())
				{
					// add this column
					$aColumns[] = array('attributes' => $columnAttributes, 'value' => $columnValue);

					// add to custom list
					$aRow['column'][$name] = $columnValue;
				}
			}

			// add the columns to the rows
			$aRow['columns'] = $aColumns;

			// add the row
			$aRows[] = $aRow;
		}

		// assign body
		$this->tpl->assign('rows', $aRows);

		// assign the number of columns
		$this->tpl->assign('numColumns', count($aRows[0]['columns']));
	}


	/**
	 * Parses the caption tag
	 *
	 * @return	void
	 */
	private function parseCaption()
	{
		if($this->caption !== null) $this->tpl->assign('caption', $this->caption);
	}


	/**
	 * Parses the column functions
	 *
	 * @return	array
	 * @param	array $record
	 */
	private function parseColumnFunctions($record)
	{
		// loop functions
		foreach ($this->columnFunctions as $function)
		{
			// no arguments given
			if(is_null($function['arguments'])) $value = @call_user_func($function['function']);

			// array
			elseif(is_array($function['arguments']))
			{
				// replace arguments
				$function['arguments'] = str_replace($record['labels'], $record['values'], $function['arguments']);

				// execute function
				$value = @call_user_func_array($function['function'], $function['arguments']);
			}

			// no null/array
			else $value = @call_user_func($function['function'], str_replace($record['labels'], $record['values'], $function['arguments']));

			/**
			 * Now that we have the return value of this method, we can
			 * do the actual writeback to the column(s). If overwrite was
			 * true, we're going to enable the overwrite of the writeback column(s)
			 */

			// one column, that exists
			if(is_string($function['columns']) && isset($this->columns[$function['columns']]))
			{
				// fetch key
				$iKey = array_search('['. $function['columns'] .']', $record['labels']);

				// value was set
				if($iKey !== false)
				{
					// update value
					$record['values'][$iKey] = $value;

					// update overwrite
					if($function['overwrite']) $this->columns[$function['columns']]->setOverwrite(true);
				}
			}

			// write to multiple columns
			elseif(is_array($function['columns']) && count($function['columns']) != 0)
			{
				// loop target columns
				foreach($function['columns'] as $column)
				{
					// fetch key
					$iKey = array_search('['. $column .']', $record['labels']);

					// value was set
					if($iKey !== false)
					{
						// update value
						$record['values'][$iKey] = $value;

						// update overwrite
						if($function['overwrite']) $this->columns[$column]->setOverwrite(true);
					}
				}
			}
		}

		return $record;
	}


	/**
	 * Parses the footer
	 *
	 * @return	void
	 */
	private function parseFooter()
	{
		// attributes
		$this->tpl->assign('footerAttributes', $this->getHtmlAttributes($this->attributes['footer']));

		// parse paging
		$this->parsePaging();
	}


	/**
	 * Parses the header row
	 *
	 * @return	void
	 */
	private function parseHeader()
	{
		// init vars
		$aHeader = array();

		// attributes
		$this->tpl->assign('headerAttributes', $this->getHtmlAttributes($this->attributes['header']));

		// sequence
		$aSequence = $this->getColumnsSequence();

		// sorting enabled?
		$sorting = $this->getSorting();

		// sortable columns
		$aSortingColumns = array();
		foreach($aSequence as $column) if($this->columns[$column]->getSorting()) $aSortingColumns[] = $column;

		// loop columns
		foreach($aSequence as $name)
		{
			// define column
			$aColumn = array();

			// column
			$column = $this->columns[$name];

			// visible
			if(!$column->getHidden())
			{
				// sorting globally enabled AND for this column
				if($sorting && in_array($name, $aSortingColumns))
				{
					// init var
					$aColumn['sorting'] = true;
					$aColumn['noSorting'] = false;

					// sorted on this column?
					if($this->getOrder() == $name)
					{
						// sorted
						$aColumn['sorted'] = true;
						$aColumn['notSorted'] = false;

						// asc
						if($this->getSort() == 'asc')
						{
							$aColumn['sortedAsc'] = true;
							$aColumn['sortedDesc'] = false;
						}

						// desc
						else
						{
							$aColumn['sortedAsc'] = false;
							$aColumn['sortedDesc'] = true;
						}
					}

					/**
					 * This column is sortable, but there's currently not being
					 * sorted on this column
					 */
					elseif(in_array($name, $aSortingColumns))
					{
						$aColumn['sorted'] = false;
						$aColumn['notSorted'] = true;
					}

					/**
					 * URL's are parsed for the opposite column, as for the asc & desc version
					 * for this column. If the sorting is currently not on this column
					 * the default sorting method (mostly asc) will be used to define the opposite/default
					 * sorting method.
					 */

					// currently not sorting on this column
					if($this->getOrder() != $name) $sortingMethod = $this->columns[$name]->getSortingMethod();

					// sorted on this column ascending
					elseif($this->getSort() == 'asc') $sortingMethod = 'desc';

					// sorting on this column descending
					else $sortingMethod = 'asc';

					// build actual urls
					$aColumn['sortingURL'] = $this->buildURL($this->getOffset(), $name, $sortingMethod);
					$aColumn['sortingURLAsc'] = $this->buildURL($this->getOffset(), $name, 'asc');
					$aColumn['sortingURLDesc'] = $this->buildURL($this->getOffset(), $name, 'desc');

					/**
					 * There's no point in parsing the icon for this column if there's
					 * not being sorted on this column.
					 */

					/**
					 * To define the default icon for sorting, we need to apply
					 * the same rules as with the default url. See those comments for
					 * the necessary details.
					 */
					if($this->getOrder() != $name) $sortingIcon = $this->sortingIcons[$this->columns[$name]->getSortingMethod()];

					// sorted on this column asc/desc
					elseif($this->getSort() == 'asc') $sortingIcon = $this->sortingIcons['ascSelected'];
					else $sortingIcon = $this->sortingIcons['descSelected'];

					// asc & desc icons
					$aColumn['sortingIcon'] = $sortingIcon;
					$aColumn['sortingIconAsc'] = ($this->getSort() == 'asc') ? $this->sortingIcons['ascSelected'] : $this->sortingIcons['asc'];
					$aColumn['sortingIconDesc'] = ($this->getSort() == 'desc') ? $this->sortingIcons['descSelected'] : $this->sortingIcons['desc'];

					// not sorted on this column
					if($this->getOrder() != $name) $sortingLabel = $this->sortingLabels[$this->columns[$name]->getSortingMethod()];

					// sorted on this column asc/desc
					elseif($this->getSort() == 'asc') $sortingLabel = $this->sortingLabels['ascSelected'];
					else $sortingLabel = $this->sortingLabels['descSelected'];

					$aColumn['sortingLabel'] = $sortingLabel;
					$aColumn['sortingLabelAsc'] = $this->sortingLabels['asc'];
					$aColumn['sortingLabelDesc'] = $this->sortingLabels['desc'];
				}

				// no sorting enabled for this column
				else
				{
					$aColumn['sorting'] = false;
					$aColumn['noSorting'] = true;
				}

				// parse vars
				$aColumn['label'] = $column->getLabel();

				// add to array
				$aHeader[] = $aColumn;
			}
		}

		// default headers
		$this->tpl->assign('headers', $aHeader);
	}


	/**
	 * Parses the paging
	 *
	 * @return	void
	 */
	private function parsePaging()
	{
		// enabled
		if($this->paging)
		{
			// offset, order & sort
			$this->tpl->assign(array('offset', 'order', 'sort'), array($this->getOffset(), $this->getOrder(), $this->getSort()));

			// number of results
			$this->tpl->assign('iResults', $this->source->getNumResults());

			// number of pages
			$this->tpl->assign('iPages', ceil($this->source->getNumResults() / $this->pagingLimit));

			// current page
			$this->tpl->assign('iCurrentPage', ceil($this->getOffset() / $this->pagingLimit) + 1);

			// number of items per page
			$this->tpl->assign('iPerPage', $this->pagingLimit);

			// parse paging @todo @davy: check me plz
			$content = call_user_func(array($this->pagingClass, 'getContent'), $this->url, $this->getOffset(), $this->getOrder(), $this->getSort(), $this->source->getNumResults(), $this->pagingLimit, $this->debug, $this->compileDirectory);

			// asign content
			$this->tpl->assign('paging', $content);
		}
	}


	/**
	 * Parses the record
	 *
	 * @return	array
	 * @param	array $record
	 */
	private function parseRecord(array $record)
	{
		// create labels/values array
		foreach($record as $label => $value)
		{
			$array['labels'][] = '['. $label .']';
			$array['values'][] = $value;
		}

		// add offset?
		if($this->paging)
		{
			$array['labels'][] = '[offset]';
			$array['values'][] = $this->getOffset();
		}

		// sorting
		if(count($this->sortingColumns) != 0)
		{
			$array['labels'][] = '[order]';
			$array['labels'][] = '[sort]';
			// --
			$array['values'][] = $this->getOrder();
			$array['values'][] = $this->getSort();
		}

		// loop the record fields
		foreach($this->columns as $column)
		{
			// this column is an extra field, added in the datagrid
			if(!in_array('['. $column->getName() .']', $array['labels']))
			{

				$array['values'][] = str_replace($array['labels'], $array['values'], $column->getValue());
				$array['labels'][] = '['. $column->getName() .']';
			}
		}

		return $array;
	}


	/**
	 * Parses the summary
	 *
	 * @return	void
	 */
	private function parseSummary()
	{
		if($this->summary !== null) $this->tpl->assign('summary', $this->summary);
	}


	/**
	 * Set main datagrid attributes
	 *
	 * @return	void
	 * @param	array $attributes
	 */
	public function setAttributes(array $attributes)
	{
		foreach($attributes as $key => $value) $this->attributes['datagrid'][(string) $key] = (string) $value;
	}


	/**
	 * Sets the table caption or main description
	 *
	 * @return	void
	 * @param	string $value
	 */
	public function setCaption($value)
	{
		$this->caption = (string) $value;
	}


	/**
	 * Set one or more attributes for a column
	 *
	 * @return	void
	 * @param	string $column
	 * @param	array $attributes
	 */
	public function setColumnAttributes($column, array $attributes)
	{
		// has results
		if($this->source->getNumResults() > 0)
		{
			// column doesnt exist
			if(!isset($this->columns[$column])) throw new SpoonDataGridException('The column "'. $column .'" doesn\'t exist, therefor no attributes can be added.');

			// exists
			else $this->columns[$column]->setAttributes($attributes);
		}
	}


	/**
	 * Set a custom column confirm message
	 *
	 * @return	void
	 * @param	string $column
	 * @param	string $message
	 * @param	string[optional] $custom
	 */
	public function setColumnConfirm($column, $message, $custom = null)
	{
		// has results
		if($this->source->getNumResults() > 0)
		{
			// column doesnt exist
			if(!isset($this->columns[$column])) throw new SpoonDataGridException('The column "'. $column .'" doesn\'t exist, therefor no confirm message/script can be added.');

			// exists
			else $this->columns[$column]->setConfirm($message, $custom);
		}
	}


	/**
	 * Sets the column function to be executed for every row
	 *
	 * @return	void
	 * @param	mixed $function
	 * @param	mixed[optional] $arguments
	 * @param	mixed $columns
	 * @param	bool[optional] $overwrite
	 */
	public function setColumnFunction($function, $arguments = null, $columns, $overwrite = false)
	{
		// has results
		if($this->source->getNumResults() > 0)
		{
			// regular function
			if(!is_array($function))
			{
				// function checks
				if(!function_exists((string) $function)) throw new SpoonDataGridException('The function "'. (string) $function .'" doesn\'t exist.');
			}

			// class method
			else
			{
				// method checks
				if(count($function) != 2) throw new SpoonDataGridException('When providing a method for a column function is must be like array(\'class\', \'method\')');

				// method doesn't exist
				elseif(!method_exists($function[0], $function[1])) throw new SpoonDataGridException('The method '. (string) $function[0] .'::'. (string) $function[1] .' does not exist.');
			}

			// add to function stack
			$this->columnFunctions[] = array('function' => $function, 'arguments' => $arguments, 'columns' => $columns, 'overwrite' => (bool) $overwrite);
		}
	}


	/**
	 * Sets a single column hidden
	 *
	 * @return	void
	 * @param	string $column
	 * @param	bool[optional] $on
	 */
	public function setColumnHidden($column, $on = true)
	{
		// has results
		if($this->source->getNumResults() > 0)
		{
			// column doesn't exist
			if(!isset($this->columns[(string) $column])) throw new SpoonDataGridException('The column "'. (string) $column .'" doesn\'t exist and therefor can\'t be set hidden.');

			// exists
			$this->columns[(string) $column]->setHidden($on);
		}
	}


	/**
	 * Sets one or more columns hidden
	 *
	 * @return	void
	 * @param	array $columns
	 */
	public function setColumnsHidden($columns)
	{
		// has results
		if($this->source->getNumResults() > 0)
		{
			// array
			if(is_array($columns)) foreach($columns as $column) $this->setColumnHidden($column);

			// multiple arguments
			else
			{
				// redefine columns
				$columns = func_get_args();

				// set columns hidden
				foreach($columns as $column) $this->setColumnHidden($column);
			}
		}
	}


	/**
	 * Sets the columns sequence
	 *
	 * @return	void
	 */
	public function setColumnsSequence($columns)
	{
		// has results
		if($this->source->getNumResults() > 0)
		{
			// array
			if(is_array($columns)) call_user_method_array('setColumnsSequence', $this, $columns);

			// multiple arguments
			else
			{
				// current sequence
				$aSequence = $this->getColumnsSequence();

				// fetch arguments
				$arguments = func_get_args();

				// build columns
				$aColumns = (is_array($arguments[0])) ? $arguments[0] : $arguments;

				// counter
				$i = 1;

				// loop colums
				foreach ($aColumns as $column)
				{
					// column exists
					if(!isset($this->columns[(string) $column])) throw new SpoonDataGridException('The column "'. (string) $column .'" doesn\'t exist. Therefor its sequence can\'t be altered.');

					// update sequence
					$this->columns[(string) $column]->setSequence($i);

					// remove from the original list
					$iKey = (int) array_search((string) $column, $aSequence);
					unset($aSequence[$iKey]);

					// update counter
					$i++;
				}

				// reset counter
				$i = 1;

				// add remaining columns
				foreach($aSequence as $sequence)
				{
					// update sequence
					$this->columns[$sequence]->setSequence(count($aColumns) + $i);

					// update counter
					$i++;
				}
			}
		}
	}


	/**
	 * Set the default sorting method for a column
	 *
	 * @return	void
	 * @param	string $column
	 * @param	string[optional] $sort
	 */
	public function setColumnSortingMethod($column, $sort = 'asc')
	{
		// has results
		if($this->source->getNumResults() > 0)
		{
			// column doesn't exist
			if(!isset($this->columns[(string) $column])) throw new SpoonDataGridException('The column "'. (string) $column .'" doesn\'t exist and therefor no default sorting method can be applied.');

			// exists
			$this->columns[(string) $column]->setSortingMethod($sort);
		}
	}


	/**
	 * Set the url for a column
	 *
	 * @return	void
	 * @param	string $column
	 * @param	string $url
	 * @param	string[optional] $title
	 */
	public function setColumnUrl($column, $url, $title = null)
	{
		// has results
		if($this->source->getNumResults() > 0)
		{
			// column doesn't exist
			if(!isset($this->columns[(string) $column])) throw new SpoonDataGridException('The column "'. (string) $column .'" doesn\'t exist and therefor no url can be applied.');

			// exists
			$this->columns[(string) $column]->setUrl($url, $title);
		}
	}


	/**
	 * Sets the compile directory
	 *
	 * @return	void
	 * @param	string $path
	 */
	public function setCompileDirectory($path)
	{
		$this->compileDirectory = (string) $path;
	}


	/**
	 * Adjust the debug setting
	 *
	 * @return	void
	 * @param	bool[optional] $on
	 */
	public function setDebug($on = true)
	{
		$this->debug = (bool) $on;
	}


	/**
	 * Set the even row attributes
	 *
	 * @return	void
	 * @param	array $attributes
	 */
	public function setEvenRowAttributes(array $attributes)
	{
		// has results
		if($this->source->getNumResults() > 0)
		{
			// add to the list
			foreach($attributes as $key => $value) $this->attributes['row_even'][(string) $key] = (string) $value;
		}
	}


	/**
	 * Set some custom header attributes
	 *
	 * @return	void
	 * @param	array $attributes
	 */
	public function setHeaderAttributes(array $attributes)
	{
		foreach($attributes as $key => $value) $this->attributes['header'][(string) $key] = (string) $value;
	}


	/**
	 * Set the header labels
	 *
	 * @return	void
	 * @param	array $labels
	 */
	public function setHeaderLabels(array $labels)
	{
		// has results
		if($this->source->getNumResults() > 0)
		{
			// loop the keys
			foreach($labels as $column => $label)
			{
				// column doesn't exist
				if(!isset($this->columns[$column])) throw new SpoonDataGridException('The column "'. $column .'" doesn\t exist, therefor no label can be assigned.');

				// exists
				else $this->columns[$column]->setLabel($label);
			}
		}
	}


	/**
	 * Set the odd row attributes
	 *
	 * @return	void
	 * @param	array $attributes
	 */
	public function setOddRowAttributes(array $attributes)
	{
		// has results
		if($this->source->getNumResults() > 0)
		{
			// add to the list
			foreach($attributes as $key => $value) $this->attributes['row_odd'][(string) $key] = (string) $value;
		}
	}


	/**
	 * Sets the value for offset. eg from the url
	 *
	 * @return	void
	 * @param	int[optional] $value
	 */
	public function setOffsetParameter($value = null)
	{
		$this->offsetParameter = (int) $value;
	}


	/**
	 * Sets the value for the order. eg from the url
	 *
	 * @return	void
	 * @param	string[optional] $value
	 */
	public function setOrderParameter($value = null)
	{
		$this->orderParameter = (string) $value;
	}


	/**
	 * Allow/disallow showing the results on multiple pages
	 *
	 * @return	void
	 * @param	bool[optional] $on
	 */
	public function setPaging($on = false)
	{
		$this->paging = (bool) $on;
	}


	/**
	 * Sets the alternative paging class
	 *
	 * @return	void
	 */
	public function setPagingClass($class)
	{
		// class cant be found
		if(!class_exists((string) $class)) throw new SpoonDataGridException('The class "'. (string) $class .'" you provided for the alternative paging can not be found.');

		// does not extend SpoonDataGridPaging
		// @todo does it implement the interface
//		if(!is_subclass_of($class, 'SpoonDataGridPaging')) throw new SpoonDataGridException('The class "'. (string) $class .'" does not extend SpoonDataGridPaging which is obligated.');

		// set the class
		else $this->pagingClass = $class;
	}


	/**
	 * Sets the number of results per page
	 *
	 * @return	void
	 * @param	int[optional] $limit
	 */
	public function setPagingLimit($limit = 30)
	{
		$this->pagingLimit = abs((int) $limit);
	}


	/**
	 * Sets the row attributes
	 *
	 * @return	void
	 * @param	array $attributes
	 */
	public function setRowAttributes(array $attributes)
	{
		foreach($attributes as $key => $value) $this->attributes['row'][(string) $key] = (string) $value;
	}


	/**
	 * Sets the columns that may be sorted on
	 *
	 * @return	void
	 * @param	array $columns
	 * @param	string[optional] $default
	 */
	public function setSortingColumns(array $columns, $default = null)
	{
		// has results
		if($this->source->getNumResults() > 0)
		{
			// loop columns
			foreach($columns as $column)
			{
				// column doesn't exist
				if(!isset($this->columns[(string) $column])) throw new SpoonDataGridException('The column "'. (string) $column .'" doesn\'t exist and therefor can\'t be sorted on.');

				// column exists
				else
				{
					// not sortable
					if(!in_array((string) $column, $this->allowedSortingColumns)) throw new SpoonDataGridException('The column "'. (string) $column .'" can\'t be sorted on.');

					// sortable
					else
					{
						// enable sorting
						$this->columns[(string) $column]->setSorting(true);

						// set default sorting
						if(!isset($defaultColumn)) $defaultColumn = (string) $column;
					}
				}
			}

			// default column set
			if($default !== null && in_array($defaultColumn, $columns)) $defaultColumn = (string) $default;

			// default column is not good
			if(!in_array($defaultColumn, $this->allowedSortingColumns)) throw new SpoonDataGridException('The column "'. $defaultColumn .'" can\'t be set as the default sorting column, because it doesn\'t exist or may not be sorted on.');

			// set default column
			$this->sortingColumn = $defaultColumn;
		}
	}


	/**
	 * Sets the sorting icons
	 *
	 * @return	void
	 * @param	string[optional] $asc
	 * @param	string[optional] $ascSelected
	 * @param	string[optional] $desc
	 * @param	string[optional] $descSelected
	 */
	public function setSortingIcons($asc = null, $ascSelected = null, $desc = null, $descSelected)
	{
		if($asc !== null) $this->sortingIcons['asc'] = (string) $asc;
		if($ascSelected !== null) $this->sortingIcons['ascSelected'] = (string) $ascSelected;
		if($desc !== null) $this->sortingIcons['desc'] = (string) $desc;
		if($descSelected !== null) $this->sortingIcons['descSelected'] = (string) $descSelected;
	}


	/**
	 * Sets the sorting labels
	 *
	 * @return	void
	 * @param	string[optional] $asc
	 * @param	string[optional] $ascSelected
	 * @param	string[optional] $desc
	 * @param	string[optional] $descSelected
	 */
	public function setSortingLabels($asc = null, $ascSelected = null, $desc = null, $descSelected = null)
	{
		if($asc !== null) $this->sortingLabels['asc'] = (string) $asc;
		if($ascSelected !== null) $this->sortingLabels['ascSelected'] = (string) $ascSelected;
		if($desc !== null) $this->sortingLabels['desc'] = (string) $desc;
		if($descSelected !== null) $this->sortingLabels['descSelected'] = (string) $descSelected;
	}


	/**
	 * Sets the value to sort
	 *
	 * @return	void
	 * @param	string[optional] $value
	 */
	public function setSortParameter($value = 'desc')
	{
		$this->sortParameter = SpoonFilter::getValue($value, array('asc', 'desc'), 'asc');
	}


	/**
	 * Sets the source for this datagrid
	 *
	 * @return	void
	 * @param	SpoonDataGridSource $source
	 */
	private function setSource(SpoonDataGridSource $source)
	{
		$this->source = $source;
	}


	/**
	 * Sets the table summary
	 *
	 * @return	void
	 * @param	string $value
	 */
	public function setSummary($value)
	{
		$this->summary = (string) $value;
	}


	/**
	 * Sets the path to the template file
	 *
	 * @return	void
	 * @param	string $template
	 */
	public function setTemplate($template)
	{
		$this->template = (string) $template;
	}


	/**
	 * Defines the default url
	 *
	 * @return	void
	 * @param	string $url
	 */
	public function setURL($url)
	{
		$this->url = (string) $url;
	}
}


/**
 * This class is internally used by the datagrid to hold the data
 * for every column.
 *
 * @package		html
 * @subpackage	datagrid
 *
 *
 * @author		Davy Hellemans <davy@spoon-library.be>
 * @since		1.0.0
 */
class SpoonDataGridColumn
{
	/**
	 * Main cell attributes
	 *
	 * @var	array
	 */
	private $attributes = array();


	/**
	 * Confirmation required (via URL)
	 *
	 * @var	bool
	 */
	private $confirm = false;


	/**
	 * Custom confirmation script
	 *
	 * @var	string
	 */
	private $confirmCustom;


	/**
	 * Confirmation message
	 *
	 * @var	string
	 */
	private $confirmMessage;


	/**
	 * Is this column hidden
	 *
	 * @var	bool
	 */
	private $hidden = false;


	/**
	 * Image for this column
	 *
	 * @var	string
	 */
	private $image;


	/**
	 * Alt/title tag for the image
	 *
	 * @var	string
	 */
	private $imageTitle;


	/**
	 * Label for the header column
	 *
	 * @var	string
	 */
	private $label;


	/**
	 * Name for this column
	 *
	 * @var	string
	 */
	private $name;


	/**
	 * Does the value overwrite images & url
	 *
	 * @var	bool
	 */
	private $overwriteValue = false;


	/**
	 * Sequence of this column
	 *
	 * @var	int
	 */
	private $sequence = 0;


	/**
	 * Is this column sortable
	 *
	 * @var	bool
	 */
	private $sorting = false;


	/**
	 * The default sorting method for this column
	 *
	 * @var	string
	 */
	private $sortingMethod = 'asc';


	/**
	 * URL for this column
	 *
	 * @var	string
	 */
	private $url;


	/**
	 * Url title tag
	 *
	 * @var	string
	 */
	private $urlTitle;


	/**
	 * The value for this column
	 *
	 * @var	string
	 */
	private $value;


	/**
	 * Class constructor.
	 *
	 * @return	void
	 * @param	string $name
	 * @param	string[optional] $label
	 * @param	string[optional] $value
	 * @param	string[optional] $URL
	 * @param	string[optional] $image
	 * @param	string[optional] $sequence
	 */
	public function __construct($name, $label = null, $value = null, $URL = null, $title = null, $image = null, $sequence = null)
	{
		// name, label & value
		$this->name = (string) $name;
		$this->label = (string) $label;
		$this->value = (string) $value;

		// url, title & image
		if($URL !== null) $this->url = (string) $URL;
		if($title !== null) $this->urlTitle = (string) $title;
		if($image !== null) $this->image = (string) $image;

		// sequence
		$this->sequence = (int) $sequence;
	}


	/**
	 * Clears the list of attributes for this column
	 *
	 * @return	void
	 */
	public function clearAttributes()
	{
		$this->attributes = array();
	}


	/**
	 * Retrieve the attributes
	 *
	 * @return	array
	 */
	public function getAttributes()
	{
		return $this->attributes;
	}


	/**
	 * Retrieve the confirm setting
	 *
	 * @return	bool
	 */
	public function getConfirm()
	{
		return $this->confirm;
	}


	/**
	 * Fetch the confirm custom script
	 *
	 * @return	string
	 */
	public function getConfirmCustom()
	{
		return $this->confirmCustom;
	}


	/**
	 * Retrieve the confirm message
	 *
	 * @return	string
	 */
	public function getConfirmMessage()
	{
		return $this->confirmMessage;
	}


	/**
	 * Retrieve the hidden status
	 *
	 * @return	bool
	 */
	public function getHidden()
	{
		return $this->hidden;
	}


	/**
	 * Retrieve the image
	 *
	 * @return	string
	 */
	public function getImage()
	{
		return $this->image;
	}


	/**
	 * Retrieve the image title
	 *
	 * @return	string
	 */
	public function getImageTitle()
	{
		return $this->imageTitle;
	}


	/**
	 * Retrieve the label
	 *
	 * @return	string
	 */
	public function getLabel()
	{
		return $this->label;
	}


	/**
	 * Retrieve the label attributes
	 *
	 * @return	array
	 */
	public function getLabelAttributes()
	{
		return $this->labelAttributes;
	}


	/**
	 * Retrieve the name
	 *
	 * @return	string
	 */
	public function getName()
	{
		return $this->name;
	}


	/**
	 * Retrieve the overwrite value setting
	 *
	 * @return	bool
	 */
	public function getOverwrite()
	{
		return $this->overwriteValue;
	}


	/**
	 * Retrieve the sequence
	 *
	 * @return	int
	 */
	public function getSequence()
	{
		return $this->sequence;
	}


	/**
	 * Retrieve the sorting setting
	 *
	 * @return	bool
	 */
	public function getSorting()
	{
		return $this->sorting;
	}


	/**
	 * Retrieve the default sorting method
	 *
	 * @return	void
	 */
	public function getSortingMethod()
	{
		return $this->sortingMethod;
	}


	/**
	 * Retrieve the url
	 *
	 * @return	string
	 */
	public function getURL()
	{
		return $this->url;
	}


	/**
	 * Retrieve the url title tag
	 *
	 * @return	string
	 */
	public function getURLTitle()
	{
		return $this->urlTitle;
	}


	/**
	 * Retrieve the value
	 *
	 * @return	string
	 */
	public function getValue()
	{
		return $this->value;
	}


	/**
	 * Set the attributes
	 *
	 * @return	void
	 * @param	array $attributes
	 */
	public function setAttributes(array $attributes)
	{
		foreach($attributes as $key => $value) $this->attributes[(string) $key] = (string) $value;
	}


	/**
	 * Sets the confirm message
	 *
	 * @return	void
	 * @param	string $message
	 * @param	string[optional] $custom
	 */
	public function setConfirm($message, $custom = null)
	{
		$this->confirm = true;
		$this->confirmMessage = SpoonFilter::htmlentities((string) $message);
		$this->confirmCustom = (string) $custom;
	}


	/**
	 * Sets the hidden status
	 *
	 * @return	void
	 * @param	bool[optional] $on
	 */
	public function setHidden($on = true)
	{
		$this->hidden = (bool) $on;
	}


	/**
	 * Sets the image
	 *
	 * @return	void
	 * @param	string $image
	 * @param	string $title
	 */
	public function setImage($image, $title)
	{
		$this->image = (string) $image;
		$this->imageTitle = (string) $title;
	}


	/**
	 * Sets the label
	 *
	 * @return	void
	 * @param	string $label
	 */
	public function setLabel($label)
	{
		$this->label = (string) $label;
	}


	/**
	 * Sets the overwrite status
	 *
	 * @return	void
	 * @param	bool[optional] $on
	 */
	public function setOverwrite($on = true)
	{
		$this->overwriteValue = (bool) $on;
	}


	/**
	 * Sets the sequence
	 *
	 * @return	void
	 * @param	int $sequence
	 */
	public function setSequence($sequence)
	{
		$this->sequence = (int) $sequence;
	}

	/**
	 * Sets the sorting
	 *
	 * @return	void
	 * @param	bool[optional] $on
	 */
	public function setSorting($on = true)
	{
		$this->sorting = (bool) $on;
	}


	/**
	 * Sets the default sorting method for this column
	 *
	 * @return	void
	 * @param	string[optional] $sort
	 */
	public function setSortingMethod($sort = 'asc')
	{
		$this->sortingMethod = SpoonFilter::getValue($sort, array('asc', 'desc'), 'asc');
	}


	/**
	 * Sets the url
	 *
	 * @return	void
	 * @param	string $url
	 * @param	string[optional] $title
	 */
	public function setURL($url, $title = null)
	{
		$this->url = (string) $url;
		$this->urlTitle = (string) $title;
	}


	/**
	 * Sets the value & its overwrite setting
	 *
	 * @return	void
	 * @param	string $value
	 * @param	bool[optional] $overwrite
	 */
	public function setValue($value, $overwrite = false)
	{
		$this->value = (string) $value;
		$this->overwriteValue = (bool) $overwrite;
	}
}


/**
 * This class is the base class for sources used with datagrids
 *
 * @package		html
 * @subpackage	datagrid
 *
 *
 * @author		Davy Hellemans <davy@spoon-library.be>
 * @since		1.0.0
 */
class SpoonDataGridSource
{
	/**
	 * Final data
	 *
	 * @var	array
	 */
	protected $data = array();


	/**
	 * Number of results
	 *
	 * @var	int
	 */
	protected $numResults = 0;


	/**
	 * Fetch the number of results
	 *
	 * @return	int
	 */
	public function getNumResults()
	{
		return $this->numResults;
	}
}


/**
 * This class is used for datagrids based on array sources
 *
 * @package		html
 * @subpackage	datagrid
 *
 *
 * @author		Davy Hellemans <davy@spoon-library.be>
 * @since		1.0.0
 */
class SpoonDataGridSourceArray extends SpoonDataGridSource
{
	/**
	 * Static ordering (for compare method)
	 *
	 * @var	string
	 */
	public static $order;


	/**
	 * Class constructor.
	 *
	 * @return	void
	 * @param	array $array
	 */
	public function __construct(array $array)
	{
		 // set data
		 $this->data = (array) $array;

		// set number of results
		$this->setNumResults();
	}


	/**
	 * Apply the sorting method
	 *
	 * @return	int
	 * @param	array $firstArray
	 * @param	array $secondArray
	 */
	public static function applySorting($firstArray, $secondArray)
	{
		if($firstArray[self::$order] < $secondArray[self::$order]) return -1;
		elseif($firstArray[self::$order] > $secondArray[self::$order]) return 1;
		else return 0;
	}


	/**
	 * Retrieve the columns
	 *
	 * @return	array
	 */
	public function getColumns()
	{
		if($this->numResults != 0) return array_keys($this->data[0]);
	}


	/**
	 * Fetch the data as an array
	 *
	 * @return	array
	 * @param	int[optional] $offset
	 * @param	int[optional] $limit
	 * @param	string[optional] $order
	 * @param	string[optional] $sort
	 */
	public function getData($offset = null, $limit = null, $order = null, $sort = null)
	{
		// sorting ?
		if($order !== null)
		{
			// static shizzle
			self::$order = $order;

			// apply sorting
			uasort($this->data, array('SpoonDataGridSourceArray', 'applySorting'));

			// reverse if needed?
			if($sort !== null && $sort == 'desc') $this->data = array_reverse($this->data, true);
		}

		// offset & limit
		if($offset !== null && $limit !== null)
		{
			$this->data = array_slice($this->data, $offset, $limit);
		}

		return $this->data;
	}


	/**
	 * Sets the number of results
	 *
	 * @return	void
	 */
	private function setNumResults()
	{
		$this->numResults = (int) count($this->data);
	}
}


/**
 * This class is used for datagrids based on database sources
 *
 * @package		html
 * @subpackage	datagrid
 *
 *
 * @author		Davy Hellemans <davy@spoon-library.be>
 * @since		1.0.0
 */
class SpoonDataGridSourceDB extends SpoonDataGridSource
{
	/**
	 * SpoonDatabase instance
	 *
	 * @var	SpoonDatabase
	 */
	private $db;


	/**
	 * Query to calculate the number of results
	 *
	 * @var	string
	 */
	private $numResultsQuery;


	/**
	 * Custom parameters for the numResults query
	 *
	 * @var	array
	 */
	private $numResultsQueryParameters = array();


	/**
	 * Query to fetch the results
	 *
	 * @var	string
	 */
	private $query;


	/**
	 * Custom parameters for the query
	 *
	 * @var	array
	 */
	private $queryParameters = array();


	/**
	 * Class construtor.
	 *
	 * @return	void
	 * @param	SpoonDatabase $dbConnection
	 * @param	mixed $query
	 * @param	mixed[optional] $numResultsQuery
	 */
	public function __construct(SpoonDatabase $dbConnection, $query, $numResultsQuery = null)
	{
		// database connection
		$this->db = $dbConnection;

		// set queries
		$this->setQuery($query, $numResultsQuery);
	}


	/**
	 * Get the list of columns
	 *
	 * @return	array
	 */
	public function getColumns()
	{
		// has results
		if($this->numResults != 0)
		{
			// build query
			switch($this->db->getDriver())
			{
				case 'mysql':
				case 'mysqli':
					$query = $this->query .' LIMIT 1';
				break;

				default:
					throw new SpoonDataGridException('No datagrid support has been written for this database backend ('. $this->db->getDriver() .')');
				break;
			}

			// fetch record
			$aRecord = $this->db->getRecord($query, $this->queryParameters);

			// fetch columns
			return array_keys($aRecord);
		}
	}


	/**
	 * Fetch the data as an array
	 *
	 * @return	array
	 * @param	int[optional] $offset
	 * @param	int[optional] $limit
	 * @param	string[optional] $order
	 * @param	string[optional] $sort
	 */
	public function getData($offset = null, $limit = null, $order = null, $sort = null)
	{
		// fetch query
		$query = $this->query;

		// order & sort defined
		if($order !== null && $sort !== null) $query .= " ORDER BY $order $sort";

		// offset & limit defined
		if($offset !== null && $limit !== null) $query .= " LIMIT $offset, $limit";

		// fetch data
		return (array) $this->db->getRecords($query, $this->queryParameters);
	}


	/**
	 * Set the number of results
	 *
	 * @return	void
	 */
	private function setNumResults()
	{
		// based on resultsQuery
		if($this->numResultsQuery != '') $this->numResults = (int) $this->db->getVar($this->numResultsQuery, $this->numResultsQueryParameters);

		// based on regular query
		else $this->numResults = (int) $this->db->getNumRows($this->query, $this->queryParameters);
	}


	/**
	 * Set the queries
	 *
	 * @return	void
	 * @param	string $query
	 * @param	string[optional] $numResultsQuery
	 */
	private function setQuery($query, $numResultsQuery = null)
	{
		// query with parameters
		if(is_array($query) && count($query) > 1 && isset($query[0]) && isset($query[1]))
		{
			$this->query = str_replace(';', '', (string) $query[0]);
			$this->queryParameters = (array) $query[1];
		}

		// no paramters
		else $this->query = str_replace(';', '', (string) $query);

		// numResults query with parameters
		if(is_array($numResultsQuery) && count($numResultsQuery) > 1 && isset($numResultsQuery[0]) && isset($numResultsQuery[1]))
		{
			$this->numResultsQuery = str_replace(';', '', (string) $numResultsQuery[0]);
			$this->numResultsQueryParameters = (array) $numResultsQuery[1];
		}

		// no paramters
		else $this->numResultsQuery = (string) $numResultsQuery;

		// set num results
		$this->setNumResults();
	}
}




/**
 * This class is the base class for pagination
 *
 * @package		html
 * @subpackage	datagrid
 *
 *
 * @author		Davy Hellemans <davy@spoon-library.be>
 * @since		1.0.0
 */
class SpoonDataGridPaging
{
	/**
	 * Next label
	 *
	 * @var	string
	 */
	private static $next = 'next';


	/**
	 * Previous label
	 *
	 * @var	string
	 */
	private static $previous = 'previous';


	/**
	 * Builds & returns the pagination
	 *
	 * @return	string
	 * @param	string $url
	 * @param	int $offset
	 * @param	string $order
	 * @param	string $sort
	 * @param	int $numResults
	 * @param	int $numPerPage
	 * @param	bool[optional] $debug
	 * @param	string[optional] $compileDirectory
	 */
	public static function getContent($url, $offset, $order, $sort, $numResults, $numPerPage, $debug = true, $compileDirectory = null)
	{
		// current page
		$iCurrentPage = ceil($offset / $numPerPage) + 1;

		// number of pages
		$iPages = ceil($numResults / $numPerPage);

		// load template
		$tpl = new SpoonTemplate();

		// compile directory
		if($compileDirectory !== null) $tpl->setCompileDirectory($compileDirectory);
		else $tpl->setCompileDirectory(dirname(__FILE__));

		// force compiling
		$tpl->setForceCompile((bool) $debug);

		// previous url
		if($iCurrentPage > 1)
		{
			// label & url
			$previousLabel = self::$previous;
			$previousURL = str_replace(array('[offset]', '[order]', '[sort]'), array(($offset - $numPerPage), $order, $sort), $url);
			$tpl->assign('previousLabel', $previousLabel);
			$tpl->assign('previousURL', $previousURL);
		}

		// next url
		if($iCurrentPage < $iPages)
		{
			// label & url
			$nextLabel = self::$next;
			$nextURL = str_replace(array('[offset]', '[order]', '[sort]'), array(($offset + $numPerPage), $order, $sort), $url);
			$tpl->assign('nextLabel', $nextLabel);
			$tpl->assign('nextURL', $nextURL);
		}

		// limit
		$limit = 7;
		$breakpoint = 4;
		$aItems = array();

		/**
		 * Less than or 7 pages. We know all the keys, and we put them in the array
		 * that we will use to generate the actual pagination.
		 */
		if($iPages <= $limit)
		{
			for($i = 1; $i <= $iPages; $i++) $aItems[$i] = $i;
		}

		// more than 7 pages
		else
		{
			// first page
			if($iCurrentPage == 1)
			{
				// [1] 2 3 4 5 6 7 8 9 10 11 12 13
				for($i = 1; $i <= $limit; $i++) $aItems[$i] = $i;
				$aItems[$limit + 1] = '...';
			}


			// last page
			elseif($iCurrentPage == $iPages)
			{
				// 1 2 3 4 5 6 7 8 9 10 11 12 [13]
				$aItems[$iPages -  $limit - 1] = '...';
				for($i = ($iPages - $limit); $i <= $iPages; $i++) $aItems[$i] = $i;
			}

			// other page
			else
			{
				// 1 2 3 [4] 5 6 7 8 9 10 11 12 13

				// define min & max
				$min = $iCurrentPage - $breakpoint + 1;
				$max = $iCurrentPage + $breakpoint - 1;

				// minimum doesnt exist
				while($min <= 0)
				{
					$min++;
					$max++;
				}

				// maximum doesnt exist
				while($max > $iPages)
				{
					$min--;
					$max--;
				}

				// create the list
				if($min != 1) $aItems[$min - 1] = '...';
				for($i = $min; $i <= $max; $i++) $aItems[$i] = $i;
				if($max != $iPages) $aItems[$max + 1] = '...';
			}
		}

		// init var
		$aPages = array();

		// loop pages
		foreach($aItems as $item)
		{
			// counter
			if(!isset($i)) $i = 0;

			// base details
			$aPages[$i]['page'] = false;
			$aPages[$i]['currentPage'] = false;
			$aPages[$i]['otherPage'] = false;
			$aPages[$i]['noPage'] = false;
			$aPages[$i]['url'] = '';
			$aPages[$i]['pageNumber'] = $item;

			// hellips
			if($item == '...') $aPages[$i]['noPage'] = true;

			// regular page
			else
			{
				// show page
				$aPages[$i]['page'] = true;

				// current page ?
				if($item == $iCurrentPage) $aPages[$i]['currentPage'] = true;

				// other page
				else
				{
					// show the page
					$aPages[$i]['otherPage'] = true;

					// url to this page
					$aPages[$i]['url'] = str_replace(array('[offset]', '[order]', '[sort]'), array((($numPerPage * $item) - $numPerPage), $order, $sort), $url);
				}
			}

			// update counter
			$i++;
		}

		// first key needs to be zero
		$aPages = SpoonFilter::arraySortKeys($aPages);

		// assign pages
		$tpl->assign('pages', $aPages);

		// cough it up
		ob_start();
		$tpl->display(dirname(__FILE__) .'/paging.tpl');
		return ob_get_clean();
	}
}

?>