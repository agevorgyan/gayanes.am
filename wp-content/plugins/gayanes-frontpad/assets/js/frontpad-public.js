(function ($) {
	'use strict';

	function getRecaptchaToken(action) {
		return new Promise(function (resolve) {
			var siteKey = (window.gayanesFrontpad && gayanesFrontpad.recaptchaSiteKey) || '';
			if (!siteKey || typeof grecaptcha === 'undefined') {
				resolve('');
				return;
			}
			grecaptcha.ready(function () {
				grecaptcha.execute(siteKey, { action: action }).then(resolve, function () {
					resolve('');
				});
			});
		});
	}

	function autofillCheckout(client) {
		if (client.name && $('#billing_first_name').length && !$('#billing_first_name').val()) {
			$('#billing_first_name').val(client.name);
		}
		if (client.mail && $('#billing_email').length && !$('#billing_email').val()) {
			$('#billing_email').val(client.mail);
		}
		if (client.street && $('#billing_address_1').length) {
			var addr = client.street + (client.home ? ' ' + client.home : '');
			$('#billing_address_1').val(addr);
		}
		if (client.pod && $('#frontpad_pod').length) {
			$('#frontpad_pod').val(client.pod);
		}
		if (client.et && $('#frontpad_et').length) {
			$('#frontpad_et').val(client.et);
		}
		if (client.apart && $('#frontpad_apart').length) {
			$('#frontpad_apart').val(client.apart);
		}
	}

	$(document).on('click', '.frontpad-client-lookup-btn', function () {
		var $btn = $(this);
		var $wrap = $btn.closest('.frontpad-widget, .frontpad-checkout-client');
		var $phone = $wrap.find('.frontpad-phone-input');
		var $res = $wrap.find('.frontpad-client-result');

		$btn.prop('disabled', true);
		$res.removeClass('is-error is-success').text(gayanesFrontpad.i18n.loading);

		getRecaptchaToken('frontpad_get_client').then(function (token) {
			$.post(gayanesFrontpad.ajaxUrl, {
				action: 'frontpad_get_client',
				nonce: gayanesFrontpad.nonce,
				phone: $phone.val(),
				recaptcha_token: token
			}).done(function (response) {
				$btn.prop('disabled', false);
				if (!response || !response.success) {
					var msg = (response && response.data && response.data.message) || gayanesFrontpad.i18n.error;
					$res.addClass('is-error').text(msg);
					return;
				}
				var c = response.data;
				autofillCheckout(c);

				var parts = [];
				if (c.name) parts.push('<strong>' + $('<div>').text(c.name).html() + '</strong>');
				if (c.card) parts.push(gayanesFrontpad.i18n.card + ': #' + $('<div>').text(c.card).html());
				if (parseFloat(c.sale) > 0) parts.push(gayanesFrontpad.i18n.discount + ': ' + c.sale + '%');
				if (parseFloat(c.score) > 0) {
					parts.push(gayanesFrontpad.i18n.points + ': ' + c.score + ' <button type="button" class="button frontpad-use-score-btn" data-score="' + parseFloat(c.score) + '">Use</button>');
				}
				$res.addClass('is-success').html(parts.join(' · '));
			}).fail(function () {
				$btn.prop('disabled', false);
				$res.addClass('is-error').text(gayanesFrontpad.i18n.error);
			});
		});
	});

	$(document).on('click', '.frontpad-use-score-btn', function () {
		var score = $(this).data('score');
		$.post(gayanesFrontpad.ajaxUrl, {
			action: 'frontpad_apply_score',
			nonce: gayanesFrontpad.nonce,
			score: score
		}).done(function () {
			$(document.body).trigger('update_checkout');
		});
	});

	$(document).on('click', '.frontpad-cert-check-btn', function () {
		var $btn = $(this);
		var $wrap = $btn.closest('.frontpad-widget, .frontpad-checkout-cert');
		var $input = $wrap.find('.frontpad-cert-input');
		var $res = $wrap.find('.frontpad-cert-result');

		$btn.prop('disabled', true);
		$res.removeClass('is-error is-success').text(gayanesFrontpad.i18n.loading);

		getRecaptchaToken('frontpad_check_certificate').then(function (token) {
			$.post(gayanesFrontpad.ajaxUrl, {
				action: 'frontpad_check_certificate',
				nonce: gayanesFrontpad.nonce,
				certificate: $input.val(),
				recaptcha_token: token
			}).done(function (response) {
				$btn.prop('disabled', false);
				if (!response || !response.success) {
					var msg = (response && response.data && response.data.message) || gayanesFrontpad.i18n.error;
					$res.addClass('is-error').text(msg);
					return;
				}
				var d = response.data;
				var label = gayanesFrontpad.i18n.applied;
				if (d.type === 'product') {
					label = gayanesFrontpad.i18n.certProduct + ': ' + d.name + ' (' + d.price + ')';
				} else if (d.type === 'sale') {
					label = gayanesFrontpad.i18n.certPercent + ': ' + d.sale + '%';
				} else if (d.type === 'amount') {
					label = gayanesFrontpad.i18n.certAmount + ': ' + d.amount;
				}
				$res.addClass('is-success').text(label);
				$(document.body).trigger('update_checkout');
			}).fail(function () {
				$btn.prop('disabled', false);
				$res.addClass('is-error').text(gayanesFrontpad.i18n.error);
			});
		});
	});

	$(document).on('click', '.frontpad-cert-remove-btn', function () {
		$.post(gayanesFrontpad.ajaxUrl, {
			action: 'frontpad_remove_certificate',
			nonce: gayanesFrontpad.nonce
		}).done(function () {
			$('.frontpad-cert-input').val('');
			$('.frontpad-cert-result').removeClass('is-success is-error').text('');
			$(document.body).trigger('update_checkout');
		});
	});
})(jQuery);
