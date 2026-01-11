<?php
/**
 * Settings para local_certificados_sso
 *
 * @package    local_certificados_sso
 * @copyright  2026 ACG Calidad
 * @author     Oliver Castelblanco
 * @license    Proprietary
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $settings = new admin_settingpage('local_certificados_sso', get_string('pluginname', 'local_certificados_sso'));

    // URL de la aplicación de certificados (producción)
    $settings->add(new admin_setting_configtext(
        'local_certificados_sso/app_url',
        get_string('appurl', 'local_certificados_sso'),
        get_string('appurl_desc', 'local_certificados_sso'),
        'https://aulavirtual.acgcalidad.co/certificados/',
        PARAM_URL
    ));

    // URL de desarrollo (localhost)
    $settings->add(new admin_setting_configtext(
        'local_certificados_sso/dev_url',
        get_string('devurl', 'local_certificados_sso'),
        get_string('devurl_desc', 'local_certificados_sso'),
        'http://localhost:4200/',
        PARAM_URL
    ));

    // Tiempo de expiración del token (en segundos)
    $settings->add(new admin_setting_configtext(
        'local_certificados_sso/token_expiry',
        get_string('tokenexpiry', 'local_certificados_sso'),
        get_string('tokenexpiry_desc', 'local_certificados_sso'),
        '300',
        PARAM_INT
    ));

    // Habilitar modo debug
    $settings->add(new admin_setting_configcheckbox(
        'local_certificados_sso/debug_mode',
        get_string('debugmode', 'local_certificados_sso'),
        get_string('debugmode_desc', 'local_certificados_sso'),
        '0'
    ));

    $ADMIN->add('localplugins', $settings);
}
