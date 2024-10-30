<?php
/**
 * @package    CForms
 * @author     Yves Hoppe <yves@compojoom.com>
 * @date       2017-07-15
 *
 * @copyright  Copyright (C) 2008 - 2017 compojoom.com - Yves Hoppe. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

class CformsApiElement extends CformsApi
{
	/**
	 * Elements
	 *
	 * @var     array
	 * @since   __DEPLOY_VERSION__
	 */
	protected $elements = array();

	/**
	 * Get all elements available
	 *
	 * @return  array
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function _()
	{
		return array('elements' => $this->loadElements());
	}

	/**
	 * Load custom elements via plugin event
	 *
	 * @return  mixed  Array of objects
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function loadElements()
	{
		return array();
	}
}
