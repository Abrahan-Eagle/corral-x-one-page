# 📋 PLAN DE MIGRACIÓN: Template corral-x-one-page → Laravel Blade

## 🎯 OBJETIVO
Migrar el template HTML estático `corral-x-one-page` a vistas Blade en `CorralX-Backend`, manteniendo todo el SEO/SGE/ASO y diseño responsive, sin afectar el backend móvil (`/api/*`).

---

## 📊 ANÁLISIS ACTUAL

### Template (corral-x-one-page):
- **Estructura**: One-page con secciones por ID (`#inicio`, `#caracteristicas`, `#beneficios`, `#como-funciona`, `#faq`, `#descargar`)
- **Assets**: `assets/` (badges, Favicon, images), `css/styles.css`
- **SEO**: Meta tags completos, Schema.org JSON-LD, robots.txt, sitemap.xml
- **PWA**: manifest.json, sw.js

### Laravel Backend Actual:
- **Vistas Frontend**: `resources/views/front/` (home, about, services, blog, project, contact)
- **Controlador**: `App\Http\Controllers\Web\Front\IndexController`
- **Rutas**: `/`, `/about`, `/services`, `/contact`, `/blog`, `/projects`
- **Assets**: Compilados con Laravel Mix desde `resources/assets/`

---

## 🗂️ ESTRUCTURA PROPUESTA

### 1. VISTAS BLADE (resources/views/front/)

```
resources/views/front/
├── layouts/
│   └── app-front.blade.php          # Layout principal (head, navbar, footer)
├── components/
│   ├── navbar.blade.php             # Navbar del template
│   ├── hero-section.blade.php        # Sección Hero (#inicio)
│   ├── features-section.blade.php   # Características (#caracteristicas)
│   ├── benefits-section.blade.php   # Beneficios (#beneficios)
│   ├── how-it-works.blade.php       # ¿Cómo funciona? (#como-funciona)
│   ├── faq-section.blade.php        # FAQ (#faq)
│   ├── download-section.blade.php   # Descargar (#descargar)
│   └── footer.blade.php             # Footer
├── welcome.blade.php                # Vista principal (one-page completa)
└── [MANTENER si se necesitan]:
    ├── about.blade.php              # Si se requiere página separada
    ├── services.blade.php            # Si se requiere página separada
    └── contact.blade.php            # Si se requiere página separada
```

### 2. ASSETS (resources/assets/front/)

```
resources/assets/front/
├── css/
│   └── styles.css                   # CSS del template (copiado desde corral-x-one-page)
├── js/
│   └── front.js                     # JavaScript del template (si existe)
└── images/
    ├── badges/                      # Badges de tiendas
    ├── Favicon/                     # Favicons
    └── phone-mockup.jpg             # Imagen del teléfono
```

### 3. PUBLIC (public/)

```
public/
├── assets/
│   └── front/                       # Assets compilados/copiados
│       ├── css/
│       ├── js/
│       └── images/
└── [MANTENER estructura actual]
```

---

## 📝 PASOS DE MIGRACIÓN

### FASE 1: Preparación de Assets ⚙️

1. **Copiar assets del template:**
   ```bash
   # Desde corral-x-one-page/assets/ → resources/assets/front/
   - assets/badges/ → resources/assets/front/images/badges/
   - assets/Favicon/ → resources/assets/front/images/Favicon/
   - assets/images/ → resources/assets/front/images/
   - assets/LOGO_CORRAL.png → resources/assets/front/images/
   ```

2. **Copiar CSS:**
   ```bash
   # Desde corral-x-one-page/css/styles.css → resources/assets/front/css/styles.css
   ```

3. **Actualizar webpack.mix.js:**
   ```javascript
   // Agregar compilación de CSS frontend
   .styles('resources/assets/front/css/styles.css', 'public/css/front.css')
   
   // Copiar imágenes frontend
   .copyDirectory("resources/assets/front/images", "public/assets/front/images")
   ```

### FASE 2: Crear Layout Principal 🎨

