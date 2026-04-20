<?php
/**
 * Template: Admin Patient Detail / Edit.
 *
 * Available variables:
 *   $patient     (object) – patient DB row.
 *   $patient_id  (int)    – patient ID.
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
			<div>
				<a class="bam-back-link" href="<?php echo esc_url( admin_url( 'admin.php?page=bam-patients' ) ); ?>">
					← <?php esc_html_e( 'Volver a Pacientes', 'beforeaftermycare' ); ?>
				</a>
				<h1 class="bam-page-title"><?php echo esc_html( $patient->nombre ); ?></h1>
			</div>
		</div>

		<?php if ( 'updated' === $msg ) : ?>
			<div class="bam-notice bam-notice-success"><?php esc_html_e( 'Paciente actualizado correctamente.', 'beforeaftermycare' ); ?></div>
		<?php endif; ?>

		<div class="bam-detail-grid">

			<!-- Edit form -->
			<div class="bam-card bam-detail-form-card">
				<div class="bam-card-header">
					<h2 class="bam-card-title"><?php esc_html_e( 'Editar Datos', 'beforeaftermycare' ); ?></h2>
				</div>
				<form method="post" action="<?php echo esc_url( add_query_arg( array( 'page' => 'bam-patient-detail', 'bam_id' => $patient_id, 'bam_action' => 'update' ), admin_url( 'admin.php' ) ) ); ?>">
					<?php wp_nonce_field( 'bam_update_patient_' . $patient_id, 'bam_update_nonce' ); ?>

					<div class="bam-field">
						<label class="bam-label" for="bam_nombre"><?php esc_html_e( 'Nombre Completo', 'beforeaftermycare' ); ?></label>
						<input class="bam-input" type="text" id="bam_nombre" name="bam_nombre" value="<?php echo esc_attr( $patient->nombre ); ?>" required>
					</div>

					<div class="bam-field">
						<label class="bam-label" for="bam_correo"><?php esc_html_e( 'Correo', 'beforeaftermycare' ); ?></label>
						<input class="bam-input" type="email" id="bam_correo" name="bam_correo" value="<?php echo esc_attr( $patient->correo ); ?>" required>
					</div>

					<div class="bam-field">
						<label class="bam-label" for="bam_telefono"><?php esc_html_e( 'Teléfono', 'beforeaftermycare' ); ?></label>
						<input class="bam-input" type="tel" id="bam_telefono" name="bam_telefono" value="<?php echo esc_attr( $patient->telefono ?? '' ); ?>">
					</div>

					<div class="bam-field">
						<label class="bam-label" for="bam_guia_asignada"><?php esc_html_e( 'Guía Asignada', 'beforeaftermycare' ); ?></label>
						<input class="bam-input" type="text" id="bam_guia_asignada" name="bam_guia_asignada" value="<?php echo esc_attr( $patient->guia_asignada ?? '' ); ?>" placeholder="guia-de-colonoscopia">
					</div>

					<button type="submit" name="bam_update_submit" class="bam-btn bam-btn-primary">
						<?php esc_html_e( 'Guardar Cambios', 'beforeaftermycare' ); ?>
					</button>
				</form>
			</div>

			<!-- Info panel -->
			<div class="bam-detail-info-col">
				<div class="bam-card">
					<div class="bam-card-header">
						<h2 class="bam-card-title"><?php esc_html_e( 'Información', 'beforeaftermycare' ); ?></h2>
					</div>
					<dl class="bam-info-list">
						<div class="bam-info-row">
							<dt><?php esc_html_e( 'ID Paciente', 'beforeaftermycare' ); ?></dt>
							<dd>#<?php echo esc_html( $patient->id ); ?></dd>
						</div>
						<div class="bam-info-row">
							<dt><?php esc_html_e( 'Usuario', 'beforeaftermycare' ); ?></dt>
							<dd><?php echo esc_html( $patient->usuario ); ?></dd>
						</div>
						<div class="bam-info-row">
							<dt><?php esc_html_e( 'WP User ID', 'beforeaftermycare' ); ?></dt>
							<dd><?php echo $patient->wp_user_id ? esc_html( '#' . $patient->wp_user_id ) : '—'; ?></dd>
						</div>
						<div class="bam-info-row">
							<dt><?php esc_html_e( 'Registro', 'beforeaftermycare' ); ?></dt>
							<dd><?php echo esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $patient->fecha_registro ) ) ); ?></dd>
						</div>
						<div class="bam-info-row">
							<dt><?php esc_html_e( 'Estado', 'beforeaftermycare' ); ?></dt>
							<dd>
								<span class="bam-badge <?php echo $patient->estado ? 'bam-badge-success' : 'bam-badge-inactive'; ?>">
									<?php echo $patient->estado ? esc_html__( 'Activo', 'beforeaftermycare' ) : esc_html__( 'Inactivo', 'beforeaftermycare' ); ?>
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
						<a
							href="<?php echo esc_url( add_query_arg( array( 'page' => 'bam-patients', 'bam_action' => 'toggle', 'bam_id' => $patient_id, 'bam_nonce' => wp_create_nonce( 'bam_action_toggle' ) ), admin_url( 'admin.php' ) ) ); ?>"
							class="bam-btn bam-btn-warning bam-btn-block bam-confirm-toggle"
						>
							<?php echo $patient->estado ? esc_html__( 'Desactivar Paciente', 'beforeaftermycare' ) : esc_html__( 'Activar Paciente', 'beforeaftermycare' ); ?>
						</a>
						<a
							href="<?php echo esc_url( add_query_arg( array( 'page' => 'bam-patients', 'bam_action' => 'delete', 'bam_id' => $patient_id, 'bam_nonce' => wp_create_nonce( 'bam_action_delete' ) ), admin_url( 'admin.php' ) ) ); ?>"
							class="bam-btn bam-btn-danger bam-btn-block bam-confirm-delete"
						>
							<?php esc_html_e( 'Eliminar Paciente', 'beforeaftermycare' ); ?>
						</a>
					</div>
				</div>
			</div>
		</div>
	</main>
</div>
