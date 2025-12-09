<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Ocultar loading overlay con transición suave
        setTimeout(() => {
            const loadingOverlay = document.getElementById('loadingOverlay');
            if (loadingOverlay) {
                loadingOverlay.style.opacity = '0';
                setTimeout(() => {
                    loadingOverlay.style.display = 'none';
                    document.body.classList.add('loaded');
                }, 500);
            }
        }, 800);

        // Navbar scroll effect
        const navbar = document.querySelector('.navbar');
        let lastScroll = 0;

        if (navbar) {
            window.addEventListener('scroll', () => {
                const currentScroll = window.pageYOffset;

                if (currentScroll > 50) {
                    navbar.classList.add('scrolled');
                } else {
                    navbar.classList.remove('scrolled');
                }

                lastScroll = currentScroll;
            }, { passive: true });
        }

        // Intersection Observer para animaciones de scroll
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -80px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const delay = entry.target.dataset.wowDelay || '0s';
                    entry.target.style.transitionDelay = delay;
                    entry.target.classList.add('visible');
                    observer.unobserve(entry.target);
                }
            });
        }, observerOptions);

        const revealElements = document.querySelectorAll('.fade-in-up, .zoom-in');
        revealElements.forEach(element => observer.observe(element));

        // Liquid Glass card hover effect con seguimiento del mouse
        document.querySelectorAll('.feature-card').forEach(card => {
            card.addEventListener('mousemove', (e) => {
                const rect = card.getBoundingClientRect();
                const x = ((e.clientX - rect.left) / rect.width) * 100;
                const y = ((e.clientY - rect.top) / rect.height) * 100;

                card.style.setProperty('--mouse-x', `${x}%`);
                card.style.setProperty('--mouse-y', `${y}%`);
                card.style.background = `
                    radial-gradient(
                        600px circle at ${x}% ${y}%,
                        rgba(52, 211, 153, 0.08),
                        transparent 40%
                    ),
                    rgba(255, 255, 255, 0.03)
                `;
            });

            card.addEventListener('mouseleave', () => {
                card.style.background = 'rgba(255, 255, 255, 0.03)';
            });
        });

        // Smooth scroll para links de navegación
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target && navbar) {
                    const navHeight = navbar.offsetHeight;
                    const targetPosition = target.getBoundingClientRect().top + window.pageYOffset - navHeight - 20;

                    window.scrollTo({
                        top: targetPosition,
                        behavior: 'smooth'
                    });
                }
            });
        });

        // Parallax sutil para los blobs
        let ticking = false;
        window.addEventListener('scroll', () => {
            if (!ticking) {
                requestAnimationFrame(() => {
                    const scrolled = window.pageYOffset;
                    const blobs = document.querySelectorAll('.blob');

                    blobs.forEach((blob, index) => {
                        const speed = 0.05 + (index * 0.02);
                        blob.style.transform = `translateY(${scrolled * speed}px)`;
                    });

                    ticking = false;
                });
                ticking = true;
            }
        }, { passive: true });
    });
</script>

