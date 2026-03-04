<?php
/**
 * Admin hooks and asset loading.
 *
 * @package PageSpeed_Portfolio
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class PSP_Admin
 *
 * Handles admin-side initialization, scripts, and styles.
 */
class PSP_Admin {

	/**
	 * Initialize hooks.
	 */
	public static function init() {
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );
		add_filter( 'plugin_action_links_' . PSP_PLUGIN_BASENAME, array( __CLASS__, 'add_settings_link' ) );
	}

	/**
	 * Enqueue admin assets on relevant screens only.
	 *
	 * @param string $hook_suffix The current admin page.
	 */
	public static function enqueue_assets( $hook_suffix ) {
		$screen = get_current_screen();

		if ( ! $screen ) {
			return;
		}

		// Only load on our CPT screens and settings page.
		if ( 'psp_site' === $screen->post_type || 'settings_page_pagespeed-portfolio' === $screen->id ) {
			wp_enqueue_style(
				'psp-admin',
				PSP_PLUGIN_URL . 'admin/css/psp-admin.css',
				array(),
				PSP_VERSION
			);

			wp_enqueue_script(
				'psp-admin',
				PSP_PLUGIN_URL . 'admin/js/psp-admin.js',
				array( 'jquery' ),
				PSP_VERSION,
				true
			);

			wp_localize_script(
				'psp-admin',
				'pspAdmin',
				array(
					'ajaxUrl' => admin_url( 'admin-ajax.php' ),
					'nonce'   => wp_create_nonce( 'psp_admin_nonce' ),
					'i18n'    => array(
						'fetching' => __( 'Fetching scores...', 'pagespeed-portfolio' ),
						'success'  => __( 'Scores updated successfully!', 'pagespeed-portfolio' ),
						'error'    => __( 'Error fetching scores.', 'pagespeed-portfolio' ),
					),
				)
			);
		}
	}

	/**
	 * Add a Settings link on the Plugins page.
	 *
	 * @param array $links Existing plugin action links.
	 * @return array Modified links.
	 */
	public static function add_settings_link( $links ) {
		$settings_link = sprintf(
			'<a href="%s">%s</a>',
			esc_url( admin_url( 'options-general.php?page=pagespeed-portfolio' ) ),
			esc_html__( 'Settings', 'pagespeed-portfolio' )
		);
		array_unshift( $links, $settings_link );
		return $links;
	}
}
