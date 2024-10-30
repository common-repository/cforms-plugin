<?php
/**
 * @package    CForms
 * @author     Yves Hoppe <yves@compojoom.com>
 * @date       2017-04-18
 *
 * @copyright  Copyright (C) 2008 - 2017 compojoom.com - Yves Hoppe. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

class CformsApiEmail extends CformsApi
{
	protected $fields = array();

	/**
	 * Get the mail for this form
	 *
	 * @return  array
	 *
	 * @since   1.0.0
	 */
	public function _()
	{
		$id = CformsInput::getInt('id', 0);

		$email = $this->loadEmail($id);

		return array('email' => $email);
	}

	/**
	 * Get all email templates
	 *
	 * @return  array
	 *
	 * @since   1.0.0
	 */
	public function all()
	{
		global $wpdb;

		$table = $wpdb->prefix . 'cforms_email';

		$query = 'SELECT * FROM ' . $table;

		return array('data' => $wpdb->get_results($query));
	}

	/**
	 * Update the form
	 *
	 * @return  array
	 *
	 * @since   1.0.0
	 */
	public function update()
	{
		global $wpdb;

		$table = $wpdb->prefix . 'cforms_email';

		$json = CformsInput::getJsonData();

		if (empty($json))
		{
			return array('error' => true, 'message' => 'No Data');
		}

		if (!empty($json->attachments))
		{
			$json->attachments = json_encode($json->attachments);
		}
		else
		{
			$json->attachments = '';
		}

		$result = $wpdb->update(
			$table, array(
			'subject'     => $json->subject,
			'txt'        => $json->text,
			'html'        => $json->html,
			'attachments' => $json->attachments,
		),
			array(
				'id' => (int) $json->id
			)
		);

		if ($result == false)
		{
			return array('error' => true, 'message' => 'Error updating email');
		}

		return array('id' => $json->id);
	}

	/**
	 * Load email
	 *
	 * @param   int  $id  The mail id
	 *
	 * @return  mixed
	 *
	 * @since   1.0.0
	 */
	protected function loadEmail($id)
	{
		global $wpdb;

		$table = $wpdb->prefix . 'cforms_email';

		$query = 'SELECT * FROM ' . $table;

		$query .= ' WHERE id = ' . (int) $id;

		return $wpdb->get_row($query);
	}
}
