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
 * CformsHelperRegistry
 *
 * @since  __DEPLOY_VERSION__
 */
class CformsHelperRegistry
{
	/**
	 * Data Object
	 *
	 * @var    object
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	protected $data;

	/**
	 * Constructor
	 *
	 * @param  object|array  $data
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function __construct($data)
	{
		$this->data = new stdClass;

		if (is_array($data))
		{
			$this->data = json_decode(json_encode($data));
		}
		elseif (is_object($data))
		{
			$this->data = $data;
		}
		else
		{
			throw new Exception('Registry can be only created with an array or object');
		}
	}

	/**
	 * Get a value out of the registry
	 *
	 * @param   string  $key      The key
	 * @param   mixed   $default  Default value
	 *
	 * @return  null
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function _($key, $default = null)
	{
		return $this->get($key, $default);
	}

	/**
	 * Get a value out of the registry
	 *
	 * @param   string  $key      The key
	 * @param   mixed   $default  Default value
	 *
	 * @return  null
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function get($key, $default = null)
	{
		if (!empty($this->data->$key))
		{
			return $this->data->$key;
		}

		// Just return the default for now
		return $default;
	}
}
