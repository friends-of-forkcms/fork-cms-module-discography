<?php

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

/**
 * This is the Detail-action, it will display the overview of discography posts
 *
 * @author Jesse Dobbelaere <jesse@dobbelaere-ae.be>
 */
class FrontendDiscographyDetail extends FrontendBaseBlock
{
	/**
	 * The album data
	 *
	 * @var array
	 */
	private $record;

	/**
	 * Execute the action
	 */
	public function execute()
	{
		parent::execute();

		$this->loadTemplate();
		$this->loadData();
		$this->parse();
	}

	/**
	 * Load the data
	 */
	protected function loadData()
	{
		$albumUrl = ($this->URL->getParameter(1) === null) ? $this->URL->getParameter(0) : $this->URL->getParameter(1);
		$albumId = ($albumUrl === null) ? 0 : FrontendDiscographyModel::getIdForUrl($albumUrl);
		if($albumId == 0) $this->redirect(FrontendNavigation::getURL(404));

		$this->record = FrontendDiscographyModel::getDataForId($albumId);
	}

	/**
	 * Parse the page
	 */
	protected function parse()
	{
		$this->tpl->assign('album', $this->record);
	}
}
