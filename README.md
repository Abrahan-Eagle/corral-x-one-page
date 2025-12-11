# 📌 Corral X - Backend (Laravel)
## Marketplace de Ganado Venezolano

**Stack:** Laravel 10, PHP 8.2+, MySQL, Sanctum (API tokens), Storage público, WebSockets (Laravel Echo)  
**Estado:** ✅ MVP 100% Completado  
**Última actualización:** 8 de octubre de 2025

---

## 🎯 Visión del Proyecto

Conectar a ganaderos de Venezuela en un marketplace confiable y simple, reduciendo fricción en la compra/venta de ganado. Digitalizamos procesos para generar confianza, ampliar el alcance y acelerar las negociaciones.

### Público y Roles
- **User** (único rol en MVP): puede vender y comprar
- **Admin** (post-MVP): moderación y verificación manual

### Propuesta de Valor
- **Confianza:** Perfiles con reputación (ratings/comentarios) y bandera "verificado"
- **Alcance:** Publicar y encontrar ganado fuera de la zona geográfica habitual
- **Eficiencia:** Fichas estandarizadas y chat 1:1 en tiempo real

---

## ✅ Estado Actual del Proyecto

### Completado
- ✅ Arquitectura de base de datos completa (15+ tablas)
- ✅ Migraciones ejecutadas exitosamente
- ✅ Factories para todos los modelos con datos realistas
- ✅ Seeders con datos del mercado ganadero venezolano
- ✅ Sistema completo de ubicaciones geográficas (683 países, 4,528 estados, 47,123 ciudades, 436 parroquias)
- ✅ Modelos Eloquent con relaciones correctas
- ✅ Controladores API REST funcionales
- ✅ Autenticación con Sanctum
- ✅ Sistema de categorías y productos
- ✅ Sistema de reportes
- ✅ **Tests completos: 27/27 pasando (100%)**

### En Desarrollo
- 🔄 WebSockets para chat en tiempo real
- 🔄 Panel de administración
- 🔄 Evolución del módulo KYC hacia verificación automática (sin revisión manual en el flujo normal)

---

## 🚀 Instalación Rápida

### Requisitos
- PHP 8.2+
- Composer 2.x
- MySQL 8.x (o 5.7+)
- Extensiones: GD, Fileinfo

### Setup
```bash
# 1. Instalar dependencias
composer install

# 2. Configurar entorno
cp .env.example .env
# Editar: DB_*, APP_URL, etc.

# 3. Generar clave
php artisan key:generate

# 4. Migrar y poblar
php artisan migrate:fresh --seed

# 5. Enlazar storage
php artisan storage:link

# 6. Iniciar servidor
php artisan serve --host=0.0.0.0 --port=8000
```

### Datos de Prueba Incluidos
- 3,428 usuarios con diferentes roles
- Haciendas con nombres venezolanos
- Productos de ganado con razas venezolanas
- Sistema completo de ubicaciones geográficas de Venezuela
- 30 códigos de operadoras venezolanas

---

## 📡 API REST - Endpoints Principales

### Autenticación
```
POST   /api/auth/register       # Registro + token
POST   /api/auth/login          # Login + token
POST   /api/auth/google         # Google OAuth
POST   /api/auth/logout         # Logout (auth)
GET    /api/auth/user           # Usuario actual (auth)
PUT    /api/auth/user           # Actualizar datos (auth)
PUT    /api/auth/password       # Cambiar contraseña (auth)
```

### Perfiles (auth)
```
GET    /api/profile             # Mi perfil completo
PUT    /api/profile             # Actualizar perfil (incluye bio)
POST   /api/profile/photo       # Subir foto de perfil (multipart)
GET    /api/profiles/{id}       # Perfil público de otro usuario
GET    /api/me/products         # Mis productos
GET    /api/me/ranches          # Mis haciendas
GET    /api/me/metrics          # Mis métricas agregadas
GET    /api/profiles/{id}/ranches  # Haciendas públicas de un perfil
```

### Haciendas/Ranches (auth)
```
GET    /api/ranches             # Listar haciendas
POST   /api/ranches             # Crear hacienda
GET    /api/ranches/{id}        # Ver hacienda
PUT    /api/ranches/{id}        # Actualizar hacienda (owner)
DELETE /api/ranches/{id}        # Eliminar hacienda (owner, con validaciones)
```

