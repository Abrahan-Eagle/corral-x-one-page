@extends('front.layouts.app-front')

@section('content')
    @include('front.components.navbar')

    <div class="position-relative overflow-hidden no-pt">
        <!-- Hero Section mejorada -->
        <section id="inicio" class="section-lg">
            <div class="container text-center hero-content">
                <div class="row justify-content-center align-items-center">
                    <div class="col-lg-7 col-md-12">
                        <div class="text-center text-lg-start">
                            <span class="badge badge-custom rounded-pill fw-semibold mb-4 fade-in-up delay-100">
                                La nueva era de la ganadería
                            </span>
                            <h1 class="display-4 fw-bolder mb-4 fade-in-up delay-200">
                                Conecta, Compra y Vende Ganado con Facilidad
                            </h1>
                            <p class="lead mb-5 fade-in-up delay-300">
                                Corral X es la plataforma digital que une a ganaderos de toda Venezuela. Encuentra los
                                mejores ejemplares o publica tu rebaño para miles de compradores.
                            </p>
                            <div
                                class="d-flex flex-column flex-sm-row justify-content-center justify-content-lg-start align-items-center gap-3 fade-in-up delay-400">
                                <a href="#descargar" class="download-link btn-shine" aria-label="Descargar Corral X en App Store para iOS">
                                    <img class="store-badge"
                                        src="{{ asset('assets/front/images/badges/app-store-badge.png') }}"
                                        alt="Descargar app Corral X marketplace ganadero en App Store" loading="lazy">
                                </a>
                                <a href="#descargar" class="download-link btn-shine" aria-label="Descargar Corral X en Google Play para Android">
                                    <img class="store-badge"
                                        src="{{ asset('assets/front/images/badges/google-play-badge.png') }}"
                                        alt="Descargar app Corral X marketplace ganadero en Google Play Store" loading="lazy">
                                </a>
                                <a href="#descargar" class="download-link btn-shine" aria-label="Descargar Corral X en Microsoft Store">
                                    <img class="store-badge"
                                        src="{{ asset('assets/front/images/badges/microsoft-store-badge.png') }}"
                                        alt="Descargar app Corral X marketplace ganadero en Microsoft Store" loading="lazy">
                                </a>
                            </div>
                            <p class="text-muted small mt-3 fade-in-up">Próximamente disponible</p>
                        </div>
                    </div>
                    <!-- Phone Mockup Section -->
                    <div class="col-lg-5 mt-5 mt-lg-0">
                        <div class="zoom-in phone-mockup-wrapper mx-auto">
                            <div class="phone-mockup">
                                <div class="phone-screen">
                                    <img src="{{ asset('assets/front/images/images/phone-mockup.jpg') }}"
                                        alt="Captura de pantalla de la app Corral X mostrando el marketplace ganadero con listado de ganado bovino, bufalino, equino y porcino disponible en Venezuela">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <main>
        @include('front.components.features-section')
        @include('front.components.benefits-section')
        @include('front.components.how-it-works')
        @include('front.components.faq-section')
        @include('front.components.download-section')
    </main>

    @include('front.components.footer')
@endsection

