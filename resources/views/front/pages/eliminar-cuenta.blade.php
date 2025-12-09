<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Eliminar cuenta — Corral X</title>
  <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('assets/front/images/Favicon/apple-touch-icon.png') }}">
  <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/front/images/Favicon/favicon-96x96.png') }}">
  <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('assets/front/images/Favicon/favicon-96x96.png') }}">
  <link rel="icon" href="{{ asset('assets/front/images/Favicon/favicon.ico') }}" type="image/x-icon">
  <link rel="mask-icon" href="{{ asset('assets/front/images/Favicon/favicon.svg') }}" color="#386A20">
  <link rel="manifest" href="{{ asset('assets/front/images/Favicon/site.webmanifest') }}">
  <meta name="theme-color" content="#386A20">
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
      line-height: 1.6;
      color: #333;
      background: #fff;
      padding: 80px 20px 40px;
    }
    .container {
      max-width: 1000px;
      margin: 0 auto;
    }
    nav {
      background: #fff;
      border-bottom: 1px solid #e0e0e0;
      padding: 15px 0;
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      z-index: 1000;
    }
    nav .container {
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    nav a {
      color: #487531;
      text-decoration: none;
      font-weight: 500;
    }
    nav a:hover {
      text-decoration: underline;
    }
    h1 {
      color: #1D3215;
      margin-bottom: 10px;
      font-size: 2rem;
    }
    h2 {
      color: #487531;
      margin-top: 30px;
      margin-bottom: 15px;
      font-size: 1.5rem;
    }
    h3 {
      color: #487531;
      margin-top: 25px;
      margin-bottom: 10px;
      font-size: 1.25rem;
    }
    p {
      margin-bottom: 15px;
      text-align: justify;
    }
    ul, ol {
      margin-left: 25px;
      margin-bottom: 15px;
    }
    li {
      margin-bottom: 8px;
    }
    strong {
      color: #1D3215;
    }
    .subtitle {
      color: #666;
      margin-bottom: 30px;
    }
    .row {
      display: flex;
      flex-wrap: wrap;
      margin: 0 -15px;
    }
    .col {
      flex: 1;
      padding: 0 15px;
      min-width: 300px;
    }
    .section {
      background: #f9f9f9;
      padding: 20px;
      border-radius: 8px;
      margin-bottom: 20px;
      border-left: 4px solid #487531;
    }
    .section-title {
      font-weight: 600;
      color: #1D3215;
      margin-bottom: 10px;
    }
    .highlight {
      background: #fff3cd;
      border-left: 4px solid #ffc107;
      padding: 15px;
      margin-top: 15px;
      border-radius: 4px;
    }
    .highlight.danger {
      background: #f8d7da;
      border-left-color: #dc3545;
    }
    .lang-label {
      font-weight: 600;
      color: #487531;
      margin-bottom: 10px;
      padding-bottom: 5px;
      border-bottom: 2px solid #487531;
    }
    .divider {
      border-top: 1px solid #e0e0e0;
      margin: 40px 0;
    }
    .brand {
      text-align: center;
      color: #666;
      font-size: 0.9rem;
      margin-bottom: 20px;
    }
    footer {
      margin-top: 50px;
      padding-top: 30px;
      border-top: 1px solid #e0e0e0;
      text-align: center;
      color: #666;
      font-size: 0.9rem;
    }
    footer a {
      color: #487531;
      text-decoration: none;
    }
    footer a:hover {
      text-decoration: underline;
    }
    a[href^="mailto:"] {
      color: #487531;
      text-decoration: none;
    }
    a[href^="mailto:"]:hover {
      text-decoration: underline;
    }
    @media (max-width: 768px) {
      body { padding: 70px 15px 30px; }
      h1 { font-size: 1.75rem; }
      h2 { font-size: 1.35rem; }
      h3 { font-size: 1.15rem; }
      .col {
        min-width: 100%;
        margin-bottom: 30px;
      }
    }
  </style>
</head>
<body>
  <nav>
    <div class="container">
      <a href="{{ url('/') }}">← Volver al sitio</a>
      <a href="{{ url('/') }}">Corral X</a>
    </div>
  </nav>

  <main>
    <div class="container">
      <h1>Eliminar cuenta y datos</h1>
      <p class="subtitle">En esta página se explica cómo puedes eliminar tu cuenta de Corral X y qué ocurre con tus datos. Esta información corresponde a lo declarado en Google Play (Data Safety).</p>

      <div class="row">
        <div class="col">
          <div class="lang-label">ES · Español</div>

          <div class="section">
            <div class="section-title">Cómo eliminar tu cuenta desde la app</div>
            <p>Puedes eliminar tu cuenta de Corral X directamente desde la app móvil. Una vez eliminada, perderás acceso a tu perfil y a todos tus datos asociados.</p>
            <ol>
              <li>Abre la app <strong>Corral X</strong> en tu dispositivo.</li>
              <li>Ve a <strong>Perfil → Configuración → Eliminar cuenta</strong>.</li>
              <li>Revisa el aviso de eliminación y confirma tu decisión.</li>
            </ol>
            <div class="highlight">
              <strong>Importante:</strong> la eliminación de cuenta es permanente y no puede deshacerse. Asegúrate de haber guardado cualquier información importante antes de continuar.
            </div>
          </div>

          <div class="section">
            <div class="section-title">Qué datos se eliminan</div>
            <p>Cuando solicitas la eliminación de tu cuenta, se elimina de forma permanente lo siguiente:</p>
            <ul>
              <li>Información de perfil (nombre, correo electrónico, teléfono).</li>
              <li>Perfiles de fincas / hatos registrados en Corral X.</li>
              <li>Anuncios, publicaciones y listados de ganado y servicios.</li>
              <li>Fotos e imágenes que hayas subido a la plataforma.</li>
              <li>Chats y mensajes enviados dentro de la app.</li>
              <li>Favoritos, historial de búsquedas y elementos guardados.</li>
              <li>Cualquier otra información asociada directamente a tu cuenta de usuario.</li>
            </ul>
          </div>

          <div class="section">
            <div class="section-title">Datos que pueden conservarse temporalmente</div>
            <p>Por motivos legales, de seguridad y prevención de fraude, algunos datos técnicos pueden conservarse durante un tiempo limitado antes de ser eliminados de forma definitiva:</p>
            <ul>
              <li>Registros técnicos y de seguridad relacionados con actividad sospechosa (hasta 180 días).</li>
              <li>Registros de transacciones o movimientos necesarios para conciliaciones y auditorías internas (hasta 90 días).</li>
            </ul>
            <p style="margin-top: 15px; color: #666; font-size: 0.9rem;">Estos datos se conservan únicamente con fines legítimos, no se usan para publicidad y se protegen mediante medidas de seguridad apropiadas.</p>
          </div>

          <div class="section">
            <div class="section-title">Plazos y contacto de soporte</div>
            <p>Una vez confirmada la eliminación desde la app, el proceso de eliminación de tu cuenta y datos asociados puede tardar entre <strong>7 y 30 días</strong> en completarse en todos nuestros sistemas.</p>
            <p style="margin-top: 15px;">Si tienes preguntas o necesitas ayuda adicional con la eliminación de tu cuenta o datos, puedes escribirnos a:</p>
            <p style="margin-top: 10px;"><a href="mailto:soporte@corralx.com">soporte@corralx.com</a></p>
          </div>
        </div>

        <div class="col">
          <div class="lang-label">EN · English</div>

          <div class="section">
            <div class="section-title">How to delete your account</div>
            <p>You can delete your Corral X account directly from within the mobile app. Once deleted, you will lose access to your profile and all associated data.</p>
            <ol>
              <li>Open the <strong>Corral X</strong> app on your device.</li>
              <li>Go to <strong>Profile → Settings → Delete Account</strong>.</li>
              <li>Review the deletion notice and confirm your decision.</li>
            </ol>
            <div class="highlight danger">
              <strong>Note:</strong> Account deletion is permanent and cannot be undone. Make sure you have saved any important information before proceeding.
            </div>
          </div>

          <div class="section">
            <div class="section-title">Data that will be deleted</div>
            <p>When you request account deletion, the following data will be permanently removed:</p>
            <ul>
              <li>Profile information (name, email address, phone number).</li>
              <li>Registered farm/ranch profiles within Corral X.</li>
              <li>Listings and posts for cattle and related services.</li>
              <li>Photos and media you uploaded to the platform.</li>
              <li>Chats and messages sent within the app.</li>
              <li>Favorites, saved items and browsing preferences.</li>
              <li>Any other information directly linked to your user account.</li>
            </ul>
          </div>

          <div class="section">
            <div class="section-title">Data that may be retained temporarily</div>
            <p>For legal, security and fraud-prevention purposes, some technical records may be retained for a limited period before being fully deleted:</p>
            <ul>
              <li>Security and anti-fraud logs related to suspicious activity (up to 180 days).</li>
              <li>Transaction and bookkeeping logs required for internal audits (up to 90 days).</li>
            </ul>
            <p style="margin-top: 15px; color: #666; font-size: 0.9rem;">These records are kept only for legitimate purposes, are not used for advertising, and are protected with appropriate security measures.</p>
          </div>

          <div class="section">
            <div class="section-title">Timing & support</div>
            <p>After you confirm deletion from within the app, your account and its associated data will normally be deleted from our active systems within <strong>7–30 days</strong>.</p>
            <p style="margin-top: 15px;">For any questions regarding account or data deletion, you can contact us at:</p>
            <p style="margin-top: 10px;"><a href="mailto:soporte@corralx.com">soporte@corralx.com</a></p>
          </div>
        </div>
      </div>

      <div class="divider"></div>
      <div class="brand">
        <p>Corral X · Ganadería al mundo digital.</p>
        <p style="margin-top: 5px; font-size: 0.85rem;">Last updated: {{ date('Y') }}</p>
      </div>
    </div>
  </main>

  <footer>
    <div class="container">
      <p>&copy; {{ date('Y') }} Corral X. Todos los derechos reservados.</p>
      <p><a href="{{ url('/') }}">Volver al sitio</a></p>
    </div>
  </footer>
</body>
</html>

