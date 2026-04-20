<?php
/**
 * Standalone frontend dashboard – no Elementor header or footer.
 *
 * Sections (controlled via ?bam_section=):
 *   dashboard      – overview with stat cards + latest registrations
 *   pacientes      – full patient list / edit view
 *   estadisticas   – statistics charts
 *   recordatorios  – reminder settings
 */

if ( ! defined( 'ABSPATH' ) ) {
exit;
}

// ── Data ──────────────────────────────────────────────────────────────────────

$current_user = wp_get_current_user();

// phpcs:disable WordPress.Security.NonceVerification.Recommended
$msg     = isset( $_GET['bam_msg'] )     ? sanitize_key( $_GET['bam_msg'] )                 : '';
$edit_id = isset( $_GET['bam_edit'] )    ? absint( $_GET['bam_edit'] )                      : 0;
$search  = isset( $_GET['s'] )           ? sanitize_text_field( wp_unslash( $_GET['s'] ) )  : '';
$page    = isset( $_GET['paged'] )       ? max( 1, absint( $_GET['paged'] ) )                : 1;
$section = isset( $_GET['bam_section'] ) ? sanitize_key( $_GET['bam_section'] )              : 'dashboard';
// phpcs:enable

// Force section to 'pacientes' when editing a patient.
if ( $edit_id ) {
$section = 'pacientes';
}

$per_page = 20;

$page_obj = get_page_by_path( BAM_Frontend_Dashboard::PAGE_SLUG );
$base_url = $page_obj ? get_permalink( $page_obj ) : home_url( '/' . BAM_Frontend_Dashboard::PAGE_SLUG . '/' );

// Always load summary counts (needed for sidebar badges + dashboard).
$total_all = BAM_Database::count_total();
$active    = BAM_Database::count_active();
$recent    = BAM_Database::count_recent();
$inactive  = $total_all - $active;
$act_rate  = $total_all > 0 ? round( ( $active / $total_all ) * 100 ) : 0;

// Patient list (pacientes section).
$result    = BAM_Database::get_patients( $per_page, $page, $search );
$patients  = $result['items'];
$total     = $result['total'];
$num_pages = (int) ceil( $total / $per_page );

// Edit patient.
$edit_patient = null;
if ( $edit_id ) {
$edit_patient = BAM_Database::get_patient( $edit_id );
}

// Latest registrations (dashboard section).
$latest_result = BAM_Database::get_patients( 5, 1 );
$latest        = $latest_result['items'];

// Stats.
$monthly = BAM_Database::get_monthly_registrations();
$guides  = BAM_Database::get_guide_distribution();

// Build month labels / values for the bar chart.
$chart_labels = array();
$chart_values = array();
$chart_max    = 1;
foreach ( $monthly as $row ) {
$ts             = mktime( 0, 0, 0, (int) substr( $row->month, 5, 2 ), 1, (int) substr( $row->month, 0, 4 ) );
$chart_labels[] = date_i18n( 'M Y', $ts );
$chart_values[] = (int) $row->count;
if ( (int) $row->count > $chart_max ) {
$chart_max = (int) $row->count;
}
}
$guides_max = 1;
foreach ( $guides as $row ) {
if ( (int) $row->count > $guides_max ) {
$guides_max = (int) $row->count;
}
}

