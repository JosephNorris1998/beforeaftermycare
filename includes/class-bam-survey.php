<?php
/**
 * BAM_Survey – Patient satisfaction survey shortcode, form processing,
 * email notifications, and statistics.
 *
 * Usage: add shortcode [bam_encuesta] to any page.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class BAM_Survey {

	/** @var BAM_Survey|null Singleton instance. */
	private static $instance = null;

	/** Form nonce action. */
	const NONCE_ACTION = 'bam_encuesta_nonce';

	// ── Singleton ─────────────────────────────────────────────────────────────

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_shortcode( 'bam_encuesta', array( $this, 'render_form' ) );
		add_action( 'init', array( $this, 'handle_submission' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	// ── Assets ────────────────────────────────────────────────────────────────

	public function enqueue_assets() {
		global $post;
		if ( ! is_a( $post, 'WP_Post' ) || ! has_shortcode( $post->post_content, 'bam_encuesta' ) ) {
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

	// ── Shortcode ─────────────────────────────────────────────────────────────

	/**
	 * Render the satisfaction survey form.
	 *
	 * @return string HTML output.
	 */
	public function render_form( $atts ) {
		$atts = shortcode_atts( array(), $atts, 'bam_encuesta' );

		ob_start();

		$this->maybe_start_session();
		$submitted = ! empty( $_SESSION['bam_survey_success'] );
		if ( $submitted ) {
			unset( $_SESSION['bam_survey_success'] );
		}
		$errors = $this->get_errors();

		// Pre-fill patient name/email if logged in.
		$patient_name  = '';
		$patient_email = '';
		if ( is_user_logged_in() ) {
			$wp_user       = wp_get_current_user();
			$patient_name  = $wp_user->display_name;
			$patient_email = $wp_user->user_email;
		}

		include BAM_PLUGIN_DIR . 'templates/survey-form.php';
		return ob_get_clean();
	}

	// ── Form Processing ───────────────────────────────────────────────────────

	/**
	 * Process POST submission (hooked to init so headers can still be sent).
	 */
	public function handle_submission() {
		if ( ! isset( $_POST['bam_survey_submit'] ) ) {
			return;
		}

		// Verify nonce.
		if ( ! isset( $_POST['bam_survey_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['bam_survey_nonce'] ) ), self::NONCE_ACTION ) ) {
			$this->store_errors( array( 'general' => __( 'Token de seguridad inválido. Por favor recarga la página.', 'beforeaftermycare' ) ) );
			return;
		}

		// Collect & sanitize.
		$patient_name    = sanitize_text_field( wp_unslash( $_POST['bam_patient_name']    ?? '' ) );
		$patient_email   = sanitize_email( wp_unslash( $_POST['bam_patient_email']        ?? '' ) );
		$calificacion    = absint( $_POST['bam_calificacion']                              ?? 0 );
		$guia_util       = isset( $_POST['bam_guia_util'] ) ? 1 : 0;
		$atencion        = sanitize_text_field( wp_unslash( $_POST['bam_atencion']        ?? '' ) );
		$recomendaria    = sanitize_text_field( wp_unslash( $_POST['bam_recomendaria']    ?? '' ) );
		$comentarios     = sanitize_textarea_field( wp_unslash( $_POST['bam_comentarios'] ?? '' ) );

		// Aspectos de mejora – array of checkboxes.
		$aspectos_raw = isset( $_POST['bam_aspectos'] ) && is_array( $_POST['bam_aspectos'] )
			? array_map( 'sanitize_text_field', wp_unslash( $_POST['bam_aspectos'] ) )
			: array();
		$aspectos_mejora = implode( ', ', $aspectos_raw );

		// Validate required fields.
		$errors = array();

		if ( empty( $patient_name ) ) {
			$errors['patient_name'] = __( 'El nombre es requerido.', 'beforeaftermycare' );
		}

		if ( empty( $patient_email ) || ! is_email( $patient_email ) ) {
			$errors['patient_email'] = __( 'Ingresa un correo electrónico válido.', 'beforeaftermycare' );
		}

		if ( $calificacion < 1 || $calificacion > 5 ) {
			$errors['calificacion'] = __( 'Por favor selecciona una calificación del 1 al 5.', 'beforeaftermycare' );
		}

		if ( ! empty( $errors ) ) {
			$this->store_errors( $errors );
			return;
		}

		// Resolve patient_id if logged in.
		$patient_id = null;
		if ( is_user_logged_in() ) {
			$patient = BAM_Database::get_patient_by_wp_user_id( get_current_user_id() );
			if ( $patient ) {
				$patient_id = (int) $patient->id;
			}
		}

		// Save to DB.
		$insert_id = BAM_Database::insert_survey_response( array(
			'patient_id'      => $patient_id,
			'patient_name'    => $patient_name,
			'patient_email'   => $patient_email,
			'calificacion'    => $calificacion,
			'guia_util'       => $guia_util,
			'atencion'        => $atencion ?: null,
			'recomendaria'    => $recomendaria ?: null,
			'aspectos_mejora' => $aspectos_mejora ?: null,
			'comentarios'     => $comentarios ?: null,
		) );

		// Send email notification.
		if ( $insert_id ) {
			$this->send_notification( $insert_id, array(
				'patient_name'    => $patient_name,
				'patient_email'   => $patient_email,
				'calificacion'    => $calificacion,
				'guia_util'       => $guia_util,
				'atencion'        => $atencion,
				'recomendaria'    => $recomendaria,
				'aspectos_mejora' => $aspectos_mejora,
				'comentarios'     => $comentarios,
			) );
		}

		// Redirect with success using session flag.
		$this->maybe_start_session();
		$_SESSION['bam_survey_success'] = true;
		$referer = wp_get_referer();
		$success_url = $referer ?: home_url( '/' );
		wp_redirect( $success_url );
		exit;
	}

	// ── Email notification ────────────────────────────────────────────────────

	/**
	 * Send email notification with survey data.
	 *
	 * @param int   $response_id
	 * @param array $data
	 */
	private function send_notification( $response_id, array $data ) {
		$to      = get_option( 'bam_survey_email', get_option( 'admin_email' ) );
		$subject = sprintf(
			/* translators: %d: response ID */
			__( '[Before After My Care] Nueva encuesta de satisfacción #%d', 'beforeaftermycare' ),
			$response_id
		);

		$rating_stars = str_repeat( '★', (int) $data['calificacion'] ) . str_repeat( '☆', 5 - (int) $data['calificacion'] );

		$body  = sprintf( __( 'Se ha recibido una nueva respuesta de encuesta de satisfacción.', 'beforeaftermycare' ) ) . "\n\n";
		$body .= sprintf( __( 'Paciente: %s', 'beforeaftermycare' ), $data['patient_name'] ) . "\n";
		$body .= sprintf( __( 'Correo: %s', 'beforeaftermycare' ), $data['patient_email'] ) . "\n";
		$body .= sprintf( __( 'Calificación: %s (%d/5)', 'beforeaftermycare' ), $rating_stars, $data['calificacion'] ) . "\n";
		$body .= sprintf( __( 'Guía fue útil: %s', 'beforeaftermycare' ), $data['guia_util'] ? __( 'Sí', 'beforeaftermycare' ) : __( 'No', 'beforeaftermycare' ) ) . "\n";

		if ( ! empty( $data['atencion'] ) ) {
			$body .= sprintf( __( 'Atención recibida: %s', 'beforeaftermycare' ), $data['atencion'] ) . "\n";
		}

		if ( ! empty( $data['recomendaria'] ) ) {
			$body .= sprintf( __( 'Recomendaría el servicio: %s', 'beforeaftermycare' ), $data['recomendaria'] ) . "\n";
		}

		if ( ! empty( $data['aspectos_mejora'] ) ) {
			$body .= sprintf( __( 'Aspectos a mejorar: %s', 'beforeaftermycare' ), $data['aspectos_mejora'] ) . "\n";
		}

		if ( ! empty( $data['comentarios'] ) ) {
			$body .= "\n" . __( 'Comentarios:', 'beforeaftermycare' ) . "\n" . $data['comentarios'] . "\n";
		}

		$body .= "\n" . sprintf(
			/* translators: %s: URL */
			__( 'Ver todos los resultados: %s', 'beforeaftermycare' ),
			admin_url( 'admin.php?page=bam-survey' )
		);

		wp_mail( $to, $subject, $body );
	}

	// ── Error helpers ─────────────────────────────────────────────────────────

	private function store_errors( array $errors ) {
		$this->maybe_start_session();
		$_SESSION['bam_survey_errors'] = $errors;

		$referer = wp_get_referer();
		if ( $referer ) {
			wp_redirect( $referer );
			exit;
		}
	}

	public function get_errors() {
		$this->maybe_start_session();
		$errors = $_SESSION['bam_survey_errors'] ?? array();
		unset( $_SESSION['bam_survey_errors'] );
		return $errors;
	}

	private function maybe_start_session() {
		if ( ! headers_sent() && session_status() === PHP_SESSION_NONE ) {
			session_start();
		}
	}
}
