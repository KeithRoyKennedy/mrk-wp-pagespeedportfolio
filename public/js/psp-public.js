/**
 * PageSpeed Portfolio - Public JavaScript
 *
 * Handles score gauge animations on the front-end.
 *
 * @package PageSpeed_Portfolio
 */

(function () {
	'use strict';

	/**
	 * Animate score gauges when they come into view.
	 */
	function initScoreGauges() {
		var gauges = document.querySelectorAll('.psp-score-gauge__circle');

		if (!gauges.length) {
			return;
		}

		// Use IntersectionObserver for performant scroll-based animation.
		if ('IntersectionObserver' in window) {
			var observer = new IntersectionObserver(
				function (entries) {
					entries.forEach(function (entry) {
						if (entry.isIntersecting) {
							animateGauge(entry.target);
							observer.unobserve(entry.target);
						}
					});
				},
				{
					threshold: 0.3,
				}
			);

			gauges.forEach(function (gauge) {
				// Reset the fill to 0 initially.
				var fill = gauge.querySelector('.psp-score-gauge__fill');
				if (fill) {
					fill.style.strokeDashoffset = '339.292';
				}
				observer.observe(gauge);
			});
		} else {
			// Fallback: animate all immediately.
			gauges.forEach(function (gauge) {
				animateGauge(gauge);
			});
		}
	}

	/**
	 * Animate a single gauge circle.
	 *
	 * @param {Element} gauge The gauge circle element.
	 */
	function animateGauge(gauge) {
		var score = parseInt(gauge.getAttribute('data-score'), 10) || 0;
		var fill = gauge.querySelector('.psp-score-gauge__fill');

		if (!fill) {
			return;
		}

		var circumference = 339.292; // 2 * PI * 54
		var offset = circumference * (1 - score / 100);

		// Small delay for visual effect.
		setTimeout(function () {
			fill.style.strokeDashoffset = offset.toString();
		}, 100);
	}

	// Initialize when DOM is ready.
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initScoreGauges);
	} else {
		initScoreGauges();
	}
})();
