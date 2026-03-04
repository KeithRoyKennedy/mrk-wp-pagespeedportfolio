/**
 * PageSpeed Portfolio - Admin JavaScript
 *
 * Schedules a background score refresh and polls for completion.
 *
 * @package PageSpeed_Portfolio
 */

/* global jQuery, pspAdmin */
(function ($) {
	'use strict';

	var pollInterval = null;
	var pollCount = 0;
	var maxPolls = 30; // 30 polls × 10s = 5 minutes max wait.

	$(document).ready(function () {
		var $button = $('#psp-refresh-scores');
		var $status = $('#psp-refresh-status');

		if (!$button.length) {
			return;
		}

		$button.on('click', function (e) {
			e.preventDefault();

			var postId = $button.data('post-id');
			if (!postId) {
				return;
			}

			$button.prop('disabled', true);
			$status
				.text(pspAdmin.i18n.fetching)
				.removeClass('psp-status--success psp-status--error')
				.addClass('psp-status--loading');

			// Step 1: Schedule the background fetch.
			$.ajax({
				url: pspAdmin.ajaxUrl,
				type: 'POST',
				data: {
					action: 'psp_fetch_scores',
					nonce: pspAdmin.nonce,
					post_id: postId,
				},
				success: function (response) {
					if (response.success) {
						$status.text(response.data.message);
						// Step 2: Start polling for completion.
						pollCount = 0;
						pollInterval = setInterval(function () {
							pollStatus(postId, $button, $status);
						}, 10000);
					} else {
						$status
							.text(response.data.message || pspAdmin.i18n.error)
							.removeClass('psp-status--loading')
							.addClass('psp-status--error');
						$button.prop('disabled', false);
					}
				},
				error: function () {
					$status
						.text(pspAdmin.i18n.error)
						.removeClass('psp-status--loading')
						.addClass('psp-status--error');
					$button.prop('disabled', false);
				},
			});
		});
	});

	/**
	 * Poll the server for background fetch status.
	 */
	function pollStatus(postId, $button, $status) {
		pollCount++;

		if (pollCount > maxPolls) {
			clearInterval(pollInterval);
			$status
				.text('Fetch is taking longer than expected. Please reload the page later.')
				.removeClass('psp-status--loading')
				.addClass('psp-status--error');
			$button.prop('disabled', false);
			return;
		}

		$.ajax({
			url: pspAdmin.ajaxUrl,
			type: 'POST',
			data: {
				action: 'psp_poll_status',
				nonce: pspAdmin.nonce,
				post_id: postId,
			},
			success: function (response) {
				if (!response.success) {
					return;
				}

				var data = response.data;

				if (data.status === 'done') {
					clearInterval(pollInterval);

					if (data.error) {
						$status
							.text('Completed with errors: ' + data.error)
							.removeClass('psp-status--loading')
							.addClass('psp-status--error');
					} else {
						$status
							.text(pspAdmin.i18n.success)
							.removeClass('psp-status--loading')
							.addClass('psp-status--success');
					}

					// Reload to show updated scores.
					setTimeout(function () {
						window.location.reload();
					}, 1500);
				} else if (data.status === 'fetching') {
					$status.text('Fetching scores in background… (poll ' + pollCount + '/' + maxPolls + ')');
				}
			},
		});
	}

})(jQuery);
