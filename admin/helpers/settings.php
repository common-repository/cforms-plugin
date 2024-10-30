<?php
/**
 * @package    CForms
 * @author     Yves Hoppe <yves@compojoom.com>
 * @date       2017-04-18
 *
 * @copyright  Copyright (C) 2008 - 2017 compojoom.com - Yves Hoppe. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

// TODO

/**
 * CformsHelperSettings
 *
 * @since  __DEPLOY_VERSION__
 */
class CformsHelperSettings
{
	/**
	 * Get a setting from Wordpress
	 *
	 * @param   string  $key      The settings key
	 * @param   mixed   $default  Default value
	 *
	 * @return  null
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function _($key, $default = null)
	{
		return self::getSetting($key, $default);
	}

	/**
	 * Get a setting from Wordpress
	 *
	 * @param   string  $key      The settings key
	 * @param   mixed   $default  Default value
	 *
	 * @return  null
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function getSetting($key, $default = null)
	{
		// Just return the default for now
		return $default;
	}
}
