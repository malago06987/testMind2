// MindVault JavaScript Functions

// Auto-resize textarea
function initTextareaResize() {
    const textareas = document.querySelectorAll('textarea');
    textareas.forEach(textarea => {
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = this.scrollHeight + 'px';
        });
    });
}

// Auto-hide messages
function initMessageAutoHide() {
    const messages = document.querySelectorAll('.message');
    messages.forEach(message => {
        setTimeout(() => {
            message.style.opacity = '0';
            setTimeout(() => {
                if(message.parentNode) {
                    message.remove();
                }
            }, 300);
        }, 5000);
    });
}

// Smooth page transitions
function initPageTransitions() {
    const links = document.querySelectorAll('a[href*="?page="]');
    links.forEach(link => {
        link.addEventListener('click', function(e) {
            const content = document.querySelector('.page-content');
            if(content) {
                content.style.opacity = '0.5';
                content.style.transform = 'translateY(10px)';
            }
        });
    });
}

// Mood selector interactions
function initMoodSelector() {
    const moodOptions = document.querySelectorAll('.mood-option');
    moodOptions.forEach(option => {
        option.addEventListener('click', function() {
            const radio = this.querySelector('input[type="radio"]');
            if(radio) {
                radio.checked = true;
                
                // Visual feedback
                moodOptions.forEach(opt => opt.classList.remove('selected'));
                this.classList.add('selected');
                
                // Add animation
                const emoji = this.querySelector('.mood-emoji');
                if(emoji) {
                    emoji.style.transform = 'scale(1.2)';
                    setTimeout(() => {
                        emoji.style.transform = 'scale(1)';
                    }, 200);
                }
            }
        });
    });
}

// Word cloud interactions
function initWordCloud() {
    const wordTags = document.querySelectorAll('.word-tag');
    wordTags.forEach(tag => {
        tag.addEventListener('mouseenter', function() {
            this.style.transform = 'scale(1.1)';
            this.style.zIndex = '10';
        });
        
        tag.addEventListener('mouseleave', function() {
            this.style.transform = 'scale(1)';
            this.style.zIndex = '1';
        });
    });
}

// Local storage for draft entries
function initDraftSaving() {
    const textarea = document.querySelector('.entry-textarea');
    if(textarea) {
        // Load saved draft
        const draft = localStorage.getItem('mindvault_draft');
        if(draft && !textarea.value) {
            textarea.value = draft;
            textarea.style.height = 'auto';
            textarea.style.height = textarea.scrollHeight + 'px';
        }
        
        // Save draft on input
        textarea.addEventListener('input', function() {
            localStorage.setItem('mindvault_draft', this.value);
        });
        
        // Clear draft on submit
        const form = textarea.closest('form');
        if(form) {
            form.addEventListener('submit', function() {
                localStorage.removeItem('mindvault_draft');
            });
        }
    }
}

// Keyboard shortcuts
function initKeyboardShortcuts() {
    document.addEventListener('keydown', function(e) {
        // Ctrl/Cmd + Enter to submit entry form
        if((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
            const submitBtn = document.querySelector('button[name="submit_entry"]');
            if(submitBtn) {
                e.preventDefault();
                submitBtn.click();
            }
        }
        
        // Escape to clear focused elements
        if(e.key === 'Escape') {
            document.activeElement.blur();
        }
    });
}

// Statistics animations
function initStatsAnimations() {
    const statNumbers = document.querySelectorAll('.stat-number');
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if(entry.isIntersecting) {
                const target = entry.target;
                const finalValue = parseInt(target.textContent);
                
                if(!isNaN(finalValue)) {
                    animateNumber(target, 0, finalValue, 1000);
                }
                
                observer.unobserve(target);
            }
        });
    });
    
    statNumbers.forEach(num => observer.observe(num));
}

function animateNumber(element, start, end, duration) {
    const startTime = performance.now();
    
    function updateNumber(currentTime) {
        const elapsed = currentTime - startTime;
        const progress = Math.min(elapsed / duration, 1);
        
        // Easing function
        const easeOut = 1 - Math.pow(1 - progress, 3);
        const current = Math.floor(start + (end - start) * easeOut);
        
        element.textContent = current;
        
        if(progress < 1) {
            requestAnimationFrame(updateNumber);
        } else {
            element.textContent = end;
        }
    }
    
    requestAnimationFrame(updateNumber);
}

// Theme utilities
function initThemeUtils() {
    // Add theme class to body
    document.body.classList.add('mindvault-theme');
    
    // Detect system theme changes
    if(window.matchMedia) {
        const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
        mediaQuery.addEventListener('change', function(e) {
            // Theme is already dark, but we could add light theme support here
        });
    }
}

// Performance optimizations
function initPerformanceOptimizations() {
    // Lazy load images if any
    const images = document.querySelectorAll('img[data-src]');
    if(images.length && 'IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if(entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.removeAttribute('data-src');
                    imageObserver.unobserve(img);
                }
            });
        });
        
        images.forEach(img => imageObserver.observe(img));
    }
    
    // Debounce resize events
    let resizeTimeout;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(() => {
            // Trigger resize-dependent functions
            initTextareaResize();
        }, 250);
    });
}

// Error handling
function initErrorHandling() {
    window.addEventListener('error', function(e) {
        console.error('MindVault Error:', e.error);
        // Could send error reports to server
    });
    
    // Handle unhandled promise rejections
    window.addEventListener('unhandledrejection', function(e) {
        console.error('MindVault Promise Rejection:', e.reason);
    });
}

// Initialize all functions when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    initTextareaResize();
    initMessageAutoHide();
    initPageTransitions();
    initMoodSelector();
    initWordCloud();
    initDraftSaving();
    initKeyboardShortcuts();
    initStatsAnimations();
    initThemeUtils();
    initPerformanceOptimizations();
    initErrorHandling();
    
    // Focus on textarea if exists
    const textarea = document.querySelector('.entry-textarea');
    if(textarea) {
        setTimeout(() => textarea.focus(), 100);
    }
    
    console.log('MindVault initialized successfully! 🧠✨');
});

// Export functions for use in other scripts
window.MindVault = {
    initTextareaResize,
    initMessageAutoHide,
    initMoodSelector,
    animateNumber
};
