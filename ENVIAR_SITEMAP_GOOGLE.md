# 📋 Enviar Sitemap a Google Search Console

## ✅ Verificación Completada

Ya estás dentro de Google Search Console. Ahora necesitas:

1. **Eliminar el sitemap antiguo** (el que está fallando)
2. **Enviar el sitemap correcto**

---

## 🗑️ Paso 1: Eliminar el Sitemap Antiguo

Veo que hay un sitemap antiguo que está fallando:
- `http://www.corralx.com/sitemap_index.xml`
- Estado: "No se ha podido obtener"
- Fecha: 2013-2014 (muy antiguo)

**Para eliminarlo:**

1. **En la tabla de sitemaps**, encuentra la fila con el sitemap antiguo
2. **Haz clic en los tres puntos verticales** (⋮) al final de la fila
3. **Selecciona "Eliminar" o "Delete"**
4. **Confirma la eliminación**

---

## ✅ Paso 2: Enviar el Sitemap Correcto

### URL del Sitemap a Enviar:

```
https://corralx.com/sitemap.xml
```

**NOTA:** Usa `https://` (no `http://`) y `corralx.com` (no `www.corralx.com`)

---

### Pasos para Enviar:

1. **En la sección "Añadir un sitemap"** (arriba de la tabla)
2. **En el campo "Introduce la URL del sitemap"**, escribe:
   ```
   sitemap.xml
   ```
   O la URL completa:
   ```
   https://corralx.com/sitemap.xml
   ```

3. **Haz clic en el botón "ENVIAR"** (gris, a la derecha del campo)

4. **Espera la confirmación** - Google procesará el sitemap

---

## 🔍 Verificación

Después de enviar, deberías ver:

- **Sitemap:** `https://corralx.com/sitemap.xml`
- **Tipo:** `Sitemap` (no "Desconocido")
- **Estado:** `Correcto` o `Procesado` (en verde)
- **Páginas descubiertas:** Debería mostrar un número (no 0)

---

## ⚠️ Notas Importantes

1. **URL Correcta:**
   - ✅ `https://corralx.com/sitemap.xml`
   - ❌ `http://www.corralx.com/sitemap_index.xml` (antiguo, incorrecto)

2. **Tiempo de Procesamiento:**
   - Google puede tardar unos minutos en procesar el sitemap
   - El estado cambiará de "Pendiente" a "Correcto"

3. **Si el Estado es "No se ha podido obtener":**
   - Verifica que el sitemap sea accesible: https://corralx.com/sitemap.xml
   - Verifica que uses `https://` (no `http://`)
   - Verifica que no uses `www.` (usa solo `corralx.com`)

---

## 📝 Resumen Rápido

1. **Elimina** el sitemap antiguo (los tres puntos → Eliminar)
2. **En el campo "Introduce la URL del sitemap"**, escribe: `sitemap.xml`
3. **Haz clic en "ENVIAR"**
4. **Espera** a que Google procese el sitemap

---

**¿Pudiste enviar el sitemap correcto? Si tienes algún error, dímelo.**