1. **Crear `resources/views/front/layouts/app-front.blade.php`:**
   - Extraer `<head>` completo del template (meta tags, SEO, Schema.org)
   - Incluir Bootstrap 5.3.3 (CDN o compilado)
   - Incluir Google Fonts (Inter)
   - Incluir favicons
   - Incluir `css/front.css` compilado
   - Estructura: `@yield('content')` para el body

2. **Convertir Schema.org JSON-LD a Blade:**
   - Mover JSON-LD del `<head>` a sección `@section('schema')`
   - Usar `@json()` para datos dinámicos si es necesario

### FASE 3: Crear Componentes Blade 🧩

1. **Navbar (`components/navbar.blade.php`):**
   - Extraer navbar del template
   - Convertir enlaces estáticos a `{{ route() }}` si es necesario
   - Mantener estructura responsive

2. **Hero Section (`components/hero-section.blade.php`):**
   - Sección `#inicio` completa
   - Badges de descarga (App Store, Google Play, Microsoft)

3. **Features Section (`components/features-section.blade.php`):**
   - Sección `#caracteristicas`
   - Cards de características

4. **Benefits Section (`components/benefits-section.blade.php`):**
   - Sección `#beneficios`
   - Lista de beneficios

5. **How It Works (`components/how-it-works.blade.php`):**
   - Sección `#como-funciona`
   - Pasos del proceso

6. **FAQ Section (`components/faq-section.blade.php`):**
   - Sección `#faq`
   - Acordeón de preguntas

7. **Download Section (`components/download-section.blade.php`):**
   - Sección `#descargar`
   - Badges de descarga

8. **Footer (`components/footer.blade.php`):**
   - Footer completo
   - Enlaces legales (política, términos, eliminar cuenta)

### FASE 4: Vista Principal (One-Page) 🏠

1. **Crear `resources/views/front/welcome.blade.php`:**
   ```blade
   @extends('front.layouts.app-front')
   
   @section('content')
       @include('front.components.navbar')
       @include('front.components.hero-section')
       @include('front.components.features-section')
       @include('front.components.benefits-section')
       @include('front.components.how-it-works')
       @include('front.components.faq-section')
       @include('front.components.download-section')
       @include('front.components.footer')
   @endsection
   ```

2. **Mantener SEO/SGE/ASO:**
   - Todos los meta tags en `@section('meta')`
   - Schema.org JSON-LD en `@section('schema')`
   - Robots.txt y sitemap.xml en `public/` (copiar desde template)

### FASE 5: Actualizar Controlador 🎮

1. **Modificar `IndexController@index()`:**
   ```php
   public function index()
   {
       // Si el template es one-page, no necesita datos
       // O si necesita datos dinámicos, agregarlos aquí
       return view('front.welcome');
   }
   ```

2. **Evaluar métodos `about()` y `services()`:**
   - Si el template es one-page, estas rutas pueden redirigir a `#about` y `#services`
   - O mantener vistas separadas si se requieren

### FASE 6: Rutas 🛣️

**Opción A: One-Page (Recomendada)**
```php
Route::get('/', [IndexController::class, 'index'])->name('front.home');
// Rutas adicionales redirigen a secciones con anchor
Route::get('/about', function() { return redirect('/#caracteristicas'); });
Route::get('/services', function() { return redirect('/#beneficios'); });
```

**Opción B: Páginas Separadas**
```php
Route::get('/', [IndexController::class, 'index'])->name('front.home');
Route::get('/about', [IndexController::class, 'about'])->name('about');
Route::get('/services', [IndexController::class, 'services'])->name('services');
```

### FASE 7: PWA y SEO 📱

1. **Copiar `manifest.json` a `public/`:**
   - Actualizar rutas de assets a `{{ asset() }}` si es necesario

2. **Copiar `sw.js` a `public/`:**
   - Actualizar rutas de cache

3. **Copiar `robots.txt` y `sitemap.xml` a `public/`**

4. **Verificar Schema.org:**
   - Mantener todos los JSON-LD del template
   - Convertir a `@section('schema')` en layout

---

## 🔄 CONVERSIONES NECESARIAS

### HTML → Blade

