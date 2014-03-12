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
class FrontendDiscographyModel
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
		$categories = $db->getRecords($queryCategories);

		// get albums
		$queryAlbums = 'SELECT a.*, m.url
					FROM discography_albums AS a
					INNER JOIN meta AS m ON a.meta_id = m.id
					WHERE hidden = "N"
					ORDER BY a.release_date DESC';
		$albums = $db->getRecords($queryAlbums);

		// init var
		$link = FrontendNavigation::getURLForBlock('discography', 'detail');

		// build array
		$discography = array();
		foreach($categories as $category)
		{
			// create category
			$discography[$category['id']] = array(
				'name' => $category['title'],
				'albums' => array()
			);

			// add albums to category
			foreach($albums as $key => $album)
			{
				if($album['category_id'] == $category['id'])
				{
					$discography[$category['id']]['albums'][$key] = $album;
					$discography[$category['id']]['albums'][$key]['full_url'] = $link . '/' . $album['url'];
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
	public static function getDataForId($id)
	{
		$results = (array) FrontendModel::getContainer()->get('database')->getRecords(
			'SELECT
				a.id, a.title AS album_title, a.release_date, a.image, UNIX_TIMESTAMP(a.created_on) AS created_on, m.url, t.title AS track_title, t.duration, t.sequence
			FROM discography_albums AS a
			INNER JOIN discography_albums_tracks AS t ON t.album_id = a.id
			INNER JOIN meta AS m ON m.id = a.meta_id
			WHERE a.id = ? AND a.hidden = ?
			ORDER BY t.sequence',
			array((int) $id, 'N')
		);

		// rebuild the array
		$albumData = array(
			'id' => $results[0]['id'],
			'title' => $results[0]['album_title'],
			'release_date' => $results[0]['release_date'],
			'image' => $results[0]['image'],
			'created_on' => $results[0]['created_on'],
			'url' => $results[0]['url'],
			'tracks' => array()
		);

		// add tracks to new array
		foreach($results as $result)
		{
			$albumData['tracks'][] = array(
				'sequence' => $result['sequence'],
				'title' => $result['track_title'],
				'duration' => $result['duration']
			);
		}

		// build the full url for the specific item
		$albumDetailUrl = FrontendNavigation::getURLForBlock('discography', 'detail');
		$albumData['full_url'] = $albumDetailUrl . '/' . $albumData['url'];

		// get the meta data
		//$albumData = self::buildMetaData($albumData);

		return $albumData;
	}
}
