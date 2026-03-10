/**
 * Flohmarkt Blog - Main JavaScript
 * @package Flohmarkt_Blog
 * @version 1.0.0
 */

document.addEventListener('DOMContentLoaded', function () {

    /* ===== DARK MODE TOGGLE ===== */
    const darkModeBtn = document.getElementById('btn-dark-mode');
    const html = document.documentElement;

    // Check saved preference
    const savedTheme = localStorage.getItem('flohmarkt-theme');
    if (savedTheme) {
        html.setAttribute('data-theme', savedTheme);
        updateDarkModeIcon(savedTheme);
    }

    if (darkModeBtn) {
        darkModeBtn.addEventListener('click', function () {
            const current = html.getAttribute('data-theme');
            const next = current === 'dark' ? 'light' : 'dark';
            html.setAttribute('data-theme', next);
            localStorage.setItem('flohmarkt-theme', next);
            updateDarkModeIcon(next);
        });
    }

    function updateDarkModeIcon(theme) {
        if (darkModeBtn) {
            const sunIcon = darkModeBtn.querySelector('.sun-icon');
            const moonIcon = darkModeBtn.querySelector('.moon-icon');
            const logoLight = document.querySelector('.logo-light');
            const logoDark = document.querySelector('.logo-dark');

            if (sunIcon && moonIcon) {
                if (theme === 'dark') {
                    sunIcon.style.display = 'none';
                    moonIcon.style.display = 'block';
                    if (logoLight) logoLight.style.display = 'none';
                    if (logoDark) logoDark.style.display = 'block';
                } else {
                    sunIcon.style.display = 'block';
                    moonIcon.style.display = 'none';
                    if (logoLight) logoLight.style.display = 'block';
                    if (logoDark) logoDark.style.display = 'none';
                }
            }
        }
    }

    /* ===== HAMBURGER MENU & DRAWER ===== */
    const hamburger = document.getElementById('hamburger');
    const navMain = document.getElementById('nav-main');
    const menuOverlay = document.getElementById('menu-overlay');
    const body = document.body;

    if (hamburger && navMain && menuOverlay) {
        function toggleMenu() {
            hamburger.classList.toggle('active');
            navMain.classList.toggle('active');
            menuOverlay.classList.toggle('active');
            body.classList.toggle('menu-open');
        }

        function closeMenu() {
            hamburger.classList.remove('active');
            navMain.classList.remove('active');
            menuOverlay.classList.remove('active');
            body.classList.remove('menu-open');
        }

        hamburger.addEventListener('click', toggleMenu);
        menuOverlay.addEventListener('click', closeMenu);

        // Close menu on link click
        navMain.querySelectorAll('a').forEach(function (link) {
            link.addEventListener('click', closeMenu);
        });
    }

    /* ===== HEADER SCROLL EFFECT ===== */
    const header = document.getElementById('site-header');
    if (header) {
        window.addEventListener('scroll', function () {
            if (window.scrollY > 50) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });
    }

    /* ===== FADE-IN ANIMATION ON SCROLL ===== */
    const fadeElements = document.querySelectorAll('.fade-in');
    if (fadeElements.length > 0) {
        const observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });

        fadeElements.forEach(function (el) {
            observer.observe(el);
        });
    }

    /* ===== SEARCH BUTTON & LIVE SEARCH ===== */
    const searchBtn = document.getElementById('btn-search');
    const heroSearchInput = document.querySelector('.hero-search input[name="s"]');

    if (searchBtn) {
        searchBtn.addEventListener('click', function () {
            if (heroSearchInput) {
                heroSearchInput.focus();
                heroSearchInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
            } else {
                window.location.href = flohmarktAjax.siteurl + '/?s=';
            }
        });
    }

    if (heroSearchInput) {
        // Create results container
        const resultsContainer = document.createElement('div');
        resultsContainer.className = 'search-live-results';
        heroSearchInput.parentElement.appendChild(resultsContainer);

        let timeout = null;
        heroSearchInput.addEventListener('input', function () {
            const query = this.value.trim();
            clearTimeout(timeout);

            if (query.length < 2) {
                resultsContainer.style.display = 'none';
                return;
            }

            timeout = setTimeout(() => {
                fetchResults(query, resultsContainer);
            }, 300);
        });

        // Close results on click outside
        document.addEventListener('click', function (e) {
            if (!heroSearchInput.parentElement.contains(e.target)) {
                resultsContainer.style.display = 'none';
            }
        });
    }

    function fetchResults(query, container) {
        const formData = new FormData();
        formData.append('action', 'live_search');
        formData.append('query', query);
        formData.append('nonce', flohmarktAjax.nonce);

        fetch(flohmarktAjax.ajaxurl, {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.data.length > 0) {
                    renderLiveResults(data.data, container);
                } else {
                    container.style.display = 'none';
                }
            })
            .catch(error => console.error('Error fetching live search results:', error));
    }

    function renderLiveResults(results, container) {
        container.innerHTML = '';
        results.forEach(item => {
            const div = document.createElement('a');
            div.href = item.url;
            div.className = 'live-result-item';

            let thumbHtml = item.thumb ? `<img src="${item.thumb}" alt="${item.title}">` : `<div class="result-icon-fallback">${item.icon}</div>`;

            div.innerHTML = `
                ${thumbHtml}
                <div class="result-info">
                    <span class="result-title">${item.title}</span>
                    <span class="result-meta">${item.meta}</span>
                </div>
            `;
            container.appendChild(div);
        });
        container.style.display = 'block';
    }

    /* ===== LOAD MORE EVENTS ===== */
    const loadMoreBtn = document.getElementById('btn-load-more-events');
    const eventsContainer = document.getElementById('events-list-container');

    if (loadMoreBtn && eventsContainer) {
        let currentPage = parseInt(loadMoreBtn.getAttribute('data-page'));
        const maxPages = parseInt(loadMoreBtn.getAttribute('data-max'));

        if (currentPage >= maxPages) {
            loadMoreBtn.parentElement.style.display = 'none';
        }

        loadMoreBtn.addEventListener('click', function () {
            const originalText = loadMoreBtn.innerText;
            loadMoreBtn.innerText = 'Lädt...';
            loadMoreBtn.disabled = true;

            const nextPage = currentPage + 1;
            const formData = new FormData();
            formData.append('action', 'load_more_events');
            formData.append('page', nextPage);
            formData.append('nonce', flohmarktAjax.nonce);

            fetch(flohmarktAjax.ajaxurl, {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.data) {
                        // Append new items
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(data.data, 'text/html');
                        const newItems = Array.from(doc.body.children);

                        newItems.forEach(item => {
                            item.style.opacity = '0';
                            item.style.transform = 'translateY(10px)';
                            eventsContainer.appendChild(item);

                            // Small timeout to trigger CSS transition
                            requestAnimationFrame(() => {
                                item.style.transition = 'all 0.3s ease';
                                item.style.opacity = '1';
                                item.style.transform = 'translateY(0)';
                            });
                        });

                        currentPage = nextPage;
                        loadMoreBtn.setAttribute('data-page', currentPage);

                        if (currentPage >= maxPages) {
                            loadMoreBtn.parentElement.style.display = 'none';
                        } else {
                            loadMoreBtn.innerText = originalText;
                            loadMoreBtn.disabled = false;
                        }
                    } else {
                        loadMoreBtn.innerText = 'Keine weiteren Veranstaltungen';
                    }
                })
                .catch(error => {
                    console.error('Error loading more events:', error);
                    loadMoreBtn.innerText = originalText;
                    loadMoreBtn.disabled = false;
                });
        });
    }

    /* ===== SMOOTH SCROLL FOR ANCHOR LINKS ===== */
    document.querySelectorAll('a[href^="#"]').forEach(function (anchor) {
        anchor.addEventListener('click', function (e) {
            var target = document.querySelector(this.getAttribute('href'));
            if (target) {
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });

    /* ===== LAZY LOAD IMAGES ===== */
    if ('loading' in HTMLImageElement.prototype) {
        document.querySelectorAll('img[loading="lazy"]').forEach(function (img) {
            if (img.dataset.src) {
                img.src = img.dataset.src;
            }
        });
    }

    /* ===== NEWSLETTER FORM ===== */
    document.querySelectorAll('.footer-newsletter button, .btn-accent').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            var input = this.closest('div').querySelector('input[type="email"]') ||
                this.previousElementSibling;
            if (input && input.type === 'email') {
                if (input.value && input.value.includes('@')) {
                    alert('Vielen Dank für Ihre Anmeldung! Sie erhalten bald unseren Newsletter.');
                    input.value = '';
                } else {
                    alert('Bitte geben Sie eine gültige E-Mail-Adresse ein.');
                }
            }
        });
    });

    /* ===== HERO STATS COUNTER ANIMATION ===== */
    const statNumbers = document.querySelectorAll('.stat-number');
    if (statNumbers.length > 0) {
        const statsObserver = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    animateCounter(entry.target);
                    statsObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.5 });

        statNumbers.forEach(function (el) { statsObserver.observe(el); });
    }

    function animateCounter(el) {
        var target = parseInt(el.textContent) || 0;
        if (target === 0) return;
        var current = 0;
        var step = Math.max(1, Math.floor(target / 40));
        var timer = setInterval(function () {
            current += step;
            if (current >= target) {
                current = target;
                clearInterval(timer);
            }
            el.textContent = current;
        }, 30);
    }

});
