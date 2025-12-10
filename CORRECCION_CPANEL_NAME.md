# 🔧 Corrección: Campo Name en cPanel Zone Editor

## ❌ Error Detectado

El error dice:
> "The DNS label must contain only the following characters: A-Z, a-z, 0-9, -, and _"

**Problema:** El campo "Name" no acepta el símbolo `@` directamente.

---

## ✅ SOLUCIÓN: Campo Name Correcto

En cPanel Zone Editor, para el dominio raíz (`corralx.com`), el campo **"Name"** debe estar:

### Opción 1: Campo Name VACÍO (Recomendado)
- **Name:** (déjalo **VACÍO** o en **blanco**)
- Esto creará el registro para el dominio raíz `corralx.com`

### Opción 2: Solo el Dominio (Sin @)
- **Name:** `corralx.com` (sin el símbolo `@`)
- Sin punto al final

---

## 📋 Formulario Correcto

Cuando hagas clic en **"Add Record"** o **"Add TXT Record"**, completa así:

| Campo | Valor Correcto |
|-------|----------------|
| **Name** | **(DÉJALO VACÍO)** o `corralx.com` |
| **TTL** | `14400` (o valor por defecto) |
| **Type** | `TXT` |
| **Record** | `google-site-verification=5NIhlSQUqE0nytWg9JF24oMgxSLKbMzYl_rC0ZxnQ2k` |

⚠️ **IMPORTANTE:**
- **NO uses `@`** en el campo Name
- **NO uses `@corralx.com`**
- **Déjalo VACÍO** o usa solo `corralx.com`

---

## 🚀 Pasos Corregidos

1. **Haz clic en "Add Record" o "Add TXT Record"**
2. **En el campo "Name":**
   - **Déjalo VACÍO** (recomendado)
   - O escribe solo: `corralx.com` (sin @, sin punto al final)
3. **TTL:** `14400` (o valor por defecto)
4. **Type:** `TXT`
5. **Record:** `google-site-verification=5NIhlSQUqE0nytWg9JF24oMgxSLKbMzYl_rC0ZxnQ2k`
6. **Guarda**

---

## ✅ Verificación

Después de guardar, en la tabla deberías ver:

```
Name: corralx.com (o vacío, dependiendo de cómo cPanel lo muestre)
TTL: 14400
Type: TXT
Record: google-site-verification=5NIhlSQUqE0nytWg9JF24oMgxSLKbMzYl_rC0ZxnQ2k
```

---

## 💡 Nota

Si ves un registro con `@corralx.com` que tiene error, puedes:
1. **Editarlo** (botón azul "Edit")
2. **Cambiar el Name** a vacío o solo `corralx.com`
3. **Guardar**

O simplemente:
1. **Eliminarlo** (botón rojo "Delete")
2. **Crear uno nuevo** con el Name correcto (vacío o `corralx.com`)

---

**Intenta de nuevo con el campo Name VACÍO o solo `corralx.com` (sin @).**

