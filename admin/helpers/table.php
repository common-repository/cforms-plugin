<?php
/**
 * @package    CForms
 * @author     Yves Hoppe <yves@compojoom.com>
 * @date       2017-04-18
 *
 * @copyright  Copyright (C) 2008 - 2017 compojoom.com - Yves Hoppe. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

class CformsTable
{
	/**
	 * Get a sortable header column (still needs JS)
	 *
	 * @param   string  $column  Column to order by
	 * @param   string  $title   Title of the column (translated)
	 *
	 * @return  string
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function getSortableHeaderColumn($column, $title)
	{
		$html = array();

		$orderByCurrent = CformsInput::getString('orderby', 'id ASC');
		$current = explode(' ', $orderByCurrent);

		$orderBy = $column . ' ASC';

		if ($current[0] == $column && $current[1] == 'ASC')
		{
			// Switch to DSC
			$orderBy = $column . ' DESC';
		}

		$html[] = '<a id="orderBy' . $column . '" href="#" class="sortable-header" data-orderby="' . $orderBy . '">';
		$html[] = __($title, 'cforms-plugin');
		$html[] = '</a>';

		return implode("\n", $html);
	}

	/**
	 * Get search display
	 *
	 * @return  string
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function getSearch()
	{
		$searchCurrent = CformsInput::getString('search', '');

		$html = array();

		$html[] = '<div class="cfilter filter-search pull-right">';
		$html[] = '<input type="text" name="search" id="search" placeholder="' . __('Search for', 'cforms-plugin') . '" value="' . $searchCurrent . '" />';
		$html[] = '<button id="btnSearch" class="button">' . __('Search', 'cforms-plugin') . '</button>';
		$html[] = '</div>';

		return implode("\n", $html);
	}
}
