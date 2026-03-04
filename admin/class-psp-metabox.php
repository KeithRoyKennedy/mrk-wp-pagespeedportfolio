<?php
/**
 * Metabox for the psp_site custom post type.
 *
 * @package PageSpeed_Portfolio
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class PSP_Metabox
 *
 * Registers and handles the Site Details metabox.
 */
class PSP_Metabox {

	/**
	 * Initialize hooks.
	 */
	public static function init() {
		add_action( 'add_meta_boxes', array( __CLASS__, 'register_metaboxes' ) );
		add_action( 'save_post_psp_site', array( __CLASS__, 'save_metabox' ), 10, 2 );
		add_action( 'wp_ajax_psp_fetch_scores', array( __CLASS__, 'ajax_fetch_scores' ) );
		add_action( 'wp_ajax_psp_poll_status', array( __CLASS__, 'ajax_poll_status' ) );
	}

	/**
	 * Register metaboxes for the psp_site post type.
	 */
	public static function register_metaboxes() {
		add_meta_box(
			'psp_site_details',
			__( 'Site Details', 'pagespeed-portfolio' ),
			array( __CLASS__, 'render_details_metabox' ),
			'psp_site',
			'normal',
			'high'
		);

		add_meta_box(
			'psp_site_scores',
			__( 'PageSpeed Scores', 'pagespeed-portfolio' ),
			array( __CLASS__, 'render_scores_metabox' ),
			'psp_site',
			'normal',
			'default'
		);
	}

	/**
	 * Render the Site Details metabox.
	 *
	 * @param WP_Post $post The current post object.
	 */
	public static function render_details_metabox( $post ) {
		wp_nonce_field( 'psp_save_site_details', 'psp_site_nonce' );

		$site_url = get_post_meta( $post->ID, '_psp_site_url', true );
		?>
		<table class="form-table">
			<tr>
				<th scope="row">
					<label for="psp_site_url"><?php esc_html_e( 'Site URL', 'pagespeed-portfolio' ); ?></label>
				</th>
				<td>
					<input
						type="url"
						id="psp_site_url"
						name="psp_site_url"
						value="<?php echo esc_url( $site_url ); ?>"
						class="large-text"
						placeholder="https://example.com"
					/>
					<p class="description">
						<?php esc_html_e( 'Enter the full URL of the site to audit. Scores will be fetched automatically when saved.', 'pagespeed-portfolio' ); ?>
					</p>
				</td>
			</tr>
		</table>
		<?php if ( $post->ID && get_post_status( $post->ID ) === 'publish' && ! empty( $site_url ) ) : ?>
			<p>
				<button type="button" class="button button-secondary" id="psp-refresh-scores" data-post-id="<?php echo esc_attr( $post->ID ); ?>">
					<?php esc_html_e( 'Refresh Scores Now', 'pagespeed-portfolio' ); ?>
				</button>
				<span id="psp-refresh-status"></span>
			</p>
		<?php endif; ?>
		<?php
	}

