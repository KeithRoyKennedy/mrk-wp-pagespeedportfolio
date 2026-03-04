<?php
/**
 * Plugin activator.
 *
 * @package PageSpeed_Portfolio
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class PSP_Activator
 *
 * Handles plugin activation tasks.
 */
class PSP_Activator {

	/**
	 * Run activation routines.
	 *
	 * Creates the database table and schedules the cron event.
	 */
	public static function activate() {
		PSP_DB::create_table();

		if ( ! wp_next_scheduled( 'psp_daily_score_refresh' ) ) {
			wp_schedule_event( time(), 'daily', 'psp_daily_score_refresh' );
		}

		flush_rewrite_rules();

		update_option( 'psp_version', PSP_VERSION );
	}
}
