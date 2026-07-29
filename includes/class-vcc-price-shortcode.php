<?php
/**
 * VCC_Price_Shortcode – Handles [vcc_base_price_compare] shortcode.
 *
 * Shortcode parameters:
 *   factory  – Factory name (e.g. "South Georgia")
 *   size     – Trailer size (e.g. "4x6 Single Axle")
 *   markup   – Dollar amount to add on top of the base price (e.g. "200")
 *   display  – "full" (default) shows the full price card;
 *              "price_only" shows only the formatted price with Font Price Tools styling.
 *
 * Example – full card:
 *   [vcc_base_price_compare factory="South Georgia" size="4x6 Single Axle" markup="200"]
 *
 * Example – price only:
 *   [vcc_base_price_compare factory="South Georgia" size="4x6 Single Axle" markup="200" display="price_only"]
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class VCC_Price_Shortcode {

	/** WordPress option key that stores all factory base prices. */
	const OPTION_PRICES = 'vcc_factory_prices';

	/** @var VCC_Price_Shortcode|null */
	private static $instance = null;

	// ── Singleton ─────────────────────────────────────────────────────────────

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_shortcode( 'vcc_base_price_compare', array( $this, 'render_shortcode' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_styles' ) );
	}

	// ── Public API ────────────────────────────────────────────────────────────

	/**
	 * Render the shortcode.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string HTML output.
	 */
	public function render_shortcode( $atts ) {
		$atts = shortcode_atts(
			array(
				'factory' => '',
				'size'    => '',
				'markup'  => '0',
				'display' => 'full',
			),
			$atts,
			'vcc_base_price_compare'
		);

		$factory = sanitize_text_field( $atts['factory'] );
		$size    = sanitize_text_field( $atts['size'] );
		$markup  = (float) $atts['markup'];
		$display = sanitize_key( $atts['display'] );

		if ( '' === $factory || '' === $size ) {
			return '';
		}

		$base_price = $this->get_base_price( $factory, $size );

		if ( false === $base_price ) {
			return '';
		}

		$total_price = $base_price + $markup;

		if ( 'price_only' === $display ) {
			return $this->render_price_only( $total_price );
		}

		return $this->render_full_card( $factory, $size, $base_price, $markup, $total_price );
	}

	/**
	 * Return the base price for a given factory + size combination.
	 *
	 * @param string $factory Factory name.
	 * @param string $size    Trailer size.
	 * @return float|false Base price or false if not found.
	 */
	public static function get_base_price( $factory, $size ) {
		$prices = get_option( self::OPTION_PRICES, array() );

		if ( ! is_array( $prices ) ) {
			return false;
		}

		if ( isset( $prices[ $factory ][ $size ] ) ) {
			return (float) $prices[ $factory ][ $size ];
		}

		return false;
	}

	/**
	 * Persist the base price for a factory + size pair.
	 *
	 * @param string $factory    Factory name.
	 * @param string $size       Trailer size.
	 * @param float  $base_price Base price value.
	 */
	public static function set_base_price( $factory, $size, $base_price ) {
		$prices = get_option( self::OPTION_PRICES, array() );

		if ( ! is_array( $prices ) ) {
			$prices = array();
		}

		if ( ! isset( $prices[ $factory ] ) ) {
			$prices[ $factory ] = array();
		}

		$prices[ $factory ][ $size ] = (float) $base_price;
		update_option( self::OPTION_PRICES, $prices );
	}

	// ── Rendering ─────────────────────────────────────────────────────────────

	/**
	 * Render the price-only output using Font Price Tools styling.
	 *
	 * @param float $total_price Total calculated price.
	 * @return string HTML span with inline styles.
	 */
	private function render_price_only( $total_price ) {
		$style    = VCC_Font_Price_Tools::get_price_only_inline_style();
		$settings = VCC_Font_Price_Tools::get_price_only_settings();
		$extra    = sanitize_html_class( $settings['custom_css_class'] );
		$classes  = trim( 'vcc-price-only ' . $extra );

		return sprintf(
			'<span class="%s"%s>%s</span>',
			esc_attr( $classes ),
			$style ? ' style="' . esc_attr( $style ) . '"' : '',
			esc_html( '$' . number_format( $total_price, 0 ) )
		);
	}

	/**
	 * Render the full price comparison card.
	 *
	 * @param string $factory     Factory name.
	 * @param string $size        Trailer size label.
	 * @param float  $base_price  Base price before markup.
	 * @param float  $markup      Markup amount.
	 * @param float  $total_price Total price.
	 * @return string HTML card output.
	 */
	private function render_full_card( $factory, $size, $base_price, $markup, $total_price ) {
		ob_start();
		?>
		<div class="vcc-price-card">
			<div class="vcc-price-card__header">
				<span class="vcc-price-card__factory"><?php echo esc_html( $factory ); ?></span>
				<span class="vcc-price-card__size"><?php echo esc_html( $size ); ?></span>
			</div>
			<div class="vcc-price-card__body">
				<div class="vcc-price-card__row">
					<span class="vcc-price-card__label"><?php esc_html_e( 'Base Price', 'beforeaftermycare' ); ?></span>
					<span class="vcc-price-card__value"><?php echo esc_html( '$' . number_format( $base_price, 0 ) ); ?></span>
				</div>
				<?php if ( $markup > 0 ) : ?>
				<div class="vcc-price-card__row vcc-price-card__row--markup">
					<span class="vcc-price-card__label"><?php esc_html_e( 'Markup', 'beforeaftermycare' ); ?></span>
					<span class="vcc-price-card__value">+<?php echo esc_html( '$' . number_format( $markup, 0 ) ); ?></span>
				</div>
				<?php endif; ?>
				<div class="vcc-price-card__row vcc-price-card__row--total">
					<span class="vcc-price-card__label"><?php esc_html_e( 'Total', 'beforeaftermycare' ); ?></span>
					<span class="vcc-price-card__value vcc-price-card__total"><?php echo esc_html( '$' . number_format( $total_price, 0 ) ); ?></span>
				</div>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	// ── Assets ────────────────────────────────────────────────────────────────

	/**
	 * Enqueue frontend CSS for price shortcode output.
	 */
	public function enqueue_frontend_styles() {
		wp_enqueue_style(
			'vcc-price-shortcode',
			BAM_PLUGIN_URL . 'assets/css/vcc-price-shortcode.css',
			array(),
			BAM_VERSION
		);
	}
}
