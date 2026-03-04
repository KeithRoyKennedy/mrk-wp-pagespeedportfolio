<?php
/**
 * Settings page using the WordPress Settings API.
 *
 * @package PageSpeed_Portfolio
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class PSP_Settings
 *
 * Registers the plugin settings page and fields.
 */
class PSP_Settings {

	/**
	 * Initialize hooks.
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'add_settings_page' ) );
		add_action( 'admin_init', array( __CLASS__, 'register_settings' ) );
	}

	/**
	 * Add the settings page under the Settings menu.
	 */
	public static function add_settings_page() {
		add_options_page(
			__( 'PageSpeed Portfolio', 'pagespeed-portfolio' ),
			__( 'PageSpeed Portfolio', 'pagespeed-portfolio' ),
			'manage_options',
			'pagespeed-portfolio',
			array( __CLASS__, 'render_settings_page' )
		);
	}

	/**
	 * Register settings, sections, and fields.
	 */
	public static function register_settings() {
		// API Key setting.
		register_setting(
			'psp_settings_group',
			'psp_api_key',
			array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => '',
			)
		);

		// Cron schedule setting.
		register_setting(
			'psp_settings_group',
			'psp_cron_schedule',
			array(
				'type'              => 'string',
				'sanitize_callback' => array( __CLASS__, 'sanitize_cron_schedule' ),
				'default'           => 'daily',
			)
		);

		// API section.
		add_settings_section(
			'psp_api_section',
			__( 'API Configuration', 'pagespeed-portfolio' ),
			array( __CLASS__, 'render_api_section' ),
			'pagespeed-portfolio'
		);

		add_settings_field(
			'psp_api_key',
			__( 'Google PageSpeed API Key', 'pagespeed-portfolio' ),
			array( __CLASS__, 'render_api_key_field' ),
			'pagespeed-portfolio',
			'psp_api_section'
		);

		// Schedule section.
		add_settings_section(
			'psp_schedule_section',
			__( 'Refresh Schedule', 'pagespeed-portfolio' ),
			array( __CLASS__, 'render_schedule_section' ),
			'pagespeed-portfolio'
		);

		add_settings_field(
			'psp_cron_schedule',
			__( 'Score Refresh Frequency', 'pagespeed-portfolio' ),
			array( __CLASS__, 'render_cron_schedule_field' ),
			'pagespeed-portfolio',
			'psp_schedule_section'
		);

		// Usage section.
		add_settings_section(
			'psp_usage_section',
			__( 'Usage', 'pagespeed-portfolio' ),
			array( __CLASS__, 'render_usage_section' ),
			'pagespeed-portfolio'
		);
	}

	/**
	 * Sanitize the cron schedule value.
	 *
	 * @param string $value The submitted value.
	 * @return string
	 */
	public static function sanitize_cron_schedule( $value ) {
		$allowed = array( 'daily', 'twicedaily' );
		if ( ! in_array( $value, $allowed, true ) ) {
			return 'daily';
		}

		// Reschedule cron if the schedule changed.
		$current = get_option( 'psp_cron_schedule', 'daily' );
		if ( $current !== $value ) {
			$timestamp = wp_next_scheduled( 'psp_daily_score_refresh' );
			if ( $timestamp ) {
				wp_unschedule_event( $timestamp, 'psp_daily_score_refresh' );
			}
			wp_schedule_event( time(), $value, 'psp_daily_score_refresh' );
		}

		return $value;
	}

	/**
	 * Render the settings page.
	 */
	public static function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<form action="options.php" method="post">
				<?php
				settings_fields( 'psp_settings_group' );
				do_settings_sections( 'pagespeed-portfolio' );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Render the API section description.
	 */
	public static function render_api_section() {
		echo '<p>' . esc_html__( 'Enter your Google PageSpeed Insights API key. You can obtain one from the', 'pagespeed-portfolio' );
		echo ' <a href="https://console.cloud.google.com/apis/credentials" target="_blank" rel="noopener noreferrer">';
		echo esc_html__( 'Google Cloud Console', 'pagespeed-portfolio' );
		echo '</a>.</p>';
	}

	/**
	 * Render the API key input field.
	 */
	public static function render_api_key_field() {
		$value = get_option( 'psp_api_key', '' );
		?>
		<input
			type="password"
			id="psp_api_key"
			name="psp_api_key"
			value="<?php echo esc_attr( $value ); ?>"
			class="regular-text"
			autocomplete="off"
		/>
		<p class="description">
			<?php esc_html_e( 'Your API key is stored securely and never displayed publicly.', 'pagespeed-portfolio' ); ?>
		</p>
		<?php
	}

	/**
	 * Render the schedule section description.
	 */
	public static function render_schedule_section() {
		$next = wp_next_scheduled( 'psp_daily_score_refresh' );
		if ( $next ) {
			echo '<p>';
			printf(
				/* translators: %s: date/time of next scheduled refresh */
				esc_html__( 'Next scheduled refresh: %s', 'pagespeed-portfolio' ),
				esc_html( get_date_from_gmt( gmdate( 'Y-m-d H:i:s', $next ), get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) ) )
			);
			echo '</p>';
		}
	}

	/**
	 * Render the cron schedule radio buttons.
	 */
	public static function render_cron_schedule_field() {
		$value = get_option( 'psp_cron_schedule', 'daily' );
		?>
		<fieldset>
			<label>
				<input type="radio" name="psp_cron_schedule" value="daily" <?php checked( $value, 'daily' ); ?> />
				<?php esc_html_e( 'Once daily', 'pagespeed-portfolio' ); ?>
			</label>
			<br />
			<label>
				<input type="radio" name="psp_cron_schedule" value="twicedaily" <?php checked( $value, 'twicedaily' ); ?> />
				<?php esc_html_e( 'Twice daily', 'pagespeed-portfolio' ); ?>
			</label>
		</fieldset>
		<?php
	}

	/**
	 * Render the usage section.
	 */
	public static function render_usage_section() {
		?>
		<p><?php esc_html_e( 'Use the following shortcode to display your portfolio:', 'pagespeed-portfolio' ); ?></p>
		<code>[pagespeed_portfolio]</code>
		<p><?php esc_html_e( 'Available attributes:', 'pagespeed-portfolio' ); ?></p>
		<ul style="list-style: disc; padding-left: 20px;">
			<li><code>columns</code> &mdash; <?php esc_html_e( 'Number of columns (default: 3)', 'pagespeed-portfolio' ); ?></li>
			<li><code>orderby</code> &mdash; <?php esc_html_e( 'Order by: date, performance, accessibility, best_practices, seo', 'pagespeed-portfolio' ); ?></li>
			<li><code>order</code> &mdash; <?php esc_html_e( 'ASC or DESC (default: DESC)', 'pagespeed-portfolio' ); ?></li>
			<li><code>limit</code> &mdash; <?php esc_html_e( 'Number of sites to show (default: all)', 'pagespeed-portfolio' ); ?></li>
			<li><code>strategy</code> &mdash; <?php esc_html_e( 'mobile or desktop (default: mobile)', 'pagespeed-portfolio' ); ?></li>
		</ul>
		<p><strong><?php esc_html_e( 'Example:', 'pagespeed-portfolio' ); ?></strong> <code>[pagespeed_portfolio columns="3" orderby="performance" order="DESC" strategy="mobile"]</code></p>
		<?php
	}
}
