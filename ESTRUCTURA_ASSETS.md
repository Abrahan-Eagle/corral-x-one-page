# 📁 Estructura de Assets Organizada

## 🎯 Nueva Estructura

Todo está organizado bajo `resources/assets/` para mantener el proyecto limpio y ordenado.

```
resources/assets/
├── frontend/          # Frontend público (sitio web)
│   ├── legacy/       # Template antiguo
│   │   ├── css/     # Bootstrap, Font Awesome, plugins
│   │   ├── js/      # jQuery, Bootstrap, plugins
│   │   ├── fonts/   # Font Awesome, Elegant Icons
│   │   └── images/  # Imágenes del template antiguo
│   └── corralx/      # Template nuevo Corral X One-Page
│       ├── css/     # styles.css del template nuevo
│       └── images/  # Imágenes, favicons, badges del template nuevo
│
├── dashboard/         # Dashboard admin (panel de control)
│   ├── css/          # app_f.css, dashboard.css
│   ├── js/           # app_f.js, dashboard.js, ckeditor.js, etc.
│   ├── fonts/        # CoreUI Icons, flaticon, icomoon, iconic, ionicons
│   ├── icons/        # Sprites y SVG de CoreUI
│   ├── images/       # Imágenes del dashboard (emails, blog, user/author)
│   └── svg/          # Banderas de países
│
└── coreui/            # CoreUI Framework (framework del dashboard)
    ├── css/          # app.css de CoreUI
    ├── js/           # app.js, app2.js de CoreUI
    ├── fonts/        # CoreUI Icons (Brand, Free)
    ├── icons/        # Sprites y SVG de CoreUI
    ├── svg/          # Banderas de países
    └── assets/       # Brand, favicon, icons, img (avatars)
```

---

## 📋 Descripción de Cada Carpeta

### 1. `resources/assets/frontend/` (8.5MB)
**Propósito**: Assets del frontend público (sitio web visible para usuarios)

#### `frontend/legacy/` - Template Antiguo
- **CSS**: Bootstrap, Font Awesome, Elegant Icons, plugins (owl.carousel, magnific-popup, etc.)
- **JS**: jQuery, Bootstrap, plugins del frontend
- **Fonts**: Font Awesome, Elegant Icons
- **Images**: Imágenes del template antiguo (hero, team, testimonial, work, etc.)

#### `frontend/corralx/` - Template Nuevo Corral X
- **CSS**: `styles.css` del template one-page
- **Images**: 
  - `badges/` - Badges de App Store, Google Play, Microsoft Store
  - `Favicon/` - Favicons del template nuevo
  - `images/` - phone-mockup.jpg
  - `LOGO_CORRAL.png`

---

### 2. `resources/assets/dashboard/` (23MB)
**Propósito**: Assets del dashboard admin (panel de control para administradores)

- **CSS**: `app_f.css`, `dashboard.css`
- **JS**: `app_f.js`, `dashboard.js`, `ckeditor.js`, `config.js`, `es.js`, `lazyload.js`, `styles.js`
- **Fonts**: CoreUI Icons, flaticon, icomoon, iconic, ionicons
- **Icons**: Sprites y SVG de CoreUI (502 archivos SVG)
- **Images**: 
  - `emails/` - Imágenes para emails
  - `front/blog/` - Imágenes de blog (pueden eliminarse si no se usa)
  - `user/author/` - Avatares de autores
- **SVG**: Banderas de países (197 archivos)

---

### 3. `resources/assets/coreui/` (22MB)
**Propósito**: Framework CoreUI completo (base del dashboard)

- **CSS**: `app.css` de CoreUI
- **JS**: `app.js`, `app2.js` de CoreUI
- **Fonts**: CoreUI Icons (Brand, Free)
- **Icons**: Sprites y SVG de CoreUI (duplicado con dashboard, pero necesario)
- **SVG**: Banderas de países (duplicado con dashboard)
- **Assets**: 
  - `brand/` - Logo CoreUI
  - `favicon/` - Favicons de CoreUI
  - `icons/` - 954 archivos SVG de iconos
  - `img/avatars/` - Avatares por defecto (1.jpg - 8.jpg)

---

## ⚙️ Configuración en webpack.mix.js

### CSS
```js
// Frontend Legacy
"resources/assets/frontend/legacy/css/bootstrap.min.css"
// CoreUI
"resources/assets/coreui/css/app.css"
// Frontend Corral X
"resources/assets/frontend/corralx/css/styles.css"
```

### JavaScript
```js
// Frontend Legacy
"resources/assets/frontend/legacy/js/jquery-3.3.1.min.js"
// CoreUI
"resources/assets/coreui/js/app.js"
```

### Copy Directories
```js
// Frontend Legacy
.copyDirectory("resources/assets/frontend/legacy/fonts", "public/fonts")
.copyDirectory("resources/assets/frontend/legacy/images/img", "public/img")
.copyDirectory("resources/assets/frontend/legacy/images/images/user", "public/images/user")

// Frontend Corral X
.copyDirectory("resources/assets/frontend/corralx/images", "public/assets/front/images")

// CoreUI
.copyDirectory("resources/assets/coreui/svg", "public/icons/svg/free")

// Dashboard (completo)
.copyDirectory("resources/assets/dashboard", "public")
```

---

## ✅ Ventajas de Esta Estructura

1. **Organización clara**: Todo bajo `resources/assets/`
2. **Separación por funcionalidad**: Frontend, Dashboard, CoreUI
3. **Fácil identificación**: Nombres descriptivos
4. **Mantenibilidad**: Fácil encontrar y actualizar assets
5. **Escalabilidad**: Fácil agregar nuevos módulos

---

## 📊 Tamaños

- `frontend/`: 8.5MB
- `dashboard/`: 23MB
- `coreui/`: 22MB
- **Total**: ~53.5MB

---

## 🔍 Identificación Rápida

- **Frontend público** → `resources/assets/frontend/`
- **Dashboard admin** → `resources/assets/dashboard/`
- **Framework CoreUI** → `resources/assets/coreui/`

