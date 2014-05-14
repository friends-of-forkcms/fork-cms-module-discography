<?php

namespace Backend\Modules\Discography\Actions;

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

use Backend\Core\Engine\Base\ActionDelete as BackendBaseActionDelete;
use Backend\Core\Engine\Model as BackendModel;
use Backend\Modules\Discography\Engine\Model as BackendDiscographyModel;

/**
 * This is the delete-action, it deletes an item
 *
 * @author Jesse Dobbelaere <jesse@dobbelaere-ae.be>
 */
class DeleteCategory extends BackendBaseActionDelete
{
    /**
     * Execute the action
     */
    public function execute()
    {
        $this->id = $this->getParameter('id', 'int');

        // does the item exist
        if($this->id !== null && BackendDiscographyModel::existsCategory($this->id)) {
            // get data
            $this->record = (array) BackendDiscographyModel::getCategory($this->id);

            // allowed to delete the category?
            if(BackendDiscographyModel::deleteCategoryAllowed($this->id)) {
                // call parent, this will probably add some general CSS/JS or other required files
                parent::execute();

                // delete item
                BackendDiscographyModel::deleteCategory($this->id);

                // trigger event
                BackendModel::triggerEvent($this->getModule(), 'after_delete_category', array('id' => $this->id));

                // category was deleted, so redirect
                $this->redirect(BackendModel::createURLForAction('Categories') . '&report=deleted-category&var=' . urlencode($this->record['title']));
            }


            // delete category not allowed
            else $this->redirect(BackendModel::createURLForAction('Categories') . '&error=delete-category-not-allowed&var=' . urlencode($this->record['title']));
        }

        // something went wrong
        else $this->redirect(BackendModel::createURLForAction('Categories') . '&error=non-existing');
    }
}
