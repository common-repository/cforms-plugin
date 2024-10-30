<?php
/**
 * @package    CForms
 * @author     Yves Hoppe <yves@compojoom.com>
 * @date       2017-04-18
 *
 * @copyright  Copyright (C) 2008 - 2017 compojoom.com - Yves Hoppe. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

class CformsApiPath extends CformsApi
{
	/**
	 * Get the base path
	 *
	 * @return array
	 */
	public function _()
	{
		return array('juri' => '');
	}

	/**
	 * Routes a given Path to full JUri
	 *
	 * @return  array
	 */
	public function route()
	{
		return array();
	}
}