### Productos/Marketplace
```
GET    /api/products            # Listar con filtros avanzados
POST   /api/products            # Crear producto (auth)
GET    /api/products/{id}       # Detalle (incrementa views si no es owner)
PUT    /api/products/{id}       # Actualizar (auth, owner)
DELETE /api/products/{id}       # Eliminar (auth, owner)
```

#### Filtros disponibles en GET /api/products:
- `type`: cattle, equipment, feed, other
- `breed`: raza específica
- `sex`: male, female, mixed
- `purpose`: breeding, meat, dairy, mixed
- `weight_min`, `weight_max`: rango de peso en kg
- `is_vaccinated`: boolean
- `delivery_method`: pickup, delivery, both
- `negotiable`: boolean
- `status`: active, paused, sold, expired
- `per_page`: paginación (default: 20)

### Favoritos y Reseñas
```
POST   /api/products/{id}/favorite    # Marcar favorito
DELETE /api/products/{id}/favorite    # Desmarcar favorito
GET    /api/products/{id}/reviews     # Reseñas del producto
GET    /api/ranches/{id}/reviews      # Reseñas de la hacienda
POST   /api/products/{id}/reviews     # Crear reseña (rating 1-5)
```

### Chat (auth)
```
GET    /api/chat/conversations         # Mis conversaciones
POST   /api/chat/conversations         # Crear conversación
GET    /api/chat/conversations/{id}/messages  # Historial de mensajes
POST   /api/chat/conversations/{id}/messages  # Enviar mensaje
POST   /api/chat/conversations/{id}/read      # Marcar como leído
DELETE /api/chat/conversations/{id}    # Eliminar conversación
```

### Reportes
```
POST   /api/reports              # Reportar producto/perfil/ranch
GET    /api/reports              # Mis reportes (usuario)
GET    /api/admin/reports        # Reportes pendientes (admin)
```

### Orders (Pedidos) - ✅ IMPLEMENTADO
```
GET    /api/orders                # Listar pedidos (filtros por rol/estado)
POST   /api/orders                # Crear pedido desde chat
GET    /api/orders/{id}           # Detalle de pedido
PUT    /api/orders/{id}/accept    # Aceptar pedido (vendedor)
PUT    /api/orders/{id}/reject    # Rechazar pedido (vendedor)
PUT    /api/orders/{id}/deliver   # Marcar como entregado (comprador)
PUT    /api/orders/{id}/cancel    # Cancelar pedido
GET    /api/orders/{id}/receipt   # Obtener comprobante de venta
POST   /api/orders/{id}/review    # Calificaciones mutuas
```

**Estados de pedido:**
- `pending`: Pendiente de aceptación del vendedor
- `accepted`: Aceptado por el vendedor (genera comprobante)
- `rejected`: Rechazado por el vendedor
- `delivered`: Marcado como entregado por el comprador
- `completed`: Completado (ambas partes calificaron)
- `cancelled`: Cancelado

**Métodos de delivery:**
1. `buyer_transport`: Comprador lleva su transporte
2. `seller_transport`: Vendedor entrega
3. `external_delivery`: Servicio de terceros
4. `corralx_delivery`: Logística interna de CorralX

**Flujo completo:**
1. Comprador crea pedido desde chat (`POST /api/orders`)
2. Vendedor acepta/rechaza (`PUT /api/orders/{id}/accept|reject`)
3. Al aceptar: se genera automáticamente comprobante de venta (`receipt_number` y `receipt_data`)
4. Comprador confirma recogida (`PUT /api/orders/{id}/deliver`)
5. Ambas partes califican (`POST /api/orders/{id}/review`)
6. Pedido pasa a `completed` y se actualizan ratings

**Nota importante:** La app coordina la operación pero NO procesa pagos. El intercambio económico ocurre cuando comprador y vendedor se encuentran físicamente usando el comprobante como contrato operativo.

### Publicidad/Anuncios (admin) - ✅ IMPLEMENTADO
```
GET    /api/advertisements              # Listar anuncios (admin)
GET    /api/advertisements/active       # Obtener anuncios activos (público)
POST   /api/advertisements              # Crear anuncio (admin)
GET    /api/advertisements/{id}         # Ver detalle (admin)
PUT    /api/advertisements/{id}         # Actualizar (admin)
DELETE /api/advertisements/{id}         # Eliminar (admin)
POST   /api/advertisements/{id}/click   # Registrar click (público)
```

