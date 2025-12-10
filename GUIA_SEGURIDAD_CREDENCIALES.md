# 🔐 Guía de Seguridad: Credenciales en Producción

## 📋 Resumen Ejecutivo

**Pregunta:** ¿Es más seguro usar GitHub Secrets directamente que `.env`?

**Respuesta:** GitHub Secrets **NO** pueden usarse directamente en PHP. La mejor opción es usar **Variables de Entorno del Sistema** en lugar de archivo `.env`.

---

## ❌ Por qué NO puedes usar GitHub Secrets directamente

### Limitaciones de GitHub Secrets:

1. **Solo disponibles durante el workflow:**
   - Los secrets solo existen durante la ejecución del workflow de CI/CD
   - Una vez que el workflow termina, los secrets desaparecen
   - PHP no puede acceder a ellos en tiempo de ejecución

2. **Laravel necesita acceso en tiempo de ejecución:**
   - Laravel lee las variables cuando se ejecuta cada request
   - Necesita acceso persistente a las credenciales
   - GitHub Secrets no proporcionan esto

3. **Solución actual (correcta pero mejorable):**
   - GitHub Secrets → Se usan para **crear** el `.env` en el servidor
   - El `.env` queda en el servidor para que Laravel lo lea
   - Funciona, pero hay opciones más seguras

---

## ✅ Opciones de Seguridad (de menor a mayor)

### Opción 1: `.env` en servidor (ACTUAL) ⚠️

**Cómo funciona:**
- GitHub Secrets → Crea `.env` en el servidor durante el despliegue
- Laravel lee el `.env` en cada request

**Ventajas:**
- ✅ Simple y funciona inmediatamente
- ✅ Fácil de depurar
- ✅ Laravel lo lee automáticamente

**Desventajas:**
- ❌ Archivo físico en el servidor
- ❌ Si el servidor se compromete, el `.env` está expuesto
- ❌ Necesita permisos correctos (600)
- ❌ Puede ser leído por otros procesos del sistema

**Seguridad:** 🟡 **MEDIA**

---

### Opción 2: Variables de Entorno del Sistema (RECOMENDADO) ✅

**Cómo funciona:**
- GitHub Secrets → Se configuran como variables de entorno del sistema
- Laravel las lee automáticamente (prioridad sobre `.env`)
- NO hay archivo `.env` físico

**Ventajas:**
- ✅ **Más seguro** (solo en memoria del proceso)
- ✅ No hay archivo físico que pueda ser leído
- ✅ Laravel las lee automáticamente
- ✅ No se puede acceder desde el sistema de archivos
- ✅ Diferentes valores por proceso (PHP-FPM, CLI, etc.)

**Desventajas:**
- ⚠️ Requiere configuración en el servidor (Apache/Nginx/PHP-FPM)
- ⚠️ Más complejo de configurar inicialmente
- ⚠️ Depende del servidor web usado

**Seguridad:** 🟢 **ALTA**

**Implementación:**
```bash
# En Apache (.htaccess o VirtualHost)
SetEnv APP_KEY "base64:tu_key_aqui"
SetEnv DB_PASSWORD "tu_password"

# En PHP-FPM (php-fpm.conf o pool.d/www.conf)
env[APP_KEY] = base64:tu_key_aqui
env[DB_PASSWORD] = tu_password

# En Nginx (fastcgi_params o location)
fastcgi_param APP_KEY "base64:tu_key_aqui";
fastcgi_param DB_PASSWORD "tu_password";
```

---

### Opción 3: Gestor de Secretos (ÓPTIMO para empresas) 🏆

**Cómo funciona:**
- AWS Secrets Manager / Google Secret Manager / HashiCorp Vault
- La aplicación consulta el gestor al iniciar
- Rotación automática de credenciales

**Ventajas:**
- ✅ **Máxima seguridad**
- ✅ Rotación automática de credenciales
- ✅ Auditoría completa de acceso
- ✅ Centralizado (múltiples aplicaciones)
- ✅ Versionado de secretos

**Desventajas:**
- ❌ Requiere servicio adicional (costo)
- ❌ Más complejo de implementar
- ❌ Dependencia externa

**Seguridad:** 🟢 **MÁXIMA**

**Ejemplo con AWS Secrets Manager:**
```php
// En AppServiceProvider
use Aws\SecretsManager\SecretsManagerClient;

$client = new SecretsManagerClient([
    'region' => 'us-east-1',
]);

$result = $client->getSecretValue(['SecretId' => 'corralx/production']);
$secrets = json_decode($result['SecretString'], true);

// Configurar variables de entorno
foreach ($secrets as $key => $value) {
    putenv("$key=$value");
}
```

---

## 🎯 Recomendación para tu Proyecto

