<!-- Footer -->
<footer class="footer-bg">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <!-- Redes Sociales -->
                <div class="d-flex justify-content-center align-items-center gap-3 mb-3">
                    <a href="https://www.facebook.com/corralx" target="_blank" rel="noopener noreferrer" 
                       class="text-muted social-link" aria-label="Síguenos en Facebook">
                        <i class="fab fa-facebook-f" style="font-size: 1.2rem;"></i>
                    </a>
                    <a href="https://www.instagram.com/corralx" target="_blank" rel="noopener noreferrer" 
                       class="text-muted social-link" aria-label="Síguenos en Instagram">
                        <i class="fab fa-instagram" style="font-size: 1.2rem;"></i>
                    </a>
                    <a href="https://twitter.com/corralx" target="_blank" rel="noopener noreferrer" 
                       class="text-muted social-link" aria-label="Síguenos en Twitter">
                        <i class="fab fa-twitter" style="font-size: 1.2rem;"></i>
                    </a>
                </div>
                
                <!-- Enlaces Legales -->
                <p class="text-muted small mb-2">
                    <a href="{{ route('pages.privacy') }}" class="text-muted link-underline">Política de Privacidad</a>
                    &nbsp;|&nbsp;
                    <a href="{{ route('pages.terms') }}" class="text-muted link-underline">Términos y Condiciones</a>
                    &nbsp;|&nbsp;
                    <a href="{{ route('pages.delete-account') }}" class="text-muted link-underline">Eliminar cuenta</a>
                </p>
                <p class="text-muted small">&copy; {{ date('Y') }} CorralX. Todos los derechos reservados.</p>
            </div>
        </div>
    </div>
</footer>