// Helper: URL builder for sidebar navigation.
function bam_section_url( $section_slug, $base ) {
return add_query_arg( 'bam_section', $section_slug, $base );
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php esc_html_e( 'Dashboard – Guías Médicas', 'beforeaftermycare' ); ?> – <?php bloginfo( 'name' ); ?></title>
<link rel="stylesheet" href="<?php echo esc_url( BAM_PLUGIN_URL . 'assets/css/admin.css' ); ?>?v=<?php echo esc_attr( BAM_VERSION ); ?>">
<style>html,body{margin:0;padding:0;}</style>
</head>
<body class="bam-frontend-dashboard">

<div class="bam-app">

<!-- ── Top bar ─────────────────────────────────────────────────────────── -->
<header class="bam-topbar">
<button class="bam-sidebar-toggle" id="bamSidebarToggle" aria-label="Menú" aria-expanded="false">
<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
</button>
<div class="bam-topbar-brand">
<svg width="28" height="28" viewBox="0 0 48 48" fill="none" aria-hidden="true">
<circle cx="24" cy="24" r="24" fill="rgba(255,255,255,0.2)"/>
<path d="M24 12c-6.627 0-12 5.373-12 12s5.373 12 12 12 12-5.373 12-12S30.627 12 24 12zm1 17h-2v-6h2v6zm0-8h-2v-2h2v2z" fill="#fff"/>
</svg>
<span><?php esc_html_e( 'Guías Médicas', 'beforeaftermycare' ); ?></span>
</div>
<div class="bam-topbar-right">
<div class="bam-topbar-user">
<div class="bam-user-avatar">
<?php echo esc_html( mb_strtoupper( mb_substr( $current_user->display_name, 0, 1 ) ) ); ?>
</div>
<span class="bam-topbar-username"><?php echo esc_html( $current_user->display_name ); ?></span>
</div>
<a class="bam-topbar-logout" href="<?php echo esc_url( wp_logout_url( home_url( '/' . BAM_Frontend_Dashboard::PAGE_LOGIN_SLUG . '/' ) ) ); ?>" title="<?php esc_attr_e( 'Cerrar sesión', 'beforeaftermycare' ); ?>">
<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
<span><?php esc_html_e( 'Salir', 'beforeaftermycare' ); ?></span>
</a>
</div>
</header>

<div class="bam-body">

<!-- ── Sidebar ──────────────────────────────────────────────────────── -->
<aside class="bam-sidebar" id="bamSidebar">
<nav class="bam-sidebar-nav" aria-label="Navegación principal">

<div class="bam-sidebar-section-label"><?php esc_html_e( 'MENÚ', 'beforeaftermycare' ); ?></div>

<a href="<?php echo esc_url( bam_section_url( 'dashboard', $base_url ) ); ?>"
   class="bam-sidebar-item <?php echo 'dashboard' === $section ? 'bam-sidebar-active' : ''; ?>">
<span class="bam-sidebar-icon">
<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
</span>
<span class="bam-sidebar-label"><?php esc_html_e( 'Dashboard', 'beforeaftermycare' ); ?></span>
</a>

<a href="<?php echo esc_url( bam_section_url( 'pacientes', $base_url ) ); ?>"
   class="bam-sidebar-item <?php echo 'pacientes' === $section ? 'bam-sidebar-active' : ''; ?>">
<span class="bam-sidebar-icon">
<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
</span>
<span class="bam-sidebar-label"><?php esc_html_e( 'Pacientes', 'beforeaftermycare' ); ?></span>
<?php if ( $total_all > 0 ) : ?>
<span class="bam-sidebar-badge"><?php echo esc_html( $total_all ); ?></span>
<?php endif; ?>
</a>

<a href="<?php echo esc_url( bam_section_url( 'estadisticas', $base_url ) ); ?>"
   class="bam-sidebar-item <?php echo 'estadisticas' === $section ? 'bam-sidebar-active' : ''; ?>">
<span class="bam-sidebar-icon">
<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/><line x1="2" y1="20" x2="22" y2="20"/></svg>
</span>
<span class="bam-sidebar-label"><?php esc_html_e( 'Estadísticas', 'beforeaftermycare' ); ?></span>
</a>

<a href="<?php echo esc_url( bam_section_url( 'recordatorios', $base_url ) ); ?>"
   class="bam-sidebar-item <?php echo 'recordatorios' === $section ? 'bam-sidebar-active' : ''; ?>">
<span class="bam-sidebar-icon">
<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
</span>
<span class="bam-sidebar-label"><?php esc_html_e( 'Recordatorios', 'beforeaftermycare' ); ?></span>
</a>

<div class="bam-sidebar-divider"></div>
<div class="bam-sidebar-section-label"><?php esc_html_e( 'SISTEMA', 'beforeaftermycare' ); ?></div>

<a href="<?php echo esc_url( wp_logout_url( home_url( '/' . BAM_Frontend_Dashboard::PAGE_LOGIN_SLUG . '/' ) ) ); ?>"
   class="bam-sidebar-item bam-sidebar-logout">
<span class="bam-sidebar-icon">
<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
</span>
<span class="bam-sidebar-label"><?php esc_html_e( 'Cerrar sesión', 'beforeaftermycare' ); ?></span>
</a>
</nav>

<div class="bam-sidebar-footer">
<div class="bam-sidebar-footer-info">
<span class="bam-sidebar-footer-label"><?php esc_html_e( 'Activos', 'beforeaftermycare' ); ?></span>
<strong><?php echo esc_html( $active ); ?> / <?php echo esc_html( $total_all ); ?></strong>
</div>
</div>
</aside>

<!-- ── Main content ─────────────────────────────────────────────────── -->
<main class="bam-main" id="bamMain">

<!-- Flash messages -->
<?php if ( 'deleted' === $msg ) : ?>
<div class="bam-notice bam-notice-success" role="status"><?php esc_html_e( 'Paciente eliminado correctamente.', 'beforeaftermycare' ); ?></div>
<?php elseif ( 'toggled' === $msg ) : ?>
<div class="bam-notice bam-notice-success" role="status"><?php esc_html_e( 'Estado del paciente actualizado.', 'beforeaftermycare' ); ?></div>
<?php elseif ( 'updated' === $msg ) : ?>
<div class="bam-notice bam-notice-success" role="status"><?php esc_html_e( 'Datos del paciente guardados correctamente.', 'beforeaftermycare' ); ?></div>
<?php elseif ( 'pass_changed' === $msg ) : ?>
<div class="bam-notice bam-notice-success" role="status"><?php esc_html_e( 'Contraseña del paciente actualizada correctamente.', 'beforeaftermycare' ); ?></div>
<?php elseif ( 'pass_mismatch' === $msg ) : ?>
<div class="bam-notice bam-notice-error" role="alert"><?php esc_html_e( 'Las contraseñas no coinciden. Inténtalo de nuevo.', 'beforeaftermycare' ); ?></div>
<?php endif; ?>


<?php if ( 'dashboard' === $section ) : ?>
<!-- ═══════════════════════════ DASHBOARD SECTION ════════════════════ -->

<div class="bam-page-header">
<div>
<h1 class="bam-page-title"><?php esc_html_e( 'Resumen General', 'beforeaftermycare' ); ?></h1>
<p class="bam-page-desc"><?php esc_html_e( 'Vista general del sistema de guías médicas.', 'beforeaftermycare' ); ?></p>
</div>
<div class="bam-page-date">
<?php echo esc_html( date_i18n( get_option( 'date_format' ) ) ); ?>
</div>
</div>

<!-- Stat cards -->
<div class="bam-stat-grid">
<div class="bam-stat-card">
<div class="bam-stat-icon bam-icon-blue">
<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
</div>
<div class="bam-stat-info">
<span class="bam-stat-value"><?php echo esc_html( number_format_i18n( $total_all ) ); ?></span>
<span class="bam-stat-label"><?php esc_html_e( 'Pacientes Totales', 'beforeaftermycare' ); ?></span>
</div>
<div class="bam-stat-trend bam-trend-up">
<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
</div>
</div>

<div class="bam-stat-card">
<div class="bam-stat-icon bam-icon-green">
<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
</div>
<div class="bam-stat-info">
<span class="bam-stat-value"><?php echo esc_html( number_format_i18n( $active ) ); ?></span>
<span class="bam-stat-label"><?php esc_html_e( 'Pacientes Activos', 'beforeaftermycare' ); ?></span>
</div>
<div class="bam-stat-badge bam-badge bam-badge-success" style="margin-left:auto;"><?php echo esc_html( $act_rate ); ?>%</div>
</div>

<div class="bam-stat-card">
<div class="bam-stat-icon bam-icon-purple">
<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
</div>
<div class="bam-stat-info">
<span class="bam-stat-value"><?php echo esc_html( number_format_i18n( $recent ) ); ?></span>
<span class="bam-stat-label"><?php esc_html_e( 'Nuevos (30 días)', 'beforeaftermycare' ); ?></span>
</div>
</div>

<div class="bam-stat-card">
<div class="bam-stat-icon bam-icon-orange">
<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
</div>
<div class="bam-stat-info">
<span class="bam-stat-value"><?php echo esc_html( $act_rate ); ?>%</span>
<span class="bam-stat-label"><?php esc_html_e( 'Tasa de Actividad', 'beforeaftermycare' ); ?></span>
</div>
</div>
</div>

<!-- Latest registrations -->
<div class="bam-card">
<div class="bam-card-header">
<h2 class="bam-card-title">
<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
<?php esc_html_e( 'Últimos Registros', 'beforeaftermycare' ); ?>
</h2>
<a class="bam-btn bam-btn-sm bam-btn-outline" href="<?php echo esc_url( bam_section_url( 'pacientes', $base_url ) ); ?>">
<?php esc_html_e( 'Ver todos', 'beforeaftermycare' ); ?>
</a>
</div>
<?php if ( empty( $latest ) ) : ?>
<div class="bam-empty-state">
<svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true" style="color:#cbd5e1;margin-bottom:12px;"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
<p><?php esc_html_e( 'Aún no hay pacientes registrados.', 'beforeaftermycare' ); ?></p>
</div>
<?php else : ?>
<div class="bam-table-wrapper">
<table class="bam-table">
<thead>
<tr>
<th><?php esc_html_e( 'Nombre', 'beforeaftermycare' ); ?></th>
<th><?php esc_html_e( 'Usuario', 'beforeaftermycare' ); ?></th>
<th><?php esc_html_e( 'Correo', 'beforeaftermycare' ); ?></th>
<th><?php esc_html_e( 'Guía', 'beforeaftermycare' ); ?></th>
<th><?php esc_html_e( 'Fecha', 'beforeaftermycare' ); ?></th>
<th><?php esc_html_e( 'Estado', 'beforeaftermycare' ); ?></th>
</tr>
</thead>
<tbody>
<?php foreach ( $latest as $patient ) : ?>
<tr>
<td>
<a href="<?php echo esc_url( add_query_arg( array( 'bam_edit' => $patient->id, 'bam_section' => 'pacientes' ), $base_url ) ); ?>" class="bam-link">
<?php echo esc_html( $patient->nombre ); ?>
</a>
</td>
<td><?php echo esc_html( $patient->usuario ); ?></td>
<td><?php echo esc_html( $patient->correo ); ?></td>
<td><?php echo $patient->guia_asignada ? esc_html( $patient->guia_asignada ) : '—'; ?></td>
<td><?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $patient->fecha_registro ) ) ); ?></td>
<td>
<span class="bam-badge <?php echo $patient->estado ? 'bam-badge-success' : 'bam-badge-inactive'; ?>">
<?php echo $patient->estado ? esc_html__( 'Activo', 'beforeaftermycare' ) : esc_html__( 'Inactivo', 'beforeaftermycare' ); ?>
</span>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
<?php endif; ?>
</div>


