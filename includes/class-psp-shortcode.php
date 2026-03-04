<?php
/**
 * Shortcode handler for front-end display.
 *
 * @package PageSpeed_Portfolio
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class PSP_Shortcode
 *
 * Registers and renders the [pagespeed_portfolio] shortcode.
 */
class PSP_Shortcode {

	/**
	 * Initialize hooks.
	 */
	public static function init() {
		add_shortcode( 'pagespeed_portfolio', array( __CLASS__, 'render' ) );
	}

	/**
	 * Render the shortcode output.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string HTML output.
	 */
	public static function render( $atts ) {
		$atts = shortcode_atts(
			array(
				'columns'  => 3,
				'orderby'  => 'date',
				'order'    => 'DESC',
				'limit'    => -1,
				'strategy' => 'mobile',
			),
			$atts,
			'pagespeed_portfolio'
		);

		$columns  = absint( $atts['columns'] );
		$strategy = in_array( $atts['strategy'], array( 'mobile', 'desktop' ), true ) ? $atts['strategy'] : 'mobile';

		$orderby = sanitize_text_field( $atts['orderby'] );

		$query_args = array(
			'post_type'      => 'psp_site',
			'post_status'    => 'publish',
			'posts_per_page' => intval( $atts['limit'] ),
			'order'          => 'ASC' === strtoupper( $atts['order'] ) ? 'ASC' : 'DESC',
		);

		// Allow ordering by score fields.
		$score_fields = array( 'performance', 'accessibility', 'best_practices', 'seo' );
		if ( in_array( $orderby, $score_fields, true ) ) {
			$prefix                    = ( 'desktop' === $strategy ) ? '_psp_desktop_' : '_psp_';
			$query_args['meta_key']    = $prefix . $orderby; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			$query_args['orderby']     = 'meta_value_num';
		} else {
			$query_args['orderby'] = 'date';
		}

		$sites = new WP_Query( $query_args );

		if ( ! $sites->have_posts() ) {
			return '<p class="psp-no-sites">' . esc_html__( 'No sites to display.', 'pagespeed-portfolio' ) . '</p>';
		}

		// Enqueue front-end assets only when shortcode is used.
		wp_enqueue_style( 'psp-public' );
		wp_enqueue_script( 'psp-public' );

		ob_start();

		echo '<div class="psp-portfolio-grid" style="--psp-columns: ' . esc_attr( $columns ) . ';">';

		while ( $sites->have_posts() ) {
			$sites->the_post();

			$post_id = get_the_ID();

			$mobile_scores = array(
				'performance'    => (int) get_post_meta( $post_id, '_psp_performance', true ),
				'accessibility'  => (int) get_post_meta( $post_id, '_psp_accessibility', true ),
				'best_practices' => (int) get_post_meta( $post_id, '_psp_best_practices', true ),
				'seo'            => (int) get_post_meta( $post_id, '_psp_seo', true ),
			);

			$desktop_scores = array(
				'performance'    => (int) get_post_meta( $post_id, '_psp_desktop_performance', true ),
				'accessibility'  => (int) get_post_meta( $post_id, '_psp_desktop_accessibility', true ),
				'best_practices' => (int) get_post_meta( $post_id, '_psp_desktop_best_practices', true ),
				'seo'            => (int) get_post_meta( $post_id, '_psp_desktop_seo', true ),
			);

			$site_url     = esc_url( get_post_meta( $post_id, '_psp_site_url', true ) );
			$last_fetched = get_post_meta( $post_id, '_psp_last_fetched', true );

			include PSP_PLUGIN_DIR . 'templates/single-site-card.php';
		}

		echo '</div>';

		wp_reset_postdata();

		return ob_get_clean();
	}
}
