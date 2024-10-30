<?php
/*
Plugin Name: CForms Plugin
Plugin URI:  https://compojoom.com/wordpress-plugins/cforms-plugin/
Description: Bootstrap Form Builder for WordPress
Version:     3.0.0
Author:      compojoom.com
Author URI:  https://compojoom.com/
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: cforms-plugin
Domain Path: /languages
*/

// If this file is called directly, abort.
if (!defined('WPINC'))
{
	die;
}

// Used for referring to the plugin file or basename
if (!defined('CFORMS_BASE_PATH'))
{
	define('CFORMS_BASE_PATH', plugin_dir_path(__FILE__));
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-plugin-name-activator.php
 */
function activate_cforms()
{
	require_once plugin_dir_path(__FILE__) . 'includes/class-cforms-activator.php';
	Cforms_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-plugin-name-deactivator.php
 */
function deactivate_cforms()
{
	return true;
}

register_activation_hook(__FILE__, 'activate_cforms');
register_deactivation_hook(__FILE__, 'deactivate_cforms');

require_once plugin_dir_path(__FILE__) . 'cforms_install.php';

// Update and install
register_activation_hook(__FILE__, 'cforms_install');
register_activation_hook(__FILE__, 'cforms_install_data');

function cforms_update_db_check()
{
	global $cforms_db_version;

	if (get_site_option('cforms_db_version') != $cforms_db_version)
	{
		cforms_install();
	}
}

add_action('plugins_loaded', 'cforms_update_db_check');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-cforms.php';

include_once plugin_dir_path(__FILE__) . 'admin/cforms-views.php';

add_action('wp_ajax_cforms_api', 'cforms_api');

// Move into class
function cforms_api()
{
	if (!current_user_can('manage_options'))
	{
		wp_die(__('You do not have sufficient permissions to access this page.'));
	}

	require_once plugin_dir_path(__FILE__) . 'admin/helpers/input.php';
	require_once plugin_dir_path(__FILE__) . 'admin/api/api.php';

	// Request
	$request = CformsInput::getCmd('request', 'none');

	if (empty($request))
	{
		throw new Exception("Please specify a request", 404);
	}

	$path = plugin_dir_path(__FILE__) . 'admin/api/' . $request . '.php';

	if (!file_exists($path))
	{
		throw new Exception("Request " . $request . " not found", 404);
	}

	require_once $path;

	$classname = "CformsApi" . ucfirst($request);

	/** @var CformsAPi $api */
	$api = new $classname;
	$api->execute(CformsInput::getCmd('task', ''));

	wp_die();
}

// Add shortcode for inclusion
add_shortcode('cforms', 'include_cforms');

function include_cforms($atts)
{
	$attributes = shortcode_atts(array('id' => '-1'), $atts);

	if (strcmp($attributes['id'], '-1') == 0)
	{
		return "";
	}

	// Expose formId
	$formId = $attributes['id'];

	// Autoload files
	require_once plugin_dir_path(__FILE__) . '/public/autoload.php';
	return include_once plugin_dir_path(__FILE__) . '/public/templates/single-form.php';
}

// Post type
add_action('init', 'create_cforms_post_type');

function create_cforms_post_type()
{
	register_post_type('cforms',
		array(
			'labels'          => array(
				'name'          => __('Forms', 'cforms-plugin'),
				'singular_name' => __('Form', 'cforms-plugin')
			),
			'public'          => true,
			'hierarchical'    => true,
			'public_querable' => true,
			'show_in_menu'    => false,
			'query_var'       => true,
			'has_archive'     => false,
		)
	);
}

function cforms_rewrite_flush() {
	create_cforms_post_type();
	flush_rewrite_rules();
}

register_activation_hook(__FILE__, 'cforms_rewrite_flush');

/**
 * Adds a default single view template for a job opening
 *
 * @param   string  $template  The name of the template
 *
 * @return  mixed  The single template
 */
function single_cforms_template($template)
{
	global $post;

	$return = $template;

	if ($post->post_type == 'cforms')
	{
		require_once plugin_dir_path(__FILE__) . 'public/autoload.php';

		$return = CformsTemplate::get_template('single-form');
	}

	return $return;
}

add_filter('single_template', 'single_cforms_template');

// Action for download files through cforms
add_action('admin_init', 'cforms_download', 1);

function cforms_download()
{
	if (!current_user_can('manage_options'))
	{
		wp_die(__('You do not have sufficient permissions to access this page.'));
	}

	$page   = CformsInput::getCmd('page');
	$action = CformsInput::getCmd('action');

	$availableAction = array('xml', 'csv', 'json');

	if ($page == 'cforms_submissions' && in_array($action, $availableAction))
	{
		CformsExport::export();
		exit;
	}
}

add_action('admin_init', 'cforms_admin_tasks', 1);

/**
 * Tasks that need a redirect afterwards, like deleting a submission or an form
 *
 * @return  void
 *
 * @since   __DEPLOY_VERSION__
 */
function cforms_admin_tasks()
{
	if (!current_user_can('manage_options'))
	{
		wp_die(__('You do not have sufficient permissions to access this page.'));
	}

	$page   = CformsInput::getCmd('page');
	$action = CformsInput::getCmd('action');

	if ($page == 'cforms_submissions' && $action == 'delete')
	{
		if (!wp_verify_nonce($_REQUEST['_wpnonce'], 'deleteSubmission'))
		{
			echo 'Error Processing request';

			exit; // Get out of here, the nonce is rotten!
		}

		$submissionId = CformsInput::getInt('submissionId', 0);

		if (!$submissionId)
		{
			echo 'Unknown Submission Id';

			exit;
		}

		CformsHelperSubmission::deleteSubmission($submissionId);

		wp_redirect(admin_url('admin.php?page=cforms_submissions'));
		exit;
	}
	elseif ($page == 'cforms' && $action == 'delete')
	{
		if (!wp_verify_nonce($_REQUEST['_wpnonce'], 'deleteForm'))
		{
			echo 'Error Processing request';

			exit; // Get out of here, the nonce is rotten!
		}

		$formId = CformsInput::getInt('formId', 0);

		CformsForms::deleteForm($formId);

		wp_redirect(admin_url('admin.php?page=cforms'));
		exit;
	}
	elseif ($page == 'cforms' && $action == 'new')
	{
		$formId = CformsForms::createNewForm();

		wp_redirect(admin_url('admin.php?page=cforms&action=edit&formId=' . $formId));
		exit;
	}
}

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_Cforms()
{
	$plugin = new Cforms();
	$plugin->run();
}

run_Cforms();