<?php elseif ( 'estadisticas' === $section ) : ?>
<!-- ═══════════════════════════ ESTADÍSTICAS SECTION ════════════════ -->

<div class="bam-page-header">
<div>
<h1 class="bam-page-title"><?php esc_html_e( 'Estadísticas', 'beforeaftermycare' ); ?></h1>
<p class="bam-page-desc"><?php esc_html_e( 'Análisis de registros y actividad de pacientes.', 'beforeaftermycare' ); ?></p>
</div>
</div>

<!-- Summary row -->
<div class="bam-stat-grid bam-stat-grid-3">
<div class="bam-stat-card bam-stat-card-wide">
<div class="bam-stat-icon bam-icon-blue">
<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
</div>
<div class="bam-stat-info">
<span class="bam-stat-value"><?php echo esc_html( $total_all ); ?></span>
<span class="bam-stat-label"><?php esc_html_e( 'Total Pacientes', 'beforeaftermycare' ); ?></span>
</div>
</div>
<div class="bam-stat-card bam-stat-card-wide">
<div class="bam-stat-icon bam-icon-green">
<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
</div>
<div class="bam-stat-info">
<span class="bam-stat-value"><?php echo esc_html( $active ); ?></span>
<span class="bam-stat-label"><?php esc_html_e( 'Activos', 'beforeaftermycare' ); ?></span>
</div>
</div>
<div class="bam-stat-card bam-stat-card-wide">
<div class="bam-stat-icon bam-icon-orange">
<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
</div>
<div class="bam-stat-info">
<span class="bam-stat-value"><?php echo esc_html( $inactive ); ?></span>
<span class="bam-stat-label"><?php esc_html_e( 'Inactivos', 'beforeaftermycare' ); ?></span>
</div>
</div>
</div>

