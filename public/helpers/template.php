<?php
/**
 * @package    CForms
 * @author     Yves Hoppe <yves@compojoom.com>
 * @date       2017-04-18
 *
 * @copyright  Copyright (C) 2008 - 2017 compojoom.com - Yves Hoppe. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

/**
 * CformsTemplate
 *
 * @since  __DEPLOY_VERSION__
 */
class CformsTemplate
{
	/**
	 * Returns the path to a template file
	 *
	 * Looks for the file in these directories, in this order:
	 *        Current theme
	 *        Parent theme
	 *        Current theme templates folder
	 *        Parent theme templates folder
	 *        This plugin
	 *
	 * To use a custom list template in a theme, copy the
	 * file from public/templates into a templates folder in your
	 * theme. Customize as needed, but keep the file name as-is. The
	 * plugin will automatically use your custom template file instead
	 * of the ones included in the plugin.
	 *
	 * @param   string  $name  The name of a template file
	 *
	 * @return  string  The path to the template
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function get_template($name)
	{
		// Helpers
		require_once plugin_dir_path(dirname(__FILE__)) . 'helpers/form.php';

		$locations[] = "{$name}.php";
		$locations[] = "/templates/{$name}.php";

		/**
		 * Filter the locations to search for a template file
		 *
		 * @param    array $locations File names and/or paths to check
		 */
		apply_filters('cforms-template-paths', $locations);

		$template = locate_template($locations, true);

		if (empty($template))
		{
			$template = plugin_dir_path(dirname(__FILE__)) . 'templates/' . $name . '.php';
		}

		return $template;
	}
}
