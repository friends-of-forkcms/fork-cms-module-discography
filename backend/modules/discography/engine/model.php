<?php

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

/**
 * In this file we store all generic functions that we will be using in the discography module
 *
 * @author Jesse Dobbelaere <jesse@dobbelaere-ae.be>
 */
class BackendDiscographyModel
{

	const QRY_DATAGRID_BROWSE =
		'SELECT e.id, UNIX_TIMESTAMP(e.release_date) as date, e.title, c.title as category, e.hidden
		FROM discography_albums AS e
		LEFT OUTER JOIN discography_categories as c ON e.category_id = c.id';

	const QRY_DATAGRID_BROWSE_CATEGORIES =
		'SELECT c.id, c.title, COUNT(a.id) AS num_items
		FROM discography_categories AS c
		LEFT OUTER JOIN discography_albums AS a ON c.id = a.category_id
		GROUP BY c.id';

	/**
	 * Delete a certain item
	 *
	 * @param int $id
	 */
	public static function delete($id)
	{
		BackendModel::getContainer()->get('database')->delete('discography_albums', 'id = ?', (int) $id);
	}

	/**
	 * Checks if a certain album exists
	 *
	 * @param int $id The id of the album to check for existence.
	 * @return bool
	 */
	public static function exists($id)
	{
		return (bool) BackendModel::getContainer()->get('database')->getVar(
			'SELECT 1
			 FROM discography_albums AS i
			 WHERE i.id = ?
			 LIMIT 1',
			array((int) $id)
		);
	}

	/**
	 * Checks if a category exists
	 *
	 * @param int $id The id of the category to check for existence.
	 * @return int
	 */
	public static function existsCategory($id)
	{
		return (bool) BackendModel::getContainer()->get('database')->getVar(
			'SELECT 1
			 FROM discography_categories AS i
			 WHERE i.id = ? AND i.language = ?
			 LIMIT 1',
			array((int) $id, BL::getWorkingLanguage())
		);
	}

	/**
	 * Checks if a certain track exists
	 *
	 * @param int $id The id of the track to check for existence.
	 * @return bool
	 */
	public static function existsTrack($id)
	{
		return (bool) BackendModel::getContainer()->get('database')->getVar(
			'SELECT 1
			 FROM discography_albums_tracks AS i
			 WHERE i.id = ?
			 LIMIT 1',
			array((int) $id)
		);
	}

	/**
	 * Fetches a certain item
	 *
	 * @param int $id
	 * @return array
	 */
	public static function get($id)
	{
		return (array) BackendModel::getContainer()->get('database')->getRecord(
			'SELECT i.*, UNIX_TIMESTAMP(i.release_date) AS date
			 FROM discography_albums AS i
			 WHERE i.id = ?',
			array((int) $id)
		);
	}

	/**
	 * Get all data for a given id
	 *
	 * @param int $id The id of the category to fetch.
	 * @return array
	 */
	public static function getCategory($id)
	{
		return (array) BackendModel::getContainer()->get('database')->getRecord(
			'SELECT i.*
			 FROM discography_categories AS i
			 WHERE i.id = ? AND i.language = ?',
			array((int) $id, BL::getWorkingLanguage())
		);
	}

	/**
	 * Get all categories
	 *
	 * @param bool[optional] $includeCount Include the count?
	 * @return array
	 */
	public static function getCategories($includeCount = false)
	{
		$db = BackendModel::getContainer()->get('database');

		if($includeCount)
		{
			return (array) $db->getPairs(
				'SELECT i.id, CONCAT(i.title, " (", COUNT(p.category_id) ,")") AS title
				 FROM discography_categories AS i
				 LEFT OUTER JOIN discography_albums AS p ON i.id = p.category_id AND p.status = ?
				 WHERE i.language = ?
				 GROUP BY i.id',
				array('active')
			);
		}

		return (array) $db->getPairs(
			'SELECT i.id, i.title
			 FROM discography_categories AS i'
		);
	}


