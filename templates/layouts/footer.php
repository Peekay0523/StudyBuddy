    </main>
</div>

<?php if (isLoggedIn()): ?>
<!-- Modern Mobile Bottom Navigation -->
<nav class="mobile-bottom-nav">
    <div class="mobile-bottom-nav-inner">
        <a href="/ai-chat" class="nav-item <?php echo ($currentPage ?? '') === 'ai-chat' ? 'active' : ''; ?>">
            <i class="fas fa-robot"></i>
            <span>AI Chat</span>
        </a>
        <a href="javascript:void(0)" onclick="toggleCalculator()" class="nav-item <?php echo ($currentPage ?? '') === 'math' ? 'active' : ''; ?>">
            <i class="fas fa-calculator"></i>
            <span>Calculator</span>
        </a>
        <div class="nav-item-plus-wrapper">
            <a href="/scan" class="nav-item-plus">
                <i class="fas fa-plus"></i>
            </a>
        </div>
        <a href="/data-sheets" class="nav-item <?php echo ($currentPage ?? '') === 'data-sheets' ? 'active' : ''; ?>">
            <i class="fas fa-file-lines"></i>
            <span>Datasheets</span>
        </a>
        <a href="/dashboard" class="nav-item <?php echo ($currentPage ?? '') === 'dashboard' ? 'active' : ''; ?>">
            <i class="fas fa-user"></i>
            <span>Profile</span>
        </a>
    </div>
</nav>

<?php include __DIR__ . '/calculator_modal.php'; ?>

<style>
/* Modern Mobile Bottom Navigation Styles */
.mobile-bottom-nav {
    display: none; /* Hide by default for desktop */
    position: fixed;
    bottom: 20px;
    left: 50%;
    transform: translateX(-50%);
    z-index: 9999;
    width: 92%;
    max-width: 420px;
    background: rgba(255, 255, 255, 0.7);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border-radius: 40px;
    padding: 12px 10px;
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.12);
    border: 1px solid rgba(255, 255, 255, 0.4);
    transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

/* Show only on mobile/tablet */
@media (max-width: 768px) {
    .mobile-bottom-nav {
        display: block;
    }
}

/* Hide when sidebar is active */
.mobile-bottom-nav.sidebar-active {
    opacity: 0;
    pointer-events: none;
    transform: translateX(-50%) translateY(100px);
}

.mobile-bottom-nav-inner {
    display: flex;
    justify-content: space-around;
    align-items: center;
    position: relative;
    width: 100%;
}

.nav-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    color: #94a3b8;
    text-decoration: none;
    font-size: 10px;
    font-weight: 500;
    transition: all 0.3s ease;
    flex: 1;
    z-index: 1;
}

.nav-item i {
    font-size: 20px;
    margin-bottom: 4px;
    transition: transform 0.3s ease;
}

.nav-item.active {
    color: #6366f1;
}

.nav-item.active i {
    transform: translateY(-2px);
}

.nav-item:active {
    transform: scale(0.9);
}

/* Floating Plus Button */
.nav-item-plus-wrapper {
    flex: 1;
    display: flex;
    justify-content: center;
    position: relative;
    height: 40px;
}

.nav-item-plus {
    position: absolute;
    bottom: 0px;
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
    border-radius: 50%;
    display: flex;
    justify-content: center;
    align-items: center;
    color: white;
    font-size: 24px;
    box-shadow: 0 10px 25px rgba(99, 102, 241, 0.4);
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    text-decoration: none;
    z-index: 2;
    border: 4px solid #fff;
}

.nav-item-plus:hover {
    transform: translateY(-8px) scale(1.1);
    box-shadow: 0 15px 30px rgba(99, 102, 241, 0.5);
}

.nav-item-plus:active {
    transform: translateY(-2px) scale(0.95);
}

/* Page padding to prevent overlap */
body {
    padding-bottom: 100px !important;
}

@media (max-width: 480px) {
    .mobile-bottom-nav {
        bottom: 15px;
        width: 94%;
        padding: 10px 5px;
    }
    
    .nav-item span {
        font-size: 9px;
    }
    
    .nav-item-plus {
        width: 54px;
        height: 54px;
        font-size: 20px;
        bottom: 5px;
    }
}

/* Dark mode support (if applicable) */
@media (prefers-color-scheme: dark) {
    .mobile-bottom-nav {
        background: rgba(30, 41, 59, 0.7);
        border: 1px solid rgba(255, 255, 255, 0.1);
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3);
    }
    .nav-item {
        color: #64748b;
    }
    .nav-item-plus {
        border-color: #1e293b;
    }
}
</style>
<?php endif; ?>

