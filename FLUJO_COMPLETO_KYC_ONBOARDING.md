# 🔄 FLUJO COMPLETO: ONBOARDING KYC CON IA GEMINI

## 📋 ÍNDICE
1. [Estructura General del Onboarding](#estructura-general)
2. [Flujo Paso a Paso del KYC](#flujo-paso-a-paso)
3. [Proceso de Liveness Detection](#liveness-detection)
4. [Captura y Almacenamiento de Imágenes](#captura-imagenes)
5. [Subida al Backend](#subida-backend)
6. [Evaluación Automática con Gemini AI](#evaluacion-gemini)
7. [Cierre del Ciclo](#cierre-ciclo)

---

## 🏗️ ESTRUCTURA GENERAL DEL ONBOARDING {#estructura-general}

El onboarding consta de **8 páginas** en este orden:

```
Página 0: WelcomePage (Bienvenida)
Página 1: KycOnboardingIntroPage (Introducción al KYC)
Página 2: KycOnboardingSelfiePage (Selfie con Liveness Detection) ⭐
Página 3: KycOnboardingDocumentPage (CI + RIF con OCR) ⭐
Página 4: KycOnboardingSelfieWithDocPage (Selfie sosteniendo CI) ⭐
Página 5: OnboardingPage1 (Datos Personales - pre-llenado con OCR)
Página 6: OnboardingPage2 (Datos de Hacienda - pre-llenado con OCR)
Página 7: OnboardingPage3 (Página final de confirmación)
```

**Nota:** Las páginas 2, 3 y 4 son las que capturan las imágenes KYC.

---

## 🔄 FLUJO PASO A PASO DEL KYC {#flujo-paso-a-paso}

### **FASE 1: INTRODUCCIÓN (Página 1)**

**Archivo:** `kyc_onboarding_intro_page.dart`

1. Usuario ve pantalla de introducción al KYC
2. Se explica qué documentos necesita (CI, RIF)
3. Usuario presiona "Continuar"
4. **No se guarda nada en esta etapa**

---

### **FASE 2: CAPTURA DE SELFIE CON LIVENESS DETECTION (Página 2)** ⭐

**Archivo:** `kyc_onboarding_selfie_page.dart`

#### **2.1. Inicialización de la Cámara**
```
1. Se inicializa la cámara frontal del dispositivo
2. Se crea un CameraController con ResolutionPreset.medium
3. Se inicializa el LivenessDetectionService
4. Se muestra la vista previa de la cámara
```

#### **2.2. Secuencia de Liveness Detection**

El sistema requiere **5 poses diferentes** en este orden:

```dart
List<HeadPose> _livenessSequence = [
  HeadPose.front,    // 1. Mirar al frente
  HeadPose.up,       // 2. Mirar hacia arriba
  HeadPose.down,     // 3. Mirar hacia abajo
  HeadPose.left,      // 4. Mirar hacia la izquierda
  HeadPose.right,     // 5. Mirar hacia la derecha
];
```

#### **2.3. Proceso de Detección de Poses**

Para cada pose:

1. **Stream de Imágenes:**
   - La cámara captura frames cada ~800ms (throttling)
   - Cada frame se analiza con Google ML Kit Face Detection
   - Se extraen los ángulos de Euler (eulerY, eulerZ) del rostro

2. **Validación de Pose:**
   - Se compara la pose actual con la pose requerida
   - Se calcula un "progreso" (0.0 a 1.0) basado en qué tan cerca está el usuario de la pose correcta
   - Se muestra una barra de progreso visual al usuario

3. **Contador de Tiempo:**
   - Una vez que el usuario alcanza la pose correcta (progreso > 80%), se inicia un contador
   - El usuario debe **mantener la pose por 1.5-2 segundos** (tiempo adaptativo)
   - Durante este tiempo, se muestra un countdown visual

4. **Captura de Selfie de Liveness:**
   - Cuando el contador llega a 0, se captura una foto automáticamente
   - La foto se guarda en `_livenessSelfies` (lista)
   - Se guarda la ruta en `FlutterSecureStorage` con la clave:
     ```
     kyc_liveness_1_path
     kyc_liveness_2_path
     kyc_liveness_3_path
     kyc_liveness_4_path
     kyc_liveness_5_path
     ```

5. **Avance al Siguiente Paso:**
   - Se marca el paso como completado
   - Se avanza al siguiente paso de la secuencia
   - Se espera 500ms antes de mostrar la siguiente instrucción

#### **2.4. Captura de Selfie Final**

Después de completar las 5 poses del liveness:

1. **Espera de Capturas Anteriores:**
   - Se espera a que termine `_captureLivenessSelfie()` completamente
   - Se espera 1000ms adicionales para asegurar que todas las capturas terminaron

2. **Detención del Stream:**
   - Se detiene el stream de imágenes de la cámara
   - Se espera 1000ms para que la cámara se estabilice

3. **Captura de Selfie Final:**
   - Se captura la selfie final con `_cameraController.takePicture()`
   - Se guarda en `_capturedSelfie`
   - Se guarda la ruta en `FlutterSecureStorage` con la clave:
     ```
     kyc_selfie_path
     ```

4. **Verificación:**
   - Se verifica que la imagen se guardó correctamente en storage
   - Se muestra un mensaje de confirmación

**Resultado de esta fase:**
- ✅ 5 selfies de liveness guardadas localmente
- ✅ 1 selfie final guardada localmente
- ✅ Todas las rutas guardadas en `FlutterSecureStorage`

---

### **FASE 3: CAPTURA DE DOCUMENTOS CI Y RIF (Página 3)** ⭐

**Archivo:** `kyc_onboarding_document_page.dart`

#### **3.1. Captura de CI (Cédula de Identidad)**

1. Usuario presiona botón "Capturar CI"
2. Se abre la cámara (puede ser galería o cámara)
3. Usuario captura/toma foto de su CI frontal
4. La imagen se guarda en `FlutterSecureStorage` con la clave:
   ```
   kyc_ci_path
   ```

#### **3.2. Procesamiento OCR del CI**

1. Se llama a `OCRUtils.extractCIData(imagePath)`
2. Se usa Google ML Kit Text Recognition para extraer texto
3. Se busca el número de CI con regex: `V-\d{7,8}`
4. Se extraen datos como:
   - Número de CI
   - Nombre completo
   - Fecha de nacimiento (si es legible)
5. Los datos se guardan temporalmente para pre-llenar formularios

#### **3.3. Captura de RIF (Registro de Información Fiscal)**

1. Usuario presiona botón "Capturar RIF"
2. Se abre la cámara/galería
3. Usuario captura/toma foto de su RIF
4. La imagen se guarda en `FlutterSecureStorage` con la clave:
   ```
   kyc_rif_path
   ```

#### **3.4. Procesamiento OCR del RIF**

1. Se llama a `OCRUtils.extractRIFData(imagePath)`
2. Se usa Google ML Kit Text Recognition
3. Se busca el número de RIF con regex: `(V|J)-\d{8}-\d`
4. Se extraen datos como:
   - Número de RIF
   - Razón social / Nombre del negocio
   - Dirección (si es legible)
5. Los datos se guardan temporalmente para pre-llenar formularios

**Resultado de esta fase:**
- ✅ CI capturada y guardada localmente
- ✅ RIF capturado y guardado localmente
- ✅ Datos extraídos por OCR guardados para pre-llenado

---

### **FASE 4: CAPTURA DE SELFIE CON DOCUMENTO (Página 4)** ⭐

**Archivo:** `kyc_onboarding_selfie_with_doc_page.dart`

1. Usuario ve instrucciones: "Sostén tu CI frente a la cámara"
2. Se inicializa la cámara frontal
3. Usuario se toma una selfie sosteniendo su CI
4. La imagen se guarda en `FlutterSecureStorage` con la clave:
   ```
   kyc_selfie_with_doc_path
   ```

**Resultado de esta fase:**
- ✅ Selfie con CI capturada y guardada localmente

---

### **FASE 5: FORMULARIOS PRE-LLENADOS (Páginas 5 y 6)**

**Archivos:** `onboarding_page1.dart`, `onboarding_page2.dart`

1. Los formularios se pre-llenan automáticamente con los datos extraídos por OCR
2. Usuario puede corregir o completar información faltante
3. Los datos se guardan en `FlutterSecureStorage` como drafts

---

### **FASE 6: COMPLETAR ONBOARDING (Página 7)**

**Archivo:** `onboarding_screen.dart` - Método `_completeOnboarding()`

#### **6.1. Creación del Perfil en el Backend**

```dart
1. Se llama a _submitOnboardingData(userId)
2. Se crea el perfil con los datos personales:
   - firstName, lastName, dateOfBirth, ciNumber
3. Se crea la hacienda con los datos del RIF:
   - name, legal_name, tax_id, address
4. Se obtiene el profile_id del perfil creado
```

#### **6.2. Subida de Documentos KYC al Backend**

**Método:** `_uploadKycDocuments()`

Este método se ejecuta **DESPUÉS** de que el perfil se crea exitosamente.

**Orden de subida:**

1. **Subir Selfies del Liveness (5 selfies):**
   ```dart
   - Se leen las rutas desde FlutterSecureStorage:
     kyc_liveness_1_path
     kyc_liveness_2_path
     kyc_liveness_3_path
     kyc_liveness_4_path
     kyc_liveness_5_path
   - Se crean objetos XFile desde las rutas
   - Se llama a: KycService.uploadLivenessSelfies(selfies: [XFile...])
   - Endpoint: POST /api/kyc/upload-liveness-selfies
   - Se envían como: selfies[] (array de archivos)
   ```

2. **Subir Selfie Principal:**
   ```dart
   - Se lee la ruta desde: kyc_selfie_path
   - Se crea XFile desde la ruta
   - Se llama a: KycService.uploadSelfie(selfie: XFile)
   - Endpoint: POST /api/kyc/upload-selfie
   - Se envía como: selfie (archivo único)
   ```

3. **Subir CI y RIF:**
   ```dart
   - Se leen las rutas desde: kyc_ci_path, kyc_rif_path
   - Se crean XFile desde las rutas
   - Se llama a: KycService.uploadDocument(front: ciFile, rif: rifFile)
   - Endpoint: POST /api/kyc/upload-document
   - Se envían como: front (CI), rif (RIF)
   ```

4. **Subir Selfie con Documento:**
   ```dart
   - Se lee la ruta desde: kyc_selfie_with_doc_path
   - Se crea XFile desde la ruta
   - Se llama a: KycService.uploadSelfieWithDoc(selfieWithDoc: XFile)
   - Endpoint: POST /api/kyc/upload-selfie-with-doc
   - Se envía como: selfie_with_doc (archivo único)
   ```

**Después de cada subida exitosa:**
- Se elimina la ruta del `FlutterSecureStorage` (limpieza)
- Se muestra un log de confirmación

---

## 📤 SUBIDA AL BACKEND {#subida-backend}

### **Backend: KycController**

Cada endpoint del `KycController` sigue este flujo:

#### **1. Validación de Autenticación**
```php
$profile = $this->getAuthenticatedProfile($request);
if (!$profile) {
    return 404; // Perfil no encontrado
}
```

#### **2. Validación de Archivos**
```php
$validated = $request->validate([
    'selfie' => 'required|image|mimes:jpeg,png,jpg|max:5120', // 5MB máximo
    // ... otros campos
]);
```

#### **3. Almacenamiento de Archivos**

**Estructura de nombres:**
```
storage/app/public/kyc/{profile_id}/
├── user_{profile_id}_selfie.jpg
├── user_{profile_id}_ci_front.jpg
├── user_{profile_id}_rif.jpg
├── user_{profile_id}_selfie_with_doc.jpg
├── user_{profile_id}_liveness_1.jpg
├── user_{profile_id}_liveness_2.jpg
├── user_{profile_id}_liveness_3.jpg
├── user_{profile_id}_liveness_4.jpg
└── user_{profile_id}_liveness_5.jpg
```

**Ejemplo de código:**
```php
$disk = 'public';
$basePath = 'kyc/' . $profile->id;
$fileName = 'user_' . $profile->id . '_selfie.' . $extension;
$path = $file->storeAs($basePath, $fileName, $disk);
```

#### **4. Actualización del Perfil**

```php
$profile->kyc_selfie_path = $path;
$profile->kyc_status = 'pending'; // Si estaba en 'no_verified'
$profile->save();
```

#### **5. Evaluación Automática**

Después de guardar cada imagen, se llama a:

```php
$this->maybeAutoVerify($profile);
```

Que internamente ejecuta:

```php
$this->kycEvaluationService->evaluate($profile);
```

---

## 🤖 EVALUACIÓN AUTOMÁTICA CON GEMINI AI {#evaluacion-gemini}

### **Backend: KycEvaluationService**

#### **PASO 1: Validaciones Locales**

**Método:** `validateLocally(Profile $profile)`

Se validan:

1. **Formato de CI:**
   ```php
   // Debe ser: V-12345678 (7-8 dígitos)
   preg_match('/^V-\d{7,8}$/', $ci)
   ```

2. **Formato de RIF:**
   ```php
   // Debe ser: V-12345678-9 o J-12345678-9
   preg_match('/^(V|J)-\d{8}-\d$/', $rif)
   ```

3. **Existencia de Imágenes:**
   ```php
   - kyc_doc_front_path (CI)
   - kyc_rif_path (RIF)
   - kyc_selfie_path (Selfie principal)
   - kyc_selfie_with_doc_path (Selfie con CI)
   - kyc_liveness_selfies_paths (Array de 5 selfies)
   ```

4. **Edad Razonable:**
   ```php
   // Entre 18 y 100 años
   $age = $profile->date_of_birth->diffInYears(now());
   ```

**Si falla validación local:**
- `kyc_status = 'pending'`
- `kyc_rejection_reason = "Razón del rechazo"`
- **NO se llama a Gemini**
- Se guarda y termina

**Si pasa validación local:**
- Continúa al Paso 2

---

#### **PASO 2: Preparación de Datos para Gemini**

**Método:** `buildKycPackage(Profile $profile)`

Se construye un array con:

```php
[
    'profile' => [
        'first_name' => 'Abraham',
        'last_name' => 'Pulido',
        'ci_number' => 'V-12345678',
        'date_of_birth' => '1986-03-03',
        // ... más datos
    ],
    'ranch' => [
        'name' => 'Hacienda El Trigal',
        'tax_id' => 'V-19217553-0',
        // ... más datos
    ],
    'address' => [
        'street' => 'Calle Principal',
        'city' => 'Valencia',
        // ... más datos
    ],
    'images' => [
        'has_doc_front' => true,
        'has_rif' => true,
        'has_selfie' => true,
        'has_selfie_with_doc' => true,
    ],
]
```

---

#### **PASO 3: Conversión de Imágenes a Base64**

**Método:** `prepareImagesForGemini(Profile $profile)`

Se leen todas las imágenes del storage y se convierten a base64:

```php
1. Selfie principal:
   - Lee: storage/app/public/kyc/{profile_id}/user_{id}_selfie.jpg
   - Convierte a base64 con prefijo: data:image/jpeg;base64,{base64}

2. CI (Cédula):
   - Lee: storage/app/public/kyc/{profile_id}/user_{id}_ci_front.jpg
   - Convierte a base64

3. Selfie con CI:
   - Lee: storage/app/public/kyc/{profile_id}/user_{id}_selfie_with_doc.jpg
   - Convierte a base64

4. Selfies del Liveness (1-5):
   - Lee: storage/app/public/kyc/{profile_id}/user_{id}_liveness_{1-5}.jpg
   - Convierte cada una a base64
```

**Resultado:**
```php
[
    ['data' => 'data:image/jpeg;base64,...', 'mime_type' => 'image/jpeg', 'type' => 'selfie'],
    ['data' => 'data:image/jpeg;base64,...', 'mime_type' => 'image/jpeg', 'type' => 'ci'],
    ['data' => 'data:image/jpeg;base64,...', 'mime_type' => 'image/jpeg', 'type' => 'selfie_with_doc'],
    ['data' => 'data:image/jpeg;base64,...', 'mime_type' => 'image/jpeg', 'type' => 'liveness_1'],
    // ... hasta liveness_5
]
```

**Nota:** El RIF NO se envía a Gemini (solo se usa para validación local).

---

#### **PASO 4: Construcción del Prompt para Gemini**

**Método:** `buildKycPromptForGemini(array $kycPackage)`

El prompt incluye:

1. **Instrucciones del rol:**
   - "Eres un experto en verificación de identidad (KYC)"

2. **Datos del usuario (JSON):**
   - Datos personales, hacienda, dirección

3. **Descripción de imágenes:**
   - Qué representa cada imagen (CI, selfie, liveness, etc.)

4. **Criterios de evaluación detallados:**
   - Verificar que selfies son REALES (no fotos de fotos)
   - Verificar que selfie con CI tiene AMBOS elementos (rostro + documento)
   - Comparar rostros entre selfie y CI
   - Comparar rostros entre selfie principal y selfies del liveness
   - Comparar rostros entre selfie con CI y selfies anteriores
   - Verificar legibilidad del CI
   - Verificar consistencia de nombres
   - Verificar coherencia de documentos

5. **Formato de respuesta requerido:**
   ```json
   {
     "decision": "verified" | "rejected" | "pending",
     "reasons": ["razón 1", "razón 2"],
     "confidence": "high" | "medium" | "low",
     "face_analysis": {
       "selfie_has_face": true/false,
       "selfie_is_real": true/false,
       "selfie_with_doc_has_face": true/false,
       "selfie_with_doc_has_document": true/false,
       "selfie_with_doc_is_real": true/false,
       "liveness_selfies_count": 5,
       "liveness_selfies_are_real": true/false,
       "liveness_selfies_match_main": true/false,
       "selfie_with_doc_matches_main": true/false,
       "selfie_with_doc_matches_liveness": true/false,
       "ci_face_matches_selfie": true/false,
       "all_faces_match": true/false,
       "face_match_confidence": "high" | "medium" | "low"
     }
   }
   ```

---

#### **PASO 5: Llamada a Gemini API**

**Método:** `callGemini(string $prompt, array $images)`

**Configuración:**
```php
$apiKey = env('GOOGLE_GEN_AI_KEY');
$model = 'gemini-1.5-pro';
$url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";
```

**Request Body:**
```json
{
  "contents": [{
    "parts": [
      {
        "text": "{prompt_completo}"
      },
      {
        "inline_data": {
          "mime_type": "image/jpeg",
          "data": "{base64_sin_prefijo_data:}"
        }
      },
      // ... una parte por cada imagen (hasta 7 imágenes: selfie, CI, selfie_con_CI, 5 liveness)
    ]
  }],
  "generationConfig": {
    "temperature": 0.1,
    "maxOutputTokens": 2048
  }
}
```

**Timeout:** 60 segundos

**Response de Gemini:**
```json
{
  "candidates": [{
    "content": {
      "parts": [{
        "text": "{\"decision\":\"verified\",\"reasons\":[...],\"face_analysis\":{...}}"
      }]
    }
  }]
}
```

---

#### **PASO 6: Parseo de Respuesta de Gemini**

**Método:** `parseGeminiResponse(array $response)`

1. Se extrae el texto de la respuesta
2. Se busca el JSON en el texto (puede venir con markdown o texto extra)
3. Se decodifica el JSON
4. Se valida la estructura:
   - `decision` debe ser: "verified", "rejected", o "pending"
   - `reasons` debe ser un array
   - `confidence` debe ser: "high", "medium", o "low"
   - `face_analysis` debe contener los campos esperados

5. Se aplican reglas de negocio:
   - Si `face_analysis.selfie_has_face === false` → **rejected**
   - Si `face_analysis.selfie_is_real === false` → **rejected**
   - Si `face_analysis.selfie_with_doc_has_face === false` → **rejected**
   - Si `face_analysis.selfie_with_doc_has_document === false` → **rejected**
   - Si `face_analysis.selfie_with_doc_is_real === false` → **rejected**
   - Si `face_analysis.all_faces_match === false` → **rejected**
   - Si `face_analysis.liveness_selfies_are_real === false` → **rejected**
   - Si `face_analysis.liveness_selfies_match_main === false` → **rejected**

6. Se construye la decisión final:
   ```php
   [
       'status' => 'verified' | 'rejected' | 'pending',
       'reason' => 'Razón concatenada de reasons[]',
       'confidence' => 'high' | 'medium' | 'low',
   ]
   ```

---

#### **PASO 7: Actualización del Estado KYC**

**Método:** `evaluate(Profile $profile)` - Final

```php
if ($geminiDecision !== null) {
    // Usar decisión de Gemini
    $profile->kyc_status = $geminiDecision['status'];
    
    if ($geminiDecision['status'] === 'verified') {
        $profile->kyc_verified_at = now();
        $profile->kyc_rejection_reason = null;
    } else {
        $profile->kyc_rejection_reason = $geminiDecision['reason'];
    }
} else {
    // Fallback: si Gemini no responde, verificar automáticamente
    $profile->kyc_status = 'verified';
    $profile->kyc_verified_at = now();
}

$profile->save();
```

---

#### **PASO 8: Notificación Push (Opcional)**

**Método:** `sendKycStatusNotification(Profile $profile)`

Si el perfil tiene `fcm_device_token`:

1. **Si `kyc_status === 'verified'`:**
   - Título: "✅ Verificación KYC completada"
   - Mensaje: "Tu identidad ha sido verificada exitosamente. Ya puedes publicar productos."
   - Data: `{type: 'kyc_verified', kyc_status: 'verified'}`

2. **Si `kyc_status === 'rejected'`:**
   - Título: "❌ Verificación KYC rechazada"
   - Mensaje: "Tu verificación fue rechazada: {razón}. Puedes reintentar el proceso."
   - Data: `{type: 'kyc_rejected', kyc_status: 'rejected', rejection_reason: '...'}`

3. **Si `kyc_status === 'pending'`:**
   - No se envía notificación (es estado intermedio)

---

## 🔄 CUÁNDO SE EJECUTA LA EVALUACIÓN {#cuando-evaluacion}

La evaluación automática se ejecuta **después de cada subida de imagen**:

1. ✅ Después de subir selfies del liveness → `maybeAutoVerify()`
2. ✅ Después de subir selfie principal → `maybeAutoVerify()`
3. ✅ Después de subir CI y RIF → `maybeAutoVerify()`
4. ✅ Después de subir selfie con CI → `maybeAutoVerify()`

**Nota:** La evaluación solo se ejecuta si:
- El perfil tiene todas las imágenes requeridas
- El perfil NO está ya en estado `verified`

---

## 🎯 CIERRE DEL CICLO {#cierre-ciclo}

### **Frontend: Finalización del Onboarding**

Después de subir todos los documentos KYC:

1. Se marca `completed_onboarding = true` en el usuario
2. Se eliminan los datos guardados del `FlutterSecureStorage`
3. Se redirige al usuario a la pantalla principal de la app
4. El usuario puede ver su estado KYC en su perfil

### **Backend: Estado Final del KYC**

El perfil queda con uno de estos estados:

- **`no_verified`**: Usuario no ha iniciado KYC
- **`pending`**: KYC en proceso o esperando evaluación
- **`verified`**: ✅ KYC aprobado automáticamente por Gemini
- **`rejected`**: ❌ KYC rechazado (con `kyc_rejection_reason`)

### **Verificación del Estado**

El usuario puede consultar su estado KYC en cualquier momento:

```
GET /api/kyc/status
```

Respuesta:
```json
{
  "kyc_status": "verified" | "rejected" | "pending" | "no_verified",
  "kyc_rejection_reason": "Razón del rechazo (si aplica)",
  "has_document": true,
  "has_rif": true,
  "has_selfie": true,
  "has_selfie_with_doc": true
}
```

---

## 📊 RESUMEN DEL FLUJO COMPLETO

```
1. Usuario inicia onboarding
   ↓
2. Captura 5 selfies de liveness (front, up, down, left, right)
   ↓
3. Captura selfie final
   ↓
4. Captura CI y RIF (con OCR)
   ↓
5. Captura selfie sosteniendo CI
   ↓
6. Completa formularios (pre-llenados con OCR)
   ↓
7. Se crea perfil en backend
   ↓
8. Se suben TODAS las imágenes al backend (en orden):
   - 5 selfies de liveness
   - Selfie principal
   - CI + RIF
   - Selfie con CI
   ↓
9. Después de CADA subida, se ejecuta evaluación automática:
   a) Validaciones locales (formato CI, RIF, existencia de imágenes)
   b) Si pasa → Preparar datos para Gemini
   c) Convertir imágenes a base64
   d) Construir prompt detallado
   e) Llamar a Gemini API con imágenes
   f) Parsear respuesta de Gemini
   g) Aplicar reglas de negocio
   h) Actualizar kyc_status (verified/rejected/pending)
   i) Enviar notificación push (si aplica)
   ↓
10. Usuario ve resultado final (verified/rejected)
   ↓
11. Si verified → Puede publicar productos
    Si rejected → Puede reintentar el proceso
```

---

## 🔍 PUNTOS CRÍTICOS DEL FLUJO

1. **Almacenamiento Local:**
   - Todas las imágenes se guardan primero en `FlutterSecureStorage`
   - Solo se suben al backend DESPUÉS de crear el perfil

2. **Orden de Subida:**
   - Liveness selfies → Selfie principal → CI+RIF → Selfie con CI
   - Este orden asegura que la evaluación se ejecute con todas las imágenes

3. **Evaluación Automática:**
   - Se ejecuta después de CADA subida
   - Solo evalúa si tiene TODAS las imágenes requeridas
   - Si Gemini no responde, usa fallback (verificar automáticamente)

4. **Nombres de Archivos:**
   - Todos los archivos usan el formato: `user_{profile_id}_{tipo}.jpg`
   - Esto permite identificar fácilmente qué usuario subió qué imagen

5. **Timeout de Gemini:**
   - 60 segundos máximo
   - Si falla, se usa fallback (verificar automáticamente)

---

## ✅ CHECKLIST DE COMPLETITUD

Para que un KYC sea evaluado completamente, debe tener:

- [x] CI capturada (`kyc_doc_front_path`)
- [x] RIF capturado (`kyc_rif_path`)
- [x] Selfie principal (`kyc_selfie_path`)
- [x] Selfie con CI (`kyc_selfie_with_doc_path`)
- [x] 5 selfies de liveness (`kyc_liveness_selfies_paths` - array de 5 rutas)
- [x] CI con formato válido (V-12345678)
- [x] RIF con formato válido (V-12345678-9 o J-12345678-9)
- [x] Edad razonable (18-100 años)

---

**Fin del documento**