**Tipos de publicidad:**
- `sponsored_product`: Producto patrocinado (requiere `product_id`)
- `external_ad`: Publicidad externa de terceros (requiere `advertiser_name`)

**Campos requeridos:**
- `type`: sponsored_product | external_ad
- `title`: Título del anuncio
- `image_url`: URL de la imagen (requerido)
- `target_url`: URL destino (opcional, para publicidad externa redirige al hacer click)
- `start_date`: Fecha de inicio
- `end_date`: Fecha de fin (opcional, si pasa se desactiva automáticamente)
- `is_active`: Estado activo/inactivo
- `priority`: Entero 0-100 (determina orden de aparición)
- `product_id`: Solo si type = 'sponsored_product'
- `advertiser_name`: Solo si type = 'external_ad'

**Modelo Unificado con Prioridad (similar a Instagram):**
- **Alta prioridad** (`priority > 50`): Aparecen primero, intercalados con productos normales
  - Intercalación: 2-3 anuncios de alta prioridad, luego 1-2 productos normales
  - Rotación aleatoria dentro del grupo (variación ±20% en prioridad)
- **Baja prioridad** (`priority <= 50`): Mezclados equitativamente con productos normales
  - Shuffle aleatorio completo entre productos y anuncios de baja prioridad
- **Productos patrocinados**: Pueden aparecer duplicados (como producto normal y como anuncio patrocinado)
- **Publicidad externa**: Al hacer click, redirige a `target_url` en navegador externo
- **Rotación**: Cada refresh del marketplace genera un orden diferente
- El endpoint `/api/products` NO se modifica (se mantiene intacto)
- Frontend hace 2 llamadas separadas (`/api/products` y `/api/advertisements/active`) y mezcla los resultados

### IA Insights (en progreso)

- `GET  /api/ia-insights/dashboard`  
  Calcula métricas reales por rol (free, premium, admin) y, si hay clave de Gemini configurada, genera titulares y resúmenes con IA.
- `POST /api/ia-insights/recommendations/{key}/status`  
  Guarda el estado de las recomendaciones para cada usuario.
- `POST /api/ia-insights/users/{user}/level`  
  Permite a un administrador promover o degradar niveles (free, premium, admin).  
  - `level=free`: mantiene `users.role = users` y desactiva `profile.is_premium_seller`.
  - `level=premium`: mantiene `users.role = users` y activa `profile.is_premium_seller`.
  - `level=admin`: actualiza `users.role = admin` y limpia cualquier flag premium previo.

**Segmentación (servidor → frontend)**

| Nivel   | Detonante                                      | Resumen entregado |
|---------|------------------------------------------------|-------------------|
| Free    | `users.role = users` **y** `profiles.is_premium_seller = 0` | Métricas básicas (vistas, favoritos, chats) y recomendaciones introductorias. |
| Premium | `profiles.is_premium_seller = 1` (sin importar `users.role`) | Comparativas contra el marketplace, proyecciones y recomendaciones priorizadas. |
| Admin   | `users.role = admin`                           | Métricas globales, monitoreo operativo y reportes. |

#### Configuración por entorno (.env)
```
GOOGLE_GEN_AI_KEY=replace-me
GOOGLE_GEN_AI_BASE_URL=https://generativelanguage.googleapis.com/v1beta
GOOGLE_GEN_AI_MODEL=models/gemini-2.0-flash
```

- Usa claves diferentes por ambiente si lo necesitas (ej. `GOOGLE_GEN_AI_KEY_STAGING`) y ajusta `config/services.php` para resolverlas según `APP_ENV`.
- Si la clave queda vacía o con `replace-me`, el servicio se ejecuta sin llamar a Gemini (solo heurísticas locales).

#### Verificación rápida
```
php artisan tinker --execute="
  auth()->login(\\App\\Models\\User::find(3349));
  echo json_encode(app(\\App\\Services\\Insights\\IAInsightsService::class)->generateDashboard(auth()->user(),'7d'));
"
```

---

## 🗄️ Modelo de Datos