1. **Rutas de Assets:**
   ```html
   <!-- ANTES -->
   <img src="assets/LOGO_CORRAL.png">
   
   <!-- DESPUÉS -->
   <img src="{{ asset('assets/front/images/LOGO_CORRAL.png') }}">
   ```

2. **CSS/JS:**
   ```html
   <!-- ANTES -->
   <link rel="stylesheet" href="css/styles.css">
   
   <!-- DESPUÉS -->
   <link rel="stylesheet" href="{{ mix('css/front.css') }}">
   ```

3. **Meta Tags Dinámicos:**
   ```blade
   <meta property="og:url" content="{{ url('/') }}">
   <link rel="canonical" href="{{ url('/') }}">
   ```

4. **JavaScript:**
   ```html
   <!-- ANTES -->
   <script src="js/app.js"></script>
   
   <!-- DESPUÉS -->
   <script src="{{ mix('js/front.js') }}"></script>
   ```

---

## ✅ CHECKLIST DE MIGRACIÓN

### Preparación
- [ ] Copiar assets del template a `resources/assets/front/`
- [ ] Copiar CSS a `resources/assets/front/css/styles.css`
- [ ] Actualizar `webpack.mix.js` para compilar assets frontend
- [ ] Ejecutar `npm run dev` para compilar

### Layout y Componentes
- [ ] Crear `layouts/app-front.blade.php` con head completo
- [ ] Crear componente `navbar.blade.php`
- [ ] Crear componente `hero-section.blade.php`
- [ ] Crear componente `features-section.blade.php`
- [ ] Crear componente `benefits-section.blade.php`
- [ ] Crear componente `how-it-works.blade.php`
- [ ] Crear componente `faq-section.blade.php`
- [ ] Crear componente `download-section.blade.php`
- [ ] Crear componente `footer.blade.php`

### Vista Principal
- [ ] Crear `welcome.blade.php` que incluya todos los componentes
- [ ] Verificar que todas las secciones se muestren correctamente
- [ ] Verificar responsive design

### SEO/SGE/ASO
- [ ] Mantener todos los meta tags en el layout
- [ ] Mantener Schema.org JSON-LD
- [ ] Copiar `robots.txt` a `public/`
- [ ] Copiar `sitemap.xml` a `public/`
- [ ] Copiar `manifest.json` a `public/`
- [ ] Copiar `sw.js` a `public/`

### Controlador y Rutas
- [ ] Actualizar `IndexController@index()` para retornar `welcome.blade.php`
- [ ] Decidir si mantener `/about` y `/services` o redirigir
- [ ] Verificar que todas las rutas funcionen

### Testing
- [ ] Verificar que el diseño se vea igual al template
- [ ] Verificar responsive en móvil, tablet, desktop
- [ ] Verificar que todos los assets se carguen correctamente
- [ ] Verificar SEO (meta tags, Schema.org)
- [ ] Verificar que no se rompa el backend móvil (`/api/*`)

---

## 🚨 CONSIDERACIONES IMPORTANTES

1. **NO tocar rutas `/api/*`** - El backend móvil debe seguir funcionando
2. **Mantener estructura de carpetas Laravel** - Usar `resources/` y `public/`
3. **Compilar assets con Laravel Mix** - No usar assets directamente desde `public/`
4. **Mantener SEO/SGE/ASO** - Todo el trabajo de SEO debe preservarse
5. **Componentes reutilizables** - Crear componentes Blade para facilitar mantenimiento

---

## 📦 ARCHIVOS A ELIMINAR (Opcional)

Después de migrar, evaluar si se eliminan:
- `resources/views/front/home.blade.php` (si se reemplaza por `welcome.blade.php`)
- `resources/views/front/about.blade.php` (si se usa one-page)
- `resources/views/front/services.blade.php` (si se usa one-page)
- Componentes antiguos que no se usen

---

## 🎯 RESULTADO ESPERADO

- ✅ Template migrado completamente a Blade
- ✅ SEO/SGE/ASO preservado
- ✅ Diseño responsive mantenido
- ✅ Assets compilados con Laravel Mix
- ✅ Componentes reutilizables
- ✅ Backend móvil intacto (`/api/*` funcionando)

---

**¿Aprobamos este plan antes de comenzar la migración?**

