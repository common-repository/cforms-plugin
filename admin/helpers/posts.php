<?php
/**
 * @package    CForms
 * @author     Yves Hoppe <yves@compojoom.com>
 * @date       2017-04-18
 *
 * @copyright  Copyright (C) 2008 - 2017 compojoom.com - Yves Hoppe. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

class CformsPosts
{
	/**
	 * Create a WordPress Post (so that it is linkable in the menu)
	 *
	 * @param   array  $form  Form
	 *
	 * @return  int
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function createPost($form)
	{
		$id = wp_insert_post(array(
			'post_title' => $form['title'],
			'post_type'  => 'cforms',
			'post_name'  => 'form-' . $form['id'],
			'comment_status' => 'closed',
			// We autopublish the form for now - todo setting in form
			'post_status' => 'publish'
		));

		return $id;
	}

	/**
	 * Update a WordPress Post (so that it is linkable in the menu)
	 *
	 * @param   array  $form  Form
	 *
	 * @return  int
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function updatePost($form)
	{
		global $wpdb;

		$table = $wpdb->prefix . 'posts';

		// Get the post id
		$sql = 'SELECT * FROM ' . $table
			. ' WHERE post_type = ' . CformsDb::quote('cforms')
			. ' AND post_name = ' . CformsDb::quote('form-' . $form['id']);

		$post = $wpdb->get_row($sql);

		// Fallback if no post is existing (ERROR should not happen!)
		if (empty($post))
		{
			return self::createPost($form);
		}

		$id = wp_update_post(array(
			'ID' => $post->ID,
			'post_title' => $form['title'],
			'post_type'  => 'cforms',
			'comment_status' => 'closed',
			// We autopublish the form for now - todo setting in form
			'post_status' => 'publish'
		));

		return $id;
	}

	/**
	 * Delete a post
	 *
	 * @param   int  $formId  The formId
	 *
	 * @return  array|false|WP_Post False on failure
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function deletePost($formId)
	{
		global $wpdb;

		$table = $wpdb->prefix . 'posts';

		// Get the post id
		$sql = 'SELECT * FROM ' . $table
			. ' WHERE post_type = ' . CformsDb::quote('cforms')
			. ' AND post_name = ' . CformsDb::quote('form-' . $formId);

		$post = $wpdb->get_row($sql);

		if (empty($post))
		{
			return true;
		}

		return wp_delete_post($post->ID, true);
	}
}
