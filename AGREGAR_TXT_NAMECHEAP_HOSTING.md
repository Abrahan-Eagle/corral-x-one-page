# ✅ Agregar Registro TXT en Namecheap Hosting (cPanel)

## 📋 Situación Actual

- **Dominio registrado en:** GoDaddy
- **DNS gestionado por:** Namecheap Hosting
- **Name Servers:** `DNS1.NAMECHEAPHOSTING.COM` y `DNS2.NAMECHEAPHOSTING.COM`

**✅ Debes agregar el registro TXT en Namecheap Hosting (cPanel), NO en GoDaddy.**

---

## 🚀 Pasos en Namecheap Hosting (cPanel)

### Paso 1: Acceder a cPanel

1. **Inicia sesión en tu cuenta de Namecheap**
   - Ve a: https://www.namecheap.com
   - Haz clic en **"Sign In"**

2. **Accede a tu hosting**
   - En el menú izquierdo, haz clic en **"Hosting List"**
   - O ve a: https://ap.www.namecheap.com/hosting/list/
   - Encuentra tu plan de hosting

3. **Abre cPanel**
   - Haz clic en **"Manage"** junto a tu plan de hosting
   - Busca el botón **"cPanel"** o **"Go to cPanel"**
   - O accede directamente a: `https://tudominio.com:2083` (reemplaza `tudominio.com` con tu dominio)

---

### Paso 2: Acceder a Zone Editor

1. **En cPanel, busca "Zone Editor"**
   - Puede estar en la sección **"DOMAINS"**
   - O busca en el buscador de cPanel: escribe "Zone" o "DNS"

2. **Haz clic en "Zone Editor"**

---

### Paso 3: Seleccionar el Dominio

1. **En la lista de dominios, encuentra `corralx.com`**
2. **Haz clic en el dominio `corralx.com`**
3. Verás una lista de todos los registros DNS actuales

---

### Paso 4: Agregar el Registro TXT

1. **Haz clic en el botón "Add Record" o "Agregar Registro"**
   - Puede estar arriba o abajo de la lista de registros

2. **Completa el formulario:**

   | Campo | Valor |
   |-------|-------|
   | **Type** (Tipo) | Selecciona **`TXT`** del dropdown |
   | **Name** (Nombre) | Escribe: **`@`** |
   | **TTL** | Deja **`14400`** o el valor por defecto |
   | **TXT Data** | Pega exactamente: **`google-site-verification=5NIhlSQUqE0nytWg9JF24oMgxSLKbMzYl_rC0ZxnQ2k`** |

   ⚠️ **IMPORTANTE:**
   - **Name:** Debe ser exactamente `@` (sin comillas)
   - **TXT Data:** Copia el valor completo sin espacios adicionales
   - **NO elimines** el registro SPF existente (si lo ves)

3. **Haz clic en "Add Record" o "Agregar Registro"**

---

### Paso 5: Verificar que se Agregó

1. **En la lista de registros DNS, deberías ver tu nuevo registro TXT**
2. Debería verse algo así:

   ```
   Type: TXT
   Name: @
   TTL: 14400
   TXT Data: google-site-verification=5NIhlSQUqE0nytWg9JF24oMgxSLKbMzYl_rC0ZxnQ2k
   ```

3. **Verifica que NO eliminaste el registro SPF** (si existía)

---

### Paso 6: Esperar Propagación DNS

1. **Espera 10-30 minutos** para que el DNS se propague
2. **Verifica que el registro esté activo:**

   ```bash
   dig TXT corralx.com
   ```

   **Resultado esperado:**
   Deberías ver **AMBOS** registros:
   - `v=spf1 +a +mx +ip4:68.65.122.55 include:spf.web-hosting.com ~all` (SPF)
   - `google-site-verification=5NIhlSQUqE0nytWg9JF24oMgxSLKbMzYl_rC0ZxnQ2k` (Google)

---

### Paso 7: Verificar en Google Search Console

1. **Espera 10-30 minutos** después de agregar el registro
2. **Vuelve a Google Search Console**
3. **Haz clic en "VERIFICAR"**
4. Si aún no funciona, espera hasta **24 horas** (propagación DNS puede tardar)

---

## 🔍 Acceso Alternativo a cPanel

Si no encuentras cPanel desde el panel de Namecheap:

### Opción 1: URL Directa
```
https://corralx.com:2083
```
O
```
https://cpanel.corralx.com
```

### Opción 2: Desde Namecheap Hosting
1. Ve a **"Hosting List"** en Namecheap
2. Haz clic en **"Manage"** junto a tu plan
3. Busca **"cPanel"** o **"Control Panel"**

### Opción 3: Credenciales de cPanel
- Las credenciales de cPanel pueden estar en el email de bienvenida de Namecheap Hosting
- O en la sección "Hosting List" → "Manage" → "cPanel Login"

---

## 📸 Estructura Visual del Formulario en cPanel Zone Editor

```
┌─────────────────────────────────────────┐
│  Zone Editor - corralx.com             │
├─────────────────────────────────────────┤
│  [Add Record]                           │
├─────────────────────────────────────────┤
│  Type: [TXT ▼]                          │
│  Name: [@                    ]          │
│  TTL:  [14400                ]          │
│  TXT Data: [google-site-verification=...]│
│                                         │
│  [Add Record] [Cancel]                  │
└─────────────────────────────────────────┘
```

---

## ⚠️ Si No Puedes Acceder a cPanel

Si no tienes acceso a cPanel, puedes:

1. **Contactar al soporte de Namecheap Hosting**
   - Pídeles que agreguen el registro TXT por ti
   - Proporciónales esta información:
     - Tipo: TXT
     - Name: @
     - Value: `google-site-verification=5NIhlSQUqE0nytWg9JF24oMgxSLKbMzYl_rC0ZxnQ2k`

2. **O usar el método alternativo en Google Search Console:**
   - Selecciona **"Prefijo de la URL"** en lugar de "Dominio"
   - Usa el método de archivo HTML

---

## ✅ Checklist

- [ ] Accedí a Namecheap → Hosting List
- [ ] Abrí cPanel
- [ ] Encontré "Zone Editor"
- [ ] Seleccioné el dominio `corralx.com`
- [ ] Hice clic en "Add Record"
- [ ] Agregué:
  - [ ] Type: `TXT`
  - [ ] Name: `@`
  - [ ] TXT Data: `google-site-verification=5NIhlSQUqE0nytWg9JF24oMgxSLKbMzYl_rC0ZxnQ2k`
- [ ] Guardé el registro
- [ ] Verifiqué que aparece en la lista
- [ ] Esperé 10-30 minutos
- [ ] Verifiqué con `dig TXT corralx.com` que ambos registros están presentes
- [ ] Volví a Google Search Console y hice clic en "VERIFICAR"

---

**¿Necesitas ayuda para acceder a cPanel? Dime qué ves en tu panel de Namecheap Hosting.**

