<?php
/**
 * @package    CForms
 * @author     Yves Hoppe <yves@compojoom.com>
 * @date       2017-04-18
 *
 * @copyright  Copyright (C) 2008 - 2017 compojoom.com - Yves Hoppe. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

class CformsApiInfo extends CformsApi
{
	/**
	 * Default function
	 *
	 * @return array
	 */
	public function _()
	{
		return array('version' => CFORMS_VERSION);
	}

	/**
	 * Get the version of CForms
	 *
	 * @return  array
	 */
	public function version()
	{
		return array('version' => CFORMS_VERSION);
	}
}
