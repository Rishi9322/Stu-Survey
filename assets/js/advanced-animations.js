/**
 * Advanced Animation System with Anime.js
 * Provides robust, creative animations for the Student Survey System
 */

class AdvancedAnimations {
    constructor() {
        this.initialized = false;
        this.particles = [];
        this.init();
    }

    init() {
        if (this.initialized) return;
        
        this.createParticleBackground();
        this.initScrollAnimations();
        this.initFormAnimations();
        this.initDataAnimations();
        this.initLoadingAnimations();
        
        this.initialized = true;
    }

    // Particle Background System
    createParticleBackground() {
        const particleBg = document.createElement('div');
        particleBg.className = 'particle-bg';
        document.body.appendChild(particleBg);

        for (let i = 0; i < 50; i++) {
            this.createParticle(particleBg);
        }

        this.animateParticles();
    }

    createParticle(container) {
        const particle = document.createElement('div');
        particle.className = 'particle';
        
        const size = Math.random() * 4 + 2;
        particle.style.width = size + 'px';
        particle.style.height = size + 'px';
        particle.style.left = Math.random() * window.innerWidth + 'px';
        particle.style.top = Math.random() * window.innerHeight + 'px';
        
        container.appendChild(particle);
        this.particles.push(particle);
    }

    animateParticles() {
        anime({
            targets: '.particle',
            translateX: () => anime.random(-100, 100),
            translateY: () => anime.random(-100, 100),
            scale: [1, 1.5, 1],
            opacity: [0.3, 0.8, 0.3],
            duration: () => anime.random(3000, 8000),
            easing: 'easeInOutSine',
            loop: true,
            direction: 'alternate',
            delay: () => anime.random(0, 2000)
        });
    }

