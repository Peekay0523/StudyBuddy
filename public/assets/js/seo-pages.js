/**
 * SEO Pages JavaScript
 * Handles interactive features for SEO content pages
 */

document.addEventListener('DOMContentLoaded', function() {
    // Smooth scroll for table of contents
    const tocLinks = document.querySelectorAll('.table-of-contents a');
    tocLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href').substring(1);
            const targetElement = document.getElementById(targetId);
            
            if (targetElement) {
                targetElement.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
                
                // Update URL without scrolling
                history.pushState(null, null, `#${targetId}`);
            }
        });
    });

    // Track outbound clicks
    const downloadButtons = document.querySelectorAll('.btn-download');
    downloadButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Track download event (for analytics)
            if (typeof gtag !== 'undefined') {
                gtag('event', 'download', {
                    event_category: 'SEO Page',
                    event_label: window.location.pathname
                });
            }
        });
    });

    // Copy link to clipboard
    const qaItems = document.querySelectorAll('.qa-item');
    qaItems.forEach(item => {
        const questionHeader = item.querySelector('.question-header');
        if (questionHeader) {
            questionHeader.style.cursor = 'pointer';
            questionHeader.addEventListener('dblclick', function() {
                const questionId = item.id;
                const url = window.location.origin + window.location.pathname + '#' + questionId;
                
                navigator.clipboard.writeText(url).then(() => {
                    // Show toast notification
                    showToast('Link copied to clipboard!');
                }).catch(err => {
                    console.error('Failed to copy:', err);
                });
            });
        }
    });

    // Print functionality
    const printButton = document.querySelector('.btn-print');
    if (printButton) {
        printButton.addEventListener('click', function() {
            window.print();
        });
    }

    // Highlight current question in TOC on scroll
    const tocLinksMap = new Map();
    tocLinks.forEach(link => {
        const targetId = link.getAttribute('href').substring(1);
        tocLinksMap.set(targetId, link);
    });

    const observerOptions = {
        root: null,
        rootMargin: '-100px 0px -60% 0px',
        threshold: 0
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                // Remove active class from all TOC links
                tocLinks.forEach(link => link.classList.remove('active'));
                
                // Add active class to current question's TOC link
                const tocLink = tocLinksMap.get(entry.target.id);
                if (tocLink) {
                    tocLink.classList.add('active');
                }
            }
        });
    }, observerOptions);

    qaItems.forEach(item => {
        observer.observe(item);
    });

    // Search functionality with keyboard shortcut
    document.addEventListener('keydown', function(e) {
        // Ctrl/Cmd + K to focus search
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            const searchInput = document.querySelector('input[name="q"]');
            if (searchInput) {
                searchInput.focus();
            }
        }
    });

    // Lazy load images
    const images = document.querySelectorAll('img[data-src]');
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.removeAttribute('data-src');
                observer.unobserve(img);
            }
        });
    });

    images.forEach(img => imageObserver.observe(img));

    // Add copy code button to code blocks
    const codeBlocks = document.querySelectorAll('pre code');
    codeBlocks.forEach(codeBlock => {
        const pre = codeBlock.parentElement;
        const copyButton = document.createElement('button');
        copyButton.className = 'copy-code-btn';
        copyButton.textContent = '📋 Copy';
        copyButton.style.cssText = `
            position: absolute;
            top: 0.5rem;
            right: 0.5rem;
            padding: 0.25rem 0.5rem;
            background: rgba(255,255,255,0.9);
            border: 1px solid #ddd;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.75rem;
        `;
        
        pre.style.position = 'relative';
        pre.appendChild(copyButton);
        
        copyButton.addEventListener('click', function() {
            navigator.clipboard.writeText(codeBlock.textContent).then(() => {
                copyButton.textContent = '✓ Copied!';
                setTimeout(() => {
                    copyButton.textContent = '📋 Copy';
                }, 2000);
            });
        });
    });

    // Track time on page
    let timeOnPage = 0;
    setInterval(() => {
        timeOnPage++;
        
        // Send to analytics every 30 seconds
        if (timeOnPage % 30 === 0 && typeof gtag !== 'undefined') {
            gtag('event', 'time_on_page', {
                event_category: 'Engagement',
                event_label: window.location.pathname,
                value: timeOnPage
            });
        }
    }, 1000);

    // Back to top button
    const backToTop = document.createElement('button');
    backToTop.className = 'back-to-top';
    backToTop.textContent = '↑ Top';
    backToTop.style.cssText = `
        position: fixed;
        bottom: 2rem;
        right: 2rem;
        padding: 0.75rem 1rem;
        background: #007bff;
        color: white;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-size: 1rem;
        font-weight: 600;
        opacity: 0;
        transition: opacity 0.3s;
        z-index: 1000;
    `;
    backToTop.style.display = 'none';
    
    document.body.appendChild(backToTop);
    
    window.addEventListener('scroll', () => {
        if (window.scrollY > 300) {
            backToTop.style.opacity = '1';
            backToTop.style.display = 'block';
        } else {
            backToTop.style.opacity = '0';
            backToTop.style.display = 'none';
        }
    });
    
    backToTop.addEventListener('click', () => {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
});

// Toast notification function
function showToast(message, duration = 3000) {
    const toast = document.createElement('div');
    toast.className = 'toast-notification';
    toast.textContent = message;
    toast.style.cssText = `
        position: fixed;
        bottom: 2rem;
        left: 50%;
        transform: translateX(-50%);
        background: #333;
        color: white;
        padding: 0.75rem 1.5rem;
        border-radius: 8px;
        font-size: 0.9rem;
        z-index: 9999;
        animation: slideUp 0.3s ease;
    `;
    
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateX(-50%) translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateX(-50%) translateY(0);
            }
        }
    `;
    document.head.appendChild(style);
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.style.animation = 'slideUp 0.3s ease reverse';
        setTimeout(() => {
            toast.remove();
        }, 300);
    }, duration);
}

// PDF download handler
function downloadPDF() {
    const slug = window.location.pathname.split('/').pop();
    const pdfUrl = `/seo/${slug}/pdf`;
    
    // Show loading state
    const downloadBtn = document.querySelector('.btn-download');
    if (downloadBtn) {
        downloadBtn.textContent = '⏳ Generating PDF...';
        downloadBtn.disabled = true;
    }
    
    // Open PDF in new tab
    window.open(pdfUrl, '_blank');
    
    // Reset button after 5 seconds
    setTimeout(() => {
        if (downloadBtn) {
            downloadBtn.textContent = '📄 Download PDF';
            downloadBtn.disabled = false;
        }
    }, 5000);
}

// Share functionality
function sharePage() {
    if (navigator.share) {
        navigator.share({
            title: document.title,
            url: window.location.href
        }).then(() => {
            console.log('Shared successfully');
        }).catch((error) => {
            console.log('Error sharing:', error);
        });
    } else {
        // Fallback: copy to clipboard
        navigator.clipboard.writeText(window.location.href).then(() => {
            showToast('Link copied to clipboard!');
        });
    }
}
