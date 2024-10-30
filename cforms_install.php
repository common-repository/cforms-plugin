<?php
/**
 * @package    CForms
 * @author     Yves Hoppe <yves@compojoom.com>
 * @date       2017-04-18
 *
 * @copyright  Copyright (C) 2008 - 2017 compojoom.com - Yves Hoppe. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

global $cforms_db_version;
$cforms_db_version = '3.0.0';

/**
 * Create the tables for CForms
 *
 * @return  void
 *
 * @since   __DEPLOY_VERSION__
 */
function cforms_install()
{
	global $wpdb;
	global $cforms_db_version;

	$installed_ver = get_option( "cforms_db_version" );

	$cformsEmailTable = $wpdb->prefix . 'cforms_email';
	$cformsFieldTable = $wpdb->prefix . 'cforms_field';
	$cformsFormTable  = $wpdb->prefix . 'cforms_form';

	$cformsSubmissionTable = $wpdb->prefix . 'cforms_submission';
	$cformsTemplateTable   = $wpdb->prefix . 'cforms_template';

	$charset_collate = $wpdb->get_charset_collate();

	$queries = array();

	// Mail
	$queries[] = "
		CREATE TABLE $cformsEmailTable (
		  id int(11) NOT NULL AUTO_INCREMENT,
		  subject varchar(500) NOT NULL DEFAULT '',
		  txt text NOT NULL,
		  html text NOT NULL,
		  attachments text NOT NULL,
		  PRIMARY KEY (id)
		) $charset_collate;
	";

	// Fields
	$queries[] = "
		CREATE TABLE $cformsFieldTable (
		  id int(11) unsigned NOT NULL AUTO_INCREMENT,
		  formId int(11) NOT NULL DEFAULT '1',
		  page int(5) NOT NULL DEFAULT '1',
		  type varchar(255) NOT NULL DEFAULT 'text',
		  component varchar(255) NOT NULL DEFAULT 'FieldInputText',
		  label text NOT NULL,
		  options text NOT NULL,
		  defaultValue varchar(500) NOT NULL DEFAULT '',
		  placeholder varchar(500) NOT NULL DEFAULT '',
		  sizeXs tinyint(4) NOT NULL DEFAULT '12',
		  sizeSm tinyint(4) NOT NULL DEFAULT '12',
		  sizeMd tinyint(4) NOT NULL DEFAULT '12',
		  sizeLg tinyint(4) NOT NULL DEFAULT '12',
		  hidden varchar(500) NOT NULL DEFAULT '{\"Xs\": false, \"Sm\": false, \"Md\": false, \"Lg\": false}',
		  cssClass varchar(1000) NOT NULL DEFAULT '',
		  style varchar(1000) NOT NULL DEFAULT '',
		  required tinyint(1) NOT NULL DEFAULT '0',
		  showLabel tinyint(1) NOT NULL DEFAULT '1',
		  validator varchar(255) NOT NULL DEFAULT '',
		  ordering tinyint(4) NOT NULL DEFAULT '1',
		  mapping varchar(500) NOT NULL DEFAULT '{}',
		  params text NOT NULL,
		  conditions text NOT NULL,
		  skipExport tinyint(1) NOT NULL DEFAULT '0',
		  status tinyint(4) NOT NULL DEFAULT '1',
		  PRIMARY KEY (id)
		) $charset_collate;
	";

	// Form
	$queries[] = "
		CREATE TABLE $cformsFormTable (
		  id int(11) unsigned NOT NULL AUTO_INCREMENT,
		  title varchar(255) NOT NULL DEFAULT '',
		  html mediumtext,
		  bootstrapType varchar(255) NOT NULL DEFAULT '',
		  design varchar(255) NOT NULL DEFAULT 'none',
		  stepHeading varchar(255) NOT NULL DEFAULT 'none',
		  pages tinyint(4) NOT NULL DEFAULT '1',
		  templateId int(11) NOT NULL DEFAULT '1',
		  params text NOT NULL,
		  PRIMARY KEY (id)
		) $charset_collate;
	";

	// Submission
	$queries[] = "
		CREATE TABLE $cformsSubmissionTable (
		    id int(11) unsigned NOT NULL AUTO_INCREMENT,
		    uuid VARCHAR(255) NOT NULL DEFAULT '',
			userId int(11) NOT NULL DEFAULT '0',
			formId int(11) NOT NULL DEFAULT '0',
			data mediumtext NOT NULL,
			viewed tinyint(1) NOT NULL DEFAULT '0',
			useragent varchar(500) NOT NULL DEFAULT '',
			ip varchar(40) NOT NULL DEFAULT '',
			created timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
			modified timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
			status tinyint(255) NOT NULL DEFAULT '1',
			PRIMARY KEY (id)
		) $charset_collate;
	";

	// Templates
	$queries[] = "
		CREATE TABLE $cformsTemplateTable (
		  id int(11) unsigned NOT NULL AUTO_INCREMENT,
		  title varchar(255) NOT NULL DEFAULT '',
		  subject varchar(255) NOT NULL DEFAULT '',
		  txt varchar(255) NOT NULL DEFAULT '',
		  PRIMARY KEY (id)
		) $charset_collate;
	";

	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	dbDelta($queries);

	add_option('cforms_db_version', $cforms_db_version);

	if ($installed_ver != $cforms_db_version)
	{
		$sql = "CREATE TABLE $cformsFieldTable (
		  id int(11) unsigned NOT NULL AUTO_INCREMENT,
		  formId int(11) NOT NULL DEFAULT '1',
		  page int(5) NOT NULL DEFAULT '1',
		  type varchar(255) NOT NULL DEFAULT 'text',
		  component varchar(255) NOT NULL DEFAULT 'FieldInputText',
		  label text NOT NULL,
		  options text NOT NULL,
		  defaultValue varchar(500) NOT NULL DEFAULT '',
		  placeholder varchar(500) NOT NULL DEFAULT '',
		  sizeXs tinyint(4) NOT NULL DEFAULT '12',
		  sizeSm tinyint(4) NOT NULL DEFAULT '12',
		  sizeMd tinyint(4) NOT NULL DEFAULT '12',
		  sizeLg tinyint(4) NOT NULL DEFAULT '12',
		  hidden varchar(500) NOT NULL DEFAULT '{\"Xs\": false, \"Sm\": false, \"Md\": false, \"Lg\": false}',
		  cssClass varchar(1000) NOT NULL DEFAULT '',
		  style varchar(1000) NOT NULL DEFAULT '',
		  required tinyint(1) NOT NULL DEFAULT '0',
		  showLabel tinyint(1) NOT NULL DEFAULT '1',
		  validator varchar(255) NOT NULL DEFAULT '',
		  ordering tinyint(4) NOT NULL DEFAULT '1',
		  mapping varchar(500) NOT NULL DEFAULT '{}',
		  params text NOT NULL,
		  conditions text NOT NULL,
		  skipExport tinyint(1) NOT NULL DEFAULT '0',
		  status tinyint(4) NOT NULL DEFAULT '1',
		  PRIMARY KEY  (id)
		) $charset_collate;";

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);

		update_option( "cforms_db_version", $cforms_db_version);
	}
}

