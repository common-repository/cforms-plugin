/*
 * CForms Two Columns Template Style
 * https://compojoom.com
 * Copyright (c) 2013 - 2017 Yves Hoppe - compojoom
 */
(function ($) {
	var version = "v20170731";

	var twocolumns = {
		currentPage: 1,
		$form: null,
		$textContent: null,

		changePage: function(nr) {
			twocolumns.$textContent.html(designParams['textP' + nr]);

			twocolumns.currentPage = nr;

			$('.twocolumns-step', twocolumns.$form).removeClass('step-active');
			$('.twocolumns-step-' + nr, twocolumns.$form).addClass('step-active');
		},

		validateForm: function(page) {
			return document.formvalidator.isValid($('.cforms-page-' + page, twocolumns.$form).get());
		},

		initialize: function () {
			twocolumns.$form = $('form.twocolumns');
			twocolumns.$textContent = $('.twocolumns-content', twocolumns.$form);

			if (!twocolumns.$form) {
				console.log('No twocolumns Form found');
				return;
			}

			$('.btnNext').click(function () {
				if (!twocolumns.validateForm(twocolumns.currentPage)) {
					return;
				}

				twocolumns.changePage(twocolumns.currentPage + 1);
			});

			$('.btnPrev').click(function () {
				twocolumns.changePage(twocolumns.currentPage - 1);
			});
		}
	};

	$(document).ready(function () {
		twocolumns.initialize();
	});

}(jQuery));