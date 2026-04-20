<?php
/**
 * Standalone frontend dashboard – no Elementor header or footer.
 *
 * This file is served via BAM_Frontend_Dashboard::intercept_template() and is a
 * complete HTML document so the theme/page-builder wrapper is bypassed
 * entirely.  Only users with manage_options capability can reach this template
 * (the class redirects everyone else to the login page).
 *
 * Available context (resolved below):
 *   $current_user  – WP_User of the logged-in admin.
 *   $msg           – flash message key (deleted|toggled|updated|'').
 *   $edit_id       – patient ID being edited, or 0.
 *   $edit_patient  – patient object when editing, or null.
 *   $search        – current search string.
 *   $page          – current list page number.
 *   $patients      – array of patient rows.
 *   $total         – total matching patients.
 *   $num_pages     – total list pages.
 *   $total_all     – all-time patient count.
 *   $active        – active patient count.
 *   $recent        – patients registered in the last 30 days.
 *   $base_url      – canonical URL of this dashboard page.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ── Data ──────────────────────────────────────────────────────────────────────

$current_user = wp_get_current_user();

// These GET parameters are display-only (read for output, not for auth/actions).
// phpcs:disable WordPress.Security.NonceVerification.Recommended
$msg      = isset( $_GET['bam_msg'] ) ? sanitize_key( $_GET['bam_msg'] ) : '';
$edit_id  = isset( $_GET['bam_edit'] ) ? absint( $_GET['bam_edit'] ) : 0;
$search   = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';
$page     = isset( $_GET['paged'] ) ? max( 1, absint( $_GET['paged'] ) ) : 1;
// phpcs:enable
$per_page = 20;

$page_obj = get_page_by_path( BAM_Frontend_Dashboard::PAGE_SLUG );
$base_url = $page_obj ? get_permalink( $page_obj ) : home_url( '/' . BAM_Frontend_Dashboard::PAGE_SLUG . '/' );

$result    = BAM_Database::get_patients( $per_page, $page, $search );
$patients  = $result['items'];
$total     = $result['total'];
$num_pages = (int) ceil( $total / $per_page );

$total_all = BAM_Database::count_total();
$active    = BAM_Database::count_active();
$recent    = BAM_Database::count_recent();

$edit_patient = null;
if ( $edit_id ) {
	$edit_patient = BAM_Database::get_patient( $edit_id );
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?php esc_html_e( 'Dashboard – Guías Médicas', 'beforeaftermycare' ); ?> – <?php bloginfo( 'name' ); ?></title>
	<link rel="stylesheet" href="<?php echo esc_url( BAM_PLUGIN_URL . 'assets/css/admin.css' ); ?>?v=<?php echo esc_attr( BAM_VERSION ); ?>">
	<style>
		html, body {
			margin: 0;
			padding: 0;
		}
	</style>
</head>
<body class="bam-frontend-dashboard">

<div class="bam-admin-wrap">

	<!-- ── Top bar ─────────────────────────────────────────────────────────── -->
	<header class="bam-topbar">
		<div class="bam-topbar-brand">
			<svg width="28" height="28" viewBox="0 0 48 48" fill="none" aria-hidden="true">
				<circle cx="24" cy="24" r="24" fill="#0077b6"/>
				<path d="M24 12c-6.627 0-12 5.373-12 12s5.373 12 12 12 12-5.373 12-12S30.627 12 24 12zm1 17h-2v-6h2v6zm0-8h-2v-2h2v2z" fill="#fff"/>
			</svg>
			<span><?php esc_html_e( 'Guías Médicas', 'beforeaftermycare' ); ?></span>
		</div>
		<nav class="bam-topbar-nav">
			<a class="bam-nav-link <?php echo ! $edit_id ? 'bam-nav-active' : ''; ?>" href="<?php echo esc_url( $base_url ); ?>">
				<?php esc_html_e( 'Dashboard', 'beforeaftermycare' ); ?>
			</a>
			<span style="margin-left:16px; font-size:0.85rem; color:rgba(255,255,255,0.75);">
				<?php
				printf(
					/* translators: %s: user display name */
					esc_html__( 'Hola, %s', 'beforeaftermycare' ),
					esc_html( $current_user->display_name )
				);
				?>
			</span>
			<a class="bam-nav-link" href="<?php echo esc_url( wp_logout_url( home_url() ) ); ?>">
				<?php esc_html_e( 'Salir', 'beforeaftermycare' ); ?>
			</a>
		</nav>
	</header>

	<main class="bam-main">

		<!-- ── Flash messages ──────────────────────────────────────────────── -->
		<?php if ( 'deleted' === $msg ) : ?>
			<div class="bam-notice bam-notice-success" role="status">
				<?php esc_html_e( 'Paciente eliminado correctamente.', 'beforeaftermycare' ); ?>
			</div>
		<?php elseif ( 'toggled' === $msg ) : ?>
			<div class="bam-notice bam-notice-success" role="status">
				<?php esc_html_e( 'Estado del paciente actualizado.', 'beforeaftermycare' ); ?>
			</div>
		<?php elseif ( 'updated' === $msg ) : ?>
			<div class="bam-notice bam-notice-success" role="status">
				<?php esc_html_e( 'Datos del paciente guardados correctamente.', 'beforeaftermycare' ); ?>
			</div>
		<?php endif; ?>

		<!-- ── Page header ─────────────────────────────────────────────────── -->
		<div class="bam-page-header">
			<h1 class="bam-page-title">
				<?php if ( $edit_patient ) : ?>
					<?php esc_html_e( 'Editar Paciente', 'beforeaftermycare' ); ?>
				<?php else : ?>
					<?php esc_html_e( 'Dashboard', 'beforeaftermycare' ); ?>
				<?php endif; ?>
			</h1>
		</div>

		<!-- ── Stat cards ──────────────────────────────────────────────────── -->
		<div class="bam-stat-grid">
			<div class="bam-stat-card">
				<div class="bam-stat-icon bam-icon-blue">
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
				</div>
				<div class="bam-stat-info">
					<span class="bam-stat-value"><?php echo esc_html( number_format_i18n( $total_all ) ); ?></span>
					<span class="bam-stat-label"><?php esc_html_e( 'Pacientes Totales', 'beforeaftermycare' ); ?></span>
				</div>
			</div>
			<div class="bam-stat-card">
				<div class="bam-stat-icon bam-icon-green">
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
				</div>
				<div class="bam-stat-info">
					<span class="bam-stat-value"><?php echo esc_html( number_format_i18n( $active ) ); ?></span>
					<span class="bam-stat-label"><?php esc_html_e( 'Pacientes Activos', 'beforeaftermycare' ); ?></span>
				</div>
			</div>
			<div class="bam-stat-card">
				<div class="bam-stat-icon bam-icon-purple">
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
				</div>
				<div class="bam-stat-info">
					<span class="bam-stat-value"><?php echo esc_html( number_format_i18n( $recent ) ); ?></span>
					<span class="bam-stat-label"><?php esc_html_e( 'Nuevos (30 días)', 'beforeaftermycare' ); ?></span>
				</div>
			</div>
			<div class="bam-stat-card">
				<div class="bam-stat-icon bam-icon-orange">
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
				</div>
				<div class="bam-stat-info">
					<span class="bam-stat-value"><?php echo $total_all > 0 ? esc_html( round( ( $active / $total_all ) * 100 ) ) . '%' : '0%'; ?></span>
					<span class="bam-stat-label"><?php esc_html_e( 'Tasa de Actividad', 'beforeaftermycare' ); ?></span>
				</div>
			</div>
		</div>

		<?php if ( $edit_patient ) : ?>
		<!-- ═══════════════════════════════════════════════════════════════════
		     EDIT VIEW
		     ═══════════════════════════════════════════════════════════════════ -->
		<div class="bam-detail-grid">

			<!-- Edit form -->
			<div class="bam-card bam-detail-form-card">
				<div class="bam-card-header">
					<h2 class="bam-card-title"><?php esc_html_e( 'Editar Datos del Paciente', 'beforeaftermycare' ); ?></h2>
					<a class="bam-back-link" href="<?php echo esc_url( $base_url ); ?>">
						← <?php esc_html_e( 'Volver al listado', 'beforeaftermycare' ); ?>
					</a>
				</div>
				<form method="post" action="<?php echo esc_url( $base_url ); ?>">
					<?php wp_nonce_field( 'bam_front_update_' . $edit_id, 'bam_front_update_nonce' ); ?>
					<input type="hidden" name="bam_id" value="<?php echo esc_attr( $edit_id ); ?>">

					<div class="bam-field">
						<label class="bam-label" for="bam_nombre"><?php esc_html_e( 'Nombre Completo', 'beforeaftermycare' ); ?></label>
						<input class="bam-input" type="text" id="bam_nombre" name="bam_nombre"
							value="<?php echo esc_attr( $edit_patient->nombre ); ?>" required>
					</div>

					<div class="bam-field">
						<label class="bam-label" for="bam_correo"><?php esc_html_e( 'Correo Electrónico', 'beforeaftermycare' ); ?></label>
						<input class="bam-input" type="email" id="bam_correo" name="bam_correo"
							value="<?php echo esc_attr( $edit_patient->correo ); ?>" required>
					</div>

					<div class="bam-field">
						<label class="bam-label" for="bam_telefono"><?php esc_html_e( 'Teléfono', 'beforeaftermycare' ); ?></label>
						<input class="bam-input" type="tel" id="bam_telefono" name="bam_telefono"
							value="<?php echo esc_attr( $edit_patient->telefono ?? '' ); ?>">
					</div>

					<div class="bam-field">
						<label class="bam-label" for="bam_guia_asignada"><?php esc_html_e( 'Guía Asignada', 'beforeaftermycare' ); ?></label>
						<input class="bam-input" type="text" id="bam_guia_asignada" name="bam_guia_asignada"
							value="<?php echo esc_attr( $edit_patient->guia_asignada ?? '' ); ?>"
							placeholder="guia-de-colonoscopia">
					</div>

					<button type="submit" name="bam_front_update_submit" class="bam-btn bam-btn-primary">
						<?php esc_html_e( 'Guardar Cambios', 'beforeaftermycare' ); ?>
					</button>
					<a class="bam-btn bam-btn-outline" href="<?php echo esc_url( $base_url ); ?>" style="margin-left:8px;">
						<?php esc_html_e( 'Cancelar', 'beforeaftermycare' ); ?>
					</a>
				</form>
			</div>

			<!-- Patient info panel -->
			<div class="bam-detail-info-col">
				<div class="bam-card">
					<div class="bam-card-header">
						<h2 class="bam-card-title"><?php esc_html_e( 'Información', 'beforeaftermycare' ); ?></h2>
					</div>
					<dl class="bam-info-list">
						<div class="bam-info-row">
							<dt><?php esc_html_e( 'ID Paciente', 'beforeaftermycare' ); ?></dt>
							<dd>#<?php echo esc_html( $edit_patient->id ); ?></dd>
						</div>
						<div class="bam-info-row">
							<dt><?php esc_html_e( 'Usuario', 'beforeaftermycare' ); ?></dt>
							<dd><?php echo esc_html( $edit_patient->usuario ); ?></dd>
						</div>
						<div class="bam-info-row">
							<dt><?php esc_html_e( 'Registro', 'beforeaftermycare' ); ?></dt>
							<dd><?php echo esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $edit_patient->fecha_registro ) ) ); ?></dd>
						</div>
						<div class="bam-info-row">
							<dt><?php esc_html_e( 'Estado', 'beforeaftermycare' ); ?></dt>
							<dd>
								<span class="bam-badge <?php echo $edit_patient->estado ? 'bam-badge-success' : 'bam-badge-inactive'; ?>">
									<?php echo $edit_patient->estado ? esc_html__( 'Activo', 'beforeaftermycare' ) : esc_html__( 'Inactivo', 'beforeaftermycare' ); ?>
								</span>
							</dd>
						</div>
					</dl>
				</div>

				<!-- Danger zone -->
				<div class="bam-card bam-card-danger">
					<div class="bam-card-header">
						<h2 class="bam-card-title"><?php esc_html_e( 'Acciones', 'beforeaftermycare' ); ?></h2>
					</div>
					<div class="bam-danger-actions">
						<a href="<?php echo esc_url( add_query_arg( array(
							'bam_front_action' => 'toggle',
							'bam_id'           => $edit_id,
							'bam_nonce'        => wp_create_nonce( 'bam_front_toggle' ),
						), $base_url ) ); ?>"
							class="bam-btn bam-btn-warning bam-btn-block bam-confirm-toggle">
							<?php echo $edit_patient->estado ? esc_html__( 'Desactivar Paciente', 'beforeaftermycare' ) : esc_html__( 'Activar Paciente', 'beforeaftermycare' ); ?>
						</a>
						<a href="<?php echo esc_url( add_query_arg( array(
							'bam_front_action' => 'delete',
							'bam_id'           => $edit_id,
							'bam_nonce'        => wp_create_nonce( 'bam_front_delete' ),
						), $base_url ) ); ?>"
							class="bam-btn bam-btn-danger bam-btn-block bam-confirm-delete">
							<?php esc_html_e( 'Eliminar Paciente', 'beforeaftermycare' ); ?>
						</a>
					</div>
				</div>
			</div>
		</div><!-- /.bam-detail-grid -->

		<?php else : ?>
		<!-- ═══════════════════════════════════════════════════════════════════
		     LIST VIEW
		     ═══════════════════════════════════════════════════════════════════ -->

		<!-- Search -->
		<form class="bam-search-form" method="get" action="<?php echo esc_url( $base_url ); ?>">
			<div class="bam-search-row">
				<input
					class="bam-input bam-search-input"
					type="search"
					name="s"
					value="<?php echo esc_attr( $search ); ?>"
					placeholder="<?php esc_attr_e( 'Buscar por nombre, usuario o correo…', 'beforeaftermycare' ); ?>"
				>
				<button type="submit" class="bam-btn bam-btn-primary"><?php esc_html_e( 'Buscar', 'beforeaftermycare' ); ?></button>
				<?php if ( $search ) : ?>
					<a class="bam-btn bam-btn-outline" href="<?php echo esc_url( $base_url ); ?>">
						<?php esc_html_e( 'Limpiar', 'beforeaftermycare' ); ?>
					</a>
				<?php endif; ?>
			</div>
		</form>

		<!-- Patient table -->
		<div class="bam-card">
			<div class="bam-card-header">
				<span class="bam-result-count">
					<?php
					printf(
						/* translators: %s: number */
						esc_html( _n( '%s paciente encontrado', '%s pacientes encontrados', $total, 'beforeaftermycare' ) ),
						esc_html( number_format_i18n( $total ) )
					);
					?>
				</span>
			</div>

			<?php if ( empty( $patients ) ) : ?>
				<div class="bam-empty-state">
					<p><?php esc_html_e( 'No se encontraron pacientes.', 'beforeaftermycare' ); ?></p>
				</div>
			<?php else : ?>
				<div class="bam-table-wrapper">
					<table class="bam-table">
						<thead>
							<tr>
								<th><?php esc_html_e( 'ID', 'beforeaftermycare' ); ?></th>
								<th><?php esc_html_e( 'Nombre', 'beforeaftermycare' ); ?></th>
								<th><?php esc_html_e( 'Usuario', 'beforeaftermycare' ); ?></th>
								<th><?php esc_html_e( 'Correo', 'beforeaftermycare' ); ?></th>
								<th><?php esc_html_e( 'Teléfono', 'beforeaftermycare' ); ?></th>
								<th><?php esc_html_e( 'Guía', 'beforeaftermycare' ); ?></th>
								<th><?php esc_html_e( 'Fecha Registro', 'beforeaftermycare' ); ?></th>
								<th><?php esc_html_e( 'Estado', 'beforeaftermycare' ); ?></th>
								<th><?php esc_html_e( 'Acciones', 'beforeaftermycare' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $patients as $patient ) :
								$toggle_url = add_query_arg( array(
									'bam_front_action' => 'toggle',
									'bam_id'           => $patient->id,
									'bam_nonce'        => wp_create_nonce( 'bam_front_toggle' ),
								), $base_url );

								$delete_url = add_query_arg( array(
									'bam_front_action' => 'delete',
									'bam_id'           => $patient->id,
									'bam_nonce'        => wp_create_nonce( 'bam_front_delete' ),
								), $base_url );

								$edit_url = add_query_arg( 'bam_edit', $patient->id, $base_url );
							?>
							<tr>
								<td><?php echo esc_html( $patient->id ); ?></td>
								<td>
									<a href="<?php echo esc_url( $edit_url ); ?>" class="bam-link">
										<?php echo esc_html( $patient->nombre ); ?>
									</a>
								</td>
								<td><?php echo esc_html( $patient->usuario ); ?></td>
								<td><?php echo esc_html( $patient->correo ); ?></td>
								<td><?php echo $patient->telefono ? esc_html( $patient->telefono ) : '—'; ?></td>
								<td><?php echo $patient->guia_asignada ? esc_html( $patient->guia_asignada ) : '—'; ?></td>
								<td><?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $patient->fecha_registro ) ) ); ?></td>
								<td>
									<span class="bam-badge <?php echo $patient->estado ? 'bam-badge-success' : 'bam-badge-inactive'; ?>">
										<?php echo $patient->estado ? esc_html__( 'Activo', 'beforeaftermycare' ) : esc_html__( 'Inactivo', 'beforeaftermycare' ); ?>
									</span>
								</td>
								<td class="bam-actions-cell">
									<a href="<?php echo esc_url( $edit_url ); ?>"
										class="bam-btn bam-btn-xs bam-btn-outline"
										title="<?php esc_attr_e( 'Editar', 'beforeaftermycare' ); ?>">
										<?php esc_html_e( 'Editar', 'beforeaftermycare' ); ?>
									</a>
									<a href="<?php echo esc_url( $toggle_url ); ?>"
										class="bam-btn bam-btn-xs bam-btn-warning bam-confirm-toggle"
										title="<?php esc_attr_e( 'Activar / Desactivar', 'beforeaftermycare' ); ?>">
										<?php echo $patient->estado ? esc_html__( 'Desactivar', 'beforeaftermycare' ) : esc_html__( 'Activar', 'beforeaftermycare' ); ?>
									</a>
									<a href="<?php echo esc_url( $delete_url ); ?>"
										class="bam-btn bam-btn-xs bam-btn-danger bam-confirm-delete"
										title="<?php esc_attr_e( 'Eliminar', 'beforeaftermycare' ); ?>">
										<?php esc_html_e( 'Eliminar', 'beforeaftermycare' ); ?>
									</a>
								</td>
							</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>

				<!-- Pagination -->
				<?php if ( $num_pages > 1 ) : ?>
					<div class="bam-pagination">
						<?php for ( $p = 1; $p <= $num_pages; $p++ ) : ?>
							<a
								class="bam-page-btn <?php echo $p === $page ? 'bam-page-current' : ''; ?>"
								href="<?php echo esc_url( add_query_arg( array( 'paged' => $p, 's' => $search ), $base_url ) ); ?>"
							><?php echo esc_html( $p ); ?></a>
						<?php endfor; ?>
					</div>
				<?php endif; ?>
			<?php endif; ?>
		</div><!-- /.bam-card -->

		<?php endif; // end if/else edit_patient ?>

	</main>
