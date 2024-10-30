<?php
/**
 * @package    CForms
 * @author     Yves Hoppe <yves@compojoom.com>
 * @date       2017-04-18
 *
 * @copyright  Copyright (C) 2008 - 2017 compojoom.com - Yves Hoppe. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

class CformsHelperTemplate
{
	/**
	 * The template
	 *
	 * @var     object
	 * @since   1.0.0
	 */
	protected $template;

	/**
	 * The form
	 *
	 * @var     object
	 * @since   1.0.0
	 */
	protected $form;

	/**
	 * The fields
	 *
	 * @var     object
	 * @since   1.0.0
	 */
	protected $fields;

	/**
	 * Submission Object
	 *
	 * @var     object
	 * @since   1.0.0
	 */
	protected $submission;

	/**
	 * Submission data (json decoded)
	 *
	 * @var     array
	 * @since   1.0.0
	 */
	protected $submissionData = array();

	/**
	 * Replacements
	 *
	 * @var     object
	 * @since   1.0.0
	 */
	protected $replacements = null;

	/**
	 * Headers
	 *
	 * @var    object
	 * @since  1.0.0
	 */
	protected static $replacementsHeader = null;

	/**
	 * CformsHelperTemplate constructor.
	 *
	 * @param   object  $template    Template object (subject, txt, html)
	 * @param   object  $form        Form
	 * @param   object  $submission  Submission
	 * @param   object  $fields      Fields
	 *
	 * @throws  Exception if now fields
	 *
	 * @since   1.0.0
	 */
	public function __construct($template, $form = null, $submission = null, $fields = null)
	{
		$this->template   = $template;
		$this->form       = $form;
		$this->submission = $submission;
		$this->fields     = $fields;

		if (!$this->fields)
		{
			throw new Exception('Fields are required');
		}

		$this->getSubmissionDataReplacements();
		$this->getReplacements();

		self::getReplacementHeaders($submission, $this->submissionData);
	}

	/**
	 * Generate Replacements (singleton)
	 *
	 * @return  array|object
	 *
	 * @since   1.0.0
	 */
	protected function getReplacements()
	{
		// Form
		$this->replacements['FORM_TITLE'] = __($this->form->title);
		$this->replacements['FORM_ID']    = $this->form->id;
		$this->replacements['FORM_PAGES'] = $this->form->pages;

		if (isset($this->form->params->emailRecipients))
		{
			$this->replacements['FORM_RECIPIENTS'] = $this->form->params->emailRecipients;
		}

		// Submission
		$this->replacements['SUB_USER_ID'] = $this->submission->userId;

		$this->replacements['SUB_ID']        = $this->submission->id;
		$this->replacements['SUB_IP']        = $this->submission->ip;
		$this->replacements['SUB_USERAGENT'] = $this->submission->useragent;
		$this->replacements['SUB_CREATED']   = $this->submission->created;
		$this->replacements['SUB_MODIFIED']  = $this->submission->modified;

		$this->replacements['SUB_DATA'] = $this->getSubmissionDataPlaceholder();

		// CSV automatic placeholder
		$this->replacements['SUB_CSV'] = CformsHelperCsv::getCsvAllTemplateReplacement($this->submissionData);

		return $this->replacements;
	}

	/**
	 * Replace in text
	 *
	 * @since  1.0.0
	 *
	 * @return  object (The template)
	 */
	public function replace()
	{
		foreach ($this->replacements as $key => $replace)
		{
			if (is_array($replace))
			{
				$replace = implode(', ', $replace);
			}

			$key                 = '{{' . $key . '}}';
			$this->template->txt = str_replace($key, $replace, $this->template->txt);

			// Export templates don't have html column
			if (isset($this->template->html))
			{
				$this->template->html = str_replace($key, $replace, $this->template->html);
			}

			$this->template->subject = str_replace($key, $replace, $this->template->subject);
		}

		return $this->template;
	}

	/**
	 * Get Headers
	 *
	 * @param   object  $submission      Submission
	 * @param   array   $submissionData  Submission Data
	 *
	 * @return  array|object
	 *
	 * @since   1.0.0
	 */
	public static function getReplacementHeaders($submission = null, $submissionData = null)
	{
		if (self::$replacementsHeader)
		{
			return self::$replacementsHeader;
		}

		// Create headers
		$header = array();

		$header['SUB_CSV'] = CformsHelperCsv::getCsvAllTemplateReplacementHeader($submissionData);
		$header['SUB_USER_ID'] = 'UserId';

		self::$replacementsHeader = $header;

		return self::$replacementsHeader;
	}

	/**
	 * Replace in header
	 *
	 * @param   string  $text  The text
	 *
	 * @return  mixed
	 *
	 * @since   1.0.0
	 */
	public function replaceHeader($text)
	{
		foreach (self::$replacementsHeader as $key => $value)
		{
			$key = '{{' . $key . '}}';

			$text = str_replace($key, $value, $text);
		}

		return $text;
	}

	/**
	 * Get the data replacements
	 *
	 * @return  array
	 *
	 * @since   1.0.0
	 */
	protected function getSubmissionDataReplacements()
	{
		$data = json_decode($this->submission->data, true);

		foreach ($data as $key => $value)
		{
			$field = $this->_findField($key);

			if ($field->skipExport || empty($field->label))
			{
				continue;
			}

			$value = CformsHelperSubmission::getSubmissionValue($value);

			// Once by number
			$this->replacements['SUB_FIELD_' . $key]                                        = $value;
			$this->replacements['SUB_' . $this->_getLabelAsReplacementTitle($field->label)] = $value;

			$this->submissionData[$key] = array('label' => $field->label, 'value' => $value);
		}

		return $this->submissionData;
	}

	/**
	 * Replacement for SUB_DATA
	 *
	 * @return  string
	 *
	 * @since   1.0.0
	 */
	protected function getSubmissionDataPlaceholder()
	{
		if (!$this->submission)
		{
			return '';
		}

		$data = json_decode($this->submission->data, true);

		$html = array();
		$html[] = '<table class="table" style="width: 100%">';

		foreach ($data as $key => $value)
		{
			$field = $this->_findField($key);

			if ($field->skipExport)
			{
				continue;
			}

			$html[] = '<tr>';
			$html[] = '<td style="width: 200px">';
			$html[] = '<strong>' . __($field->label, 'cforms-plugin') . '</strong>';
			$html[] = '</td>';
			$html[] = '<td>';

			$html[] = CformsHelperSubmission::getSubmissionValue($value);
			$html[] = '</td>';
			$html[] = '</tr>';
		}

		$html[] = '</table>';

		return implode("\n", $html);
	}

	/**
	 * Find a field with id in an array of fields
	 *
	 * @param   int  $id  Field id
	 *
	 * @return  object|null
	 *
	 * @since   1.0.0
	 */
	private function _findField($id)
	{
		foreach ($this->fields as $field)
		{
			if ($field->id == $id)
			{
				return $field;
			}
		}

		return null;
	}

	/**
	 * Get the label as Placeholder
	 *
	 * @param   string  $label  Replacement Label
	 *
	 * @return  string
	 *
	 * @since   1.0.0
	 */
	private function _getLabelAsReplacementTitle($label)
	{
		$label = str_replace(' ', '_', $label);

		return preg_replace('/[^A-Za-z0-9\-_]/', '', strtoupper($label));
	}
}
