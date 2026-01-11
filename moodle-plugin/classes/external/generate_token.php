<?php
/**
 * External API para generar tokens SSO
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
 * Clase para generar tokens SSO vía Web Service
 */
class generate_token extends external_api {

    /**
     * Definir parámetros de entrada
     *
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'userid' => new external_value(
                PARAM_INT,
                'ID del usuario (opcional, usa el usuario actual si no se especifica)',
                VALUE_DEFAULT,
                0
            ),
        ]);
    }

    /**
     * Generar token SSO
     *
     * @param int $userid ID del usuario (0 = usuario actual)
     * @return array Con el token generado y tiempo de expiración
     */
    public static function execute($userid = 0) {
        global $USER;

        // Validar parámetros
        $params = self::validate_parameters(
            self::execute_parameters(),
            ['userid' => $userid]
        );

        // Si no se especifica userid, usar el usuario actual
        if ($params['userid'] == 0) {
            $params['userid'] = $USER->id;
        }

        // Verificar contexto y permisos
        $context = context_system::instance();
        self::validate_context($context);

        // Verificar que el usuario tenga permiso para generar tokens
        require_capability('local/certificados_sso:generatetoken', $context);

        // Solo permitir generar tokens para uno mismo (excepto administradores)
        if ($params['userid'] != $USER->id && !has_capability('local/certificados_sso:manage', $context)) {
            throw new \moodle_exception(
                'nopermissiontogeneratetoken',
                'local_certificados_sso',
                '',
                null,
                'Solo puedes generar tokens para ti mismo'
            );
        }

        // Generar el token usando la función de lib.php
        require_once(__DIR__ . '/../../lib.php');
        $token = local_certificados_sso_generate_token($params['userid']);

        // Calcular tiempo de expiración
        $timeexpires = time() + 300; // 5 minutos

        return [
            'success' => true,
            'token' => $token,
            'expires_in' => 300,
            'expires_at' => $timeexpires,
            'userid' => $params['userid'],
        ];
    }

    /**
     * Definir estructura de retorno
     *
     * @return external_single_structure
     */
    public static function execute_returns() {
        return new external_single_structure([
            'success' => new external_value(PARAM_BOOL, 'Indica si la operación fue exitosa'),
            'token' => new external_value(PARAM_TEXT, 'Token generado'),
            'expires_in' => new external_value(PARAM_INT, 'Tiempo de expiración en segundos'),
            'expires_at' => new external_value(PARAM_INT, 'Timestamp de expiración'),
            'userid' => new external_value(PARAM_INT, 'ID del usuario'),
        ]);
    }
}
