<?php
/**
 * Template for a single site card in the portfolio grid.
 *
 * Variables available:
 *   $post_id        - The post ID.
 *   $mobile_scores  - Array of mobile score values.
 *   $desktop_scores - Array of desktop score values.
 *   $site_url       - The site URL.
 *   $last_fetched   - Last fetch timestamp.
 *
 * @package PageSpeed_Portfolio
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$score_labels = array(
	'performance'    => __( 'Performance', 'pagespeed-portfolio' ),
	'accessibility'  => __( 'Accessibility', 'pagespeed-portfolio' ),
	'best_practices' => __( 'Best Practices', 'pagespeed-portfolio' ),
	'seo'            => __( 'SEO', 'pagespeed-portfolio' ),
);

$strategies = array(
	'mobile'  => array(
		'label'  => __( 'Mobile', 'pagespeed-portfolio' ),
		'icon'   => 'dashicons-smartphone',
		'scores' => $mobile_scores,
	),
	'desktop' => array(
		'label'  => __( 'Desktop', 'pagespeed-portfolio' ),
		'icon'   => 'dashicons-desktop',
		'scores' => $desktop_scores,
	),
);
?>
<div class="psp-site-card">
	<?php if ( has_post_thumbnail( $post_id ) ) : ?>
		<div class="psp-site-card__image">
			<?php if ( ! empty( $site_url ) ) : ?>
				<a href="<?php echo esc_url( $site_url ); ?>" target="_blank" rel="noopener noreferrer">
					<?php echo get_the_post_thumbnail( $post_id, 'medium_large', array( 'class' => 'psp-site-card__thumbnail' ) ); ?>
				</a>
			<?php else : ?>
				<?php echo get_the_post_thumbnail( $post_id, 'medium_large', array( 'class' => 'psp-site-card__thumbnail' ) ); ?>
			<?php endif; ?>
		</div>
	<?php endif; ?>

	<div class="psp-site-card__content">
		<h3 class="psp-site-card__title">
			<?php if ( ! empty( $site_url ) ) : ?>
				<a href="<?php echo esc_url( $site_url ); ?>" target="_blank" rel="noopener noreferrer">
					<?php echo esc_html( get_the_title( $post_id ) ); ?>
				</a>
			<?php else : ?>
				<?php echo esc_html( get_the_title( $post_id ) ); ?>
			<?php endif; ?>
		</h3>

		<?php foreach ( $strategies as $strategy_key => $strategy_data ) : ?>
			<div class="psp-site-card__strategy-section">
				<h4 class="psp-site-card__strategy-label">
					<span class="dashicons <?php echo esc_attr( $strategy_data['icon'] ); ?>"></span>
					<?php echo esc_html( $strategy_data['label'] ); ?>
				</h4>

				<div class="psp-site-card__scores">
					<?php
					foreach ( $score_labels as $key => $label ) :
						$score = isset( $strategy_data['scores'][ $key ] ) ? (int) $strategy_data['scores'][ $key ] : 0;

						if ( $score >= 90 ) {
							$color_class = 'psp-score--good';
						} elseif ( $score >= 50 ) {
							$color_class = 'psp-score--average';
						} else {
							$color_class = 'psp-score--poor';
						}
						?>
						<div class="psp-score-gauge <?php echo esc_attr( $color_class ); ?>">
							<div class="psp-score-gauge__circle" data-score="<?php echo esc_attr( $score ); ?>">
								<svg viewBox="0 0 120 120" class="psp-score-gauge__svg">
									<circle class="psp-score-gauge__bg" cx="60" cy="60" r="54" />
									<circle class="psp-score-gauge__fill" cx="60" cy="60" r="54"
										stroke-dasharray="339.292"
										stroke-dashoffset="<?php echo esc_attr( 339.292 * ( 1 - $score / 100 ) ); ?>" />
								</svg>
								<span class="psp-score-gauge__value"><?php echo esc_html( $score ); ?></span>
							</div>
							<span class="psp-score-gauge__label"><?php echo esc_html( $label ); ?></span>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		<?php endforeach; ?>

		<?php if ( ! empty( $last_fetched ) ) : ?>
			<div class="psp-site-card__meta">
				<span class="psp-site-card__updated">
					<?php
					printf(
						/* translators: %s: relative time since last update */
						esc_html__( 'Updated %s ago', 'pagespeed-portfolio' ),
						esc_html( human_time_diff( strtotime( $last_fetched ), current_time( 'timestamp' ) ) )
					);
					?>
				</span>
			</div>
		<?php endif; ?>
	</div>
</div>