<!-- Activity rate bar -->
<div class="bam-card" style="margin-bottom:24px;">
<div class="bam-card-header">
<h2 class="bam-card-title">
<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
<?php esc_html_e( 'Tasa de Actividad', 'beforeaftermycare' ); ?>
</h2>
<span class="bam-badge bam-badge-success"><?php echo esc_html( $act_rate ); ?>%</span>
</div>
<div style="padding:20px;">
<div class="bam-progress-bar-wrap">
<div class="bam-progress-bar-track">
<div class="bam-progress-bar-fill bam-progress-green" style="width:<?php echo esc_attr( $act_rate ); ?>%"></div>
</div>
<div class="bam-progress-labels">
<span><?php esc_html_e( 'Activos', 'beforeaftermycare' ); ?> (<?php echo esc_html( $active ); ?>)</span>
<span><?php esc_html_e( 'Inactivos', 'beforeaftermycare' ); ?> (<?php echo esc_html( $inactive ); ?>)</span>
</div>
</div>
</div>
</div>

<div class="bam-stats-row">
<!-- Monthly registrations bar chart -->
<div class="bam-card bam-stats-chart-card">
<div class="bam-card-header">
<h2 class="bam-card-title">
<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/><line x1="2" y1="20" x2="22" y2="20"/></svg>
<?php esc_html_e( 'Registros por Mes (últimos 12 meses)', 'beforeaftermycare' ); ?>
</h2>
</div>
<div class="bam-chart-wrap">
<?php if ( empty( $chart_values ) ) : ?>
<div class="bam-empty-state"><p><?php esc_html_e( 'Sin datos suficientes.', 'beforeaftermycare' ); ?></p></div>
<?php else : ?>
<div class="bam-bar-chart">
<?php foreach ( $chart_values as $i => $val ) :
$pct = $chart_max > 0 ? round( ( $val / $chart_max ) * 100 ) : 0;
?>
<div class="bam-bar-col">
<div class="bam-bar-value"><?php echo esc_html( $val ); ?></div>
<div class="bam-bar-track">
<div class="bam-bar-fill" style="height:<?php echo esc_attr( $pct ); ?>%"></div>
</div>
<div class="bam-bar-label"><?php echo esc_html( $chart_labels[ $i ] ?? '' ); ?></div>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>
</div>
</div>

<!-- Guide distribution -->
<div class="bam-card bam-stats-dist-card">
<div class="bam-card-header">
<h2 class="bam-card-title">
<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
<?php esc_html_e( 'Distribución por Guía', 'beforeaftermycare' ); ?>
</h2>
</div>
<div style="padding:20px;">
<?php if ( empty( $guides ) ) : ?>
<div class="bam-empty-state"><p><?php esc_html_e( 'Sin datos.', 'beforeaftermycare' ); ?></p></div>
<?php else :
$dist_colors = array( '#0077b6','#00b4d8','#48cae4','#90e0ef','#caf0f8','#023e8a','#0096c7','#0077b6','#023e8a','#48cae4' );
foreach ( $guides as $gi => $row ) :
$pct = $guides_max > 0 ? round( ( (int) $row->count / $guides_max ) * 100 ) : 0;
$color = $dist_colors[ $gi % count( $dist_colors ) ];
?>
<div class="bam-dist-row">
<span class="bam-dist-label" title="<?php echo esc_attr( $row->guia ); ?>"><?php echo esc_html( $row->guia ); ?></span>
<div class="bam-dist-bar-track">
<div class="bam-dist-bar-fill" style="width:<?php echo esc_attr( $pct ); ?>%;background:<?php echo esc_attr( $color ); ?>"></div>
</div>
<span class="bam-dist-count"><?php echo esc_html( $row->count ); ?></span>
</div>
<?php endforeach; endif; ?>
</div>
</div>
</div>


<?php elseif ( 'recordatorios' === $section ) : ?>
<!-- ═══════════════════════════ RECORDATORIOS SECTION ══════════════════ -->

