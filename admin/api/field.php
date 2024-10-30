<?php
/**
 * @package    CForms
 * @author     Yves Hoppe <yves@compojoom.com>
 * @date       2017-04-18
 *
 * @copyright  Copyright (C) 2008 - 2017 compojoom.com - Yves Hoppe. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

class CformsApiField extends CformsApi
{
	/**
	 * Get all fields for this form
	 *
	 * @return  array
	 *
	 * @since   1.0.0
	 */
	public function _()
	{
		$formId = CformsInput::getInt('formId', 1);

		return array('fields' => $this->loadFields($formId));
	}

	/**
	 * Save a new field
	 *
	 * @return  array
	 *
	 * @since   1.0.0
	 */
	public function create()
	{
		global $wpdb;

		$table = $wpdb->prefix . 'cforms_field';

		$json = CformsInput::getJsonData();

		if (empty($json))
		{
			return array('error', 'No Data');
		}

		// ORM need
		$result = $wpdb->insert(
			$table, array(
			'formId'       => $json->formId,
			'page'         => $json->page,
			'type'         => $json->type,
			'component'    => $json->component,
			'label'        => $json->label,
			'options'      => $json->options,
			'defaultValue' => $json->defaultValue,
			'placeholder'  => $json->placeholder,
			'sizeXs'       => $json->sizeXs,
			'sizeSm'       => $json->sizeSm,
			'sizeMd'       => $json->sizeMd,
			'sizeLg'       => $json->sizeLg,
			'hidden'       => $json->hidden,
			'cssClass'     => $json->cssClass,
			'style'        => $json->style,
			'required'     => $json->required,
			'showLabel'    => $json->showLabel,
			'validator'    => $json->validator,
			'ordering'     => $json->ordering,
			'mapping'      => $json->mapping,
			'params'       => $json->params,
			'conditions'   => $json->conditions,
			'skipExport'   => $json->skipExport,
			'status'       => $json->status,
			)
		);

		// Result is number of rows
		if ($result === false)
		{
			return array('error' => true, 'message' => 'Could not insert');
		}

		$lastId = $wpdb->insert_id;

		return array('id' => $lastId);
	}

	/**
	 * Duplicate a single field
	 *
	 * @return  array
	 *
	 * @since   1.0.0
	 */
	public function duplicate()
	{
		global $wpdb;

		$table = $wpdb->prefix . 'cforms_field';

		$fieldId = CformsInput::getJsonData();

		if (empty($fieldId))
		{
			return array('error', 'No Data');
		}

		$query = 'SELECT * FROM ' . $table . ' WHERE id = ' . (int) $fieldId;

		$duplicateField = $wpdb->get_row($query);

		if (empty($duplicateField))
		{
			return array('error', 'No Field');
		}

		$duplicateField->id       = null;
		$duplicateField->ordering = $this->getHighestOrdering() + 1;
		$duplicateField->label    = $duplicateField->label . ' (COPY)';

		$result = $wpdb->insert($table, json_decode(json_encode($duplicateField), true));

		if ($result === false)
		{
			return array('error' => true, 'message' => 'Could not insert');
		}

		$lastId = $wpdb->insert_id;

		return array('id' => $lastId);
	}

	/**
	 * Update a new field
	 *
	 * @return  array
	 *
	 * @since   1.0.0
	 */
	public function update()
	{
		global $wpdb;

		$table = $wpdb->prefix . 'cforms_field';

		$json = CformsInput::getJsonData();

		if (empty($json))
		{
			return array('error', 'No Data');
		}

		// ORM need
		$result = $wpdb->update(
			$table, array(
				'formId'       => (int) $json->formId,
				'page'         => (int) $json->page,
				'type'         => $json->type,
				'component'    => $json->component,
				'label'        => $json->label,
				'options'      => json_encode($json->options),
				'defaultValue' => $json->defaultValue,
				'placeholder'  => $json->placeholder,
				'sizeXs'       => $json->sizeXs,
				'sizeSm'       => $json->sizeSm,
				'sizeMd'       => $json->sizeMd,
				'sizeLg'       => $json->sizeLg,
				'hidden'       => json_encode($json->hidden),
				'cssClass'     => $json->cssClass,
				'style'        => $json->style,
				'required'     => $json->required,
				'showLabel'    => $json->showLabel,
				'validator'    => $json->validator,
				'ordering'     => $json->ordering,
				'mapping'      => json_encode($json->mapping),
				'params'       => json_encode($json->params),
				'conditions'   => json_encode($json->conditions),
				'skipExport'   => $json->skipExport,
				'status'       => $json->status,
			),
			array(
				'id' => (int) $json->id
			)
		);

		if ($result === false)
		{
			return array('error' => true, 'message' => 'Error updating field ' . $wpdb->last_query);
		}

		return array('id' => $json->id);
	}

	/**
	 * Delete a single field
	 *
	 * @return  array
	 *
	 * @since   1.0.0
	 */
	public function delete()
	{
		global $wpdb;

		$table = $wpdb->prefix . 'cforms_field';

		$fieldId = CformsInput::getJsonData();

		if (empty($fieldId))
		{
			return array('error', 'No Field Id');
		}

		$result = $wpdb->delete($table, array(
			'id' => (int) $fieldId
		));

		return array('result' => $result);
	}

	/**
	 * Delete fields from a page (given as json parameter)
	 *
	 * @return  array
	 *
	 * @since   1.0.0
	 */
	public function deletePage()
	{
		global $wpdb;

		$table = $wpdb->prefix . 'cforms_field';

		$page = CformsInput::getJsonData();

		if (empty($page))
		{
			return array('error', 'No Field Id');
		}

		$result = $wpdb->delete($table, array(
			'page' => (int) $page
		));

		return array('result' => $result);
	}

	/**
	 * Load fields
	 *
	 * @param   int  $formId  Form Id
	 *
	 * @return  mixed  Array of objects
	 *
	 * @since   1.0.0
	 */
	public function loadFields($formId)
	{
		global $wpdb;

		$table = $wpdb->prefix . 'cforms_field';

		$query = 'SELECT * FROM ' . $table;

		$query .= ' WHERE formId = ' . (int) $formId;

		return $wpdb->get_results($query);
	}

	/**
	 * Get the highest ordering count
	 *
	 * @return  int
	 *
	 * @since   1.0.0
	 */
	protected function getHighestOrdering()
	{
		global $wpdb;

		$table = $wpdb->prefix . 'cforms_field';

		$query = 'SELECT MAX(ordering) FROM ' . $table;

		return (int) $wpdb->get_var($query);
	}
}
