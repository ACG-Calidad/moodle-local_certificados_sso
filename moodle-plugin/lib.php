<?php
/**
 * Library functions for local_certificados_sso
 *
 * @package    local_certificados_sso
 * @copyright  2026 ACG Calidad
 * @author     Oliver Castelblanco
 * @license    Proprietary
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Extender la navegación global para agregar enlace a certificados
 *
 * @param global_navigation $navigation El objeto de navegación global
 */
function local_certificados_sso_extend_navigation(global_navigation $navigation) {
    global $USER, $CFG, $PAGE;

    // Solo mostrar para usuarios autenticados (no invitados)
    if (!isloggedin() || isguestuser()) {
        return;
    }

    // Debug: verificar que la función se está llamando (deshabilitado)
    // debugging('Plugin certificados_sso: extend_navigation llamado para user ' . $USER->id, DEBUG_DEVELOPER);

    // URL de la aplicación de certificados desde configuración
    $app_url = get_config('local_certificados_sso', 'app_url');
    $dev_url = get_config('local_certificados_sso', 'dev_url');

    // Si no hay configuración, usar valores por defecto
    if (empty($app_url)) {
        $app_url = 'https://aulavirtual.acgcalidad.co/certificados/';
    }
    if (empty($dev_url)) {
        $dev_url = 'http://localhost:4200/';
    }

    // En modo debug, usar URL de desarrollo
    $debug_mode = get_config('local_certificados_sso', 'debug_mode');
    if ($debug_mode) {
        $app_url = $dev_url;
    }

    // Generar token temporal
    try {
        $token = local_certificados_sso_generate_token($USER->id);
        $redirect_url = $app_url . '?moodle_token=' . $token;
    } catch (Exception $e) {
        // Si falla la generación del token, usar URL sin SSO
        $redirect_url = $app_url;
        debugging('Error generando token SSO: ' . $e->getMessage(), DEBUG_DEVELOPER);
    }

    // Buscar el nodo raíz (home) para agregar después de "Área personal"
    $homenode = $navigation->find('home', navigation_node::TYPE_SYSTEM);

    // Agregar nodo a la navegación principal
    $node = navigation_node::create(
        get_string('mycertificates', 'local_certificados_sso'),
        new moodle_url($redirect_url),
        navigation_node::TYPE_CUSTOM,
        null,
        'local_certificados_sso',
        new pix_icon('i/badge', get_string('mycertificates', 'local_certificados_sso'))
    );

    if ($node) {
        // Forzar que sea visible en la navegación
        $node->showinflatnavigation = true;
        $node->add_class('certificados-sso-link');

        // Agregarlo a la navegación principal
        if ($homenode) {
            $navigation->add_node($node, 'mycourses'); // Agregar antes de "Mis cursos"
        } else {
            $navigation->add_node($node);
        }
    }
}

/**
 * Hook para agregar elementos a la navegación primaria (Boost theme)
 * Este método funciona mejor con temas Boost y Boost Union
 *
 * @return void
 */
