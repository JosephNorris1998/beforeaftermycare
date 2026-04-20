=== Before After My Care – Guías Médicas ===
Contributors:      beforeaftermycare
Tags:              medical guides, patient registration, colonoscopy, endoscopy, healthcare
Requires at least: 5.8
Tested up to:      6.5
Stable tag:        1.0.0
Requires PHP:      7.4
License:           GPL-2.0-or-later
License URI:       https://www.gnu.org/licenses/gpl-2.0.html

Sistema completo de guías médicas con registro de pacientes y dashboard de administración.

== Description ==

**Before After My Care – Guías Médicas** es un plugin para WordPress diseñado para
clínicas y centros médicos que deseen guiar a sus pacientes a través de procedimientos
médicos (colonoscopia, endoscopia, etc.).

= Características principales =

* **Registro de Pacientes** — Formulario en `/registro/` con campos: Nombre Completo,
  Usuario, Contraseña, Correo y Teléfono (opcional).
* **Redirección automática** — Tras el registro, el paciente es redirigido directamente
  a su guía médica asignada.
* **Dashboard de Administración** — Panel independiente dentro del admin de WordPress
  con su propio diseño, accesible en *Guías Médicas* del menú lateral.
* **Gestión de Pacientes** — Lista paginada, búsqueda, edición, activar/desactivar y
  eliminación de pacientes.
* **Sincronización con WordPress** — Cada paciente registrado crea automáticamente un
  usuario de WordPress.

= Shortcode =

Añade el shortcode `[bam_registro]` en cualquier página de WordPress para mostrar el
formulario de registro.

Ejemplo: crea una página con slug `/registro/` e inserta `[bam_registro]` en su
contenido.

== Installation ==

1. Sube la carpeta `beforeaftermycare` al directorio `/wp-content/plugins/`.
2. Activa el plugin desde el menú *Plugins* en WordPress.
3. El plugin creará automáticamente la tabla de pacientes en la base de datos.
4. Crea una página con el slug `registro` e inserta el shortcode `[bam_registro]`.
5. Accede al dashboard en **Guías Médicas** en el menú lateral del administrador.

== Frequently Asked Questions ==

= ¿Dónde se guardan los datos de los pacientes? =

En la tabla `wp_bam_patients` de la base de datos de WordPress, además del usuario
estándar de WordPress que se crea automáticamente.

= ¿Puedo cambiar la URL de redirección después del registro? =

Sí, edita la constante `BAM_REDIRECT_URL` en el archivo principal `beforeaftermycare.php`.

= ¿El plugin es compatible con multisite? =

La versión actual está diseñada para instalaciones de un solo sitio.

== Changelog ==

= 1.0.0 =
* Lanzamiento inicial.
* Formulario de registro con validación completa.
* Tabla `wp_bam_patients` en la base de datos.
* Dashboard administrativo con estadísticas y gestión de pacientes.
* Sincronización automática con usuarios de WordPress.

== Upgrade Notice ==

= 1.0.0 =
Primera versión estable.
