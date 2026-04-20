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
		<div class="bam-notice" style="background:#e8f4fd;border-left:4px solid #0077b6;padding:12px 16px;margin-bottom:20px;border-radius:4px;">
			<strong><?php esc_html_e( 'Shortcode:', 'beforeaftermycare' ); ?></strong>
			<code style="background:#fff;padding:2px 8px;border-radius:3px;font-size:.9rem;">[bam_encuesta]</code>
			&nbsp;–&nbsp;
			<?php esc_html_e( 'Agrega este shortcode a cualquier página para mostrar la encuesta.', 'beforeaftermycare' ); ?>
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
					<span class="bam-stat-label"><?php esc_html_e( 'Calificación promedio', 'beforeaftermycare' ); ?></span>
				</div>
			</div>

			<div class="bam-stat-card">
				<div class="bam-stat-icon bam-icon-green">
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
				</div>
				<div class="bam-stat-info">
					<span class="bam-stat-value">
						<?php
						$guia_pct = $stats['total'] > 0 ? round( ( $stats['guia_util'] / $stats['total'] ) * 100 ) : 0;
						echo esc_html( $guia_pct . '%' );
						?>
					</span>
					<span class="bam-stat-label"><?php esc_html_e( 'Guía fue útil', 'beforeaftermycare' ); ?></span>
				</div>
			</div>

			<div class="bam-stat-card">
				<div class="bam-stat-icon bam-icon-purple">
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
				</div>
				<div class="bam-stat-info">
					<?php
					$si_count = 0;
					foreach ( $stats['recomienda_dist'] as $r ) {
						if ( 'Sí' === $r->recomendaria ) {
							$si_count = (int) $r->count;
							break;
						}
					}
					$recom_pct = $stats['total'] > 0 ? round( ( $si_count / $stats['total'] ) * 100 ) : 0;
					?>
					<span class="bam-stat-value"><?php echo esc_html( $recom_pct . '%' ); ?></span>
					<span class="bam-stat-label"><?php esc_html_e( 'Recomendarían el servicio', 'beforeaftermycare' ); ?></span>
				</div>
			</div>
		</div>

		<!-- Distribution charts (text-based) -->
		<?php if ( $stats['total'] > 0 ) : ?>
		<div class="bam-stat-grid" style="margin-bottom:28px;">

			<?php if ( ! empty( $stats['atencion_dist'] ) ) : ?>
			<div class="bam-card">
				<div class="bam-card-header">
					<h2 class="bam-card-title"><?php esc_html_e( 'Calidad de Atención', 'beforeaftermycare' ); ?></h2>
				</div>
				<div style="padding:16px 24px;">
					<?php foreach ( $stats['atencion_dist'] as $row ) :
						$pct = $stats['total'] > 0 ? round( ( $row->count / $stats['total'] ) * 100 ) : 0;
					?>
						<div style="margin-bottom:12px;">
							<div style="display:flex;justify-content:space-between;margin-bottom:4px;">
								<span style="font-size:.85rem;font-weight:600;"><?php echo esc_html( $row->atencion ); ?></span>
								<span style="font-size:.85rem;color:var(--bam-muted);"><?php echo esc_html( $row->count . ' (' . $pct . '%)' ); ?></span>
							</div>
							<div style="background:#e9ecef;border-radius:4px;height:8px;overflow:hidden;">
								<div style="background:#0077b6;height:100%;width:<?php echo esc_attr( $pct ); ?>%;border-radius:4px;"></div>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
			<?php endif; ?>

			<?php if ( ! empty( $stats['recomienda_dist'] ) ) : ?>
			<div class="bam-card">
				<div class="bam-card-header">
					<h2 class="bam-card-title"><?php esc_html_e( '¿Recomendarían el servicio?', 'beforeaftermycare' ); ?></h2>
				</div>
				<div style="padding:16px 24px;">
					<?php foreach ( $stats['recomienda_dist'] as $row ) :
						$pct = $stats['total'] > 0 ? round( ( $row->count / $stats['total'] ) * 100 ) : 0;
						$bar_color = 'Sí' === $row->recomendaria ? '#28a745' : ( 'No' === $row->recomendaria ? '#dc3545' : '#ffc107' );
					?>
						<div style="margin-bottom:12px;">
							<div style="display:flex;justify-content:space-between;margin-bottom:4px;">
								<span style="font-size:.85rem;font-weight:600;"><?php echo esc_html( $row->recomendaria ); ?></span>
								<span style="font-size:.85rem;color:var(--bam-muted);"><?php echo esc_html( $row->count . ' (' . $pct . '%)' ); ?></span>
							</div>
							<div style="background:#e9ecef;border-radius:4px;height:8px;overflow:hidden;">
								<div style="background:<?php echo esc_attr( $bar_color ); ?>;height:100%;width:<?php echo esc_attr( $pct ); ?>%;border-radius:4px;"></div>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
			<?php endif; ?>

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
								<th><?php esc_html_e( 'Calificación', 'beforeaftermycare' ); ?></th>
								<th><?php esc_html_e( 'Guía útil', 'beforeaftermycare' ); ?></th>
								<th><?php esc_html_e( 'Atención', 'beforeaftermycare' ); ?></th>
								<th><?php esc_html_e( 'Recomendaría', 'beforeaftermycare' ); ?></th>
								<th><?php esc_html_e( 'Aspectos a mejorar', 'beforeaftermycare' ); ?></th>
								<th><?php esc_html_e( 'Comentarios', 'beforeaftermycare' ); ?></th>
								<th><?php esc_html_e( 'Fecha', 'beforeaftermycare' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $responses as $r ) :
								$stars = str_repeat( '★', (int) $r->calificacion ) . str_repeat( '☆', 5 - (int) $r->calificacion );
							?>
								<tr>
									<td><?php echo esc_html( $r->id ); ?></td>
									<td>
										<?php echo esc_html( $r->patient_name ); ?>
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
									<td><?php echo esc_html( $r->patient_email ); ?></td>
									<td style="white-space:nowrap;color:#f4a825;" title="<?php echo esc_attr( $r->calificacion . '/5' ); ?>">
										<?php echo esc_html( $stars ); ?>
									</td>
									<td>
										<?php if ( $r->guia_util ) : ?>
											<span class="bam-badge bam-badge-success"><?php esc_html_e( 'Sí', 'beforeaftermycare' ); ?></span>
										<?php else : ?>
											<span class="bam-badge bam-badge-inactive"><?php esc_html_e( 'No', 'beforeaftermycare' ); ?></span>
										<?php endif; ?>
									</td>
									<td><?php echo $r->atencion ? esc_html( $r->atencion ) : '—'; ?></td>
									<td><?php echo $r->recomendaria ? esc_html( $r->recomendaria ) : '—'; ?></td>
									<td style="max-width:160px;font-size:.8rem;"><?php echo $r->aspectos_mejora ? esc_html( $r->aspectos_mejora ) : '—'; ?></td>
									<td style="max-width:200px;font-size:.8rem;"><?php echo $r->comentarios ? esc_html( $r->comentarios ) : '—'; ?></td>
									<td style="white-space:nowrap;"><?php echo esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $r->fecha_envio ) ) ); ?></td>
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
