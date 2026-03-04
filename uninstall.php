<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * Cleans up all plugin data including options, post meta,
 * custom posts, and database tables.
 *
 * @package PageSpeed_Portfolio
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;

// Delete plugin options.
delete_option( 'psp_api_key' );
delete_option( 'psp_cron_schedule' );
delete_option( 'psp_version' );

// Clear scheduled cron events.
$timestamp = wp_next_scheduled( 'psp_daily_score_refresh' );
if ( $timestamp ) {
	wp_unschedule_event( $timestamp, 'psp_daily_score_refresh' );
}
wp_unschedule_hook( 'psp_fetch_single_strategy' );

// Delete all psp_site posts and their meta.
$site_ids = $wpdb->get_col( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	"SELECT ID FROM {$wpdb->posts} WHERE post_type = 'psp_site'"
);

if ( ! empty( $site_ids ) ) {
	foreach ( $site_ids as $site_id ) {
		wp_delete_post( absint( $site_id ), true );
	}
}

// Delete any orphaned post meta.
$wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	"DELETE FROM {$wpdb->postmeta} WHERE meta_key LIKE '_psp\_%'"
);

// Drop the scores history table.
$table_name = $wpdb->prefix . 'psp_scores_history';
$wpdb->query( "DROP TABLE IF EXISTS {$table_name}" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange

// Clean up any transients.
$wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	"DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_psp_%' OR option_name LIKE '_transient_timeout_psp_%'"
);
