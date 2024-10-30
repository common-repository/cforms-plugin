/*
 * CForms Frontend Form
 * https://compojoom.com
 * Copyright (c) 2013 - 2017 Yves Hoppe - compojoom
 */
(function ($) {
	var version = "v20170803";

	$.fn.cform = function (options) {

		var settings = $.extend({
			// Default settings - see API instructions
			juri: '',
			pages: 1,
			confirmationPage: 0,
			fields: [],
			formLogin: false,
			mappingData: '',
			preloadData: '',
			debug: false
		}, options);

		var holder = $.extend({
			form: null,
			currentPage: 1,
			pages: 1,
			fields: {},
			btnPrev: null,
			btnNext: null,
			btnSubmit: null,
			stepHeading: null
		});

		var API = $.extend({
			showPage: function (number) {
				$('.cforms-page', holder.form).hide();
				$('.cforms-page-' + number, holder.form).show();
			},

			showStepHeading: function (target) {
				if (holder.stepHeading == null) {
					return;
				}

				if (holder.currentPage == 0) {
					holder.stepHeading.hide();
				}

				$('.cforms-step', holder.stepHeading).removeClass('step-active');

				$('.step-' + target, holder.stepHeading).addClass('step-active');
			},

			changePage: function (target) {
				holder.currentPage = target;
				API.hideNav();
				API.showPage(target);
				API.showStepHeading(target);

				// Login Form
				if (holder.currentPage == 0) {
					return;
				}

				holder.stepHeading.show();

				if (settings.pages == 1) {
					holder.btnSubmit.show();
					return;
				}

				if (settings.pages == target) {
					if (settings.confirmationPage) {
						API.showConfirmation();
					}

					holder.btnPrev.show();
					holder.btnSubmit.show();
					return;
				}

				if (target < settings.pages) {
					holder.btnNext.show();
				}

				if (target > 1) {
					holder.btnPrev.show();
				}
			},

			showConfirmation: function() {
				$('.cforms-page-confirmation', holder.form).show();

				$.each(settings.fields, function(index, field) {
					if (field.skipExport == 1) {
						return true;
					}

					if (field.type == 'radio') {
						var fieldValue = $('input[name="field[' + field.id +']"]:checked', holder.form).parent('label').text();
					} else if (field.type == 'checkbox') {
						var fieldValue = $('input[name="field[' + field.id +']"]:checked', holder.form).val();
					} else {
						var fieldValue = $('#field_' + field.id, holder.form).val();
					}

					$('#field-confirmation-' + field.id, holder.form).text(fieldValue);
				});
			},

			hideNav: function () {
				holder.btnPrev.hide();
				holder.btnNext.hide();
				holder.btnSubmit.hide();
			},

			setupNav: function () {
				holder.btnPrev = $('.btnPrev', holder.form);
				holder.btnNext = $('.btnNext', holder.form);
				holder.btnSubmit = $('.btnSubmit', holder.form);
				holder.stepHeading = $('.cforms-steps', holder.form);

				if (settings.confirmationPage) {
					settings.pages++;
				}

				if (settings.formLogin) {
					settings.currentPage = 0;

					$('#btnBookAsGuest').click(function(e){
						e.preventDefault();

						$('#loginForm').hide();

						API.changePage(1);
					})
				}

				holder.btnPrev.click(function (e) {
					e.preventDefault();
					API.changePage(holder.currentPage - 1)
				});

				holder.btnNext.click(function (e) {
					e.preventDefault();

					if (!API.validateForm(holder.currentPage)) {
						return;
					}

					$('html, body').animate({
							scrollTop: $('.compojoom-bootstrap').offset().top - 60
						}, 600,
						function () {
							document.location.hash = 'page-' + holder.currentPage + 1;
					});

					API.changePage(holder.currentPage + 1)
				});

				holder.btnSubmit.click(function (e) {
					e.preventDefault();
					if (!API.validateForm(holder.currentPage)) {
						return;
					}
					document.cformsForm.submit();
				})
			},

			setupConditions: function() {
				// Hide all fields which should only be shown in certain conditions
				$('.cforms-field-condition[data-task="show"]', holder.form).hide();

				$('.cforms-field-condition').each(function(key, element){
					var $field = $(element);
					var fieldId = $field.data('field-id');
					var task = $field.data('task');
					var rules = $field.data(task + '-rules');

					API.debug(rules);

					for (var i = 0; i < rules.length; i++) {
						var targetField = rules[i].field;

						var $targetField = $('[name="field[' + targetField + ']"]', holder.form);
						var current = $targetField.attr('data-condition-targets');

						if (typeof current == 'undefined') {
							current = [fieldId];
						} else {
							current = current.split(',');
							current.push(fieldId);
						}

						$targetField.attr('data-condition-targets', current.join(','));

						API.debug('Adding trigger to ' + targetField + ' with targets ' + current);

						$targetField.change(function(e){
							var element = $(this);

							API.checkConditionMatches(element.attr('data-condition-targets').split(','));
						});

						// Make sure conditions don't match from the beginning
						API.checkConditionMatches($targetField.attr('data-condition-targets').split(','));
					}
				});
			},

			checkConditionMatches: function(targetFields) {
				for (var i = 0; i < targetFields.length; i++)
				{
					var field = targetFields[i];
					var $field = $('div[data-field-id="' + field + '"]', holder.form);

					var task = $field.data('task');

					// For later versions - multiple tasks
					var rules = $field.data(task + '-rules');

					var fullFilled = true;

					for (var j = 0; j < rules.length; j++) {
						var rule = rules[j];

						var $sourceField = $('[name="field[' + rule.field + ']"]', holder.form);
						var type = $sourceField.attr('type');

						// We need to handle some input types different
						if (type === 'radio' || type === 'checkbox') {
							$sourceField = $('[name="field[' + rule.field + ']"]:checked', holder.form);
						}

						var sourceValue = $sourceField.val();

						var matched = false;

						API.debug('SourceValue ' + sourceValue + ' matches ' + rule.matches + ' = ' + rule.value);

						// Not empty
						if (rule.matches === 'notempty' && sourceValue.length > 0) {
							matched = true;
						} else if (rule.matches === 'empty' && sourceValue.length === 0) {
							matched = true;
						} else if (rule.matches === 'value' && sourceValue == rule.value) {
							matched = true;
						}

						if (matched === false) {
							fullFilled = false;
						}
					}

					API.debug('Conditions are ' + fullFilled);

					if (fullFilled) {
						if (task === 'hide') {
							$field.hide();
						} else if (task === 'show') {
							API.debug('Showing field ' + field);
							$field.show();
						}
					} else {
						if (task === 'hide') {
							$field.show();
						} else if (task === 'show') {
							API.debug('Hiding field ' + field);
							$field.hide();
						}
					}
				}
			},

			validateForm: function (page) {
				// Validate the current page
				return document.formvalidator.isValid($('.cforms-page-' + page, holder.form).get());
			},

			loadMappingData: function() {
				if (!settings.mappingData) {
					return;
				}

				$('[data-mapping]', holder.form).each(function(key, element){
					$field = $(this);

					var mapping = $field.data('mapping');

					// TODO update to object
					parts = mapping.split('.');

					if (parts.length !== 2) {
						return;
					}

					var category = parts[0];
					var dataSource = parts[1];

					try {
						$field.val(settings.mappingData[category][dataSource]);
					} catch (err) {
						// Ignore
					}
				});
			},

			preloadData: function() {
				if (!settings.preloadData) {
					return;
				}

				for (var fieldId in settings.preloadData) {
					try {
						var value = settings.preloadData[fieldId];
						var $field = $('#field_' + fieldId);

						if (!$field.length) {
							// Try by name
							$field = $('[name="field[' + fieldId + ']"');
						}

						if (!$field.length) {
							// Try checkboxes
							$field = $('[name="field[' + fieldId + '][]"');
						}

						if (!$field.length) {
							console.log('Field ' + fieldId + ' not found');
							continue;
						}

						var type = $field.attr('type');

						if (type !== 'radio') {
							API.debug('Setting ' + fieldId + ' to ' + value);

							$field.val(value);

							continue;
						}

						// Handle Radio
						$field.removeAttr('checked');

						$field.each(function(){
							var $element = $(this);
							var $label = $element.parent('label');

							$label.removeClass('btn-success');

							if ($element.val() === value) {
								API.debug('Setting to checked ' + value);

								$element.prop('checked', true);
								$label.addClass('btn-success');
							}
						});
					} catch (err) {
						// Ignore
					}
				}
			},

			debug: function(message) {
				if (settings.debug) {
					console && console.log(message);
				}
			},

			init: function () {
				// We are setting up
				API.debug('Setting up CForms ' + version);
				API.setupNav();
				API.loadMappingData();
				API.preloadData();
				API.changePage(settings.formLogin ? 0 : 1);
				API.setupConditions();

				// Terms and conditions
				$(".open_cforms_dialog").magnificPopup({
					type: 'inline',
					midClick: true
				});

				return true;
			}
		});

		return this.each(function () {
			holder.form = $(this);
			API.init();
		});
	};

	// Add the form validator
	if (typeof document.formvalidator === 'undefined') {
		document.formvalidator = {
			renderErrors: function(errors) {
				var $errorDisplay = $('#form-errors');
				var $errorContainer = $('#error-container');

				$errorContainer.show();

				// Wipe Content
				$errorDisplay.html('');

				$.each(errors, function(key, element){
					var labelText = $('label[for="' + element.attr('id') + '"]');

					labelText.css({'color': 'red'});

					$errorDisplay.append(labelText.html() + '<br />');
				});
			},

			validatePassword: function(value) {
				// Just a length check for now @todo add more
				return (value.length > 7);
			},

			validateEmail: function (value) {
				var re = /^(([^<>()\[\]\.,;:\s@\"]+(\.[^<>()\[\]\.,;:\s@\"]+)*)|(\".+\"))@(([^<>()[\]\.,;:\s@\"]+\.)+[^<>()[\]\.,;:\s@\"]{2,})$/i;
				return re.test(value);
			},

			validateNumeric: function(value) {
				return (!isNaN(value));
			},

			cleanErrors: function() {
				var $errorDisplay = $('#form-errors');
				var $errorContainer = $('#error-container');
				$errorContainer.hide();
				$errorDisplay.html('');
			},

			isValid: function(parent) {
				var errors = [];

				$('.required', parent).each(function(key, element){
					$element = $(element);

					val = $element.val();

					// Basic value field
					if (val.length < 1) {
						errors.push($element);
						return true;
					}

					// Other validators, todo refactor to automatic
					if ($element.hasClass('validate-password')) {
						if (!document.formvalidator.validatePassword(val)) {
							errors.push($element);
							return true;
						}
					}

					if ($element.hasClass('validate-email')) {
						if (!document.formvalidator.validateEmail(val)) {
							errors.push($element);
							return true;
						}
					}

					if ($element.hasClass('validate-numeric')) {
						if (!document.formvalidator.validateNumeric(val)) {
							errors.push($element);
							return true;
						}
					}
				});

				if (errors.length > 0) {
					this.renderErrors(errors);

					return false;
				}

				this.cleanErrors();

				return true;
			}
		}
	}
}(jQuery));
