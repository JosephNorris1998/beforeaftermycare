<?php
/**
 * Template: Admin Dashboard overview.
 *
 * Available variables:
 *   $total   (int)   – all-time patient count.
 *   $active  (int)   – active patient count.
 *   $recent  (int)   – patients registered in the last 30 days.
 *   $latest  (array) – last 5 patients.
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
			<a class="bam-nav-link bam-nav-active" href="<?php echo esc_url( admin_url( 'admin.php?page=bam-dashboard' ) ); ?>">
				<?php esc_html_e( 'Dashboard', 'beforeaftermycare' ); ?>
			</a>
			<a class="bam-nav-link" href="<?php echo esc_url( admin_url( 'admin.php?page=bam-patients' ) ); ?>">
				<?php esc_html_e( 'Pacientes', 'beforeaftermycare' ); ?>
			</a>
			<a class="bam-nav-link" href="<?php echo esc_url( admin_url( 'admin.php?page=bam-urls' ) ); ?>">
				<?php esc_html_e( 'URLs del Plugin', 'beforeaftermycare' ); ?>
			</a>
			<a class="bam-nav-link" href="<?php echo esc_url( admin_url( 'admin.php?page=bam-cache' ) ); ?>">
				<?php esc_html_e( 'Limpiar Caché', 'beforeaftermycare' ); ?>
			</a>
		</nav>
	</header>

	<main class="bam-main">
		<div class="bam-page-header">
			<h1 class="bam-page-title"><?php esc_html_e( 'Dashboard', 'beforeaftermycare' ); ?></h1>
			<p class="bam-page-desc"><?php esc_html_e( 'Resumen general del sistema de guías médicas.', 'beforeaftermycare' ); ?></p>
		</div>

		<!-- Stat cards -->
		<div class="bam-stat-grid">
			<div class="bam-stat-card">
				<div class="bam-stat-icon bam-icon-blue">
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
				</div>
				<div class="bam-stat-info">
					<span class="bam-stat-value"><?php echo esc_html( number_format_i18n( $total ) ); ?></span>
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
					<span class="bam-stat-value"><?php echo $total > 0 ? esc_html( round( ( $active / $total ) * 100 ) ) . '%' : '0%'; ?></span>
					<span class="bam-stat-label"><?php esc_html_e( 'Tasa de Actividad', 'beforeaftermycare' ); ?></span>
				</div>
			</div>
		</div>

		<!-- Latest registrations -->
		<div class="bam-card">
			<div class="bam-card-header">
				<h2 class="bam-card-title"><?php esc_html_e( 'Últimos Registros', 'beforeaftermycare' ); ?></h2>
				<a class="bam-btn bam-btn-sm bam-btn-outline" href="<?php echo esc_url( admin_url( 'admin.php?page=bam-patients' ) ); ?>">
					<?php esc_html_e( 'Ver todos', 'beforeaftermycare' ); ?>
				</a>
			</div>
			<?php if ( empty( $latest ) ) : ?>
				<div class="bam-empty-state">
					<p><?php esc_html_e( 'Aún no hay pacientes registrados.', 'beforeaftermycare' ); ?></p>
				</div>
			<?php else : ?>
				<div class="bam-table-wrapper">
					<table class="bam-table">
						<thead>
							<tr>
								<th><?php esc_html_e( 'Nombre', 'beforeaftermycare' ); ?></th>
								<th><?php esc_html_e( 'Usuario', 'beforeaftermycare' ); ?></th>
								<th><?php esc_html_e( 'Correo', 'beforeaftermycare' ); ?></th>
								<th><?php esc_html_e( 'Guía', 'beforeaftermycare' ); ?></th>
								<th><?php esc_html_e( 'Fecha', 'beforeaftermycare' ); ?></th>
								<th><?php esc_html_e( 'Estado', 'beforeaftermycare' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $latest as $patient ) : ?>
								<tr>
									<td>
										<a href="<?php echo esc_url( add_query_arg( array( 'page' => 'bam-patient-detail', 'bam_id' => $patient->id ), admin_url( 'admin.php' ) ) ); ?>" class="bam-link">
											<?php echo esc_html( $patient->nombre ); ?>
										</a>
									</td>
									<td><?php echo esc_html( $patient->usuario ); ?></td>
									<td><?php echo esc_html( $patient->correo ); ?></td>
									<td><?php echo $patient->guia_asignada ? esc_html( $patient->guia_asignada ) : '—'; ?></td>
									<td><?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $patient->fecha_registro ) ) ); ?></td>
									<td>
										<span class="bam-badge <?php echo $patient->estado ? 'bam-badge-success' : 'bam-badge-inactive'; ?>">
											<?php echo $patient->estado ? esc_html__( 'Activo', 'beforeaftermycare' ) : esc_html__( 'Inactivo', 'beforeaftermycare' ); ?>
										</span>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			<?php endif; ?>
		</div>
	</main>
</div>