<?php
$reminder_hours   = (int) get_option( 'bam_reminder_hours', BAM_Reminder::DEFAULT_HOURS );
$reminder_msg     = isset( $_GET['bam_reminder_msg'] ) ? sanitize_key( $_GET['bam_reminder_msg'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$reminder_options = array(
	1    => __( '1 minuto antes (prueba)', 'beforeaftermycare' ),
	30   => __( '30 minutos antes', 'beforeaftermycare' ),
	60   => __( '1 hora antes', 'beforeaftermycare' ),
	360  => __( '6 horas antes', 'beforeaftermycare' ),
	720  => __( '12 horas antes', 'beforeaftermycare' ),
	1440 => __( '24 horas antes', 'beforeaftermycare' ),
	2880 => __( '48 horas antes', 'beforeaftermycare' ),
	4320 => __( '72 horas antes', 'beforeaftermycare' ),
);
?>

<div class="bam-page-header">
<div>
<h1 class="bam-page-title"><?php esc_html_e( 'Recordatorios de Procedimiento', 'beforeaftermycare' ); ?></h1>
<p class="bam-page-desc"><?php esc_html_e( 'Configura cuándo se enviará el correo de recordatorio al paciente.', 'beforeaftermycare' ); ?></p>
</div>
</div>

<?php if ( 'saved' === $reminder_msg ) : ?>
<div class="bam-notice bam-notice-success" role="status"><?php esc_html_e( 'Configuración guardada correctamente.', 'beforeaftermycare' ); ?></div>
<?php endif; ?>

<div style="display:grid;grid-template-columns:1fr 340px;gap:24px;align-items:start;">

<!-- Settings card -->
<div class="bam-card">
<div class="bam-card-header">
<h2 class="bam-card-title">
<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
<?php esc_html_e( 'Configuración de Envío', 'beforeaftermycare' ); ?>
</h2>
</div>
<form method="post" action="<?php echo esc_url( $base_url ); ?>" style="padding:24px;">
<?php wp_nonce_field( 'bam_front_save_reminder', 'bam_front_reminder_nonce' ); ?>

<div class="bam-field">
<label class="bam-label" for="bam_reminder_hours_front">
<?php esc_html_e( 'Enviar recordatorio', 'beforeaftermycare' ); ?>
</label>
<select class="bam-input" id="bam_reminder_hours_front" name="bam_reminder_hours">
<?php foreach ( $reminder_options as $value => $label ) : ?>
<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $reminder_hours, $value ); ?>>
<?php echo esc_html( $label ); ?>
</option>
<?php endforeach; ?>
</select>
<span style="font-size:0.8rem;color:#64748b;margin-top:6px;display:block;">
<?php esc_html_e( 'El sistema verificará cada hora y enviará el correo cuando el procedimiento esté dentro del tiempo configurado.', 'beforeaftermycare' ); ?>
</span>
</div>

<button type="submit" name="bam_front_reminder_submit" class="bam-btn bam-btn-primary">
<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
<?php esc_html_e( 'Guardar Configuración', 'beforeaftermycare' ); ?>
</button>
</form>
</div>

<!-- Info panel -->
<div class="bam-card">
<div class="bam-card-header">
<h2 class="bam-card-title">
<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
<?php esc_html_e( 'Cómo funciona', 'beforeaftermycare' ); ?>
</h2>
</div>
<div style="padding:20px;">
<ul style="margin:0;padding:0 0 0 18px;display:flex;flex-direction:column;gap:12px;">
<li style="font-size:0.875rem;color:#475569;line-height:1.6;"><?php esc_html_e( 'Asigna la fecha y hora del procedimiento en el perfil del paciente.', 'beforeaftermycare' ); ?></li>
<li style="font-size:0.875rem;color:#475569;line-height:1.6;"><?php esc_html_e( 'El sistema verifica automáticamente cada hora.', 'beforeaftermycare' ); ?></li>
<li style="font-size:0.875rem;color:#475569;line-height:1.6;"><?php esc_html_e( 'El correo se envía desde "Pacifica Salud" con el asunto "Recordatorio de Colonoscopia".', 'beforeaftermycare' ); ?></li>
<li style="font-size:0.875rem;color:#475569;line-height:1.6;"><?php esc_html_e( 'Al actualizar la fecha del procedimiento, el recordatorio se restablece.', 'beforeaftermycare' ); ?></li>
</ul>
<div style="margin-top:20px;padding:14px;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;">
<p style="margin:0 0 4px;font-size:0.78rem;color:#15803d;font-weight:700;"><?php esc_html_e( 'Configuración actual:', 'beforeaftermycare' ); ?></p>
<p style="margin:0;font-size:0.875rem;color:#15803d;">
<?php
$current_label = isset( $reminder_options[ $reminder_hours ] ) ? $reminder_options[ $reminder_hours ] : $reminder_hours . ' ' . __( 'horas antes', 'beforeaftermycare' );
echo esc_html( $current_label );
?>
</p>
</div>
</div>
</div>

</div>


<?php else : ?>
<!-- ═══════════════════════════ PACIENTES SECTION ════════════════════ -->

<?php if ( $edit_patient ) : ?>
<!-- ─── EDIT VIEW ─────────────────────────────────────────────────── -->

<div class="bam-page-header">
<div>
<a class="bam-back-link" href="<?php echo esc_url( bam_section_url( 'pacientes', $base_url ) ); ?>">
← <?php esc_html_e( 'Volver a Pacientes', 'beforeaftermycare' ); ?>
</a>
<h1 class="bam-page-title"><?php echo esc_html( $edit_patient->nombre ); ?></h1>
</div>
</div>

<div class="bam-detail-grid">

<!-- Edit form -->
<div class="bam-card bam-detail-form-card">
<div class="bam-card-header">
<h2 class="bam-card-title"><?php esc_html_e( 'Editar Datos del Paciente', 'beforeaftermycare' ); ?></h2>
</div>
<form method="post" action="<?php echo esc_url( $base_url ); ?>">
<?php wp_nonce_field( 'bam_front_update_' . $edit_id, 'bam_front_update_nonce' ); ?>
<input type="hidden" name="bam_id" value="<?php echo esc_attr( $edit_id ); ?>">

<div class="bam-field">
<label class="bam-label" for="bam_nombre"><?php esc_html_e( 'Nombre Completo', 'beforeaftermycare' ); ?></label>
<input class="bam-input" type="text" id="bam_nombre" name="bam_nombre" value="<?php echo esc_attr( $edit_patient->nombre ); ?>" required>
</div>
<div class="bam-field">
<label class="bam-label" for="bam_correo"><?php esc_html_e( 'Correo Electrónico', 'beforeaftermycare' ); ?></label>
<input class="bam-input" type="email" id="bam_correo" name="bam_correo" value="<?php echo esc_attr( $edit_patient->correo ); ?>" required>
</div>
<div class="bam-field">
<label class="bam-label" for="bam_telefono"><?php esc_html_e( 'Teléfono', 'beforeaftermycare' ); ?></label>
<input class="bam-input" type="tel" id="bam_telefono" name="bam_telefono" value="<?php echo esc_attr( $edit_patient->telefono ?? '' ); ?>">
</div>
<div class="bam-field">
<label class="bam-label" for="bam_guia_asignada"><?php esc_html_e( 'Guía Asignada', 'beforeaftermycare' ); ?></label>
<input class="bam-input" type="text" id="bam_guia_asignada" name="bam_guia_asignada" value="<?php echo esc_attr( $edit_patient->guia_asignada ?? '' ); ?>" placeholder="guia-de-colonoscopia">
</div>
<div class="bam-field">
<label class="bam-label" for="bam_procedimiento"><?php esc_html_e( 'Nombre del Procedimiento', 'beforeaftermycare' ); ?></label>
<input class="bam-input" type="text" id="bam_procedimiento" name="bam_procedimiento" value="<?php echo esc_attr( $edit_patient->procedimiento ?? '' ); ?>" placeholder="<?php esc_attr_e( 'Ej: Colonoscopia', 'beforeaftermycare' ); ?>">
</div>
<div class="bam-field">
<label class="bam-label" for="bam_fecha_procedimiento"><?php esc_html_e( 'Fecha y Hora del Procedimiento', 'beforeaftermycare' ); ?></label>
<input class="bam-input" type="datetime-local" id="bam_fecha_procedimiento" name="bam_fecha_procedimiento" value="<?php echo esc_attr( ! empty( $edit_patient->fecha_procedimiento ) ? gmdate( 'Y-m-d\TH:i', strtotime( $edit_patient->fecha_procedimiento ) ) : '' ); ?>">
<span style="font-size:0.8rem;color:#64748b;margin-top:4px;display:block;"><?php esc_html_e( 'El recordatorio se enviará automáticamente según la configuración de horas.', 'beforeaftermycare' ); ?></span>
</div>
<div class="bam-form-actions">
<button type="submit" name="bam_front_update_submit" class="bam-btn bam-btn-primary"><?php esc_html_e( 'Guardar Cambios', 'beforeaftermycare' ); ?></button>
<a class="bam-btn bam-btn-outline" href="<?php echo esc_url( bam_section_url( 'pacientes', $base_url ) ); ?>"><?php esc_html_e( 'Cancelar', 'beforeaftermycare' ); ?></a>
</div>
</form>
</div>

<!-- Right column -->
<div class="bam-detail-info-col">

<!-- Info card -->
<div class="bam-card">
<div class="bam-card-header">
<h2 class="bam-card-title"><?php esc_html_e( 'Información', 'beforeaftermycare' ); ?></h2>
</div>
<dl class="bam-info-list">
<div class="bam-info-row"><dt><?php esc_html_e( 'ID', 'beforeaftermycare' ); ?></dt><dd>#<?php echo esc_html( $edit_patient->id ); ?></dd></div>
<div class="bam-info-row"><dt><?php esc_html_e( 'Usuario', 'beforeaftermycare' ); ?></dt><dd><?php echo esc_html( $edit_patient->usuario ); ?></dd></div>
<div class="bam-info-row">
<dt><?php esc_html_e( 'Registro', 'beforeaftermycare' ); ?></dt>
<dd><?php echo esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $edit_patient->fecha_registro ) ) ); ?></dd>
</div>
<div class="bam-info-row">
<dt><?php esc_html_e( 'Estado', 'beforeaftermycare' ); ?></dt>
<dd><span class="bam-badge <?php echo $edit_patient->estado ? 'bam-badge-success' : 'bam-badge-inactive'; ?>"><?php echo $edit_patient->estado ? esc_html__( 'Activo', 'beforeaftermycare' ) : esc_html__( 'Inactivo', 'beforeaftermycare' ); ?></span></dd>
</div>
<div class="bam-info-row">
<dt><?php esc_html_e( 'Recordatorio', 'beforeaftermycare' ); ?></dt>
<dd>
<?php if ( ! empty( $edit_patient->fecha_procedimiento ) ) : ?>
<span class="bam-badge <?php echo $edit_patient->recordatorio_enviado ? 'bam-badge-success' : 'bam-badge-warning'; ?>">
<?php echo $edit_patient->recordatorio_enviado ? esc_html__( 'Enviado', 'beforeaftermycare' ) : esc_html__( 'Pendiente', 'beforeaftermycare' ); ?>
</span>
<?php else : ?>
<span style="color:#94a3b8;font-size:0.8rem;"><?php esc_html_e( 'Sin fecha', 'beforeaftermycare' ); ?></span>
<?php endif; ?>
</dd>
</div>
</dl>
</div>