### Arquitectura Principal
```
users (autenticación)
  ↓ 1:1
profiles (datos personales + marketplace)
  ↓ 1:N
ranches (haciendas/negocios)
  ↓ 1:N
products (ganado/equipos/alimentos)
```

### Tablas Core

#### users
```sql
id, name, email, password, google_id, role, completed_onboarding, 
created_at, updated_at, deleted_at
```

#### profiles
```sql
id, user_id→users, first_name, middle_name, last_name, second_last_name,
bio(500), photo_users, date_of_birth, marital_status, sex, ci_number,
user_type, is_verified, rating, ratings_count,
accepts_calls, accepts_whatsapp, accepts_emails, whatsapp_number,
created_at, updated_at
```

#### ranches
```sql
id, profile_id→profiles, name, legal_name, tax_id,
business_description(1000), specialization, certifications(json),
contact_hours, delivery_policy, return_policy,
address_id→addresses, is_primary, accepts_orders, min_order_amount,
max_delivery_distance_km, avg_rating, total_sales, last_sale_at,
created_at, updated_at, deleted_at
```

#### products
```sql
id, ranch_id→ranches, title, description, type, breed, age_months,
quantity, price, currency, weight_avg_kg, weight_min_kg, weight_max_kg,
sex, purpose, health_certificate_url, vaccines_applied(json),
documentation_included, genetic_test_results, is_vaccinated,
delivery_method, delivery_cost, delivery_radius_km, negotiable,
status, views_count, created_at, updated_at
```

#### advertisements - 📋 PLANIFICADO
```sql
id, type (enum: 'sponsored_product', 'external_ad'),
title, description (nullable), image_url, target_url (nullable),
is_active (boolean), start_date (datetime), end_date (datetime, nullable),
priority (int), clicks (int), impressions (int),
product_id→products|null, advertiser_name (string|null),
created_by→users (admin),
created_at, updated_at
```

#### addresses
```sql
id, profile_id→profiles|null, ranch_id→ranches|null,
street, house_number, postal_code, latitude, longitude,
city_id→cities, status, created_at, updated_at
```

### Tablas Adicionales
- `product_images`: Imágenes/videos de productos (max 10 por producto)
- `favorites`: Favoritos de usuarios
- `reviews`: Reseñas y calificaciones
- `conversations`: Chats 1:1
- `messages`: Mensajes de chat
- `reports`: Sistema de reportes polimórfico
- `phones`: Teléfonos con códigos de operadora
- `categories`: Categorías de productos
- `countries`, `states`, `cities`, `parishes`: Sistema de ubicaciones

---

## ✅ Testing - Estado Actual

### Tests Backend: 155/155 (100% PASANDO)

```bash
# Ejecutar todos los tests
php artisan test

# Tests específicos
php artisan test --filter=ProfileApiTest  # 17 tests
php artisan test --filter=RanchApiTest    # 10 tests
```

#### ProfileApiTest (17 tests - 48 aserciones)
- ✅ GET /api/profile (auth, 401, 404)
- ✅ PUT /api/profile (update, bio validation)
- ✅ POST /api/profile/photo (upload, validation, 401)
- ✅ GET /api/profiles/{id} (público, 404)
- ✅ GET /api/me/products (filtrado, vacío)
- ✅ GET /api/me/ranches (orden por primary)
- ✅ GET /api/me/metrics (cálculos, zeros)
- ✅ GET /api/profiles/{id}/ranches (público, vacío)

#### RanchApiTest (10 tests - 23 aserciones)
- ✅ PUT /api/ranches/{id} (update, ownership, primary)
- ✅ DELETE /api/ranches/{id} (delete, validaciones)
- ✅ Validación: no eliminar con productos activos
- ✅ Validación: no eliminar única hacienda
- ✅ Auto-promoción de primary al eliminar

---

## 🔒 Validaciones y Reglas de Negocio

### Productos
- `type` ∈ {cattle, equipment, feed, other}
- `breed` requerido (≤100) para cattle
- `age_months` ≥0 ≤360
- `quantity` ≥1 ≤10000
- `price` ≥0, `currency` ∈ {USD, VES}
- `weight_*` ≥0 ≤2000 kg
- `sex` ∈ {male, female, mixed}
- `purpose` ∈ {breeding, meat, dairy, mixed}
- `delivery_method` ∈ {pickup, delivery, both}
- Imágenes: ≤10MB, máximo 10 por producto

