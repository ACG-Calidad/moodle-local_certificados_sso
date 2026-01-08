# Plugin Moodle: local_certificados_sso

Plugin local de Moodle para implementar Single Sign-On (SSO) desde Moodle hacia la aplicación de gestión de certificados de ACG Calidad.

## 📋 Descripción

Este plugin permite que los usuarios de Moodle accedan a la aplicación de certificados sin necesidad de autenticarse nuevamente, mediante la generación de tokens temporales seguros.

---

## ✨ Características

- ✅ **Generación de tokens temporales** con TTL de 5 minutos
- ✅ **Validación de tokens** vía Web Services REST
- ✅ **Enlace en navegación principal** de Moodle
- ✅ **Limpieza automática** de tokens expirados (tarea programada)
- ✅ **Tokens de uso único** (se eliminan después de validar)
- ✅ **Auditoría completa** de generación y uso de tokens

---

## 🛠️ Instalación

### Requisitos
- Moodle 5.1 o superior
- PHP 8.4 o superior
- Web Services habilitados en Moodle
- Protocolo REST activo

### Pasos de Instalación

#### Opción 1: Instalación Manual

1. **Descargar o clonar el repositorio:**
```bash
git clone https://github.com/ACG-Calidad/moodle-local_certificados_sso.git
```

2. **Copiar a la carpeta de plugins de Moodle:**
```bash
cp -r moodle-local_certificados_sso /var/www/html/moodle/local/certificados_sso
```

3. **Establecer permisos correctos:**
```bash
chown -R www-data:www-data /var/www/html/moodle/local/certificados_sso
chmod -R 755 /var/www/html/moodle/local/certificados_sso
```

4. **Acceder a Moodle como administrador:**
   - Ir a: `Administración del sitio → Notificaciones`
   - Moodle detectará el plugin y solicitará actualizar la base de datos
   - Hacer clic en "Actualizar base de datos"

5. **Verificar instalación:**
   - Ir a: `Administración del sitio → Plugins → Plugins locales`
   - Verificar que "Certificados SSO" aparezca en la lista

#### Opción 2: Instalación vía Interface de Moodle

1. Comprimir el plugin en un archivo .zip
2. Ir a: `Administración del sitio → Plugins → Instalar plugins`
3. Subir el archivo .zip
4. Seguir las instrucciones en pantalla

---

## ⚙️ Configuración

### 1. Habilitar Web Services

Si aún no están habilitados:

1. Ir a: `Administración del sitio → Funcionalidad avanzada`
2. Marcar "Habilitar servicios web"
3. Guardar cambios

### 2. Activar Protocolo REST

1. Ir a: `Administración del sitio → Plugins → Servicios web → Gestionar protocolos`
2. Activar "REST protocol"

### 3. Crear Servicio Web

1. Ir a: `Administración del sitio → Servidor → Servicios web → Servicios externos`
2. Hacer clic en "Agregar"
3. Configurar:
   - **Nombre:** Certificados SSO
   - **Nombre corto:** certificados_sso
   - **Habilitado:** Sí
   - **Usuarios autorizados:** Seleccionar usuarios que pueden usar el servicio
4. Guardar cambios

### 4. Agregar Funciones al Servicio

1. En la lista de servicios, hacer clic en "Agregar funciones" junto a "Certificados SSO"
2. Agregar las siguientes funciones:
   - `local_certificados_sso_generate_token`
   - `local_certificados_sso_validate_token`
3. Guardar cambios

### 5. Crear Token para la Aplicación Externa

1. Ir a: `Administración del sitio → Servidor → Servicios web → Gestionar tokens`
2. Hacer clic en "Crear token"
3. Configurar:
   - **Usuario:** adminav (o el usuario gestor)
   - **Servicio:** Certificados SSO
   - **IP restringida:** (opcional, dejar en blanco para desarrollo)
4. Guardar y copiar el token generado
5. Configurar este token en el backend de la aplicación (config.php)

