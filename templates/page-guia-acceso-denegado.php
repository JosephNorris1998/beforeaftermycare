<?php
/**
 * Template: Acceso Denegado a la Guía de Colonoscopía.
 *
 * Shown when a non-patient or inactive patient tries to access the guide page.
 * This is a standalone full-HTML template (no Elementor/theme wrapper).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$login_url = home_url( '/' . BAM_Frontend_Dashboard::PAGE_LOGIN_SLUG . '/' );
$site_name = get_bloginfo( 'name' );
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="robots" content="noindex, nofollow">
	<title><?php echo esc_html__( 'Acceso Requerido', 'beforeaftermycare' ) . ' – ' . esc_html( $site_name ); ?></title>
	<style>
		*, *::before, *::after { box-sizing: border-box; }
		body {
			margin: 0;
			font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
			background: #f1f5f9;
			color: #0f172a;
			min-height: 100vh;
			display: flex;
			align-items: center;
			justify-content: center;
			padding: 24px;
		}
		.bam-access-card {
			background: #fff;
			border-radius: 16px;
			box-shadow: 0 4px 24px rgba(0,0,0,0.09);
			padding: 48px 40px;
			max-width: 520px;
			width: 100%;
			text-align: center;
		}
		.bam-access-icon {
			width: 72px;
			height: 72px;
			background: #fef2f2;
			border-radius: 50%;
			display: flex;
			align-items: center;
			justify-content: center;
			margin: 0 auto 24px;
			color: #dc2626;
		}
		.bam-access-title {
			font-size: 1.5rem;
			font-weight: 700;
			margin: 0 0 12px;
		}
		.bam-access-desc {
			font-size: 0.95rem;
			color: #64748b;
			line-height: 1.65;
			margin: 0 0 32px;
		}
		.bam-access-btn {
			display: inline-flex;
			align-items: center;
			gap: 8px;
			background: #0077b6;
			color: #fff;
			padding: 12px 28px;
			border-radius: 8px;
			text-decoration: none;
			font-size: 0.95rem;
			font-weight: 600;
			transition: background 0.15s;
			margin: 0 6px 10px;
		}
		.bam-access-btn:hover { background: #005f92; color: #fff; }
		.bam-access-btn-outline {
			background: transparent;
			color: #0077b6;
			border: 2px solid #0077b6;
		}
		.bam-access-btn-outline:hover { background: #eff6ff; color: #0077b6; }
		.bam-access-divider {
			height: 1px;
			background: #e2e8f0;
			margin: 28px 0;
		}
		.bam-access-note {
			font-size: 0.82rem;
			color: #94a3b8;
		}
	</style>
</head>
<body>
	<div class="bam-access-card">

		<div class="bam-access-icon">
			<svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
				<rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
				<path d="M7 11V7a5 5 0 0 1 10 0v4"/>
			</svg>
		</div>

		<h1 class="bam-access-title"><?php esc_html_e( 'Acceso Restringido', 'beforeaftermycare' ); ?></h1>

		<p class="bam-access-desc">
			<?php esc_html_e( 'Esta guía médica está disponible únicamente para pacientes registrados y activos.', 'beforeaftermycare' ); ?>
			<?php if ( is_user_logged_in() ) : ?>
				<br><br>
				<?php esc_html_e( 'Tu cuenta no tiene acceso a esta guía o ha sido desactivada. Contacta a tu médico o al equipo de soporte.', 'beforeaftermycare' ); ?>
			<?php else : ?>
				<br><br>
				<?php esc_html_e( 'Por favor inicia sesión con tu cuenta de paciente para continuar.', 'beforeaftermycare' ); ?>
			<?php endif; ?>
		</p>

		<?php if ( is_user_logged_in() ) : ?>
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="bam-access-btn bam-access-btn-outline">
				<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><polyline points="15 18 9 12 15 6"/></svg>
				<?php esc_html_e( 'Volver al inicio', 'beforeaftermycare' ); ?>
			</a>
		<?php else : ?>
			<a href="<?php echo esc_url( add_query_arg( 'redirect_to', urlencode( get_permalink() ), wp_login_url() ) ); ?>" class="bam-access-btn">
				<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
				<?php esc_html_e( 'Iniciar Sesión', 'beforeaftermycare' ); ?>
			</a>
			<a href="<?php echo esc_url( home_url( '/' . BAM_Registration::PAGE_SLUG . '/' ) ); ?>" class="bam-access-btn bam-access-btn-outline">
				<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>
				<?php esc_html_e( 'Registrarse', 'beforeaftermycare' ); ?>
			</a>
		<?php endif; ?>

		<div class="bam-access-divider"></div>
		<p class="bam-access-note"><?php echo esc_html( $site_name ); ?> &mdash; <?php esc_html_e( 'Guías Médicas', 'beforeaftermycare' ); ?></p>
	</div>
</body>
</html>
