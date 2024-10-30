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
$page    = CformsInput::getString('page', 'cforms');

$offset = CformsInput::getInt('offset', 0);
$limit  = CformsInput::getInt('limit', 100);

$forms = CformsForms::getForms($offset, $limit, $search, $orderBy);
?>
<!-- Start CForms by compojoom.com -->
<div class="wrap">
	<h1 class="wp-heading-inline"><?php _e('CForms - Forms', 'cforms-plugin'); ?></h1>
	<a href="<?php echo 'admin.php?page=' . $page . '&action=new'; ?>" class="page-title-action"><?php _e('New Form', 'cforms-plugin') ?></a>
	<hr class="wp-header-end">

	<div class="compojoom-bootstrap">
		<form action="" method="GET" id="cformsForm" name="cformsForm">

			<div id="cfilter" class="filter-bar clearfix">
				<?php echo CformsTable::getSearch(); ?>
			</div>

			<div class="table-responsive">
				<table class="table table-hover table-striped">
					<thead>
					<tr>
						<th width="5"></th>
						<th>
							<?php echo CformsTable::getSortableHeaderColumn('title', 'Title'); ?>
						</th>
						<th>
							<?php echo CformsTable::getSortableHeaderColumn('pages', 'Pages'); ?>
						</th>
						<th><?php _e('Bootstrap Type', 'cforms-plugin'); ?></th>
						<th>
							<?php echo CformsTable::getSortableHeaderColumn('id', 'Id'); ?>
						</th>
					</tr>
					</thead>
					<?php foreach ($forms as $i => $form) : ?>
						<tr>
							<td>
								<input type="checkbox" name="cid[<?php echo $i; ?>]" id="cid_<?php echo $i; ?>"
								       value="<?php echo $form->id; ?>"/>
							</td>
							<td>
								<a class="row-title" href="admin.php?page=<?php echo $page; ?>&action=edit&formId=<?php echo $form->id; ?>">
									<?php echo $form->title; ?>
								</a>
								<div class="row-actions">
									<span class="edit">
										<a href="admin.php?page=<?php echo $page; ?>&action=edit&formId=<?php echo $form->id; ?>">
										<?php _e('Edit', 'cforms-plugin'); ?></a> |
									</span>
									<span class="delete">
										<?php $url = wp_nonce_url('admin.php?page=' . $page . '&action=delete&formId=' . $form->id,
											'deleteForm'); ?>

										<a href="<?php echo $url; ?>">
											<?php _e('Delete', 'cforms-plugin'); ?></a>
									</span>
								</div>
							</td>
							<td>
								<?php echo $form->pages; ?>
							</td>
							<td>
								<?php echo $form->bootstrapType ?: __('Default', 'cforms-plugin'); ?>
							</td>
							<td>
								<?php echo $form->id; ?>
							</td>
						</tr>
					<?php endforeach; ?>
				</table>
			</div>

			<div class="text-center cforms-copyright">
				<?php _e('CForms by <a href="compojoom.com">Compojoom.com</a>'); ?>
			</div>

			<input type="hidden" name="page" id="page" value="<?php echo $page; ?>" />
			<input type="hidden" name="orderby" id="orderby" value="<?php echo $orderBy; ?>" />
		</form>
	</div>
</div>

<script>
	// TODO move
	jQuery(document).ready(function ($) {
		$('.sortable-header').click(function (e) {
			e.preventDefault();
			var orderBy = $(this).attr('data-orderby');
			$('#orderby').val(orderBy);
			$('#cformsForm').submit();
		});

		$('#btnSearch').click(function (e) {
			e.preventDefault();
			$('#cformsForm').submit();
		});
	});
</script>
<!-- End CForms by compojoom.com -->