### Perfiles
- `first_name`, `last_name` requeridos (≤100)
- `bio` ≤500 caracteres
- `user_type` ∈ {buyer, seller, both}
- `photo_users`: jpeg/png/jpg, ≤5MB

### Haciendas
- Validación de ownership estricta
- No eliminar hacienda con productos activos
- No eliminar la única hacienda del perfil
- Auto-reasignación de `is_primary` al eliminar
- Soft delete con recuperación posible

### Chat
- `content` requerido ≤2000 caracteres
- Acceso restringido a participantes
- Rate-limit: throttle:30,1

---

## 🔐 Seguridad y Políticas

### Middleware
- `auth:sanctum`: Todas las rutas protegidas
- `throttle:60,1`: Rate limiting global
- `throttle:30,1`: Chat (prevención de spam)

### Políticas de Autorización
- **ProductPolicy:** Solo owner del ranch o admin pueden editar/eliminar
- **RanchPolicy:** Solo owner del perfil o admin pueden editar/eliminar
- **ConversationPolicy:** Solo participantes pueden ver mensajes
- **ReviewPolicy:** Una reseña por producto por perfil

---

## 📊 Sistema de Ubicaciones Geográficas

### Datos Completos
- **Países:** 683 con códigos ISO y prefijos telefónicos
- **Estados:** 4,528 organizados por país
- **Ciudades:** 47,123 organizadas por estado
- **Parroquias:** 436 (432 de Venezuela)

### Venezuela Específicamente
- 24 estados (IDs: 4020-4043)
- 117 ciudades
- 432 parroquias organizadas por municipio
- 30 códigos de operadoras telefónicas

### Seeders
```bash
php artisan db:seed --class=CountriesSeeder
php artisan db:seed --class=StatesSeeder
php artisan db:seed --class=CitiesSeeder
php artisan db:seed --class=ParishesSeeder
```

---

## 🚀 Comandos Útiles

### Desarrollo
```bash
# Migrar y poblar base de datos
php artisan migrate:fresh --seed --force

# Limpiar cachés
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Generar IDE helpers
php artisan ide-helper:generate
php artisan ide-helper:models

# Tests
php artisan test
php artisan test --filter=ProfileApiTest
php artisan test --coverage
```

### Producción
```bash
composer install --no-dev --optimize-autoloader
php artisan key:generate
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 📝 Configuración .env

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=http://192.168.27.12:8000
APP_URL_LOCAL=http://192.168.27.12:8000
APP_URL_PRODUCTION=https://corralx.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=corralx
DB_USERNAME=usuario
DB_PASSWORD=clave

FILESYSTEM_DISK=public

SANCTUM_STATEFUL_DOMAINS=localhost:3000,192.168.27.3
SESSION_DOMAIN=.corralx.com
```

### Firebase / FCM (Notificaciones Push)

- **Proyecto Firebase recomendado**: `corralx-777-aipp` (mismo que el frontend) para evitar errores de *SenderId mismatch*.
- **Variables clave en `.env`**:
  - `FIREBASE_CREDENTIALS=storage/app/<archivo-service-account>.json`
  - `FIREBASE_DATABASE_URL=https://corralx-777-aipp-default-rtdb.firebaseio.com`
  - `FIREBASE_STORAGE_BUCKET=corralx-777-aipp.firebasestorage.app`
- **Buenas prácticas**:
  - Mantener el archivo de credenciales fuera de git (solo en `storage/app/` del servidor).
  - Limpiar cachés después de cambiar credenciales o variables (`php artisan config:clear && php artisan cache:clear`).
  - Verificar la configuración con un comando tipo:
    ```bash
    php artisan tinker --execute="echo config('services.firebase.credentials');"
    ```
  - Asegurarse de que frontend y backend usen el **mismo proyecto Firebase** antes de probar notificaciones push.

---

## 🎯 Métricas de Calidad

### Tests
```
✅ ProfileApiTest: 17/17 tests (48 aserciones)
✅ RanchApiTest:   10/10 tests (23 aserciones)
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
   TOTAL:          27/27 tests (100% PASANDO)
   Duración:       ~2.8s
   Estado:         Production-Ready ✅
```