function local_certificados_sso_before_standard_top_of_body_html() {
    global $USER, $PAGE, $OUTPUT;

    // Solo mostrar para usuarios autenticados (no invitados)
    if (!isloggedin() || isguestuser()) {
        return '';
    }

    // URL de la aplicación de certificados desde configuración
    $app_url = get_config('local_certificados_sso', 'app_url');
    $dev_url = get_config('local_certificados_sso', 'dev_url');

    // Si no hay configuración, usar valores por defecto
    if (empty($app_url)) {
        $app_url = 'https://aulavirtual.acgcalidad.co/certificados/';
    }
    if (empty($dev_url)) {
        $dev_url = 'http://localhost:4200/';
    }

    // En modo debug, usar URL de desarrollo
    $debug_mode = get_config('local_certificados_sso', 'debug_mode');
    if ($debug_mode) {
        $app_url = $dev_url;
    }

    // Generar token temporal
    try {
        $token = local_certificados_sso_generate_token($USER->id);
        $redirect_url = $app_url . '?moodle_token=' . $token;
    } catch (Exception $e) {
        // Si falla la generación del token, usar URL sin SSO
        $redirect_url = $app_url;
    }

    // Inyectar JavaScript para agregar el enlace al menú de navegación de Boost
    $js = "
    <script>
    (function() {
        // Esperar a que el DOM esté listo
        document.addEventListener('DOMContentLoaded', function() {
            // Buscar la navegación primaria (primary-navigation)
            var nav = document.querySelector('.primary-navigation ul[role=\"menubar\"]');

            if (!nav) {
                // Intentar con otra estructura de Boost Union
                nav = document.querySelector('nav#primary-navigation ul');
            }

            if (!nav) {
                // Intentar con estructura de Boost estándar
                nav = document.querySelector('.navbar-nav');
            }

            if (nav) {
                // Crear el elemento de navegación
                var li = document.createElement('li');
                li.className = 'nav-item';

                var a = document.createElement('a');
                a.className = 'nav-link';
                a.href = '" . addslashes($redirect_url) . "';
                a.textContent = '" . addslashes(get_string('mycertificates', 'local_certificados_sso')) . "';
                a.title = '" . addslashes(get_string('mycertificates', 'local_certificados_sso')) . "';
                a.target = '_blank';
                a.rel = 'noopener noreferrer';

                li.appendChild(a);

                // Insertar después de 'Mis cursos' si existe
                var mycourses = null;
                var items = nav.querySelectorAll('li.nav-item');
                for (var i = 0; i < items.length; i++) {
                    var link = items[i].querySelector('a');
                    if (link && (link.textContent.includes('Mis cursos') || link.textContent.includes('My courses'))) {
                        mycourses = items[i];
                        break;
                    }
                }

                if (mycourses && mycourses.nextSibling) {
                    nav.insertBefore(li, mycourses.nextSibling);
                } else {
                    nav.appendChild(li);
                }
            }
        });
    })();
    </script>
    ";

    return $js;
}

/**
 * Extender la navegación del usuario para agregar enlace a certificados
 * Esta es otra forma de agregar el enlace que funciona mejor en algunos temas
 *
 * @param navigation_node $navigation El nodo de navegación
 * @param stdClass $user El objeto usuario
 * @param context_user $usercontext El contexto del usuario
 * @param stdClass $course El objeto curso
 * @param context_course $coursecontext El contexto del curso
 */
function local_certificados_sso_extend_navigation_user($navigation, $user, $usercontext, $course, $coursecontext) {
    global $USER, $CFG;

    // Solo mostrar para el usuario actual (no para otros perfiles)
    if ($USER->id != $user->id) {
        return;
    }

    // URL de la aplicación de certificados desde configuración
    $app_url = get_config('local_certificados_sso', 'app_url');
    $dev_url = get_config('local_certificados_sso', 'dev_url');

    // Si no hay configuración, usar valores por defecto
    if (empty($app_url)) {
        $app_url = 'https://aulavirtual.acgcalidad.co/certificados/';
    }
    if (empty($dev_url)) {
        $dev_url = 'http://localhost:4200/';
    }

    // En modo debug, usar URL de desarrollo
    $debug_mode = get_config('local_certificados_sso', 'debug_mode');
    if ($debug_mode) {
        $app_url = $dev_url;
    }

    // Generar token temporal
    try {
        $token = local_certificados_sso_generate_token($USER->id);
        $redirect_url = $app_url . '?moodle_token=' . $token;
    } catch (Exception $e) {
        // Si falla la generación del token, usar URL sin SSO
        $redirect_url = $app_url;
        debugging('Error generando token SSO: ' . $e->getMessage(), DEBUG_DEVELOPER);
    }

    // Agregar nodo a la navegación del usuario
    $node = $navigation->add(
        get_string('mycertificates', 'local_certificados_sso'),
        new moodle_url($redirect_url),
        navigation_node::TYPE_CUSTOM,
        null,
        'certificados',
        new pix_icon('i/badge', get_string('mycertificates', 'local_certificados_sso'))
    );
}

