<?php
/**
 * @package    CForms
 * @author     Yves Hoppe <yves@compojoom.com>
 * @date       2017-04-18
 *
 * @copyright  Copyright (C) 2008 - 2017 compojoom.com - Yves Hoppe. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

class CformsInput
{
	/**
	 * Get an sanitized value from the $_REQUEST
	 *
	 * @param   string      $key      Request Key
	 * @param   null|mixed  $default  Default if not isset
	 * @param   string      $filter   Validation filter to use
	 *
	 * @return  mixed
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function get($key, $default = null, $filter = 'Cmd')
	{
		$methodName = 'get' . ucfirst($filter);

		return self::$methodName($key, $default);
	}

	/**
	 * Get an integer from the $_REQUEST
	 *
	 * @param   string      $key      Request Key
	 * @param   null|mixed  $default  Default if not isset
	 *
	 * @return  int
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function getInt($key, $default = 0)
	{
		if (!isset($_REQUEST[$key]))
		{
			return (int) $default;
		}

		return (int) $_REQUEST[$key];
	}

	/**
	 * Get an integer from the $_REQUEST
	 *
	 * @param   string      $key      Request Key
	 * @param   null|mixed  $default  Default if not isset
	 *
	 * @return  string|null
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function getString($key, $default = null)
	{
		if (!isset($_REQUEST[$key]))
		{
			return $default;
		}

		return sanitize_text_field($_REQUEST[$key]);
	}

	/**
	 * Get an command from the $_REQUEST
	 *
	 * @param   string      $key      Request Key
	 * @param   null|mixed  $default  Default if not isset
	 *
	 * @return  string|null
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function getCmd($key, $default = null)
	{
		// TODO add some more filters we just want one word, no special chars etc
		return self::getString($key, $default);
	}

	/**
	 * Get an raw value from the $_REQUEST (Careful dangerous)
	 *
	 * @param   string      $key      Request Key
	 * @param   null|mixed  $default  Default if not isset
	 *
	 * @return  int
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function getRaw($key, $default)
	{
		if (!isset($_REQUEST[$key]))
		{
			return $default;
		}

		return $_REQUEST[$key];
	}

	/**
	 * Get the JSON Data from the raw Request
	 *
	 * @param   bool  $asArray  Should the JSON decoded values returned as array
	 *
	 * @return  array|mixed|object
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function getJsonData($asArray = false)
	{
		// We need the raw input
		$input = file_get_contents('php://input');

		// Get rid of data
		$input = str_replace('data=', '', $input);

		$json = json_decode($input, $asArray);

		return $json;
	}

	/**
	 * Get an array from the $_REQUEST
	 *
	 * @param   string      $key      Request Key
	 * @param   null|mixed  $default  Default if not isset
	 * @param   string      $filter   The filter to use (Defaults to string)
	 *
	 * @return  array
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function getArray($key, $default = array(), $filter = 'string')
	{
		if (!isset($_REQUEST[$key]))
		{
			return $default;
		}

		$inputs = $_REQUEST[$key];

		// Always return an array
		if (is_string($inputs))
		{
			return array(sanitize_text_field($inputs));
		}

		foreach ($inputs as $i => $input)
		{
			$inputs[$i] = sanitize_text_field($input);
		}

		return $inputs;
	}
}
