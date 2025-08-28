# 🚀 Corral X - Sitio Web con Deploy Automático

Sitio web moderno para la revolución ganadera con pipeline de CI/CD automático mediante GitHub Actions.

## 🌟 Características del Sitio

- **Bootstrap 5.3.3** con diseño responsive y moderno
- **PWA (Progressive Web App)** con manifest y service worker
- **Animaciones CSS avanzadas** y efectos visuales
- **Deploy automático** con GitHub Actions
- **Limpieza automática** del servidor antes de cada deploy
- **Validación de archivos** antes del despliegue

## 🚀 Pipeline de CI/CD Automático

### **Flujo del Deploy:**
1. **Checkout** del repositorio
2. **Verificación** de archivos
3. **🧹 Limpieza** del servidor (borra archivos viejos)
4. **🚀 Deploy** de archivos nuevos por FTP

### **Trigger del Deploy:**
- ✅ **Push a main** → Deploy automático
- ✅ **Manual** → Desde GitHub Actions

## 📋 Configuración Requerida

### **1. Secrets de GitHub (Obligatorios):**

Ve a tu repositorio → **Settings** → **Secrets and variables** → **Actions** y agrega:

| Secret | Descripción | Ejemplo |
|--------|-------------|---------|
| `FTP_SERVER` | Servidor FTP | `ftp.tudominio.com` |
| `FTP_USERNAME` | Usuario FTP | `tuusuario` |
| `FTP_PASSWORD` | Contraseña FTP | `tucontraseña` |

### **2. Estructura del Proyecto:**
```
corral-x-one-page/
├── .github/
│   └── workflows/
│       └── deploy.yml          # Pipeline de CI/CD
├── index.html                  # Sitio web principal
├── manifest.json              # Configuración PWA
├── sw.js                     # Service Worker
└── README.md                 # Este archivo
```

## 🛠️ Comandos Git para el Equipo

### **Configuración Inicial (Primera vez):**
```bash
# Clonar el repositorio
git clone https://github.com/Abrahan-Eagle/corral-x-one-page.git
cd corral-x-one-page

# Configurar usuario
git config --global user.name "Tu Nombre"
git config --global user.email "tu@email.com"
```

### **Flujo de Trabajo Diario:**
```bash
# 1. Sincronizar con el repositorio
git pull origin main

# 2. Hacer cambios en los archivos

# 3. Ver qué cambió
git status
git diff

# 4. Agregar cambios
git add .

# 5. Hacer commit
git commit -m "📝 Descripción del cambio"

# 6. Subir y activar deploy automático
git push origin main
```

### **Comandos Esenciales:**
```bash
git status          # Ver estado
git add .           # Agregar cambios
git commit -m "mensaje"  # Hacer commit
git push origin main     # Subir cambios
git pull origin main     # Bajar cambios
```

## 🔄 Proceso de Deploy Automático

### **Cuando haces `git push origin main`:**
1. **GitHub Actions** detecta el push
2. **Se ejecuta** el workflow automáticamente
3. **Se validan** los archivos del proyecto
4. **Se limpia** completamente el servidor FTP
5. **Se suben** los nuevos archivos
6. **Se confirma** el deploy exitoso

### **Ventajas del Deploy Automático:**
- 🧹 **Siempre limpio** - Sin archivos obsoletos
- ⚡ **Rápido** - Deploy en segundos
- 🔄 **Consistente** - Mismo proceso cada vez
- 📱 **Accesible** - Cualquier miembro del equipo puede hacer deploy

## 🎨 Personalización del Sitio

### **Archivos Principales:**
- **`index.html`** - Contenido principal del sitio
- **`manifest.json`** - Configuración de la PWA
- **`sw.js`** - Service Worker para funcionalidades offline

### **Modificaciones Comunes:**
- **Cambiar colores:** Editar variables CSS en `:root`
- **Modificar logos:** Cambiar URLs en `index.html`
- **Actualizar contenido:** Editar texto en `index.html`
- **Cambiar estilos:** Modificar CSS en la sección `<style>`

## 🚨 Solución de Problemas

### **Error: "Workflow failed"**
1. Verificar que todos los secrets estén configurados
2. Revisar logs del workflow en GitHub Actions
3. Verificar conectividad FTP

### **Error: "Permission denied"**
1. Verificar credenciales FTP en los secrets
2. Confirmar permisos del usuario FTP
3. Verificar directorio de destino

### **Error: "Deploy failed"**
1. Revisar logs del workflow
2. Verificar espacio en disco del servidor
3. Confirmar configuración del servidor FTP

## 📞 Soporte y Mantenimiento

### **Logs Importantes:**
- **GitHub Actions:** `.github/workflows/deploy.yml`
- **Workflow:** Actions → Deploy Corral X to Server

### **Comandos de Diagnóstico:**
```bash
# Ver estado del repositorio
git status

# Ver historial de commits
git log --oneline

# Ver cambios pendientes
git diff

# Ver ramas disponibles
git branch -a
```

## 🎯 Próximos Pasos

1. **Configura los secrets** en GitHub (FTP_SERVER, FTP_USERNAME, FTP_PASSWORD)
2. **Haz un push** para activar el primer deploy automático
3. **Verifica** que el sitio se despliegue correctamente
4. **Comparte** este README con tu equipo

## 🚀 ¡Listo para Usar!

Con esta configuración:
- ✅ **Deploy automático** en cada push a main
- ✅ **Limpieza automática** del servidor
- ✅ **Validación** de archivos antes del deploy
- ✅ **Proceso estandarizado** para todo el equipo

¡Tu pipeline de CI/CD está listo para revolucionar la ganadería! 🐄🚀

---

## 📝 Notas del Desarrollador

- **Framework:** Bootstrap 5.3.3
- **PWA:** Manifest + Service Worker
- **Deploy:** GitHub Actions + FTP
- **Limpieza:** `dangerous-clean-slate: true`
- **Validación:** Verificación de archivos antes del deploy

**Última actualización:** $(date)
**Versión:** 1.0.0
**Estado:** ✅ Producción
