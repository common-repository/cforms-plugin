<?php
/**
 * @package    CForms
 * @author     Yves Hoppe <yves@compojoom.com>
 * @date       2017-04-18
 *
 * @copyright  Copyright (C) 2008 - 2017 compojoom.com - Yves Hoppe. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

class CformsApiForm extends CformsApi
{
	protected $fields = array();

	/**
	 * Get all fields for this form
	 *
	 * @return  array  Data for JSON
	 *
	 * @since   1.0.0
	 */
	public function _()
	{
		$formId = CformsInput::getInt('formId', 1);

		return array('form' => array($this->loadForm($formId)));
	}

	/**
	 * Update the form
	 *
	 * @return  array  Data for JSON
	 *
	 * @since   1.0.0
	 */
	public function update()
	{
		global $wpdb;

		$cformsFormTable = $wpdb->prefix . 'cforms_form';

		$json = CformsInput::getJsonData();

		if (empty($json)  || empty($json->id))
		{
			return array('error', 'No Data');
		}

		$form = array(
			'title'         => $json->title,
			'html'          => $json->html,
			'pages'         => $json->pages,
			'bootstrapType' => $json->bootstrapType,
			'design'        => $json->design,
			'stepHeading'   => $json->stepHeading,
			'templateId'    => $json->templateId,
			'params'        => json_encode($json->params)
		);

		$result = $wpdb->update(
			$cformsFormTable, $form, array(
			'id' => $json->id
		));

		if ($result === false)
		{
			return array('error' => true, 'message' => 'Error updating form ' . $wpdb->last_query);
		}

		$form['id'] = $json->id;

		CformsPosts::updatePost($form);

		return array('id' => $json->id);
	}

	/**
	 * Update only the HTML
	 *
	 * @return  array
	 *
	 * @since   1.0.0
	 */
	public function updateHtml()
	{
		global $wpdb;

		$cformsFormTable = $wpdb->prefix . 'cforms_form';

		$json = CformsInput::getJsonData();

		if (empty($json))
		{
			return array('error', 'No Data');
		}

		$id   = $json->formId;
		$html = $json->html;

		$result = $wpdb->update($cformsFormTable, array(
			'html' => $html
		), array(
			'id' => (int) $id
		));

		return array('success' => ($result) ? true : false);
	}

	/**
	 * Load the form
	 *
	 * @param   int $formId Form Id
	 *
	 * @return  mixed|null
	 *
	 * @since   1.0.0
	 */
	public function loadForm($formId)
	{
		global $wpdb;

		$cformsFormTable = $wpdb->prefix . 'cforms_form';

		$query = 'SELECT * FROM ' . $cformsFormTable;

		$query .= ' WHERE id = ' . (int) $formId;

		return $wpdb->get_row($query);
	}
}
