    </main>
    <footer class="footer">
        <div class="container">
            <div class="row g-5">
                <!-- Column 1: Brand Info -->
                <div class="col-lg-4">
                    <a href="index.php" class="footer-logo">
                        <span class="text-accent"><?= substr(SITE_NAME, 0, 2) ?></span><?= substr(SITE_NAME, 2) ?>
                    </a>
                    <p class="mb-4 pe-lg-5">
                        <?= get_setting('footer_description', 'Penyedia layanan sewa motor terpercaya di Yogyakarta sejak 2018. Kami mengutamakan kualitas armada dan kepuasan pelanggan di setiap perjalanan.') ?>
                    </p>
                    <div class="social-links">
                        <a href="<?= get_setting('social_instagram', '#') ?>" target="_blank"><i class="fab fa-instagram"></i></a>
                        <a href="<?= get_setting('social_facebook', '#') ?>" target="_blank"><i class="fab fa-facebook-f"></i></a>
                        <a href="<?= get_setting('social_tiktok', '#') ?>" target="_blank"><i class="fab fa-tiktok"></i></a>
                        <a href="<?= get_setting('social_youtube', '#') ?>" target="_blank"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>

                <!-- Column 2: Quick Links -->
                <div class="col-6 col-lg-2">
                    <h6><?= get_setting('footer_explore_title', 'Jelajahi') ?></h6>
                    <ul>
                        <li><a href="<?= BASE_URL ?>index.php">Beranda</a></li>
                        <li><a href="<?= BASE_URL ?>motor.php">Armada</a></li>
                        <li><a href="<?= BASE_URL ?>#tentang">Tentang Kami</a></li>
                        <li><a href="<?= BASE_URL ?>#testimonials">Testimoni</a></li>
                        <li><a href="<?= BASE_URL ?>#contact">Hubungi Kami</a></li>
                    </ul>
                </div>

                <!-- Column 3: Services -->
                <div class="col-6 col-lg-2">
                    <h6><?= get_setting('footer_services_title', 'Layanan') ?></h6>
                    <ul>
                        <li><a href="index.php#facilities">Antar Jemput</a></li>
                        <li><a href="motor.php?type=Matic">Motor Matic</a></li>
                        <li><a href="motor.php?type=Sport">Motor Sport</a></li>
                        <li><a href="index.php#terms">Syarat & Ketentuan</a></li>
                    </ul>
                </div>

                <!-- Column 4: Contact -->
                <div class="col-lg-4">
                    <h6><?= get_setting('footer_contact_title', 'Kontak Support') ?></h6>
                    <ul class="mb-0">
                        <li class="d-flex align-items-start mb-3">
                            <i class="fas fa-map-marker-alt text-accent me-3 mt-1"></i>
                            <span><?= get_setting('address') ?></span>
                        </li>
                        <li class="d-flex align-items-center mb-3">
                            <i class="fab fa-whatsapp text-accent me-3"></i>
                            <span>+<?= get_setting('whatsapp_number') ?></span>
                        </li>
                        <li class="d-flex align-items-center mb-3">
                            <i class="fas fa-envelope text-accent me-3"></i>
                            <span><?= get_setting('email') ?></span>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="row mt-5 pt-5 border-top border-white border-opacity-5">
                <div class="col-md-6 text-center text-md-start">
                    <p class="small mb-0 opacity-50">© <?= date('Y') ?> <?= SITE_NAME ?> <?= get_setting('footer_copyright_loc', 'Yogyakarta') ?>. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-center text-md-end mt-3 mt-md-0">
                    <p class="small mb-0 opacity-50"><?= get_setting('footer_designer_text', 'Designed with <i class="fas fa-heart text-accent mx-1"></i> Special for You') ?></p>
                </div>
            </div>
        </div>
    </footer>


    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Flatpickr JS -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <!-- Lottie Player -->
    <script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>
    <!-- Swiper JS -->
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <!-- AOS JS -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({
            duration: 800,
            once: true,
            offset: 100
        });
    </script>

    <!-- Smooth Scroll Script -->
    <script>
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    window.scrollTo({
                        top: target.offsetTop - 80,
                        behavior: 'smooth'
                    });
                }
            });
        });

        // Register Service Worker for PWA
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('<?= BASE_URL ?>sw.js')
                    .then(reg => console.log('SW Registered', reg))
                    .catch(err => console.log('SW Failed', err));
            });
        }
    </script>
    <!-- Swiper Initialization -->
    <script>
        var swiper = new Swiper(".testimonialSwiper", {
            slidesPerView: 1,
            spaceBetween: 20,
            loop: true,
            autoplay: {
                delay: 4000,
                disableOnInteraction: false,
            },
            pagination: {
                el: ".swiper-pagination",
                clickable: true,
            },
            navigation: {
                nextEl: ".swiper-button-next",
                prevEl: ".swiper-button-prev",
            },
            breakpoints: {
                640: {
                    slidesPerView: 1,
                    spaceBetween: 20,
                },
                768: {
                    slidesPerView: 2,
                    spaceBetween: 30,
                },
                1024: {
                    slidesPerView: 3,
                    spaceBetween: 30,
                },
            },
        });
    </script>

    <style>
        .swiper-pagination-bullet-active {
            background: var(--accent) !important;
        }
        .swiper-button-next, .swiper-button-prev {
            color: var(--accent) !important;
            background: var(--bg-card);
            width: 50px;
            height: 50px;
            border-radius: 50%;
            box-shadow: var(--shadow-md);
            z-index: 100;
            top: 50%;
            transform: translateY(-50%);
        }
        .swiper-button-next { right: 20px !important; }
        .swiper-button-prev { left: 20px !important; }
        .swiper-button-next:after, .swiper-button-prev:after {
            font-size: 20px;
            font-weight: bold;
        }
    </style>
</body>
</html>