/**
 * Generar token temporal para SSO
 *
 * @param int $userid ID del usuario
 * @return string Token generado
 * @throws dml_exception
 */
function local_certificados_sso_generate_token($userid) {
    global $DB;

    // Generar token aleatorio seguro (32 bytes = 64 caracteres hex)
    $token = bin2hex(random_bytes(32));

    // Calcular tiempo de expiración (5 minutos)
    $timecreated = time();
    $timeexpires = $timecreated + 300; // 5 minutos

    // Obtener información del cliente
    $ipaddress = getremoteaddr();
    $useragent = $_SERVER['HTTP_USER_AGENT'] ?? '';

    // Insertar token en la base de datos
    $record = new stdClass();
    $record->token = $token;
    $record->userid = $userid;
    $record->timecreated = $timecreated;
    $record->timeexpires = $timeexpires;
    $record->used = 0;
    $record->ipaddress = $ipaddress;
    $record->useragent = substr($useragent, 0, 255); // Truncar si es muy largo

    $DB->insert_record('local_certsso_tokens', $record);

    return $token;
}

/**
 * Validar token temporal
 *
 * @param string $token Token a validar
 * @return stdClass|false Objeto con información del usuario o false si es inválido
 * @throws dml_exception
 */
function local_certificados_sso_validate_token($token) {
    global $DB;

    // Buscar token en la base de datos
    $record = $DB->get_record('local_certsso_tokens', ['token' => $token]);

    if (!$record) {
        return false;
    }

    // Verificar si ya fue usado
    if ($record->used) {
        return false;
    }

    // Verificar si expiró
    if ($record->timeexpires < time()) {
        return false;
    }

    // Obtener información del usuario
    $user = $DB->get_record('user', ['id' => $record->userid]);

    if (!$user) {
        return false;
    }

    // Marcar token como usado
    $record->used = 1;
    $DB->update_record('local_certsso_tokens', $record);

    // Determinar rol del usuario
    $role = local_certificados_sso_get_user_role($user->id);

    // Retornar información del usuario
    return (object)[
        'valid' => true,
        'userid' => $user->id,
        'username' => $user->username,
        'firstname' => $user->firstname,
        'lastname' => $user->lastname,
        'email' => $user->email,
        'role' => $role
    ];
}

/**
 * Determinar el rol del usuario para la aplicación de certificados
 *
 * @param int $userid ID del usuario
 * @return string Rol: 'admin', 'gestor' o 'participante'
 */
function local_certificados_sso_get_user_role($userid) {
    global $DB;

    // Verificar si es administrador del sitio
    $context = context_system::instance();
    if (has_capability('moodle/site:config', $context, $userid)) {
        return 'admin';
    }

    // Verificar si es manager (gestor)
    if (has_capability('moodle/site:manageblocks', $context, $userid)) {
        return 'gestor';
    }

    // Por defecto, es participante
    return 'participante';
}

/**
 * Limpiar tokens expirados (llamado por tarea programada)
 *
 * @return int Número de tokens eliminados
 * @throws dml_exception
 */
function local_certificados_sso_cleanup_expired_tokens() {
    global $DB;

    $now = time();

    // Eliminar tokens expirados
    $expired = $DB->count_records_select(
        'local_certsso_tokens',
        'timeexpires < :now',
        ['now' => $now]
    );

    $DB->delete_records_select(
        'local_certsso_tokens',
        'timeexpires < :now',
        ['now' => $now]
    );

    // Eliminar tokens usados con más de 7 días
    $sevendaysago = $now - (7 * 24 * 60 * 60);

    $old_used = $DB->count_records_select(
        'local_certsso_tokens',
        'used = 1 AND timecreated < :time',
        ['time' => $sevendaysago]
    );

    $DB->delete_records_select(
        'local_certsso_tokens',
        'used = 1 AND timecreated < :time',
        ['time' => $sevendaysago]
    );

    return $expired + $old_used;
}
