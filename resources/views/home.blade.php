@extends('layouts.app')

@section('title', '3D Printing Solutions')

@push('styles')
<style>
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        color: #333;
        transition: background-color 0.3s ease, color 0.3s ease;
    }
    .hero {
        background: linear-gradient(135deg, #3a7bd5, #00d2ff);
        color: white;
        padding: 5rem 0;
        margin: -2rem -2rem 2rem -2rem;
    }
    .service-card {
        border-radius: 8px;
        transition: transform 0.3s, background-color 0.3s, color 0.3s;
        height: 100%;
    }
    .service-card:hover {
        transform: translateY(-5px);
    }
    .portfolio-item {
        overflow: hidden;
        border-radius: 0.5rem;
        margin-bottom: 30px;
    }
    .portfolio-item img {
        transition: transform 0.5s;
        width: 100%;
        height: auto;
    }
    .portfolio-item:hover img {
        transform: scale(1.05);
    }
    .cta-section {
        background-color: #f8f9fa;
        transition: background-color 0.3s;
    }
    footer {
        background-color: #343a40;
        color: white;
    }

    /* Dark mode styles */
    [data-bs-theme="dark"] {
        --bs-body-color: #e2e2e2;
        --bs-body-bg: #1a1a1a;
    }
    [data-bs-theme="dark"] .cta-section {
        background-color: #2a2a2a;
    }
    [data-bs-theme="dark"] .card {
        background-color: #2c2c2c;
        border-color: #444;
    }
    [data-bs-theme="dark"] .form-control {
        background-color: #333;
        border-color: #444;
        color: #e2e2e2;
    }
    [data-bs-theme="dark"] .form-control::placeholder {
        color: #aaa;
    }

    /* Dark mode toggle button styling */
    .theme-toggle {
        cursor: pointer;
        color: rgba(255, 255, 255, 0.85);
        border: none;
        background: transparent;
        margin-left: 15px;
        padding: 5px;
        display: flex;
        align-items: center;
    }
    .theme-toggle:hover {
        color: white;
    }
    .management-section {
        background-color: #f8f9fa;
        padding: 3rem 0;
        margin: 2rem -2rem;
    }
    .management-card {
        text-align: center;
        padding: 2rem;
        transition: transform 0.3s;
    }
    .management-card:hover {
        transform: translateY(-5px);
    }
    .management-icon {
        font-size: 2.5rem;
        margin-bottom: 1rem;
        color: #3a7bd5;
    }
</style>
@endpush

@section('content')
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">3D Print Pro</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link active" href="#">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="#services">Services</a></li>
                    <li class="nav-item"><a class="nav-link" href="#portfolio">Portfolio</a></li>
                    <li class="nav-item"><a class="nav-link" href="#contact">Contact</a></li>
                    <li class="nav-item"><a class="nav-link" href="#invoices">Invoices</a></li>
                    <li class="nav-item d-flex align-items-center">
                        <button id="themeToggle" class="theme-toggle" aria-label="Toggle dark mode">
                            <svg id="sunIcon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-sun" viewBox="0 0 16 16">
                                <path d="M8 11a3 3 0 1 1 0-6 3 3 0 0 1 0 6m0 1a4 4 0 1 0 0-8 4 4 0 0 0 0 8M8 0a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 0m0 13a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 13m8-5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2a.5.5 0 0 1 .5.5M3 8a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2A.5.5 0 0 1 3 8m10.657-5.657a.5.5 0 0 1 0 .707l-1.414 1.415a.5.5 0 1 1-.707-.708l1.414-1.414a.5.5 0 0 1 .707 0m-9.193 9.193a.5.5 0 0 1 0 .707L3.05 13.657a.5.5 0 0 1-.707-.707l1.414-1.414a.5.5 0 0 1 .707 0m9.193 2.121a.5.5 0 0 1-.707 0l-1.414-1.414a.5.5 0 0 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .707M4.464 4.465a.5.5 0 0 1-.707 0L2.343 3.05a.5.5 0 0 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .708"/>
                            </svg>
                            <svg id="moonIcon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="bi bi-moon-stars d-none" viewBox="0 0 16 16">
                                <path d="M6 .278a.77.77 0 0 1 .08.858 7.2 7.2 0 0 0-.878 3.46c0 4.021 3.278 7.277 7.318 7.277q.792 0 1.533-.16a.79.79 0 0 1 .81.316.73.73 0 0 1-.031.893A8.35 8.35 0 0 1 8.344 16C3.734 16 0 12.286 0 7.71 0 4.266 2.114 1.312 5.124.06A.75.75 0 0 1 6 .278M4.858 1.311A7.27 7.27 0 0 0 1.025 7.71c0 4.02 3.279 7.276 7.319 7.276a7.32 7.32 0 0 0 5.205-2.162q-.506.063-1.029.063c-4.61 0-8.343-3.714-8.343-8.29 0-1.167.242-2.278.681-3.286z"/>
                                <path d="M10.794 3.148a.217.217 0 0 1 .412 0l.387 1.162c.173.518.579.924 1.097 1.097l1.162.387a.217.217 0 0 1 0 .412l-1.162.387a1.73 1.73 0 0 0-1.097 1.097l-.387 1.162a.217.217 0 0 1-.412 0l-.387-1.162A1.73 1.73 0 0 0 9.31 6.593l-1.162-.387a.217.217 0 0 1 0-.412l1.162-.387a1.73 1.73 0 0 0 1.097-1.097l.387-1.162z"/>
                            </svg>
                        </button>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container text-center">
            <h1 class="display-4 fw-bold mb-4">Bringing Ideas to Life with 3D Printing</h1>
            <p class="lead mb-5">Professional 3D printing services for prototypes, custom designs, and production parts</p>
            <a href="#contact" class="btn btn-light btn-lg px-5 py-3">Get a Quote</a>
        </div>
    </section>

    <!-- Services Section -->
    <section id="services" class="py-5">
        <div class="container">
            <h2 class="text-center mb-5">Our Services</h2>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card service-card shadow-sm h-100">
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="currentColor" class="bi bi-box" viewBox="0 0 16 16">
                                    <path d="M8.186 1.113a.5.5 0 0 0-.372 0L1.846 3.5l2.404.961L10.404 2l-2.218-.887zm3.564 1.426L5.596 5 8 5.961 14.154 3.5l-2.404-.961zm3.25 1.7-6.5 2.6v7.922l6.5-2.6V4.24zM7.5 14.762V6.838L1 4.239v7.923l6.5 2.6zM7.443.184a1.5 1.5 0 0 1 1.114 0l7.129 2.852A.5.5 0 0 1 16 3.5v8.662a1 1 0 0 1-.629.928l-7.185 2.874a.5.5 0 0 1-.372 0L.63 13.09a1 1 0 0 1-.63-.928V3.5a.5.5 0 0 1 .314-.464L7.443.184z"/>
                                </svg>
                            </div>
                            <h3>Rapid Prototyping</h3>
                            <p>Turn your ideas into physical prototypes within days, not weeks.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card service-card shadow-sm h-100">
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="currentColor" class="bi bi-gear" viewBox="0 0 16 16">
                                    <path d="M8 4.754a3.246 3.246 0 1 0 0 6.492 3.246 3.246 0 0 0 0-6.492zM5.754 8a2.246 2.246 0 1 1 4.492 0 2.246 2.246 0 0 1-4.492 0z"/>
                                    <path d="M9.796 1.343c-.527-1.79-3.065-1.79-3.592 0l-.094.319a.873.873 0 0 1-1.255.52l-.292-.16c-1.64-.892-3.433.902-2.54 2.541l.159.292a.873.873 0 0 1-.52 1.255l-.319.094c-1.79.527-1.79 3.065 0 3.592l.319.094a.873.873 0 0 1 .52 1.255l-.16.292c-.892 1.64.901 3.434 2.541 2.54l.292-.159a.873.873 0 0 1 1.255.52l.094.319c.527 1.79 3.065 1.79 3.592 0l.094-.319a.873.873 0 0 1 1.255-.52l.292.16c1.64.893 3.434-.902 2.54-2.541l-.159-.292a.873.873 0 0 1 .52-1.255l.319-.094c1.79-.527 1.79-3.065 0-3.592l-.319-.094a.873.873 0 0 1-.52-1.255l.16-.292c.893-1.64-.902-3.433-2.541-2.54l-.292.159a.873.873 0 0 1-1.255-.52l-.094-.319zm-2.633.283c.246-.835 1.428-.835 1.674 0l.094.319a1.873 1.873 0 0 0 2.693 1.115l.291-.16c.764-.415 1.6.42 1.184 1.185l-.159.292a1.873 1.873 0 0 0 1.116 2.692l.318.094c.835.246.835 1.428 0 1.674l-.319.094a1.873 1.873 0 0 0-1.115 2.693l.16.291c.415.764-.42 1.6-1.185 1.184l-.291-.159a1.873 1.873 0 0 0-2.693 1.116l-.094.318c-.246.835-1.428.835-1.674 0l-.094-.319a1.873 1.873 0 0 0-2.692-1.115l-.292.16c-.764.415-1.6-.42-1.184-1.185l.159-.291A1.873 1.873 0 0 0 1.945 8.93l-.319-.094c-.835-.246-.835-1.428 0-1.674l.319-.094A1.873 1.873 0 0 0 3.06 4.377l-.16-.292c-.415-.764.42-1.6 1.185-1.184l.292.159a1.873 1.873 0 0 0 2.692-1.115l.094-.319z"/>
                                </svg>
                            </div>
                            <h3>Custom Manufacturing</h3>
                            <p>Small to medium production runs with consistent quality and precision.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card service-card shadow-sm h-100">
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="currentColor" class="bi bi-brush" viewBox="0 0 16 16">
                                    <path d="M15.825.12a.5.5 0 0 1 .132.584c-1.53 3.43-4.743 8.17-7.095 10.64a6.067 6.067 0 0 1-2.373 1.534c-.018.227-.06.538-.16.868-.201.659-.667 1.479-1.708 1.74a8.118 8.118 0 0 1-3.078.132 3.659 3.659 0 0 1-.562-.135 1.382 1.382 0 0 1-.466-.247.714.714 0 0 1-.204-.288.622.622 0 0 1 .004-.443c.095-.245.316-.38.461-.452.394-.197.625-.453.867-.826.095-.144.184-.297.287-.472l.117-.198c.151-.255.326-.54.546-.848.528-.739 1.201-.925 1.746-.896.126.007.243.025.348.048.062-.172.142-.38.238-.608.261-.619.658-1.419 1.187-2.069 2.176-2.67 6.18-6.206 9.117-8.104a.5.5 0 0 1 .596.04zM4.705 11.912a1.23 1.23 0 0 0-.419-.1c-.246-.013-.573.05-.879.479-.197.275-.355.532-.5.777l-.105.177c-.106.181-.213.362-.32.528a3.39 3.39 0 0 1-.76.861c.69.112 1.736.111 2.657-.12.559-.139.843-.569.993-1.06a3.122 3.122 0 0 0 .126-.75l-.793-.792zm1.44.026c.12-.04.277-.1.458-.183a5.068 5.068 0 0 0 1.535-1.1c1.9-1.996 4.412-5.57 6.052-8.631-2.59 1.927-5.566 4.66-7.302 6.792-.442.543-.795 1.243-1.042 1.826-.121.288-.214.54-.275.72v.001l.575.575zm-4.973 3.04.007-.005a.031.031 0 0 1-.007.004zm3.582-3.043.002.001h-.002z"/>
                                </svg>
                            </div>
                            <h3>Design Services</h3>
                            <p>Expert 3D modeling and design optimization for perfect prints.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Portfolio Section -->
    <section id="portfolio" class="py-5 bg-light">
        <div class="container">
            <h2 class="text-center mb-5">Recent Projects</h2>
            <div class="row">
                <div class="col-md-4">
                    <div class="portfolio-item shadow">
                        <img src="https://via.placeholder.com/600x400?text=3D+Printed+Product" alt="3D Printed Product">
                        <div class="p-3">
                            <h5>Custom Phone Stand</h5>
                            <p class="text-muted">Functional prototype</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="portfolio-item shadow">
                        <img src="https://via.placeholder.com/600x400?text=3D+Printed+Parts" alt="3D Printed Parts">
                        <div class="p-3">
                            <h5>Mechanical Components</h5>
                            <p class="text-muted">Production series</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="portfolio-item shadow">
                        <img src="https://via.placeholder.com/600x400?text=3D+Design" alt="3D Design">
                        <div class="p-3">
                            <h5>Architectural Model</h5>
                            <p class="text-muted">Concept visualization</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section id="contact" class="cta-section py-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 mb-4 mb-lg-0">
                    <h2>Ready to start your 3D printing project?</h2>
                    <p class="lead">Get in touch today for a free consultation and quote.</p>
                </div>
                <div class="col-lg-6">
                    <form>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <input type="text" class="form-control" placeholder="Your Name" required>
                            </div>
                            <div class="col-md-6">
                                <input type="email" class="form-control" placeholder="Email Address" required>
                            </div>
                            <div class="col-12">
                                <textarea class="form-control" rows="3" placeholder="Tell us about your project"></textarea>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">Send Message</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-6 text-center text-md-start">
                    <p>© 2025 3D Print Pro. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <a href="#" class="text-white me-3">Privacy Policy</a>
                    <a href="#" class="text-white">Terms of Service</a>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Dark mode functionality
        document.addEventListener('DOMContentLoaded', () => {
            const htmlElement = document.documentElement;
            const themeToggle = document.getElementById('themeToggle');
            const sunIcon = document.getElementById('sunIcon');
            const moonIcon = document.getElementById('moonIcon');

            // Check for saved theme preference or use system preference
            const savedTheme = localStorage.getItem('theme');
            if (savedTheme) {
                htmlElement.setAttribute('data-bs-theme', savedTheme);
                updateIcon(savedTheme);
            } else {
                // Check system preference
                const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                const initialTheme = prefersDark ? 'dark' : 'light';
                htmlElement.setAttribute('data-bs-theme', initialTheme);
                updateIcon(initialTheme);
            }

            themeToggle.addEventListener('click', () => {
                const currentTheme = htmlElement.getAttribute('data-bs-theme');
                const newTheme = currentTheme === 'dark' ? 'light' : 'dark';

                htmlElement.setAttribute('data-bs-theme', newTheme);
                localStorage.setItem('theme', newTheme);
                updateIcon(newTheme);
            });

            function updateIcon(theme) {
                if (theme === 'dark') {
                    moonIcon.classList.remove('d-none');
                    sunIcon.classList.add('d-none');
                } else {
                    sunIcon.classList.remove('d-none');
                    moonIcon.classList.add('d-none');
                }
            }
        });
    </script>
@endsection
