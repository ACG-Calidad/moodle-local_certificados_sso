<?php
/**
 * Language strings for local_certificados_sso
 *
 * @package    local_certificados_sso
 * @copyright  2026 ACG Calidad
 * @author     Oliver Castelblanco
 * @license    Proprietary
 */

defined('MOODLE_INTERNAL') || die();

// Plugin general
$string['pluginname'] = 'Certificados SSO';
$string['plugindescription'] = 'Plugin para Single Sign-On hacia la aplicación de gestión de certificados ACG';

// Navigation
$string['mycertificates'] = 'Mis Certificados';
$string['mycertificates_link'] = 'Ver mis certificados';

// Web Services
$string['generate_token'] = 'Generar token SSO';
$string['validate_token'] = 'Validar token SSO';

// Errors
$string['error:tokennotfound'] = 'Token no encontrado o expirado';
$string['error:tokenexpired'] = 'El token ha expirado. Por favor, intente nuevamente.';
$string['error:tokenused'] = 'Este token ya ha sido utilizado';
$string['error:invalidtoken'] = 'Token inválido';
$string['error:usernotfound'] = 'Usuario no encontrado';
$string['error:notloggedin'] = 'Debe iniciar sesión para acceder a sus certificados';

// Task
$string['task:cleanupexpiredtokens'] = 'Limpiar tokens SSO expirados';
$string['task:cleanupexpiredtokens_desc'] = 'Elimina tokens SSO expirados y usados de la base de datos';
$string['taskcleanuptokens'] = 'Limpiar tokens SSO expirados';

// Capabilities
$string['certificados_sso:generate'] = 'Generar token SSO para certificados';
$string['certificados_sso:validate'] = 'Validar tokens SSO';
$string['certificados_sso:generatetoken'] = 'Generar tokens SSO';
$string['certificados_sso:validatetoken'] = 'Validar tokens SSO';
$string['certificados_sso:manage'] = 'Administrar plugin Certificados SSO';

// Settings
$string['settings:appurl'] = 'URL de la aplicación de certificados';
$string['settings:appurl_desc'] = 'URL completa de la aplicación de certificados (ej: https://aulavirtual.acgcalidad.co/certificados)';
$string['settings:tokenttl'] = 'Tiempo de vida del token (segundos)';
$string['settings:tokenttl_desc'] = 'Tiempo en segundos que un token es válido antes de expirar. Por defecto: 300 (5 minutos)';
$string['appurl'] = 'URL de la aplicación (producción)';
$string['appurl_desc'] = 'URL de la aplicación de certificados en producción';
$string['devurl'] = 'URL de desarrollo';
$string['devurl_desc'] = 'URL de la aplicación de certificados en ambiente de desarrollo (localhost)';
$string['tokenexpiry'] = 'Tiempo de expiración del token (segundos)';
$string['tokenexpiry_desc'] = 'Tiempo en segundos antes de que expire un token SSO (por defecto: 300 = 5 minutos)';
$string['debugmode'] = 'Modo debug';
$string['debugmode_desc'] = 'Activar modo debug para registro detallado de tokens';

// More errors
$string['nopermissiontogeneratetoken'] = 'No tienes permiso para generar tokens para otros usuarios';

// Privacy
$string['privacy:metadata:local_certsso_tokens'] = 'Almacena tokens temporales para SSO';
$string['privacy:metadata:local_certsso_tokens:userid'] = 'ID del usuario que generó el token';
$string['privacy:metadata:local_certsso_tokens:token'] = 'Token de autenticación temporal';
$string['privacy:metadata:local_certsso_tokens:timecreated'] = 'Fecha de creación del token';
$string['privacy:metadata:local_certsso_tokens:timeexpires'] = 'Fecha de expiración del token';
$string['privacy:metadata:local_certsso_tokens:ipaddress'] = 'Dirección IP del usuario';
$string['privacy:metadata:local_certsso_tokens:useragent'] = 'User agent del navegador del usuario';
