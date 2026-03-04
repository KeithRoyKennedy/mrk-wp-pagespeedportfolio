<?php
/**
 * Public-facing functionality.
 *
 * @package PageSpeed_Portfolio
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class PSP_Public
 *
 * Handles front-end asset registration.
 */
class PSP_Public {

	/**
	 * Initialize hooks.
	 */
	public static function init() {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'register_assets' ) );
	}

	/**
	 * Register (but do not enqueue) front-end assets.
	 *
	 * Assets are enqueued only when the shortcode is rendered.
	 */
	public static function register_assets() {
		wp_register_style(
			'psp-public',
			PSP_PLUGIN_URL . 'public/css/psp-public.css',
			array( 'dashicons' ),
			PSP_VERSION
		);

		wp_register_script(
			'psp-chartjs',
			PSP_PLUGIN_URL . 'assets/chart.min.js',
			array(),
			'4.4.1',
			true
		);

		wp_register_script(
			'psp-public',
			PSP_PLUGIN_URL . 'public/js/psp-public.js',
			array( 'psp-chartjs' ),
			PSP_VERSION,
			true
		);
	}
}
