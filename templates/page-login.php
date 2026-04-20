<?php
/**
 * Standalone frontend login page – no Elementor header or footer.
 *
 * Served via BAM_Frontend_Dashboard::intercept_template() when the
 * /loginplugin/ page is requested.  On successful login the user is
 * redirected to /dashboardplugin/.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.Security.NonceVerification.Recommended
$error = isset( $_GET['bam_login_error'] ) ? sanitize_key( $_GET['bam_login_error'] ) : '';
// phpcs:enable
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?php esc_html_e( 'Acceso – Guías Médicas', 'beforeaftermycare' ); ?> – <?php bloginfo( 'name' ); ?></title>
	<link rel="stylesheet" href="<?php echo esc_url( BAM_PLUGIN_URL . 'assets/css/admin.css' ); ?>?v=<?php echo esc_attr( BAM_VERSION ); ?>">
	<style>
		html, body { margin: 0; padding: 0; }
	</style>
</head>
<body class="bam-login-page">

<div class="bam-login-wrap">

	<div class="bam-login-card">

		<div class="bam-login-header">
			<div class="bam-login-logo">
				<svg width="48" height="48" viewBox="0 0 48 48" fill="none" aria-hidden="true">
					<circle cx="24" cy="24" r="24" fill="#0077b6"/>
					<path d="M24 12c-6.627 0-12 5.373-12 12s5.373 12 12 12 12-5.373 12-12S30.627 12 24 12zm1 17h-2v-6h2v6zm0-8h-2v-2h2v2z" fill="#fff"/>
				</svg>
			</div>
			<h1 class="bam-login-title"><?php esc_html_e( 'Guías Médicas', 'beforeaftermycare' ); ?></h1>
			<p class="bam-login-subtitle"><?php esc_html_e( 'Accede al panel de administración', 'beforeaftermycare' ); ?></p>
		</div>

		<?php if ( '1' === $error ) : ?>
			<div class="bam-notice bam-notice-error" role="alert">
				<?php esc_html_e( 'Usuario o contraseña incorrectos. Inténtalo de nuevo.', 'beforeaftermycare' ); ?>
			</div>
		<?php elseif ( 'noaccess' === $error ) : ?>
			<div class="bam-notice bam-notice-error" role="alert">
				<?php esc_html_e( 'No tienes permisos para acceder a este panel.', 'beforeaftermycare' ); ?>
			</div>
		<?php endif; ?>

		<form class="bam-login-form" method="post" action="<?php echo esc_url( home_url( '/' . BAM_Frontend_Dashboard::PAGE_LOGIN_SLUG . '/' ) ); ?>">
			<?php wp_nonce_field( 'bam_login', 'bam_login_nonce' ); ?>

			<div class="bam-field">
				<label class="bam-label" for="bam_username">
					<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
					<?php esc_html_e( 'Usuario o correo electrónico', 'beforeaftermycare' ); ?>
				</label>
				<input
					class="bam-input"
					type="text"
					id="bam_username"
					name="bam_username"
					autocomplete="username"
					required
					autofocus
					value="<?php echo isset( $_POST['bam_username'] ) ? esc_attr( sanitize_user( wp_unslash( $_POST['bam_username'] ) ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing ?>"
				>
			</div>

			<div class="bam-field">
				<label class="bam-label" for="bam_password">
					<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
					<?php esc_html_e( 'Contraseña', 'beforeaftermycare' ); ?>
				</label>
				<div class="bam-input-password-wrapper">
					<input
						class="bam-input"
						type="password"
						id="bam_password"
						name="bam_password"
						autocomplete="current-password"
						required
					>
					<button type="button" class="bam-toggle-pass" aria-label="<?php esc_attr_e( 'Mostrar / ocultar contraseña', 'beforeaftermycare' ); ?>">
						<svg class="bam-eye-show" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
						<svg class="bam-eye-hide" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" style="display:none"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
					</button>
				</div>
			</div>

			<div class="bam-login-remember">
				<label class="bam-checkbox-label">
					<input type="checkbox" name="bam_remember" value="1">
					<?php esc_html_e( 'Recordarme', 'beforeaftermycare' ); ?>
				</label>
			</div>

			<button type="submit" name="bam_login_submit" class="bam-btn bam-btn-primary bam-btn-block bam-login-btn">
				<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
				<?php esc_html_e( 'Iniciar sesión', 'beforeaftermycare' ); ?>
			</button>
		</form>

		<p class="bam-login-footer">
			<?php echo esc_html( get_bloginfo( 'name' ) ); ?> &middot; <?php esc_html_e( 'Panel de Guías Médicas', 'beforeaftermycare' ); ?>
		</p>

	</div>
</div>

<script>
(function () {
	'use strict';
	var btn = document.querySelector('.bam-toggle-pass');
	if (!btn) return;
	btn.addEventListener('click', function () {
		var input  = document.getElementById('bam_password');
		var show   = btn.querySelector('.bam-eye-show');
		var hide   = btn.querySelector('.bam-eye-hide');
		if (input.type === 'password') {
			input.type = 'text';
			show.style.display = 'none';
			hide.style.display = '';
		} else {
			input.type = 'password';
			show.style.display = '';
			hide.style.display = 'none';
		}
	});
}());
</script>
</body>
</html>
