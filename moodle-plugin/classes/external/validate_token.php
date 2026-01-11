<?php
/**
 * External API para validar tokens SSO
 *
 * @package    local_certificados_sso
 * @copyright  2026 ACG Calidad
 * @author     Oliver Castelblanco
 * @license    Proprietary
 */

namespace local_certificados_sso\external;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/externallib.php');

use external_api;
use external_function_parameters;
use external_value;
use external_single_structure;
use context_system;

/**
 * Clase para validar tokens SSO vía Web Service
 */
class validate_token extends external_api {

    /**
     * Definir parámetros de entrada
     *
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'token' => new external_value(
                PARAM_TEXT,
                'Token a validar',
                VALUE_REQUIRED
            ),
        ]);
    }

    /**
     * Validar token SSO
     *
     * @param string $token Token a validar
     * @return array Con información del usuario si el token es válido
     */
    public static function execute($token) {
        global $DB;

        // Validar parámetros
        $params = self::validate_parameters(
            self::execute_parameters(),
            ['token' => $token]
        );

        // Verificar contexto (no requiere login)
        $context = context_system::instance();
        self::validate_context($context);

        // Validar el token usando la función de lib.php
        require_once(__DIR__ . '/../../lib.php');
        $userinfo = local_certificados_sso_validate_token($params['token']);

        // Si el token no es válido, retornar error
        if (!$userinfo) {
            return [
                'valid' => false,
                'error' => 'Token inválido, expirado o ya utilizado',
                'userid' => 0,
                'username' => '',
                'firstname' => '',
                'lastname' => '',
                'email' => '',
                'role' => '',
            ];
        }

        // Retornar información del usuario
        return [
            'valid' => true,
            'error' => '',
            'userid' => $userinfo->userid,
            'username' => $userinfo->username,
            'firstname' => $userinfo->firstname,
            'lastname' => $userinfo->lastname,
            'email' => $userinfo->email,
            'role' => $userinfo->role,
        ];
    }

    /**
     * Definir estructura de retorno
     *
     * @return external_single_structure
     */
    public static function execute_returns() {
        return new external_single_structure([
            'valid' => new external_value(PARAM_BOOL, 'Indica si el token es válido'),
            'error' => new external_value(PARAM_TEXT, 'Mensaje de error si el token no es válido'),
            'userid' => new external_value(PARAM_INT, 'ID del usuario'),
            'username' => new external_value(PARAM_TEXT, 'Nombre de usuario'),
            'firstname' => new external_value(PARAM_TEXT, 'Nombre'),
            'lastname' => new external_value(PARAM_TEXT, 'Apellido'),
            'email' => new external_value(PARAM_TEXT, 'Correo electrónico'),
            'role' => new external_value(PARAM_TEXT, 'Rol del usuario (admin, gestor, participante)'),
        ]);
    }
}
