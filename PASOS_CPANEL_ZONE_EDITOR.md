# ✅ Pasos en cPanel Zone Editor - Agregar Registro TXT

## 🎯 Estás en el lugar correcto

Estás en **Zone Editor** de cPanel, viendo los registros DNS de `corralx.com`.

---

## 📋 Pasos para Agregar el Registro TXT

### Paso 1: Haz clic en "Add Record"

1. **Busca el botón azul "Add Record"** en la parte superior derecha de la tabla
2. **Haz clic en "Add Record"**
3. Se abrirá un formulario para agregar un nuevo registro

---

### Paso 2: Completa el Formulario

Cuando se abra el formulario, completa los campos así:

| Campo | Valor a Ingresar |
|-------|----------------|
| **Name** | `@` o `corralx.com.` (con punto al final) |
| **TTL** | `14400` (o deja el valor que aparece por defecto) |
| **Type** | Selecciona **`TXT`** del dropdown |
| **Record** | Pega exactamente: **`google-site-verification=5NIhlSQUqE0nytWg9JF24oMgxSLKbMzYl_rC0ZxnQ2k`** |

⚠️ **IMPORTANTE:**
- **Name:** Puede ser `@` o `corralx.com.` (nota el punto al final si usas el nombre completo)
- **Record:** Copia **EXACTAMENTE** el valor sin espacios adicionales
- **NO elimines** los registros TXT existentes (SPF, DKIM, etc.)

---

### Paso 3: Guardar el Registro

1. **Revisa que todos los campos estén correctos**
2. **Haz clic en el botón "Add Record" o "Save"** (depende de la versión de cPanel)
3. Verás una confirmación de que el registro se agregó

---

### Paso 4: Verificar que se Agregó

1. **En la tabla de registros DNS, busca tu nuevo registro TXT**
2. Puedes usar el **filtro "TXT"** en la parte superior para ver solo los registros TXT
3. Deberías ver algo así:

   ```
   Name: corralx.com. (o @)
   TTL: 14400
   Type: TXT
   Record: google-site-verification=5NIhlSQUqE0nytWg9JF24oMgxSLKbMzYl_rC0ZxnQ2k
   ```

4. **Verifica que NO eliminaste** los otros registros TXT existentes (SPF, DKIM, etc.)

---

### Paso 5: Esperar Propagación DNS

1. **Espera 10-30 minutos** para que el DNS se propague
2. **Verifica que el registro esté activo:**

   ```bash
   dig TXT corralx.com
   ```

   **Resultado esperado:**
   Deberías ver el nuevo registro junto con los existentes:
   - `v=spf1 +a +mx ~all` (SPF - si existe)
   - `v=DKIM1; k=rsa; p=...` (DKIM - si existe)
   - `google-site-verification=5NIhlSQUqE0nytWg9JF24oMgxSLKbMzYl_rC0ZxnQ2k` (Google - NUEVO)

---

### Paso 6: Verificar en Google Search Console

1. **Espera 10-30 minutos** después de agregar el registro
2. **Vuelve a Google Search Console**
3. **Haz clic en "VERIFICAR"**
4. Si aún no funciona, espera hasta **24 horas** (propagación DNS puede tardar)

---

## 🔍 Usar el Filtro TXT (Opcional)

Para ver solo los registros TXT y verificar que se agregó correctamente:

1. **En la barra de filtros, haz clic en "TXT"**
2. Verás solo los registros TXT
3. Deberías ver tu nuevo registro de verificación de Google

---

## ⚠️ Notas Importantes

1. **NO elimines registros TXT existentes** - Puedes tener múltiples registros TXT
2. **El Name puede ser `@` o `corralx.com.`** - Ambos funcionan, pero `@` es más común
3. **El punto al final** - Si usas `corralx.com.` (con punto), está bien. Si usas `@`, no lleva punto
4. **TTL 14400** - Es el valor estándar (4 horas), está bien dejarlo así

---

## ✅ Checklist

- [ ] Hice clic en "Add Record" (botón azul)
- [ ] Completó el formulario:
  - [ ] Name: `@` o `corralx.com.`
  - [ ] TTL: `14400` (o valor por defecto)
  - [ ] Type: `TXT`
  - [ ] Record: `google-site-verification=5NIhlSQUqE0nytWg9JF24oMgxSLKbMzYl_rC0ZxnQ2k`
- [ ] Guardé el registro
- [ ] Verifiqué que aparece en la lista (puedo usar filtro TXT)
- [ ] NO eliminé otros registros TXT existentes
- [ ] Esperé 10-30 minutos
- [ ] Verifiqué con `dig TXT corralx.com` que el registro está presente
- [ ] Volví a Google Search Console y hice clic en "VERIFICAR"

---

## 🎯 Resumen Rápido

1. **Clic en "Add Record"** (botón azul)
2. **Name:** `@`
3. **Type:** `TXT`
4. **Record:** `google-site-verification=5NIhlSQUqE0nytWg9JF24oMgxSLKbMzYl_rC0ZxnQ2k`
5. **Guardar**
6. **Esperar 10-30 minutos**
7. **Verificar en Google Search Console**

---

**¡Estás a un clic de agregar el registro! Haz clic en "Add Record" y completa el formulario.**

