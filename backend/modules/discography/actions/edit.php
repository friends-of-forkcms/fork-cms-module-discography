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
class BackendDiscographyEdit extends BackendBaseActionEdit
{
	/**
	 * List of database album tracks
	 *
	 * @var array
	 */
	private $tracks = array();

	/**
	 * List of the current datagrid tracks
	 *
	 * @var array
	 */
	private $dgTracks = array();

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
		// Check parameters
		$this->id = $this->getParameter('id', 'int', null);
		if($this->id == null || !BackendDiscographyModel::exists($this->id))
		{
			$this->redirect(
				BackendModel::createURLForAction('index') . '&error=non-existing'
			);
		}

		// Get data
		$this->record = BackendDiscographyModel::get($this->id);

		// Get tracks
		$this->tracks = (array) BackendDiscographyModel::getTracks($this->id);

		// no item found, throw an exception, because somebody is fucking with our URL
		if(empty($this->record)) $this->redirect(BackendModel::createURLForAction('index') . '&error=non-existing');
	}

	/**
	 * Load the form
	 */
	protected function loadForm()
	{
		// create form
		$this->frm = new BackendForm('edit');

		// set hidden values
		$rbtHiddenValues[] = array('label' => BL::lbl('Hidden'), 'value' => 'Y');
		$rbtHiddenValues[] = array('label' => BL::lbl('Published'), 'value' => 'N');

		// use POST values to rebuild the tracks
		$tracks = array();
		if($this->frm->isSubmitted())
		{
			if(isset($_POST['tracks']) && is_array($_POST['tracks']))
			{
				foreach($_POST['tracks'] as $track)
				{
					$chunks = explode(':::::', $track);
					if(count($chunks) == 3)
					{
						$tracks[] = array(
							'id' => ($chunks[0] == '') ? '' : (int) $chunks[0],
							'track' => (string) $chunks[1],
							'duration' => (string) $chunks[2],
						);
					}
				}
			}
			$this->newTracks = $tracks;
		}
		// not yet submitted so use database values
		else $tracks = $this->tracks;
		$this->tpl->assign('tracks', json_encode($tracks));

		// get categories
		$categories = BackendDiscographyModel::getCategories();

		// create elements
		$this->frm->addText('title', $this->record['title'], null, 'inputText title', 'inputTextError title');
		$this->frm->addRadiobutton('hidden', $rbtHiddenValues, $this->record['hidden']);
		$this->frm->addDate('release_date', $this->record['date']);
		$this->frm->addDropdown('category_id', $categories, $this->record['category_id']);
		if(count($categories) != 2) $this->frm->getField('category_id')->setDefaultElement('');
		$this->frm->addImage('image');
		$this->frm->addCheckbox('delete_image');
		$this->frm->addText('track')->setAttributes(array('class' => 'inputText', 'style' => 'width: 300px'));
		$this->frm->addText('duration')->setAttributes(array('class' => 'inputText', 'type' => 'time'));
		$this->frm->addHidden('dummy_tracks');

		// load meta
		$this->meta = new BackendMeta($this->frm, $this->record['meta_id'], 'title', true );
		$this->meta->setUrlCallback('BackendDiscographyModel', 'getUrl', array($this->record['id']));
	}

	/**
	 * Parse the page
	 */
	protected function parse()
	{
		parent::parse();
		$this->tpl->assign('item', $this->record);

		// get url
		$url = BackendModel::getURLForBlock($this->URL->getModule(), 'detail');
		$url404 = BackendModel::getURL(404);
		if($url404 != $url) $this->tpl->assign('detailURL', SITE_URL . $url);

		// fetch proper slug
		$this->record['url'] = $this->meta->getURL();

		// assign the active record and additional variables
		$this->tpl->assign('item', $this->record);
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
			$fields['title']->isFilled(BL::err('FieldIsRequired'));
			$fields['release_date']->isFilled(BL::err('FieldIsRequired'));
			$this->meta->validate();

			// not enough tracks
			if(count($this->newTracks) == 0)
			{
				$this->tpl->assign('noTracks', true);
				$this->frm->addError('noTracks');
			}

			// no errors?
			if($this->frm->isCorrect())
			{
				$item['meta_id'] = $this->meta->save(true);
				$item['title'] = $fields['title']->getValue();
				$item['hidden'] = $fields['hidden']->getValue();
				$item['release_date'] = date('Y-m-d', strtotime(str_replace('/', '-', $fields['release_date']->getValue())));
				$item['category_id'] = $fields['category_id']->getValue();

				// add image
				$item['image'] = $this->record['image'];
				$imagePath = FRONTEND_FILES_PATH . '/discography/images';

				// create folders if needed
				if(!SpoonDirectory::exists($imagePath . '/source')) SpoonDirectory::create($imagePath . '/source');
				if(!SpoonDirectory::exists($imagePath . '/50x50')) SpoonDirectory::create($imagePath . '/50x50');
				if(!SpoonDirectory::exists($imagePath . '/128x128')) SpoonDirectory::create($imagePath . '/128x128');
				if(!SpoonDirectory::exists($imagePath . '/150x150')) SpoonDirectory::create($imagePath . '/150x150');
				if(!SpoonDirectory::exists($imagePath . '/300x300')) SpoonDirectory::create($imagePath . '/300x300');

				// if the image should be deleted
				if($this->frm->getField('delete_image')->isChecked())
				{
					// delete the image
					SpoonFile::delete($imagePath . '/source/' . $item['image']);

					// reset the name
					$item['image'] = null;
				}

				// new image given?
				if($this->frm->getField('image')->isFilled())
				{
					// delete the old image
					SpoonFile::delete($imagePath . '/source/' . $this->record['image']);

					// build the image name
					$item['image'] = $this->meta->getURL() . '.' . $this->frm->getField('image')->getExtension();

					// upload the image & generate thumbnails
					$this->frm->getField('image')->generateThumbnails($imagePath, $item['image']);
				}

				// rename the old image
				elseif($item['image'] != null)
				{
					// get the old file extension
					$imageExtension = SpoonFile::getExtension($imagePath . '/source/' . $item['image']);

					// get the new image name
					$newName = $this->meta->getURL() . '.' . $imageExtension;

					// only change the name if there is a difference
					if($newName != $item['image'])
					{
						// loop folders
						foreach(BackendModel::getThumbnailFolders($imagePath, true) as $folder)
						{
							// move the old file to the new name
							SpoonFile::move($folder['path'] . '/' . $item['image'], $folder['path'] . '/' . $newName);
						}

						// assign the new name to the database
						$item['image'] = $newName;
					}
				}


				BackendDiscographyModel::update($this->id, $item);
				$item['id'] = $this->id;

				BackendSearchModel::saveIndex(
					$this->getModule(),
					$item['id'],
					array('title' => $item['title'], 'text' => $item['title'])
				);

				// usable array with tracks
				$tracks = $this->newTracks;

				// not enough tracks
				if(count($tracks) == 0)
				{
					$this->tpl->assign('noTracks', true);
					$this->frm->addError('noTracks');
				}

				// tracks that got updated, used for checking which items to delete
				$updatedTracks = array();

				// update/insert the tracks
				foreach($tracks as $i => $track)
				{
					// update existing track
					if(!empty($track['id']))
					{
						BackendDiscographyModel::updateTrack(
							array(
								'id' => $track['id'],
								'title' => $track['track'],
								'duration' => $track['duration'],
								'sequence' => $i + 1
							)
						);

						$updatedTracks[] = $track['id'];
					}

					// new track
					else
					{
						BackendDiscographyModel::insertTrack(
							array(
								'album_id' => $this->id,
								'title' => $track['track'],
								'duration' => $track['duration'],
								'sequence' => $i + 1
							)
						);
					}
				}

				// delete removed tracks
				foreach($this->tracks as $track)
				{
					// not in list of updated answers so delete
					if(!in_array($track['id'], $updatedTracks))
					{
						BackendDiscographyModel::deleteTrack($track['id']);
					}
				}

				BackendModel::triggerEvent(
					$this->getModule(), 'after_edit', $item
				);
				$this->redirect(
					BackendModel::createURLForAction('index') . '&report=edited&highlight=row-' . $item['id']
				);
			}
		}
	}
}
