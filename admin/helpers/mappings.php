<?php
/**
 * @package    CForms
 * @author     Yves Hoppe <yves@compojoom.com>
 * @date       2017-08-01
 *
 * @copyright  Copyright (C) 2008 - 2017 compojoom.com - Yves Hoppe. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */


/**
 * Helper for loading data
 *
 * @since  __DEPLOY_VERSION__
 */
class CformsHelperMappings
{
	/**
	 * Get the mapping for Data if any
	 *
	 * Workflow:
	 *
	 * - Get data from wordpress user
	 *
	 * @return  array
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function get()
	{
		$categories = array();

		$categories['wordpress'] = self::getWordpressMapping();

		return $categories;
	}

	/**
	 * Get the Data for the mapping
	 *
	 * @return  array
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function getData()
	{
		$categories = array();

		$categories['wordpress'] = self::getWordpressMappingData();

		return $categories;
	}

	/**
	 * Get the WordPress Mapping for the WordPress User
	 *
	 * @return  stdClass
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected static function getWordpressMapping()
	{
		$mapping = new stdClass;

		$mapping->user_firstname     = __('First name', 'cforms-plugin');
		$mapping->user_lastname      = __('Last name', 'cforms-plugin');
		$mapping->user_display_name  = __('Display name', 'cforms-plugin');
		$mapping->user_email         = __('Email', 'cforms-plugin');
		$mapping->ID                 = __('ID', 'cforms-plugin');

		return $mapping;
	}

	/**
	 * Get the Joomla user Data
	 *
	 * @return  stdClass
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected static function getWordpressMappingData()
	{
		$user = wp_get_current_user();

		$data = new stdClass;

		// we could use the property, but we can extend it later with name etc. this way
		$data->user_firstname    = $user->user_firstname;
		$data->user_lastname     = $user->user_lastname;
		$data->user_display_name = $user->display_name;
		$data->user_email        = $user->user_email;
		$data->ID                = $user->ID;

		return $data;
	}
}
