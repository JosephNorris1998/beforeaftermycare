<?php
/**
 * BAM_Frontend_Dashboard – Frontend dashboard page at /dashboardplugin/.
 *
 * Only the administrator (manage_options capability) can access this page.
 * It provides a full patient-management interface (view, search, edit, toggle
 * status, delete) without any Elementor header or footer.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class BAM_Frontend_Dashboard {

	/** @var BAM_Frontend_Dashboard|null */
	private static $instance = null;

	/** Slug of the auto-created dashboard page. */
	const PAGE_SLUG = 'dashboardplugin';

	/** Slug of the auto-created login page. */
	const PAGE_LOGIN_SLUG = 'loginplugin';

	// ── Singleton ─────────────────────────────────────────────────────────────

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_filter( 'template_include', array( $this, 'intercept_template' ) );
		add_action( 'init',             array( $this, 'handle_actions' ) );
		add_action( 'init',             array( $this, 'handle_login' ) );
	}

	// ── Page creation ──────────────────────────────────────────────────────────

	/**
	 * Create the dashboard page (called on plugin activation).
	 */
	public static function create_page() {
		if ( get_page_by_path( self::PAGE_SLUG ) ) {
			return; // Already exists.
		}

		wp_insert_post( array(
			'post_title'   => __( 'Dashboard', 'beforeaftermycare' ),
			'post_name'    => self::PAGE_SLUG,
			'post_status'  => 'publish',
			'post_type'    => 'page',
			'post_content' => '',
		) );
	}

	/**
	 * Create the login page (called on plugin activation).
	 */
	public static function create_login_page() {
		if ( get_page_by_path( self::PAGE_LOGIN_SLUG ) ) {
			return; // Already exists.
		}

		wp_insert_post( array(
			'post_title'   => __( 'Login – Guías Médicas', 'beforeaftermycare' ),
			'post_name'    => self::PAGE_LOGIN_SLUG,
			'post_status'  => 'publish',
			'post_type'    => 'page',
			'post_content' => '',
		) );
	}

	// ── Template override ──────────────────────────────────────────────────────

	/**
	 * Serve our standalone full-HTML templates for our pages.
	 *
	 * @param string $template
	 * @return string
	 */
	public function intercept_template( $template ) {
		if ( is_page( self::PAGE_LOGIN_SLUG ) ) {
			// Already logged in and has access → go directly to dashboard.
			if ( $this->can_access() ) {
				wp_redirect( home_url( '/' . self::PAGE_SLUG . '/' ) );
				exit;
			}
			return BAM_PLUGIN_DIR . 'templates/page-login.php';
		}

		if ( is_page( self::PAGE_SLUG ) ) {
			if ( ! $this->can_access() ) {
				wp_redirect( home_url( '/' . self::PAGE_LOGIN_SLUG . '/' ) );
				exit;
			}
			return BAM_PLUGIN_DIR . 'templates/page-dashboard.php';
		}

		return $template;
	}

	// ── Access control ─────────────────────────────────────────────────────────

	/**
	 * Check whether the current user may access the dashboard.
	 *
	 * @return bool
	 */
	private function can_access() {
		return is_user_logged_in() && current_user_can( 'manage_options' );
	}

	// ── Login handling ─────────────────────────────────────────────────────────

	/**
	 * Process the login form submitted from the custom login page.
	 */
	public function handle_login() {
		if ( ! isset( $_POST['bam_login_submit'] ) ) {
			return;
		}

		if (
			! isset( $_POST['bam_login_nonce'] ) ||
			! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['bam_login_nonce'] ) ), 'bam_login' )
		) {
			return;
		}

		$username = isset( $_POST['bam_username'] ) ? sanitize_user( wp_unslash( $_POST['bam_username'] ) ) : '';
		$password = isset( $_POST['bam_password'] ) ? $_POST['bam_password'] : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized,WordPress.Security.ValidatedSanitizedInput.MissingUnslash

		$user = wp_signon( array(
			'user_login'    => $username,
			'user_password' => $password,
			'remember'      => ! empty( $_POST['bam_remember'] ),
		), is_ssl() );

		$login_url = home_url( '/' . self::PAGE_LOGIN_SLUG . '/' );

		if ( is_wp_error( $user ) ) {
			wp_redirect( add_query_arg( 'bam_login_error', '1', $login_url ) );
			exit;
		}

		if ( ! user_can( $user, 'manage_options' ) ) {
			wp_logout();
			wp_redirect( add_query_arg( 'bam_login_error', 'noaccess', $login_url ) );
			exit;
		}

		wp_redirect( home_url( '/' . self::PAGE_SLUG . '/' ) );
		exit;
	}

	// ── Action handling ────────────────────────────────────────────────────────

	/**
	 * Process GET (delete / toggle) and POST (update / change_password) actions.
	 * Hooked to 'init' so redirects can still be issued.
	 */
	public function handle_actions() {
		$action = isset( $_GET['bam_front_action'] ) ? sanitize_key( $_GET['bam_front_action'] ) : '';

		// Handle POST update first.
		if ( isset( $_POST['bam_front_update_submit'] ) ) {
			$this->process_update();
			return;
		}

		// Handle password change POST.
		if ( isset( $_POST['bam_front_change_pass_submit'] ) ) {
			$this->process_password_change();
			return;
		}

		if ( empty( $action ) ) {
			return;
		}

		if ( ! $this->can_access() ) {
			return;
		}

		// Verify nonce for GET actions.
		if (
			! isset( $_GET['bam_nonce'] ) ||
			! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['bam_nonce'] ) ), 'bam_front_' . $action )
		) {
			wp_die( esc_html__( 'Acción no autorizada.', 'beforeaftermycare' ) );
		}

		$patient_id  = isset( $_GET['bam_id'] ) ? absint( $_GET['bam_id'] ) : 0;
		$dashboard_url = home_url( '/' . self::PAGE_SLUG . '/' );

		switch ( $action ) {
			case 'delete':
				$patient = BAM_Database::get_patient( $patient_id );
				if ( $patient && $patient->wp_user_id ) {
					require_once ABSPATH . 'wp-admin/includes/user.php';
					wp_delete_user( (int) $patient->wp_user_id );
				}
				BAM_Database::delete_patient( $patient_id );
				wp_redirect( add_query_arg( array( 'bam_section' => 'pacientes', 'bam_msg' => 'deleted' ), $dashboard_url ) );
				exit;

			case 'toggle':
				BAM_Database::toggle_status( $patient_id );
				wp_redirect( add_query_arg( array( 'bam_section' => 'pacientes', 'bam_msg' => 'toggled' ), $dashboard_url ) );
				exit;
		}
	}

	/**
	 * Process the patient-update form submitted from the edit view.
	 */
	private function process_update() {
		if ( ! $this->can_access() ) {
			return;
		}

		$patient_id = isset( $_POST['bam_id'] ) ? absint( $_POST['bam_id'] ) : 0;

		if (
			! isset( $_POST['bam_front_update_nonce'] ) ||
			! wp_verify_nonce(
				sanitize_text_field( wp_unslash( $_POST['bam_front_update_nonce'] ) ),
				'bam_front_update_' . $patient_id
			)
		) {
			wp_die( esc_html__( 'Acción no autorizada.', 'beforeaftermycare' ) );
		}

		$data = array(
			'nombre'        => sanitize_text_field( wp_unslash( $_POST['bam_nombre'] ?? '' ) ),
			'correo'        => sanitize_email( wp_unslash( $_POST['bam_correo'] ?? '' ) ),
			'telefono'      => sanitize_text_field( wp_unslash( $_POST['bam_telefono'] ?? '' ) ) ?: null,
			'guia_asignada' => sanitize_text_field( wp_unslash( $_POST['bam_guia_asignada'] ?? '' ) ) ?: null,
		);

		BAM_Database::update_patient( $patient_id, $data );

		// Keep the WP user in sync.
		$patient = BAM_Database::get_patient( $patient_id );
		if ( $patient && $patient->wp_user_id ) {
			wp_update_user( array(
				'ID'           => (int) $patient->wp_user_id,
				'display_name' => $data['nombre'],
				'first_name'   => $data['nombre'],
				'user_email'   => $data['correo'],
			) );
		}

		$dashboard_url = home_url( '/' . self::PAGE_SLUG . '/' );
		wp_redirect( add_query_arg( array( 'bam_msg' => 'updated', 'bam_edit' => $patient_id, 'bam_section' => 'pacientes' ), $dashboard_url ) );
		exit;
	}

	/**
	 * Process the password-change form submitted from the edit view.
	 */
	private function process_password_change() {
		if ( ! $this->can_access() ) {
			return;
		}

		$patient_id = isset( $_POST['bam_id'] ) ? absint( $_POST['bam_id'] ) : 0;

		if (
			! isset( $_POST['bam_front_change_pass_nonce'] ) ||
			! wp_verify_nonce(
				sanitize_text_field( wp_unslash( $_POST['bam_front_change_pass_nonce'] ) ),
				'bam_front_change_pass_' . $patient_id
			)
		) {
			wp_die( esc_html__( 'Acción no autorizada.', 'beforeaftermycare' ) );
		}

		$new_pass  = isset( $_POST['bam_new_password'] )  ? $_POST['bam_new_password']  : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized,WordPress.Security.ValidatedSanitizedInput.MissingUnslash
		$new_pass2 = isset( $_POST['bam_new_password2'] ) ? $_POST['bam_new_password2'] : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized,WordPress.Security.ValidatedSanitizedInput.MissingUnslash

		$dashboard_url = home_url( '/' . self::PAGE_SLUG . '/' );

		if ( empty( $new_pass ) || $new_pass !== $new_pass2 ) {
			wp_redirect( add_query_arg( array( 'bam_edit' => $patient_id, 'bam_section' => 'pacientes', 'bam_msg' => 'pass_mismatch' ), $dashboard_url ) );
			exit;
		}

		$patient = BAM_Database::get_patient( $patient_id );
		if ( $patient && $patient->wp_user_id ) {
			wp_set_password( $new_pass, (int) $patient->wp_user_id );
		}

		wp_redirect( add_query_arg( array( 'bam_edit' => $patient_id, 'bam_section' => 'pacientes', 'bam_msg' => 'pass_changed' ), $dashboard_url ) );
		exit;
	}
}
