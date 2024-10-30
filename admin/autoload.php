<?php
/**
 * @package    CForms
 * @author     Yves Hoppe <yves@compojoom.com>
 * @date       2017-04-18
 *
 * @copyright  Copyright (C) 2008 - 2017 compojoom.com - Yves Hoppe. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

$pluginBasePath = plugin_dir_path( __FILE__ );

// Get Helpers @todo move to autodiscover
require_once $pluginBasePath . 'helpers/defines.php';
require_once $pluginBasePath . 'helpers/behavior.php';
require_once $pluginBasePath . 'helpers/forms.php';
require_once $pluginBasePath . 'helpers/input.php';
require_once $pluginBasePath . 'helpers/table.php';
require_once $pluginBasePath . 'helpers/template.php';
require_once $pluginBasePath . 'helpers/submission.php';
require_once $pluginBasePath . 'helpers/csv.php';
require_once $pluginBasePath . 'helpers/settings.php';
require_once $pluginBasePath . 'helpers/posts.php';
require_once $pluginBasePath . 'helpers/export.php';
require_once $pluginBasePath . 'helpers/db.php';
require_once $pluginBasePath . 'helpers/registry.php';
require_once $pluginBasePath . 'helpers/mappings.php';

// API
require_once $pluginBasePath . 'api/api.php';
require_once $pluginBasePath . 'api/config.php';
require_once $pluginBasePath . 'api/email.php';
require_once $pluginBasePath . 'api/field.php';
require_once $pluginBasePath . 'api/form.php';
require_once $pluginBasePath . 'api/info.php';
require_once $pluginBasePath . 'api/path.php';

