<?php

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

/**
 * This is the index-action (default), it will display the overview of discography posts
 *
 * @author Jesse Dobbelaere <jesse@dobbelaere-ae.be>
 */
class BackendDiscographyIndex extends BackendBaseActionIndex
{
	/**
	 * Execute the action
	 */
	public function execute()
	{
		parent::execute();
		$this->loadDataGrid();
		$this->parse();
		$this->display();
	}

	/**
	 * Load the dataGrid
	 */
	protected function loadDataGrid()
	{
		// create datagrid
		$this->dataGrid = new BackendDataGridDB(BackendDiscographyModel::QRY_DATAGRID_BROWSE);

		// set headers
		$this->dataGrid->setHeaderLabels(array('category' => SpoonFilter::ucfirst(BL::lbl('Category'))));
		$this->dataGrid->setHeaderLabels(array('date' => SpoonFilter::ucfirst(BL::lbl('ReleaseDate'))));

		// add columns
		$this->dataGrid->addColumn('cover', SpoonFilter::ucfirst(BL::lbl('Cover')));

		// linkify the title column to the edit page
		$this->dataGrid->setColumnURL('title', BackendModel::createURLForAction('edit') . '&amp;id=[id]');
		$this->dataGrid->setColumnURL('cover', BackendModel::createURLForAction('edit') . '&amp;id=[id]');

		// set columns functions
		$this->dataGrid->setColumnFunction(array('BackendDiscographyModel', 'getCoverImageThumb'), array('[id]', $this->getModule()), 'cover', true);
		$this->dataGrid->setColumnFunction(array('BackendDiscographyModel', 'getMonthYearDate'), array('[date]'), 'date', true);

		// sorting columns
		$this->dataGrid->setSortingColumns(array('title', 'date', 'category'), 'date');
		$this->dataGrid->setSortParameter('desc');

		// check if this action is allowed
		if(BackendAuthentication::isAllowedAction('edit'))
		{
			$this->dataGrid->addColumn(
				'edit', null, BL::lbl('Edit'),
				BackendModel::createURLForAction('edit') . '&amp;id=[id]',
				BL::lbl('Edit')
			);
		}

		// Set sequence of the columns
		$this->dataGrid->setColumnsSequence(array('cover','date','title','category','edit'));
	}

	/**
	 * Parse the page
	 */
	protected function parse()
	{
		// parse the dataGrid if there are results
		$this->tpl->assign(
			'dataGrid',
			($this->dataGrid->getNumResults() != 0) ? $this->dataGrid->getContent() : false
		);
	}
}