<!-- Change password card -->
<div class="bam-card bam-card-password">
<div class="bam-card-header">
<h2 class="bam-card-title">
<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
<?php esc_html_e( 'Cambiar Contraseña', 'beforeaftermycare' ); ?>
</h2>
</div>
<form method="post" action="<?php echo esc_url( $base_url ); ?>" style="padding:16px 20px;">
<?php wp_nonce_field( 'bam_front_change_pass_' . $edit_id, 'bam_front_change_pass_nonce' ); ?>
<input type="hidden" name="bam_id" value="<?php echo esc_attr( $edit_id ); ?>">
<div class="bam-field">
<label class="bam-label" for="bam_new_password"><?php esc_html_e( 'Nueva Contraseña', 'beforeaftermycare' ); ?></label>
<div class="bam-input-password-wrapper">
<input class="bam-input" type="password" id="bam_new_password" name="bam_new_password" autocomplete="new-password" required minlength="8">
<button type="button" class="bam-toggle-pass" data-target="bam_new_password" aria-label="<?php esc_attr_e( 'Mostrar / ocultar contraseña', 'beforeaftermycare' ); ?>">
<svg class="bam-eye-show" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
<svg class="bam-eye-hide" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" style="display:none"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
</button>
</div>
</div>
<div class="bam-field">
<label class="bam-label" for="bam_new_password2"><?php esc_html_e( 'Confirmar Contraseña', 'beforeaftermycare' ); ?></label>
<input class="bam-input" type="password" id="bam_new_password2" name="bam_new_password2" autocomplete="new-password" required minlength="8">
</div>
<button type="submit" name="bam_front_change_pass_submit" class="bam-btn bam-btn-primary" style="width:100%;">
<?php esc_html_e( 'Actualizar Contraseña', 'beforeaftermycare' ); ?>
</button>
</form>
</div>

