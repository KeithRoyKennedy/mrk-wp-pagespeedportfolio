<?php
/**
 * Google PageSpeed Insights API wrapper.
 *
 * @package PageSpeed_Portfolio
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class PSP_API
 *
 * Handles communication with the Google PageSpeed Insights API.
 */
class PSP_API {

	/**
	 * API endpoint.
	 *
	 * @var string
	 */
	const API_ENDPOINT = 'https://www.googleapis.com/pagespeedonline/v5/runPagespeed';

	/**
	 * Categories to fetch.
	 *
	 * @var array
	 */
	const CATEGORIES = array( 'performance', 'accessibility', 'best-practices', 'seo' );

	/**
	 * Fetch PageSpeed scores for a given URL.
	 *
	 * @param string $url      The URL to audit.
	 * @param string $strategy 'mobile' or 'desktop'.
	 * @return array|WP_Error Array of scores or WP_Error on failure.
	 */
	public static function fetch_scores( $url, $strategy = 'mobile' ) {
		$api_key = get_option( 'psp_api_key', '' );

		if ( empty( $api_key ) ) {
			return new WP_Error(
				'psp_no_api_key',
				__( 'Google PageSpeed API key is not configured. Please add it in Settings → PageSpeed Portfolio.', 'pagespeed-portfolio' )
			);
		}

		// Build category params manually — the API requires repeated
		// 'category' keys (category=X&category=Y) which add_query_arg cannot produce.
		$category_params = array();
		foreach ( self::CATEGORIES as $cat ) {
			$category_params[] = 'category=' . rawurlencode( $cat );
		}

		$request_url = add_query_arg(
			array(
				'url'      => esc_url_raw( $url ),
				'key'      => sanitize_text_field( $api_key ),
				'strategy' => sanitize_text_field( $strategy ),
			),
			self::API_ENDPOINT
		);

		$request_url .= '&' . implode( '&', $category_params );

		$response = wp_remote_get(
			$request_url,
			array(
				'timeout' => 120,
			)
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$status_code = wp_remote_retrieve_response_code( $response );
		$body        = wp_remote_retrieve_body( $response );
		$data        = json_decode( $body, true );

		if ( 200 !== $status_code ) {
			$error_message = isset( $data['error']['message'] )
				? sanitize_text_field( $data['error']['message'] )
				: __( 'Unknown API error.', 'pagespeed-portfolio' );

			return new WP_Error(
				'psp_api_error',
				/* translators: %s: API error message */
				sprintf( __( 'PageSpeed API error: %s', 'pagespeed-portfolio' ), $error_message )
			);
		}

		if ( empty( $data['lighthouseResult']['categories'] ) ) {
			return new WP_Error(
				'psp_invalid_response',
				__( 'Invalid response from PageSpeed API.', 'pagespeed-portfolio' )
			);
		}

		$categories = $data['lighthouseResult']['categories'];

		return array(
			'performance'    => self::extract_score( $categories, 'performance' ),
			'accessibility'  => self::extract_score( $categories, 'accessibility' ),
			'best_practices' => self::extract_score( $categories, 'best-practices' ),
			'seo'            => self::extract_score( $categories, 'seo' ),
		);
	}

	/**
	 * Extract a category score from the API response.
	 *
	 * @param array  $categories The categories data from the API.
	 * @param string $key        The category key.
	 * @return int Score as 0-100.
	 */
	private static function extract_score( $categories, $key ) {
		if ( isset( $categories[ $key ]['score'] ) ) {
			return (int) round( $categories[ $key ]['score'] * 100 );
		}
		return 0;
	}

	/**
	 * Maximum number of retries for transient API errors.
	 *
	 * @var int
	 */
	const MAX_RETRIES = 2;

	/**
	 * Seconds to wait between strategy calls to avoid rate limits.
	 *
	 * @var int
	 */
	const STRATEGY_DELAY = 30;

	/**
	 * Seconds to wait before a retry attempt.
	 *
	 * @var int
	 */
	const RETRY_DELAY = 15;

	/**
	 * Fetch scores with retry logic for transient Lighthouse errors.
	 *
	 * @param string $url      The URL to audit.
	 * @param string $strategy 'mobile' or 'desktop'.
	 * @return array|WP_Error Array of scores or WP_Error on failure.
	 */
	public static function fetch_scores_with_retry( $url, $strategy = 'mobile' ) {
		$last_error = null;

		for ( $attempt = 0; $attempt <= self::MAX_RETRIES; $attempt++ ) {
			if ( $attempt > 0 ) {
				sleep( self::RETRY_DELAY );
			}

			$scores = self::fetch_scores( $url, $strategy );

			if ( ! is_wp_error( $scores ) ) {
				return $scores;
			}

			$last_error = $scores;

			// Only retry on transient Lighthouse errors, not on config issues.
			$code = $scores->get_error_code();
			if ( 'psp_no_api_key' === $code || 'psp_no_url' === $code ) {
				return $scores;
			}
		}

		return $last_error;
	}

	/**
	 * Fetch and save scores for a site post.
	 *
	 * @param int $post_id The psp_site post ID.
	 * @return array|WP_Error Combined results or first error encountered.
	 */
	public static function fetch_and_save( $post_id ) {
		$url = get_post_meta( $post_id, '_psp_site_url', true );

		if ( empty( $url ) ) {
			return new WP_Error(
				'psp_no_url',
				__( 'No site URL specified for this site.', 'pagespeed-portfolio' )
			);
		}

		$results = array();
		$errors  = array();

		foreach ( array( 'mobile', 'desktop' ) as $strategy ) {
			// Delay between strategies to avoid Google API rate limits.
			if ( ! empty( $results ) ) {
				sleep( self::STRATEGY_DELAY );
			}

			$scores = self::fetch_scores_with_retry( $url, $strategy );

			if ( is_wp_error( $scores ) ) {
				$errors[ $strategy ] = $scores;

				// Clear stale meta so old values don't display for a failed strategy.
				$prefix = ( 'desktop' === $strategy ) ? '_psp_desktop_' : '_psp_';
				delete_post_meta( $post_id, $prefix . 'performance' );
				delete_post_meta( $post_id, $prefix . 'accessibility' );
				delete_post_meta( $post_id, $prefix . 'best_practices' );
				delete_post_meta( $post_id, $prefix . 'seo' );

				continue;
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

			$results[ $strategy ] = $scores;
		}

		// Update last-fetched timestamp if at least one strategy succeeded.
		if ( ! empty( $results ) ) {
			update_post_meta( $post_id, '_psp_last_fetched', current_time( 'mysql' ) );
		}

		// If both failed, return the first error.
		if ( empty( $results ) && ! empty( $errors ) ) {
			return reset( $errors );
		}

		// If one strategy failed, include partial error info but still return results.
		if ( ! empty( $errors ) ) {
			foreach ( $errors as $strategy => $error ) {
				$results[ $strategy . '_error' ] = $error->get_error_message();
			}
		}

		return $results;
	}
}
