<?php
/**
 * Template: Admin – URLs del Plugin.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Build URLs
$urls = array(
	array(
		'label'       => __( 'Registro de Paciente', 'beforeaftermycare' ),
		'description' => __( 'Formulario público donde nuevos pacientes pueden registrarse.', 'beforeaftermycare' ),
		'url'         => home_url( '/' . BAM_Registration::PAGE_SLUG . '/' ),
		'slug'        => BAM_Registration::PAGE_SLUG,
		'icon_color'  => 'bam-icon-green',
		'icon'        => '<path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/>',
	),
	array(
		'label'       => __( 'Login del Plugin', 'beforeaftermycare' ),
		'description' => __( 'Página de inicio de sesión personalizada para administradores del plugin.', 'beforeaftermycare' ),
		'url'         => home_url( '/' . BAM_Frontend_Dashboard::PAGE_LOGIN_SLUG . '/' ),
		'slug'        => BAM_Frontend_Dashboard::PAGE_LOGIN_SLUG,
		'icon_color'  => 'bam-icon-blue',
		'icon'        => '<rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>',
	),
	array(
		'label'       => __( 'Dashboard del Plugin (Frontend)', 'beforeaftermycare' ),
		'description' => __( 'Panel de gestión de pacientes en el frontend (solo administradores).', 'beforeaftermycare' ),
		'url'         => home_url( '/' . BAM_Frontend_Dashboard::PAGE_SLUG . '/' ),
		'slug'        => BAM_Frontend_Dashboard::PAGE_SLUG,
		'icon_color'  => 'bam-icon-purple',
		'icon'        => '<rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>',
	),
	array(
		'label'       => __( 'Guía de Colonoscopía (Guía Médica)', 'beforeaftermycare' ),
		'description' => __( 'Página de la guía médica. Solo accesible para pacientes registrados y activos.', 'beforeaftermycare' ),
		'url'         => BAM_REDIRECT_URL,
		'slug'        => BAM_Registration::GUIDE_SLUG,
		'icon_color'  => 'bam-icon-orange',
		'icon'        => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/>',
	),
	array(
		'label'       => __( 'Dashboard de WordPress (Admin)', 'beforeaftermycare' ),
		'description' => __( 'Panel interno del plugin dentro de WordPress.', 'beforeaftermycare' ),
		'url'         => admin_url( 'admin.php?page=' . BAM_Admin::MENU_SLUG ),
		'slug'        => 'wp-admin',
		'icon_color'  => 'bam-icon-blue',
		'icon'        => '<path d="M22 12h-4l-3 9L9 3l-3 9H2"/>',
	),
);
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
			<a class="bam-nav-link bam-nav-active" href="<?php echo esc_url( admin_url( 'admin.php?page=bam-urls' ) ); ?>">
				<?php esc_html_e( 'URLs del Plugin', 'beforeaftermycare' ); ?>
			</a>
			<a class="bam-nav-link" href="<?php echo esc_url( admin_url( 'admin.php?page=bam-cache' ) ); ?>">
				<?php esc_html_e( 'Limpiar Caché', 'beforeaftermycare' ); ?>
			</a>
		</nav>
	</header>

	<main class="bam-main">
		<div class="bam-page-header">
			<h1 class="bam-page-title"><?php esc_html_e( 'URLs del Plugin', 'beforeaftermycare' ); ?></h1>
			<p class="bam-page-desc"><?php esc_html_e( 'Control de todas las URLs generadas y usadas por el plugin.', 'beforeaftermycare' ); ?></p>
		</div>

		<!-- URL cards grid -->
		<div class="bam-url-grid">
			<?php foreach ( $urls as $item ) : ?>
				<?php
				$page_obj    = get_page_by_path( $item['slug'] );
				$page_exists = ! empty( $page_obj ) || 'wp-admin' === $item['slug'];
				$page_status = ( $page_obj && 'publish' === $page_obj->post_status ) ? 'publish' : ( $page_obj ? $page_obj->post_status : 'not_found' );
				if ( 'wp-admin' === $item['slug'] ) {
					$page_status = 'admin';
				}
				?>
				<div class="bam-url-card">
					<div class="bam-url-card-header">
						<div class="bam-stat-icon <?php echo esc_attr( $item['icon_color'] ); ?>" style="width:40px;height:40px;border-radius:10px;">
							<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
								<?php
								echo wp_kses(
									$item['icon'],
									array(
										'path'     => array( 'd' => array() ),
										'polyline' => array( 'points' => array() ),
										'polygon'  => array( 'points' => array() ),
										'circle'   => array( 'cx' => array(), 'cy' => array(), 'r' => array() ),
										'rect'     => array( 'x' => array(), 'y' => array(), 'width' => array(), 'height' => array(), 'rx' => array(), 'ry' => array() ),
										'line'     => array( 'x1' => array(), 'y1' => array(), 'x2' => array(), 'y2' => array() ),
									)
								);
								?>
							</svg>
						</div>
						<div class="bam-url-card-title-wrap">
							<h3 class="bam-url-card-title"><?php echo esc_html( $item['label'] ); ?></h3>
							<?php if ( 'admin' === $page_status ) : ?>
								<span class="bam-badge bam-badge-info"><?php esc_html_e( 'WordPress Admin', 'beforeaftermycare' ); ?></span>
							<?php elseif ( 'publish' === $page_status ) : ?>
								<span class="bam-badge bam-badge-success"><?php esc_html_e( 'Publicada', 'beforeaftermycare' ); ?></span>
							<?php elseif ( $page_obj ) : ?>
								<span class="bam-badge bam-badge-warning"><?php echo esc_html( ucfirst( $page_status ) ); ?></span>
							<?php else : ?>
								<span class="bam-badge bam-badge-inactive"><?php esc_html_e( 'No encontrada', 'beforeaftermycare' ); ?></span>
							<?php endif; ?>
						</div>
					</div>
					<p class="bam-url-card-desc"><?php echo esc_html( $item['description'] ); ?></p>
					<div class="bam-url-card-link-row">
						<code class="bam-url-code"><?php echo esc_html( $item['url'] ); ?></code>
						<a href="<?php echo esc_url( $item['url'] ); ?>" target="_blank" rel="noopener noreferrer" class="bam-btn bam-btn-xs bam-btn-outline">
							<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true" style="margin-right:3px;vertical-align:-1px;"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
							<?php esc_html_e( 'Abrir', 'beforeaftermycare' ); ?>
						</a>
						<?php if ( $page_obj ) : ?>
							<a href="<?php echo esc_url( get_edit_post_link( $page_obj->ID ) ); ?>" class="bam-btn bam-btn-xs bam-btn-outline">
								<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true" style="margin-right:3px;vertical-align:-1px;"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
								<?php esc_html_e( 'Editar', 'beforeaftermycare' ); ?>
							</a>
						<?php endif; ?>
					</div>
				</div>
			<?php endforeach; ?>
		</div>

		<!-- Shortcodes info -->
		<div class="bam-card" style="margin-top:8px;">
			<div class="bam-card-header">
				<h2 class="bam-card-title">
					<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>
					<?php esc_html_e( 'Shortcodes del Plugin', 'beforeaftermycare' ); ?>
				</h2>
			</div>
			<div style="padding:20px 24px;">
				<table class="bam-table">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Shortcode', 'beforeaftermycare' ); ?></th>
							<th><?php esc_html_e( 'Descripción', 'beforeaftermycare' ); ?></th>
							<th><?php esc_html_e( 'Usado en', 'beforeaftermycare' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td><code>[bam_registro]</code></td>
							<td><?php esc_html_e( 'Muestra el formulario de registro de pacientes.', 'beforeaftermycare' ); ?></td>
							<td>
								<a href="<?php echo esc_url( home_url( '/' . BAM_Registration::PAGE_SLUG . '/' ) ); ?>" target="_blank" class="bam-link">
									/<?php echo esc_html( BAM_Registration::PAGE_SLUG ); ?>/
								</a>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>

	</main>
</div>
