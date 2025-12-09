# Configuración CI/CD - Corral X One Page

## 📋 Resumen del Flujo

```
┌─────────────────────────────────────────┐
│  DESARROLLADOR                          │
│  git push origin dev                    │
└─────────────────────────────────────────┘
              ↓
┌─────────────────────────────────────────┐
│  GitHub Actions (dev.yml)               │
│  → Despliega a test.corralx.com         │
└─────────────────────────────────────────┘
              ↓
┌─────────────────────────────────────────┐
│  PRUEBAS en test.corralx.com            │
│  ✅ Todo OK?                            │
└─────────────────────────────────────────┘
              ↓
┌─────────────────────────────────────────┐
│  Merge dev → main                       │
└─────────────────────────────────────────┘
              ↓
┌─────────────────────────────────────────┐
│  GitHub Actions (main.yml)              │
│  → Despliega a corralx.com             │
└─────────────────────────────────────────┘
```

## 🔐 Secretos de GitHub Actions

### Ubicación
Ve a: `https://github.com/Abrahan-Eagle/corral-x-one-page/settings/secrets/actions`

### Secretos para PRODUCCIÓN (corralx.com) - Rama `main`

Estos secretos ya deberían existir (del workflow anterior):

| Secreto | Descripción | Ejemplo |
|---------|-------------|---------|
| `FTP_SERVER` | Servidor FTP de producción | `ftp.corralx.com` o IP |
| `FTP_USERNAME` | Usuario FTP de producción | `usuario_prod` |
| `FTP_PASSWORD` | Contraseña FTP de producción | `password_prod` |
| `ENV_CONTENT` | Contenido completo del archivo `.env` para producción | (ver abajo) |

### Secretos para TESTING (test.corralx.com) - Rama `dev`

**NUEVOS SECRETOS** - Debes crearlos:

| Secreto | Descripción | Ejemplo |
|---------|-------------|---------|
| `FTP_SERVER_TEST` | Servidor FTP de testing | `ftp.test.corralx.com` o IP |
| `FTP_USERNAME_TEST` | Usuario FTP de testing | `usuario_test` |
| `FTP_PASSWORD_TEST` | Contraseña FTP de testing | `password_test` |
| `ENV_CONTENT_TEST` | Contenido completo del archivo `.env` para testing | (ver abajo) |

## 📝 Configuración de ENV_CONTENT

### Para PRODUCCIÓN (`ENV_CONTENT`)

```env
APP_NAME="Corral X"
APP_ENV=production
APP_KEY=base64:TU_APP_KEY_AQUI
APP_DEBUG=false
APP_URL=https://corralx.com

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=corralx_prod
DB_USERNAME=usuario_db_prod
DB_PASSWORD=password_db_prod

# ... resto de configuración
```

### Para TESTING (`ENV_CONTENT_TEST`)

```env
APP_NAME="Corral X (Test)"
APP_ENV=testing
APP_KEY=base64:TU_APP_KEY_AQUI
APP_DEBUG=true
APP_URL=https://test.corralx.com

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=corralx_test
DB_USERNAME=usuario_db_test
DB_PASSWORD=password_db_test

# ... resto de configuración
```

## 🚀 Cómo Usar

### 1. Desarrollo y Testing

```bash
# Trabajar en la rama dev
git checkout dev

# Hacer cambios
# ... editar archivos ...

# Commit y push
git add .
git commit -m "feat: nueva funcionalidad"
git push origin dev
```

**Resultado:** GitHub Actions despliega automáticamente a `test.corralx.com`

### 2. Desplegar a Producción

```bash
# Cuando todo está probado en test.corralx.com
git checkout main
git merge dev
git push origin main
```

**Resultado:** GitHub Actions despliega automáticamente a `corralx.com`

## ⚠️ Notas Importantes

1. **Credenciales FTP diferentes**: Cada subdominio necesita sus propias credenciales FTP
2. **Base de datos separada**: Recomendado usar bases de datos diferentes para test y producción
3. **APP_DEBUG**: 
   - `true` en testing (test.corralx.com)
   - `false` en producción (corralx.com)
4. **APP_URL**: Se actualiza automáticamente en los workflows según el entorno

## 🔍 Verificar Despliegues

- **Testing**: https://test.corralx.com
- **Producción**: https://corralx.com

## 📚 Archivos de Workflow

- `.github/workflows/main.yml` → Producción (corralx.com)
- `.github/workflows/dev.yml` → Testing (test.corralx.com)

