<?php

namespace Frontend\Modules\Discography\Engine;

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

use Frontend\Core\Engine\Model as FrontendModel;
use Frontend\Core\Engine\Navigation as FrontendNavigation;

/**
 * In this file we store all generic functions that we will be using in the discography module
 *
 * @author Jesse Dobbelaere <jesse@dobbelaere-ae.be>
 */
class Model
{
	/**
	 * Get all albums with their categories
	 * @return Array All albums in their categories
	 */
	public static function getAll()
	{
		// get db instance
		$db = FrontendModel::getContainer()->get('database');

		// get (non-empty) categories
		$queryCategories = 'SELECT c.id, c.title
					FROM discography_categories as c
					RIGHT OUTER JOIN discography_albums as a ON c.id = a.category_id
					ORDER BY c.title';
		$categoriesWithAlbums = $db->getRecords($queryCategories);

		// get albums
		$queryAlbums = 'SELECT a.*, m.url
					FROM discography_albums AS a
					INNER JOIN meta AS m ON a.meta_id = m.id
					WHERE hidden = "N"
					ORDER BY a.release_date DESC';
		$albums = $db->getRecords($queryAlbums);

		// init var
		$link = FrontendNavigation::getURLForBlock('Discography', 'detail');

		// build array
		$discography = array();
		if($categoriesWithAlbums !== null) {
			foreach($categoriesWithAlbums as $category) {
				// create category
				$discography[$category['id']] = array(
					'name' => $category['title'],
					'albums' => array()
				);

				// add albums to category
				foreach($albums as $key => $album) {
					if($album['category_id'] == $category['id']) {
						$discography[$category['id']]['albums'][$key] = $album;
						$discography[$category['id']]['albums'][$key]['full_url'] = $link . '/' . $album['url'];
					}
				}
			}
		}

		return $discography;
	}

	/**
	 * This function will fetch the id for an album from its url
	 *
	 * @param  string $url The album url
	 * @return int The album ID
	 */
	public static function getIdForUrl($url)
	{
		return (int) FrontendModel::getContainer()->get('database')->getVar(
			'SELECT a.id
			FROM discography_albums AS a
			INNER JOIN meta AS m ON m.id = a.meta_id
			WHERE m.url = ? AND a.hidden = ?',
			array((string) $url, 'N')
		);
	}

	/**
	 * This will fetch the data for a given id
	 *
	 * @param  int $id The album ID
	 * @return Array The album data
	 */
	public static function getById($id)
	{
		// Get album
		$result = (array) FrontendModel::getContainer()->get('database')->getRecord(
			'SELECT
			a.id,
			a.title,
			a.release_date, a.image,
			UNIX_TIMESTAMP(a.created_on) AS created_on,
			m.keywords AS meta_keywords,
			m.keywords_overwrite AS meta_keywords_overwrite,
			m.description AS meta_description,
			m.description_overwrite AS meta_description_overwrite,
			m.title AS meta_title,
			m.title_overwrite AS meta_title_overwrite,
			m.url,
			m.data AS meta_data
			FROM discography_albums AS a
			INNER JOIN meta AS m ON m.id = a.meta_id
			WHERE a.id = ? AND a.hidden = ?',
			array((int) $id, 'N')
		);

		// Get tracks (if available)
		$tracks = (array) FrontendModel::getContainer()->get('database')->getRecords(
			'SELECT
			t.title AS title,
			t.duration,
			t.sequence
			FROM discography_albums AS a
			INNER JOIN discography_albums_tracks AS t ON t.album_id = a.id
			WHERE a.id = ? AND a.hidden = ?
			ORDER BY t.sequence',
			array((int) $id, 'N')
		);

		// Add tracks to album array
		if(isset($tracks)) $result['tracks'] = $tracks;

		// build the full url for the specific item
		$albumDetailUrl = FrontendNavigation::getURLForBlock('Discography', 'detail');
		$result['full_url'] = $albumDetailUrl . '/' . $result['url'];

		// get the meta data
		$result = self::buildMetaData($result);

		return $result;
	}

	/**
	 * Builds the meta
	 *
	 * @param array $data The data to convert the meta from.
	 * @return array
	 */
	public static function buildMetaData(array $data)
	{
		// return if no data is given
		if(empty($data)) return array();

		// the meta
		$meta = array();

		// loop the data
		foreach($data as $key => $column) {
			// if there is meta_ set in the column name
			if(strpos($key, 'meta_') !== false) {
				$metaKey = substr($key, 5);
				$meta[$metaKey] = $column;
				unset($data[$key]);
			}
		}

		// add the meta
		$data['meta'] = $meta;

		// return
		return $data;
	}
}
