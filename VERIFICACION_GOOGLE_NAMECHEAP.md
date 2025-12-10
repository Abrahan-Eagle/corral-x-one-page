# 🔧 Verificación Google Search Console - Namecheap

## 📋 Información del Registro

**Tipo:** TXT  
**Host:** `@`  
**Valor:** `google-site-verification=5NIhlSQUqE0nytWg9JF24oMgxSLKbMzYl_rC0ZxnQ2k`  
**TTL:** Automatic (o 30 min)

---

## 🚀 Pasos Detallados en Namecheap

### Paso 1: Iniciar Sesión

1. Ve a **https://www.namecheap.com**
2. Haz clic en **"Sign In"** (Iniciar Sesión)
3. Ingresa tus credenciales

---

### Paso 2: Acceder a la Lista de Dominios

1. Una vez dentro, haz clic en **"Domain List"** (Lista de Dominios) en el menú izquierdo
2. O ve directamente a: **https://ap.www.namecheap.com/domains/list/**
3. Busca el dominio **`corralx.com`** en la lista

---

### Paso 3: Abrir la Gestión del Dominio

1. Encuentra **`corralx.com`** en la lista
2. Haz clic en el botón **"Manage"** (Gestionar) que está a la derecha del dominio
3. Se abrirá la página de gestión del dominio

---

### Paso 4: Acceder a Advanced DNS

1. En la página de gestión, busca la pestaña **"Advanced DNS"** (DNS Avanzado)
2. Haz clic en **"Advanced DNS"**
3. Verás una sección llamada **"Host Records"** (Registros de Host)

---

### Paso 5: Agregar el Registro TXT

1. En la sección **"Host Records"**, busca el botón **"+ Add New Record"** (Agregar Nuevo Registro)
2. Haz clic en **"+ Add New Record"**
3. Se abrirá un formulario para agregar un registro

**Completa el formulario así:**

| Campo | Valor a Ingresar |
|-------|----------------|
| **Type** (Tipo) | Selecciona **`TXT Record`** del dropdown |
| **Host** (Host) | Escribe: **`@`** |
| **Value** (Valor) | Pega exactamente: **`google-site-verification=5NIhlSQUqE0nytWg9JF24oMgxSLKbMzYl_rC0ZxnQ2k`** |
| **TTL** | Deja **`Automatic`** o selecciona **`30 min`** |

⚠️ **IMPORTANTE:**
- El campo **Host** debe ser exactamente: `@` (sin comillas)
- El campo **Value** debe ser exactamente: `google-site-verification=5NIhlSQUqE0nytWg9JF24oMgxSLKbMzYl_rC0ZxnQ2k` (sin comillas)
- **NO elimines** el registro SPF existente (si lo tienes)

---

### Paso 6: Guardar el Registro

1. Después de completar todos los campos, busca el botón **"✓" (checkmark)** o **"Save"** (Guardar)
2. Haz clic en **"✓"** o **"Save"**
3. Verás una confirmación de que el registro se agregó correctamente

---

### Paso 7: Verificar que el Registro se Agregó

1. En la lista de **"Host Records"**, deberías ver tu nuevo registro TXT
2. Debería verse algo así:

```
Type: TXT Record
Host: @
Value: google-site-verification=5NIhlSQUqE0nytWg9JF24oMgxSLKbMzYl_rC0ZxnQ2k
TTL: Automatic
```

3. **NO elimines** otros registros TXT que ya existan (como el SPF)

---

### Paso 8: Esperar la Propagación DNS

1. **Espera 10-30 minutos** para que el DNS se propague
2. Puedes verificar que el registro esté activo usando:

```bash
dig TXT corralx.com
```

O usando herramientas online:
- https://mxtoolbox.com/TXTLookup.aspx
- https://www.whatsmydns.net/#TXT/corralx.com

**Resultado esperado:**
Deberías ver **AMBOS** registros:
- `v=spf1 +a +mx +ip4:68.65.122.55 include:spf.web-hosting.com ~all` (SPF)
- `google-site-verification=5NIhlSQUqE0nytWg9JF24oMgxSLKbMzYl_rC0ZxnQ2k` (Google)

---

### Paso 9: Verificar en Google Search Console

1. Espera **10-30 minutos** después de agregar el registro
2. Vuelve a **Google Search Console**
3. Haz clic en **"VERIFICAR"** o **"ACEPTAR"** y luego **"VERIFICAR"**
4. Si aún no funciona, espera hasta **24 horas** (la propagación DNS puede tardar)

---

## 📸 Estructura Visual del Formulario en Namecheap

```
┌─────────────────────────────────────────┐
│  Host Records                           │
├─────────────────────────────────────────┤
│  [+ Add New Record]                     │
├─────────────────────────────────────────┤
│  Type: [TXT Record ▼]                   │
│  Host: [@                    ]          │
│  Value: [google-site-verification=...]   │
│  TTL:   [Automatic ▼]                   │
│         [✓ Save]                        │
└─────────────────────────────────────────┘
```

---

## ⚠️ Errores Comunes y Soluciones

### Error 1: "Host already exists"
**Solución:** Puedes tener múltiples registros TXT con el mismo Host (`@`). Simplemente agrega otro registro TXT.

### Error 2: "Invalid value"
**Solución:** Asegúrate de copiar **EXACTAMENTE** el valor sin espacios adicionales:
```
google-site-verification=5NIhlSQUqE0nytWg9JF24oMgxSLKbMzYl_rC0ZxnQ2k
```

### Error 3: El registro no aparece después de guardar
**Solución:** 
- Refresca la página
- Verifica que guardaste correctamente (debería aparecer en la lista)
- Espera 5-10 minutos y verifica con `dig TXT corralx.com`

---

## 🔍 Verificación Rápida

### Desde Terminal:
```bash
dig TXT corralx.com +short
```

**Deberías ver:**
```
"v=spf1 +a +mx +ip4:68.65.122.55 include:spf.web-hosting.com ~all"
"google-site-verification=5NIhlSQUqE0nytWg9JF24oMgxSLKbMzYl_rC0ZxnQ2k"
```

### Herramientas Online:
- **MXToolbox:** https://mxtoolbox.com/TXTLookup.aspx
- **What's My DNS:** https://www.whatsmydns.net/#TXT/corralx.com

---

## ✅ Checklist Final

- [ ] Inicié sesión en Namecheap
- [ ] Accedí a "Domain List"
- [ ] Hice clic en "Manage" para `corralx.com`
- [ ] Abrí la pestaña "Advanced DNS"
- [ ] Agregué un nuevo registro TXT con:
  - [ ] Type: `TXT Record`
  - [ ] Host: `@`
  - [ ] Value: `google-site-verification=5NIhlSQUqE0nytWg9JF24oMgxSLKbMzYl_rC0ZxnQ2k`
- [ ] Guardé el registro (✓)
- [ ] Verifiqué que aparece en la lista
- [ ] Esperé 10-30 minutos
- [ ] Verifiqué con `dig TXT corralx.com` que ambos registros están presentes
- [ ] Volví a Google Search Console y hice clic en "VERIFICAR"

---

## 📞 Si Necesitas Ayuda

Si después de seguir estos pasos y esperar 24 horas aún no funciona:

1. **Verifica el registro DNS** con las herramientas online
2. **Contacta al soporte de Namecheap** si el registro no aparece
3. **Prueba otro método de verificación** en Google Search Console:
   - Selecciona "Prefijo de la URL" en lugar de "Dominio"
   - Usa el método de archivo HTML

---

**Última actualización:** 2025-12-09

