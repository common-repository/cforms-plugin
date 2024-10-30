<?php
/**
 * @package    CForms
 * @author     Yves Hoppe <yves@compojoom.com>
 * @date       2017-04-18
 *
 * @copyright  Copyright (C) 2008 - 2017 compojoom.com - Yves Hoppe. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

class CformsForms
{
	/**
	 * Get the available forms
	 *
	 * @param   int     $offset   Offset
	 * @param   int     $limit    Limit
	 * @param   string  $search   Search string defaults to empty
	 * @param   string  $orderBy  Order by
	 *
	 * @return  array|null|object
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function getForms($offset = 0, $limit = 100, $search = '', $orderBy = 'id ASC')
	{
		global $wpdb;

		$cformsFormTable  = $wpdb->prefix . 'cforms_form';

		$query = 'SELECT * FROM ' . $cformsFormTable;

		if (!empty($search))
		{
			$query .= " WHERE title LIKE '%" . sanitize_text_field($search) . "%'";
		}

		$query .= ' ORDER BY ' . sanitize_sql_orderby($orderBy);

		return $wpdb->get_results($query);
	}

	/**
	 * Get the form Select
	 *
	 * @param   string  $name   Name of the form select
	 * @param   int     $value  Current value
	 *
	 * @return  string
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function getFormSelect($name = 'formId', $value = 0)
	{
		$forms = self::getForms();

		$html = array();

		$html[] = '<select name="' . $name . '" id="' . $name . '">';

		$html[] = '<option value="0">' . __('All', 'cforms-plugin') . '</option>';

		foreach ($forms as $form)
		{
			$selected = '';

			if ($form->id == $value)
			{
				$selected = ' selected="selected"';
			}

			$html[] = '<option value="' . $form->id . '"' . $selected . '>' . $form->title . '</option>';
		}

		$html[] = '</select>';

		return implode("\n", $html);
	}

	/**
	 * Delete the form (don't delete fields, as else the submissions are screwed)
	 *
	 * @param   int  $id  Id of the form
	 *
	 * @return  false|int
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function deleteForm($id)
	{
		global $wpdb;

		$table = $wpdb->prefix . 'cforms_form';

		$query = 'DELETE FROM ' . $table . ' WHERE id = ' . (int) $id;

		$result = $wpdb->query($query);

		if ($result)
		{
			CformsPosts::deletePost($id);
		}

		return true;
	}

	/**
	 * Create a new mail template
	 *
	 * @return  int
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function createNewMailTemplate()
	{
		global $wpdb;

		$table = $wpdb->prefix . 'cforms_email';

		$result = $wpdb->insert($table, array(
			'subject' => __('Submission', 'cforms-plugin') . ' {{FORM_TITLE}}',
			'txt'     => __('New Form submission for', 'cforms-plugin') . " {{FORM_TITLE}}\n\n{{SUB_DATA}}",
			'html'    => __('New Form submission for', 'cforms-plugin') . " {{FORM_TITLE}}<br/>\n<br />{{SUB_DATA}}",
			'attachments' => ''
		));

		return $wpdb->insert_id;
	}

	/**
	 * Create a new form (required for displaying it)
	 *
	 * @return  int
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function createNewForm()
	{
		global $wpdb;

		$table = $wpdb->prefix . 'cforms_form';

		$sitename = strtolower($_SERVER['SERVER_NAME']);

		$params = new stdClass;

		$params->emailRecipients = 'wordpress@' . $sitename;

		$emailId = self::createNewMailTemplate();

		$form = array(
			'title' => __('New Form', 'cforms-plugin'),
			'pages' => 1,
			'params' => json_encode($params),
			'templateId' => $emailId
		);

		$wpdb->insert($table, $form);

		$form['id'] = $wpdb->insert_id;

		$postId = CformsPosts::createPost($form);

		return $form['id'];
	}
}
