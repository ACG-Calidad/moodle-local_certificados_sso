<?php
/**
 * Definición de tareas programadas
 *
 * @package    local_certificados_sso
 * @copyright  2026 ACG Calidad
 * @author     Oliver Castelblanco
 * @license    Proprietary
 */

defined('MOODLE_INTERNAL') || die();

$tasks = [
    [
        'classname' => 'local_certificados_sso\task\cleanup_expired_tokens',
        'blocking' => 0,
        'minute' => '*/15',  // Cada 15 minutos
        'hour' => '*',
        'day' => '*',
        'dayofweek' => '*',
        'month' => '*',
    ],
];