### Para MVP / Proyecto Pequeño-Mediano:

**Usar Variables de Entorno del Sistema** (Opción 2)

**Razones:**
- Más seguro que `.env` físico
- No requiere servicios adicionales
- Laravel lo soporta nativamente
- Mejor balance seguridad/complejidad

### Para Proyecto Grande / Empresa:

**Usar Gestor de Secretos** (Opción 3)

**Razones:**
- Máxima seguridad
- Rotación automática
- Auditoría y compliance

---

## 📝 Cómo Migrar de `.env` a Variables de Entorno del Sistema

### Paso 1: Configurar Variables en el Servidor

**Para Apache (cPanel/Shared Hosting):**

Editar `.htaccess` o configuración del VirtualHost:
```apache
<IfModule mod_env.c>
    SetEnv APP_KEY "base64:tu_key_aqui"
    SetEnv DB_PASSWORD "tu_password"
    SetEnv DB_USERNAME "tu_usuario"
    # ... todas las variables necesarias
</IfModule>
```

**Para PHP-FPM:**

Editar `php-fpm.conf` o `pool.d/www.conf`:
```ini
[www]
env[APP_KEY] = base64:tu_key_aqui
env[DB_PASSWORD] = tu_password
env[DB_USERNAME] = tu_usuario
```

**Para Nginx + PHP-FPM:**

Editar configuración de PHP-FPM:
```ini
env[APP_KEY] = base64:tu_key_aqui
env[DB_PASSWORD] = tu_password
```

### Paso 2: Actualizar Workflow de CI/CD

Modificar `.github/workflows/main.yml`:

```yaml
# En lugar de crear .env, configurar variables del sistema
- name: Setup environment variables
  run: |
    # Opción A: Si el servidor soporta script de configuración
    # Crear script que configure variables de entorno
    
    # Opción B: Si usas SSH después del FTP
    # Configurar variables vía SSH
    
    # Opción C: Mantener .env pero con valores desde secrets
    # (actual, pero menos seguro)
```

### Paso 3: Verificar que Laravel las Lee

Laravel automáticamente prioriza:
1. Variables de entorno del sistema (`getenv()`)
2. Archivo `.env` (si no existe en sistema)
3. Valor por defecto en `config/*.php`

**Verificación:**
```bash
php artisan tinker
>>> env('APP_KEY')
=> "base64:tu_key_aqui"  # Debe venir del sistema, no de .env
```

### Paso 4: Eliminar `.env` (Opcional)

Si todas las variables están en el sistema:
```bash
# Hacer backup primero
cp .env .env.backup

# Eliminar .env
rm .env

# Verificar que la app funciona
php artisan config:clear
php artisan cache:clear
```

---

## 🔒 Mejores Prácticas de Seguridad

### 1. Permisos de Archivos

```bash
# .env (si lo usas)
chmod 600 .env
chown www-data:www-data .env

# Variables de entorno del sistema
# No hay archivo, solo configuración del servidor
```

### 2. Rotación de Credenciales

- **Base de datos:** Cada 3-6 meses
- **API Keys:** Cada 6 meses
- **APP_KEY:** Solo si se compromete
- **Firebase:** Cada 6-12 meses

### 3. Monitoreo

- Escanear logs de acceso a credenciales
- Alertas si se detectan accesos anómalos
- Auditoría de cambios en credenciales

### 4. Backup Seguro

- NO hacer backup de `.env` en repositorio
- Guardar credenciales en gestor de secretos o lugar seguro
- Documentar dónde están las credenciales (fuera del repo)

---

## 📊 Comparación Final

| Aspecto | `.env` Físico | Variables Sistema | Gestor Secretos |
|---------|---------------|-------------------|-----------------|
| **Seguridad** | 🟡 Media | 🟢 Alta | 🟢 Máxima |
| **Complejidad** | 🟢 Baja | 🟡 Media | 🔴 Alta |
| **Costo** | 🟢 Gratis | 🟢 Gratis | 🔴 Pago |
| **Rotación** | 🔴 Manual | 🔴 Manual | 🟢 Automática |
| **Auditoría** | 🔴 No | 🔴 No | 🟢 Sí |
| **Recomendado para** | Desarrollo | Producción MVP | Empresa |

---

## ✅ Conclusión

**Para tu proyecto actual:**

1. **Corto plazo:** Mantener `.env` con permisos 600 (ya configurado) ✅
2. **Mediano plazo:** Migrar a Variables de Entorno del Sistema
3. **Largo plazo:** Considerar Gestor de Secretos si crece

**La configuración actual es SEGURA** para un MVP, pero las Variables de Entorno del Sistema son el siguiente paso recomendado.

---

**Última actualización:** 2025-12-10

