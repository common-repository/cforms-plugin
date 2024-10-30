<?php
/**
 * @package    CForms
 * @author     Yves Hoppe <yves@compojoom.com>
 * @date       2017-04-18
 *
 * @copyright  Copyright (C) 2008 - 2017 compojoom.com - Yves Hoppe. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

class CformsHelperCsv
{
	/**
	 * Get the replacement for CSV submission details simple placeholder
	 *
	 * @param   array  $submissionData  The submission Data
	 *
	 * @return  mixed
	 *
	 * @since   1.0.0
	 */
	public static function getCsvAllTemplateReplacement($submissionData)
	{
		$row = array();

		foreach ($submissionData as $entry)
		{
			$value = $entry['value'];

			if (is_array($value))
			{
				$value = implode(', ', $value);
			}

			$row[] = self::_delimitValue($value);
		}

		return implode(self::_getSeparator(), $row);
	}

	/**
	 * Get the replacement header for CSV submission details simple placeholder
	 *
	 * @param   array  $submissionData  The submission Data
	 *
	 * @return  mixed
	 *
	 * @since   1.0.0
	 */
	public static function getCsvAllTemplateReplacementHeader($submissionData)
	{
		$header = array();

		foreach ($submissionData as $entry)
		{
			$label = $entry['label'];

			$header[] = self::_delimitValue($label);
		}

		return implode(self::_getSeparator(), $header);
	}

	/**
	 * Delimit / CSV Escape value
	 *
	 * @param   string  $value  The value
	 *
	 * @return  string
	 *
	 * @since   1.0.0
	 */
	private static function _delimitValue($value)
	{
		$delimiter = CformsHelperSettings::_('csv_delimiter', "'");

		return $delimiter . $value . $delimiter;
	}

	/**
	 * Get the CSV separator char
	 *
	 * @return  string
	 *
	 * @since   1.0.0
	 */
	private static function _getSeparator()
	{
		return CformsHelperSettings::_('csv_separator', ";");
	}

	/**
	 * Print the CSV file
	 *
	 * @param   string  $header   CSV header
	 * @param   array   $entries  Rows
	 * @param   int     $id       Submission id
	 * @param   int     $formId   Form Id
	 *
	 * @todo improve and separate
	 *
	 * @return  string
	 *
	 * @since   1.0.0
	 */
	public static function printCsv($header, $entries, $id = null, $formId = null)
	{
		$charset = CformsHelperSettings::_('csv_charset', 'UTF-8');

		header("Content-Encoding: " . $charset);
		header("Content-Type: text/csv; charset=" . $charset);

		$filename = self::_getFilename($entries, $id, $formId);

		header("Content-Disposition: attachment; filename=\"" . $filename . "\"");
		header('Pragma: no-cache');

		$csvdata = $header;

		foreach ($entries as $row)
		{
			$csvdata .= "\n";
			$csvdata .= $row;
		}

		if (function_exists("iconv") && $charset != 'UTF-8')
		{
			$csvdata = iconv("UTF-8", $charset, $csvdata);
		}
		else
		{
			// TODO add error
		}

		echo $csvdata;
	}

	/**
	 * Get the filename
	 *
	 * @param   array  $entries  The entries
	 * @param   int    $id       Optional id
	 * @param   int    $formId   Optional form id
	 *
	 * @return  string
	 *
	 * @since   1.0.0
	 */
	protected static function _getFilename($entries, $id = null, $formId = null)
	{
		if (count($entries) > 1)
		{
			$filename = "submissions-";

			if ($formId)
			{
				$filename .= $formId . '-';
			}

			$filename .= date('Y-m-d');
		}
		else
		{
			$filename = "submission-";

			if ($id)
			{
				$filename .= $id . '-';
			}

			$filename .= date('Y-m-d');
		}

		$filename .= ".csv";

		return $filename;
	}
}