### Cobertura de Funcionalidades
- ✅ Autenticación: 100%
- ✅ Perfiles: 100%
- ✅ Haciendas: 100% (CRUD completo)
- ✅ Productos: 100%
- ✅ Chat: 90% (falta WebSocket)
- ✅ Favoritos/Reseñas: 100%

---

## 🏗️ Arquitectura

### Estructura de Carpetas
```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Authenticator/AuthController.php
│   │   ├── Profiles/ProfileController.php
│   │   ├── Profiles/RanchController.php
│   │   ├── Marketplace/ProductController.php
│   │   └── ChatController.php
│   ├── Middleware/
│   └── Requests/
├── Models/
│   ├── User.php
│   ├── Profile.php
│   ├── Ranch.php
│   ├── Product.php
│   └── [15+ modelos más]
└── Policies/
    ├── ProductPolicy.php
    └── RanchPolicy.php

database/
├── migrations/      # 28 migraciones
├── factories/       # 18 factories con datos reales
└── seeders/         # 12 seeders

tests/
├── Feature/
│   ├── ProfileApiTest.php    # ✅ 17 tests
│   └── RanchApiTest.php      # ✅ 10 tests
└── Unit/
```

---

## 🔥 Features Destacados

### 1. Sistema de Haciendas (Ranches)
- CRUD completo con validaciones estrictas
- Auto-gestión de hacienda principal (`is_primary`)
- Validación: no eliminar si tiene productos activos
- Validación: no eliminar la única hacienda
- Soft delete con recuperación
- Tests completos (10/10)

### 2. Sistema de Perfiles
- Bio personalizada (≤500 caracteres)
- Subida de fotos con endpoint dedicado
- Métricas agregadas (vistas, favoritos, ventas)
- Perfil público vs. privado
- Tests completos (17/17)

### 3. Módulo KYC con Evaluación Automática por IA (Gemini) - ✅ IMPLEMENTADO

**Estado:** ✅ KYC 100% automático con integración de Gemini IA

**Endpoints:**
```
GET    /api/kyc/status                    # Estado actual de KYC
POST   /api/kyc/start                     # Iniciar/reiniciar flujo KYC
POST   /api/kyc/upload-document           # Subir CI (front) y RIF
POST   /api/kyc/upload-selfie             # Subir selfie
POST   /api/kyc/upload-selfie-with-doc    # Subir selfie con documento
```

**Flujo de evaluación automática:**
1. Usuario sube CI, RIF, selfie y selfie con documento
2. `KycEvaluationService` valida localmente (formato CI, RIF, imágenes)
3. Si pasa validación local, llama a **Gemini IA** para evaluación inteligente:
   - Construye paquete KYC con datos del perfil, hacienda y direcciones
   - Envía prompt estructurado a Gemini
   - Gemini evalúa consistencia de nombres, documentos, persona-negocio
   - Devuelve decisión: `verified`, `rejected` o `pending` con razones
4. Si Gemini no está disponible, usa decisión local (fallback automático)
5. Actualiza `kyc_status` y envía notificación push al usuario

**Configuración de Gemini para KYC:**

Agregar en `.env`:
```env
GOOGLE_GEN_AI_KEY=tu_api_key_de_gemini_aqui
GOOGLE_GEN_AI_BASE_URL=https://generativelanguage.googleapis.com/v1beta
GOOGLE_GEN_AI_MODEL=models/gemini-2.0-flash
```

**Comportamiento:**
- **Con API key de Gemini:** Usa IA para evaluación inteligente de consistencia de datos
- **Sin API key:** Usa validación local (formato CI, RIF, imágenes) - funciona sin IA
- **Si Gemini falla:** Fallback automático a validación local

**Notificaciones push:**
- Cuando `kyc_status` cambia a `verified`: Notificación de éxito
- Cuando `kyc_status` cambia a `rejected`: Notificación con motivo de rechazo
- Requiere `fcm_device_token` en el perfil del usuario

**Validación KYC obligatoria:**
- `POST /api/products` requiere `kyc_status = 'verified'`
- Si no está verificado, responde `422` con `error: "kyc_incomplete"` y `kyc_status` actual

**Tests:**
- `KycApiTest`: 3 tests pasando (flujo completo)
- `KycEvaluationServiceTest`: 5 tests pasando (mocks de Gemini)
- `ProductApiTest`: Incluye test de validación KYC para productos

