# Plugin Moodle - Certificados SSO

Plugin local de Moodle para autenticación Single Sign-On (SSO) con la aplicación de gestión de certificados ACG.

## Descripción

Este plugin permite a los usuarios de Moodle acceder directamente a la aplicación de certificados sin necesidad de volver a autenticarse. Genera tokens temporales seguros que se validan en el backend de la aplicación.

## Características

- **Autenticación SSO**: Tokens temporales de 5 minutos de duración
- **Enlace en navegación**: Agrega "Mis Certificados" al menú de navegación de Moodle
- **Web Services**: APIs para generar y validar tokens
- **Limpieza automática**: Tarea programada que elimina tokens expirados cada 15 minutos
- **Mapeo de roles**: Convierte roles de Moodle (Admin, Manager, User) a roles de la aplicación
- **Seguridad**: Tokens de un solo uso con tracking de IP y User Agent
- **Configuración flexible**: URLs configurables para producción y desarrollo
- **GDPR**: Implementa Privacy API de Moodle

## Requisitos

- Moodle 5.1 o superior
- PHP 8.4 o superior
- Backend de certificados ACG funcionando

## Instalación

### 1. Copiar archivos del plugin

```bash
# Desde la raíz de Moodle
cd /path/to/moodle/local/
git clone https://github.com/ACG-Calidad/acg-certificados-moodle-plugin.git certificados_sso
```

O descargar y extraer el ZIP en `moodle/local/certificados_sso/`

### 2. Instalar desde la interfaz de Moodle

1. Ir a **Administración del sitio** → **Notificaciones**
2. Moodle detectará el nuevo plugin
3. Hacer clic en **Actualizar base de datos**
4. Seguir el asistente de instalación

### 3. Configurar el plugin

1. Ir a **Administración del sitio** → **Plugins** → **Plugins locales** → **Certificados SSO**
2. Configurar las siguientes opciones:

   - **URL de la aplicación (producción)**: `https://aulavirtual.acgcalidad.co/certificados/`
   - **URL de desarrollo**: `http://localhost:4200/`
   - **Tiempo de expiración del token**: `300` (5 minutos)
   - **Modo debug**: Activar solo en desarrollo

3. Guardar cambios

### 4. Habilitar Web Services

1. Ir a **Administración del sitio** → **Servidor** → **Web services** → **Visión general**
2. Seguir los pasos para habilitar Web Services
3. Ir a **Servicios externos**
4. Localizar el servicio **ACG Certificados SSO** (`acg_certificados_sso`)
5. Hacer clic en **Editar** y marcar como **Habilitado**
6. Asignar usuarios autorizados (o permitir todos los usuarios autenticados)

### 5. Crear token para el backend

El backend necesita un token permanente para validar los tokens SSO:

1. Ir a **Administración del sitio** → **Servidor** → **Web services** → **Gestionar tokens**
2. Hacer clic en **Crear token**
3. Seleccionar:
   - **Usuario**: Usuario administrador o de servicio
   - **Servicio**: ACG Certificados SSO
4. Guardar y copiar el token generado
5. Configurar este token en el backend (`MOODLE_WS_TOKEN` en config.php)

### 6. Verificar instalación

1. Como usuario autenticado, verificar que aparezca el enlace **"Mis Certificados"** en la navegación principal
2. Hacer clic en el enlace - debe redirigir a la aplicación de certificados
3. Verificar en **Administración del sitio** → **Servidor** → **Tareas programadas** que existe la tarea **"Limpiar tokens SSO expirados"** (ejecuta cada 15 minutos)

## Estructura de archivos

