<?php
/**
 * Admin template – Font Price Tools
 *
 * Variables available:
 *   $msg      – string  success/error message key
 *   $settings – array   current price-only style settings
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap bam-wrap">

	<h1><?php esc_html_e( 'Font Price Tools', 'beforeaftermycare' ); ?></h1>

	<?php if ( 'saved' === $msg ) : ?>
		<div class="notice notice-success is-dismissible">
			<p><?php esc_html_e( '¡Configuración guardada correctamente!', 'beforeaftermycare' ); ?></p>
		</div>
	<?php endif; ?>

	<p class="description">
		<?php esc_html_e( 'Controla el diseño y tipografía del precio cuando el shortcode usa el parámetro display="price_only".', 'beforeaftermycare' ); ?>
	</p>

	<!-- ── Price Only Display Section ───────────────────────────────────────── -->

	<div class="bam-card" style="max-width:800px;margin-top:20px;">
		<div class="bam-card__header">
			<h2 style="margin:0;font-size:1.1em;">
				<?php esc_html_e( 'Display "price_only" — Diseño del Precio', 'beforeaftermycare' ); ?>
			</h2>
			<p class="description" style="margin:4px 0 0;">
				<?php
				echo wp_kses(
					/* translators: shortcode example */
					__( 'Estas opciones aplican cuando el shortcode incluye <code>display="price_only"</code>. Ejemplo: <code>[vcc_base_price_compare factory="South Georgia" size="4x6 Single Axle" markup="200" display="price_only"]</code>', 'beforeaftermycare' ),
					array( 'code' => array() )
				);
				?>
			</p>
		</div>

		<div class="bam-card__body">
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="vcc_save_font_price_tools">
				<?php wp_nonce_field( VCC_Font_Price_Tools::NONCE_SAVE, 'vcc_font_price_tools_nonce' ); ?>

				<table class="form-table" role="presentation">

					<!-- Font Size -->
					<tr>
						<th scope="row">
							<label for="vcc_font_size"><?php esc_html_e( 'Tamaño de fuente', 'beforeaftermycare' ); ?></label>
						</th>
						<td>
							<input
								type="number"
								id="vcc_font_size"
								name="vcc_font_size"
								value="<?php echo esc_attr( $settings['font_size'] ); ?>"
								min="1"
								max="300"
								step="1"
								style="width:80px;"
							>
							<select name="vcc_font_size_unit" id="vcc_font_size_unit" style="margin-left:6px;">
								<?php foreach ( array( 'px', 'em', 'rem', '%' ) as $unit ) : ?>
									<option value="<?php echo esc_attr( $unit ); ?>" <?php selected( $settings['font_size_unit'], $unit ); ?>>
										<?php echo esc_html( $unit ); ?>
									</option>
								<?php endforeach; ?>
							</select>
							<p class="description"><?php esc_html_e( 'Tamaño de la fuente del precio. Por defecto: 28px.', 'beforeaftermycare' ); ?></p>
						</td>
					</tr>

					<!-- Font Weight -->
					<tr>
						<th scope="row">
							<label for="vcc_font_weight"><?php esc_html_e( 'Peso de fuente', 'beforeaftermycare' ); ?></label>
						</th>
						<td>
							<select name="vcc_font_weight" id="vcc_font_weight">
								<?php
								$weights = array(
									'100'     => '100 – Thin',
									'200'     => '200 – Extra Light',
									'300'     => '300 – Light',
									'400'     => '400 – Normal',
									'500'     => '500 – Medium',
									'600'     => '600 – Semi Bold',
									'700'     => '700 – Bold',
									'800'     => '800 – Extra Bold',
									'900'     => '900 – Black',
									'normal'  => 'Normal',
									'bold'    => 'Bold',
									'lighter' => 'Lighter',
									'bolder'  => 'Bolder',
								);
								foreach ( $weights as $val => $label ) :
									?>
									<option value="<?php echo esc_attr( $val ); ?>" <?php selected( $settings['font_weight'], $val ); ?>>
										<?php echo esc_html( $label ); ?>
									</option>
								<?php endforeach; ?>
							</select>
							<p class="description"><?php esc_html_e( 'Grosor de la fuente. Por defecto: 700 (Bold).', 'beforeaftermycare' ); ?></p>
						</td>
					</tr>

					<!-- Font Color -->
					<tr>
						<th scope="row">
							<label for="vcc_font_color"><?php esc_html_e( 'Color de fuente', 'beforeaftermycare' ); ?></label>
						</th>
						<td>
							<input
								type="color"
								id="vcc_font_color"
								name="vcc_font_color"
								value="<?php echo esc_attr( $settings['font_color'] ); ?>"
								style="width:60px;height:36px;padding:2px;border:1px solid #ccc;cursor:pointer;"
							>
							<input
								type="text"
								id="vcc_font_color_hex"
								name="vcc_font_color"
								value="<?php echo esc_attr( $settings['font_color'] ); ?>"
								maxlength="9"
								placeholder="#1a7a1a"
								style="width:90px;margin-left:8px;vertical-align:middle;"
								oninput="document.getElementById('vcc_font_color').value=this.value"
							>
							<p class="description"><?php esc_html_e( 'Color del texto del precio. Por defecto: #1a7a1a (verde).', 'beforeaftermycare' ); ?></p>
						</td>
					</tr>

					<!-- Font Family -->
					<tr>
						<th scope="row">
							<label for="vcc_font_family"><?php esc_html_e( 'Familia de fuente', 'beforeaftermycare' ); ?></label>
						</th>
						<td>
							<input
								type="text"
								id="vcc_font_family"
								name="vcc_font_family"
								value="<?php echo esc_attr( $settings['font_family'] ); ?>"
								placeholder="<?php esc_attr_e( 'Ej: Arial, sans-serif', 'beforeaftermycare' ); ?>"
								style="width:300px;"
							>
							<p class="description"><?php esc_html_e( 'Fuente tipográfica. Dejar en blanco para usar la fuente del tema.', 'beforeaftermycare' ); ?></p>
						</td>
					</tr>

					<!-- Letter Spacing -->
					<tr>
						<th scope="row">
							<label for="vcc_letter_spacing"><?php esc_html_e( 'Espaciado de letras', 'beforeaftermycare' ); ?></label>
						</th>
						<td>
							<input
								type="text"
								id="vcc_letter_spacing"
								name="vcc_letter_spacing"
								value="<?php echo esc_attr( $settings['letter_spacing'] ); ?>"
								placeholder="<?php esc_attr_e( 'Ej: 0.05em', 'beforeaftermycare' ); ?>"
								style="width:120px;"
							>
							<p class="description"><?php esc_html_e( 'Espaciado entre letras (letter-spacing). Ej: 0.05em, 2px. Dejar en blanco para usar el valor por defecto.', 'beforeaftermycare' ); ?></p>
						</td>
					</tr>

					<!-- Text Transform -->
					<tr>
						<th scope="row">
							<label for="vcc_text_transform"><?php esc_html_e( 'Transformación del texto', 'beforeaftermycare' ); ?></label>
						</th>
						<td>
							<select name="vcc_text_transform" id="vcc_text_transform">
								<?php
								$transforms = array(
									'none'       => __( 'Sin transformación', 'beforeaftermycare' ),
									'uppercase'  => __( 'MAYÚSCULAS', 'beforeaftermycare' ),
									'lowercase'  => __( 'minúsculas', 'beforeaftermycare' ),
									'capitalize' => __( 'Primera Letra En Mayúscula', 'beforeaftermycare' ),
								);
								foreach ( $transforms as $val => $label ) :
									?>
									<option value="<?php echo esc_attr( $val ); ?>" <?php selected( $settings['text_transform'], $val ); ?>>
										<?php echo esc_html( $label ); ?>
									</option>
								<?php endforeach; ?>
							</select>
						</td>
					</tr>

					<!-- Custom CSS Class -->
					<tr>
						<th scope="row">
							<label for="vcc_custom_css_class"><?php esc_html_e( 'Clase CSS personalizada', 'beforeaftermycare' ); ?></label>
						</th>
						<td>
							<input
								type="text"
								id="vcc_custom_css_class"
								name="vcc_custom_css_class"
								value="<?php echo esc_attr( $settings['custom_css_class'] ); ?>"
								placeholder="<?php esc_attr_e( 'Ej: mi-precio-custom', 'beforeaftermycare' ); ?>"
								style="width:200px;"
							>
							<p class="description"><?php esc_html_e( 'Clase CSS adicional que se añadirá al elemento span del precio.', 'beforeaftermycare' ); ?></p>
						</td>
					</tr>

				</table>

				<!-- Live Preview -->
				<div class="vcc-font-preview-wrapper" style="margin:20px 0 10px;padding:16px;background:#f9f9f9;border:1px solid #ddd;border-radius:4px;">
					<p style="margin:0 0 8px;font-weight:600;"><?php esc_html_e( 'Vista previa:', 'beforeaftermycare' ); ?></p>
					<span id="vcc-price-preview" class="vcc-price-only" style="<?php echo esc_attr( VCC_Font_Price_Tools::get_price_only_inline_style() ); ?>">
						$2,800
					</span>
				</div>

				<p class="submit">
					<input
						type="submit"
						class="button button-primary"
						value="<?php esc_attr_e( 'Guardar configuración', 'beforeaftermycare' ); ?>"
					>
				</p>

			</form>
		</div><!-- /.bam-card__body -->
	</div><!-- /.bam-card -->

	<!-- ── Shortcode Reference ───────────────────────────────────────────────── -->

	<div class="bam-card" style="max-width:800px;margin-top:20px;">
		<div class="bam-card__header">
			<h2 style="margin:0;font-size:1.1em;">
				<?php esc_html_e( 'Referencia de Shortcodes', 'beforeaftermycare' ); ?>
			</h2>
		</div>
		<div class="bam-card__body">
			<table class="widefat striped" style="margin-top:0;">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Parámetro', 'beforeaftermycare' ); ?></th>
						<th><?php esc_html_e( 'Descripción', 'beforeaftermycare' ); ?></th>
						<th><?php esc_html_e( 'Valores', 'beforeaftermycare' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td><code>factory</code></td>
						<td><?php esc_html_e( 'Nombre de la fábrica', 'beforeaftermycare' ); ?></td>
						<td><?php esc_html_e( 'Ej: "South Georgia"', 'beforeaftermycare' ); ?></td>
					</tr>
					<tr>
						<td><code>size</code></td>
						<td><?php esc_html_e( 'Tamaño del trailer', 'beforeaftermycare' ); ?></td>
						<td><?php esc_html_e( 'Ej: "4x6 Single Axle"', 'beforeaftermycare' ); ?></td>
					</tr>
					<tr>
						<td><code>markup</code></td>
						<td><?php esc_html_e( 'Monto adicional al precio base', 'beforeaftermycare' ); ?></td>
						<td><?php esc_html_e( 'Número. Ej: "200"', 'beforeaftermycare' ); ?></td>
					</tr>
					<tr>
						<td><code>display</code></td>
						<td><?php esc_html_e( 'Modo de visualización', 'beforeaftermycare' ); ?></td>
						<td>
							<strong><code>full</code></strong> <?php esc_html_e( '(por defecto)', 'beforeaftermycare' ); ?> — <?php esc_html_e( 'muestra fábrica, tamaño, precio base, markup y total', 'beforeaftermycare' ); ?>.<br>
							<strong><code>price_only</code></strong> — <?php esc_html_e( 'muestra solo el precio total, sin fábrica ni markup, con el estilo configurado arriba', 'beforeaftermycare' ); ?>.
						</td>
					</tr>
				</tbody>
			</table>

			<div style="margin-top:16px;padding:12px;background:#fff;border:1px solid #ddd;border-radius:4px;">
				<p style="margin:0 0 8px;font-weight:600;"><?php esc_html_e( 'Ejemplo – Solo precio:', 'beforeaftermycare' ); ?></p>
				<code>[vcc_base_price_compare factory="South Georgia" size="4x6 Single Axle" markup="200" display="price_only"]</code>
				<p style="margin:8px 0 8px;font-weight:600;"><?php esc_html_e( 'Ejemplo – Tarjeta completa:', 'beforeaftermycare' ); ?></p>
				<code>[vcc_base_price_compare factory="South Georgia" size="4x6 Single Axle" markup="200"]</code>
			</div>
		</div>
	</div>

</div><!-- /.wrap -->

<script>
(function() {
	// Live preview update
	var fields = [
		'vcc_font_size', 'vcc_font_size_unit', 'vcc_font_weight',
		'vcc_font_color_hex', 'vcc_font_family', 'vcc_letter_spacing',
		'vcc_text_transform'
	];
	var preview = document.getElementById('vcc-price-preview');

	function updatePreview() {
		if ( ! preview ) return;

		var fontSize   = document.getElementById('vcc_font_size')      ? document.getElementById('vcc_font_size').value      : '28';
		var fontUnit   = document.getElementById('vcc_font_size_unit') ? document.getElementById('vcc_font_size_unit').value  : 'px';
		var fontWeight = document.getElementById('vcc_font_weight')    ? document.getElementById('vcc_font_weight').value     : '700';
		var fontColor  = document.getElementById('vcc_font_color')     ? document.getElementById('vcc_font_color').value      : '#1a7a1a';
		var fontFamily = document.getElementById('vcc_font_family')    ? document.getElementById('vcc_font_family').value     : '';
		var letterSpacing = document.getElementById('vcc_letter_spacing') ? document.getElementById('vcc_letter_spacing').value : '';
		var textTransform = document.getElementById('vcc_text_transform') ? document.getElementById('vcc_text_transform').value : 'none';

		preview.style.fontSize      = fontSize && fontSize > 0 ? fontSize + fontUnit : '';
		preview.style.fontWeight    = fontWeight  || '';
		preview.style.color         = fontColor   || '';
		preview.style.fontFamily    = fontFamily  || '';
		preview.style.letterSpacing = letterSpacing || '';
		preview.style.textTransform = textTransform !== 'none' ? textTransform : '';
	}

	// Sync color picker → hex text field
	var colorPicker = document.getElementById('vcc_font_color');
	var colorHex    = document.getElementById('vcc_font_color_hex');
	if ( colorPicker && colorHex ) {
		colorPicker.addEventListener('input', function() {
			colorHex.value = colorPicker.value;
			updatePreview();
		});
		colorHex.addEventListener('input', function() {
			if ( /^#[0-9a-fA-F]{3,8}$/.test( colorHex.value ) ) {
				colorPicker.value = colorHex.value;
			}
			updatePreview();
		});
	}

	fields.forEach(function(id) {
		var el = document.getElementById(id);
		if ( el ) {
			el.addEventListener('input', updatePreview);
			el.addEventListener('change', updatePreview);
		}
	});
}());
</script>
