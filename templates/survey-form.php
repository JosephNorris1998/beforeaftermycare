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

// Helper to retrieve old POST value.
$old = function( $key ) {
	// phpcs:ignore WordPress.Security.NonceVerification
	return isset( $_POST[ 'bam_' . $key ] ) ? esc_attr( sanitize_text_field( wp_unslash( $_POST[ 'bam_' . $key ] ) ) ) : '';
};
?>

<div class="bam-survey-wrap">

	<?php if ( $submitted ) : ?>
		<div class="bam-notice bam-notice-success bam-survey-success" role="alert">
			<strong><?php esc_html_e( '¡Gracias por tu opinión!', 'beforeaftermycare' ); ?></strong>
			<?php esc_html_e( 'Hemos recibido tu encuesta de satisfacción. Tu retroalimentación nos ayuda a mejorar.', 'beforeaftermycare' ); ?>
		</div>
	<?php else : ?>

		<?php if ( ! empty( $errors['general'] ) ) : ?>
			<div class="bam-notice bam-notice-error" role="alert">
				<?php echo esc_html( $errors['general'] ); ?>
			</div>
		<?php endif; ?>

		<form class="bam-survey-form" method="post" action="" novalidate>
			<?php wp_nonce_field( BAM_Survey::NONCE_ACTION, 'bam_survey_nonce' ); ?>

			<!-- Nombre -->
			<div class="bam-field <?php echo ! empty( $errors['patient_name'] ) ? 'bam-field-error' : ''; ?>">
				<label class="bam-label" for="bam_patient_name">
					<?php esc_html_e( 'Nombre completo', 'beforeaftermycare' ); ?>
					<span class="bam-required" aria-hidden="true">*</span>
				</label>
				<input
					class="bam-input"
					type="text"
					id="bam_patient_name"
					name="bam_patient_name"
					value="<?php echo esc_attr( $patient_name ?: $old( 'patient_name' ) ); ?>"
					placeholder="<?php esc_attr_e( 'Tu nombre completo', 'beforeaftermycare' ); ?>"
					required
				>
				<?php if ( ! empty( $errors['patient_name'] ) ) : ?>
					<span class="bam-field-message"><?php echo esc_html( $errors['patient_name'] ); ?></span>
				<?php endif; ?>
			</div>

			<!-- Correo -->
			<div class="bam-field <?php echo ! empty( $errors['patient_email'] ) ? 'bam-field-error' : ''; ?>">
				<label class="bam-label" for="bam_patient_email">
					<?php esc_html_e( 'Correo electrónico', 'beforeaftermycare' ); ?>
					<span class="bam-required" aria-hidden="true">*</span>
				</label>
				<input
					class="bam-input"
					type="email"
					id="bam_patient_email"
					name="bam_patient_email"
					value="<?php echo esc_attr( $patient_email ?: $old( 'patient_email' ) ); ?>"
					placeholder="<?php esc_attr_e( 'ejemplo@correo.com', 'beforeaftermycare' ); ?>"
					required
				>
				<?php if ( ! empty( $errors['patient_email'] ) ) : ?>
					<span class="bam-field-message"><?php echo esc_html( $errors['patient_email'] ); ?></span>
				<?php endif; ?>
			</div>

			<!-- Calificación general (1-5) -->
			<div class="bam-field bam-survey-rating-field <?php echo ! empty( $errors['calificacion'] ) ? 'bam-field-error' : ''; ?>">
				<label class="bam-label">
					<?php esc_html_e( '¿Cómo calificarías tu experiencia general?', 'beforeaftermycare' ); ?>
					<span class="bam-required" aria-hidden="true">*</span>
				</label>
				<div class="bam-star-rating" role="group" aria-label="<?php esc_attr_e( 'Calificación de 1 a 5 estrellas', 'beforeaftermycare' ); ?>">
					<?php for ( $i = 1; $i <= 5; $i++ ) : ?>
						<label class="bam-star-label" for="bam_cal_<?php echo esc_attr( $i ); ?>">
							<input
								type="radio"
								id="bam_cal_<?php echo esc_attr( $i ); ?>"
								name="bam_calificacion"
								value="<?php echo esc_attr( $i ); ?>"
								<?php checked( $old( 'calificacion' ), (string) $i ); ?>
							>
							<svg class="bam-star" width="32" height="32" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
								<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
							</svg>
							<span class="screen-reader-text"><?php echo esc_html( $i ); ?></span>
						</label>
					<?php endfor; ?>
				</div>
				<?php if ( ! empty( $errors['calificacion'] ) ) : ?>
					<span class="bam-field-message"><?php echo esc_html( $errors['calificacion'] ); ?></span>
				<?php endif; ?>
			</div>

			<!-- ¿La guía fue útil? -->
			<div class="bam-field">
				<label class="bam-label"><?php esc_html_e( '¿Fue clara y útil la información de tu guía médica?', 'beforeaftermycare' ); ?></label>
				<label class="bam-checkbox-label">
					<input type="checkbox" name="bam_guia_util" value="1" <?php checked( $old( 'guia_util' ), '1' ); ?>>
					<?php esc_html_e( 'Sí, la guía fue útil', 'beforeaftermycare' ); ?>
				</label>
			</div>

			<!-- Calidad de atención -->
			<div class="bam-field">
				<label class="bam-label" for="bam_atencion"><?php esc_html_e( '¿Cómo calificarías la atención recibida?', 'beforeaftermycare' ); ?></label>
				<select class="bam-input" id="bam_atencion" name="bam_atencion">
					<option value=""><?php esc_html_e( '— Selecciona una opción —', 'beforeaftermycare' ); ?></option>
					<option value="Excelente" <?php selected( $old( 'atencion' ), 'Excelente' ); ?>><?php esc_html_e( 'Excelente', 'beforeaftermycare' ); ?></option>
					<option value="Buena" <?php selected( $old( 'atencion' ), 'Buena' ); ?>><?php esc_html_e( 'Buena', 'beforeaftermycare' ); ?></option>
					<option value="Regular" <?php selected( $old( 'atencion' ), 'Regular' ); ?>><?php esc_html_e( 'Regular', 'beforeaftermycare' ); ?></option>
					<option value="Mala" <?php selected( $old( 'atencion' ), 'Mala' ); ?>><?php esc_html_e( 'Mala', 'beforeaftermycare' ); ?></option>
				</select>
			</div>

			<!-- ¿Recomendaría el servicio? -->
			<div class="bam-field">
				<label class="bam-label"><?php esc_html_e( '¿Recomendarías este servicio a un familiar o amigo?', 'beforeaftermycare' ); ?></label>
				<div class="bam-radio-group">
					<label class="bam-radio-label">
						<input type="radio" name="bam_recomendaria" value="Sí" <?php checked( $old( 'recomendaria' ), 'Sí' ); ?>>
						<?php esc_html_e( 'Sí', 'beforeaftermycare' ); ?>
					</label>
					<label class="bam-radio-label">
						<input type="radio" name="bam_recomendaria" value="No" <?php checked( $old( 'recomendaria' ), 'No' ); ?>>
						<?php esc_html_e( 'No', 'beforeaftermycare' ); ?>
					</label>
					<label class="bam-radio-label">
						<input type="radio" name="bam_recomendaria" value="Tal vez" <?php checked( $old( 'recomendaria' ), 'Tal vez' ); ?>>
						<?php esc_html_e( 'Tal vez', 'beforeaftermycare' ); ?>
					</label>
				</div>
			</div>

			<!-- Aspectos a mejorar -->
			<div class="bam-field">
				<label class="bam-label"><?php esc_html_e( '¿Qué aspectos podríamos mejorar? (puedes elegir varios)', 'beforeaftermycare' ); ?></label>
				<?php
				$aspectos_options = array(
					'Información'       => __( 'Información de la guía', 'beforeaftermycare' ),
					'Atención'          => __( 'Calidad de atención', 'beforeaftermycare' ),
					'Tiempo de espera'  => __( 'Tiempo de espera', 'beforeaftermycare' ),
					'Instalaciones'     => __( 'Instalaciones', 'beforeaftermycare' ),
					'Comunicación'      => __( 'Comunicación', 'beforeaftermycare' ),
					'Otro'              => __( 'Otro', 'beforeaftermycare' ),
				);
				// phpcs:ignore WordPress.Security.NonceVerification
				$old_aspectos = isset( $_POST['bam_aspectos'] ) && is_array( $_POST['bam_aspectos'] )
					? array_map( 'sanitize_text_field', wp_unslash( $_POST['bam_aspectos'] ) )
					: array();
				foreach ( $aspectos_options as $value => $label ) :
				?>
					<label class="bam-checkbox-label">
						<input
							type="checkbox"
							name="bam_aspectos[]"
							value="<?php echo esc_attr( $value ); ?>"
							<?php checked( in_array( $value, $old_aspectos, true ) ); ?>
						>
						<?php echo esc_html( $label ); ?>
					</label>
				<?php endforeach; ?>
			</div>

			<!-- Comentarios adicionales -->
			<div class="bam-field">
				<label class="bam-label" for="bam_comentarios">
					<?php esc_html_e( 'Comentarios adicionales', 'beforeaftermycare' ); ?>
					<span class="bam-optional">(<?php esc_html_e( 'opcional', 'beforeaftermycare' ); ?>)</span>
				</label>
				<textarea
					class="bam-input bam-textarea"
					id="bam_comentarios"
					name="bam_comentarios"
					rows="4"
					placeholder="<?php esc_attr_e( 'Comparte cualquier comentario, sugerencia o experiencia que desees comunicarnos…', 'beforeaftermycare' ); ?>"
				><?php
				// phpcs:ignore WordPress.Security.NonceVerification
				echo esc_textarea( sanitize_textarea_field( wp_unslash( $_POST['bam_comentarios'] ?? '' ) ) );
				?></textarea>
			</div>

			<button type="submit" name="bam_survey_submit" class="bam-btn bam-btn-primary bam-btn-block">
				<?php esc_html_e( 'Enviar encuesta', 'beforeaftermycare' ); ?>
			</button>

		</form>

	<?php endif; ?>

</div>