```
moodle-plugin/
├── README.md                           # Este archivo
├── version.php                         # Versión y metadata del plugin
├── settings.php                        # Página de configuración
├── lib.php                            # Funciones principales del plugin
├── lang/
│   └── es/
│       └── local_certificados_sso.php # Strings en español
├── db/
│   ├── install.xml                    # Esquema de base de datos
│   ├── access.php                     # Definición de capabilities
│   ├── services.php                   # Definición de Web Services
│   └── tasks.php                      # Tareas programadas
└── classes/
    ├── external/
    │   ├── generate_token.php         # WS: Generar token
    │   └── validate_token.php         # WS: Validar token
    ├── task/
    │   └── cleanup_expired_tokens.php # Tarea de limpieza
    └── privacy/
        └── provider.php               # Privacy API (GDPR)
```

## Tabla de base de datos

El plugin crea la tabla `mdl_local_certsso_tokens`:

| Campo       | Tipo         | Descripción                          |
|-------------|--------------|--------------------------------------|
| id          | int(10)      | ID único (auto-incremental)          |
| token       | char(64)     | Token único (32 bytes en hex)        |
| userid      | int(10)      | ID del usuario de Moodle             |
| timecreated | int(10)      | Timestamp de creación                |
| timeexpires | int(10)      | Timestamp de expiración              |
| used        | int(1)       | Indica si fue usado (0=no, 1=sí)     |
| ipaddress   | char(45)     | IP del cliente                       |
| useragent   | char(255)    | User Agent del navegador             |

Índices:
- `token` (UNIQUE) - Búsqueda rápida
- `timeexpires` - Limpieza eficiente
- `userid, timecreated` - Auditoría

## Uso del plugin

### Para usuarios finales

1. Iniciar sesión en Moodle
2. Hacer clic en **"Mis Certificados"** en el menú de navegación
3. Serás redirigido automáticamente a la aplicación de certificados

### Para desarrolladores del backend

El backend debe validar tokens usando el Web Service:

```php
// Endpoint del WS
$ws_url = 'https://aulavirtual.acgcalidad.co/webservice/rest/server.php';

// Parámetros
$params = [
    'wstoken' => 'TOKEN_PERMANENTE_DEL_BACKEND',
    'wsfunction' => 'local_certificados_sso_validate_token',
    'moodlewsrestformat' => 'json',
    'token' => $moodle_token_recibido
];

// Hacer request
$response = file_get_contents($ws_url . '?' . http_build_query($params));
$result = json_decode($response);

if ($result->valid) {
    // Token válido, crear sesión para el usuario
    $userid = $result->userid;
    $role = $result->role; // 'admin', 'gestor', 'participante'
    // ...
}
```

## Flujo de autenticación SSO

```
1. Usuario hace clic en "Mis Certificados" en Moodle
   ↓
2. Moodle ejecuta local_certificados_sso_generate_token()
   - Genera token aleatorio de 32 bytes (64 caracteres hex)
   - Almacena en BD con expiración de 5 minutos
   - Guarda IP y User Agent
   ↓
3. Moodle redirige a: https://app.com/certificados?moodle_token=XXXXX
   ↓
4. Frontend detecta parámetro moodle_token
   ↓
5. Frontend envía token al backend ACG
   ↓
6. Backend llama al WS local_certificados_sso_validate_token
   - Verifica que exista
   - Verifica que no haya expirado
   - Verifica que no haya sido usado
   - Marca token como usado
   ↓
7. Backend retorna información del usuario y rol
   ↓
8. Backend crea sesión JWT para el usuario
   ↓
9. Usuario accede a la aplicación sin volver a autenticarse
```

## Web Services disponibles

### 1. local_certificados_sso_generate_token

**Descripción**: Genera un token SSO temporal

**Parámetros**:
- `userid` (opcional): ID del usuario (por defecto: usuario actual)

**Retorna**:
```json
{
  "success": true,
  "token": "abc123...",
  "expires_in": 300,
  "expires_at": 1234567890,
  "userid": 123
}
```

### 2. local_certificados_sso_validate_token

**Descripción**: Valida un token SSO y retorna información del usuario

**Parámetros**:
- `token` (requerido): Token a validar

