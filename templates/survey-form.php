<?php
/**
 * Survey form template – rendered via the [bam_encuesta] shortcode.
 *
 * Available variables:
 *   $submitted     (bool)   – true when the form was just submitted successfully.
 *   $errors        (array)  – validation error messages keyed by field.
 *   $patient_name  (string) – pre-filled from logged-in user.
 *   $patient_email (string) – pre-filled from logged-in user.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="bam-survey-wrap">

	<?php if ( $submitted ) : ?>
		<div class="bam-notice bam-notice-success bam-survey-success" role="alert">
			<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
			<div>
				<strong><?php esc_html_e( '¡Gracias por tu opinión!', 'beforeaftermycare' ); ?></strong>
				<?php esc_html_e( 'Hemos recibido tu encuesta de satisfacción. Tu retroalimentación nos ayuda a mejorar.', 'beforeaftermycare' ); ?>
			</div>
		</div>
	<?php else : ?>

		<!-- Trigger button (centered, sky-blue gradient) -->
		<div class="bam-survey-trigger-wrap">
			<button type="button" class="bam-btn bam-survey-trigger" id="bam-survey-open">
				<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
				<?php esc_html_e( 'Completar encuesta de satisfacción', 'beforeaftermycare' ); ?>
			</button>
		</div>

		<!-- Modal overlay -->
		<div class="bam-survey-modal" id="bam-survey-modal" role="dialog" aria-modal="true" aria-labelledby="bam-survey-modal-title" hidden>
			<div class="bam-survey-modal-backdrop" id="bam-survey-backdrop"></div>
			<div class="bam-survey-modal-content">

				<div class="bam-survey-modal-header">
					<div class="bam-survey-modal-header-text">
						<h2 class="bam-survey-modal-title" id="bam-survey-modal-title"><?php esc_html_e( 'Encuesta de satisfacción del cliente', 'beforeaftermycare' ); ?></h2>
						<p class="bam-survey-modal-subtitle"><?php esc_html_e( 'Califica tu experiencia en cada momento de la atención. Gracias por ayudarnos a mejorar.', 'beforeaftermycare' ); ?></p>
					</div>
					<button type="button" class="bam-survey-modal-close" id="bam-survey-close" aria-label="<?php esc_attr_e( 'Cerrar encuesta', 'beforeaftermycare' ); ?>">
						<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
					</button>
				</div>

				<div class="bam-survey-modal-body">

					<?php if ( ! empty( $errors['general'] ) ) : ?>
						<div class="bam-notice bam-notice-error" role="alert">
							<?php echo esc_html( $errors['general'] ); ?>
						</div>
					<?php endif; ?>

					<form class="bam-survey-form" id="bam-survey-form" method="post" action="" novalidate>
						<?php wp_nonce_field( BAM_Survey::NONCE_ACTION, 'bam_survey_nonce' ); ?>

						<!-- Hidden patient fields (auto-filled from logged-in user) -->
						<input type="hidden" name="bam_patient_name"  value="<?php echo esc_attr( $patient_name ); ?>">
						<input type="hidden" name="bam_patient_email" value="<?php echo esc_attr( $patient_email ); ?>">

						<!-- Scale legend -->
						<div class="bam-survey-scale-info">
							<strong><?php esc_html_e( 'Escala:', 'beforeaftermycare' ); ?></strong>
							<?php esc_html_e( '1 = Muy insatisfecho, 2 = Insatisfecho, 3 = Neutral, 4 = Satisfecho, 5 = Muy satisfecho.', 'beforeaftermycare' ); ?>
						</div>

						<!-- Section 1: Calificación por momento -->
						<div class="bam-survey-section">
							<h3 class="bam-survey-section-label"><?php esc_html_e( 'Calificación por momento', 'beforeaftermycare' ); ?></h3>
							<div class="bam-rating-table-wrap">
								<table class="bam-rating-table" role="group" aria-label="<?php esc_attr_e( 'Calificación por momento de atención', 'beforeaftermycare' ); ?>">
									<thead>
										<tr>
											<th class="bam-rt-moment-col"><?php esc_html_e( 'Momento', 'beforeaftermycare' ); ?></th>
											<?php for ( $i = 1; $i <= 5; $i++ ) : ?>
												<th><?php echo esc_html( $i ); ?></th>
											<?php endfor; ?>
										</tr>
									</thead>
									<tbody>
										<?php
										$momentos = array(
											'momento_indicaciones'     => __( 'Indicaciones para la preparación', 'beforeaftermycare' ),
											'momento_admision'         => __( 'Admisión', 'beforeaftermycare' ),
											'momento_sala_preparacion' => __( 'Sala de preparación y recuperación', 'beforeaftermycare' ),
											'momento_salida_hospital'  => __( 'Salida del hospital', 'beforeaftermycare' ),
										);
										// phpcs:ignore WordPress.Security.NonceVerification
										foreach ( $momentos as $field => $label ) :
											$old_val = isset( $_POST[ 'bam_' . $field ] ) ? absint( $_POST[ 'bam_' . $field ] ) : 0;
										?>
											<tr>
												<td class="bam-rt-moment-col"><span><?php echo esc_html( $label ); ?></span></td>
												<?php for ( $i = 1; $i <= 5; $i++ ) : ?>
													<td>
														<label class="bam-rt-radio-label">
															<input
																class="bam-rt-radio"
																type="radio"
																name="bam_<?php echo esc_attr( $field ); ?>"
																value="<?php echo esc_attr( $i ); ?>"
																<?php checked( $old_val, $i ); ?>
																aria-label="<?php echo esc_attr( $label . ' – ' . $i ); ?>"
															>
															<span class="bam-rt-radio-dot" aria-hidden="true"></span>
														</label>
													</td>
												<?php endfor; ?>
											</tr>
										<?php endforeach; ?>
									</tbody>
								</table>
							</div>
						</div>

						<!-- Section 2: Valoración general del hospital -->
						<div class="bam-survey-section">
							<h3 class="bam-survey-section-label"><?php esc_html_e( 'Valoración general del hospital', 'beforeaftermycare' ); ?></h3>
							<div class="bam-rating-table-wrap">
								<table class="bam-rating-table" role="group" aria-label="<?php esc_attr_e( 'Valoración general del hospital', 'beforeaftermycare' ); ?>">
									<thead>
										<tr>
											<th class="bam-rt-moment-col"><?php esc_html_e( 'Aspecto', 'beforeaftermycare' ); ?></th>
											<?php for ( $i = 1; $i <= 5; $i++ ) : ?>
												<th><?php echo esc_html( $i ); ?></th>
											<?php endfor; ?>
										</tr>
									</thead>
									<tbody>
										<?php
										// phpcs:ignore WordPress.Security.NonceVerification
										$old_global = isset( $_POST['bam_satisfaccion_global'] ) ? absint( $_POST['bam_satisfaccion_global'] ) : 0;
										?>
										<tr>
											<td class="bam-rt-moment-col"><span><?php esc_html_e( 'Satisfacción global', 'beforeaftermycare' ); ?></span></td>
											<?php for ( $i = 1; $i <= 5; $i++ ) : ?>
												<td>
													<label class="bam-rt-radio-label">
														<input
															class="bam-rt-radio"
															type="radio"
															name="bam_satisfaccion_global"
															value="<?php echo esc_attr( $i ); ?>"
															<?php checked( $old_global, $i ); ?>
															aria-label="<?php echo esc_attr( __( 'Satisfacción global', 'beforeaftermycare' ) . ' – ' . $i ); ?>"
														>
														<span class="bam-rt-radio-dot" aria-hidden="true"></span>
													</label>
												</td>
											<?php endfor; ?>
										</tr>
									</tbody>
								</table>
							</div>
						</div>

						<!-- Section 3: Recomendaciones o comentarios -->
						<div class="bam-survey-section">
							<h3 class="bam-survey-section-label"><?php esc_html_e( 'Recomendaciones o comentarios', 'beforeaftermycare' ); ?></h3>
							<div class="bam-field">
								<textarea
									class="bam-input bam-textarea bam-survey-comments"
									id="bam_comentarios"
									name="bam_comentarios"
									rows="4"
									maxlength="600"
									placeholder="<?php esc_attr_e( '¿Qué podríamos mejorar? ¿Algo a destacar?', 'beforeaftermycare' ); ?>"
								><?php
								// phpcs:ignore WordPress.Security.NonceVerification
								echo esc_textarea( sanitize_textarea_field( wp_unslash( $_POST['bam_comentarios'] ?? '' ) ) );
								?></textarea>
								<div class="bam-survey-char-counter" id="bam-survey-char-counter" aria-live="polite">
									<span id="bam-char-count">0</span> / 600
								</div>
							</div>
						</div>

						<div class="bam-survey-modal-footer">
							<button type="button" class="bam-btn bam-btn-outline-teal" id="bam-survey-clear">
								<?php esc_html_e( 'Limpiar', 'beforeaftermycare' ); ?>
							</button>
							<button type="submit" name="bam_survey_submit" class="bam-btn bam-btn-teal">
								<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
								<?php esc_html_e( 'Enviar', 'beforeaftermycare' ); ?>
							</button>
						</div>

					</form>

				</div><!-- /.bam-survey-modal-body -->
			</div><!-- /.bam-survey-modal-content -->
		</div><!-- /.bam-survey-modal -->

	<?php endif; ?>

</div>
