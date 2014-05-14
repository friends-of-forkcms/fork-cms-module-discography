<?php

namespace Frontend\Modules\Discography\Actions;

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

use Frontend\Core\Engine\Base\Block as FrontendBaseBlock;
use Frontend\Core\Engine\Navigation as FrontendNavigation;
use Frontend\Modules\Discography\Engine\Model as FrontendDiscographyModel;

/**
 * This is the Detail-action, it will display the overview of discography posts
 *
 * @author Jesse Dobbelaere <jesse@dobbelaere-ae.be>
 */
class Detail extends FrontendBaseBlock
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

        $this->record = FrontendDiscographyModel::getById($albumId);
    }

    /**
     * Parse the page
     */
    protected function parse()
    {
        $this->tpl->assign('album', $this->record);
        $this->tpl->assign('discographyUrl', FrontendNavigation::getURLForBlock('Discography'));

        // Set meta
        $this->header->setPageTitle($this->record['meta']['title'], ($this->record['meta']['title_overwrite'] == 'Y'));
        $this->header->addMetaDescription($this->record['meta']['description'], ($this->record['meta']['description_overwrite'] == 'Y'));
        $this->header->addMetaKeywords($this->record['meta']['keywords'], ($this->record['meta']['title_overwrite'] == 'Y'));
    }
}
