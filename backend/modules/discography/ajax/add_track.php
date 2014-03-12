<?php

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

/**
 * This add-action will create a new track using Ajax
 *
 * @author Jesse Dobbelaere <jesse@dobbelaere-ae.be>
 */
class BackendDiscographyAjaxAddtrack extends BackendBaseAJAXAction
{
	/**
	 * Execute the action
	 */
	public function execute()
	{
		parent::execute();

		// get parameters
		$trackTitle = trim(SpoonFilter::getPostValue('title', null, '', 'string'));
		$albumId = SpoonFilter::getPostValue('albumId', null, '', 'int');

		// validate
		if($trackTitle === '') $this->output(self::BAD_REQUEST, null, BL::err('TitleIsRequired'));

		// get the data
		// build array
		$track['album_id'] = $albumId;
		$track['title'] = SpoonFilter::htmlspecialchars($trackTitle);
		//$track['duration'] =
		//$track['link_youtube'] =
		$track['sequence'] = BackendDiscographyModel::getMaxTrackSequence($albumId) + 1;

		// insert
		$track['id'] = BackendDiscographyModel::insertTrack($track);

		// output
		$this->output(self::OK, $track, vsprintf(BL::msg('AddedCategory'), array($track['title'])));
	}
}
