<?php
/**
 * @package    CForms
 * @author     Yves Hoppe <yves@compojoom.com>
 * @date       2017-04-18
 *
 * @copyright  Copyright (C) 2008 - 2017 compojoom.com - Yves Hoppe. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

class CformsHelperSubmission
{
	/**
	 * Check if form has a recaptcha
	 *
	 * @param   int  $formId  The form id
	 *
	 * @return  int|null
	 *
	 * @since   1.0.0
	 */
	public static function hasRecaptcha($formId)
	{
		global $wpdb;
		$table = $wpdb->prefix . 'cforms_field';

		$query = 'SELECT count(*) FROM ' . $table
			. " WHERE component = 'FieldGoogleRecaptcha'"
			. ' AND formId = ' . (int) $formId;

		return $wpdb->get_var($query);
	}

	/**
	 * Get the recaptcha key
	 *
	 * @param   int  $formId  The form id
	 *
	 * @return  string|null
	 *
	 * @since   1.0.0
	 */
	public static function getRecaptchaKey($formId)
	{
		global $wpdb;
		$table = $wpdb->prefix . 'cforms_field';

		$query = 'SELECT params FROM ' . $table
			. ' WHERE component = ' . CformsDb::quote('FieldGoogleRecaptcha')
			. ' AND formId = ' . (int) $formId;

		$result = $wpdb->get_var($query);

		if (!$result)
		{
			return null;
		}

		$params = json_decode($result);

		return isset($params->captchakeyPrivate) ? $params->captchakeyPrivate : null;
	}

	/**
	 * Get the submission
	 *
	 * @param   int  $id  The submission id
	 *
	 * @return  object|null
	 *
	 * @since   1.0.0
	 */
	public static function getSubmission($id)
	{
		global $wpdb;
		$table     = $wpdb->prefix . 'cforms_submission';
		$tableForm = $wpdb->prefix . 'cforms_form';

		$query = 'SELECT s.*, f.title as formTitle FROM ' . $table . ' AS s '
			. ' LEFT JOIN ' . $tableForm . ' AS f ON f.id = s.formId'
			. ' WHERE s.id = ' . (int) $id;

		return $wpdb->get_row($query);
	}

	/**
	 * Set the submission viewed status to true
	 *
	 * @param   int  $id  The submission id
	 *
	 * @return  boolean
	 *
	 * @since   1.0.0
	 */
	public static function setViewed($id)
	{
		global $wpdb;
		$table     = $wpdb->prefix . 'cforms_submission';

		$result = $wpdb->update(
			$table,
			array(
				'viewed' => '1'
			),
			array(
				'id' => (int) $id
			)
		);

		return (bool) $result;
	}

	/**
	 * Get the submission field value (can be array or string)
	 *
	 * @param   mixed  $value  The submission field value
	 * @param   string  $type   Optional type of file
	 *
	 * @return  string
	 *
	 * @since   1.0.0
	 */
	public static function getSubmissionValue($value, $type = '')
	{
		if (!is_array($value))
		{
			if (!empty($type) && $type == 'file')
			{
				return '<a href="' . plugin_dir_url(dirname(dirname(__FILE__))) . 'public/uploads/' . $value . '" target="_blank">' . $value . '</a>';
			}

			return __(esc_html($value), 'cforms-plugin');
		}

		$html = '';

		foreach ($value as $i => $v)
		{
			$html .= __(esc_html($v), 'cforms-plugin');

			if ($i != count($value) - 1)
			{
				$html .= ' ';
			}
		}

		return $html;
	}

	/**
	 * Is the form valid?
	 *
	 * @param   array   $fieldsData  The field data [id => value]
	 * @param   object  $form        Form
	 * @param   array   $fields      Fields
	 *
	 * @return  boolean
	 *
	 * @since   1.0.0
	 */
	public static function isValid($fieldsData, $form, $fields)
	{
		foreach ($fields as $field)
		{
			if (!$field->required)
			{
				// No need to validate
				continue;
			}

			$validators = explode(' ', $field->validator);

			// @todo dynamic
			if (in_array('required', $validators))
			{
				// @todo checkbox, select
				if (empty($fieldsData[$field->id]))
				{
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Caching fields
	 *
	 * @var     array
	 * @since   1.0.0
	 */
	private static $fieldCache = array();

	/**
	 * Get a basic preview of the submissions
	 *
	 * @param   string  $data    JSON encoded data
	 * @param   int     $formId  Form Id
	 * @param   int     $length  Length
	 *
	 * @return  string
	 *
	 * @since   1.0.0
	 */
	public static function getSubmissionPreview($data, $formId, $length = 3)
	{
		$html = array();
		$fieldValues = json_decode($data);

		// Hack to not load it multiple times
		if (!isset(self::$fieldCache[$formId]))
		{
			$fieldsApi = new CformsApiField;

			self::$fieldCache[$formId] = $fieldsApi->loadFields($formId);
		}

		$count = 0;

		foreach ($fieldValues as $key => $value)
		{
			$field = self::findField(self::$fieldCache[$formId], $key);

			if ($field->skipExport)
			{
				continue;
			}

			$html[] = __($field->label, 'cforms-plugin') . ': ' . $value;

			$count++;

			if ($count >= $length)
			{
				break;
			}

			$html[] = '- ';
		}

		return implode("\n", $html);
	}

	/**
	 * Find a field with id in an array of fields
	 *
	 * @param   array  $fields  The fields
	 * @param   int    $id      Field id
	 *
	 * @return  object|null
	 *
	 * @since   1.0.0
	 */
	public static function findField($fields, $id)
	{
		foreach ($fields as $field)
		{
			if ($field->id == $id)
			{
				return $field;
			}
		}

		return null;
	}

	/**
	 * Get the submissions
	 *
	 * @param   int     $formId   Form Id
	 * @param   string  $search   Search string for search in the Data
	 * @param   int     $offset   Offset to start
	 * @param   int     $limit    Limit the results
	 * @param   string  $orderBy  Order the results by
	 *
	 * @return  array|null|object
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function getSubmissions($formId = 0, $search = null, $offset = 0, $limit = 0, $orderBy = 'id ASC')
	{
		global $wpdb;

		$table     = $wpdb->prefix . 'cforms_submission';
		$tableForm = $wpdb->prefix . 'cforms_form';

		$where     = array();

		$query = 'SELECT s.*, f.title as formTitle FROM ' . $table . ' AS s '
			. ' LEFT JOIN ' . $tableForm . ' AS f ON f.id = s.formId';

		if (!empty($formId))
		{
			$where[] = 'formId = ' . (int) $formId;
		}

		if (!empty($search))
		{
			$search  = '%' . $wpdb->_escape($search) . '%';
			$where[] = 's.data LIKE ' . CformsDb::quote($search);
		}

		if (count($where))
		{
			$query .= ' WHERE ' . implode(' AND ', $where);
		}

		$query .= ' ORDER BY ' . sanitize_sql_orderby($orderBy);

		return $wpdb->get_results($query);
	}

	/**
	 * Delete a submission
	 *
	 * @param   int  $id  Id of the submission to delete
	 *
	 * @return  false|int
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function deleteSubmission($id)
	{
		global $wpdb;

		$table = $wpdb->prefix . 'cforms_submission';

		$query = 'DELETE FROM ' . $table . ' WHERE id = ' . (int) $id;

		return $wpdb->query($query);
	}
}
