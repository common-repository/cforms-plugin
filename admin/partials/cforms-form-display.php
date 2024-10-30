<?php
/**
 * @package    CForms
 * @author     Yves Hoppe <yves@compojoom.com>
 * @date       2017-04-18
 *
 * @copyright  Copyright (C) 2008 - 2017 compojoom.com - Yves Hoppe. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('ABSPATH') or exit;

$formId = CformsInput::getInt('id', 0);

CformsBehavior::loadLanguage();
?>
<!-- Start CForms by compojoom.com -->
<div class="compojoom-bootstrap">
	<cforms>
		<div id="loading-dialog">
			<div id="loading-text">
				<h3>
					<img src="<?php echo plugin_dir_url(dirname(__FILE__)); ?>images/spinning_wheel.gif"
					     style="height: 50px;"/>
					<?php _e('Loading CForms...', 'cforms-plugin'); ?>
				</h3>
			</div>
		</div>
	</cforms>

	<div class="text-center cforms-copyright">
		<?php _e('CForms by <a href="compojoom.com">Compojoom.com</a>'); ?>
	</div>
</div>

<script>
	// Expose paths to Angular 2
	var cformsBase = "<?php echo plugin_dir_url(dirname(__FILE__)); ?>";
	var cms = 'wordpress';
	var cformsMappings = <?php echo json_encode(CformsHelperMappings::get()); ?>;

	// Faking translations
	var Joomla = {
		JText: {
			_: function (text) {
				if (typeof cformsLanguage[text] !== 'undefined') {
					return cformsLanguage[text];
				}

				// Fallback
				return text;
			}
		}
	};
</script>
<!-- End CForms by compojoom.com -->
