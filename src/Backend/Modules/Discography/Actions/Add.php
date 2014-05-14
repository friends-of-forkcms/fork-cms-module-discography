<?php

namespace Backend\Modules\Discography\Actions;

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

use Backend\Core\Engine\Base\ActionAdd as BackendBaseActionAdd;
use Backend\Core\Engine\Model as BackendModel;
use Backend\Modules\Discography\Engine\Model as BackendDiscographyModel;
use Backend\Core\Engine\Form as BackendForm;
use Backend\Core\Engine\Language as BL;
use Backend\Core\Engine\Meta as BackendMeta;
use Backend\Modules\Search\Engine\Model as BackendSearchModel;

/**
 * This is the add-action, it will display a form to create a new item
 *
 * @author Jesse Dobbelaere <jesse@dobbelaere-ae.be>
 */
class Add extends BackendBaseActionAdd
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
	protected function loadForm()
	{
		// create form
		$this->frm = new BackendForm('add');
		$this->frm->addText('title', null, null, 'inputText title', 'inputTextError title');

		// load meta
		$this->meta = new BackendMeta($this->frm, null, 'title', true);
	}

	/**
	 * Parse the page
	 */
	protected function parse()
	{
		parent::parse();

		// assign the url for the detail page
		$url = BackendModel::getURLForBlock($this->URL->getModule(), 'detail');
		$url404 = BackendModel::getURL(404);
		if($url404 != $url) $this->tpl->assign('detailURL', SITE_URL . $url);
	}

	/**
	 * Validate the form
	 */
	protected function validateForm()
	{
		if($this->frm->isSubmitted()) {
			$this->frm->cleanupFields();

			// validation
			$fields = $this->frm->getFields();
			$fields['title']->isFilled(BL::err('TitleIsRequired'));
			$this->meta->validate();

			if($this->frm->isCorrect()) {
				$item['meta_id'] = $this->meta->save();
				$item['title'] = $fields['title']->getValue();

				$item['id'] = BackendDiscographyModel::insert($item);

				BackendSearchModel::saveIndex(
					$this->getModule(),
					$item['id'],
					array('title' => $item['title'], 'text' => $item['title'])
				);

				// everything is saved, so redirect
				$this->redirect(BackendModel::createURLForAction('AddInfo') . '&id=' . $item['id']);
			}
		}
	}
}
