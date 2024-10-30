<?php
/**
 * @package    CForms
 * @author     Yves Hoppe <yves@compojoom.com>
 * @date       2017-04-18
 *
 * @copyright  Copyright (C) 2008 - 2017 compojoom.com - Yves Hoppe. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

class CformsDb
{
	/**
	 * @param   string  $value  Quote a value
	 *
	 * @return  string
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function quote($value)
	{
		global $wpdb;

		return "'" . $wpdb->_escape($value) . "'";
	}
}
