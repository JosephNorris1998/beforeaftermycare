<?php
/**
 * Template: Admin – Limpiar Caché.
 *
 * Available variables:
 *   $msg (string) – status message key ('cleared' | '').
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="bam-admin-wrap">

	<!-- Topbar -->
	<header class="bam-topbar">
		<div class="bam-topbar-brand">
			<svg width="28" height="28" viewBox="0 0 48 48" fill="none" aria-hidden="true"><circle cx="24" cy="24" r="24" fill="#0077b6"/><path d="M24 12c-6.627 0-12 5.373-12 12s5.373 12 12 12 12-5.373 12-12S30.627 12 24 12zm1 17h-2v-6h2v6zm0-8h-2v-2h2v2z" fill="#fff"/></svg>
			<span><?php esc_html_e( 'Guías Médicas', 'beforeaftermycare' ); ?></span>
		</div>
		<nav class="bam-topbar-nav">
			<a class="bam-nav-link" href="<?php echo esc_url( admin_url( 'admin.php?page=bam-dashboard' ) ); ?>">
				<?php esc_html_e( 'Dashboard', 'beforeaftermycare' ); ?>
			</a>
			<a class="bam-nav-link" href="<?php echo esc_url( admin_url( 'admin.php?page=bam-patients' ) ); ?>">
				<?php esc_html_e( 'Pacientes', 'beforeaftermycare' ); ?>
			</a>
			<a class="bam-nav-link" href="<?php echo esc_url( admin_url( 'admin.php?page=bam-urls' ) ); ?>">
				<?php esc_html_e( 'URLs del Plugin', 'beforeaftermycare' ); ?>
			</a>
			<a class="bam-nav-link bam-nav-active" href="<?php echo esc_url( admin_url( 'admin.php?page=bam-cache' ) ); ?>">
				<?php esc_html_e( 'Limpiar Caché', 'beforeaftermycare' ); ?>
			</a>
		</nav>
	</header>

	<main class="bam-main">
		<div class="bam-page-header">
			<h1 class="bam-page-title"><?php esc_html_e( 'Limpiar Caché', 'beforeaftermycare' ); ?></h1>
			<p class="bam-page-desc"><?php esc_html_e( 'Elimina residuos de caché para que los cambios se reflejen inmediatamente.', 'beforeaftermycare' ); ?></p>
		</div>

		<?php if ( 'cleared' === $msg ) : ?>
			<div class="bam-notice bam-notice-success">
				<?php esc_html_e( '✅ Caché limpiada correctamente. Los cambios ya son visibles.', 'beforeaftermycare' ); ?>
			</div>
		<?php endif; ?>

		<!-- Info cards -->
		<div class="bam-stat-grid" style="margin-bottom:28px;">
			<div class="bam-stat-card">
				<div class="bam-stat-icon bam-icon-blue">
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 .49-3.51"/></svg>
				</div>
				<div class="bam-stat-info">
					<span class="bam-stat-label" style="font-size:.85rem;font-weight:600;color:var(--bam-text);"><?php esc_html_e( 'Transients del Plugin', 'beforeaftermycare' ); ?></span>
					<span class="bam-stat-label"><?php esc_html_e( 'Datos temporales almacenados en la BD de WordPress', 'beforeaftermycare' ); ?></span>
				</div>
			</div>
			<div class="bam-stat-card">
				<div class="bam-stat-icon bam-icon-green">
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
				</div>
				<div class="bam-stat-info">
					<span class="bam-stat-label" style="font-size:.85rem;font-weight:600;color:var(--bam-text);"><?php esc_html_e( 'Object Cache (WordPress)', 'beforeaftermycare' ); ?></span>
					<span class="bam-stat-label"><?php esc_html_e( 'Caché de objetos en memoria de WordPress', 'beforeaftermycare' ); ?></span>
				</div>
			</div>
			<div class="bam-stat-card">
				<div class="bam-stat-icon bam-icon-purple">
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
				</div>
				<div class="bam-stat-info">
					<span class="bam-stat-label" style="font-size:.85rem;font-weight:600;color:var(--bam-text);"><?php esc_html_e( 'LiteSpeed Cache', 'beforeaftermycare' ); ?></span>
					<span class="bam-stat-label"><?php esc_html_e( 'Purga total de LiteSpeed si está instalado', 'beforeaftermycare' ); ?></span>
				</div>
			</div>
			<div class="bam-stat-card">
				<div class="bam-stat-icon bam-icon-orange">
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
				</div>
				<div class="bam-stat-info">
					<span class="bam-stat-label" style="font-size:.85rem;font-weight:600;color:var(--bam-text);"><?php esc_html_e( 'Reglas de Reescritura', 'beforeaftermycare' ); ?></span>
					<span class="bam-stat-label"><?php esc_html_e( 'Recarga las reglas de permalinks de WordPress', 'beforeaftermycare' ); ?></span>
				</div>
			</div>
		</div>

		<!-- Clear cache form -->
		<div class="bam-card">
			<div class="bam-card-header">
				<h2 class="bam-card-title">
					<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 .49-3.51"/></svg>
					<?php esc_html_e( 'Limpiar Caché del Plugin', 'beforeaftermycare' ); ?>
				</h2>
			</div>
			<div style="padding:24px;">
				<p style="color:var(--bam-muted);margin:0 0 20px;font-size:.9rem;">
					<?php esc_html_e( 'Esta acción eliminará todos los datos de caché relacionados con el plugin: transients de la base de datos, caché de objetos de WordPress, caché de LiteSpeed (si está activo) y recargará las reglas de permalinks. El navegador recibirá las páginas actualizadas.', 'beforeaftermycare' ); ?>
				</p>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<input type="hidden" name="action" value="bam_clear_cache">
					<?php wp_nonce_field( 'bam_clear_cache', 'bam_cache_nonce' ); ?>
					<button type="submit" class="bam-btn bam-btn-primary bam-btn-lg">
						<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true" style="margin-right:6px;vertical-align:-2px;"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 .49-3.51"/></svg>
						<?php esc_html_e( 'Limpiar Toda la Caché Ahora', 'beforeaftermycare' ); ?>
					</button>
				</form>
			</div>
		</div>

		<?php
		// Show LiteSpeed status using the shared detection helper.
		$ls_active = BAM_Admin::litespeed_available();
		?>
		<div class="bam-card">
			<div class="bam-card-header">
				<h2 class="bam-card-title">
					<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
					<?php esc_html_e( 'Estado de LiteSpeed Cache', 'beforeaftermycare' ); ?>
				</h2>
			</div>
			<div style="padding:20px 24px;">
				<?php if ( $ls_active ) : ?>
					<div class="bam-cache-status bam-cache-status-ok">
						<span class="bam-cache-dot bam-cache-dot-ok"></span>
						<strong><?php esc_html_e( 'LiteSpeed Cache detectado', 'beforeaftermycare' ); ?></strong>
						<span style="color:var(--bam-muted);margin-left:8px;font-size:.85rem;"><?php esc_html_e( '– se incluirá en la limpieza automáticamente.', 'beforeaftermycare' ); ?></span>
					</div>
				<?php else : ?>
					<div class="bam-cache-status bam-cache-status-off">
						<span class="bam-cache-dot bam-cache-dot-off"></span>
						<strong><?php esc_html_e( 'LiteSpeed Cache no detectado', 'beforeaftermycare' ); ?></strong>
						<span style="color:var(--bam-muted);margin-left:8px;font-size:.85rem;"><?php esc_html_e( '– plugin de LiteSpeed no está instalado o activo.', 'beforeaftermycare' ); ?></span>
					</div>
				<?php endif; ?>
			</div>
		</div>

		<!-- Tips -->
		<div class="bam-card">
			<div class="bam-card-header">
				<h2 class="bam-card-title">
					<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
					<?php esc_html_e( 'Consejos de Caché del Navegador', 'beforeaftermycare' ); ?>
				</h2>
			</div>
			<div style="padding:20px 24px;">
				<ul class="bam-cache-tips">
					<li><?php esc_html_e( 'Si después de limpiar la caché del servidor sigues viendo contenido antiguo, fuerza la recarga del navegador con Ctrl+Shift+R (Windows/Linux) o Cmd+Shift+R (Mac).', 'beforeaftermycare' ); ?></li>
					<li><?php esc_html_e( 'Para limpiar la caché del navegador Chrome: Configuración → Privacidad → Borrar datos de navegación → Imágenes y archivos en caché.', 'beforeaftermycare' ); ?></li>
					<li><?php esc_html_e( 'El plugin usa el header "Cache-Control: no-store" en el área de administración para evitar que el navegador almacene páginas del dashboard.', 'beforeaftermycare' ); ?></li>
					<li><?php esc_html_e( 'LiteSpeed Cache excluye automáticamente a los usuarios logueados y el wp-admin del caché.', 'beforeaftermycare' ); ?></li>
				</ul>
			</div>
		</div>

	</main>
</div>