    // Scroll-triggered Animations
    initScrollAnimations() {
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    this.animateElement(entry.target);
                }
            });
        }, observerOptions);

        // Observe elements for animation
        document.querySelectorAll('.card, .table, .chart-container, .stats-card').forEach(el => {
            observer.observe(el);
        });
    }

    animateElement(element) {
        const animationType = element.dataset.animation || 'fadeInUp';
        
        switch (animationType) {
            case 'fadeInUp':
                anime({
                    targets: element,
                    opacity: [0, 1],
                    translateY: [30, 0],
                    duration: 800,
                    easing: 'easeOutQuart'
                });
                break;
            
            case 'scaleIn':
                anime({
                    targets: element,
                    opacity: [0, 1],
                    scale: [0.8, 1],
                    duration: 600,
                    easing: 'easeOutBack'
                });
                break;
            
            case 'slideInLeft':
                anime({
                    targets: element,
                    opacity: [0, 1],
                    translateX: [-50, 0],
                    duration: 700,
                    easing: 'easeOutExpo'
                });
                break;
        }
    }

    // Form Animation System
    initFormAnimations() {
        // Input focus animations
        document.querySelectorAll('input, textarea, select').forEach(input => {
            input.addEventListener('focus', () => {
                anime({
                    targets: input,
                    scale: [1, 1.02],
                    duration: 200,
                    easing: 'easeOutQuart'
                });
            });

            input.addEventListener('blur', () => {
                anime({
                    targets: input,
                    scale: [1.02, 1],
                    duration: 200,
                    easing: 'easeOutQuart'
                });
            });
        });

        // Form submission animation
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', (e) => {
                const submitBtn = form.querySelector('[type="submit"]');
                if (submitBtn) {
                    this.animateSubmission(submitBtn);
                }
            });
        });
    }

    animateSubmission(button) {
        const originalText = button.textContent;
        button.textContent = 'Processing...';
        
        anime({
            targets: button,
            scale: [1, 0.95, 1],
            duration: 300,
            easing: 'easeInOutQuart',
            complete: () => {
                // Add loading spinner
                button.innerHTML = '<span class="anime-loading"></span>';
                this.animateSpinner(button.querySelector('.anime-loading'));
            }
        });
    }

    animateSpinner(spinner) {
        anime({
            targets: spinner,
            rotate: 360,
            duration: 1000,
            easing: 'linear',
            loop: true
        });
    }

    // Data Visualization Animations
    initDataAnimations() {
        // Animate progress bars
        document.querySelectorAll('.progress-bar').forEach(bar => {
            const width = bar.dataset.width || bar.style.width;
            bar.style.width = '0%';
            
            anime({
                targets: bar,
                width: width,
                duration: 1500,
                easing: 'easeOutExpo',
                delay: 300
            });
        });

        // Animate counters
        document.querySelectorAll('.counter').forEach(counter => {
            const target = parseInt(counter.dataset.target) || 0;
            
            anime({
                targets: counter,
                innerHTML: [0, target],
                duration: 2000,
                easing: 'easeOutExpo',
                round: 1,
                delay: 500
            });
        });
    }

    // Loading Animation System
    initLoadingAnimations() {
        // Dot loading animation
        const dotContainers = document.querySelectorAll('.anime-dots');
        dotContainers.forEach(container => {
            const dots = container.querySelectorAll('.anime-dot');
            
            anime({
                targets: dots,
                scale: [1, 1.5, 1],
                opacity: [0.5, 1, 0.5],
                duration: 800,
                delay: anime.stagger(200),
                loop: true,
                direction: 'alternate',
                easing: 'easeInOutQuart'
            });
        });
    }

    // Notification System
    showNotification(message, type = 'success') {
        const notification = document.createElement('div');
        notification.className = `anime-notification alert alert-${type}`;
        notification.innerHTML = `
            <div class="d-flex align-items-center">
                <i class="fas fa-check-circle me-2"></i>
                <span>${message}</span>
                <button type="button" class="btn-close ms-auto" onclick="this.parentElement.parentElement.remove()"></button>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        // Show animation
        setTimeout(() => notification.classList.add('show'), 10);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            anime({
                targets: notification,
                translateX: 400,
                opacity: 0,
                duration: 300,
                easing: 'easeInQuart',
                complete: () => notification.remove()
            });
        }, 5000);
    }

    // Page Transition Effects
    pageTransition(callback) {
        // Fade out current content
        anime({
            targets: 'main',
            opacity: 0,
            translateY: -30,
            duration: 400,
            easing: 'easeInQuart',
            complete: () => {
                if (callback) callback();
                
                // Fade in new content
                anime({
                    targets: 'main',
                    opacity: [0, 1],
                    translateY: [30, 0],
                    duration: 600,
                    easing: 'easeOutQuart'
                });
            }
        });
    }

    // Chart Animation Helper
    animateChart(chartElement, data) {
        const bars = chartElement.querySelectorAll('.chart-bar');
        
        anime({
            targets: bars,
            height: (el, i) => data[i] + '%',
            duration: 1500,
            delay: anime.stagger(100),
            easing: 'easeOutExpo'
        });
    }

    // Utility: Animate on hover
    addHoverAnimation(selector, options = {}) {
        document.querySelectorAll(selector).forEach(el => {
            el.addEventListener('mouseenter', () => {
                anime({
                    targets: el,
                    scale: options.scale || 1.05,
                    translateY: options.translateY || -5,
                    duration: options.duration || 200,
                    easing: 'easeOutQuart'
                });
            });

            el.addEventListener('mouseleave', () => {
                anime({
                    targets: el,
                    scale: 1,
                    translateY: 0,
                    duration: options.duration || 200,
                    easing: 'easeOutQuart'
                });
            });
        });
    }
}

// Initialize Advanced Animations
document.addEventListener('DOMContentLoaded', () => {
    window.advancedAnimations = new AdvancedAnimations();
    
    // Add hover animations to common elements
    window.advancedAnimations.addHoverAnimation('.btn', { scale: 1.03, translateY: -2 });
    window.advancedAnimations.addHoverAnimation('.card', { scale: 1.02, translateY: -5 });
});

// Export for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = AdvancedAnimations;
}
