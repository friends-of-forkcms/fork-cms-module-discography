<?php

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

/**
 * Installer for the discography module
 *
 * @author Jesse Dobbelaere <jesse@dobbelaere-ae.be>
 */
class DiscographyInstaller extends ModuleInstaller
{
	public function install()
	{
		// import install.sql
		$this->importSQL(dirname(__FILE__) . '/data/install.sql');

		// add 'discography' as a module
		$this->addModule('discography');

		// install the locale
		$this->importLocale(dirname(__FILE__) . '/data/locale.xml');

		// make searchable
		$this->makeSearchable('discography');

		// module rights
		$this->setModuleRights(1, 'discography');

		// action rights
		$this->setActionRights(1, 'discography', 'add');
		$this->setActionRights(1, 'discography', 'edit');
		$this->setActionRights(1, 'discography', 'delete');
		$this->setActionRights(1, 'discography', 'add_category');
		$this->setActionRights(1, 'discography', 'edit_category');
		$this->setActionRights(1, 'discography', 'detail');
		$this->setActionRights(1, 'discography', 'categories');
		$this->setActionRights(1, 'discography', 'delete_category');
		$this->setActionRights(1, 'discography', 'add_info');
		$this->setActionRights(1, 'discography', 'index');

		// add module block
		$discographyID = $this->insertExtra('discography', 'block', 'Discography', null, null, 'N', 1000);

		// set navigation
		$navigationModulesId = $this->setNavigation(null, 'Modules');
		$navigationDiscographyId = $this->setNavigation($navigationModulesId, 'Discography');
		$this->setNavigation($navigationDiscographyId, 'Albums', 'discography/index',
			array(
				'discography/add',
				'discography/add_info',
				'discography/edit'
		));
		$this->setNavigation($navigationDiscographyId, 'Categories', 'discography/categories', array('discography/add_category', 'discography/edit_category'));

		// install example categories
		$this->installCategories();

		// insert discography page
		$this->insertDiscographyPage('Discography', $discographyID);

		// create folders if needed
		$imagePath = FRONTEND_FILES_PATH . '/discography/images';
		if(!SpoonDirectory::exists($imagePath . '/source')) SpoonDirectory::create($imagePath . '/source');
		if(!SpoonDirectory::exists($imagePath . '/50x50')) SpoonDirectory::create($imagePath . '/50x50');
		if(!SpoonDirectory::exists($imagePath . '/128x128')) SpoonDirectory::create($imagePath . '/128x128');
		if(!SpoonDirectory::exists($imagePath . '/150x150')) SpoonDirectory::create($imagePath . '/150x150');
		if(!SpoonDirectory::exists($imagePath . '/300x300')) SpoonDirectory::create($imagePath . '/300x300');

		// copy placeholder image
		SpoonDirectory::copy(PATH_WWW . '/backend/modules/discography/installer/data/placeholder50x50.png', FRONTEND_FILES_PATH . '/discography/images/50x50/placeholder.png');
		SpoonDirectory::copy(PATH_WWW . '/backend/modules/discography/installer/data/placeholder128x128.png', FRONTEND_FILES_PATH . '/discography/images/128x128/placeholder.png');
		SpoonDirectory::copy(PATH_WWW . '/backend/modules/discography/installer/data/placeholder150x150.png', FRONTEND_FILES_PATH . '/discography/images/150x150/placeholder.png');
		SpoonDirectory::copy(PATH_WWW . '/backend/modules/discography/installer/data/placeholder300x300.png', FRONTEND_FILES_PATH . '/discography/images/300x300/placeholder.png');
	}

	/**
	 * Install some elementary categories
	 */
	private function installCategories()
	{
		// get db instance
		$db = $this->getDB();

		$db->insert('discography_categories', array(
			'id' => NULL,
			'meta_id' => 0,
			'language' => 'en',
			'title' => 'Albums'
		));

		$db->insert('discography_categories', array(
			'id' => NULL,
			'meta_id' => 0,
			'language' => 'en',
			'title' => 'EP\'s'
		));
	}

	/**
	 * Insert the discography page
	 *
	 * @param $title string The page title
	 * @param $extraId int The block Id
	 */
	private function insertDiscographyPage($title, $extraId)
	{
		// loop languages
		foreach($this->getLanguages() as $language)
		{

			// check if a page for discography already exists in this language
			if(!(bool) $this->getDB()->getVar('SELECT COUNT(p.id)
												FROM pages AS p
												INNER JOIN pages_blocks AS b ON b.revision_id = p.revision_id
												WHERE b.extra_id = ? AND p.language = ?',
												array($extraId, $language)))
			{
				$this->insertPage(
					array('title' =>  $title, 'language' => $language, 'type' => 'root'),
					null,
					array('extra_id' => $extraId, 'position' => 'main')
				);
			}
		}
	}
}