	/**
	 * Get album tracks
	 *
	 * @param $id
	 * @return array
	 */
	public static function getTracks($id)
	{
		return (array) BackendModel::getContainer()->get('database')->getRecords(
			'SELECT t.id, t.sequence, t.title, t.duration, t.hidden
			FROM discography_albums_tracks AS t
			WHERE t.album_id = ?
			ORDER BY t.sequence',
			array($id)
		);
	}

	/**
	 * Get the highest sequence nr of the albumtracks
	 *
	 * @param $albumId int The id of a specific album
	 * @return int The highest sequence number
	 */
	public static function getMaxTrackSequence($albumId)
	{
		return (int) BackendModel::getContainer()->get('database')->getVar(
			'SELECT COUNT(*)
			FROM discography_albums_tracks
			WHERE album_id = ?',
			array($albumId)
		);
	}

	public static function getCoverImageThumb($albumId, $module)
	{
		$imageFilename = (string) BackendModel::getContainer()->get('database')->getVar(
			'SELECT e.image
			FROM discography_albums AS e
			WHERE id = ?
			LIMIT 1',
			array((int) $albumId)
		);

		// If the album has no image, set a placeholder image
		if(empty($imageFilename)) {
			$image = '/' . $module . '/images/50x50/placeholder.png';
			if(SpoonFile::exists(FRONTEND_FILES_PATH . $image)) {
				return '<img src="' . FRONTEND_FILES_URL . $image . '" width="50" height="50" />';
			}
			return '';
		}

		$image = FRONTEND_FILES_URL . '/' . $module . '/images/50x50/' . $imageFilename;
		return '<img src="' . $image . '" width="50" height="50" />';
	}

	/**
	 * Retrieve the unique url for an item
	 *
	 * @param string $url
	 * @param int[optional] $id
	 * @return string
	 */
	public static function getUrl($url, $id = null)
	{
		// redefine Url
		$url = SpoonFilter::urlise((string) $url);

		// get db
		$db = BackendModel::getContainer()->get('database');

		// new item
		if($id === null)
		{
			$numberOfItems = (int) $db->getVar(
				'SELECT 1
				 FROM discography_albums AS i
				 INNER JOIN meta AS m ON i.meta_id = m.id
				 WHERE m.url = ?
				 LIMIT 1',
				array($url));

			// already exists
			if($numberOfItems != 0)
			{
				// add number
				$url = BackendModel::addNumber($url);

				// try again
				return self::getUrl($url);
			}
		}
		// current item should be excluded
		else
		{
			$numberOfItems = (int) $db->getVar(
				'SELECT 1
				 FROM discography_albums AS i
				 INNER JOIN meta AS m ON i.meta_id = m.id
				 WHERE m.url = ? AND i.id != ?
				 LIMIT 1',
				array($url, $id));

			// already exists
			if($numberOfItems != 0)
			{
				// add number
				$url = BackendModel::addNumber($url);

				// try again
				return self::getUrl($url, $id);
			}
		}

		// return the unique Url!
		return $url;
	}

	/**
	 * Retrieve the unique URL for a category
	 *
	 * @param string $URL The string whereon the URL will be based.
	 * @param int[optional] $id The id of the category to ignore.
	 * @return string
	 */
	public static function getURLForCategory($URL, $id = null)
	{
		// redefine URL
		$URL = (string) $URL;

		// get db
		$db = BackendModel::getContainer()->get('database');

		// new category
		if($id === null)
		{
			// already exists
			if((bool) $db->getVar(
				'SELECT 1
				 FROM discography_categories AS i
				 INNER JOIN meta AS m ON i.meta_id = m.id
				 WHERE i.language = ? AND m.url = ?
				 LIMIT 1',
				array(BL::getWorkingLanguage(), $URL)))
			{
				$URL = BackendModel::addNumber($URL);
				return self::getURLForCategory($URL);
			}
		}

		// current category should be excluded
		else
		{
			// already exists
			if((bool) $db->getVar(
				'SELECT 1
				 FROM discography_categories AS i
				 INNER JOIN meta AS m ON i.meta_id = m.id
				 WHERE i.language = ? AND m.url = ? AND i.id != ?
				 LIMIT 1',
				array(BL::getWorkingLanguage(), $URL, $id)))
			{
				$URL = BackendModel::addNumber($URL);
				return self::getURLForCategory($URL, $id);
			}
		}

		return $URL;
	}

