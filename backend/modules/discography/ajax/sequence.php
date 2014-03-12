<?php

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

/**
 * This is an ajax handler
 *
 * @author Jesse Dobbelaere <jesse@dobbelaere-ae.be>
 */
class BackendDiscographyAjaxSequence extends BackendBaseAJAXAction
{
	/**
	 * Execute the action
	 */
	public function execute()
	{
		// call parent, this will probably add some general CSS/JS or other required files
		parent::execute();

		// get parameters
		$newIdSequence = trim(SpoonFilter::getPostValue('new_id_sequence', null, '', 'string'));

		// list id
		$ids = (array) explode(',', rtrim($newIdSequence, ','));
		$ids = array_reverse($ids);
		$count = count($ids);

		// loop id's and set new sequence
		foreach($ids as $i => $id)
		{
			// build item
			$item['id'] = (int) $id;

			// change sequence
			$item['sequence'] = $count--;

			// update sequence
			if(BackendDiscographyModel::existsTrack($item['id'])) BackendDiscographyModel::updateTrack($item);
		}

		// success output
		$this->output(self::OK, null, 'sequence updated');
	}
}
