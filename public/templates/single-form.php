<?php
/**
 * @package    CForms
 * @author     Yves Hoppe <yves@compojoom.com>
 * @date       2017-04-18
 *
 * @copyright  Copyright (C) 2008 - 2017 compojoom.com - Yves Hoppe. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

if (!defined('ABSPATH'))
{
	exit;
} // Exit if accessed directly


global $post;

/** @var bool|null $submitResult true (success), false (error), null (not called) */
$submitResult  = CformsForm::handleSubmit();
$shortCodeMode = false;

// Get the form
if (isset($formId))
{
	$form          = CformsForm::getForm($formId);
	$shortCodeMode = true;
}
elseif (is_numeric($post->post_name))
{
	$formId = (int) $post->post_name;
	$form   = CformsForm::getForm($formId);
}
elseif (is_numeric($formId = str_replace('form-', '', $post->name)))
{
	$form = CformsForm::getForm($formId);
}
else
{
	// Get Form on post title
	$form   = CformsForm::getFormOnTitle($post->post_title);
	$formId = (int) $form->id;
}

if (empty($form))
{
	echo '<h3>Error loading form with the Id ' . $formId . ' - Check your settings</h3>';
	return;
}


$baseAdmin    = dirname(dirname(__FILE__));
$baseFrontend = dirname(__FILE__);

wp_enqueue_style('cforms_bs', plugin_dir_url($baseAdmin) . 'admin/css/bootstrap-3.1.1.css', array(), CFORMS_VERSION, 'all');
wp_enqueue_style('cforms_css_magnific', plugin_dir_url($baseFrontend) . 'css/magnific-popup.css', array(), CFORMS_VERSION);
wp_enqueue_style('cforms_css_form', plugin_dir_url($baseFrontend) . 'css/form.css', array(), CFORMS_VERSION);

wp_enqueue_script('jquery');
wp_enqueue_script('cforms_js_bs3', plugin_dir_url($baseFrontend) . 'js/bootstrap.min.js', array(), CFORMS_VERSION);
wp_enqueue_script('cforms_js_magnific', plugin_dir_url($baseFrontend) . 'js/jquery.magnific-popup.js', array(), CFORMS_VERSION);
wp_enqueue_script('cforms_jsform', plugin_dir_url($baseFrontend) . 'js/form.jquery.js', array(), CFORMS_VERSION);

$stepHeading = $form->stepHeading;
$design      = $form->design;

if ($stepHeading != 'none' )
{
	wp_enqueue_style('cforms_css_stepheading_' . $stepHeading, plugin_dir_url($baseAdmin) . 'admin/app/css/stepheading/' . $stepHeading . '.css', array(), CFORMS_VERSION);
}

if ($design != 'none')
{
	wp_enqueue_style('cforms_css_fontawesome', plugin_dir_url($baseAdmin) . 'admin/css/font-awesome.min.css', array(), CFORMS_VERSION, 'all');
	wp_enqueue_style('cforms_css_design_' . $design, plugin_dir_url($baseAdmin) . 'admin/app/css/design/' . $design . '.css', array(), CFORMS_VERSION);
	wp_enqueue_script('cforms_js_design_' . $design, plugin_dir_url($baseFrontend) . 'js/design/' . $design . '.js', array(), CFORMS_VERSION);
}

$fields = array();

if ($form->params->get('confirmationPage', false))
{
	$fieldsApi = new CformsApiField;
	$fields    = $fieldsApi->loadFields($form->id);
}

$showLogin = $form->params->get('formLogin', false) && get_current_user_id() == 0;

$designParameter = $form->params->get('design', false);

if ($designParameter)
{
	$designParameter = empty($designParameter->$design) ? false : $designParameter->$design;
}

/**
 * Get a custom header-cforms.php file, if it exists.
 * Otherwise, get default header.
 */
if (!$shortCodeMode)
{
	get_header('cforms');
}
?>
	<div class="compojoom-bootstrap clearfix">
		<?php echo $shortCodeMode ? '<div>' : '<div class="wrap">'; ?>
		<h3><?php _e($form->title, 'cforms-plugin'); ?></h3>

		<form action=""
		      name="cformsForm"
		      id="cformsForm_<?php echo $form->id ?>"
		      method="post"
		      class="form-validate <?php echo $form->bootstrapType; ?> <?php echo $design != 'none' ? $design : '' ?>"
		      enctype="multipart/form-data">

			<?php if ($submitResult === true) : ?>
				<div id="success-container" class="alert alert-info">
					<?php _e('Form was submitted successfully', 'cforms-plugin'); ?>
				</div>
			<?php elseif ($submitResult === false): ?>
				<div id="error-container" class="alert alert-warning">
					<?php _e('There was an error during the form submission', 'cforms-plugin'); ?>
				</div>
			<?php endif; ?>

			<div id="error-container" class="alert alert-warning" style="display: none;">
				<?php _e('Please fill out the following fields:', 'cforms-plugin'); ?>
				<div id="form-errors">
				</div>
			</div>

			<div class="cforms_draw">
				<div class="cforms-content">
					<?php echo $form->html; ?>

					<?php if ($form->params->get('confirmationPage', false)) : ?>
						<div class="cforms-page cforms-page-confirmation">
							<div class="row">
								<div class="col-md-12">
									<h3><?php _e('Confirmation', 'cforms-plugin'); ?></h3>
								</div>

								<?php foreach ($fields as $field): ?>
									<?php
									// TODO Move to generation
									if ($field->skipExport)
									{
										continue;
									}
									?>
									<div class="cforms-field-confirmation cforms-field-confirmation-<?php echo $field->id; ?> col-md-12">
										<label class="control-label-confirmation">
											<?php _e($field->label, 'cforms-plugin') ?>
										</label>
										<span id="field-confirmation-<?php echo $field->id; ?>"></span>
									</div>
								<?php endforeach; ?>
							</div>
						</div>
					<?php endif; ?>
				</div>
			</div>

			<input type="hidden" name="formId" value="<?php echo $form->id; ?>"/>
			<input type="hidden" name="uuid" value="<?php echo CformsBehavior::getUuid(true) ?>"/>
			<input type="hidden" name="plugin" value="cforms"/>

			<?php wp_nonce_field('cforms_submit'); ?>

			<div class="row">
				<div class="col-md-12">
					<div class="cforms_navigation text-right">
						<button class="btn btn-default btnPrev"><?php _e('Previous', 'cforms-plugin'); ?></button>
						<button class="btn btn-default btnNext"><?php _e('Next', 'cforms-plugin'); ?></button>
						<button class="btn btn-success btnSubmit"><?php _e($form->params->get('submitButtonText', 'Submit Form'), 'cforms-plugin'); ?></button>
					</div>
				</div>
			</div>

		</form>
	</div>

	<script type="text/javascript">
		var designParams = <?php echo json_encode($designParameter); ?>;

		jQuery(document).ready(function ($) {
			$("#cformsForm_<?php echo $form->id; ?>").cform({
				pages: <?php echo $form->pages; ?>,
				juri: '',
				confirmationPage: <?php echo $form->params->get('confirmationPage', false) ? 'true' : 'false'; ?>,
				fields: <?php echo json_encode($fields); ?>,
				formLogin: <?php echo 'false'; // echo $showLogin ? 'true' : 'false'; ?>,
				mappingData: <?php echo json_encode(CformsHelperMappings::getData()); ?>,
			});
		});
	</script>
<?php
if (!$shortCodeMode)
{
	get_footer('cforms');
}
