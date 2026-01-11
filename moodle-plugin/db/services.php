<?php
/**
 * Web service definitions for local_certificados_sso
 *
 * @package    local_certificados_sso
 * @copyright  2026 ACG Calidad
 * @author     Oliver Castelblanco
 * @license    Proprietary
 */

defined('MOODLE_INTERNAL') || die();

$functions = [
    // Generar token SSO
    'local_certificados_sso_generate_token' => [
        'classname'   => 'local_certificados_sso\external\generate_token',
        'methodname'  => 'execute',
        'description' => 'Genera un token temporal para SSO',
        'type'        => 'read',
        'ajax'        => true,
        'capabilities'=> 'local/certificados_sso:generatetoken',
        'services'    => [MOODLE_OFFICIAL_MOBILE_SERVICE],
    ],

    // Validar token SSO
    'local_certificados_sso_validate_token' => [
        'classname'   => 'local_certificados_sso\external\validate_token',
        'methodname'  => 'execute',
        'description' => 'Valida un token temporal y retorna información del usuario',
        'type'        => 'read',
        'ajax'        => true,
        'capabilities'=> 'local/certificados_sso:validatetoken',
        'services'    => [MOODLE_OFFICIAL_MOBILE_SERVICE],
        'loginrequired' => false, // Permite validación sin estar logueado en Moodle
    ],
];

// Definir servicios personalizados
$services = [
    'ACG Certificados SSO' => [
        'functions' => [
            'local_certificados_sso_generate_token',
            'local_certificados_sso_validate_token',
        ],
        'restrictedusers' => 0,
        'enabled' => 1,
        'shortname' => 'acg_certificados_sso',
        'downloadfiles' => 0,
        'uploadfiles' => 0,
    ],
];