	/**
	 * Get the meta url
	 *
	 * @param $meta_id The meta id
	 * @return string The meta url
	 */
	public static function getMetaUrl($meta_id)
	{
		return (string) BackendModel::getContainer()->get('database')->getVar(
			'SELECT url
			FROM meta
			WHERE id = ?',
			array($meta_id)
		);
	}

	/**
	 * Insert an album in the database
	 *
	 * @param array $data
	 * @return int
	 */
	public static function insert(array $data)
	{
		$data['created_on'] = BackendModel::getUTCDate();

		return (int) BackendModel::getContainer()->get('database')->insert('discography_albums', $data);
	}

	/**
	 * Insert an new track in the database
	 *
	 * @param array $data
	 * @return int
	 */
	public static function insertTrack(array $data)
	{
		$data['created_on'] = BackendModel::getUTCDate();

		return (int) BackendModel::getContainer()->get('database')->insert('discography_albums_tracks', $data);
	}

	/**
	 * Inserts a new category into the database
	 *
	 * @param array $item The data for the category to insert.
	 * @param array[optional] $meta The metadata for the category to insert.
	 * @return int
	 */
	public static function insertCategory(array $item, $meta = null)
	{
		// get db
		$db = BackendModel::getContainer()->get('database');

		// meta given?
		if($meta !== null) $item['meta_id'] = $db->insert('meta', $meta);

		// create category
		$item['id'] = $db->insert('discography_categories', $item);

		// return the id
		return $item['id'];
	}

	/**
	 * Updates an item
	 *
	 * @param int $id
	 * @param array $data
	 */
	public static function update($id, array $data)
	{
		$data['edited_on'] = BackendModel::getUTCDate();

		BackendModel::getContainer()->get('database')->update(
			'discography_albums', $data, 'id = ?', (int) $id
		);
	}

	/**
	 * Update an existing category
	 *
	 * @param array $item The new data.
	 * @return int
	 */
	public static function updateCategory(array $item)
	{
		// get db
		$db = BackendModel::getContainer()->get('database');

		// update category
		$updated = $db->update('discography_categories', $item, 'id = ?', array((int) $item['id']));

		// invalidate the cache for discography
		BackendModel::invalidateFrontendCache('discography', BL::getWorkingLanguage());

		return $updated;
	}

	/**
	 * Update an existing track
	 *
	 * @param array $item The new data.
	 * @return int
	 */
	public static function updateTrack(array $item)
	{
		// get db
		$db = BackendModel::getContainer()->get('database');

		// update track
		$updated = $db->update('discography_albums_tracks', $item, 'id = ?', array((int) $item['id']));

		// invalidate the cache for discography
		BackendModel::invalidateFrontendCache('discography', BL::getWorkingLanguage());

		return $updated;
	}

	public static function deleteTrack($id)
	{
		BackendModel::getContainer()->get('database')->delete('discography_albums_tracks', 'id = ?', (int) $id);
	}

	/**
	 * Format a date to the month and year
	 *
	 * @param int $timestamp The UNIX-timestamp to format
	 */
	public static function getMonthYearDate($timestamp)
	{
		// redefine
		$timestamp = (int) $timestamp;

		// if invalid timestamp return an empty string
		if($timestamp <= 0) return '';

		// format the date according the user his settings
		return SpoonDate::getDate('F Y', $timestamp, BL::getInterfaceLanguage());
	}
}
