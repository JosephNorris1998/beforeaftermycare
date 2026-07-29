<?php
/**
 * VCC_Font_Price_Tools – Admin page for controlling price display typography.
 *
 * Provides a "Font Price Tools" submenu under the BAM admin menu.
 * The settings saved here are consumed by the [vcc_base_price_compare] shortcode
 * when the display="price_only" parameter is used.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class VCC_Font_Price_Tools {

	/** WordPress option key for price-only style settings. */
	const OPTION_PRICE_ONLY_STYLE = 'vcc_price_only_style';

	/** Menu slug. */
	const MENU_SLUG = 'vcc-font-price-tools';

	/** Nonce action for saving settings. */
	const NONCE_SAVE = 'vcc_save_font_price_tools';

	/** @var VCC_Font_Price_Tools|null */
	private static $instance = null;

	// ── Singleton ─────────────────────────────────────────────────────────────

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'admin_menu',       array( $this, 'register_menu' ) );
		add_action( 'admin_post_vcc_save_font_price_tools', array( $this, 'handle_save' ) );
	}

	// ── Menu ──────────────────────────────────────────────────────────────────

	/**
	 * Register the "Font Price Tools" submenu page under the BAM admin menu.
	 */
	public function register_menu() {
		add_submenu_page(
			BAM_Admin::MENU_SLUG,
			__( 'Font Price Tools', 'beforeaftermycare' ),
			__( 'Font Price Tools', 'beforeaftermycare' ),
			'manage_options',
			self::MENU_SLUG,
			array( $this, 'render_page' )
		);
	}

	// ── Page Rendering ────────────────────────────────────────────────────────

	/**
	 * Render the Font Price Tools admin page.
	 */
	public function render_page() {
		$msg       = isset( $_GET['vcc_msg'] ) ? sanitize_key( $_GET['vcc_msg'] ) : '';
		$settings  = self::get_price_only_settings();
		include BAM_PLUGIN_DIR . 'templates/admin-font-price-tools.php';
	}

	// ── Settings API ──────────────────────────────────────────────────────────

	/**
	 * Get the price-only display settings with defaults.
	 *
	 * @return array Associative array of style settings.
	 */
	public static function get_price_only_settings() {
		$defaults = array(
			'font_size'        => '28',
			'font_size_unit'   => 'px',
			'font_weight'      => '700',
			'font_color'       => '#1a7a1a',
			'font_family'      => '',
			'letter_spacing'   => '',
			'text_transform'   => 'none',
			'custom_css_class' => '',
		);

		$saved = get_option( self::OPTION_PRICE_ONLY_STYLE, array() );

		if ( ! is_array( $saved ) ) {
			$saved = array();
		}

		return array_merge( $defaults, $saved );
	}

	/**
	 * Build an inline CSS style string from the price-only settings.
	 *
	 * @return string CSS string for use in a style attribute (no trailing semicolon needed).
	 */
	public static function get_price_only_inline_style() {
		$s      = self::get_price_only_settings();
		$parts  = array();

		// Font size.
		$size = (int) $s['font_size'];
		$unit = in_array( $s['font_size_unit'], array( 'px', 'em', 'rem', '%' ), true ) ? $s['font_size_unit'] : 'px';
		if ( $size > 0 ) {
			$parts[] = 'font-size:' . $size . $unit;
		}

		// Font weight.
		$weight_map = array(
			'100', '200', '300', '400', '500', '600', '700', '800', '900',
			'normal', 'bold', 'lighter', 'bolder',
		);
		if ( in_array( $s['font_weight'], $weight_map, true ) ) {
			$parts[] = 'font-weight:' . $s['font_weight'];
		}

		// Font color.
		if ( preg_match( '/^#[0-9a-fA-F]{3,8}$/', $s['font_color'] ) ) {
			$parts[] = 'color:' . $s['font_color'];
		}

		// Font family.
		$family = sanitize_text_field( $s['font_family'] );
		if ( '' !== $family ) {
			$parts[] = 'font-family:' . $family;
		}

		// Letter spacing.
		$ls = sanitize_text_field( $s['letter_spacing'] );
		if ( '' !== $ls ) {
			$parts[] = 'letter-spacing:' . $ls;
		}

		// Text transform.
		$tt_options = array( 'none', 'uppercase', 'lowercase', 'capitalize' );
		if ( in_array( $s['text_transform'], $tt_options, true ) && 'none' !== $s['text_transform'] ) {
			$parts[] = 'text-transform:' . $s['text_transform'];
		}

		return implode( ';', $parts );
	}

	// ── Save Handler ──────────────────────────────────────────────────────────

	/**
	 * Handle the save POST action (hooked to admin_post_vcc_save_font_price_tools).
	 */
	public function handle_save() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'No tienes permiso para realizar esta acción.', 'beforeaftermycare' ) );
		}

		if ( ! isset( $_POST['vcc_font_price_tools_nonce'] )
			|| ! wp_verify_nonce(
				sanitize_text_field( wp_unslash( $_POST['vcc_font_price_tools_nonce'] ) ),
				self::NONCE_SAVE
			)
		) {
			wp_die( esc_html__( 'Acción no autorizada.', 'beforeaftermycare' ) );
		}

		$font_size      = isset( $_POST['vcc_font_size'] )      ? absint( $_POST['vcc_font_size'] )                                                                    : 28;
		$font_size_unit = isset( $_POST['vcc_font_size_unit'] ) ? sanitize_key( $_POST['vcc_font_size_unit'] )                                                         : 'px';
		$font_weight    = isset( $_POST['vcc_font_weight'] )    ? sanitize_text_field( wp_unslash( $_POST['vcc_font_weight'] ) )                                        : '700';
		$font_color     = isset( $_POST['vcc_font_color'] )     ? sanitize_hex_color( wp_unslash( $_POST['vcc_font_color'] ) )                                          : '#1a7a1a';
		$font_family    = isset( $_POST['vcc_font_family'] )    ? sanitize_text_field( wp_unslash( $_POST['vcc_font_family'] ) )                                        : '';
		$letter_spacing = isset( $_POST['vcc_letter_spacing'] ) ? sanitize_text_field( wp_unslash( $_POST['vcc_letter_spacing'] ) )                                     : '';
		$text_transform = isset( $_POST['vcc_text_transform'] ) ? sanitize_key( $_POST['vcc_text_transform'] )                                                          : 'none';
		$custom_class   = isset( $_POST['vcc_custom_css_class'] ) ? sanitize_html_class( wp_unslash( $_POST['vcc_custom_css_class'] ) )                                : '';

		// Validate font_size_unit.
		if ( ! in_array( $font_size_unit, array( 'px', 'em', 'rem', '%' ), true ) ) {
			$font_size_unit = 'px';
		}

		// Validate font_weight.
		$allowed_weights = array( '100', '200', '300', '400', '500', '600', '700', '800', '900', 'normal', 'bold', 'lighter', 'bolder' );
		if ( ! in_array( $font_weight, $allowed_weights, true ) ) {
			$font_weight = '700';
		}

		// Validate text_transform.
		if ( ! in_array( $text_transform, array( 'none', 'uppercase', 'lowercase', 'capitalize' ), true ) ) {
			$text_transform = 'none';
		}

		// Validate font_color fallback.
		if ( ! $font_color ) {
			$font_color = '#1a7a1a';
		}

		$settings = array(
			'font_size'        => $font_size,
			'font_size_unit'   => $font_size_unit,
			'font_weight'      => $font_weight,
			'font_color'       => $font_color,
			'font_family'      => $font_family,
			'letter_spacing'   => $letter_spacing,
			'text_transform'   => $text_transform,
			'custom_css_class' => $custom_class,
		);

		update_option( self::OPTION_PRICE_ONLY_STYLE, $settings );

		wp_redirect( add_query_arg( array( 'page' => self::MENU_SLUG, 'vcc_msg' => 'saved' ), admin_url( 'admin.php' ) ) );
		exit;
	}
}
