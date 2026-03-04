<?php
/**
 * Database helper class.
 *
 * @package PageSpeed_Portfolio
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class PSP_DB
 *
 * Handles database table creation and CRUD operations for score history.
 */
class PSP_DB {

	/**
	 * Get the scores history table name.
	 *
	 * @return string
	 */
	public static function get_table_name() {
		global $wpdb;
		return $wpdb->prefix . 'psp_scores_history';
	}

	/**
	 * Create the scores history table.
	 *
	 * Uses dbDelta for safe table creation and updates.
	 */
	public static function create_table() {
		global $wpdb;

		$table_name      = self::get_table_name();
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$table_name} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			site_id bigint(20) unsigned NOT NULL,
			strategy varchar(10) NOT NULL DEFAULT 'mobile',
			performance tinyint(3) unsigned DEFAULT NULL,
			accessibility tinyint(3) unsigned DEFAULT NULL,
			best_practices tinyint(3) unsigned DEFAULT NULL,
			seo tinyint(3) unsigned DEFAULT NULL,
			recorded_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY site_id (site_id),
			KEY recorded_at (recorded_at)
		) {$charset_collate};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

	/**
	 * Drop the scores history table.
	 */
	public static function drop_table() {
		global $wpdb;
		$table_name = self::get_table_name();
		$wpdb->query( "DROP TABLE IF EXISTS {$table_name}" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
	}

	/**
	 * Insert a score record into the history table.
	 *
	 * @param int    $site_id        The post ID of the psp_site.
	 * @param string $strategy       'mobile' or 'desktop'.
	 * @param int    $performance    Performance score (0-100).
	 * @param int    $accessibility  Accessibility score (0-100).
	 * @param int    $best_practices Best Practices score (0-100).
	 * @param int    $seo            SEO score (0-100).
	 * @return int|false The number of rows inserted, or false on error.
	 */
	public static function insert_score( $site_id, $strategy, $performance, $accessibility, $best_practices, $seo ) {
		global $wpdb;

		return $wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
			self::get_table_name(),
			array(
				'site_id'        => absint( $site_id ),
				'strategy'       => sanitize_text_field( $strategy ),
				'performance'    => absint( $performance ),
				'accessibility'  => absint( $accessibility ),
				'best_practices' => absint( $best_practices ),
				'seo'            => absint( $seo ),
				'recorded_at'    => current_time( 'mysql' ),
			),
			array( '%d', '%s', '%d', '%d', '%d', '%d', '%s' )
		);
	}

	/**
	 * Get score history for a specific site.
	 *
	 * @param int    $site_id  The post ID.
	 * @param string $strategy 'mobile' or 'desktop'.
	 * @param int    $limit    Number of records to retrieve.
	 * @return array
	 */
	public static function get_score_history( $site_id, $strategy = 'mobile', $limit = 365 ) {
		global $wpdb;

		$table_name = self::get_table_name();

		return $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->prepare(
				"SELECT performance, accessibility, best_practices, seo, recorded_at
				FROM {$table_name}
				WHERE site_id = %d AND strategy = %s
				ORDER BY recorded_at DESC
				LIMIT %d", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				absint( $site_id ),
				sanitize_text_field( $strategy ),
				absint( $limit )
			),
			ARRAY_A
		);
	}

	/**
	 * Delete all score history for a specific site.
	 *
	 * @param int $site_id The post ID.
	 * @return int|false
	 */
	public static function delete_site_history( $site_id ) {
		global $wpdb;

		return $wpdb->delete( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			self::get_table_name(),
			array( 'site_id' => absint( $site_id ) ),
			array( '%d' )
		);
	}
}
