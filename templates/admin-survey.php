<?php
/**
 * Template: Admin – Encuesta de Satisfacción.
 *
 * Available variables:
 *   $msg          (string) – status key ('email_saved' | '').
 *   $responses    (array)  – current page of survey responses.
 *   $total        (int)    – total responses.
 *   $page         (int)    – current page.
 *   $num_pages    (int)    – total pages.
 *   $stats        (array)  – aggregate statistics.
 *   $survey_email (string) – current notification email.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:ignore WordPress.Security.NonceVerification
$msg = isset( $_GET['bam_survey_msg'] ) ? sanitize_key( $_GET['bam_survey_msg'] ) : '';
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
			<a class="bam-nav-link" href="<?php echo esc_url( admin_url( 'admin.php?page=bam-cache' ) ); ?>">
				<?php esc_html_e( 'Limpiar Caché', 'beforeaftermycare' ); ?>
			</a>
			<a class="bam-nav-link bam-nav-active" href="<?php echo esc_url( admin_url( 'admin.php?page=bam-survey' ) ); ?>">
				<?php esc_html_e( 'Encuesta', 'beforeaftermycare' ); ?>
			</a>
		</nav>
	</header>

	<main class="bam-main">
		<div class="bam-page-header">
			<h1 class="bam-page-title"><?php esc_html_e( 'Encuesta de Satisfacción', 'beforeaftermycare' ); ?></h1>
			<p class="bam-page-desc"><?php esc_html_e( 'Estadísticas y respuestas de la encuesta de satisfacción de pacientes.', 'beforeaftermycare' ); ?></p>
		</div>

		<?php if ( 'email_saved' === $msg ) : ?>
			<div class="bam-notice bam-notice-success">
				<?php esc_html_e( '✅ Correo de notificación actualizado correctamente.', 'beforeaftermycare' ); ?>
			</div>
		<?php endif; ?>

		<!-- Shortcode info -->
		<div class="bam-notice" style="background:#e8f4fd;border-left:4px solid #0077b6;padding:12px 16px;margin-bottom:20px;border-radius:4px;display:flex;flex-wrap:wrap;gap:16px;align-items:center;">
			<span>
				<strong><?php esc_html_e( 'Encuesta:', 'beforeaftermycare' ); ?></strong>
				<code style="background:#fff;padding:2px 8px;border-radius:3px;font-size:.9rem;">[bam_encuesta]</code>
			</span>
			<span style="color:#64748b;font-size:.85rem;"><?php esc_html_e( 'Agrega a cualquier página para mostrar la encuesta de satisfacción.', 'beforeaftermycare' ); ?></span>
			<span style="border-left:1px solid #bae6fd;padding-left:16px;">
				<strong><?php esc_html_e( 'Recordatorio:', 'beforeaftermycare' ); ?></strong>
				<code style="background:#fff;padding:2px 8px;border-radius:3px;font-size:.9rem;">[bam_recordatorio]</code>
			</span>
			<span style="color:#64748b;font-size:.85rem;"><?php esc_html_e( 'Muestra la próxima cita del paciente y el estado del recordatorio.', 'beforeaftermycare' ); ?></span>
		</div>

		<!-- Stat cards -->
		<div class="bam-stat-grid" style="margin-bottom:28px;">
			<div class="bam-stat-card">
				<div class="bam-stat-icon bam-icon-blue">
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
				</div>
				<div class="bam-stat-info">
					<span class="bam-stat-value"><?php echo esc_html( number_format_i18n( $stats['total'] ) ); ?></span>
					<span class="bam-stat-label"><?php esc_html_e( 'Respuestas totales', 'beforeaftermycare' ); ?></span>
				</div>
			</div>

			<div class="bam-stat-card">
				<div class="bam-stat-icon bam-icon-orange">
					<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor" stroke="none" aria-hidden="true"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
				</div>
				<div class="bam-stat-info">
					<span class="bam-stat-value"><?php echo esc_html( $stats['avg_rating'] > 0 ? $stats['avg_rating'] . ' / 5' : '—' ); ?></span>
					<span class="bam-stat-label"><?php esc_html_e( 'Satisfacción global prom.', 'beforeaftermycare' ); ?></span>
				</div>
			</div>

			<div class="bam-stat-card">
				<div class="bam-stat-icon bam-icon-green">
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="10"/><polyline points="8 12 11 15 16 9"/></svg>
				</div>
				<div class="bam-stat-info">
					<span class="bam-stat-value"><?php echo esc_html( $stats['avg_indicaciones'] > 0 ? $stats['avg_indicaciones'] . ' / 5' : '—' ); ?></span>
					<span class="bam-stat-label"><?php esc_html_e( 'Prom. Indicaciones prep.', 'beforeaftermycare' ); ?></span>
				</div>
			</div>

			<div class="bam-stat-card">
				<div class="bam-stat-icon bam-icon-purple">
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
				</div>
				<div class="bam-stat-info">
					<span class="bam-stat-value"><?php echo esc_html( $stats['avg_admision'] > 0 ? $stats['avg_admision'] . ' / 5' : '—' ); ?></span>
					<span class="bam-stat-label"><?php esc_html_e( 'Prom. Admisión', 'beforeaftermycare' ); ?></span>
				</div>
			</div>
		</div>

		<!-- Distribution charts – moment averages -->
		<?php if ( $stats['total'] > 0 ) : ?>
		<div class="bam-stat-grid" style="margin-bottom:28px;">
			<div class="bam-card">
				<div class="bam-card-header">
					<h2 class="bam-card-title"><?php esc_html_e( 'Promedios por Momento', 'beforeaftermycare' ); ?></h2>
				</div>
				<div style="padding:16px 24px;">
					<?php
					$moment_avgs = array(
						__( 'Indicaciones para la preparación', 'beforeaftermycare' ) => $stats['avg_indicaciones'],
						__( 'Admisión', 'beforeaftermycare' )                         => $stats['avg_admision'],
						__( 'Sala de preparación y recuperación', 'beforeaftermycare' ) => $stats['avg_sala_preparacion'],
						__( 'Salida del hospital', 'beforeaftermycare' )               => $stats['avg_salida_hospital'],
						__( 'Satisfacción global', 'beforeaftermycare' )               => $stats['avg_rating'],
					);
					foreach ( $moment_avgs as $label => $avg ) :
						$pct = $avg > 0 ? round( ( $avg / 5 ) * 100 ) : 0;
					?>
						<div style="margin-bottom:12px;">
							<div style="display:flex;justify-content:space-between;margin-bottom:4px;">
								<span style="font-size:.85rem;font-weight:600;"><?php echo esc_html( $label ); ?></span>
								<span style="font-size:.85rem;color:var(--bam-muted);"><?php echo $avg > 0 ? esc_html( $avg . ' / 5' ) : '—'; ?></span>
							</div>
							<div style="background:#e9ecef;border-radius:4px;height:8px;overflow:hidden;">
								<div style="background:linear-gradient(90deg,#0096c7,#48cae4);height:100%;width:<?php echo esc_attr( $pct ); ?>%;border-radius:4px;"></div>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
		<?php endif; ?>

		<!-- Email settings -->
		<div class="bam-card" style="margin-bottom:28px;">
			<div class="bam-card-header">
				<h2 class="bam-card-title">
					<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
					<?php esc_html_e( 'Correo de notificación', 'beforeaftermycare' ); ?>
				</h2>
			</div>
			<div style="padding:20px 24px;">
				<p style="color:var(--bam-muted);margin:0 0 16px;font-size:.9rem;">
					<?php esc_html_e( 'Cada vez que un paciente complete la encuesta, se enviará un correo electrónico a la dirección configurada aquí.', 'beforeaftermycare' ); ?>
				</p>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap;">
					<input type="hidden" name="action" value="bam_save_survey_email">
					<?php wp_nonce_field( 'bam_save_survey_email', 'bam_survey_email_nonce' ); ?>
					<div class="bam-field" style="flex:1;min-width:260px;margin:0;">
						<label class="bam-label" for="bam_survey_email_field">
							<?php esc_html_e( 'Correo electrónico', 'beforeaftermycare' ); ?>
						</label>
						<input
							class="bam-input"
							type="email"
							id="bam_survey_email_field"
							name="bam_survey_email"
							value="<?php echo esc_attr( $survey_email ); ?>"
							placeholder="admin@ejemplo.com"
							required
						>
					</div>
					<button type="submit" class="bam-btn bam-btn-primary">
						<?php esc_html_e( 'Guardar', 'beforeaftermycare' ); ?>
					</button>
				</form>
			</div>
		</div>

		<!-- Responses table -->
		<div class="bam-card">
			<div class="bam-card-header">
				<h2 class="bam-card-title"><?php esc_html_e( 'Respuestas Recibidas', 'beforeaftermycare' ); ?></h2>
				<span class="bam-result-count">
					<?php
					printf(
						/* translators: %s: number */
						esc_html( _n( '%s respuesta', '%s respuestas', $total, 'beforeaftermycare' ) ),
						esc_html( number_format_i18n( $total ) )
					);
					?>
				</span>
			</div>

			<?php if ( empty( $responses ) ) : ?>
				<div class="bam-empty-state">
					<p><?php esc_html_e( 'Aún no se han recibido respuestas de la encuesta.', 'beforeaftermycare' ); ?></p>
				</div>
			<?php else : ?>
				<div class="bam-table-wrapper">
					<table class="bam-table">
						<thead>
							<tr>
								<th><?php esc_html_e( '#', 'beforeaftermycare' ); ?></th>
								<th><?php esc_html_e( 'Paciente', 'beforeaftermycare' ); ?></th>
								<th><?php esc_html_e( 'Correo', 'beforeaftermycare' ); ?></th>
								<th title="<?php esc_attr_e( 'Indicaciones para la preparación', 'beforeaftermycare' ); ?>"><?php esc_html_e( 'Indicaciones', 'beforeaftermycare' ); ?></th>
								<th><?php esc_html_e( 'Admisión', 'beforeaftermycare' ); ?></th>
								<th title="<?php esc_attr_e( 'Sala de preparación y recuperación', 'beforeaftermycare' ); ?>"><?php esc_html_e( 'Sala Prep.', 'beforeaftermycare' ); ?></th>
								<th title="<?php esc_attr_e( 'Salida del hospital', 'beforeaftermycare' ); ?>"><?php esc_html_e( 'Salida', 'beforeaftermycare' ); ?></th>
								<th title="<?php esc_attr_e( 'Satisfacción global', 'beforeaftermycare' ); ?>"><?php esc_html_e( 'S. Global', 'beforeaftermycare' ); ?></th>
								<th><?php esc_html_e( 'Comentarios', 'beforeaftermycare' ); ?></th>
								<th><?php esc_html_e( 'Fecha', 'beforeaftermycare' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php
							$scale_labels = array( 1 => 'Muy insatisfecho', 2 => 'Insatisfecho', 3 => 'Neutral', 4 => 'Satisfecho', 5 => 'Muy satisfecho' );
							$rating_html  = function( $val ) use ( $scale_labels ) {
								if ( ! $val ) return '<span style="color:#9ca3af;">—</span>';
								$colors = array( 1 => '#ef4444', 2 => '#f97316', 3 => '#f59e0b', 4 => '#22c55e', 5 => '#16a34a' );
								$color  = $colors[ (int) $val ] ?? '#374151';
								$title  = $scale_labels[ (int) $val ] ?? '';
								return '<span style="font-weight:700;color:' . esc_attr( $color ) . ';" title="' . esc_attr( $title ) . '">' . esc_html( $val ) . '/5</span>';
							};
							foreach ( $responses as $r ) : ?>
								<tr>
									<td><?php echo esc_html( $r->id ); ?></td>
									<td>
										<?php echo esc_html( $r->patient_name ?: '—' ); ?>
										<?php if ( $r->patient_id ) : ?>
											<br><small style="color:var(--bam-muted);">
												<?php
												$linked_url = add_query_arg( array( 'page' => 'bam-patient-detail', 'bam_id' => $r->patient_id ), admin_url( 'admin.php' ) );
												?>
												<a href="<?php echo esc_url( $linked_url ); ?>" class="bam-link">
													<?php
													/* translators: %d: patient ID */
													printf( esc_html__( 'Paciente #%d', 'beforeaftermycare' ), (int) $r->patient_id );
													?>
												</a>
											</small>
										<?php endif; ?>
									</td>
									<td><?php echo esc_html( $r->patient_email ?: '—' ); ?></td>
									<td style="text-align:center;"><?php echo $rating_html( $r->momento_indicaciones ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></td>
									<td style="text-align:center;"><?php echo $rating_html( $r->momento_admision ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></td>
									<td style="text-align:center;"><?php echo $rating_html( $r->momento_sala_preparacion ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></td>
									<td style="text-align:center;"><?php echo $rating_html( $r->momento_salida_hospital ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></td>
									<td style="text-align:center;"><?php echo $rating_html( $r->satisfaccion_global ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></td>
									<td style="max-width:200px;font-size:.8rem;"><?php echo $r->comentarios ? esc_html( $r->comentarios ) : '—'; ?></td>
									<td style="white-space:nowrap;"><?php echo esc_html( mysql2date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $r->fecha_envio ) ); ?></td>
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
								href="<?php echo esc_url( add_query_arg( array( 'page' => 'bam-survey', 'paged' => $p ), admin_url( 'admin.php' ) ) ); ?>"
							><?php echo esc_html( $p ); ?></a>
						<?php endfor; ?>
					</div>
				<?php endif; ?>

			<?php endif; ?>
		</div>

	</main>
</div>
