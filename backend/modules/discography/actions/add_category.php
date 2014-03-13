<?php

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

/**
 * This is the add-action, it will display a form to create a new item
 *
 * @author Jesse Dobbelaere <jesse@dobbelaere-ae.be>
 */
class BackendDiscographyAddCategory extends BackendBaseActionAdd
{
	/**
	 * Execute the actions
	 */
	public function execute()
	{
		parent::execute();

		$this->loadForm();
		$this->validateForm();

		$this->parse();
		$this->display();
	}

	/**
	 * Load the form
	 */
	private function loadForm()
	{
		$this->frm = new BackendForm('addCategory');
		$this->frm->addText('title', null, 255, 'inputText title', 'inputTextError title');

		// meta
		$this->meta = new BackendMeta($this->frm, null, 'title', true);

		// set callback for generating an unique URL
		$this->meta->setURLCallback('BackendDiscographyModel', 'getURLForCategory');
	}

	/**
	 * Parse the page
	 */
//	private function parse()
//	{
//		parent::parse();
//
//		// assign the url for the detail page
//		$url = BackendModel::getURLForBlock($this->URL->getModule(), 'detail');
//		$url404 = BackendModel::getURL(404);
//		if($url404 != $url) $this->tpl->assign('detailURL', SITE_URL . $url);
//	}

	/**
	 * Validate the form
	 */
	private function validateForm()
	{
		if($this->frm->isSubmitted())
		{
			// cleanup the submitted fields, ignore fields that were added by hackers
			$this->frm->cleanupFields();

			// validation
			$this->frm->getField('title')->isFilled(BL::err('TitleIsRequired'));

			// validate meta
			$this->meta->validate();

			// no errors?
			if($this->frm->isCorrect())
			{
				$item['title'] = $this->frm->getField('title')->getValue();
				$item['language'] = BL::getWorkingLanguage();
				$item['meta_id'] = $this->meta->save();

				// insert the category
				$item['id'] = BackendDiscographyModel::insertCategory($item);

				BackendSearchModel::saveIndex(
					$this->getModule(),
					$item['id'],
					array('title' => $item['title'], 'text' => $item['title'])
				);

				// trigger event
				BackendModel::triggerEvent(
					$this->getModule(), 'after_add_category', $item
				);

				// everything is saved, so redirect to the overview
				$this->redirect(BackendModel::createURLForAction('categories') . '&report=added-category&var=' . urlencode($item['title']) . '&highlight=row-' . $item['id']);
			}
		}
	}
}