<script>
    // Sidebar toggle for mobile
    const hamburger = document.getElementById('hamburger');
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');

    function toggleSidebar() {
        sidebar.classList.toggle('mobile-open');
        overlay.classList.toggle('active');
        hamburger.classList.toggle('active');
        
        // Hide/Show bottom navigation bar
        const bottomNav = document.querySelector('.mobile-bottom-nav');
        if (bottomNav) {
            bottomNav.classList.toggle('sidebar-active');
        }
    }

    function closeSidebar() {
        sidebar.classList.remove('mobile-open');
        overlay.classList.remove('active');
        hamburger.classList.remove('active');
        
        // Ensure bottom navigation bar is visible
        const bottomNav = document.querySelector('.mobile-bottom-nav');
        if (bottomNav) {
            bottomNav.classList.remove('sidebar-active');
        }
    }

    if (hamburger && sidebar) {
        hamburger.addEventListener('click', toggleSidebar);

        // Close sidebar when clicking overlay
        if (overlay) {
            overlay.addEventListener('click', closeSidebar);
        }

        // Close sidebar when clicking on a link
        const sidebarLinks = sidebar.querySelectorAll('a');
        sidebarLinks.forEach(link => {
            link.addEventListener('click', closeSidebar);
        });

        // Mark study group notifications as viewed when clicking the Study Group link
        // NOTE: We no longer mark notifications as viewed here - they should only be
        // cleared when the user actually views the specific content (chat, scripts)
        const studyGroupLink = document.getElementById('study-group-link');
        if (studyGroupLink) {
            studyGroupLink.addEventListener('click', async function(e) {
                // Just update the badge count from server - don't mark as viewed
                try {
                    const response = await fetch('/api/study-group-notification-count');
                    const data = await response.json();
                    
                    // Update badge with fresh count
                    const badge = this.querySelector('.notification-badge');
                    if (data.count <= 0) {
                        if (badge) badge.remove();
                    } else {
                        if (badge) {
                            badge.textContent = data.count > 99 ? '99+' : data.count;
                        } else if (data.count > 0) {
                            const newBadge = document.createElement('span');
                            newBadge.className = 'notification-badge';
                            newBadge.textContent = data.count > 99 ? '99+' : data.count;
                            this.appendChild(newBadge);
                        }
                    }
                } catch (error) {
                    console.error('Error updating notification count:', error);
                }
            });
        }

        // Mark bursary notifications as viewed when clicking the Careers link
        const careersLink = document.getElementById('careers-link');
        if (careersLink) {
            careersLink.addEventListener('click', async function(e) {
                // Mark bursaries as viewed
                try {
                    await fetch('/mark-bursaries-viewed', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        }
                    });
                    // Refresh the notification count
                    const response = await fetch('/api/bursary-notification-count');
                    const data = await response.json();
                    
                    // Remove or update badge
                    const badge = this.querySelector('.notification-badge');
                    if (data.count <= 0) {
                        if (badge) badge.remove();
                    } else {
                        if (badge) {
                            badge.textContent = data.count > 99 ? '99+' : data.count;
                        } else if (data.count > 0) {
                            const newBadge = document.createElement('span');
                            newBadge.className = 'notification-badge';
                            newBadge.textContent = data.count > 99 ? '99+' : data.count;
                            this.appendChild(newBadge);
                        }
                    }
                } catch (error) {
                    console.error('Error marking bursaries as viewed:', error);
                }
            });
        }
    }

    // PWA Service Worker Registration
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('/service-worker.js')
                .then((registration) => {
                    console.log('[Service Worker] Registered successfully:', registration.scope);
                })
                .catch((error) => {
                    console.log('[Service Worker] Registration failed:', error);
                });
        });
    }

    // PWA Install Button Logic
    let deferredPrompt;
    const installBtn = document.getElementById('installBtn');

    window.addEventListener('beforeinstallprompt', (e) => {
        // Prevent the mini-infobar from showing automatically
        e.preventDefault();
        // Stash the event so it can be triggered later
        deferredPrompt = e;
        // Show the install button
        if (installBtn) {
            installBtn.style.display = 'block';
        }
    });

    function installApp() {
        if (!deferredPrompt) {
            console.log('[PWA] Install prompt not available');
            return;
        }

        // Show the install prompt
        deferredPrompt.prompt();

        // Wait for the user to respond to the prompt
        deferredPrompt.userChoice.then((choiceResult) => {
            if (choiceResult.outcome === 'accepted') {
                console.log('[PWA] User accepted the install prompt');
            } else {
                console.log('[PWA] User dismissed the install prompt');
            }
            // Clear the deferredPrompt so it can't be used again
            deferredPrompt = null;
        });
    }

    if (installBtn) {
        installBtn.addEventListener('click', installApp);
    }

    // Hide install button after app is installed
    window.addEventListener('appinstalled', () => {
        console.log('[PWA] App installed successfully');
        if (installBtn) {
            installBtn.style.display = 'none';
        }
        deferredPrompt = null;
    });
</script>

<?php echo $extraScripts ?? ''; ?>

</body>
</html>
