<?php
/**
 * English language strings for local_certificados_sso
 *
 * @package    local_certificados_sso
 * @copyright  2026 ACG Calidad
 * @author     Oliver Castelblanco
 * @license    Proprietary
 */

defined('MOODLE_INTERNAL') || die();

// Plugin information
$string['pluginname'] = 'ACG Certificados SSO';
$string['mycertificates'] = 'My Certificates';

// Settings
$string['appurl'] = 'Production App URL';
$string['appurl_desc'] = 'URL of the certificates application in production environment';
$string['devurl'] = 'Development App URL';
$string['devurl_desc'] = 'URL of the certificates application for local development (localhost)';
$string['tokenexpiry'] = 'Token Expiry Time';
$string['tokenexpiry_desc'] = 'Time in seconds before SSO tokens expire (default: 300 seconds = 5 minutes)';
$string['debugmode'] = 'Debug Mode';
$string['debugmode_desc'] = 'Enable debug logging for SSO token generation and validation operations';

// Capabilities
$string['certificados_sso:generatetoken'] = 'Generate SSO token for certificates application';
$string['certificados_sso:validatetoken'] = 'Validate SSO tokens from certificates application';
$string['certificados_sso:manage'] = 'Manage certificates SSO plugin settings';

// Scheduled tasks
$string['taskcleanuptokens'] = 'Clean up expired SSO tokens';
