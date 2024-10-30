<?php
/**
 * @package    CForms
 * @author     Yves Hoppe <yves@compojoom.com>
 * @date       2017-04-18
 *
 * @copyright  Copyright (C) 2008 - 2017 compojoom.com - Yves Hoppe. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

class CformsEmail
{
	/**
	 * The mail template
	 *
	 * @var     object
	 * @since   1.0.0
	 */
	protected $mailTemplate = null;

	/**
	 * The form
	 *
	 * @var     object
	 * @since   1.0.0
	 */
	protected $form = null;

	/**
	 * The submission
	 *
	 * @var     object
	 * @since   1.0.0
	 */
	protected $submission = null;

	/**
	 * Recipients
	 *
	 * @var     array
	 * @since   1.0.0
	 */
	protected $recipients = array();

	/**
	 * The template helper for sending mails
	 *
	 * @var     CformsHelperTemplate|null
	 * @since   1.0.0
	 */
	protected $templateHelper = null;

	/**
	 * The mail body
	 *
	 * @var     string
	 * @since   1.0.0
	 */
	protected $body = null;

	/**
	 * CformsHelperEmail constructor.
	 *
	 * @param   object  $form        Form object
	 * @param   object  $submission  Submission
	 * @param   array   $fields      Fields
	 *
	 * @since   1.0.0
	 */
	public function __construct($form, $submission, $fields)
	{
		$this->mailTemplate   = self::getMailTemplate($form->templateId);
		$this->recipients     = explode(';', $form->params->emailRecipients);
		$this->templateHelper = new CformsHelperTemplate($this->mailTemplate, $form, $submission, $fields);
	}

	/**
	 * Send the submission mail to the recipients
	 *
	 * @return  bool  true on success
	 *
	 * @since   1.0.0
	 */
	public function send()
	{
		// Nothing to send
		if (!count($this->recipients))
		{
			return true;
		}

		// Replace placeholders
		$this->templateHelper->replace();

		$html = self::getHtmlMailSkeleton();
		$html .= $this->mailTemplate->html;
		$html .= self::getHtmlMailSkeletonClose();

		$headers = array('Content-Type: text/html; charset=UTF-8');

		$success = wp_mail(
			implode($this->recipients, ';'),
			$this->mailTemplate->subject,
			$html,
			$headers
		);

		if (!$success)
		{
			// TODO add message to the user
		}

		return $success;
	}

	/**
	 * Get the email template
	 *
	 * @param   int  $id  The mail template id
	 *
	 * @return  mixed
	 *
	 * @since   1.0.0
	 */
	public static function getMailTemplate($id)
	{
		global $wpdb;
		$table = $wpdb->prefix . 'cforms_email';

		$query = 'SELECT * FROM ' . $table . ' WHERE id = ' . (int) $id;

		return $wpdb->get_row($query);
	}

	/**
	 * Generates the html skeleton for the emails send
	 *
	 * @return  string
	 *
	 * @since   1.0.0
	 */
	public static function getHtmlMailSkeleton()
	{
		$body = array();

		// Doctype XHTML, still the most compatible one
		$body[] = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional //EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
		$body[] = '<html>';
		$body[] = '<head>';
		$body[] = '<style type=\"text/css\">';
		$body[] = 'html, body {';
		$body[] = CformsHelperSettings::getSetting("email_fontfamily", "font-family: Verdana, Tahoma, Arial;");
		$body[] = CformsHelperSettings::getSetting("email_fontsize", "font-size:14px;");
		$body[] = '}';
		$body[] = CformsHelperSettings::getSetting("email_css", "");
		$body[] = '</style>';
		$body[] = '</head>';
		$body[] = '<body>';

		return implode("\n", $body);
	}

	/**
	 * Generates the html skeleton close for the emails send
	 *
	 * @return  string
	 *
	 * @since   1.0.0
	 */
	public static function getHtmlMailSkeletonClose()
	{
		$body = array();

		$body[] = '</body>';
		$body[] = '</html>';

		return implode("\n", $body);
	}
}
