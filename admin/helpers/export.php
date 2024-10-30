<?php
/**
 * @package    CForms
 * @author     Yves Hoppe <yves@compojoom.com>
 * @date       2017-04-18
 *
 * @copyright  Copyright (C) 2008 - 2017 compojoom.com - Yves Hoppe. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

class CformsExport
{
	/**
	 * Export the given file - Looking for an $_GET action and submissionId Parameter
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws Exception
	 */
	public static function export()
	{
		$submissionIds = CformsInput::getArray('submissionId', array());
		$action        = CformsInput::getCmd('action');

		if (empty($submissionIds) || empty($action))
		{
			throw new Exception('No submission found');
		}

		if ($action == 'csv')
		{
			self::exportCsv($submissionIds);
		}
		elseif ($action == 'xml')
		{
			self::exportXml($submissionIds);
		}
		elseif ($action == 'json')
		{
			self::exportJson($submissionIds);
		}
		else
		{
			echo 'Error: Unknown Format';
		}
	}

	/**
	 * Export the CSV file
	 *
	 * @param   array  $ids  Submission Ids
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected static function exportCsv($ids)
	{
		$result = self::getEntries($ids);
		$id     = null;

		list($header, $entries) = $result;

		if (count($ids) === 1)
		{
			$id = $ids[0];
		}

		CformsHelperCsv::printCsv($header, $entries, $id);
	}

	/**
	 * Export as JSON
	 *
	 * @param   array  $ids  Submission Ids
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws Exception
	 */
	protected static function exportJson($ids)
	{
		$id = $ids[0];

		$submission = CformsHelperSubmission::getSubmission($id);

		if (!$submission)
		{
			throw new Exception('No submission with id ' . $id . ' found', 404);
		}

		// @todo implement check
		$fieldApi = new CformsApiField;
		$fields   = $fieldApi->loadFields($submission->formId);

		$data   = json_decode($submission->data, true);
		$export = array();

		foreach ($data as $key => $value)
		{
			$field = self::_findField($fields, $key);

			if (!$field)
			{
				continue;
			}

			$export[$field->label] = $value;
		}

		$fileName = 'submission-' . $id . '.json';

		header('Content-type: text/json');
		header("Content-Disposition: attachment; filename=\"" . $fileName . "\"");

		echo json_encode($export);
	}

	/**
	 * Export as XML
	 *
	 * @param   array  $ids  Submission Ids
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected static function exportXml($ids)
	{
		require_once CFORMS_BASE_PATH . 'lib/XML_Serializer/XML/Serializer.php';

		$id = $ids[0];

		$submission = CformsHelperSubmission::getSubmission($id);

		$fieldApi = new CformsApiField;
		$fields   = $fieldApi->loadFields($submission->formId);

		$data   = json_decode($submission->data, true);
		$export = array();

		foreach ($data as $key => $value)
		{
			$field = self::_findField($fields, $key);

			if (!$field)
			{
				continue;
			}

			$label = self::_getLabelAsReplacementTitle($field->label);

			$export[$label] = $value;
		}

		$options = array(
			"indent"        => '    ',
			XML_SERIALIZER_OPTION_RETURN_RESULT => true,
			"defaultTagName" => "item",
			"rootName" => "submission"
		);

		$serializer = new XML_Serializer($options);

		if ($serializer->serialize($export))
		{
			$fileName = 'submission-' . $id . '.xml';

			header('Content-type: text/xml');
			header("Content-Disposition: attachment; filename=\"" . $fileName . "\"");
			header('Pragma: no-cache');

			echo $serializer->getSerializedData();
		}
	}

	/**
	 * Export entries (mostly for CSV)
	 *
	 * @param   array   $ids   Array of Submission Ids
	 * @param   string  $type  Export Type
	 *
	 * @return  array
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected static function getEntries($ids, $type = 'csv')
	{
		$template = self::getTemplate($type);
		$entries  = array();
		$form     = null;
		$fields   = null;
		$header   = null;

		foreach ($ids as $id)
		{
			$submission = CformsHelperSubmission::getSubmission($id);

			// Prevent loading every time
			if ($form == null || $form->id != $submission->formId)
			{
				// @todo implement check
				$formApi = new CformsApiForm;
				$form    = $formApi->loadForm($submission->formId);

				$fieldApi = new CformsApiField;
				$fields   = $fieldApi->loadFields($submission->formId);
			}

			$helper = new CformsHelperTemplate(clone $template, $form, $submission, $fields);

			// First get header if any
			if (!$header)
			{
				$header = $helper->replaceHeader($template->txt);
			}

			$result = $helper->replace();

			// We only need the text column
			$entries[] = $result->txt;
		}

		return array($header, $entries);
	}

	/**
	 * Get the template
	 *
	 * @param   string  $name  Name of the Template
	 *
	 * @return  array|null|object|void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function getTemplate($name)
	{
		global $wpdb;

		$table = $wpdb->prefix . 'cforms_template';

		$query = 'SELECT * FROM ' . $table . ' WHERE title = ' . CformsDb::quote($name);

		return $wpdb->get_row($query);
	}

	/**
	 * Find a field in an array of fields
	 *
	 * @param   array  $fields  Array of Fields
	 * @param   int    $id      Id of the field to find
	 *
	 * @return  null
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	private static function _findField($fields, $id)
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
	 * Replace all unallowed characters for XML / JSON
	 *
	 * @param   string  $label  The label
	 *
	 * @return  mixed
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	private static function _getLabelAsReplacementTitle($label)
	{
		$label = str_replace(' ', '-', $label);

		return preg_replace('/[^A-Za-z0-9\-]/', '', $label);
	}
}