</div><!-- /.bam-admin-wrap -->

<script>
(function () {
	'use strict';

	document.addEventListener('DOMContentLoaded', function () {

		// Confirm before deleting a patient.
		document.querySelectorAll('.bam-confirm-delete').forEach(function (el) {
			el.addEventListener('click', function (e) {
				if (!window.confirm('<?php echo esc_js( __( '¿Eliminar este paciente? Esta acción no se puede deshacer.', 'beforeaftermycare' ) ); ?>')) {
					e.preventDefault();
				}
			});
		});

		// Confirm before toggling a patient's status.
		document.querySelectorAll('.bam-confirm-toggle').forEach(function (el) {
			el.addEventListener('click', function (e) {
				if (!window.confirm('<?php echo esc_js( __( '¿Cambiar el estado de este paciente?', 'beforeaftermycare' ) ); ?>')) {
					e.preventDefault();
				}
			});
		});

		// Auto-dismiss success notices after 4 seconds.
		var notices = document.querySelectorAll('.bam-notice');
		if (notices.length) {
			setTimeout(function () {
				notices.forEach(function (el) {
					el.style.transition = 'opacity 0.5s';
					el.style.opacity = '0';
					setTimeout(function () {
						if (el.parentNode) {
							el.parentNode.removeChild(el);
						}
					}, 500);
				});
			}, 4000);
		}
	});
}());
</script>
</body>
</html>