### 3. Módulo KYC Básico (documentos y selfies) - DEPRECATED
- Captura y almacenamiento de:
  - Documento de identidad (CI) frontal (y dorso opcional).
  - Selfie.
  - Selfie sosteniendo el documento.
- Estado de verificación centralizado en `profiles` (`kyc_status`, `kyc_rejection_reason`, paths de imágenes).
- Integrado al flujo de onboarding y expuesto al frontend para bloquear acciones sensibles (ej. publicar) cuando no está verificado.

### 4. Sistema de Productos
- Filtros avanzados (tipo, raza, sexo, peso, vacunación)
- Múltiples imágenes/videos por producto
- Sistema de favoritos
- Reseñas y calificaciones
- Contador de vistas automático

### 5. Sistema de Ubicaciones
- 47,123 ciudades de todo el mundo
- 432 parroquias de Venezuela
- Integración con perfiles y haciendas
- Coordenadas GPS opcionales

### 6. Sistema de Publicidad en Marketplace - 📋 PLANIFICADO
- Productos patrocinados (sponsored_product)
- Publicidad externa de terceros (external_ad)
- Rotación aleatoria de anuncios
- Desactivación automática por fecha de expiración
- Tracking de clicks e impressions
- Gestión exclusiva por admin
- El endpoint `/api/products` se mantiene intacto (sin modificaciones)

---

## 🐛 Bugs Resueltos

### Bug Crítico: Foto de Perfil
**Problema:** Laravel no procesa archivos multipart con PUT  
**Solución:** Endpoint dedicado `POST /api/profile/photo`  
**Estado:** ✅ Resuelto y testeado

### Bug: URLs de Imágenes Incorrectas
**Problema:** URLs guardadas con IP incorrecta (.11 vs .12)  
**Solución:** Actualización masiva en BD + configuración de .env  
**Estado:** ✅ Resuelto

---

## 📖 Convenciones de Código

### Commits Semánticos
```
feat:     nueva funcionalidad
fix:      corrección de bug
test:     agregar o modificar tests
chore:    tareas de mantenimiento
docs:     cambios en documentación
refactor: cambio interno sin afectar comportamiento
```

### Estándares
- PSR-12 para estilo de código PHP
- Eloquent para ORM (evitar queries raw)
- Form Requests para validación
- API Resources para respuestas JSON
- Políticas para autorización

---

## 🔀 Flujo de Trabajo con Git

### Estrategia de Ramas

**IMPORTANTE:** Este proyecto utiliza un flujo de trabajo con dos ramas principales:

1. **`dev`** - Rama de pruebas/testing
   - Despliegue automático a: `test.corralx.com`
   - Ambiente: `APP_DEBUG=true`
   - Todos los cambios deben probarse aquí primero

2. **`main`** - Rama de producción
   - Despliegue automático a: `corralx.com`
   - Ambiente: `APP_DEBUG=false`
   - Solo se actualiza cuando los cambios están 100% verificados

### Permisos y Roles

#### 👑 ADMIN (Solo el administrador principal)
- ✅ Puede hacer **push directamente a `dev`**
- ✅ Puede hacer **push directamente a `main`** (solo él)
- ✅ Puede hacer **merge de `dev` → `main`** (solo él, cuando apruebe los cambios)

#### 👨‍💻 PROGRAMADOR (No admin)
- ✅ Puede hacer **push a `dev`** solamente
- ❌ **NO puede hacer push directo a `main`**
- ❌ **NO puede hacer merge de `dev` → `main`** (solo el admin puede)

### Proceso de Trabajo

#### Para ADMIN:

**OPCIÓN 1: Flujo Normal (Recomendado)**
```bash
# 1. Trabajar en la rama dev
git checkout dev
git pull origin dev

# 2. Hacer cambios y commits
git add .
git commit -m "feat: descripción del cambio"

# 3. Push a dev (pruebas)
git push origin dev
# ✅ Se despliega automáticamente a test.corralx.com

# 4. Verificar en test.corralx.com
# - Probar todos los cambios
# - Verificar que no hay errores
# - Ejecutar tests: php artisan test

# 5. Si todo está bien, merge a main
git checkout main
git pull origin main
git merge dev
git push origin main
# ✅ Se despliega automáticamente a corralx.com
```

