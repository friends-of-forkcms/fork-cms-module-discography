<?php

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

/**
 * This is the edit-action, it will display a form with the item data to edit
 *
 * @author Jesse Dobbelaere <jesse@dobbelaere-ae.be>
 */
class BackendDiscographyEditCategory extends BackendBaseActionEdit
{
	/**
	 * Execute the action
	 */
	public function execute()
	{
		parent::execute();

		$this->loadData();
		$this->loadForm();
		$this->validateForm();

		$this->parse();
		$this->display();
	}

	/**
	 * Load the item data
	 */
	protected function loadData()
	{
		// get parameters
		$this->id = $this->getParameter('id', 'int');

		// does the item exists
		if($this->id == null || !BackendDiscographyModel::existsCategory($this->id))
		{
			// no item found, throw an exception, because somebody is fucking with our URL
			$this->redirect(BackendModel::createURLForAction('index') . '&error=non-existing');
		}

		$this->record = BackendDiscographyModel::getCategory($this->id);
	}

	/**
	 * Load the form
	 */
	protected function loadForm()
	{
		// create form
		$this->frm = new BackendForm('edit');
		$this->frm->addText('title', $this->record['title'], 255, 'inputText title', 'inputTextError title');
	}

	/**
	 * Parse the page
	 */
	protected function parse()
	{
		parent::parse();

		$this->tpl->assign('item', $this->record);

		// delete allowed?
		$this->tpl->assign('showDiscographyDeleteCategory', BackendDiscographyModel::deleteCategoryAllowed($this->id) && BackendModel::createURLForAction('delete_category'));
	}

	/**
	 * Validate the form
	 */
	protected function validateForm()
	{
		if($this->frm->isSubmitted())
		{
			$this->frm->cleanupFields();

			// validation
			$fields = $this->frm->getFields();
			$fields['title']->isFilled(BL::err('TitleIsRequired'));

			if($this->frm->isCorrect())
			{
				$item['id'] = $this->id;
				$item['title'] = $fields['title']->getValue();

				BackendDiscographyModel::updateCategory($item);

				BackendModel::triggerEvent(
					$this->getModule(), 'after_edit_category', $item
				);

				// everything is saved, so redirect to the overview
				$this->redirect(BackendModel::createURLForAction('categories') . '&report=edited-category&var=' . urlencode($item['title']) . '&highlight=row-' . $item['id']);
			}
		}
	}
}
