<?php
/**
 * Template: Admin Patients list.
 *
 * Available variables:
 *   $patients  (array) – current page of patients.
 *   $total     (int)   – total matching patients.
 *   $page      (int)   – current page number.
 *   $num_pages (int)   – total pages.
 *   $search    (string)– current search query.
 *   $per_page  (int)   – items per page.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$msg = isset( $_GET['bam_msg'] ) ? sanitize_key( $_GET['bam_msg'] ) : '';
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
			<a class="bam-nav-link bam-nav-active" href="<?php echo esc_url( admin_url( 'admin.php?page=bam-patients' ) ); ?>">
				<?php esc_html_e( 'Pacientes', 'beforeaftermycare' ); ?>
			</a>
			<a class="bam-nav-link" href="<?php echo esc_url( admin_url( 'admin.php?page=bam-urls' ) ); ?>">
				<?php esc_html_e( 'URLs del Plugin', 'beforeaftermycare' ); ?>
			</a>
			<a class="bam-nav-link" href="<?php echo esc_url( admin_url( 'admin.php?page=bam-cache' ) ); ?>">
				<?php esc_html_e( 'Limpiar Caché', 'beforeaftermycare' ); ?>
			</a>
			<a class="bam-nav-link" href="<?php echo esc_url( admin_url( 'admin.php?page=bam-survey' ) ); ?>">
				<?php esc_html_e( 'Encuesta', 'beforeaftermycare' ); ?>
			</a>
		</nav>
	</header>

	<main class="bam-main">
		<div class="bam-page-header">
			<h1 class="bam-page-title"><?php esc_html_e( 'Pacientes', 'beforeaftermycare' ); ?></h1>
		</div>

		<?php if ( 'deleted' === $msg ) : ?>
			<div class="bam-notice bam-notice-success"><?php esc_html_e( 'Paciente eliminado correctamente.', 'beforeaftermycare' ); ?></div>
		<?php elseif ( 'toggled' === $msg ) : ?>
			<div class="bam-notice bam-notice-success"><?php esc_html_e( 'Estado actualizado correctamente.', 'beforeaftermycare' ); ?></div>
		<?php endif; ?>

		<!-- Search bar -->
		<form class="bam-search-form" method="get" action="">
			<input type="hidden" name="page" value="bam-patients">
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
					<a class="bam-btn bam-btn-outline" href="<?php echo esc_url( admin_url( 'admin.php?page=bam-patients' ) ); ?>">
						<?php esc_html_e( 'Limpiar', 'beforeaftermycare' ); ?>
					</a>
				<?php endif; ?>
			</div>
		</form>

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
							<?php foreach ( $patients as $patient ) : ?>
								<?php
								$toggle_url = add_query_arg( array(
									'page'       => 'bam-patients',
									'bam_action' => 'toggle',
									'bam_id'     => $patient->id,
									'bam_nonce'  => wp_create_nonce( 'bam_action_toggle' ),
								), admin_url( 'admin.php' ) );

								$delete_url = add_query_arg( array(
									'page'       => 'bam-patients',
									'bam_action' => 'delete',
									'bam_id'     => $patient->id,
									'bam_nonce'  => wp_create_nonce( 'bam_action_delete' ),
								), admin_url( 'admin.php' ) );

								$detail_url = add_query_arg( array(
									'page'   => 'bam-patient-detail',
									'bam_id' => $patient->id,
								), admin_url( 'admin.php' ) );
								?>
								<tr>
									<td><?php echo esc_html( $patient->id ); ?></td>
									<td>
										<a href="<?php echo esc_url( $detail_url ); ?>" class="bam-link">
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
										<a href="<?php echo esc_url( $detail_url ); ?>" class="bam-btn bam-btn-xs bam-btn-outline" title="<?php esc_attr_e( 'Ver / Editar', 'beforeaftermycare' ); ?>">
											<?php esc_html_e( 'Ver', 'beforeaftermycare' ); ?>
										</a>
										<a href="<?php echo esc_url( $toggle_url ); ?>" class="bam-btn bam-btn-xs bam-btn-warning bam-confirm-toggle" title="<?php esc_attr_e( 'Activar / Desactivar', 'beforeaftermycare' ); ?>">
											<?php esc_html_e( 'Toggle', 'beforeaftermycare' ); ?>
										</a>
										<a href="<?php echo esc_url( $delete_url ); ?>" class="bam-btn bam-btn-xs bam-btn-danger bam-confirm-delete" title="<?php esc_attr_e( 'Eliminar', 'beforeaftermycare' ); ?>">
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
								href="<?php echo esc_url( add_query_arg( array( 'page' => 'bam-patients', 'paged' => $p, 's' => $search ), admin_url( 'admin.php' ) ) ); ?>"
							><?php echo esc_html( $p ); ?></a>
						<?php endfor; ?>
					</div>
				<?php endif; ?>
			<?php endif; ?>
		</div>
	</main>
</div>
