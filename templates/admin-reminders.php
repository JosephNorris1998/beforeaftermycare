<?php
/**
 * Template: Admin Reminders settings page.
 *
 * Available variables:
 *   $msg            (string) – flash message key.
 *   $reminder_hours (int)    – current reminder lead time in hours.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$reminder_options = array(
	1    => __( '1 minuto antes (prueba)', 'beforeaftermycare' ),
	30   => __( '30 minutos antes', 'beforeaftermycare' ),
	60   => __( '1 hora antes', 'beforeaftermycare' ),
	360  => __( '6 horas antes', 'beforeaftermycare' ),
	720  => __( '12 horas antes', 'beforeaftermycare' ),
	1440 => __( '24 horas antes', 'beforeaftermycare' ),
	2880 => __( '48 horas antes', 'beforeaftermycare' ),
	4320 => __( '72 horas antes', 'beforeaftermycare' ),
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
			<a class="bam-nav-link" href="<?php echo esc_url( admin_url( 'admin.php?page=bam-dashboard' ) ); ?>"><?php esc_html_e( 'Dashboard', 'beforeaftermycare' ); ?></a>
			<a class="bam-nav-link" href="<?php echo esc_url( admin_url( 'admin.php?page=bam-patients' ) ); ?>"><?php esc_html_e( 'Pacientes', 'beforeaftermycare' ); ?></a>
			<a class="bam-nav-link" href="<?php echo esc_url( admin_url( 'admin.php?page=bam-urls' ) ); ?>"><?php esc_html_e( 'URLs del Plugin', 'beforeaftermycare' ); ?></a>
			<a class="bam-nav-link" href="<?php echo esc_url( admin_url( 'admin.php?page=bam-cache' ) ); ?>"><?php esc_html_e( 'Limpiar Caché', 'beforeaftermycare' ); ?></a>
			<a class="bam-nav-link" href="<?php echo esc_url( admin_url( 'admin.php?page=bam-survey' ) ); ?>"><?php esc_html_e( 'Encuesta', 'beforeaftermycare' ); ?></a>
			<a class="bam-nav-link bam-nav-active" href="<?php echo esc_url( admin_url( 'admin.php?page=bam-reminders' ) ); ?>"><?php esc_html_e( 'Recordatorios', 'beforeaftermycare' ); ?></a>
		</nav>
	</header>

	<main class="bam-main">
		<div class="bam-page-header">
			<div>
				<h1 class="bam-page-title">
					<?php esc_html_e( 'Recordatorios de Procedimiento', 'beforeaftermycare' ); ?>
				</h1>
				<p class="bam-page-desc">
					<?php esc_html_e( 'Configura cuándo se enviará el recordatorio por correo al paciente antes de su procedimiento médico.', 'beforeaftermycare' ); ?>
				</p>
			</div>
		</div>

		<?php if ( 'saved' === $msg ) : ?>
			<div class="bam-notice bam-notice-success" role="status">
				<?php esc_html_e( 'Configuración de recordatorio guardada correctamente.', 'beforeaftermycare' ); ?>
			</div>
		<?php endif; ?>

		<!-- Shortcode info card -->
		<div class="bam-card" style="margin-bottom:24px;border-left:4px solid #0096c7;background:linear-gradient(135deg,#e0f7fa,#f0fdff);">
			<div style="padding:18px 24px;">
				<p style="margin:0 0 10px;font-size:.9rem;font-weight:700;color:#0077b6;">
					<svg style="vertical-align:-4px;margin-right:6px;" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
					<?php esc_html_e( 'Widget de Recordatorio – Shortcode', 'beforeaftermycare' ); ?>
				</p>
				<p style="margin:0 0 12px;font-size:.875rem;color:#0369a1;line-height:1.6;">
					<?php esc_html_e( 'Agrega el siguiente shortcode a cualquier página para mostrar a cada paciente (sesión iniciada) su próxima cita y el estado del recordatorio:', 'beforeaftermycare' ); ?>
				</p>
				<div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
					<code style="background:#fff;color:#0077b6;padding:8px 18px;border-radius:8px;font-size:1rem;font-weight:700;border:1.5px solid #0096c7;letter-spacing:.03em;">[bam_recordatorio]</code>
					<span style="font-size:.8rem;color:#64748b;"><?php esc_html_e( 'Muestra: nombre, procedimiento, fecha y estado del recordatorio.', 'beforeaftermycare' ); ?></span>
				</div>
			</div>
		</div>

		<div style="display:grid;grid-template-columns:1fr 360px;gap:24px;align-items:start;">

			<!-- Settings card -->
			<div class="bam-card">
				<div class="bam-card-header">
					<h2 class="bam-card-title">
						<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
						<?php esc_html_e( 'Configuración de Envío', 'beforeaftermycare' ); ?>
					</h2>
				</div>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="padding:24px;">
					<?php wp_nonce_field( 'bam_save_reminder_hours', 'bam_reminder_nonce' ); ?>
					<input type="hidden" name="action" value="bam_save_reminder_hours">

					<div class="bam-field">
						<label class="bam-label" for="bam_reminder_hours">
							<?php esc_html_e( 'Enviar recordatorio', 'beforeaftermycare' ); ?>
						</label>
						<select class="bam-input" id="bam_reminder_hours" name="bam_reminder_hours">
							<?php foreach ( $reminder_options as $value => $label ) : ?>
								<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $reminder_hours, $value ); ?>>
									<?php echo esc_html( $label ); ?>
								</option>
							<?php endforeach; ?>
						</select>
						<span style="font-size:0.8rem;color:#64748b;margin-top:6px;display:block;">
							<?php esc_html_e( 'El sistema verificará cada hora los procedimientos próximos y enviará el correo de recordatorio al paciente.', 'beforeaftermycare' ); ?>
						</span>
					</div>

					<button type="submit" class="bam-btn bam-btn-primary">
						<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
						<?php esc_html_e( 'Guardar Configuración', 'beforeaftermycare' ); ?>
					</button>
				</form>
			</div>

			<!-- Info panel -->
			<div class="bam-card">
				<div class="bam-card-header">
					<h2 class="bam-card-title">
						<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
						<?php esc_html_e( 'Cómo funciona', 'beforeaftermycare' ); ?>
					</h2>
				</div>
				<div style="padding:20px;">
					<ul style="margin:0;padding:0 0 0 18px;display:flex;flex-direction:column;gap:12px;">
						<li style="font-size:0.875rem;color:#475569;line-height:1.6;">
							<?php esc_html_e( 'Configure la fecha y hora del procedimiento en el perfil de cada paciente.', 'beforeaftermycare' ); ?>
						</li>
						<li style="font-size:0.875rem;color:#475569;line-height:1.6;">
							<?php esc_html_e( 'El sistema revisa automáticamente cada hora los procedimientos próximos.', 'beforeaftermycare' ); ?>
						</li>
						<li style="font-size:0.875rem;color:#475569;line-height:1.6;">
							<?php esc_html_e( 'Se envía un correo elegante al paciente con los datos de su cita, remitente "Pacifica Salud".', 'beforeaftermycare' ); ?>
						</li>
						<li style="font-size:0.875rem;color:#475569;line-height:1.6;">
							<?php esc_html_e( 'Al cambiar la fecha del procedimiento, el recordatorio se restablece automáticamente.', 'beforeaftermycare' ); ?>
						</li>
					</ul>
					<div style="margin-top:20px;padding:14px;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;">
						<p style="margin:0;font-size:0.8rem;color:#15803d;font-weight:600;">
							<?php esc_html_e( 'Configuración actual:', 'beforeaftermycare' ); ?>
						</p>
						<p style="margin:6px 0 0;font-size:0.875rem;color:#15803d;">
							<?php
							$current_label = isset( $reminder_options[ $reminder_hours ] ) ? $reminder_options[ $reminder_hours ] : $reminder_hours . ' ' . __( 'horas antes', 'beforeaftermycare' );
							echo esc_html( $current_label );
							?>
						</p>
					</div>
				</div>
			</div>

		</div>
	</main>
</div>