**OPCIÓN 2: Push Directo a Main (Solo Admin)**
```bash
# Si estás 100% seguro y quieres saltar pruebas
git checkout main
git pull origin main
git add .
git commit -m "feat: cambio directo a producción"
git push origin main
# ✅ Se despliega automáticamente a corralx.com
```

#### Para PROGRAMADOR:

**Flujo Único (Solo dev)**
```bash
# 1. Trabajar en la rama dev
git checkout dev
git pull origin dev

# 2. Hacer cambios y commits
git add .
git commit -m "feat: descripción del cambio"

# 3. Push a dev (pruebas)
git push origin dev
# ✅ Se despliega automáticamente a test.corralx.com

# 4. Esperar aprobación del admin
# El admin revisará en test.corralx.com y hará el merge a main
```

### Reglas Importantes

⚠️ **Para PROGRAMADORES:**
- ❌ **NUNCA intentar push a `main`** (será rechazado por GitHub)
- ❌ **NUNCA intentar merge a `main`** (solo el admin puede)
- ✅ **Siempre trabajar en `dev`** y esperar aprobación del admin

✅ **Flujo correcto para PROGRAMADOR:**
1. Cambios → `dev` → Push → Probar en `test.corralx.com`
2. Notificar al admin para revisión
3. Admin verifica y hace merge a `main` si aprueba

✅ **Flujo correcto para ADMIN:**
1. Cambios → `dev` → Push → Probar en `test.corralx.com`
2. Si todo está bien → Merge `dev` → `main` → Push → Producción
3. O push directo a `main` si estás 100% seguro

### Configuración de GitHub (Branch Protection)

Para aplicar estas restricciones automáticamente:

1. **Rama `main`:**
   - Activar "Require pull request reviews before merging"
   - Activar "Restrict who can push to matching branches" (solo admin)
   - Activar "Require status checks to pass before merging"

2. **Rama `dev`:**
   - Permitir push a todos los colaboradores
   - No requiere pull request (push directo permitido)

### Control de Acceso Resumido

| Acción | Admin | Programador |
|--------|-------|-------------|
| Push a `dev` | ✅ Sí | ✅ Sí |
| Push a `main` | ✅ Sí | ❌ No |
| Merge `dev` → `main` | ✅ Sí | ❌ No |

---

## 🚢 Despliegue

### Hosting Compartido
1. Subir archivos excepto `vendor/`
2. Ejecutar `composer install --no-dev`
3. Configurar `.env` con datos de producción
4. Ejecutar migraciones: `php artisan migrate --force`
5. Enlazar storage: `php artisan storage:link`
6. Cachear configuración: `php artisan config:cache`
7. Permisos: `storage/` y `bootstrap/cache/` escribibles

### Servidor Dedicado
```bash
# Nginx o Apache configurado para public/
# PHP-FPM corriendo
# MySQL configurado
# Supervisor para queues (opcional)
# Laravel Echo Server para WebSockets (opcional)
```

---

## 🎯 KPIs de Éxito (MVP)

- Usuarios registrados activos/mes
- Publicaciones activas
- Contactos iniciados (chats)
- Tasa de respuesta en chats
- % publicaciones con al menos 1 conversación
- Retención a 30 días

---

## 🔮 Roadmap (Post-MVP)

### Corto Plazo
- WebSockets para chat en tiempo real
- Panel de administración completo
- ✅ **KYC 100% automático con Gemini IA** (IMPLEMENTADO)
- ✅ **Notificaciones push para cambios de estado KYC** (IMPLEMENTADO)

### Mediano Plazo
- Pagos integrados (escrow)
- Comisiones por transacción
- Suscripción Premium
- Analítica de mercado (precios, tendencias)
- Mejoras en precisión de OCR (CI/RIF)

### Largo Plazo
- App móvil nativa
- Integración con sistemas de trazabilidad
- Marketplace de equipos y alimentos
- Expansión internacional

---

## 📞 Soporte

**Documentación completa:** Ver `.cursorrules` para reglas de desarrollo  
**Tests:** 27 tests automatizados garantizan calidad  
**Estado:** ✅ Production-Ready (MVP 100%)

---

**Última actualización:** 8 de octubre de 2025  
**Versión:** 1.0.0 (MVP)  
**Mantenedor:** Equipo CorralX
