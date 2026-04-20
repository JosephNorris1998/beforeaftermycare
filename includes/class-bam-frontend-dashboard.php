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

	// ── Template override ──────────────────────────────────────────────────────

	/**
	 * Serve our standalone full-HTML dashboard template for /dashboardplugin/.
	 *
	 * @param string $template
	 * @return string
	 */
	public function intercept_template( $template ) {
		if ( ! is_page( self::PAGE_SLUG ) ) {
			return $template;
		}

		if ( ! $this->can_access() ) {
			wp_redirect( wp_login_url( home_url( '/' . self::PAGE_SLUG . '/' ) ) );
			exit;
		}

		return BAM_PLUGIN_DIR . 'templates/page-dashboard.php';
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

	// ── Action handling ────────────────────────────────────────────────────────

	/**
	 * Process GET (delete / toggle) and POST (update) actions sent from the
	 * frontend dashboard.  Hooked to 'init' so redirects can still be issued.
	 */
	public function handle_actions() {
		$action = isset( $_GET['bam_front_action'] ) ? sanitize_key( $_GET['bam_front_action'] ) : '';

		// Handle POST update first.
		if ( isset( $_POST['bam_front_update_submit'] ) ) {
			$this->process_update();
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
				wp_redirect( add_query_arg( 'bam_msg', 'deleted', $dashboard_url ) );
				exit;

			case 'toggle':
				BAM_Database::toggle_status( $patient_id );
				wp_redirect( add_query_arg( 'bam_msg', 'toggled', $dashboard_url ) );
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
		wp_redirect( add_query_arg( array( 'bam_msg' => 'updated', 'bam_edit' => $patient_id ), $dashboard_url ) );
		exit;
	}
}
