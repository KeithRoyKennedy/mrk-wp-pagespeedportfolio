<?php
/**
 * WP-Cron handler for background score fetching.
 *
 * All API calls run as individual WP-Cron single events so they never
 * block an HTTP request. Each strategy (mobile/desktop) for each site
 * is its own event, staggered to avoid overloading the target server.
 *
 * @package PageSpeed_Portfolio
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class PSP_Cron
 *
 * Handles background score fetching via WP-Cron.
 */
class PSP_Cron {

	/**
	 * Seconds between mobile and desktop fetches for the same site.
	 *
	 * @var int
	 */
	const STRATEGY_GAP = 60;

	/**
	 * Seconds between different sites during daily refresh.
	 *
	 * @var int
	 */
	const SITE_GAP = 180;

	/**
	 * Initialize hooks.
	 */
	public static function init() {
		add_action( 'psp_daily_score_refresh', array( __CLASS__, 'schedule_all_sites' ) );
		add_action( 'psp_fetch_single_strategy', array( __CLASS__, 'fetch_single_strategy' ), 10, 2 );
	}

	/**
	 * Schedule background fetches for a single site (mobile + desktop).
	 *
	 * @param int $post_id The psp_site post ID.
	 * @param int $offset  Optional seconds offset from now (default 5).
	 */
	public static function schedule_site_fetch( $post_id, $offset = 5 ) {
		$post_id = absint( $post_id );

		// Mark the site as "fetching" so the UI can poll for it.
		update_post_meta( $post_id, '_psp_fetch_status', 'fetching' );
		delete_post_meta( $post_id, '_psp_fetch_error' );

		// Schedule mobile fetch.
		$mobile_time = time() + $offset;
		if ( ! wp_next_scheduled( 'psp_fetch_single_strategy', array( $post_id, 'mobile' ) ) ) {
			wp_schedule_single_event( $mobile_time, 'psp_fetch_single_strategy', array( $post_id, 'mobile' ) );
		}

		// Schedule desktop fetch with a gap after mobile.
		$desktop_time = $mobile_time + self::STRATEGY_GAP;
		if ( ! wp_next_scheduled( 'psp_fetch_single_strategy', array( $post_id, 'desktop' ) ) ) {
			wp_schedule_single_event( $desktop_time, 'psp_fetch_single_strategy', array( $post_id, 'desktop' ) );
		}
	}

	/**
	 * WP-Cron callback: fetch scores for one site + one strategy.
	 *
	 * @param int    $post_id  The psp_site post ID.
	 * @param string $strategy 'mobile' or 'desktop'.
	 */
	public static function fetch_single_strategy( $post_id, $strategy ) {
		$post_id = absint( $post_id );

		if ( 'psp_site' !== get_post_type( $post_id ) ) {
			return;
		}

		$url = get_post_meta( $post_id, '_psp_site_url', true );
		if ( empty( $url ) ) {
			self::mark_done( $post_id, $strategy, 'No site URL configured.' );
			return;
		}

		$scores = PSP_API::fetch_scores_with_retry( $url, $strategy );

		if ( is_wp_error( $scores ) ) {
			self::mark_done( $post_id, $strategy, $scores->get_error_message() );
			return;
		}

		$prefix = ( 'desktop' === $strategy ) ? '_psp_desktop_' : '_psp_';

		update_post_meta( $post_id, $prefix . 'performance', $scores['performance'] );
		update_post_meta( $post_id, $prefix . 'accessibility', $scores['accessibility'] );
		update_post_meta( $post_id, $prefix . 'best_practices', $scores['best_practices'] );
		update_post_meta( $post_id, $prefix . 'seo', $scores['seo'] );

		PSP_DB::insert_score(
			$post_id,
			$strategy,
			$scores['performance'],
			$scores['accessibility'],
			$scores['best_practices'],
			$scores['seo']
		);

		update_post_meta( $post_id, '_psp_last_fetched', current_time( 'mysql' ) );

		self::mark_done( $post_id, $strategy );
	}

	/**
	 * Mark a strategy fetch as done and update the overall status.
	 *
	 * @param int    $post_id  The psp_site post ID.
	 * @param string $strategy The strategy that just completed.
	 * @param string $error    Optional error message.
	 */
	private static function mark_done( $post_id, $strategy, $error = '' ) {
		if ( ! empty( $error ) ) {
			$existing = get_post_meta( $post_id, '_psp_fetch_error', true );
			$msg      = ( $existing ? $existing . '; ' : '' ) . ucfirst( $strategy ) . ': ' . $error;
			update_post_meta( $post_id, '_psp_fetch_error', sanitize_text_field( $msg ) );

			if ( defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
				error_log( // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
					sprintf(
						'PageSpeed Portfolio: %s fetch failed for site %d: %s',
						ucfirst( $strategy ),
						$post_id,
						$error
					)
				);
			}
		}

		// Check if the other strategy is still scheduled.
		$other = ( 'mobile' === $strategy ) ? 'desktop' : 'mobile';
		$still_pending = wp_next_scheduled( 'psp_fetch_single_strategy', array( $post_id, $other ) );

		if ( ! $still_pending ) {
			// Both strategies are done.
			update_post_meta( $post_id, '_psp_fetch_status', 'done' );
		}
	}

	/**
	 * Daily cron callback: schedule fetches for all published sites,
	 * staggered to avoid hammering target servers.
	 */
	public static function schedule_all_sites() {
		$api_key = get_option( 'psp_api_key', '' );
		if ( empty( $api_key ) ) {
			return;
		}

		$sites = get_posts(
			array(
				'post_type'      => 'psp_site',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'fields'         => 'ids',
			)
		);

		if ( empty( $sites ) ) {
			return;
		}

		$offset = 10; // Start 10 seconds from now.
		foreach ( $sites as $site_id ) {
			self::schedule_site_fetch( $site_id, $offset );
			// Each site pair (mobile+desktop) takes STRATEGY_GAP seconds,
			// then add SITE_GAP before the next site.
			$offset += self::STRATEGY_GAP + self::SITE_GAP;
		}
	}
}
