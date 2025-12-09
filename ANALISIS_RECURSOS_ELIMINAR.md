# Análisis de Recursos para Eliminar

## 📋 Resumen
Análisis de la carpeta `resources/` para identificar código y assets no utilizados que pueden ser eliminados.

---

## ✅ RECURSOS QUE SE PUEDEN ELIMINAR

### 1. **resources/assets/img/blog/** (Blog eliminado)
- **Razón**: Las rutas de blog fueron eliminadas (`/blog`, `/post/{slug}`, etc.)
- **Contenido**: 
  - blog-1.jpg, blog-2.jpg, blog-hero.jpg, blog-x.jpeg
  - ri-1.jpg, ri-2.jpg, ri-3.jpg (relacionados)
  - next.jpg, prev.jpg (navegación de blog)
- **Tamaño estimado**: ~500KB - 2MB

### 2. **resources/assets/img/portafolio/** (Projects eliminado)
- **Razón**: Las rutas de projects fueron eliminadas (`/projects`, `/post-projects/{slug}`, etc.)
- **Contenido**: 
  - portfolio-1.jpg hasta portfolio-9.jpg
  - programing-uniblock-projects-*.png (67 archivos)
  - render-3d-uniblock-projects-*.png (47 archivos)
- **Tamaño estimado**: ~50MB - 100MB (muchos PNG grandes)

### 3. **resources/assets/img/about/** (About eliminado)
- **Razón**: La ruta `/about` fue eliminada
- **Contenido**: 
  - about-1.jpg, about-2.jpg, about-3.jpg
  - robot-uniblockweb-tecnologia-programacion-inteligencia-artificial.png
  - uniblock-negocio.png, uniblock-people.png
  - Uniblock negro.png, Uniblock-negro.png
- **Tamaño estimado**: ~2MB - 5MB

### 4. **resources/assets/sass/** (SCSS del template antiguo)
- **Razón**: Estos archivos SCSS son del template antiguo que ya no se usa
- **Contenido**: 
  - _about.scss, _blog.scss, _blog-details.scss, _portfolio.scss, _services.scss
  - _home-page.scss (template antiguo)
  - style.scss (puede tener referencias al template antiguo)
- **Nota**: Verificar si `style.scss` se usa antes de eliminar
- **Tamaño estimado**: ~50KB

### 5. **resources/assets/css/flaticon.css** (No se usa)
- **Razón**: Está comentado en `webpack.mix.js` (línea 31)
- **Tamaño estimado**: ~10KB

### 6. **resources/views/welcome.blade.php** (Vista antigua)
- **Razón**: El nuevo frontend usa `resources/views/front/welcome.blade.php`
- **Contenido**: Vista antigua con referencias a `/about`, `/services`, `/blog`
- **Verificación**: No se encuentra en ningún controlador (IndexController usa `front.welcome`)

### 7. **resources/js/components/** (Vacío pero con referencias en código compilado)
- **Razón**: El directorio está vacío, pero el código compilado (`dashboard.js`) tiene referencias a:
  - CreateAuthorComponent.vue
  - CreateSponsorComponent.vue
  - CreateCategoryComponent.vue
  - CreateTagComponent.vue
  - CreatePostComponent.vue
  - CheckBoxComponent.vue
- **Nota**: Estos componentes eran para Blog/Projects que ya no existen
- **Acción**: Limpiar referencias en `resources/js/app.js` si existen

### 8. **resources/css/app.css** (No se usa directamente)
- **Razón**: No se referencia en `webpack.mix.js`
- **Nota**: Puede ser generado automáticamente, verificar antes de eliminar

---

## ⚠️ RECURSOS QUE SE DEBEN MANTENER

### 1. **resources/assets2/** ✅
- **Razón**: Se usa en `webpack.mix.js` (línea 66: `.copyDirectory("resources/assets2", "public")`)
- **Contenido**: CSS del dashboard, fonts CoreUI, icons, imágenes del dashboard

### 2. **resources/coreui-x/** ✅
- **Razón**: Se usa en `webpack.mix.js` (líneas 33, 53-54, 65)
- **Contenido**: CSS y JS del dashboard CoreUI

### 3. **resources/sass/app.scss** ✅
- **Razón**: Se compila en `webpack.mix.js` (línea 37)
- **Contenido**: Estilos principales de Laravel

### 4. **resources/js/app.js** ✅
- **Razón**: Se compila en `webpack.mix.js` (línea 59)
- **Contenido**: JavaScript principal de Laravel

### 5. **resources/assets/front/** ✅
- **Razón**: Template nuevo de Corral X One-Page
- **Contenido**: CSS, imágenes, favicons del nuevo frontend

### 6. **resources/assets/css/** (excepto flaticon.css) ✅
- **Razón**: Se usan en `webpack.mix.js` (líneas 23-30)
- **Contenido**: Bootstrap, Font Awesome, Elegant Icons, etc.

### 7. **resources/assets/js/** ✅
- **Razón**: Se usan en `webpack.mix.js` (líneas 42-50)
- **Contenido**: jQuery, Bootstrap, plugins del frontend

### 8. **resources/assets/fonts/** ✅
- **Razón**: Se copian en `webpack.mix.js` (línea 63)
- **Contenido**: Fuentes del frontend

### 9. **resources/assets/img/** (excepto blog, portafolio, about) ✅
- **Razón**: Algunas imágenes pueden estar en uso
- **Contenido**: favicon, hero, team, testimonial, etc.
- **Nota**: Revisar individualmente antes de eliminar

### 10. **resources/lang/** ✅
- **Razón**: Validaciones de Laravel
- **Contenido**: Mensajes de validación en español

---

## 📊 ESTIMACIÓN DE ESPACIO LIBERADO

- **Blog**: ~500KB - 2MB
- **Portfolio**: ~50MB - 100MB (el más grande)
- **About**: ~2MB - 5MB
- **SCSS antiguo**: ~50KB
- **flaticon.css**: ~10KB
- **welcome.blade.php**: ~20KB

**Total estimado**: ~52MB - 107MB

---

## 🔧 ACCIONES RECOMENDADAS

1. **Eliminar inmediatamente**:
   - `resources/assets/img/blog/`
   - `resources/assets/img/portafolio/`
   - `resources/assets/img/about/`
   - `resources/assets/css/flaticon.css`
   - `resources/views/welcome.blade.php` (si no se usa)

2. **Revisar antes de eliminar**:
   - `resources/assets/sass/` (verificar si `style.scss` se usa)
   - `resources/css/app.css` (verificar si se genera automáticamente)
   - Limpiar referencias a componentes Vue antiguos en `resources/js/app.js`

3. **Después de eliminar**:
   - Ejecutar `npm run dev` para verificar que no hay errores
   - Ejecutar `php artisan test` para verificar que todo funciona
   - Verificar que el dashboard y frontend siguen funcionando

---

## ⚠️ ADVERTENCIAS

- **NO eliminar** `resources/assets2/` (usuario lo pidió explícitamente)
- **NO eliminar** imágenes que puedan estar en uso en el dashboard
- **Hacer backup** antes de eliminar archivos grandes
- **Verificar** que las rutas eliminadas realmente no se usan

