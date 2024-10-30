<?php
/**
 * @package    CForms
 * @author     Yves Hoppe <yves@compojoom.com>
 * @date       2017-04-18
 *
 * @copyright  Copyright (C) 2008 - 2017 compojoom.com - Yves Hoppe. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

class CformsBehavior
{
	/**
	 * Load Angular and CSS
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function loadAngular()
	{
		$base = plugin_dir_url(dirname(__FILE__));

		wp_enqueue_script('cforms_tinymce', $base . 'js/vendor/tinymce/tinymce.min.js', array(), CFORMS_VERSION);
		wp_enqueue_script('cforms_shim', $base . 'js/vendor/es6-shim/es6-shim.min.js', array(), CFORMS_VERSION);
		wp_enqueue_script('cforms_zone', $base . 'js/vendor/zone.js/dist/zone.js', array(), CFORMS_VERSION);
		wp_enqueue_script('cforms_reflect', $base . 'js/vendor/reflect-metadata/Reflect.js', array(), CFORMS_VERSION);
		wp_enqueue_script('cforms_polyfills', $base . 'js/vendor/systemjs/dist/system-polyfills.js', array(), CFORMS_VERSION);
		wp_enqueue_script('cforms_system', $base . 'js/vendor/systemjs/dist/system.src.js', array(), CFORMS_VERSION);
		wp_enqueue_script('cforms_app', $base . 'js/app/main.min.js', array(), CFORMS_VERSION);

		wp_enqueue_style('cforms_css_dragular', $base . 'app/css/dragula.min.css', array(), CFORMS_VERSION);
		wp_enqueue_style('cforms_css_canvas', $base . 'app/css/canvas.css', array(), CFORMS_VERSION);
		wp_enqueue_style('cforms_css_forms', $base . 'app/css/cforms.css', array(), CFORMS_VERSION);
		wp_enqueue_style('cforms_css_menu', $base . 'app/css/menu.css', array(), CFORMS_VERSION);
		wp_enqueue_style('cforms_css_layout', $base . 'app/css/layout.css', array(), CFORMS_VERSION);
		wp_enqueue_style('cforms_css_fa', $base . 'css/font-awesome.min.css', array(), CFORMS_VERSION);
	}

	/**
	 * Load the language base file
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function loadLanguage()
	{
		$base = plugin_dir_path(dirname(dirname(__FILE__)));

		$languageStrings = parse_ini_file($base . 'languages/en-GB.com_cforms.js.ini');

		$languageJs = array();

		foreach ($languageStrings as $key => $value)
		{
			$languageJs[$key] = __($value, 'cforms-plugin');
		}

		echo '<script>';
		echo 'var cformsLanguage = ' . json_encode($languageJs);
		echo '</script>';
	}

	/**
	 * Generates a Universally Unique Identifier, version 4. (truly random UUID)
	 *
	 * @param   bool  $hex  - If TRUE return the uuid in hex format, otherwise as a string
	 *
	 * @see http://tools.ietf.org/html/rfc4122#section-4.4
	 * @see http://en.wikipedia.org/wiki/UUID
	 *
	 * @return string - A UUID, made up of 36 characters or 16 hex digits.
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public static function getUuid($hex = false)
	{
		$pr_bits = false;

		if (!$pr_bits)
		{
			$fp = @fopen('/dev/urandom', 'rb');

			if ($fp !== false)
			{
				$pr_bits .= @fread($fp, 16);
				@fclose($fp);
			}
			else
			{
				// If /dev/urandom isn't available (eg: in non-unix systems), use mt_rand().
				$pr_bits = "";

				for ($cnt = 0; $cnt < 16; $cnt++)
				{
					$pr_bits .= chr(mt_rand(0, 255));
				}
			}
		}

		$time_low = bin2hex(substr($pr_bits, 0, 4));
		$time_mid = bin2hex(substr($pr_bits, 4, 2));
		$time_hi_and_version = bin2hex(substr($pr_bits, 6, 2));
		$clock_seq_hi_and_reserved = bin2hex(substr($pr_bits, 8, 2));
		$node = bin2hex(substr($pr_bits, 10, 6));

		/**
		 * Set the four most significant bits (bits 12 through 15) of the
		 * time_hi_and_version field to the 4-bit version number from
		 * Section 4.1.3.
		 * @see http://tools.ietf.org/html/rfc4122#section-4.1.3
		 */
		$time_hi_and_version = hexdec($time_hi_and_version);
		$time_hi_and_version = $time_hi_and_version >> 4;
		$time_hi_and_version = $time_hi_and_version | 0x4000;

		/**
		 * Set the two most significant bits (bits 6 and 7) of the
		 * clock_seq_hi_and_reserved to zero and one, respectively.
		 */
		$clock_seq_hi_and_reserved = hexdec($clock_seq_hi_and_reserved);
		$clock_seq_hi_and_reserved = $clock_seq_hi_and_reserved >> 2;
		$clock_seq_hi_and_reserved = $clock_seq_hi_and_reserved | 0x8000;

		// Either return as hex or as string
		$format = $hex ? '%08s%04s%04x%04x%012s' : '%08s-%04s-%04x-%04x-%012s';

		return sprintf($format, $time_low, $time_mid, $time_hi_and_version, $clock_seq_hi_and_reserved, $node);
	}
}
