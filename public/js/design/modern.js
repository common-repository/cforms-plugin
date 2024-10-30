/*
 * CForms Modern Template Style
 * https://compojoom.com
 * Copyright (c) 2013 - 2017 Yves Hoppe - compojoom
 */
(function ($) {
	var version = "v20170728";

	// TODO not working for multiple forms on one page
	var modern = {
		currentPage: 1,
		$form: null,

		changePage: function(nr) {
			if (nr > modern.currentPage)
			{
				$target = $('.inactive-page-' + nr, modern.$form);

				var inactive = $target.clone();

				inactive.find('.inactive-page-number').html('<i class="fa fa-bars"></i><br /><br />' + (nr - 1));
				inactive.removeClass('inactive-page-' + nr);
				inactive.addClass('inactive-page-' + (nr - 1));

				$target.remove();

				inactive.appendTo($('.modern-left', modern.$form));

				modern.currentPage++;
			}
			else
			{
				// Previous
				$target = $('.inactive-page-' + nr);

				var inactive = $target.clone();

				inactive.find('.inactive-page-number').html('<i class="fa fa-bars"></i><br /><br />' + (nr + 1));
				inactive.removeClass('inactive-page-' + nr);
				inactive.addClass('inactive-page-' + (nr + 1));

				$target.remove();

				inactive.appendTo($('.modern-right', modern.$form));

				modern.currentPage--;
			}

			$('.modern-step', modern.$form).removeClass('step-active');
			$('.modern-step-' + nr, modern.$form).addClass('step-active');
		},

		validateForm: function(page) {
			return document.formvalidator.isValid($('.cforms-page-' + page, modern.$form).get());
		},

		initialize: function () {
			modern.$form = $('form.modern');

			if (!modern.$form) {
				console.log('No modern Form found');
				return;
			}

			$('.btnNext').click(function () {
				if (!modern.validateForm(modern.currentPage)) {
					return;
				}

				modern.changePage(modern.currentPage + 1);
			});

			$('.btnPrev').click(function () {
				modern.changePage(modern.currentPage - 1);
			});
		}
	};


	$(document).ready(function () {
		modern.initialize();
	});

}(jQuery));