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

$submissionId = CformsInput::getInt('submissionId', 0);
$submission   = CformsHelperSubmission::getSubmission($submissionId);

if (empty($submission))
{
	_e('No such submission', 'cforms');
	wp_die();
}

$submission->data = json_decode($submission->data);

if ($submission->viewed == 0)
{
	CformsHelperSubmission::setViewed($submissionId);
}

$fieldApi = new CformsApiField;
$fields   = $fieldApi->loadFields($submission->formId);
?>

<!-- Start CForms by compojoom.com -->
<div class="compojoom-bootstrap">
	<h3><?php _e('CForms - View Submission', 'cforms-plugin'); ?></h3>

	<div id="table-responsive">
		<table class="table table-hover table-striped">
			<tr>
				<td class="key" style="width: 200px">
					<?php _e('Created', 'cforms-plugin'); ?>
				</td>
				<td>
					<?php echo $submission->created; ?>
				</td>
			</tr>
			<tr>
				<td class="key">
					<?php _e('Form', 'cforms-plugin'); ?>
				</td>
				<td>
					<?php echo esc_html($submission->formTitle); ?>
				</td>
			</tr>

			<?php
			foreach ($submission->data as $fieldId => $value)
			{
				$field = null;

				for ($i = 0; $i < count($fields); $i++)
				{
					if ($fields[$i]->id == $fieldId)
					{
						$field = $fields[$i];

						break;
					}
				}

				if (empty($field))
				{
					_e('Field ' . $fieldId . ' not found', 'cforms-plugin');
					continue;
				}

				$label = empty($field->label) ? '' : esc_html($field->label);
				?>
				<tr>
					<td class="key">
						<?php _e($label, 'cforms-plugin'); ?>
					</td>
					<td>
						<?php echo CformsHelperSubmission::getSubmissionValue($value, $field->type); ?>
					</td>
				</tr>
				<?php
			}
			?>
			<tr>
				<td class="key">
					<?php _e('IP', 'cforms-plugin'); ?>
				</td>
				<td>
					<?php echo esc_html($submission->ip); ?>
				</td>
			</tr>
			<tr>
				<td class="key">
					<?php _e('User Agent', 'cforms-plugin'); ?>
				</td>
				<td>
					<?php echo esc_html($submission->useragent); ?>
				</td>
			</tr>
			<tr>
				<td class="key">
					<?php _e('Submission Id', 'cforms-plugin'); ?>
				</td>
				<td>
					<?php echo $submission->id; ?>
				</td>
			</tr>

		</table>
	</div>

	<div class="text-center cforms-copyright">
		<?php _e('CForms by <a href="compojoom.com">Compojoom.com</a>', 'cforms-plugin'); ?>
	</div>
</div>
<!-- End CForms by compojoom.com -->
