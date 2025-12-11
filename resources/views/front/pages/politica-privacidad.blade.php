<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Política de Privacidad — Corral X</title>
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
      max-width: 800px;
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
    @media (max-width: 768px) {
      body { padding: 70px 15px 30px; }
      h1 { font-size: 1.75rem; }
      h2 { font-size: 1.35rem; }
      h3 { font-size: 1.15rem; }
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
      <h1>Política de Privacidad de Corral X</h1>
      <p><strong>Fecha de entrada en vigor:</strong> 18 de noviembre de 2025</p>

      <p>Esta Política de Privacidad describe cómo la aplicación y el sitio web <strong>Corral X</strong> (en adelante, "la Aplicación" o "nosotros") recopilan, usan y protegen la información personal de sus usuarios.</p>

      <h2>1. Recopilación y Uso de la Información</h2>
      <p>Recopilamos información para proporcionar y mejorar nuestro servicio, incluyendo las funciones del marketplace, mensajería y perfiles.</p>
      <ul>
        <li><strong>Datos de perfil:</strong> nombre, correo electrónico, foto de perfil, biografía y datos de finca/publicación que usted proporciona.</li>
        <li><strong>Fotos y videos:</strong> imágenes de productos (ganado) que publica, selfies y documentos de identidad para verificación de identidad (KYC), y fotos de perfil.</li>
        <li><strong>Ubicación:</strong> recopilamos su ubicación precisa (GPS) y aproximada (basada en red) para mostrar productos cercanos, filtrar búsquedas por ubicación y mejorar la experiencia del marketplace. Puede desactivar el acceso a la ubicación en la configuración de su dispositivo.</li>
        <li><strong>Contenido de mensajes:</strong> los mensajes enviados a través del chat se almacenan para permitir la comunicación entre usuarios.</li>
        <li><strong>Datos de actividad:</strong> acciones en la Aplicación (favoritos, reportes, interacciones).</li>
      </ul>

      <h2>2. Datos de uso y registro (automáticos)</h2>
      <p>Recopilamos datos sobre cómo accede y utiliza la Aplicación: tipo de dispositivo, sistema operativo, identificadores de dispositivo, dirección IP, registros de errores y diagnóstico.</p>
      <p><strong>Token de dispositivo para notificaciones:</strong> utilizamos Firebase Cloud Messaging (FCM) para enviar notificaciones push. Para ello, recopilamos un token único de dispositivo que nos permite enviarle notificaciones sobre mensajes, actualizaciones de productos y otras comunicaciones relevantes.</p>

      <h2>3. Bases legales</h2>
      <p>Procesamos sus datos con base en su consentimiento, para ejecutar el servicio (contrato) y por interés legítimo (seguridad, prevención de fraude y mejora del servicio).</p>

      <h2>4. Uso de servicios de terceros</h2>
      <p>Utilizamos los siguientes servicios de terceros que recopilan datos bajo sus propias políticas de privacidad:</p>
      <ul>
        <li><strong>Google (Autenticación):</strong> para permitir el inicio de sesión con cuenta de Google. Google recopila información según su <a href="https://policies.google.com/privacy" target="_blank">Política de Privacidad</a>.</li>
        <li><strong>Firebase Cloud Messaging (FCM):</strong> servicio de Google para enviar notificaciones push. Compartimos el token de dispositivo y datos básicos de uso para permitir las notificaciones. Firebase procesa estos datos según la <a href="https://firebase.google.com/support/privacy" target="_blank">Política de Privacidad de Firebase</a>.</li>
        <li><strong>Google Analytics (si aplica):</strong> para analizar el uso de la aplicación y mejorar nuestros servicios. Los datos se procesan según la <a href="https://policies.google.com/privacy" target="_blank">Política de Privacidad de Google</a>.</li>
        <li><strong>Proveedores de hosting y servicios:</strong> utilizamos servicios de terceros para alojar nuestros servidores y procesar datos técnicos necesarios para el funcionamiento de la aplicación.</li>
      </ul>

      <h2>5. Compartición de datos</h2>
      <p>Podemos compartir datos con:</p>
      <ul>
        <li><strong>Otros usuarios:</strong> información de publicación visible públicamente (fotos de productos, descripción, ubicación general, datos de contacto si usted lo permite).</li>
        <li><strong>Proveedores de servicios:</strong> hosting, procesadores de pago (si aplica), y servicios técnicos necesarios para el funcionamiento de la aplicación.</li>
        <li><strong>Google/Firebase:</strong> identificadores de dispositivo, datos de uso básicos y tokens para notificaciones push, según se describe en la sección 4.</li>
        <li><strong>Autoridades:</strong> cuando la ley lo requiera o para proteger nuestros derechos legales.</li>
      </ul>

      <h2>6. Permisos de la aplicación</h2>
      <p>La aplicación solicita los siguientes permisos y los utiliza de la siguiente manera:</p>
      <ul>
        <li><strong>Cámara:</strong> para tomar fotos de productos (ganado) que desea publicar, y para la verificación de identidad (KYC) mediante selfies y captura de documentos de identidad.</li>
        <li><strong>Ubicación:</strong> para mostrar productos cercanos a su ubicación, filtrar búsquedas por ubicación y mejorar la experiencia del marketplace. Puede desactivar este permiso en cualquier momento desde la configuración de su dispositivo.</li>
        <li><strong>Notificaciones:</strong> para enviarle alertas sobre mensajes recibidos, actualizaciones de productos de su interés, y otras comunicaciones relevantes. Puede gestionar estas preferencias desde la configuración de la aplicación.</li>
        <li><strong>Almacenamiento:</strong> para guardar temporalmente imágenes antes de subirlas y para mejorar el rendimiento de la aplicación mediante caché.</li>
      </ul>
      <p>Puede revocar estos permisos en cualquier momento desde la configuración de su dispositivo, aunque esto puede afectar algunas funcionalidades de la aplicación.</p>

      <h2>7. Seguridad</h2>
      <p>Implementamos medidas razonables para proteger la información. Sin embargo, ningún sistema es 100% seguro; actúe con precaución al compartir información sensible.</p>

      <h2>8. Privacidad de menores</h2>
      <p>La Aplicación no está dirigida a menores de 18 años. No recopilamos conscientemente información de menores; si tiene conocimiento de ello, contáctenos para eliminar los datos.</p>

      <h2>9. Cambios a esta Política</h2>
      <p>Podemos actualizar esta Política periódicamente; los cambios serán efectivos cuando se publiquen en esta página. Revise la política con regularidad.</p>

      <h2>10. Contáctenos</h2>
      <p>Si tiene preguntas sobre esta Política de Privacidad, contáctenos en: <strong>soporte@corralx.com</strong></p>
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

