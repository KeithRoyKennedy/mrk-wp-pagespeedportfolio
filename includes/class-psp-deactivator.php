<?php
/**
 * Plugin deactivator.
 *
 * @package PageSpeed_Portfolio
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class PSP_Deactivator
 *
 * Handles plugin deactivation tasks.
 */
class PSP_Deactivator {

	/**
	 * Run deactivation routines.
	 *
	 * Clears scheduled cron events.
	 */
	public static function deactivate() {
		$timestamp = wp_next_scheduled( 'psp_daily_score_refresh' );
		if ( $timestamp ) {
			wp_unschedule_event( $timestamp, 'psp_daily_score_refresh' );
		}

		wp_unschedule_hook( 'psp_fetch_single_strategy' );

		flush_rewrite_rules();
	}
}
