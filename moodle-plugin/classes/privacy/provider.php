<?php
/**
 * Privacy Subsystem implementation for local_certificados_sso
 *
 * @package    local_certificados_sso
 * @copyright  2026 ACG Calidad
 * @author     Oliver Castelblanco
 * @license    Proprietary
 */

namespace local_certificados_sso\privacy;

defined('MOODLE_INTERNAL') || die();

use core_privacy\local\metadata\collection;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\writer;

/**
 * Provider para el subsistema de privacidad
 */
class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\plugin\provider {

    /**
     * Retorna metadata sobre los datos almacenados
     *
     * @param collection $collection
     * @return collection
     */
    public static function get_metadata(collection $collection): collection {
        $collection->add_database_table(
            'local_certsso_tokens',
            [
                'userid' => 'privacy:metadata:local_certsso_tokens:userid',
                'token' => 'privacy:metadata:local_certsso_tokens:token',
                'timecreated' => 'privacy:metadata:local_certsso_tokens:timecreated',
                'timeexpires' => 'privacy:metadata:local_certsso_tokens:timeexpires',
                'ipaddress' => 'privacy:metadata:local_certsso_tokens:ipaddress',
                'useragent' => 'privacy:metadata:local_certsso_tokens:useragent',
            ],
            'privacy:metadata:local_certsso_tokens'
        );

        return $collection;
    }

    /**
     * Obtener lista de contextos con datos de usuario
     *
     * @param int $userid
     * @return contextlist
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        $contextlist = new contextlist();

        // Los tokens se almacenan a nivel de sistema, no de usuario específico
        $sql = "SELECT DISTINCT ctx.id
                  FROM {context} ctx
                  JOIN {local_certsso_tokens} t ON t.userid = :userid
                 WHERE ctx.contextlevel = :contextlevel";

        $params = [
            'userid' => $userid,
            'contextlevel' => CONTEXT_SYSTEM,
        ];

        $contextlist->add_from_sql($sql, $params);
        return $contextlist;
    }

    /**
     * Exportar datos de usuario
     *
     * @param approved_contextlist $contextlist
     */
    public static function export_user_data(approved_contextlist $contextlist) {
        global $DB;

        $userid = $contextlist->get_user()->id;

        // Obtener tokens del usuario
        $tokens = $DB->get_records('local_certsso_tokens', ['userid' => $userid]);

        if (!empty($tokens)) {
            $data = [];
            foreach ($tokens as $token) {
                $data[] = (object)[
                    'token' => 'REDACTED',  // No exportar el token real por seguridad
                    'timecreated' => transform::datetime($token->timecreated),
                    'timeexpires' => transform::datetime($token->timeexpires),
                    'used' => $token->used ? get_string('yes') : get_string('no'),
                    'ipaddress' => $token->ipaddress,
                    'useragent' => $token->useragent,
                ];
            }

            writer::with_context($contextlist->current())->export_data(
                [get_string('pluginname', 'local_certificados_sso')],
                (object)['tokens' => $data]
            );
        }
    }

    /**
     * Eliminar datos de usuario
     *
     * @param approved_contextlist $contextlist
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {
        global $DB;

        $userid = $contextlist->get_user()->id;
        $DB->delete_records('local_certsso_tokens', ['userid' => $userid]);
    }

    /**
     * Eliminar datos para todos los usuarios en contexto
     *
     * @param \context $context
     */
    public static function delete_data_for_all_users_in_context(\context $context) {
        global $DB;

        // Solo limpiar si es contexto de sistema
        if ($context->contextlevel == CONTEXT_SYSTEM) {
            $DB->delete_records('local_certsso_tokens');
        }
    }
}
