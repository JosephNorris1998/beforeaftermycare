<?php
/**
 * BAM_Reminder – Schedules and sends procedure reminder emails.
 *
 * Uses WP-Cron to periodically check for upcoming patient procedures and
 * sends an elegant HTML reminder email from "Pacifica Salud".
 *
 * Each patient has their own recordatorio_horas column for per-patient timing.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class BAM_Reminder {

	/** @var BAM_Reminder|null */
	private static $instance = null;

	/** WP-Cron hook name. */
	const CRON_HOOK = 'bam_check_reminders';

	/** Default reminder lead time in hours. */
	const DEFAULT_HOURS = 24;

	/** Nonce action for the frontend reminder form. */
	const REMINDER_NONCE_ACTION = 'bam_recordatorio_save';

	// ── Singleton ─────────────────────────────────────────────────────────────

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/** WP-Cron hook for single patient reminders. */
	const CRON_HOOK_SINGLE = 'bam_send_single_reminder';

	/** WP-Cron hook for single reminder-record reminders. */
	const CRON_HOOK_REMINDER_RECORD = 'bam_send_reminder_record';

	private function __construct() {
		add_action( self::CRON_HOOK,               array( $this, 'check_reminders' ) );
		add_action( self::CRON_HOOK_SINGLE,         array( $this, 'send_single_reminder' ), 10, 1 );
		add_action( self::CRON_HOOK_REMINDER_RECORD, array( $this, 'send_single_reminder_record' ), 10, 1 );
		add_filter( 'wp_mail_from',      array( $this, 'mail_from' ) );
		add_filter( 'wp_mail_from_name', array( $this, 'mail_from_name' ) );
		add_shortcode( 'bam_recordatorio', array( $this, 'render_widget' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_action( 'init', array( $this, 'handle_reminder_form' ) );
	}

	// ── Cron schedule ─────────────────────────────────────────────────────────

	/**
	 * Register a custom 'bam_hourly' cron schedule if needed.
	 * Called via 'cron_schedules' filter.
	 *
	 * @param array $schedules Existing cron schedules.
	 * @return array
	 */
	public static function add_cron_schedule( array $schedules ) {
		if ( ! isset( $schedules['bam_hourly'] ) ) {
			$schedules['bam_hourly'] = array(
				'interval' => 3600,
				'display'  => __( 'Every hour (BAM)', 'beforeaftermycare' ),
			);
		}
		return $schedules;
	}

	/**
	 * Schedule the reminder cron event (called on plugin activation).
	 */
	public static function schedule_cron() {
		if ( ! wp_next_scheduled( self::CRON_HOOK ) ) {
			wp_schedule_event( time(), 'bam_hourly', self::CRON_HOOK );
		}
	}

	/**
	 * Remove the scheduled cron event (called on plugin deactivation).
	 */
	public static function unschedule_cron() {
		$timestamp = wp_next_scheduled( self::CRON_HOOK );
		if ( $timestamp ) {
			wp_unschedule_event( $timestamp, self::CRON_HOOK );
		}
	}

	// ── Mail sender filters ────────────────────────────────────────────────────

	/**
	 * Enqueue frontend CSS/JS when the [bam_recordatorio] shortcode is on the page.
	 */
	public function enqueue_assets() {
		global $post;
		if ( ! is_a( $post, 'WP_Post' ) || ! has_shortcode( $post->post_content, 'bam_recordatorio' ) ) {
			return;
		}
		$ver = get_option( 'bam_asset_version', BAM_VERSION );
		wp_enqueue_style(
			'bam-frontend',
			BAM_PLUGIN_URL . 'assets/css/frontend.css',
			array(),
			$ver
		);

		wp_enqueue_script(
			'bam-frontend',
			BAM_PLUGIN_URL . 'assets/js/frontend.js',
			array(),
			$ver,
			true
		);
	}

	// ── Frontend form handler ─────────────────────────────────────────────────

	/**
	 * Process POST submission of the [bam_recordatorio] appointment form.
	 * Hooked to 'init' so headers can still be sent.
	 * Works for any visitor (logged-in or guest).
	 * Saves a reminder record to bam_reminder_records (NOT patients table).
	 */
	public function handle_reminder_form() {
		if ( ! isset( $_POST['bam_recordatorio_submit'] ) ) {
			return;
		}

		// Verify nonce.
		if ( ! isset( $_POST['bam_recordatorio_nonce'] ) ||
		     ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['bam_recordatorio_nonce'] ) ), self::REMINDER_NONCE_ACTION ) ) {
			wp_die( esc_html__( 'Token de seguridad inválido. Por favor recarga la página.', 'beforeaftermycare' ) );
		}

		// Sanitize inputs.
		$nombre         = sanitize_text_field( wp_unslash( $_POST['bam_nombre_form']    ?? '' ) );
		$correo         = sanitize_email( wp_unslash( $_POST['bam_correo_form']         ?? '' ) );
		$procedimiento  = sanitize_text_field( wp_unslash( $_POST['bam_procedimiento']  ?? '' ) );
		$fecha_datetime = sanitize_text_field( wp_unslash( $_POST['bam_fecha_datetime'] ?? '' ) );
		$confirmacion   = isset( $_POST['bam_confirmacion'] ) ? (bool) $_POST['bam_confirmacion'] : false;
		// Page URL passed from the hidden form field for reliable redirect.
		$page_url       = isset( $_POST['bam_page_url'] ) ? esc_url_raw( wp_unslash( $_POST['bam_page_url'] ) ) : '';

		// Use the email as the transient key for errors (works for guests too).
		$uid          = get_current_user_id(); // 0 for guests.
		$error_suffix = $uid ? (string) $uid : bin2hex( random_bytes( 8 ) );
		$error_key    = 'bam_reminder_errors_' . $error_suffix;

		// Validate.
		$errors = array();
		$ts     = 0;

		if ( ! $confirmacion ) {
			$errors[] = __( 'Debe confirmar que ha verificado la fecha con el departamento de admisión.', 'beforeaftermycare' );
		}

		if ( empty( $nombre ) ) {
			$errors[] = __( 'El nombre completo es requerido.', 'beforeaftermycare' );
		}

		if ( empty( $correo ) || ! is_email( $correo ) ) {
			$errors[] = __( 'Ingresa un correo electrónico válido.', 'beforeaftermycare' );
		}

		if ( empty( $fecha_datetime ) ) {
			$errors[] = __( 'La fecha y hora del procedimiento son requeridas.', 'beforeaftermycare' );
		} else {
			// Parse YYYY-MM-DDTHH:MM (native datetime-local format) in WP timezone.
			$wp_tz = wp_timezone();
			$dt    = DateTime::createFromFormat( 'Y-m-d\TH:i', $fecha_datetime, $wp_tz );
			if ( ! $dt ) {
				$errors[] = __( 'El formato de fecha u hora no es válido.', 'beforeaftermycare' );
			} else {
				$ts = $dt->getTimestamp();
				if ( $ts <= time() ) {
					$errors[] = __( 'La fecha del procedimiento debe ser una fecha futura.', 'beforeaftermycare' );
					$ts       = 0;
				}
			}
		}

		$base_url = $page_url ?: wp_get_referer() ?: home_url();

		if ( ! empty( $errors ) ) {
			set_transient( $error_key, $errors, 120 );
			if ( $uid ) {
				set_transient( 'bam_reminder_errors_' . $uid, $errors, 120 );
			}
			wp_safe_redirect( $base_url );
			exit;
		}

		$fecha_mysql   = gmdate( 'Y-m-d H:i:s', $ts );
		$procedimiento = $procedimiento ?: __( 'Colonoscopia', 'beforeaftermycare' );

		// Save to reminder records table (not patients table).
		$new_id = BAM_Database::insert_reminder( array(
			'nombre'               => $nombre,
			'correo'               => $correo,
			'procedimiento'        => $procedimiento,
			'fecha_procedimiento'  => $fecha_mysql,
			'recordatorio_horas'   => self::DEFAULT_HOURS,
			'recordatorio_enviado' => 0,
		) );

		$reminder = $new_id ? BAM_Database::get_reminder( $new_id ) : null;

		// Send immediate confirmation email.
		if ( $reminder ) {
			$this->send_confirmation_email( $reminder );
		}

		// Schedule a single-event reminder at exactly 24 hours before the procedure.
		if ( $new_id && $ts > 0 ) {
			$reminder_time = $ts - ( self::DEFAULT_HOURS * HOUR_IN_SECONDS );
			wp_clear_scheduled_hook( self::CRON_HOOK_REMINDER_RECORD, array( (int) $new_id ) );
			if ( $reminder_time > time() ) {
				wp_schedule_single_event( $reminder_time, self::CRON_HOOK_REMINDER_RECORD, array( (int) $new_id ) );
			}
		}

		$redirect = add_query_arg( 'bam_reminder_saved', '1', $base_url );
		wp_safe_redirect( $redirect );
		exit;
	}

	// ── Shortcode: [bam_recordatorio] ─────────────────────────────────────────

	/**
	 * Render the appointment scheduling form.
	 * Works for all visitors – no pre-existing patient record required.
	 *
	 * @return string HTML output.
	 */
	public function render_widget( $atts ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$saved = isset( $_GET['bam_reminder_saved'] ) && '1' === $_GET['bam_reminder_saved'];

		// Retrieve errors for the current session (logged-in uid or guest token).
		$uid        = get_current_user_id();
		$error_key  = $uid ? 'bam_reminder_errors_' . $uid : null;
		$errors     = array();
		if ( $error_key ) {
			$raw = get_transient( $error_key );
			if ( is_array( $raw ) && ! empty( $raw ) ) {
				$errors = $raw;
				delete_transient( $error_key );
			}
		}

		// Pre-fill from existing patient record when the user is logged in.
		$patient       = $uid ? BAM_Database::get_patient_by_wp_user_id( $uid ) : null;
		$prefill_name  = $patient ? $patient->nombre : ( $uid ? wp_get_current_user()->display_name : '' );
		$prefill_email = $patient ? $patient->correo  : ( $uid ? wp_get_current_user()->user_email    : '' );

		// Pre-fill date/time field (YYYY-MM-DDTHH:MM for datetime-local input).
		$prefill_datetime = '';
		$fecha_fmt        = '';
		$is_future        = false;

		if ( $patient && ! empty( $patient->fecha_procedimiento ) ) {
			$ts = strtotime( $patient->fecha_procedimiento . ' UTC' );
			// Guard against invalid timestamps.
			if ( $ts && $ts > 0 ) {
				$prefill_datetime = wp_date( 'Y-m-d\TH:i', $ts );
				$fecha_fmt        = wp_date( 'j \d\e F \d\e Y \a \l\a\s H:i', $ts );
				$is_future        = $ts > time();
			}
		}

		$prefill_proc  = $patient && ! empty( $patient->procedimiento ) ? $patient->procedimiento : 'Colonoscopia';

		// Status badge for existing appointment.
		$status_badge = '';
		if ( $patient && ! empty( $patient->fecha_procedimiento ) ) {
			if ( $patient->recordatorio_enviado ) {
				$status_badge = '<span class="bam-badge bam-badge-sent">&#10003; ' . esc_html__( 'Recordatorio enviado', 'beforeaftermycare' ) . '</span>';
			} elseif ( $is_future ) {
				$status_badge = '<span class="bam-badge bam-badge-pending">&#9201; ' . esc_html__( 'Se enviará 24 h antes', 'beforeaftermycare' ) . '</span>';
			}
		}

		$form_id = 'bam-reminder-form-' . esc_attr( $uid ?: 'guest' );

		ob_start();
		?>
<div class="bam-reminder-widget">

	<!-- Header -->
	<div class="bam-reminder-header">
		<div class="bam-reminder-icon" aria-hidden="true">&#128276;</div>
		<div class="bam-reminder-header-text">
			<h3><?php esc_html_e( 'Programar Recordatorio de Cita', 'beforeaftermycare' ); ?></h3>
			<p><?php esc_html_e( 'Ingresa la fecha de tu procedimiento médico', 'beforeaftermycare' ); ?></p>
		</div>
	</div>

	<?php if ( $saved ) : ?>
	<!-- Success notice – shown above form after successful registration -->
	<div class="bam-reminder-notice bam-reminder-notice-success bam-reminder-notice-standalone" role="status">
		<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
		<span><?php esc_html_e( '¡Envío Exitoso! Tu recordatorio ha sido programado. Recibirás un correo de confirmación y un recordatorio automático 24 horas antes de tu procedimiento.', 'beforeaftermycare' ); ?></span>
	</div>
	<?php endif; ?>

	<?php if ( ! empty( $errors ) ) : ?>
	<!-- Error notice -->
	<div class="bam-reminder-notice bam-reminder-notice-error" role="alert">
		<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
		<span>
		<?php foreach ( $errors as $err ) : ?>
			<?php echo esc_html( $err ); ?><br>
		<?php endforeach; ?>
		</span>
	</div>
	<?php endif; ?>

	<?php if ( $patient && ! empty( $patient->fecha_procedimiento ) && $status_badge ) : ?>
	<!-- Existing appointment status summary -->
	<div class="bam-reminder-body">
		<div class="bam-reminder-row">
			<span class="bam-reminder-label"><?php esc_html_e( 'Cita actual', 'beforeaftermycare' ); ?></span>
			<span class="bam-reminder-value bam-reminder-date"><?php echo esc_html( $fecha_fmt ); ?></span>
		</div>
		<div class="bam-reminder-row">
			<span class="bam-reminder-label"><?php esc_html_e( 'Recordatorio', 'beforeaftermycare' ); ?></span>
			<span class="bam-reminder-value"><?php echo $status_badge; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
		</div>
	</div>
	<?php endif; ?>

	<!-- Appointment scheduling form -->
	<div class="bam-reminder-form-wrap">

		<!-- Important notice -->
		<div class="bam-reminder-notice bam-reminder-notice-warning">
			<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
			<span>
				<strong><?php esc_html_e( 'Aviso importante:', 'beforeaftermycare' ); ?></strong>
				<?php esc_html_e( ' Antes de registrar su cita, debe confirmar la fecha de su admisión y procedimiento con el departamento de admisión del hospital. Al usar este formulario, asumimos que ya ha realizado esta confirmación.', 'beforeaftermycare' ); ?>
			</span>
		</div>

		<form class="bam-reminder-form" id="<?php echo esc_attr( $form_id ); ?>" method="post" action="">
			<?php wp_nonce_field( self::REMINDER_NONCE_ACTION, 'bam_recordatorio_nonce' ); ?>
			<input type="hidden" name="bam_page_url" value="<?php echo esc_url( get_permalink() ?: home_url() ); ?>">

			<!-- Confirmation checkbox -->
			<div class="bam-reminder-field bam-field-checkbox">
				<label class="bam-checkbox-label">
					<input
						type="checkbox"
						name="bam_confirmacion"
						value="1"
						required
					>
					<span><?php esc_html_e( 'Confirmo que he verificado la fecha con el departamento de admisión del hospital.', 'beforeaftermycare' ); ?></span>
				</label>
			</div>

			<!-- Two-column row: Nombre + Correo -->
			<div class="bam-reminder-form-row">
				<div class="bam-reminder-field">
					<label for="bam_nombre_form_<?php echo esc_attr( $uid ?: 'guest' ); ?>">
						<?php esc_html_e( 'Nombre completo:', 'beforeaftermycare' ); ?>
						<span class="bam-required" aria-hidden="true">*</span>
					</label>
					<input
						class="bam-input"
						type="text"
						id="bam_nombre_form_<?php echo esc_attr( $uid ?: 'guest' ); ?>"
						name="bam_nombre_form"
						value="<?php echo esc_attr( $saved ? '' : $prefill_name ); ?>"
						placeholder="<?php esc_attr_e( 'Ej: Juan Pérez', 'beforeaftermycare' ); ?>"
						required
					>
				</div>

				<div class="bam-reminder-field">
					<label for="bam_correo_form_<?php echo esc_attr( $uid ?: 'guest' ); ?>">
						<?php esc_html_e( 'Correo electrónico:', 'beforeaftermycare' ); ?>
						<span class="bam-required" aria-hidden="true">*</span>
					</label>
					<input
						class="bam-input"
						type="email"
						id="bam_correo_form_<?php echo esc_attr( $uid ?: 'guest' ); ?>"
						name="bam_correo_form"
						value="<?php echo esc_attr( $saved ? '' : $prefill_email ); ?>"
						placeholder="correo@ejemplo.com"
						required
					>
				</div>
			</div>

			<!-- Two-column row: Fecha + Procedimiento -->
			<div class="bam-reminder-form-row">
				<div class="bam-reminder-field">
					<label for="bam_fecha_datetime_<?php echo esc_attr( $uid ?: 'guest' ); ?>">
						<?php esc_html_e( 'Fecha y hora:', 'beforeaftermycare' ); ?>
						<span class="bam-required" aria-hidden="true">*</span>
					</label>
					<input
						class="bam-input bam-datetime-local"
						type="datetime-local"
						id="bam_fecha_datetime_<?php echo esc_attr( $uid ?: 'guest' ); ?>"
						name="bam_fecha_datetime"
						value="<?php echo esc_attr( $saved ? '' : $prefill_datetime ); ?>"
						min="<?php echo esc_attr( wp_date( 'Y-m-d\TH:i' ) ); ?>"
						required
					>
				</div>

				<div class="bam-reminder-field">
					<label for="bam_procedimiento_<?php echo esc_attr( $uid ?: 'guest' ); ?>">
						<?php esc_html_e( 'Procedimiento:', 'beforeaftermycare' ); ?>
					</label>
					<select
						class="bam-input"
						id="bam_procedimiento_<?php echo esc_attr( $uid ?: 'guest' ); ?>"
						name="bam_procedimiento"
					>
						<option value="Colonoscopia" <?php selected( $saved ? 'Colonoscopia' : $prefill_proc, 'Colonoscopia' ); ?>>
							<?php esc_html_e( 'Colonoscopia', 'beforeaftermycare' ); ?>
						</option>
					</select>
				</div>
			</div>

			<button type="submit" name="bam_recordatorio_submit" value="1" class="bam-btn bam-btn-primary bam-btn-block">
				<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
				<?php esc_html_e( 'Registrar cita', 'beforeaftermycare' ); ?>
			</button>
		</form>
	</div>

	<div class="bam-reminder-footer">
		<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
		<?php esc_html_e( 'Al registrar tu cita recibirás un correo de confirmación y un recordatorio automático 24 horas antes de tu procedimiento.', 'beforeaftermycare' ); ?>
	</div>

</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Override the From email address only while sending reminders.
	 */
	public function mail_from( $email ) {
		return $email; // Default – overridden per-send via headers.
	}

	/**
	 * Override the From name only while sending reminders.
	 */
	public function mail_from_name( $name ) {
		return $name; // Default – overridden per-send via headers.
	}

	// ── Cron callback ─────────────────────────────────────────────────────────

	/**
	 * Single-event cron callback: send the reminder for one specific patient.
	 * Scheduled at appointment registration time for exactly 24 h before the procedure.
	 *
	 * @param int $patient_id Patient ID.
	 */
	public function send_single_reminder( $patient_id ) {
		$patient = BAM_Database::get_patient( (int) $patient_id );
		if ( ! $patient || $patient->recordatorio_enviado ) {
			return;
		}
		if ( $this->send_reminder_email( $patient ) ) {
			BAM_Database::mark_reminder_sent( (int) $patient->id );
		}
	}

	/**
	 * Recurring cron callback: check for upcoming procedures and send reminder emails.
	 * Acts as a safety net for reminders that may have been missed by the single event.
	 */
	public function check_reminders() {
		// Process patient-based reminders (admin-created patients with procedure dates).
		$patients = BAM_Database::get_patients_for_reminder();
		foreach ( $patients as $patient ) {
			if ( $this->send_reminder_email( $patient ) ) {
				BAM_Database::mark_reminder_sent( (int) $patient->id );
			}
		}

		// Process reminder records (frontend form submissions).
		$reminders = BAM_Database::get_reminders_for_sending();
		foreach ( $reminders as $reminder ) {
			if ( $this->send_reminder_email( $reminder ) ) {
				BAM_Database::mark_reminder_record_sent( (int) $reminder->id );
			}
		}
	}

	/**
	 * Single-event cron callback: send the reminder for one specific reminder record.
	 *
	 * @param int $reminder_id Reminder record ID.
	 */
	public function send_single_reminder_record( $reminder_id ) {
		$reminder = BAM_Database::get_reminder( (int) $reminder_id );
		if ( ! $reminder || $reminder->recordatorio_enviado ) {
			return;
		}
		if ( $this->send_reminder_email( $reminder ) ) {
			BAM_Database::mark_reminder_record_sent( (int) $reminder->id );
		}
	}

	/**
	 * Send the reminder email to a patient.
	 *
	 * @param object $patient Patient DB row.
	 * @return bool Whether wp_mail succeeded.
	 */
	public function send_reminder_email( $patient ) {
		if ( empty( $patient->correo ) ) {
			return false;
		}

		$procedimiento = ! empty( $patient->procedimiento ) ? $patient->procedimiento : __( 'Colonoscopia', 'beforeaftermycare' );

		$subject = sprintf(
			/* translators: %s – procedure name */
			__( 'Recordatorio de %s', 'beforeaftermycare' ),
			$procedimiento
		);

		$headers = array(
			'Content-Type: text/html; charset=UTF-8',
			'From: Pacifica Salud <noreply@pacificasalud.beforeaftermycare.com>',
		);

		$body = $this->build_email_html( $patient, $procedimiento );

		return wp_mail( $patient->correo, $subject, $body, $headers );
	}

	/**
	 * Build the HTML body for the reminder email.
	 *
	 * @param object $patient       Patient DB row.
	 * @param string $procedimiento Procedure name.
	 * @return string HTML string.
	 */
	private function build_email_html( $patient, $procedimiento ) {
		$nombre    = esc_html( $patient->nombre );
		$proc_html = esc_html( $procedimiento );

		// Format procedure date (stored as UTC in DB).
		if ( ! empty( $patient->fecha_procedimiento ) ) {
			$ts = strtotime( $patient->fecha_procedimiento . ' UTC' );
			if ( $ts && $ts > 0 ) {
				$fecha_fmt = wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $ts );
			} else {
				$fecha_fmt = __( 'Fecha por confirmar', 'beforeaftermycare' );
			}
		} else {
			$fecha_fmt = __( 'Fecha por confirmar', 'beforeaftermycare' );
		}
		$fecha_html = esc_html( $fecha_fmt );

		$site_url = esc_url( home_url() );

		ob_start();
		?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo esc_html( sprintf( __( 'Recordatorio de %s', 'beforeaftermycare' ), $procedimiento ) ); ?></title>
</head>
<body style="margin:0;padding:0;background-color:#f0f4f8;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;">

<table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f0f4f8;padding:40px 20px;">
  <tr>
    <td align="center">

      <!-- Outer card -->
      <table width="600" cellpadding="0" cellspacing="0" border="0" style="max-width:600px;width:100%;border-radius:18px;overflow:hidden;box-shadow:0 8px 40px rgba(0,0,0,0.12);">

        <!-- ── HEADER ── -->
        <tr>
          <td style="background:linear-gradient(135deg,#0d2137 0%,#0077b6 60%,#00b4d8 100%);padding:40px 40px 32px;text-align:center;">
            <div style="display:inline-block;background:rgba(255,255,255,0.15);border-radius:50%;width:72px;height:72px;line-height:72px;text-align:center;margin-bottom:18px;font-size:32px;">
              &#10084;
            </div>
            <h1 style="color:#ffffff;margin:0 0 6px;font-size:26px;font-weight:700;letter-spacing:-0.5px;">Pacifica Salud</h1>
            <p style="color:rgba(255,255,255,0.75);margin:0;font-size:14px;letter-spacing:0.03em;">Guías Médicas Personalizadas</p>
          </td>
        </tr>

        <!-- ── ALERT BANNER ── -->
        <tr>
          <td style="background:#0096c7;padding:14px 40px;text-align:center;">
            <p style="color:#ffffff;margin:0;font-size:15px;font-weight:700;letter-spacing:0.02em;">
              &#128276;&nbsp; Recordatorio de Procedimiento Médico
            </p>
          </td>
        </tr>

        <!-- ── BODY ── -->
        <tr>
          <td style="background:#ffffff;padding:40px 40px 32px;">

            <p style="color:#374151;font-size:16px;margin:0 0 20px;line-height:1.6;">
              Estimado/a <strong style="color:#0077b6;"><?php echo $nombre; ?></strong>,
            </p>

            <p style="color:#374151;font-size:15px;line-height:1.75;margin:0 0 32px;">
              Le recordamos que tiene programado un <strong><?php echo $proc_html; ?></strong>. Por favor, asegúrese de seguir todas las instrucciones de preparación indicadas por su equipo médico.
            </p>

            <!-- Appointment details card -->
            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#f0f9ff;border:1px solid #bae6fd;border-radius:14px;margin-bottom:28px;overflow:hidden;">
              <tr>
                <td style="padding:24px 28px;">
                  <p style="color:#0077b6;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.1em;margin:0 0 16px;padding-bottom:12px;border-bottom:1px solid #bae6fd;">
                    &#128203;&nbsp; Detalles de su Cita
                  </p>

                  <!-- Paciente row -->
                  <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:12px;">
                    <tr>
                      <td style="padding:10px 0;border-bottom:1px solid #e0f2fe;">
                        <span style="display:block;color:#64748b;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.07em;margin-bottom:3px;">Paciente</span>
                        <span style="color:#0f172a;font-size:15px;font-weight:600;"><?php echo $nombre; ?></span>
                      </td>
                    </tr>
                    <!-- Procedimiento row -->
                    <tr>
                      <td style="padding:10px 0;border-bottom:1px solid #e0f2fe;">
                        <span style="display:block;color:#64748b;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.07em;margin-bottom:3px;">Procedimiento</span>
                        <span style="color:#0f172a;font-size:15px;font-weight:600;"><?php echo $proc_html; ?></span>
                      </td>
                    </tr>
                    <!-- Fecha row -->
                    <tr>
                      <td style="padding:10px 0;">
                        <span style="display:block;color:#64748b;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.07em;margin-bottom:3px;">Fecha y Hora</span>
                        <span style="color:#0077b6;font-size:16px;font-weight:700;"><?php echo $fecha_html; ?></span>
                      </td>
                    </tr>
                  </table>

                </td>
              </tr>
            </table>

            <!-- Important notice -->
            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#fffbeb;border:1px solid #fde68a;border-radius:10px;margin-bottom:28px;">
              <tr>
                <td style="padding:16px 20px;">
                  <p style="color:#92400e;font-size:14px;margin:0;line-height:1.7;">
                    <strong>&#9888;&#65039; Aviso importante:</strong> Antes de su procedimiento, confirme cualquier instrucción de preparación con el departamento de admisión del hospital. En caso de dudas, comuníquese con su médico tratante.
                  </p>
                </td>
              </tr>
            </table>

            <p style="color:#64748b;font-size:14px;line-height:1.75;margin:0 0 8px;">
              Si tiene alguna pregunta o necesita reprogramar su cita, no dude en contactar a nuestro equipo de atención al paciente.
            </p>
            <p style="color:#64748b;font-size:14px;line-height:1.75;margin:0;">
              Le deseamos una pronta recuperación. &#128153;
            </p>

          </td>
        </tr>

        <!-- ── FOOTER ── -->
        <tr>
          <td style="background:#f8fafc;padding:24px 40px;border-top:1px solid #e2e8f0;text-align:center;">
            <p style="color:#0077b6;font-size:16px;font-weight:700;margin:0 0 4px;">Pacifica Salud</p>
            <p style="color:#94a3b8;font-size:12px;margin:0 0 12px;">
              Guías Médicas Personalizadas &middot;
              <a href="<?php echo $site_url; ?>" style="color:#0077b6;text-decoration:none;"><?php echo esc_html( parse_url( home_url(), PHP_URL_HOST ) ); ?></a>
            </p>
            <p style="color:#cbd5e1;font-size:11px;margin:0;">
              Este es un mensaje automático, por favor no responda directamente a este correo.
            </p>
          </td>
        </tr>

      </table>
      <!-- /Outer card -->

    </td>
  </tr>
</table>

</body>
</html>
		<?php
		return ob_get_clean();
	}

	// ── Confirmation email ─────────────────────────────────────────────────────

	/**
	 * Send an immediate confirmation email to a patient after they schedule their appointment.
	 *
	 * @param object $patient Patient DB row.
	 * @return bool Whether wp_mail succeeded.
	 */
	public function send_confirmation_email( $patient ) {
		if ( empty( $patient->correo ) ) {
			return false;
		}

		$procedimiento = ! empty( $patient->procedimiento ) ? $patient->procedimiento : __( 'Colonoscopia', 'beforeaftermycare' );

		$subject = sprintf(
			/* translators: %s – procedure name */
			__( '¡Cita Confirmada! – %s', 'beforeaftermycare' ),
			$procedimiento
		);

		$headers = array(
			'Content-Type: text/html; charset=UTF-8',
			'From: Pacifica Salud <noreply@pacificasalud.beforeaftermycare.com>',
		);

		$body = $this->build_confirmation_email_html( $patient, $procedimiento );

		return wp_mail( $patient->correo, $subject, $body, $headers );
	}

	/**
	 * Build the HTML body for the confirmation email.
	 *
	 * @param object $patient       Patient DB row.
	 * @param string $procedimiento Procedure name.
	 * @return string HTML string.
	 */
	private function build_confirmation_email_html( $patient, $procedimiento ) {
		$nombre    = esc_html( $patient->nombre );
		$proc_html = esc_html( $procedimiento );

		$recordatorio_horas = isset( $patient->recordatorio_horas ) ? (int) $patient->recordatorio_horas : self::DEFAULT_HOURS;

		// Format procedure date (stored as UTC in DB).
		if ( ! empty( $patient->fecha_procedimiento ) ) {
			$ts = strtotime( $patient->fecha_procedimiento . ' UTC' );
			if ( $ts && $ts > 0 ) {
				$fecha_fmt = wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $ts );
			} else {
				$fecha_fmt = __( 'Fecha por confirmar', 'beforeaftermycare' );
			}
		} else {
			$fecha_fmt = __( 'Fecha por confirmar', 'beforeaftermycare' );
		}
		$fecha_html = esc_html( $fecha_fmt );

		$site_url = esc_url( home_url() );

		ob_start();
		?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo esc_html( sprintf( __( 'Cita Confirmada – %s', 'beforeaftermycare' ), $procedimiento ) ); ?></title>