<!-- Actions card -->
<div class="bam-card bam-card-danger">
<div class="bam-card-header">
<h2 class="bam-card-title"><?php esc_html_e( 'Acciones', 'beforeaftermycare' ); ?></h2>
</div>
<div class="bam-danger-actions">
<a href="<?php echo esc_url( add_query_arg( array( 'bam_front_action' => 'toggle', 'bam_id' => $edit_id, 'bam_nonce' => wp_create_nonce( 'bam_front_toggle' ) ), $base_url ) ); ?>"
class="bam-btn bam-btn-warning bam-btn-block bam-confirm-toggle">
<?php echo $edit_patient->estado ? esc_html__( 'Desactivar Paciente', 'beforeaftermycare' ) : esc_html__( 'Activar Paciente', 'beforeaftermycare' ); ?>
</a>
<a href="<?php echo esc_url( add_query_arg( array( 'bam_front_action' => 'delete', 'bam_id' => $edit_id, 'bam_nonce' => wp_create_nonce( 'bam_front_delete' ) ), $base_url ) ); ?>"
class="bam-btn bam-btn-danger bam-btn-block bam-confirm-delete">
<?php esc_html_e( 'Eliminar Paciente', 'beforeaftermycare' ); ?>
</a>
</div>
</div>

</div>
</div><!-- /.bam-detail-grid -->

<?php else : ?>
<!-- ─── LIST VIEW ─────────────────────────────────────────────────── -->

<div class="bam-page-header">
<div>
<h1 class="bam-page-title"><?php esc_html_e( 'Pacientes', 'beforeaftermycare' ); ?></h1>
<p class="bam-page-desc"><?php printf( esc_html__( '%s pacientes registrados en total.', 'beforeaftermycare' ), esc_html( number_format_i18n( $total_all ) ) ); ?></p>
</div>
</div>

<!-- Search -->
<form class="bam-search-form" method="get" action="<?php echo esc_url( $base_url ); ?>">
<input type="hidden" name="bam_section" value="pacientes">
<div class="bam-search-row">
<input
class="bam-input bam-search-input"
type="search"
name="s"
value="<?php echo esc_attr( $search ); ?>"
placeholder="<?php esc_attr_e( 'Buscar por nombre, usuario o correo…', 'beforeaftermycare' ); ?>"
>
<button type="submit" class="bam-btn bam-btn-primary"><?php esc_html_e( 'Buscar', 'beforeaftermycare' ); ?></button>
<?php if ( $search ) : ?>
<a class="bam-btn bam-btn-outline" href="<?php echo esc_url( bam_section_url( 'pacientes', $base_url ) ); ?>"><?php esc_html_e( 'Limpiar', 'beforeaftermycare' ); ?></a>
<?php endif; ?>
</div>
</form>

<div class="bam-card">
<div class="bam-card-header">
<span class="bam-result-count">
<?php printf( esc_html( _n( '%s paciente encontrado', '%s pacientes encontrados', $total, 'beforeaftermycare' ) ), esc_html( number_format_i18n( $total ) ) ); ?>
</span>
</div>