/**
 * Install sample data
 *
 * @return  void
 *
 * @since   __DEPLOY_VERSION__
 */
function cforms_install_data()
{
	global $wpdb;

	$cformsEmailTable    = $wpdb->prefix . 'cforms_email';
	$cformsFieldTable    = $wpdb->prefix . 'cforms_field';
	$cformsFormTable     = $wpdb->prefix . 'cforms_form';
	$cformsTemplateTable = $wpdb->prefix . 'cforms_template';

	// Make sure
	$query = 'SELECT count(*) FROM ' . $cformsFormTable;

	$count = $wpdb->get_var($query);

	if (!empty($count))
	{
		return true;
	}

	$wpdb->insert($cformsFormTable,
		array(
			'title'         => __('Sample Form', 'cforms'),
			'html'          => "Please edit the form First",
			'bootstrapType' => '',
			'design'        => 'none',
			'stepHeading'   => 'none',
			'pages'         => 1,
			'templateId'    => 2,
			'params'        => '{"emailRecipients":"noreply@compojoom.com"}'
		)
	);

	$wpdb->insert($cformsEmailTable,
		array(
			'id'          => 2,
			'subject'     => 'Submission {{FORM_TITLE}}',
			'txt'         => "New form submission for {{FORM_TITLE}}\n\n{{SUB_DATA}}",
			'html'        => "New form submission for {{FORM_TITLE}}<br />\n<br />\n{{SUB_DATA}}",
			'attachments' => '',
		)
	);

	$wpdb->insert($cformsFieldTable, array(
			'formId'       => 1,
			'page'         => 1,
			'type'         => 'text',
			'component'    => 'FieldInputText',
			'label'        => 'First field',
			'options'      => '[]',
			'defaultValue' => '',
			'placeholder'  => 'Sample Placeholder',
			'sizeXs'       => 12,
			'sizeSm'       => 12,
			'sizeMd'       => 12,
			'sizeLg'       => 6,
			'cssClass'     => 'form-control',
			'style'        => '',
			'required'     => '0',
			'validator'    => '',
			'ordering'     => '',
			'mapping'      => '{}',
			'params'       => '{"tooltip":""}',
			'skipExport'   => '0',
			'status'       => '1'
		)
	);

	$wpdb->insert($cformsTemplateTable,
		array(
			'title'   => 'csv',
			'subject' => '',
			'txt'     => '{{SUB_CSV}}'
		)
	);
}

