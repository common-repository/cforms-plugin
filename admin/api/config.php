<?php
/**
 * @package    CForms
 * @author     Yves Hoppe <yves@compojoom.com>
 * @date       2017-04-18
 *
 * @copyright  Copyright (C) 2008 - 2017 compojoom.com - Yves Hoppe. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

class CformsApiConfig extends CformsApi
{

	/**
	 * Get the config
	 *
	 * @return  array
	 *
	 * @since   1.0.0
	 */
	public function _()
	{
		return array('config' => $this->loadConfig());
	}

	/**
	 * Load the component parameters and some extras
	 *
	 * @return  mixed
	 *
	 * @since   1.0.0
	 */
	protected function loadConfig()
	{
		return array('juri' => '');
	}
}