### 6. Configurar Enlace en Navegación

El plugin automáticamente agrega un enlace "Mis Certificados" en el menú principal de Moodle para usuarios autenticados.

Para personalizar el texto o ubicación:
1. Editar archivo `lib.php`
2. Modificar la función `local_certificados_sso_extend_navigation()`

---

## 📡 Funciones del Web Service

### 1. local_certificados_sso_generate_token

Genera un token temporal para SSO.

**Parámetros:** Ninguno (usa el usuario actual de la sesión)

**Retorna:**
```json
{
  "token": "abc123def456...",
  "expires": 1672531200,
  "redirect_url": "https://aulavirtual.acgcalidad.co/certificados/?moodle_token=abc123def456..."
}
```

**Uso desde JavaScript (en Moodle):**
```javascript
// Llamar al web service para generar token
M.util.js_pending('local_certificados_sso_generate');
var xhr = new XMLHttpRequest();
xhr.open('POST', M.cfg.wwwroot + '/webservice/rest/server.php', true);
xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
xhr.onload = function() {
    if (xhr.status === 200) {
        var response = JSON.parse(xhr.responseText);
        // Redirigir a la aplicación con el token
        window.location.href = response.redirect_url;
    }
    M.util.js_complete('local_certificados_sso_generate');
};
xhr.send('wstoken=YOUR_TOKEN&wsfunction=local_certificados_sso_generate_token&moodlewsrestformat=json');
```

### 2. local_certificados_sso_validate_token

Valida un token y retorna información del usuario.

**Parámetros:**
- `token` (string, requerido): El token a validar

**Retorna (si válido):**
```json
{
  "valid": true,
  "userid": 1234,
  "username": "jperez",
  "firstname": "Juan",
  "lastname": "Pérez",
  "email": "juan@example.com",
  "role": "participante"
}
```

**Retorna (si inválido):**
```json
{
  "valid": false,
  "error": "Token inválido o expirado"
}
```

**Uso desde PHP (aplicación externa):**
```php
function validateMoodleToken($token) {
    $url = 'http://aulavirtual.acgcalidad.co/webservice/rest/server.php';

    $params = [
        'wstoken' => 'YOUR_WEBSERVICE_TOKEN',
        'wsfunction' => 'local_certificados_sso_validate_token',
        'moodlewsrestformat' => 'json',
        'token' => $token
    ];

    $query = http_build_query($params);
    $response = file_get_contents($url . '?' . $query);

    return json_decode($response, true);
}
```

---

## 🗄️ Estructura de Base de Datos

### Tabla: mdl_local_certsso_tokens

```sql
CREATE TABLE mdl_local_certsso_tokens (
    id BIGINT(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    token VARCHAR(64) NOT NULL UNIQUE,
    userid BIGINT(10) UNSIGNED NOT NULL,
    timecreated BIGINT(10) UNSIGNED NOT NULL,
    timeexpires BIGINT(10) UNSIGNED NOT NULL,
    used BOOLEAN NOT NULL DEFAULT FALSE,
    ipaddress VARCHAR(45) DEFAULT NULL,
    useragent TEXT DEFAULT NULL,
    INDEX idx_token (token),
    INDEX idx_userid (userid),
    INDEX idx_timeexpires (timeexpires),
    CONSTRAINT fk_certsso_token_user
        FOREIGN KEY (userid)
        REFERENCES mdl_user(id)
        ON DELETE CASCADE
);
```

**Campos:**
- `id`: Identificador único
- `token`: Token aleatorio de 64 caracteres (SHA-256)
- `userid`: ID del usuario en Moodle
- `timecreated`: Timestamp de creación (Unix timestamp)
- `timeexpires`: Timestamp de expiración (timecreated + 300 segundos)
- `used`: Indica si el token ya fue usado (uso único)
- `ipaddress`: IP del cliente (opcional, para auditoría)
- `useragent`: User agent del navegador (opcional, para auditoría)

---

## 🔒 Seguridad

