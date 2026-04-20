<?php
/**
 * BAM_Registration – Manages the patient registration shortcode, form processing,
 * and automatic redirect after successful registration.
 *
 * Usage: add shortcode [bam_registro] to any page (e.g. the /registro/ page).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class BAM_Registration {

	/** @var BAM_Registration|null Singleton instance. */
	private static $instance = null;

	/** Form nonce action. */
	const NONCE_ACTION = 'bam_registro_nonce';

	// ── Singleton ─────────────────────────────────────────────────────────────

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_shortcode( 'bam_registro', array( $this, 'render_form' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_action( 'init', array( $this, 'handle_registration' ) );
	}

	// ── Assets ────────────────────────────────────────────────────────────────

	public function enqueue_assets() {
		if ( ! $this->is_registration_page() ) {
			return;
		}

		wp_enqueue_style(
			'bam-frontend',
			BAM_PLUGIN_URL . 'assets/css/frontend.css',
			array(),
			BAM_VERSION
		);

		wp_enqueue_script(
			'bam-frontend',
			BAM_PLUGIN_URL . 'assets/js/frontend.js',
			array(),
			BAM_VERSION,
			true
		);
	}

	// ── Shortcode ─────────────────────────────────────────────────────────────

	/**
	 * Render the registration form (or success/error messages).
	 *
	 * @return string HTML output.
	 */
	public function render_form( $atts ) {
		$atts = shortcode_atts( array(), $atts, 'bam_registro' );

		// If the user is already logged-in redirect them.
		if ( is_user_logged_in() ) {
			wp_redirect( BAM_REDIRECT_URL );
			exit;
		}

		ob_start();
		$errors  = $this->get_form_errors();
		$success = isset( $_GET['bam_registered'] ) && '1' === $_GET['bam_registered'];
		include BAM_PLUGIN_DIR . 'templates/registration-form.php';
		return ob_get_clean();
	}

	// ── Form Processing ───────────────────────────────────────────────────────

	/**
	 * Process POST submission (hooked to init so headers can still be sent).
	 */
	public function handle_registration() {
		if ( ! isset( $_POST['bam_register_submit'] ) ) {
			return;
		}

		// Verify nonce
		if ( ! isset( $_POST['bam_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['bam_nonce'] ) ), self::NONCE_ACTION ) ) {
			$this->store_errors( array( 'general' => __( 'Token de seguridad inválido. Por favor recarga la página.', 'beforeaftermycare' ) ) );
			return;
		}

		// Collect & sanitize
		$nombre   = sanitize_text_field( wp_unslash( $_POST['bam_nombre']   ?? '' ) );
		$usuario  = sanitize_user( wp_unslash( $_POST['bam_usuario']  ?? '' ), true );
		$correo   = sanitize_email( wp_unslash( $_POST['bam_correo']   ?? '' ) );
		$password = wp_unslash( $_POST['bam_password']  ?? '' );
		$confirm  = wp_unslash( $_POST['bam_confirm']   ?? '' );
		$telefono = sanitize_text_field( wp_unslash( $_POST['bam_telefono'] ?? '' ) );

		// Validate
		$errors = array();

		if ( empty( $nombre ) ) {
			$errors['nombre'] = __( 'El nombre completo es requerido.', 'beforeaftermycare' );
		}

		if ( empty( $usuario ) || strlen( $usuario ) < 3 ) {
			$errors['usuario'] = __( 'El usuario debe tener al menos 3 caracteres.', 'beforeaftermycare' );
		} elseif ( username_exists( $usuario ) ) {
			$errors['usuario'] = __( 'Ese nombre de usuario ya está en uso.', 'beforeaftermycare' );
		}

		if ( empty( $correo ) || ! is_email( $correo ) ) {
			$errors['correo'] = __( 'Ingresa un correo electrónico válido.', 'beforeaftermycare' );
		} elseif ( email_exists( $correo ) ) {
			$errors['correo'] = __( 'Ese correo ya está registrado.', 'beforeaftermycare' );
		}

		if ( strlen( $password ) < 8 ) {
			$errors['password'] = __( 'La contraseña debe tener al menos 8 caracteres.', 'beforeaftermycare' );
		} elseif ( $password !== $confirm ) {
			$errors['confirm'] = __( 'Las contraseñas no coinciden.', 'beforeaftermycare' );
		}

		// Check plugin-level duplicates
		if ( empty( $errors['usuario'] ) && empty( $errors['correo'] ) ) {
			$dupes = BAM_Database::check_duplicates( $usuario, $correo );
			if ( ! isset( $errors['usuario'] ) && $dupes['usuario_exists'] ) {
				$errors['usuario'] = __( 'Ese nombre de usuario ya está en uso.', 'beforeaftermycare' );
			}
			if ( ! isset( $errors['correo'] ) && $dupes['correo_exists'] ) {
				$errors['correo'] = __( 'Ese correo ya está registrado.', 'beforeaftermycare' );
			}
		}

		if ( ! empty( $errors ) ) {
			$this->store_errors( $errors );
			return;
		}

		// Create WordPress user
		$wp_user_id = wp_create_user( $usuario, $password, $correo );

		if ( is_wp_error( $wp_user_id ) ) {
			$this->store_errors( array( 'general' => $wp_user_id->get_error_message() ) );
			return;
		}

		// Update user display name
		wp_update_user( array(
			'ID'           => $wp_user_id,
			'display_name' => $nombre,
			'first_name'   => $nombre,
		) );

		// Store in plugin table
		BAM_Database::insert_patient( array(
			'wp_user_id'    => $wp_user_id,
			'nombre'        => $nombre,
			'usuario'       => $usuario,
			'correo'        => $correo,
			'telefono'      => $telefono ?: null,
			'guia_asignada' => 'guia-de-colonoscopia',
		) );

		// Log the user in
		wp_set_current_user( $wp_user_id );
		wp_set_auth_cookie( $wp_user_id );

		// Clear any stored errors / old post data
		$this->clear_errors();

		// Redirect to medical guide
		wp_redirect( BAM_REDIRECT_URL );
		exit;
	}

	// ── Helpers ───────────────────────────────────────────────────────────────

	/**
	 * Detect if we are on a page that contains the registration shortcode.
	 *
	 * @return bool
	 */
	private function is_registration_page() {
		global $post;
		return is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'bam_registro' );
	}

	/**
	 * Persist errors to the PHP session so they survive the redirect-on-error pattern.
	 *
	 * @param array $errors
	 */
	private function store_errors( array $errors ) {
		$this->maybe_start_session();
		$_SESSION['bam_errors'] = $errors;

		// Redirect back to the form so the URL is clean
		$referer = wp_get_referer();
		if ( $referer ) {
			wp_redirect( $referer );
			exit;
		}
	}

	/**
	 * Retrieve stored errors (once) and clear the session key.
	 *
	 * @return array
	 */
	private function get_form_errors() {
		$this->maybe_start_session();
		$errors = $_SESSION['bam_errors'] ?? array();
		unset( $_SESSION['bam_errors'] );
		return $errors;
	}

	private function clear_errors() {
		$this->maybe_start_session();
		unset( $_SESSION['bam_errors'] );
	}

	private function maybe_start_session() {
		if ( ! headers_sent() && session_status() === PHP_SESSION_NONE ) {
			session_start();
		}
	}
}