<?php if ( empty( $patients ) ) : ?>
<div class="bam-empty-state">
<svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true" style="color:#cbd5e1;margin-bottom:12px;"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
<p><?php esc_html_e( 'No se encontraron pacientes.', 'beforeaftermycare' ); ?></p>
</div>
<?php else : ?>
<div class="bam-table-wrapper">
<table class="bam-table">
<thead>
<tr>
<th><?php esc_html_e( 'ID', 'beforeaftermycare' ); ?></th>
<th><?php esc_html_e( 'Nombre', 'beforeaftermycare' ); ?></th>
<th><?php esc_html_e( 'Usuario', 'beforeaftermycare' ); ?></th>
<th><?php esc_html_e( 'Correo', 'beforeaftermycare' ); ?></th>
<th><?php esc_html_e( 'Teléfono', 'beforeaftermycare' ); ?></th>
<th><?php esc_html_e( 'Guía', 'beforeaftermycare' ); ?></th>
<th><?php esc_html_e( 'Fecha', 'beforeaftermycare' ); ?></th>
<th><?php esc_html_e( 'Estado', 'beforeaftermycare' ); ?></th>
<th><?php esc_html_e( 'Acciones', 'beforeaftermycare' ); ?></th>
</tr>
</thead>
<tbody>
<?php foreach ( $patients as $patient ) :
$toggle_url = add_query_arg( array( 'bam_front_action' => 'toggle', 'bam_id' => $patient->id, 'bam_nonce' => wp_create_nonce( 'bam_front_toggle' ) ), $base_url );
$delete_url = add_query_arg( array( 'bam_front_action' => 'delete', 'bam_id' => $patient->id, 'bam_nonce' => wp_create_nonce( 'bam_front_delete' ) ), $base_url );
$edit_url   = add_query_arg( array( 'bam_edit' => $patient->id, 'bam_section' => 'pacientes' ), $base_url );
?>
<tr>
<td><?php echo esc_html( $patient->id ); ?></td>
<td>
<a href="<?php echo esc_url( $edit_url ); ?>" class="bam-link"><?php echo esc_html( $patient->nombre ); ?></a>
</td>
<td><?php echo esc_html( $patient->usuario ); ?></td>
<td><?php echo esc_html( $patient->correo ); ?></td>
<td><?php echo $patient->telefono ? esc_html( $patient->telefono ) : '—'; ?></td>
<td><?php echo $patient->guia_asignada ? esc_html( $patient->guia_asignada ) : '—'; ?></td>
<td><?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $patient->fecha_registro ) ) ); ?></td>
<td>
<span class="bam-badge <?php echo $patient->estado ? 'bam-badge-success' : 'bam-badge-inactive'; ?>">
<?php echo $patient->estado ? esc_html__( 'Activo', 'beforeaftermycare' ) : esc_html__( 'Inactivo', 'beforeaftermycare' ); ?>
</span>
</td>
<td class="bam-actions-cell">
<a href="<?php echo esc_url( $edit_url ); ?>" class="bam-btn bam-btn-xs bam-btn-outline"><?php esc_html_e( 'Editar', 'beforeaftermycare' ); ?></a>
<a href="<?php echo esc_url( $toggle_url ); ?>" class="bam-btn bam-btn-xs bam-btn-warning bam-confirm-toggle"><?php echo $patient->estado ? esc_html__( 'Desactivar', 'beforeaftermycare' ) : esc_html__( 'Activar', 'beforeaftermycare' ); ?></a>
<a href="<?php echo esc_url( $delete_url ); ?>" class="bam-btn bam-btn-xs bam-btn-danger bam-confirm-delete"><?php esc_html_e( 'Eliminar', 'beforeaftermycare' ); ?></a>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
<?php if ( $num_pages > 1 ) : ?>
<div class="bam-pagination">
<?php for ( $p = 1; $p <= $num_pages; $p++ ) : ?>
<a class="bam-page-btn <?php echo $p === $page ? 'bam-page-current' : ''; ?>"
   href="<?php echo esc_url( add_query_arg( array( 'bam_section' => 'pacientes', 'paged' => $p, 's' => $search ), $base_url ) ); ?>">
<?php echo esc_html( $p ); ?>
</a>
<?php endfor; ?>
</div>
<?php endif; ?>
<?php endif; ?>
</div>

<?php endif; // end edit / list ?>
<?php endif; // end section switch ?>

</main>
</div><!-- /.bam-body -->
</div><!-- /.bam-app -->

<div class="bam-sidebar-overlay" id="bamSidebarOverlay"></div>

<script>
(function () {
'use strict';

document.addEventListener('DOMContentLoaded', function () {

// ── Sidebar toggle (mobile) ──────────────────────────────────
var toggle  = document.getElementById('bamSidebarToggle');
var sidebar = document.getElementById('bamSidebar');
var overlay = document.getElementById('bamSidebarOverlay');

function openSidebar() {
sidebar.classList.add('bam-sidebar-open');
overlay.classList.add('bam-overlay-visible');
toggle.setAttribute('aria-expanded', 'true');
}
function closeSidebar() {
sidebar.classList.remove('bam-sidebar-open');
overlay.classList.remove('bam-overlay-visible');
toggle.setAttribute('aria-expanded', 'false');
}
if (toggle) {
toggle.addEventListener('click', function () {
sidebar.classList.contains('bam-sidebar-open') ? closeSidebar() : openSidebar();
});
}
if (overlay) {
overlay.addEventListener('click', closeSidebar);
}

// ── Password toggle ──────────────────────────────────────────
document.querySelectorAll('.bam-toggle-pass').forEach(function (btn) {
btn.addEventListener('click', function () {
var targetId = btn.getAttribute('data-target') || 'bam_password';
var input    = document.getElementById(targetId);
var show     = btn.querySelector('.bam-eye-show');
var hide     = btn.querySelector('.bam-eye-hide');
if (!input) return;
if (input.type === 'password') {
input.type = 'text';
if (show) show.style.display = 'none';
if (hide) hide.style.display = '';
} else {
input.type = 'password';
if (show) show.style.display = '';
if (hide) hide.style.display = 'none';
}
});
});

// ── Confirm delete ───────────────────────────────────────────
document.querySelectorAll('.bam-confirm-delete').forEach(function (el) {
el.addEventListener('click', function (e) {
if (!window.confirm('<?php echo esc_js( __( '¿Eliminar este paciente? Esta acción no se puede deshacer.', 'beforeaftermycare' ) ); ?>')) {
e.preventDefault();
}
});
});

// ── Confirm toggle ───────────────────────────────────────────
document.querySelectorAll('.bam-confirm-toggle').forEach(function (el) {
el.addEventListener('click', function (e) {
if (!window.confirm('<?php echo esc_js( __( '¿Cambiar el estado de este paciente?', 'beforeaftermycare' ) ); ?>')) {
e.preventDefault();
}
});
});

// ── Auto-dismiss notices ─────────────────────────────────────
var notices = document.querySelectorAll('.bam-notice');
if (notices.length) {
setTimeout(function () {
notices.forEach(function (el) {
el.style.transition = 'opacity 0.5s';
el.style.opacity = '0';
setTimeout(function () { if (el.parentNode) el.parentNode.removeChild(el); }, 500);
});
}, 5000);
}
});
}());
</script>
</body>
</html>