### Características de Seguridad

1. **Tokens de uso único:** Después de validar, el token se marca como usado y no puede reutilizarse
2. **TTL de 5 minutos:** Los tokens expiran automáticamente después de 5 minutos
3. **Tokens aleatorios seguros:** Generados con `random_bytes(32)` y hasheados con SHA-256
4. **Limpieza automática:** Tarea programada elimina tokens expirados diariamente
5. **Validación de usuario:** Solo usuarios autenticados pueden generar tokens
6. **Auditoría:** Se registra IP y user agent en cada generación

### Mejores Prácticas

- ✅ Usar HTTPS en producción
- ✅ Restringir IPs del servicio web si es posible
- ✅ Rotar el token del web service periódicamente
- ✅ Monitorear logs de tokens generados y validados
- ✅ Configurar rate limiting en el servidor web

---

## 📅 Tareas Programadas

### Limpieza de Tokens Expirados

**Clase:** `\local_certificados_sso\task\cleanup_expired_tokens`
**Frecuencia:** Diaria (3:00 AM por defecto)

Esta tarea elimina:
- Tokens expirados (timeexpires < now)
- Tokens usados con más de 7 días de antigüedad

**Configurar manualmente:**
1. Ir a: `Administración del sitio → Servidor → Tareas → Tareas programadas`
2. Buscar "Limpiar tokens SSO expirados"
3. Ajustar frecuencia si es necesario

---

## 🔧 Desarrollo

### Estructura de Archivos

```
local/certificados_sso/
├── version.php              # Información del plugin
├── lib.php                  # Funciones principales (navegación, etc.)
├── db/
│   ├── access.php           # Capacidades del plugin
│   ├── install.xml          # Esquema de base de datos
│   └── services.php         # Definición de web services
├── classes/
│   ├── external/
│   │   ├── generate_token.php    # Web service: generar token
│   │   └── validate_token.php    # Web service: validar token
│   └── task/
│       └── cleanup_expired_tokens.php  # Tarea de limpieza
└── lang/
    └── es/
        └── local_certificados_sso.php  # Textos en español
```

### Agregar Nuevos Idiomas

1. Crear carpeta en `lang/[código_idioma]/`
2. Copiar `local_certificados_sso.php` y traducir strings
3. Moodle detectará automáticamente el nuevo idioma

---

## 🧪 Testing

### Probar Generación de Token

1. Iniciar sesión en Moodle como usuario normal
2. Hacer clic en enlace "Mis Certificados" en el menú
3. Verificar que redirige a la aplicación con parámetro `moodle_token`
4. Verificar en base de datos que se creó un registro en `mdl_local_certsso_tokens`

### Probar Validación de Token

```bash
# Reemplazar TOKEN_GENERADO y WEBSERVICE_TOKEN con valores reales
curl "http://aulavirtual.acgcalidad.co/webservice/rest/server.php?wstoken=WEBSERVICE_TOKEN&wsfunction=local_certificados_sso_validate_token&moodlewsrestformat=json&token=TOKEN_GENERADO"
```

Debería retornar información del usuario si el token es válido.

---

## 🚨 Troubleshooting

### Error: "Web service not available"
- Verificar que Web Services estén habilitados
- Verificar que el protocolo REST esté activo
- Verificar que el servicio "Certificados SSO" esté habilitado

### Error: "Token inválido"
- Verificar que el token no haya expirado (5 minutos)
- Verificar que el token no haya sido usado previamente
- Verificar en base de datos que el token existe

### El enlace no aparece en el menú
- Purgar cachés de Moodle: `Administración del sitio → Desarrollo → Purgar todas las cachés`
- Verificar que el plugin esté instalado correctamente

---

## 📄 Licencia

Proyecto privado - ACG Calidad © 2026

---

## 👥 Contacto

**Desarrollador:** Oliver Castelblanco
**Soporte:** cursosvirtualesacg@gmail.com

---

*Última actualización: 2026-01-08*
