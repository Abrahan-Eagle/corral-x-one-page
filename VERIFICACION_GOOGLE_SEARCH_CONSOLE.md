# Verificación de Google Search Console - corralx.com

## 📋 Información del Registro DNS TXT

**Tipo:** TXT  
**Nombre/Host:** `@` o `corralx.com` (depende del proveedor)  
**Valor:** `google-site-verification=5NIhlSQUqE0nytWg9JF24oMgxSLKbMzYl_rC0ZxnQ2k`  
**TTL:** 3600 (o el valor por defecto)

---

## 🔧 Pasos para Agregar el Registro DNS

### Opción 1: Si usas cPanel

1. **Inicia sesión en cPanel**
2. **Ve a "Zone Editor" o "Editor de Zona DNS"**
3. **Selecciona el dominio `corralx.com`**
4. **Haz clic en "Add Record" o "Agregar Registro"**
5. **Completa los campos:**
   - **Tipo:** `TXT`
   - **Nombre:** `@` o `corralx.com`
   - **TTL:** `3600` (o deja el valor por defecto)
   - **TXT Data:** `google-site-verification=5NIhlSQUqE0nytWg9JF24oMgxSLKbMzYl_rC0ZxnQ2k`
6. **Haz clic en "Add Record" o "Agregar Registro"**
7. **Espera 5-10 minutos** para que se propague el DNS
8. **Vuelve a Google Search Console y haz clic en "VERIFICAR"**

### Opción 2: Si usas GoDaddy

1. **Inicia sesión en GoDaddy**
2. **Ve a "Mis Productos" → "DNS"**
3. **Selecciona el dominio `corralx.com`**
4. **Haz clic en "Agregar" en la sección de registros**
5. **Completa los campos:**
   - **Tipo:** `TXT`
   - **Nombre:** `@`
   - **Valor:** `google-site-verification=5NIhlSQUqE0nytWg9JF24oMgxSLKbMzYl_rC0ZxnQ2k`
   - **TTL:** `600` (10 minutos) o `3600` (1 hora)
6. **Guarda el registro**
7. **Espera 5-10 minutos** para que se propague el DNS
8. **Vuelve a Google Search Console y haz clic en "VERIFICAR"**

### Opción 3: Si usas Namecheap

1. **Inicia sesión en Namecheap**
2. **Ve a "Domain List" → Selecciona `corralx.com` → "Manage"**
3. **Ve a la pestaña "Advanced DNS"**
4. **Haz clic en "Add New Record"**
5. **Completa los campos:**
   - **Tipo:** `TXT Record`
   - **Host:** `@`
   - **Value:** `google-site-verification=5NIhlSQUqE0nytWg9JF24oMgxSLKbMzYl_rC0ZxnQ2k`
   - **TTL:** `Automatic` o `30 min`
6. **Guarda el registro (✓)**
7. **Espera 5-10 minutos** para que se propague el DNS
8. **Vuelve a Google Search Console y haz clic en "VERIFICAR"**

### Opción 4: Si usas Cloudflare

1. **Inicia sesión en Cloudflare**
2. **Selecciona el dominio `corralx.com`**
3. **Ve a "DNS" → "Records"**
4. **Haz clic en "Add record"**
5. **Completa los campos:**
   - **Type:** `TXT`
   - **Name:** `@` o `corralx.com`
   - **Content:** `google-site-verification=5NIhlSQUqE0nytWg9JF24oMgxSLKbMzYl_rC0ZxnQ2k`
   - **TTL:** `Auto` o `3600`
6. **Haz clic en "Save"**
7. **Espera 5-10 minutos** para que se propague el DNS
8. **Vuelve a Google Search Console y haz clic en "VERIFICAR"**

---

## ⚠️ Notas Importantes

1. **Propagación DNS:** Los cambios pueden tardar entre 5 minutos y 48 horas en propagarse. Normalmente toma 10-30 minutos.

2. **Verificación:** Si Google Search Console no encuentra el registro inmediatamente:
   - Espera 10-30 minutos
   - Intenta verificar de nuevo
   - Si después de 24 horas no funciona, verifica que el registro esté correctamente agregado

3. **Múltiples registros TXT:** Puedes tener múltiples registros TXT para el mismo dominio. No elimines otros registros TXT existentes (como SPF, DKIM, etc.).

4. **Formato del valor:** Asegúrate de copiar **exactamente** el valor completo:
   ```
   google-site-verification=5NIhlSQUqE0nytWg9JF24oMgxSLKbMzYl_rC0ZxnQ2k
   ```

---

## 🔍 Verificar que el Registro está Activo

Puedes verificar que el registro DNS está activo usando estos comandos:

### Desde Terminal (Linux/Mac):
```bash
dig TXT corralx.com
```

O:
```bash
nslookup -type=TXT corralx.com
```

### Desde Windows:
```cmd
nslookup -type=TXT corralx.com
```

### Herramientas Online:
- https://mxtoolbox.com/TXTLookup.aspx
- https://www.whatsmydns.net/#TXT/corralx.com

Deberías ver el registro:
```
google-site-verification=5NIhlSQUqE0nytWg9JF24oMgxSLKbMzYl_rC0ZxnQ2k
```

---

## ✅ Después de Agregar el Registro

1. **Espera 10-30 minutos** para la propagación DNS
2. **Vuelve a Google Search Console**
3. **Haz clic en "VERIFICAR"**
4. **Si funciona:** Verás un mensaje de éxito y podrás acceder a Search Console
5. **Si no funciona:** Espera 24 horas y vuelve a intentar

---

## 📝 Resumen Rápido

**Registro a agregar:**
- **Tipo:** TXT
- **Nombre:** `@` o `corralx.com`
- **Valor:** `google-site-verification=5NIhlSQUqE0nytWg9JF24oMgxSLKbMzYl_rC0ZxnQ2k`

**Después de agregarlo:**
1. Espera 10-30 minutos
2. Haz clic en "VERIFICAR" en Google Search Console

