<?php

namespace Backend\Modules\Discography\Installer;

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

use Backend\Core\Installer\ModuleInstaller;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Installer for the discography module
 *
 * @author Jesse Dobbelaere <jesse@dobbelaere-ae.be>
 */
class Installer extends ModuleInstaller
{
    /**
     * Install the module
     */
    public function install()
    {
        // import install.sql
        $this->importSQL(dirname(__FILE__) . '/Data/install.sql');

        // add 'discography' as a module
        $this->addModule('Discography');

        // install the locale
        $this->importLocale(dirname(__FILE__) . '/Data/locale.xml');

        // make searchable
        $this->makeSearchable('Discography');

        // module rights
        $this->setModuleRights(1, 'Discography');

        // action rights
        $this->setActionRights(1, 'Discography', 'Add');
        $this->setActionRights(1, 'Discography', 'Edit');
        $this->setActionRights(1, 'Discography', 'Delete');
        $this->setActionRights(1, 'Discography', 'AddCategory');
        $this->setActionRights(1, 'Discography', 'EditCategory');
        $this->setActionRights(1, 'Discography', 'Detail');
        $this->setActionRights(1, 'Discography', 'Categories');
        $this->setActionRights(1, 'Discography', 'DeleteCategory');
        $this->setActionRights(1, 'Discography', 'AddInfo');
        $this->setActionRights(1, 'Discography', 'Index');

        // add module block
        $discographyID = $this->insertExtra('Discography', 'block', 'Discography', null, null, 'N', 1000);

        // set navigation
        $navigationModulesId = $this->setNavigation(null, 'Modules');
        $navigationDiscographyId = $this->setNavigation($navigationModulesId, 'Discography');
        $this->setNavigation(
            $navigationDiscographyId,
            'Albums',
            'discography/index',
            array(
                'discography/add',
                'discography/add_info',
                'discography/edit'
        ));
        $this->setNavigation(
            $navigationDiscographyId,
            'Categories',
            'discography/categories',
            array(
                'discography/add_category',
                'discography/edit_category'
            )
        );

        // install example categories
        $this->installCategories();

        // insert discography page
        $this->insertDiscographyPage('Discography', $discographyID);

        // create folders if needed
        $imagePath = FRONTEND_FILES_PATH . '/Discography/images';
        $fs = new Filesystem();
        if(!$fs->exists($imagePath . '/source')) $fs->mkdir($imagePath . '/source');
        if(!$fs->exists($imagePath . '/50x50')) $fs->mkdir($imagePath . '/50x50');
        if(!$fs->exists($imagePath . '/128x128')) $fs->mkdir($imagePath . '/128x128');
        if(!$fs->exists($imagePath . '/150x150')) $fs->mkdir($imagePath . '/150x150');
        if(!$fs->exists($imagePath . '/300x300')) $fs->mkdir($imagePath . '/300x300');

        // copy placeholder image
        $fs->copy(dirname(__FILE__) . '/Data/placeholder50x50.png', FRONTEND_FILES_PATH . '/Discography/images/50x50/placeholder.png');
        $fs->copy(dirname(__FILE__) . '/Data/placeholder128x128.png', FRONTEND_FILES_PATH . '/Discography/images/128x128/placeholder.png');
        $fs->copy(dirname(__FILE__) . '/Data/placeholder150x150.png', FRONTEND_FILES_PATH . '/Discography/images/150x150/placeholder.png');
        $fs->copy(dirname(__FILE__) . '/Data/placeholder300x300.png', FRONTEND_FILES_PATH . '/Discography/images/300x300/placeholder.png');
    }

    /**
     * Install some elementary categories
     */
    private function installCategories() {
        // get db instance
        $db = $this->getDB();

        // insert default category for every language
        foreach($this->getLanguages() as $language) {
            $db->insert('discography_categories', array(
                'id' => NULL,
                'meta_id' => 0,
                'language' => $language,
                'title' => 'Albums'
            ));

            $db->insert('discography_categories', array(
                'id' => NULL,
                'meta_id' => 0,
                'language' => $language,
                'title' => 'EP\'s'
            ));
        }
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
        foreach($this->getLanguages() as $language) {

            // check if a page for discography already exists in this language
            $pageExists = (bool) $this->getDB()->getVar('SELECT COUNT(p.id)
                                                FROM pages AS p
                                                INNER JOIN pages_blocks AS b ON b.revision_id = p.revision_id
                                                WHERE b.extra_id = ? AND p.language = ?',
                                                array($extraId, $language));
            if(!$pageExists) {
                $this->insertPage(
                    array('title' =>  $title, 'language' => $language, 'type' => 'root'),
                    null,
                    array('extra_id' => $extraId, 'position' => 'main')
                );
            }
        }
    }
}
