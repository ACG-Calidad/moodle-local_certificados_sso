<?php
/**
 * Tarea programada para limpiar tokens expirados
 *
 * @package    local_certificados_sso
 * @copyright  2026 ACG Calidad
 * @author     Oliver Castelblanco
 * @license    Proprietary
 */

namespace local_certificados_sso\task;

defined('MOODLE_INTERNAL') || die();

/**
 * Tarea programada para limpiar tokens expirados y usados
 */
class cleanup_expired_tokens extends \core\task\scheduled_task {

    /**
     * Obtener nombre de la tarea
     *
     * @return string
     */
    public function get_name() {
        return get_string('taskcleanuptokens', 'local_certificados_sso');
    }

    /**
     * Ejecutar la tarea
     */
    public function execute() {
        require_once(__DIR__ . '/../../lib.php');

        // Limpiar tokens expirados
        $deleted = local_certificados_sso_cleanup_expired_tokens();

        // Log del resultado
        if ($deleted > 0) {
            mtrace("Limpieza de tokens SSO completada: {$deleted} tokens eliminados");
        } else {
            mtrace("Limpieza de tokens SSO: no hay tokens para eliminar");
        }
    }
}
