<?php
/**
 * Plugin Name:       Before After My Care – Guías Médicas
 * Plugin URI:        https://beforeaftermycare.com
 * Description:       Gestión de guías de procesos médicos con registro de pacientes y dashboard de administración.
 * Version:           1.0.2
 * Author:            Before After My Care
 * Author URI:        https://beforeaftermycare.com
 * Text Domain:       beforeaftermycare
 * Domain Path:       /languages
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * License:           GPL-2.0-or-later
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ── Constants ────────────────────────────────────────────────────────────────
define( 'BAM_VERSION',     '1.0.2' );
define( 'BAM_PLUGIN_FILE', __FILE__ );
define( 'BAM_PLUGIN_DIR',  plugin_dir_path( __FILE__ ) );
define( 'BAM_PLUGIN_URL',  plugin_dir_url( __FILE__ ) );
define( 'BAM_REDIRECT_URL', 'https://pacificasalud.beforeaftermycare.com/guia-de-colonoscopia/' );

// ── Autoload classes ──────────────────────────────────────────────────────────
require_once BAM_PLUGIN_DIR . 'includes/class-bam-database.php';
require_once BAM_PLUGIN_DIR . 'includes/class-bam-registration.php';
require_once BAM_PLUGIN_DIR . 'includes/class-bam-admin.php';
require_once BAM_PLUGIN_DIR . 'includes/class-bam-frontend-dashboard.php';
require_once BAM_PLUGIN_DIR . 'includes/class-bam-survey.php';
require_once BAM_PLUGIN_DIR . 'includes/class-bam-reminder.php';

// ── Activation / Deactivation hooks ──────────────────────────────────────────
register_activation_hook( __FILE__, 'bam_activate' );
register_deactivation_hook( __FILE__, 'bam_deactivate' );

function bam_activate() {
	BAM_Database::install();
	BAM_Registration::create_page();
	BAM_Frontend_Dashboard::create_page();
	BAM_Frontend_Dashboard::create_login_page();
	BAM_Reminder::schedule_cron();
	flush_rewrite_rules();
}

function bam_deactivate() {
	BAM_Database::deactivate();
	BAM_Reminder::unschedule_cron();
}

// ── Boot ──────────────────────────────────────────────────────────────────────
add_action( 'plugins_loaded', 'bam_init' );

function bam_init() {
	// Load text domain for translations
	load_plugin_textdomain( 'beforeaftermycare', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

	// Run DB upgrade if needed (handles existing installs when plugin is updated).
	if ( get_option( BAM_Database::DB_VERSION_KEY ) !== BAM_Database::DB_VERSION ) {
		BAM_Database::install();
	}

	// Register cron schedule before any scheduling calls.
	add_filter( 'cron_schedules', array( 'BAM_Reminder', 'add_cron_schedule' ) );

	// Ensure cron is scheduled (in case the plugin was re-activated without deactivation).
	BAM_Reminder::schedule_cron();

	// Boot sub-systems
	BAM_Registration::get_instance();
	BAM_Admin::get_instance();
	BAM_Frontend_Dashboard::get_instance();
	BAM_Survey::get_instance();
	BAM_Reminder::get_instance();
}
