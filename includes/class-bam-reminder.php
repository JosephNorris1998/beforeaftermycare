<?php
/**
 * BAM_Reminder – Schedules and sends procedure reminder emails.
 *
 * Uses WP-Cron to periodically check for upcoming patient procedures and
 * sends an elegant HTML reminder email from "Pacifica Salud".
 *
 * Option: bam_reminder_hours  (int)  – hours before procedure to send email (default 24).
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

	// ── Singleton ─────────────────────────────────────────────────────────────

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( self::CRON_HOOK, array( $this, 'check_reminders' ) );
		add_filter( 'wp_mail_from',      array( $this, 'mail_from' ) );
		add_filter( 'wp_mail_from_name', array( $this, 'mail_from_name' ) );
		add_shortcode( 'bam_recordatorio', array( $this, 'render_widget' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
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
	 * Enqueue frontend CSS when the [bam_recordatorio] shortcode is on the page.
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
	}

	// ── Shortcode: [bam_recordatorio] ─────────────────────────────────────────

	/**
	 * Render the appointment reminder widget for the logged-in patient.
	 *
	 * @return string HTML output.
	 */
	public function render_widget( $atts ) {
		// Guest users – show nothing.
		if ( ! is_user_logged_in() ) {
			return '<div class="bam-reminder-widget-login">'
				. '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>'
				. esc_html__( 'Inicia sesión para ver tu próxima cita.', 'beforeaftermycare' )
				. '</div>';
		}

		$patient = BAM_Database::get_patient_by_wp_user_id( get_current_user_id() );

		if ( ! $patient ) {
			return '';
		}

		$reminder_hours = (int) get_option( 'bam_reminder_hours', self::DEFAULT_HOURS );

		// Format procedure date.
		if ( ! empty( $patient->fecha_procedimiento ) ) {
			$ts        = strtotime( $patient->fecha_procedimiento );
			$fecha_fmt = date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $ts );
			$is_future = $ts > time();
		} else {
			$fecha_fmt = __( 'Fecha por confirmar', 'beforeaftermycare' );
			$is_future = false;
		}

		$procedimiento = ! empty( $patient->procedimiento ) ? $patient->procedimiento : __( 'Colonoscopia', 'beforeaftermycare' );

		// Reminder status badge.
		if ( $patient->recordatorio_enviado ) {
			$status_badge = '<span style="color:#15803d;font-weight:700;">&#10003; ' . esc_html__( 'Recordatorio enviado', 'beforeaftermycare' ) . '</span>';
		} elseif ( $is_future ) {
			$status_badge = '<span style="color:#0096c7;font-weight:700;">&#9201; ' . sprintf(
				/* translators: %d: hours */
				esc_html__( 'Se enviará %d h antes', 'beforeaftermycare' ),
				$reminder_hours
			) . '</span>';
		} else {
			$status_badge = '<span style="color:#9ca3af;">' . esc_html__( 'Pendiente de programar', 'beforeaftermycare' ) . '</span>';
		}

		ob_start();
		?>
<div class="bam-reminder-widget">
	<div class="bam-reminder-header">
		<div class="bam-reminder-icon" aria-hidden="true">&#128276;</div>
		<div class="bam-reminder-header-text">
			<h3><?php esc_html_e( 'Tu Próxima Cita', 'beforeaftermycare' ); ?></h3>
			<p><?php esc_html_e( 'Procedimiento médico programado', 'beforeaftermycare' ); ?></p>
		</div>
	</div>
	<div class="bam-reminder-body">
		<div class="bam-reminder-row">
			<span class="bam-reminder-label"><?php esc_html_e( 'Paciente', 'beforeaftermycare' ); ?></span>
			<span class="bam-reminder-value"><?php echo esc_html( $patient->nombre ); ?></span>
		</div>
		<div class="bam-reminder-row">
			<span class="bam-reminder-label"><?php esc_html_e( 'Procedimiento', 'beforeaftermycare' ); ?></span>
			<span class="bam-reminder-value"><?php echo esc_html( $procedimiento ); ?></span>
		</div>
		<div class="bam-reminder-row">
			<span class="bam-reminder-label"><?php esc_html_e( 'Fecha y hora', 'beforeaftermycare' ); ?></span>
			<span class="bam-reminder-value bam-reminder-date"><?php echo esc_html( $fecha_fmt ); ?></span>
		</div>
		<div class="bam-reminder-row">
			<span class="bam-reminder-label"><?php esc_html_e( 'Recordatorio', 'beforeaftermycare' ); ?></span>
			<span class="bam-reminder-value"><?php echo $status_badge; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
		</div>
	</div>
	<div class="bam-reminder-footer">
		<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
		<?php
		printf(
			/* translators: %d: hours */
			esc_html__( 'El correo de recordatorio se enviará %d horas antes de tu procedimiento.', 'beforeaftermycare' ),
			$reminder_hours
		);
		?>
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

	// ── Core logic ────────────────────────────────────────────────────────────

	/**
	 * Cron callback: check for upcoming procedures and send reminder emails.
	 */
	public function check_reminders() {
		$hours   = (int) get_option( 'bam_reminder_hours', self::DEFAULT_HOURS );
		$hours   = max( 0, $hours );
		$patients = BAM_Database::get_patients_for_reminder( $hours );

		foreach ( $patients as $patient ) {
			if ( $this->send_reminder_email( $patient ) ) {
				BAM_Database::mark_reminder_sent( (int) $patient->id );
			}
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

		// Format procedure date.
		if ( ! empty( $patient->fecha_procedimiento ) ) {
			$ts        = strtotime( $patient->fecha_procedimiento );
			$fecha_fmt = date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $ts );
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
}
