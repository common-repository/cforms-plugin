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

$orderBy = CformsInput::getString('orderby', 'id ASC');
$search  = CformsInput::getString('search', '');
$page    = CformsInput::getString('page', 'cforms_submissions');

$formId = CformsInput::getInt('formId', 0);
$offset = CformsInput::getInt('offset', 0);
$limit  = CformsInput::getInt('limit', 100);

$submissions = CformsHelperSubmission::getSubmissions($formId, $search, $offset, $limit, $orderBy);
?>
<!-- Start Cforms by compojoom.com -->
<div class="compojoom-bootstrap">
	<h3><?php _e('CForms - Submissions', 'cforms-plugin'); ?></h3>

	<form action="" method="GET" id="cformsSubmissions" name="cformsSubmissions">

		<div id="cfilter" class="filter-bar clearfix">
			<label for="formId"><?php _e('Form', 'cforms-plugin'); ?> </label>
			<?php echo CformsForms::getFormSelect('formId', $formId); ?>

			<?php echo CformsTable::getSearch(); ?>
		</div>

		<div class="table-responsive">
			<table class="table table-hover table-striped">
				<thead>
				<tr>
					<th width="5"></th>
					<th>
						<?php echo CformsTable::getSortableHeaderColumn('created', 'Created'); ?>
					</th>
					<th>
						<?php echo CformsTable::getSortableHeaderColumn('formTitle', 'Form'); ?>
					</th>
					<th>
						<?php _e('Submission Preview', 'cforms-plugin'); ?>
					</th>
					<th><?php _e('CSV', 'cforms-plugin'); ?></th>
					<th><?php _e('XML', 'cforms-plugin'); ?></th>
					<th><?php _e('JSON', 'cforms-plugin'); ?></th>
					<th>
						<?php echo CformsTable::getSortableHeaderColumn('id', 'Id'); ?>
					</th>
				</tr>
				</thead>
				<?php foreach ($submissions as $i => $submission) : ?>
					<tr>
						<td>
							<input type="checkbox" name="cid[<?php echo $i; ?>]" id="cid_<?php echo $i; ?>"
							       value="<?php echo $submission->id; ?>"/>
						</td>
						<td>
							<?php echo date_i18n(get_option('date_format') . ' H:i', strtotime($submission->created)); ?>
						</td>
						<td>
							<?php echo esc_html($submission->formTitle) ?>
						</td>
						<td>
							<a href="admin.php?page=<?php echo $page; ?>&action=view&submissionId=<?php echo $submission->id; ?>">
								<?php echo $submission->viewed == 0 ? '<strong>' : ''; ?>
								<?php echo CformsHelperSubmission::getSubmissionPreview($submission->data, $submission->formId); ?>
								<?php echo $submission->viewed == 0 ? '</strong>' : ''; ?>
							</a>
							<div class="row-actions">
									<span class="edit">
										<a href="admin.php?page=<?php echo $page; ?>&action=view&submissionId=<?php echo $submission->id; ?>">
										<?php _e('View', 'cforms-plugin'); ?></a> |
									</span>
								<span class="delete">
										<?php $url = wp_nonce_url('admin.php?page=' . $page . '&action=delete&submissionId=' . $submission->id,
											'deleteSubmission'); ?>

									<a href="<?php echo $url; ?>">
											<?php _e('Delete', 'cforms-plugin'); ?></a>
									</span>
							</div>
						</td>
						<td>
							<a href="admin.php?page=<?php echo $page; ?>&action=csv&submissionId=<?php echo $submission->id; ?>">
								<i class="glyphicon glyphicon-download"></i>
							</a>
						</td>
						<td>
							<a href="admin.php?page=<?php echo $page; ?>&action=xml&submissionId=<?php echo $submission->id; ?>">
								<i class="glyphicon glyphicon-download"></i>
							</a>
						</td>
						<td>
							<a href="admin.php?page=<?php echo $page; ?>&action=json&submissionId=<?php echo $submission->id; ?>">
								<i class="glyphicon glyphicon-download"></i>
							</a>
						</td>
						<td>
							<?php echo $submission->id; ?>
						</td>
					</tr>
				<?php endforeach; ?>
			</table>
		</div>

		<div class="text-center cforms-copyright">
			<?php _e('CForms by <a href="compojoom.com">Compojoom.com</a>'); ?>
		</div>

		<input type="hidden" name="page" id="page" value="<?php echo $page; ?>">
		<input type="hidden" name="orderby" id="orderby" value="<?php echo $orderBy; ?>"/>
	</form>
</div>
<script>
	// TODO move
	jQuery(document).ready(function ($) {
		$('.sortable-header').click(function (e) {
			e.preventDefault();

			var orderBy = $(this).attr('data-orderby');

			$('#orderby').val(orderBy);

			$('#cformsSubmissions').submit();
		});

		$('#btnSearch').click(function (e) {
			e.preventDefault();

			$('#cformsSubmissions').submit();
		});

		$('#formId').change(function () {
			$('#cformsSubmissions').submit();
		})
	});
</script>
<!-- End Cforms by compojoom.com -->
