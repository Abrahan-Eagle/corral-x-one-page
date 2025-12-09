# 🧹 PLAN DE DEPURACIÓN Y MIGRACIÓN COMPLETA

## 📋 PROPUESTA PARA `/about` y `/services`

**Opción Recomendada:** **ELIMINAR** las rutas `/about` y `/services` porque:
- El template es one-page con todas las secciones en una sola vista
- No hay necesidad de rutas separadas
- Simplifica la estructura

**Alternativa:** Si quieres mantener compatibilidad con enlaces antiguos, redirigir:
```php
Route::get('/about', function() { return redirect('/#caracteristicas'); });
Route::get('/services', function() { return redirect('/#beneficios'); });
```

---

## 🗑️ ARCHIVOS A ELIMINAR (Depuración)

### 1. VISTAS FRONTEND OBSOLETAS
```
resources/views/front/
├── ❌ about.blade.php              # Reemplazado por sección en welcome.blade.php
├── ❌ services.blade.php            # Reemplazado por sección en welcome.blade.php
├── ❌ home.blade.php                # Reemplazado por welcome.blade.php
├── ❌ blog.blade.php                # ELIMINAR (no se usa blog)
├── ❌ blog-details.blade.php        # ELIMINAR (no se usa blog)
├── ❌ project.blade.php             # ELIMINAR (no se usa projects)
├── ❌ project-details.blade.php     # ELIMINAR (no se usa projects)
├── ❌ contact.blade.php             # EVALUAR (¿se mantiene o se integra en one-page?)
└── component/
    ├── ❌ aiblock-plans-section.blade.php  # No se usa
    ├── ❌ callto-section.blade.php        # No se usa
    ├── ❌ reply.blade.php                 # No se usa (blog)
    └── ❌ team-section.blade.php          # No se usa
```

### 2. CONTROLADORES A ELIMINAR
```
app/Http/Controllers/Web/Front/
├── ❌ BlogController.php            # ELIMINAR (no se usa blog)
└── ❌ ProjectsController.php        # ELIMINAR (no se usa projects)
```

### 3. RUTAS A ELIMINAR
```php
// ELIMINAR de web.php:
❌ Route::get('/about', ...)         # Eliminar o redirigir
❌ Route::get('/services', ...)      # Eliminar o redirigir
❌ Route::get('/blog', ...)          # Todas las rutas de blog
❌ Route::get('/post/{slug}', ...)
❌ Route::get('/category/{slug}', ...)
❌ Route::get('/categorie/{slug}', ...)
❌ Route::get('/tag/{slug}', ...)
❌ Route::get('/tag-{slug}', ...)
❌ Route::post('/comment/{post_id}', ...)
❌ Route::get('/reply', ...)
❌ Route::get('/like/{slug}', ...)
❌ Route::get('/dislike/{slug}', ...)
❌ Route::get('/projects', ...)      # Todas las rutas de projects
❌ Route::get('/post-projects/{slug}', ...)
❌ Route::get('/category-projects/{slug}', ...)
❌ Route::get('/categorie-projects/{slug}', ...)
❌ Route::get('/tag-project/{slug}', ...)
❌ Route::get('/tag-projects/{slug}', ...)
❌ Route::post('/comment-projects/{post_id}', ...)
❌ Route::get('/reply-projects', ...)
❌ Route::get('/like-projects/{slug}', ...)
❌ Route::get('/dislike-projects/{slug}', ...)
```

### 4. MÉTODOS DEL IndexController A ELIMINAR
```php
app/Http/Controllers/Web/Front/IndexController.php
├── ❌ public function about()       # Eliminar
└── ❌ public function services()     # Eliminar
```

### 5. EVALUAR: ContactController
```
app/Http/Controllers/Web/Front/ContactController.php
```
**Decisión:** ¿Mantener `/contact` como ruta separada o integrar formulario de contacto en el one-page?

**Recomendación:** Mantener `/contact` si tiene funcionalidad backend (envío de emails, etc.)

---

## ✅ ARCHIVOS A MANTENER

### Controladores
```
app/Http/Controllers/Web/Front/
├── ✅ IndexController.php           # Modificar método index() para retornar welcome.blade.php
├── ✅ ContactController.php         # MANTENER (si se usa)
└── ✅ NewsletterController.php      # MANTENER (si se usa)
```

### Rutas a Mantener
```php
✅ Route::get('/', [IndexController::class, 'index'])->name('front.home');
✅ Route::get('/contact', [ContactController::class, 'contact'])->name('contact');  // Si se mantiene
✅ Route::post('/contact/store', [ContactController::class, 'store'])->name('contact.submit');
✅ Route::post('/newsletter', [NewsletterController::class, 'store'])->name('newsletter.submit');
```

---

## 📦 ESTRUCTURA FINAL PROPUESTA

### Vistas Frontend
```
resources/views/front/
├── layouts/
│   └── app-front.blade.php          # Layout principal con SEO
├── components/
│   ├── navbar.blade.php
│   ├── hero-section.blade.php
│   ├── features-section.blade.php
│   ├── benefits-section.blade.php
│   ├── how-it-works.blade.php
│   ├── faq-section.blade.php
│   ├── download-section.blade.php
│   └── footer.blade.php
└── welcome.blade.php                # Vista one-page principal
```

### Assets
```
resources/assets/front/
├── css/
│   └── styles.css                   # CSS del template
└── images/
    ├── badges/
    ├── Favicon/
    └── phone-mockup.jpg
```

### Public (compilado)
```
public/
├── css/
│   └── front.css                    # Compilado desde resources/assets/front/css/styles.css
├── assets/
│   └── front/
│       └── images/                  # Copiado desde resources/assets/front/images/
├── manifest.json                    # Copiado desde template
├── sw.js                            # Copiado desde template
├── robots.txt                       # Copiado desde template
└── sitemap.xml                      # Copiado desde template
```

---

## 🚀 ORDEN DE EJECUCIÓN

1. ✅ **FASE 1: Preparación de Assets**
   - Copiar assets del template
   - Copiar CSS
   - Actualizar webpack.mix.js

2. ✅ **FASE 2: Crear Estructura Blade**
   - Layout principal
   - Componentes
   - Vista welcome.blade.php

3. ✅ **FASE 3: Actualizar Controladores y Rutas**
   - Modificar IndexController
   - Eliminar rutas de blog/projects
   - Decidir sobre /about y /services

4. ✅ **FASE 4: Depuración**
   - Eliminar vistas obsoletas
   - Eliminar controladores obsoletos
   - Limpiar rutas

5. ✅ **FASE 5: PWA y SEO**
   - Copiar manifest.json, sw.js
   - Copiar robots.txt, sitemap.xml

6. ✅ **FASE 6: Compilar y Verificar**
   - npm run dev
   - Verificar funcionamiento
   - Verificar que no se rompa /api/*

---

## ⚠️ ADVERTENCIAS

- **NO eliminar** nada relacionado con `/api/*` (backend móvil)
- **NO eliminar** rutas del dashboard (`/home`, `/dashboard`, etc.)
- **NO eliminar** controladores del dashboard
- **Verificar** que ContactController y NewsletterController se usen antes de eliminar

---

## ❓ DECISIONES PENDIENTES

1. **¿Eliminar o redirigir `/about` y `/services`?**
   - Recomendación: ELIMINAR

2. **¿Mantener `/contact` como ruta separada?**
   - Recomendación: MANTENER si tiene funcionalidad backend

3. **¿Eliminar NewsletterController si no se usa?**
   - Verificar primero si se usa en el template

