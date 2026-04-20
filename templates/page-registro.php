<?php
/**
 * Standalone registration page – no Elementor header or footer.
 *
 * This file is served via BAM_Registration::intercept_template() and is a
 * complete HTML document so the theme/page-builder wrapper is bypassed
 * entirely.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$errors = BAM_Registration::get_errors();

$old = function( $key ) {
	// phpcs:ignore WordPress.Security.NonceVerification
	return isset( $_POST[ 'bam_' . $key ] ) ? esc_attr( sanitize_text_field( wp_unslash( $_POST[ 'bam_' . $key ] ) ) ) : '';
};
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?php esc_html_e( 'Registro de Paciente', 'beforeaftermycare' ); ?> – <?php bloginfo( 'name' ); ?></title>
	<link rel="stylesheet" href="<?php echo esc_url( BAM_PLUGIN_URL . 'assets/css/frontend.css' ); ?>?v=<?php echo esc_attr( BAM_VERSION ); ?>">
	<style>
		html, body {
			margin: 0;
			padding: 0;
			background: #f0f4f8;
		}
	</style>
</head>
<body class="bam-registration-page">

<div class="bam-registration-wrapper">
	<div class="bam-registration-card">

		<div class="bam-registration-header">
			<div class="bam-logo-mark">
				<svg width="48" height="48" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
					<circle cx="24" cy="24" r="24" fill="#0077b6"/>
					<path d="M24 12c-6.627 0-12 5.373-12 12s5.373 12 12 12 12-5.373 12-12S30.627 12 24 12zm1 17h-2v-6h2v6zm0-8h-2v-2h2v2z" fill="#fff"/>
				</svg>
			</div>
			<h1 class="bam-registration-title"><?php esc_html_e( 'Registro de Paciente', 'beforeaftermycare' ); ?></h1>
			<p class="bam-registration-subtitle"><?php esc_html_e( 'Crea tu cuenta para acceder a tu guía médica personalizada.', 'beforeaftermycare' ); ?></p>
		</div>

		<?php if ( ! empty( $errors['general'] ) ) : ?>
			<div class="bam-notice bam-notice-error" role="alert">
				<?php echo esc_html( $errors['general'] ); ?>
			</div>
		<?php endif; ?>

		<form class="bam-registration-form" method="post" action="" novalidate>
			<?php wp_nonce_field( BAM_Registration::NONCE_ACTION, 'bam_nonce' ); ?>

			<!-- Nombre Completo -->
			<div class="bam-field <?php echo ! empty( $errors['nombre'] ) ? 'bam-field-error' : ''; ?>">
				<label class="bam-label" for="bam_nombre">
					<?php esc_html_e( 'Nombre Completo', 'beforeaftermycare' ); ?>
					<span class="bam-required" aria-hidden="true">*</span>
				</label>
				<input
					class="bam-input"
					type="text"
					id="bam_nombre"
					name="bam_nombre"
					value="<?php echo $old( 'nombre' ); ?>"
					placeholder="<?php esc_attr_e( 'Ej: María López García', 'beforeaftermycare' ); ?>"
					autocomplete="name"
					required
				>
				<?php if ( ! empty( $errors['nombre'] ) ) : ?>
					<span class="bam-field-message"><?php echo esc_html( $errors['nombre'] ); ?></span>
				<?php endif; ?>
			</div>

			<!-- Usuario -->
			<div class="bam-field <?php echo ! empty( $errors['usuario'] ) ? 'bam-field-error' : ''; ?>">
				<label class="bam-label" for="bam_usuario">
					<?php esc_html_e( 'Nombre de Usuario', 'beforeaftermycare' ); ?>
					<span class="bam-required" aria-hidden="true">*</span>
				</label>
				<input
					class="bam-input"
					type="text"
					id="bam_usuario"
					name="bam_usuario"
					value="<?php echo $old( 'usuario' ); ?>"
					placeholder="<?php esc_attr_e( 'Ej: mlopez98', 'beforeaftermycare' ); ?>"
					autocomplete="username"
					required
				>
				<?php if ( ! empty( $errors['usuario'] ) ) : ?>
					<span class="bam-field-message"><?php echo esc_html( $errors['usuario'] ); ?></span>
				<?php endif; ?>
			</div>

			<!-- Correo -->
			<div class="bam-field <?php echo ! empty( $errors['correo'] ) ? 'bam-field-error' : ''; ?>">
				<label class="bam-label" for="bam_correo">
					<?php esc_html_e( 'Correo Electrónico', 'beforeaftermycare' ); ?>
					<span class="bam-required" aria-hidden="true">*</span>
				</label>
				<input
					class="bam-input"
					type="email"
					id="bam_correo"
					name="bam_correo"
					value="<?php echo $old( 'correo' ); ?>"
					placeholder="<?php esc_attr_e( 'ejemplo@correo.com', 'beforeaftermycare' ); ?>"
					autocomplete="email"
					required
				>
				<?php if ( ! empty( $errors['correo'] ) ) : ?>
					<span class="bam-field-message"><?php echo esc_html( $errors['correo'] ); ?></span>
				<?php endif; ?>
			</div>

			<!-- Contraseña -->
			<div class="bam-field bam-field-row">
				<div class="bam-field-half <?php echo ! empty( $errors['password'] ) ? 'bam-field-error' : ''; ?>">
					<label class="bam-label" for="bam_password">
						<?php esc_html_e( 'Contraseña', 'beforeaftermycare' ); ?>
						<span class="bam-required" aria-hidden="true">*</span>
					</label>
					<div class="bam-input-password-wrapper">
						<input
							class="bam-input"
							type="password"
							id="bam_password"
							name="bam_password"
							placeholder="<?php esc_attr_e( 'Mínimo 8 caracteres', 'beforeaftermycare' ); ?>"
							autocomplete="new-password"
							required
						>
						<button type="button" class="bam-toggle-pass" aria-label="<?php esc_attr_e( 'Mostrar contraseña', 'beforeaftermycare' ); ?>" data-target="bam_password">
							<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
						</button>
					</div>
					<?php if ( ! empty( $errors['password'] ) ) : ?>
						<span class="bam-field-message"><?php echo esc_html( $errors['password'] ); ?></span>
					<?php endif; ?>
				</div>

				<div class="bam-field-half <?php echo ! empty( $errors['confirm'] ) ? 'bam-field-error' : ''; ?>">
					<label class="bam-label" for="bam_confirm">
						<?php esc_html_e( 'Confirmar Contraseña', 'beforeaftermycare' ); ?>
						<span class="bam-required" aria-hidden="true">*</span>
					</label>
					<div class="bam-input-password-wrapper">
						<input
							class="bam-input"
							type="password"
							id="bam_confirm"
							name="bam_confirm"
							placeholder="<?php esc_attr_e( 'Repite la contraseña', 'beforeaftermycare' ); ?>"
							autocomplete="new-password"
							required
						>
						<button type="button" class="bam-toggle-pass" aria-label="<?php esc_attr_e( 'Mostrar contraseña', 'beforeaftermycare' ); ?>" data-target="bam_confirm">
							<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
						</button>
					</div>
					<?php if ( ! empty( $errors['confirm'] ) ) : ?>
						<span class="bam-field-message"><?php echo esc_html( $errors['confirm'] ); ?></span>
					<?php endif; ?>
				</div>
			</div>

			<!-- Teléfono (opcional) -->
			<div class="bam-field">
				<label class="bam-label" for="bam_telefono">
					<?php esc_html_e( 'Teléfono', 'beforeaftermycare' ); ?>
					<span class="bam-optional">(<?php esc_html_e( 'opcional', 'beforeaftermycare' ); ?>)</span>
				</label>
				<input
					class="bam-input"
					type="tel"
					id="bam_telefono"
					name="bam_telefono"
					value="<?php echo $old( 'telefono' ); ?>"
					placeholder="<?php esc_attr_e( 'Ej: +52 55 1234 5678', 'beforeaftermycare' ); ?>"
					autocomplete="tel"
				>
			</div>

			<button type="submit" name="bam_register_submit" class="bam-btn bam-btn-primary bam-btn-block">
				<?php esc_html_e( 'Crear mi cuenta', 'beforeaftermycare' ); ?>
			</button>

			<p class="bam-terms">
				<?php
				printf(
					/* translators: %1$s opening anchor, %2$s closing anchor */
					esc_html__( 'Al registrarte aceptas nuestros %1$sTérminos y Condiciones%2$s.', 'beforeaftermycare' ),
					'<a href="#" target="_blank" rel="noopener noreferrer">',
					'</a>'
				);
				?>
			</p>
		</form>
	</div>
</div>

<script src="<?php echo esc_url( BAM_PLUGIN_URL . 'assets/js/frontend.js' ); ?>?v=<?php echo esc_attr( BAM_VERSION ); ?>"></script>
</body>
</html>
