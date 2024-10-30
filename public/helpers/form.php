<?php
/**
 * @package    CForms
 * @author     Yves Hoppe <yves@compojoom.com>
 * @date       2017-04-18
 *
 * @copyright  Copyright (C) 2008 - 2017 compojoom.com - Yves Hoppe. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

class CformsForm
{
	/**
	 * Constant for not viewed submission
	 */
	const NOT_VIEWED = 0;

	/**
	 * Viewed submission
	 */
	const VIEWED = 1;

	/**
	 * Get a form based on the Id
	 *
	 * @param   int  $formId  Form Id
	 *
	 * @return  array|null|object
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws Exception
	 */
	public static function getForm($formId)
	{
		global $wpdb;

		$cformsFormTable = $wpdb->prefix . 'cforms_form';

		$query = 'SELECT * FROM ' . $cformsFormTable;

		$query .= ' WHERE id = ' . (int) $formId;

		$form = $wpdb->get_row($query);

		if (!$form)
		{
			throw new Exception('No form with this id', 404);
		}

		// JSON Decode
		$form->params = new CformsHelperRegistry(json_decode($form->params));

		return $form;
	}

	/**
	 * Get a form on the title
	 *
	 * @param   string  $title  Form title
	 *
	 * @return  array|null|object
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws Exception
	 */
	public static function getFormOnTitle($title)
	{
		global $wpdb;

		$cformsFormTable = $wpdb->prefix . 'cforms_form';

		$query = 'SELECT * FROM ' . $cformsFormTable;

		$query .= ' WHERE title = ' . CformsDb::quote($title);

		$form = $wpdb->get_row($query);

		if (!$form)
		{
			throw new Exception('No form with this title', 404);
		}

		// JSON Decode
		$form->params = new CformsHelperRegistry(json_decode($form->params));

		return $form;
	}


	/**
	 * Handle a submission
	 *
	 * @return  bool|null (Null if not called, false on error, true on success)
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws Exception
	 */
	public static function handleSubmit()
	{
		if (!isset($_POST['plugin']) || $_POST['plugin'] != 'cforms')
		{
			return null;
		}

		$nonce = $_REQUEST['_wpnonce'];

		if (!wp_verify_nonce($nonce, 'cforms_submit'))
		{
			echo 'Error Processing request';

			exit; // Get out of here, the nonce is rotten!
		}

		$formId = CformsInput::getInt('formId', 0);

		$form = self::getForm($formId);

		if (!$formId || !$form)
		{
			throw new Exception('Form not found', 404);
		}

		// Load API, we need it here
		require_once CFORMS_BASE_PATH . '/admin/autoload.php';

		$submissionData = CformsInput::getArray('field');

		$fieldsApi = new CformsApiField;
		$fields    = $fieldsApi->loadFields($formId);

		$isValid = self::isValid($submissionData, $fields);

		if (!$isValid)
		{
			return false;
		}

		self::validateRecaptcha($form);

		$files = self::uploadFiles($fields);

		// No array merge here! We need to keep the ids
		if (!empty($files))
		{
			$submissionData = $submissionData + $files;
		}

		global $wpdb;

		$table = $wpdb->prefix . 'cforms_submission';

		$submission = array(
			'uuid'      => CformsInput::getCmd('uuid', ''),
			'userId'    => get_current_user_id(),
			'formId'    => $formId,
			'data'      => json_encode($submissionData),
			'useragent' => sanitize_text_field($_SERVER['HTTP_USER_AGENT']),
			'ip'        => sanitize_text_field($_SERVER['REMOTE_ADDR']),
			'created'   => date("Y-m-d H:i:s"),
			'modified'  => date("Y-m-d H:i:s"),
			'viewed'    => self::NOT_VIEWED,
			'status'    => 1,
		);

		do_action_ref_array('cforms_before_submission_save', array($submission, $form, $fields));

		$result = $wpdb->insert(
			$table,
			$submission
		);

		if ($result)
		{
			$submission['id'] = $wpdb->insert_id;

			do_action_ref_array('cforms_after_submission_save', array($submission, $form, $fields));

			$mailHelper = new CformsEmail(
				$form,
				json_decode(json_encode($submission)),
				$fields
			);

			$success = $mailHelper->send();

			if (!$success)
			{
				// TODO
			}
		}

		return (bool) $result;
	}

	/**
	 * Validate the recaptcha
	 *
	 * @todo    Move to plugin
	 *
	 * @return  boolean
	 *
	 * @since   1.0.0
	 * @throws  Exception
	 */
	protected static function validateRecaptcha($form)
	{
		if (!CformsHelperSubmission::hasRecaptcha($form->id))
		{
			return true;
		}

		require_once CFORMS_BASE_PATH . "/lib/recaptcha/autoload.php";

		$key = CformsHelperSubmission::getRecaptchaKey($form->id);

		if (empty($key))
		{
			throw new Exception(__("You have to set a recaptcha key", 'cforms-plugin'), 500);
		}

		$recaptcha = new \ReCaptcha\ReCaptcha($key);

		$resp = $recaptcha->verify($_POST['g-recaptcha-response'], $_SERVER['REMOTE_ADDR']);

		return $resp->isSuccess();
	}

	/**
	 * Is the submitted data valid (required etc)
	 *
	 * @param   array  $fieldsData  Submission data by the user
	 * @param   array  $fields      Fields
	 *
	 * @return  bool
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected static function isValid($fieldsData, $fields)
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
	 * Upload the files
	 *
	 * @return  array|null  Array of file paths or null
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected static function uploadFiles($fields)
	{
		$fileUploadFields = array();

		foreach ($fields as $field)
		{
			if ($field->type == 'file')
			{
				$fileUploadFields[] = $field;
			}
		}

		if (empty($fileUploadFields))
		{
			return null;
		}

		$uploadedFiles = array();

		foreach ($fileUploadFields as $field)
		{
			$field->params = new CformsHelperRegistry(json_decode($field->params));

			$file = self::uploadFile(
				$field->id,
				$field->params->get('targetDirectory', 'images/cforms-uploads'),
				$field->params->get('maxFileSize', 10000),
				$field->params->get('fileExtensions', 'bmp,jpg,png,gif'),
				$field->params->get('mimeTypes', 'image/jpeg,image/gif,image/png,image/bmp,application/msword,application/excel,application/pdf,application/powerpoint,text/plain,application/x-zip')
			);

			if (!empty($file))
			{
				$uploadedFiles[$field->id] = $file;
			}
		}

		return $uploadedFiles;
	}

	/**
	 * File upload
	 *
	 * @param   string   $uploadFolder  The upload folder
	 *
	 * @throws  Exception
	 *
	 * @return  false|string  Path to the uploaded file
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function uploadFile($inputFieldName, $uploadFolder, $maxFileSize = 10000, $allowed = 'bmp,jpg,png,gif', $mimeTypes = 'image/jpeg,image/gif,image/png,image/bmp,application/msword,application/excel,application/pdf,application/powerpoint,text/plain,application/x-zip')
	{
		$folderName = dirname(dirname(__DIR__)) . "/public/uploads/" . $uploadFolder;

		if (!file_exists($folderName))
		{
			mkdir($folderName);
		}

		$files = self::sortFilesArray($_FILES);

		// Single file in this array (field[42])
		$file = $files[$inputFieldName];

		if (empty($file))
		{
			return false;
		}

		// Cleans the name of the file by removing weird characters
		$filename = self::makeFilenameSafe($file['name']);

		$allowed   = explode(',', $allowed);
		$mimeTypes = explode(',', $mimeTypes);

		$extension = pathinfo($filename, PATHINFO_EXTENSION);

		// File extension check @todo improve error messages
		if (!in_array(strtolower($extension), $allowed))
		{
			echo 'Invalid file type ' . $extension;

			return false;
		}

		if ($file['size'] >= ($maxFileSize * 1024))
		{
			// Add Error
			echo 'File too large';

			return false;
		}

		// Generate new unique filename
		$filename = strtolower(md5($filename . date('ymdhms') . uniqid()) . "." . $extension);

		$src  = $file['tmp_name'];
		$dest = $folderName . "/" . $filename;

		if (move_uploaded_file($src, $dest))
		{
			return $uploadFolder . "/" . $filename;
		}

		// Error
		return false;
	}

	/**
	 * Sort the files in an matching array
	 *
	 * @param   array  $files  Files
	 *
	 * @return  array
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function sortFilesArray($files)
	{
		$result = array();

		$fields = $files['field'];

		foreach ($fields as $name => $inner)
		{
			foreach ($inner as $id => $value)
			{
				$result[$id][$name] = $value;
			}
		}

		return $result;
	}

	/**
	 * Makes file name safe to use
	 *
	 * @param   string  $file  The name of the file [not full path]
	 *
	 * @return  string  The sanitised string
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function makeFilenameSafe($file)
	{
		// Remove any trailing dots, as those aren't ever valid file names.
		$file = rtrim($file, '.');

		$regex = array('#(\.){2,}#', '#[^A-Za-z0-9\.\_\- ]#', '#^\.#');

		return trim(preg_replace($regex, '', $file));
	}
}
