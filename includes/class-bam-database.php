<?php
/**
 * BAM_Database – Handles custom DB table creation and queries.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class BAM_Database {

	/** @var string DB version key used for upgrade checks. */
	const DB_VERSION_KEY = 'bam_db_version';
	const DB_VERSION     = '1.0';

	/** @var string Patients table name (without prefix). */
	const TABLE_PATIENTS = 'bam_patients';

	// ── Lifecycle ─────────────────────────────────────────────────────────────

	/**
	 * Run on plugin activation: create / upgrade tables.
	 */
	public static function install() {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();
		$table           = $wpdb->prefix . self::TABLE_PATIENTS;

		$sql = "CREATE TABLE {$table} (
			id            BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			wp_user_id    BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
			nombre        VARCHAR(200)        NOT NULL,
			usuario       VARCHAR(100)        NOT NULL,
			correo        VARCHAR(200)        NOT NULL,
			telefono      VARCHAR(30)                  DEFAULT NULL,
			guia_asignada VARCHAR(200)                 DEFAULT NULL,
			fecha_registro DATETIME           NOT NULL DEFAULT CURRENT_TIMESTAMP,
			estado        TINYINT(1)          NOT NULL DEFAULT 1,
			PRIMARY KEY  (id),
			UNIQUE KEY uq_usuario (usuario),
			UNIQUE KEY uq_correo  (correo)
		) {$charset_collate};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );

		update_option( self::DB_VERSION_KEY, self::DB_VERSION );
	}

	/**
	 * Placeholder for deactivation cleanup (tables are kept on deactivation).
	 */
	public static function deactivate() {
		// Intentionally left blank – data is preserved on deactivation.
	}

	// ── CRUD ──────────────────────────────────────────────────────────────────

	/**
	 * Insert a new patient record.
	 *
	 * @param array $data Keys: wp_user_id, nombre, usuario, correo, telefono, guia_asignada.
	 * @return int|false Inserted ID or false on error.
	 */
	public static function insert_patient( array $data ) {
		global $wpdb;

		$defaults = array(
			'wp_user_id'    => 0,
			'nombre'        => '',
			'usuario'       => '',
			'correo'        => '',
			'telefono'      => null,
			'guia_asignada' => null,
			'fecha_registro'=> current_time( 'mysql' ),
			'estado'        => 1,
		);

		$row = wp_parse_args( $data, $defaults );

		$result = $wpdb->insert(
			$wpdb->prefix . self::TABLE_PATIENTS,
			$row,
			array( '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%d' )
		);

		return $result ? (int) $wpdb->insert_id : false;
	}

	/**
	 * Fetch a paginated list of patients.
	 *
	 * @param int    $per_page
	 * @param int    $page     1-based.
	 * @param string $search   Optional search string.
	 * @return array { items: array, total: int }
	 */
	public static function get_patients( $per_page = 20, $page = 1, $search = '' ) {
		global $wpdb;

		$table  = $wpdb->prefix . self::TABLE_PATIENTS;
		$offset = ( max( 1, (int) $page ) - 1 ) * (int) $per_page;

		if ( $search ) {
			$like  = '%' . $wpdb->esc_like( $search ) . '%';
			$where = $wpdb->prepare( 'WHERE nombre LIKE %s OR usuario LIKE %s OR correo LIKE %s', $like, $like, $like );
		} else {
			$where = '';
		}

		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$items = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table} {$where} ORDER BY fecha_registro DESC LIMIT %d OFFSET %d",
				(int) $per_page,
				(int) $offset
			)
		);

		$total = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table} {$where}" );
		// phpcs:enable

		return array(
			'items' => $items ?: array(),
			'total' => $total,
		);
	}

	/**
	 * Get a single patient by ID.
	 *
	 * @param int $id Patient ID.
	 * @return object|null
	 */
	public static function get_patient( $id ) {
		global $wpdb;
		$table = $wpdb->prefix . self::TABLE_PATIENTS;
		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", (int) $id ) );
	}

	/**
	 * Update a patient record.
	 *
	 * @param int   $id   Patient ID.
	 * @param array $data Fields to update.
	 * @return bool
	 */
	public static function update_patient( $id, array $data ) {
		global $wpdb;
		$result = $wpdb->update(
			$wpdb->prefix . self::TABLE_PATIENTS,
			$data,
			array( 'id' => (int) $id )
		);
		return $result !== false;
	}

	/**
	 * Toggle patient status (active/inactive).
	 *
	 * @param int $id Patient ID.
	 * @return bool
	 */
	public static function toggle_status( $id ) {
		global $wpdb;
		$table   = $wpdb->prefix . self::TABLE_PATIENTS;
		$patient = self::get_patient( $id );
		if ( ! $patient ) {
			return false;
		}
		$new_estado = $patient->estado ? 0 : 1;
		return self::update_patient( $id, array( 'estado' => $new_estado ) );
	}

	/**
	 * Delete a patient record.
	 *
	 * @param int $id Patient ID.
	 * @return bool
	 */
	public static function delete_patient( $id ) {
		global $wpdb;
		$result = $wpdb->delete(
			$wpdb->prefix . self::TABLE_PATIENTS,
			array( 'id' => (int) $id ),
			array( '%d' )
		);
		return $result !== false;
	}

	/**
	 * Get a single patient record by WordPress user ID.
	 *
	 * @param int $wp_user_id WordPress user ID.
	 * @return object|null
	 */
	public static function get_patient_by_wp_user_id( $wp_user_id ) {
		global $wpdb;
		$table = $wpdb->prefix . self::TABLE_PATIENTS;
		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE wp_user_id = %d LIMIT 1", (int) $wp_user_id ) );
	}

	/**
	 *
	 * @param string $usuario
	 * @param string $correo
	 * @return array Keys: usuario_exists (bool), correo_exists (bool).
	 */
	public static function check_duplicates( $usuario, $correo ) {
		global $wpdb;
		$table = $wpdb->prefix . self::TABLE_PATIENTS;

		return array(
			'usuario_exists' => (bool) $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$table} WHERE usuario = %s LIMIT 1", $usuario ) ),
			'correo_exists'  => (bool) $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$table} WHERE correo  = %s LIMIT 1", $correo ) ),
		);
	}

	/**
	 * Get count of active patients.
	 *
	 * @return int
	 */
	public static function count_active() {
		global $wpdb;
		$table = $wpdb->prefix . self::TABLE_PATIENTS;
		return (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table} WHERE estado = 1" );
	}

	/**
	 * Get total count of patients.
	 *
	 * @return int
	 */
	public static function count_total() {
		global $wpdb;
		$table = $wpdb->prefix . self::TABLE_PATIENTS;
		return (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" );
	}

	/**
	 * Get patients registered in the last 30 days.
	 *
	 * @return int
	 */
	public static function count_recent() {
		global $wpdb;
		$table = $wpdb->prefix . self::TABLE_PATIENTS;
		return (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table} WHERE fecha_registro >= DATE_SUB(NOW(), INTERVAL 30 DAY)" );
	}

	/**
	 * Get monthly patient registrations for the last 12 months.
	 *
	 * @return array Array of objects with `month` (YYYY-MM) and `count`.
	 */
	public static function get_monthly_registrations() {
		global $wpdb;
		$table = $wpdb->prefix . self::TABLE_PATIENTS;
		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return $wpdb->get_results(
			"SELECT DATE_FORMAT(fecha_registro, '%Y-%m') as month, COUNT(*) as count
			 FROM {$table}
			 WHERE fecha_registro >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
			 GROUP BY month
			 ORDER BY month ASC"
		) ?: array();
		// phpcs:enable
	}

	/**
	 * Get distribution of patients by assigned guide.
	 *
	 * @return array Array of objects with `guia` and `count`.
	 */
	public static function get_guide_distribution() {
		global $wpdb;
		$table = $wpdb->prefix . self::TABLE_PATIENTS;
		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return $wpdb->get_results(
			"SELECT COALESCE(NULLIF(guia_asignada, ''), 'Sin Guía') as guia, COUNT(*) as count
			 FROM {$table}
			 GROUP BY guia_asignada
			 ORDER BY count DESC
			 LIMIT 10"
		) ?: array();
		// phpcs:enable
	}
}
