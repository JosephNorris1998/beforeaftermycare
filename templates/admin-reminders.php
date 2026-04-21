<?php
/**
 * Template: Admin Reminders settings page.
 *
 * Available variables:
 *   $msg            (string) – flash message key.
 *   $reminder_hours (int)    – current reminder lead time in hours.
 *   $reminder_stats (array)  – { total_con_fecha, enviados, pendientes, sin_fecha }.
 *   $rem_patients   (array)  – paginated list of patients with a procedure date.
 *   $rem_total      (int)    – total count of records in the list.
 *   $rem_num_pages  (int)    – total pages for the list.
 *   $paged          (int)    – current page number for the list.
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
		<?php elseif ( 'reminder_sent' === $msg ) : ?>
			<div class="bam-notice bam-notice-success" role="status">
				<?php esc_html_e( 'Recordatorio enviado correctamente al correo del paciente.', 'beforeaftermycare' ); ?>
			</div>
		<?php elseif ( 'reminder_error' === $msg ) : ?>
			<div class="bam-notice bam-notice-error" role="alert">
				<?php esc_html_e( 'No se pudo enviar el recordatorio. Verifica los datos e inténtalo de nuevo.', 'beforeaftermycare' ); ?>
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

		<!-- Manual reminder send form -->
		<div class="bam-card" style="margin-bottom:24px;">
			<div class="bam-card-header">
				<h2 class="bam-card-title">
					<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
					<?php esc_html_e( 'Enviar Recordatorio Manual', 'beforeaftermycare' ); ?>
				</h2>
			</div>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="padding:24px;" id="bam-manual-reminder-form">
				<?php wp_nonce_field( 'bam_send_manual_reminder', 'bam_manual_reminder_nonce' ); ?>
				<input type="hidden" name="action" value="bam_send_manual_reminder">

				<!-- Confirmation checkbox -->
				<div class="bam-field" style="margin-bottom:20px;">
					<label style="display:flex;align-items:flex-start;gap:10px;cursor:pointer;font-size:.9rem;font-weight:600;color:#374151;line-height:1.5;">
						<input
							type="checkbox"
							id="bam-reminder-confirm-check"
							name="bam_reminder_confirmed"
							value="1"
							style="margin-top:3px;width:16px;height:16px;flex-shrink:0;cursor:pointer;"
						>
						<?php esc_html_e( 'Confirmo que he verificado la fecha con el departamento de admisión del hospital.', 'beforeaftermycare' ); ?>
					</label>
				</div>

				<!-- Fields (locked until checkbox is checked) -->
				<div id="bam-reminder-fields" style="opacity:.4;pointer-events:none;transition:opacity .25s;">
					<div style="display:grid;grid-template-columns:1fr 1fr;gap:18px;">

						<div class="bam-field">
							<label class="bam-label" for="bam_reminder_nombre">
								<?php esc_html_e( 'Nombre completo', 'beforeaftermycare' ); ?>
							</label>
							<input
								class="bam-input"
								type="text"
								id="bam_reminder_nombre"
								name="bam_reminder_nombre"
								placeholder="<?php esc_attr_e( 'Nombre del paciente', 'beforeaftermycare' ); ?>"
								disabled
							>
						</div>

						<div class="bam-field">
							<label class="bam-label" for="bam_reminder_correo">
								<?php esc_html_e( 'Correo electrónico', 'beforeaftermycare' ); ?>
							</label>
							<input
								class="bam-input"
								type="email"
								id="bam_reminder_correo"
								name="bam_reminder_correo"
								placeholder="paciente@ejemplo.com"
								disabled
							>
						</div>

						<div class="bam-field">
							<label class="bam-label" for="bam_reminder_fecha">
								<?php esc_html_e( 'Fecha y hora', 'beforeaftermycare' ); ?>
							</label>
							<input
								class="bam-input"
								type="datetime-local"
								id="bam_reminder_fecha"
								name="bam_reminder_fecha"
								placeholder="mm/dd/yyyy --:-- --"
								disabled
							>
						</div>

						<div class="bam-field">
							<label class="bam-label" for="bam_reminder_procedimiento">
								<?php esc_html_e( 'Procedimiento', 'beforeaftermycare' ); ?>
							</label>
							<input
								class="bam-input"
								type="text"
								id="bam_reminder_procedimiento"
								name="bam_reminder_procedimiento"
								value="Colonoscopia"
								disabled
							>
						</div>

					</div>

					<div style="margin-top:20px;">
						<button type="submit" class="bam-btn bam-btn-primary" disabled id="bam-reminder-submit-btn">
							<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
							<?php esc_html_e( 'Enviar recordatorio al correo del paciente', 'beforeaftermycare' ); ?>
						</button>
					</div>
				</div><!-- /#bam-reminder-fields -->

			</form>
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

		<!-- ── Estadísticas de Recordatorios ─────────────────────────────── -->
		<h2 style="margin:36px 0 16px;font-size:1.1rem;font-weight:700;color:#1e293b;">
			<?php esc_html_e( 'Estadísticas de Recordatorios', 'beforeaftermycare' ); ?>
		</h2>
		<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:28px;">

			<div class="bam-card" style="padding:20px 24px;border-top:3px solid #0096c7;">
				<p style="margin:0 0 6px;font-size:.8rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.04em;">
					<?php esc_html_e( 'Con Fecha Asignada', 'beforeaftermycare' ); ?>
				</p>
				<p style="margin:0;font-size:2rem;font-weight:800;color:#0077b6;"><?php echo esc_html( $reminder_stats['total_con_fecha'] ); ?></p>
				<p style="margin:4px 0 0;font-size:.75rem;color:#94a3b8;"><?php esc_html_e( 'pacientes con procedimiento agendado', 'beforeaftermycare' ); ?></p>
			</div>

			<div class="bam-card" style="padding:20px 24px;border-top:3px solid #16a34a;">
				<p style="margin:0 0 6px;font-size:.8rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.04em;">
					<?php esc_html_e( 'Recordatorios Enviados', 'beforeaftermycare' ); ?>
				</p>
				<p style="margin:0;font-size:2rem;font-weight:800;color:#16a34a;"><?php echo esc_html( $reminder_stats['enviados'] ); ?></p>
				<p style="margin:4px 0 0;font-size:.75rem;color:#94a3b8;"><?php esc_html_e( 'correos de recordatorio enviados', 'beforeaftermycare' ); ?></p>
			</div>

			<div class="bam-card" style="padding:20px 24px;border-top:3px solid #f59e0b;">
				<p style="margin:0 0 6px;font-size:.8rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.04em;">
					<?php esc_html_e( 'Pendientes de Envío', 'beforeaftermycare' ); ?>
				</p>
				<p style="margin:0;font-size:2rem;font-weight:800;color:#d97706;"><?php echo esc_html( $reminder_stats['pendientes'] ); ?></p>
				<p style="margin:4px 0 0;font-size:.75rem;color:#94a3b8;"><?php esc_html_e( 'procedimientos futuros sin recordatorio', 'beforeaftermycare' ); ?></p>
			</div>

			<div class="bam-card" style="padding:20px 24px;border-top:3px solid #e2e8f0;">
				<p style="margin:0 0 6px;font-size:.8rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.04em;">
					<?php esc_html_e( 'Sin Fecha', 'beforeaftermycare' ); ?>
				</p>
				<p style="margin:0;font-size:2rem;font-weight:800;color:#94a3b8;"><?php echo esc_html( $reminder_stats['sin_fecha'] ); ?></p>
				<p style="margin:4px 0 0;font-size:.75rem;color:#94a3b8;"><?php esc_html_e( 'pacientes activos sin procedimiento asignado', 'beforeaftermycare' ); ?></p>
			</div>

		</div>

		<!-- ── Lista de Registros de Recordatorios ────────────────────────── -->
		<div class="bam-card">
			<div class="bam-card-header" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
				<h2 class="bam-card-title">
					<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
					<?php esc_html_e( 'Registros de Recordatorios', 'beforeaftermycare' ); ?>
				</h2>
				<span style="font-size:.8rem;color:#64748b;">
					<?php
					printf(
						/* translators: %d = total records */
						esc_html__( '%d registros en total', 'beforeaftermycare' ),
						(int) $rem_total
					);
					?>
				</span>
			</div>

			<?php if ( empty( $rem_patients ) ) : ?>
				<div style="padding:40px 24px;text-align:center;color:#94a3b8;">
					<svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin-bottom:12px;opacity:.4;" aria-hidden="true"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
					<p style="margin:0;font-size:.9rem;"><?php esc_html_e( 'No hay pacientes con fecha de procedimiento asignada todavía.', 'beforeaftermycare' ); ?></p>
				</div>
			<?php else : ?>
				<div style="overflow-x:auto;">
					<table class="bam-table" style="width:100%;border-collapse:collapse;">
						<thead>
							<tr>
								<th style="text-align:left;padding:12px 16px;font-size:.75rem;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.05em;border-bottom:2px solid #e2e8f0;white-space:nowrap;"><?php esc_html_e( 'Paciente', 'beforeaftermycare' ); ?></th>
								<th style="text-align:left;padding:12px 16px;font-size:.75rem;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.05em;border-bottom:2px solid #e2e8f0;white-space:nowrap;"><?php esc_html_e( 'Correo', 'beforeaftermycare' ); ?></th>
								<th style="text-align:left;padding:12px 16px;font-size:.75rem;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.05em;border-bottom:2px solid #e2e8f0;white-space:nowrap;"><?php esc_html_e( 'Procedimiento', 'beforeaftermycare' ); ?></th>
								<th style="text-align:left;padding:12px 16px;font-size:.75rem;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.05em;border-bottom:2px solid #e2e8f0;white-space:nowrap;"><?php esc_html_e( 'Fecha Procedimiento', 'beforeaftermycare' ); ?></th>
								<th style="text-align:center;padding:12px 16px;font-size:.75rem;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.05em;border-bottom:2px solid #e2e8f0;white-space:nowrap;"><?php esc_html_e( 'Aviso (h)', 'beforeaftermycare' ); ?></th>
								<th style="text-align:center;padding:12px 16px;font-size:.75rem;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.05em;border-bottom:2px solid #e2e8f0;white-space:nowrap;"><?php esc_html_e( 'Estado', 'beforeaftermycare' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $rem_patients as $rp ) : ?>
								<?php
								$is_sent   = (bool) $rp->recordatorio_enviado;
								$fecha_dt  = $rp->fecha_procedimiento ? strtotime( $rp->fecha_procedimiento ) : 0;
								$is_past   = $fecha_dt && $fecha_dt < time();
								if ( $is_sent ) {
									$badge_bg    = '#dcfce7';
									$badge_color = '#16a34a';
									$badge_text  = __( 'Enviado', 'beforeaftermycare' );
								} elseif ( $is_past ) {
									$badge_bg    = '#f1f5f9';
									$badge_color = '#64748b';
									$badge_text  = __( 'Vencido', 'beforeaftermycare' );
								} else {
									$badge_bg    = '#fef9c3';
									$badge_color = '#a16207';
									$badge_text  = __( 'Pendiente', 'beforeaftermycare' );
								}
								?>
								<tr style="border-bottom:1px solid #f1f5f9;">
									<td style="padding:14px 16px;font-size:.875rem;color:#1e293b;font-weight:600;">
										<a href="<?php echo esc_url( add_query_arg( array( 'page' => 'bam-patient-detail', 'bam_id' => $rp->id ), admin_url( 'admin.php' ) ) ); ?>" style="color:#0077b6;text-decoration:none;">
											<?php echo esc_html( $rp->nombre ); ?>
										</a>
									</td>
									<td style="padding:14px 16px;font-size:.875rem;color:#475569;"><?php echo esc_html( $rp->correo ); ?></td>
									<td style="padding:14px 16px;font-size:.875rem;color:#475569;"><?php echo esc_html( $rp->procedimiento ?: '—' ); ?></td>
									<td style="padding:14px 16px;font-size:.875rem;color:#475569;white-space:nowrap;">
										<?php echo $fecha_dt ? esc_html( gmdate( 'd/m/Y H:i', $fecha_dt ) ) : '—'; ?>
									</td>
									<td style="padding:14px 16px;font-size:.875rem;color:#475569;text-align:center;"><?php echo esc_html( $rp->recordatorio_horas ); ?>h</td>
									<td style="padding:14px 16px;text-align:center;">
										<span style="display:inline-block;padding:3px 10px;border-radius:99px;font-size:.75rem;font-weight:700;background:<?php echo esc_attr( $badge_bg ); ?>;color:<?php echo esc_attr( $badge_color ); ?>;">
											<?php echo esc_html( $badge_text ); ?>
										</span>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>

				<?php if ( $rem_num_pages > 1 ) : ?>
					<div style="padding:16px 24px;display:flex;gap:8px;align-items:center;justify-content:flex-end;border-top:1px solid #f1f5f9;">
						<?php for ( $p = 1; $p <= $rem_num_pages; $p++ ) : ?>
							<a
								href="<?php echo esc_url( add_query_arg( array( 'page' => 'bam-reminders', 'paged' => $p ), admin_url( 'admin.php' ) ) ); ?>"
								style="display:inline-block;padding:5px 11px;border-radius:6px;font-size:.8rem;font-weight:600;text-decoration:none;<?php echo ( $p === $paged ) ? 'background:#0077b6;color:#fff;' : 'background:#f1f5f9;color:#374151;'; ?>"
							><?php echo esc_html( $p ); ?></a>
						<?php endfor; ?>
					</div>
				<?php endif; ?>

			<?php endif; ?>
		</div>

	</main>
</div>