**Retorna** (si válido):
```json
{
  "valid": true,
  "error": "",
  "userid": 123,
  "username": "jperez",
  "firstname": "Juan",
  "lastname": "Pérez",
  "email": "juan@example.com",
  "role": "participante"
}
```

**Retorna** (si inválido):
```json
{
  "valid": false,
  "error": "Token inválido, expirado o ya utilizado",
  "userid": 0,
  "username": "",
  "firstname": "",
  "lastname": "",
  "email": "",
  "role": ""
}
```

## Mapeo de roles

El plugin mapea roles de Moodle a roles de la aplicación:

| Capability en Moodle           | Rol en Aplicación |
|--------------------------------|-------------------|
| `moodle/site:config`           | `admin`           |
| `moodle/site:manageblocks`     | `gestor`          |
| Usuario autenticado (default)  | `participante`    |

## Seguridad

- Tokens generados con `random_bytes(32)` - criptográficamente seguros
- Tokens de un solo uso (marcados como usados después de validación)
- Expiración de 5 minutos (configurable)
- Limpieza automática de tokens expirados cada 15 minutos
- Tracking de IP y User Agent para auditoría
- Capabilities de Moodle para control de acceso

## Troubleshooting

### El enlace "Mis Certificados" no aparece

1. Verificar que el plugin está instalado correctamente
2. Verificar que estás autenticado (no como invitado)
3. Limpiar caché de Moodle: **Administración del sitio** → **Desarrollo** → **Purgar todas las cachés**

### Error al validar token

1. Verificar que los Web Services están habilitados
2. Verificar que el servicio "ACG Certificados SSO" está habilitado
3. Verificar que el token permanente del backend es correcto
4. Verificar que el token SSO no haya expirado (5 minutos)
5. Verificar que el token no haya sido usado anteriormente

### Tokens no se limpian automáticamente

1. Verificar que el cron de Moodle está funcionando
2. Ir a **Administración del sitio** → **Servidor** → **Tareas programadas**
3. Buscar "Limpiar tokens SSO expirados"
4. Verificar que está habilitada y se ejecuta cada 15 minutos
5. Puedes ejecutarla manualmente haciendo clic en "Ejecutar ahora"

### Error "No tienes permiso para generar tokens"

1. Verificar que el usuario tiene la capability `local/certificados_sso:generatetoken`
2. Solo puedes generar tokens para ti mismo (excepto administradores)

## Desarrollo

### Ejecutar limpieza manualmente (CLI)

```bash
# Desde la raíz de Moodle
php admin/cli/scheduled_task.php --execute='\local_certificados_sso\task\cleanup_expired_tokens'
```

### Generar token desde CLI (desarrollo)

```php
// En un script PHP con Moodle incluido
require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/local/certificados_sso/lib.php');

$userid = 2; // ID del usuario
$token = local_certificados_sso_generate_token($userid);
echo "Token: $token\n";
```

### Verificar tabla de tokens

```sql
-- Ver todos los tokens
SELECT * FROM mdl_local_certsso_tokens ORDER BY timecreated DESC;

-- Ver tokens no usados
SELECT * FROM mdl_local_certsso_tokens WHERE used = 0;

-- Ver tokens expirados
SELECT * FROM mdl_local_certsso_tokens WHERE timeexpires < UNIX_TIMESTAMP();
```

## Changelog

### Version 1.0.0 (2026-01-09)

- Release inicial
- Generación de tokens SSO
- Validación de tokens vía Web Services
- Tarea programada de limpieza
- Enlace en navegación de Moodle
- Mapeo de roles
- Implementación de Privacy API

## Licencia

Copyright © 2026 ACG Calidad - Todos los derechos reservados

## Autor

Oliver Castelblanco - oliver@acgcalidad.co

## Soporte

Para reportar problemas o solicitar características:
- Email: soporte@acgcalidad.co
- Issues: https://github.com/ACG-Calidad/acg-certificados-moodle-plugin/issues