	/**
	 * Render the PageSpeed Scores metabox.
	 *
	 * @param WP_Post $post The current post object.
	 */
	public static function render_scores_metabox( $post ) {
		$last_fetched = get_post_meta( $post->ID, '_psp_last_fetched', true );

		$strategies = array(
			'mobile'  => array(
				'label'  => __( 'Mobile', 'pagespeed-portfolio' ),
				'prefix' => '_psp_',
			),
			'desktop' => array(
				'label'  => __( 'Desktop', 'pagespeed-portfolio' ),
				'prefix' => '_psp_desktop_',
			),
		);

		$has_scores = ! empty( get_post_meta( $post->ID, '_psp_performance', true ) );
		?>

		<?php if ( ! $has_scores ) : ?>
			<p class="psp-no-scores">
				<?php esc_html_e( 'No scores fetched yet. Add a URL above and save the post to fetch scores.', 'pagespeed-portfolio' ); ?>
			</p>
		<?php else : ?>
			<?php if ( $last_fetched ) : ?>
				<p class="psp-last-fetched">
					<?php
					printf(
						/* translators: %s: date/time of last fetch */
						esc_html__( 'Last updated: %s', 'pagespeed-portfolio' ),
						esc_html( $last_fetched )
					);
					?>
				</p>
			<?php endif; ?>

			<?php foreach ( $strategies as $strategy => $info ) : ?>
				<h4><?php echo esc_html( $info['label'] ); ?></h4>
				<div class="psp-admin-scores">
					<?php
					$fields = array(
						'performance'    => __( 'Performance', 'pagespeed-portfolio' ),
						'accessibility'  => __( 'Accessibility', 'pagespeed-portfolio' ),
						'best_practices' => __( 'Best Practices', 'pagespeed-portfolio' ),
						'seo'            => __( 'SEO', 'pagespeed-portfolio' ),
					);

					foreach ( $fields as $key => $label ) :
						$score = (int) get_post_meta( $post->ID, $info['prefix'] . $key, true );
						$color = self::get_score_color( $score );
						?>
						<div class="psp-admin-score-item">
							<span class="psp-admin-score-value" style="color: <?php echo esc_attr( $color ); ?>;">
								<?php echo esc_html( $score ); ?>
							</span>
							<span class="psp-admin-score-label"><?php echo esc_html( $label ); ?></span>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endforeach; ?>
		<?php endif; ?>
		<?php
	}

	/**
	 * Flag to prevent recursive saves.
	 *
	 * @var bool
	 */
	private static $saving = false;

	/**
	 * Save the metabox data.
	 *
	 * @param int     $post_id The post ID.
	 * @param WP_Post $post    The post object.
	 */
	public static function save_metabox( $post_id, $post ) {
		// Prevent recursive saves.
		if ( self::$saving ) {
			return;
		}

		// Verify nonce.
		if ( ! isset( $_POST['psp_site_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['psp_site_nonce'] ) ), 'psp_save_site_details' ) ) {
			return;
		}

		// Check autosave.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Check permissions.
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		self::$saving = true;

		// Save URL.
		if ( isset( $_POST['psp_site_url'] ) ) {
			$url = esc_url_raw( wp_unslash( $_POST['psp_site_url'] ) );
			update_post_meta( $post_id, '_psp_site_url', $url );

			// Schedule background score fetch if URL is set and post is published.
			if ( ! empty( $url ) && 'publish' === $post->post_status ) {
				PSP_Cron::schedule_site_fetch( $post_id );
			}
		}

		self::$saving = false;
	}

	/**
	 * AJAX handler: schedule a background score refresh.
	 */
	public static function ajax_fetch_scores() {
		check_ajax_referer( 'psp_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'pagespeed-portfolio' ) ) );
		}

		$post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;

		if ( ! $post_id || 'psp_site' !== get_post_type( $post_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid site.', 'pagespeed-portfolio' ) ) );
		}

		// Schedule background fetch — returns immediately.
		PSP_Cron::schedule_site_fetch( $post_id );

		wp_send_json_success(
			array(
				'message' => __( 'Score refresh scheduled. Mobile scores will update in ~30 seconds, desktop in ~90 seconds.', 'pagespeed-portfolio' ),
				'status'  => 'fetching',
			)
		);
	}

	/**
	 * AJAX handler: poll for background fetch status.
	 */
	public static function ajax_poll_status() {
		check_ajax_referer( 'psp_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'pagespeed-portfolio' ) ) );
		}

		$post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;

		if ( ! $post_id || 'psp_site' !== get_post_type( $post_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid site.', 'pagespeed-portfolio' ) ) );
		}

		$status = get_post_meta( $post_id, '_psp_fetch_status', true );
		$error  = get_post_meta( $post_id, '_psp_fetch_error', true );

		wp_send_json_success(
			array(
				'status' => $status ? $status : 'idle',
				'error'  => $error ? $error : '',
			)
		);
	}

	/**
	 * Get the color for a score value.
	 *
	 * @param int $score The score (0-100).
	 * @return string Hex color code.
	 */
	public static function get_score_color( $score ) {
		if ( $score >= 90 ) {
			return '#0cce6b';
		} elseif ( $score >= 50 ) {
			return '#ffa400';
		}
		return '#ff4e42';
	}
}
