<?php
/**
 * BAM_Admin – Custom admin dashboard for the Before After My Care plugin.
 * Provides a dedicated top-level admin menu with its own look & feel,
 * independent from default WordPress admin navigation.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class BAM_Admin {

	/** @var BAM_Admin|null */
	private static $instance = null;

	/** Menu slug. */
	const MENU_SLUG = 'bam-dashboard';

	/** @var string[] Registered admin page hooks (set during register_menu). */
	private $page_hooks = array();

	// ── Singleton ─────────────────────────────────────────────────────────────

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'admin_menu',            array( $this, 'register_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_action( 'admin_init',            array( $this, 'handle_actions' ) );
	}

	// ── Menu ──────────────────────────────────────────────────────────────────

	public function register_menu() {
		// Top-level menu
		$hook = add_menu_page(
			__( 'BAM – Guías Médicas', 'beforeaftermycare' ),
			__( 'Guías Médicas', 'beforeaftermycare' ),
			'manage_options',
			self::MENU_SLUG,
			array( $this, 'page_dashboard' ),
			'dashicons-heart',
			3
		);
		$this->page_hooks[] = $hook;

		// Sub-pages
		$hook = add_submenu_page(
			self::MENU_SLUG,
			__( 'Dashboard', 'beforeaftermycare' ),
			__( 'Dashboard', 'beforeaftermycare' ),
			'manage_options',
			self::MENU_SLUG,
			array( $this, 'page_dashboard' )
		);
		$this->page_hooks[] = $hook;

		$hook = add_submenu_page(
			self::MENU_SLUG,
			__( 'Pacientes', 'beforeaftermycare' ),
			__( 'Pacientes', 'beforeaftermycare' ),
			'manage_options',
			'bam-patients',
			array( $this, 'page_patients' )
		);
		$this->page_hooks[] = $hook;

		$hook = add_submenu_page(
			self::MENU_SLUG,
			__( 'Detalle de Paciente', 'beforeaftermycare' ),
			'',    // hidden from menu
			'manage_options',
			'bam-patient-detail',
			array( $this, 'page_patient_detail' )
		);
		$this->page_hooks[] = $hook;
	}

	// ── Assets ────────────────────────────────────────────────────────────────

	public function enqueue_assets( $hook ) {
		// Only load on our admin pages (hooks captured during register_menu)
		if ( ! in_array( $hook, $this->page_hooks, true ) ) {
			return;
		}

		wp_enqueue_style(
			'bam-admin',
			BAM_PLUGIN_URL . 'assets/css/admin.css',
			array(),
			BAM_VERSION
		);

		wp_enqueue_script(
			'bam-admin',
			BAM_PLUGIN_URL . 'assets/js/admin.js',
			array( 'jquery' ),
			BAM_VERSION,
			true
		);

		wp_localize_script( 'bam-admin', 'bamAdmin', array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'bam_admin_nonce' ),
			'i18n'    => array(
				'confirmDelete'   => __( '¿Eliminar este paciente? Esta acción no se puede deshacer.', 'beforeaftermycare' ),
				'confirmToggle'   => __( '¿Cambiar el estado de este paciente?', 'beforeaftermycare' ),
			),
		) );
	}

	// ── Action Handling ───────────────────────────────────────────────────────

	/**
	 * Handle admin POST/GET actions (delete, toggle status, update patient).
	 */
	public function handle_actions() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$action = isset( $_GET['bam_action'] ) ? sanitize_key( $_GET['bam_action'] ) : '';

		if ( empty( $action ) ) {
			return;
		}

		if ( ! isset( $_GET['bam_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['bam_nonce'] ) ), 'bam_action_' . $action ) ) {
			wp_die( esc_html__( 'Acción no autorizada.', 'beforeaftermycare' ) );
		}

		$patient_id = isset( $_GET['bam_id'] ) ? absint( $_GET['bam_id'] ) : 0;

		switch ( $action ) {
			case 'delete':
				$patient = BAM_Database::get_patient( $patient_id );
				if ( $patient && $patient->wp_user_id ) {
					require_once ABSPATH . 'wp-admin/includes/user.php';
					wp_delete_user( (int) $patient->wp_user_id );
				}
				BAM_Database::delete_patient( $patient_id );
				wp_redirect( add_query_arg( array( 'page' => 'bam-patients', 'bam_msg' => 'deleted' ), admin_url( 'admin.php' ) ) );
				exit;

			case 'toggle':
				BAM_Database::toggle_status( $patient_id );
				wp_redirect( add_query_arg( array( 'page' => 'bam-patients', 'bam_msg' => 'toggled' ), admin_url( 'admin.php' ) ) );
				exit;
		}

		// Handle patient update form
		if ( 'update' === $action && isset( $_POST['bam_update_submit'] ) ) {
			if ( ! isset( $_POST['bam_update_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['bam_update_nonce'] ) ), 'bam_update_patient_' . $patient_id ) ) {
				wp_die( esc_html__( 'Acción no autorizada.', 'beforeaftermycare' ) );
			}

			$data = array(
				'nombre'        => sanitize_text_field( wp_unslash( $_POST['bam_nombre']        ?? '' ) ),
				'correo'        => sanitize_email( wp_unslash( $_POST['bam_correo']              ?? '' ) ),
				'telefono'      => sanitize_text_field( wp_unslash( $_POST['bam_telefono']       ?? '' ) ) ?: null,
				'guia_asignada' => sanitize_text_field( wp_unslash( $_POST['bam_guia_asignada']  ?? '' ) ) ?: null,
			);

			BAM_Database::update_patient( $patient_id, $data );

			// Sync WP user
			$patient = BAM_Database::get_patient( $patient_id );
			if ( $patient && $patient->wp_user_id ) {
				wp_update_user( array(
					'ID'           => (int) $patient->wp_user_id,
					'display_name' => $data['nombre'],
					'first_name'   => $data['nombre'],
					'user_email'   => $data['correo'],
				) );
			}

			wp_redirect( add_query_arg( array( 'page' => 'bam-patient-detail', 'bam_id' => $patient_id, 'bam_msg' => 'updated' ), admin_url( 'admin.php' ) ) );
			exit;
		}
	}

	// ── Pages ─────────────────────────────────────────────────────────────────

	/** Dashboard overview page. */
	public function page_dashboard() {
		$total   = BAM_Database::count_total();
		$active  = BAM_Database::count_active();
		$recent  = BAM_Database::count_recent();
		$result  = BAM_Database::get_patients( 5, 1 );
		$latest  = $result['items'];
		include BAM_PLUGIN_DIR . 'templates/admin-dashboard.php';
	}

	/** Patients list page. */
	public function page_patients() {
		$per_page  = 20;
		$page      = isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1;
		$search    = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';
		$result    = BAM_Database::get_patients( $per_page, $page, $search );
		$patients  = $result['items'];
		$total     = $result['total'];
		$num_pages = (int) ceil( $total / $per_page );
		include BAM_PLUGIN_DIR . 'templates/admin-patients.php';
	}

	/** Patient detail / edit page. */
	public function page_patient_detail() {
		$patient_id = isset( $_GET['bam_id'] ) ? absint( $_GET['bam_id'] ) : 0;
		$patient    = $patient_id ? BAM_Database::get_patient( $patient_id ) : null;

		if ( ! $patient ) {
			wp_die( esc_html__( 'Paciente no encontrado.', 'beforeaftermycare' ) );
		}

		include BAM_PLUGIN_DIR . 'templates/admin-patient-detail.php';
	}
}
