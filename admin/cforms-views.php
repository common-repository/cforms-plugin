<?php
/**
 * @package    CForms
 * @author     Yves Hoppe <yves@compojoom.com>
 * @date       2017-04-18
 *
 * @copyright  Copyright (C) 2008 - 2017 compojoom.com - Yves Hoppe. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

function cforms_forms()
{
	$action = CformsInput::getCmd('action', '');

	if (empty($action) || $action == 'view')
	{
		include_once plugin_dir_path(__FILE__) . 'partials/cforms-forms-display.php';
	}
	elseif ($action == 'edit')
	{
		CformsBehavior::loadAngular();
		include_once plugin_dir_path(__FILE__) . 'partials/cforms-form-display.php';
	}
}

/**
 * Submission view and edit
 *
 * @return  void
 *
 * @since   __DEPLOY_VERSION__
 */
function cforms_submissions()
{
	$action = CformsInput::getCmd('action', '');

	if (empty($action))
	{
		include_once plugin_dir_path(__FILE__) . 'partials/cforms-submissions-display.php';
	}
	elseif ($action == 'view')
	{
		include_once plugin_dir_path(__FILE__) . 'partials/cforms-submission-display.php';
	}
}
