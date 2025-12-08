# 🔧 Información Técnica del Workflow

## 📁 Archivo del Workflow
**Ubicación:** `.github/workflows/deploy.yml`

## 🚀 Funcionamiento del Pipeline

### **Trigger:**
- **Push a main** → Deploy automático
- **Manual** → Desde GitHub Actions

### **Pasos del Workflow:**

#### **1. Checkout Repository**
```yaml
- name: Checkout repository
  uses: actions/checkout@v4
  with:
    fetch-depth: 2
```
**Propósito:** Descarga el código del repositorio

#### **2. Check Files**
```yaml
- name: Check files
  run: |
    echo "📁 Archivos a desplegar:"
    ls -la
    echo "✅ Archivos verificados correctamente"
```
**Propósito:** Verifica que los archivos estén presentes

#### **3. Clean Server Files**
```yaml
- name: Clean server files
  uses: SamKirkland/FTP-Deploy-Action@v4.3.5
  with:
    dangerous-clean-slate: true
```
**Propósito:** **BORRA TODOS** los archivos del servidor FTP

#### **4. Deploy New Files**
```yaml
- name: Deploy new files
  uses: SamKirkland/FTP-Deploy-Action@v4.3.5
```
**Propósito:** Sube los nuevos archivos al servidor

## 🔑 Secrets Requeridos

### **Configuración en GitHub:**
1. Ve a tu repositorio
2. **Settings** → **Secrets and variables** → **Actions**
3. Agrega estos secrets:

| Secret | Descripción |
|--------|-------------|
| `FTP_SERVER` | Servidor FTP (ej: ftp.tudominio.com) |
| `FTP_USERNAME` | Usuario FTP |
| `FTP_PASSWORD` | Contraseña FTP |

## ⚠️ Configuraciones Importantes

### **Limpieza del Servidor:**
```yaml
dangerous-clean-slate: true
```
**⚠️ ADVERTENCIA:** Esto borra TODOS los archivos del servidor antes del deploy.

### **Exclusiones:**
```yaml
exclude: |
  .git*
  .github
  README.md
  node_modules
  *.log
```
**Propósito:** No sube archivos innecesarios al servidor.

### **Directorio del Servidor:**
```yaml
server-dir: /
```
**Propósito:** Sube archivos a la raíz del servidor FTP.

## 🔍 Debugging del Workflow

### **Ver Logs:**
1. Ve a tu repositorio en GitHub
2. **Actions** → **Deploy Corral X to Server**
3. Haz clic en la ejecución más reciente
4. Revisa los logs de cada paso

### **Errores Comunes:**
- **"Permission denied"** → Verificar credenciales FTP
- **"Connection failed"** → Verificar servidor FTP
- **"Workflow failed"** → Revisar logs completos

## 🚨 Consideraciones de Seguridad

### **FTP vs SFTP:**
- **FTP** (actual): Transmisión no encriptada
- **SFTP** (recomendado): Transmisión encriptada

### **Permisos:**
- El usuario FTP debe tener permisos de escritura
- El directorio de destino debe ser accesible

## 🔄 Modificaciones del Workflow

### **Para cambiar el servidor:**
Edita el archivo `.github/workflows/deploy.yml` y modifica:
```yaml
server: ${{ secrets.FTP_SERVER }}
```

### **Para cambiar el directorio:**
Modifica:
```yaml
server-dir: tu-directorio/
```

### **Para agregar validaciones:**
Agrega pasos antes del deploy:
```yaml
- name: Custom validation
  run: |
    # Tu validación personalizada aquí
    echo "Validación completada"
```

## 📊 Monitoreo

### **Métricas del Workflow:**
- **Tiempo de ejecución:** Típicamente 1-3 minutos
- **Frecuencia:** Cada push a main
- **Tasa de éxito:** Depende de la conectividad FTP

### **Notificaciones:**
- **GitHub Actions** muestra el estado en tiempo real
- **Logs detallados** disponibles en cada ejecución
- **Historial completo** de todos los deploys

---

**Última actualización:** $(date)
**Versión del Workflow:** 1.0.0
**Estado:** ✅ Activo