</head>
<body style="margin:0;padding:0;background-color:#f0f4f8;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;">

<table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f0f4f8;padding:40px 20px;">
  <tr>
    <td align="center">

      <!-- Outer card -->
      <table width="600" cellpadding="0" cellspacing="0" border="0" style="max-width:600px;width:100%;border-radius:18px;overflow:hidden;box-shadow:0 8px 40px rgba(0,0,0,0.12);">

        <!-- ── HEADER ── -->
        <tr>
          <td style="background:linear-gradient(135deg,#065f46 0%,#059669 60%,#34d399 100%);padding:40px 40px 32px;text-align:center;">
            <div style="display:inline-block;background:rgba(255,255,255,0.18);border-radius:50%;width:72px;height:72px;line-height:72px;text-align:center;margin-bottom:18px;font-size:36px;">
              &#10004;
            </div>
            <h1 style="color:#ffffff;margin:0 0 6px;font-size:26px;font-weight:700;letter-spacing:-0.5px;">Pacifica Salud</h1>
            <p style="color:rgba(255,255,255,0.8);margin:0;font-size:14px;letter-spacing:0.03em;">Guías Médicas Personalizadas</p>
          </td>
        </tr>

        <!-- ── ALERT BANNER ── -->
        <tr>
          <td style="background:#059669;padding:14px 40px;text-align:center;">
            <p style="color:#ffffff;margin:0;font-size:15px;font-weight:700;letter-spacing:0.02em;">
              &#128203;&nbsp; ¡Tu Cita ha sido Confirmada!
            </p>
          </td>
        </tr>

        <!-- ── BODY ── -->
        <tr>
          <td style="background:#ffffff;padding:40px 40px 32px;">

            <p style="color:#374151;font-size:16px;margin:0 0 20px;line-height:1.6;">
              Estimado/a <strong style="color:#059669;"><?php echo $nombre; ?></strong>,
            </p>

            <p style="color:#374151;font-size:15px;line-height:1.75;margin:0 0 32px;">
              Tu cita para <strong><?php echo $proc_html; ?></strong> ha sido registrada exitosamente en nuestro sistema. A continuación encontrarás el detalle de tu cita.
            </p>

            <!-- Appointment details card -->
            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:14px;margin-bottom:28px;overflow:hidden;">
              <tr>
                <td style="padding:24px 28px;">
                  <p style="color:#059669;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.1em;margin:0 0 16px;padding-bottom:12px;border-bottom:1px solid #bbf7d0;">
                    &#128203;&nbsp; Detalles de su Cita
                  </p>

                  <table width="100%" cellpadding="0" cellspacing="0" border="0">
                    <tr>
                      <td style="padding:10px 0;border-bottom:1px solid #dcfce7;">
                        <span style="display:block;color:#64748b;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.07em;margin-bottom:3px;">Paciente</span>
                        <span style="color:#0f172a;font-size:15px;font-weight:600;"><?php echo $nombre; ?></span>
                      </td>
                    </tr>
                    <tr>
                      <td style="padding:10px 0;border-bottom:1px solid #dcfce7;">
                        <span style="display:block;color:#64748b;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.07em;margin-bottom:3px;">Procedimiento</span>
                        <span style="color:#0f172a;font-size:15px;font-weight:600;"><?php echo $proc_html; ?></span>
                      </td>
                    </tr>
                    <tr>
                      <td style="padding:10px 0;border-bottom:1px solid #dcfce7;">
                        <span style="display:block;color:#64748b;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.07em;margin-bottom:3px;">Fecha y Hora</span>
                        <span style="color:#059669;font-size:16px;font-weight:700;"><?php echo $fecha_html; ?></span>
                      </td>
                    </tr>
                    <tr>
                      <td style="padding:10px 0;">
                        <span style="display:block;color:#64748b;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.07em;margin-bottom:3px;">Recordatorio automático</span>
                        <span style="color:#059669;font-size:14px;font-weight:600;">
                          <?php
                          echo esc_html( sprintf(
                              /* translators: %d: hours */
                              __( '%d horas antes del procedimiento', 'beforeaftermycare' ),
                              $recordatorio_horas
                          ) );
                          ?>
                        </span>
                      </td>
                    </tr>
                  </table>

                </td>
              </tr>
            </table>

            <!-- Reminder notice -->
            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:10px;margin-bottom:28px;">
              <tr>
                <td style="padding:16px 20px;">
                  <p style="color:#1e40af;font-size:14px;margin:0;line-height:1.7;">
                    <strong>&#128276; Recordatorio programado:</strong> Recibirás automáticamente un correo recordatorio <strong><?php echo esc_html( $recordatorio_horas ); ?> horas antes</strong> de tu procedimiento.
                  </p>
                </td>
              </tr>
            </table>

            <!-- Important notice -->
            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#fffbeb;border:1px solid #fde68a;border-radius:10px;margin-bottom:28px;">
              <tr>
                <td style="padding:16px 20px;">
                  <p style="color:#92400e;font-size:14px;margin:0;line-height:1.7;">
                    <strong>&#9888;&#65039; Aviso importante:</strong> Antes de su procedimiento, confirme cualquier instrucción de preparación con el departamento de admisión del hospital. En caso de dudas, comuníquese con su médico tratante.
                  </p>
                </td>
              </tr>
            </table>

            <p style="color:#64748b;font-size:14px;line-height:1.75;margin:0 0 8px;">
              Si necesita modificar la fecha de su cita, puede actualizarla desde su perfil de paciente en nuestra plataforma.
            </p>
            <p style="color:#64748b;font-size:14px;line-height:1.75;margin:0;">
              ¡Gracias por confiar en Pacifica Salud! &#128153;
            </p>

          </td>
        </tr>

        <!-- ── FOOTER ── -->
        <tr>
          <td style="background:#f8fafc;padding:24px 40px;border-top:1px solid #e2e8f0;text-align:center;">
            <p style="color:#059669;font-size:16px;font-weight:700;margin:0 0 4px;">Pacifica Salud</p>
            <p style="color:#94a3b8;font-size:12px;margin:0 0 12px;">
              Guías Médicas Personalizadas &middot;
              <a href="<?php echo $site_url; ?>" style="color:#059669;text-decoration:none;"><?php echo esc_html( parse_url( home_url(), PHP_URL_HOST ) ); ?></a>
            </p>
            <p style="color:#cbd5e1;font-size:11px;margin:0;">
              Este es un mensaje automático, por favor no responda directamente a este correo.
            </p>
          </td>
        </tr>

      </table>
      <!-- /Outer card -->

    </td>
  </tr>
</table>

</body>
</html>
		<?php
		return ob_get_clean();
	}
}
