    </main>
</div>

<script>
    // Sidebar toggle for mobile
    const hamburger = document.getElementById('hamburger');
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');

    function toggleSidebar() {
        sidebar.classList.toggle('mobile-open');
        overlay.classList.toggle('active');
        hamburger.classList.toggle('active');
    }

    function closeSidebar() {
        sidebar.classList.remove('mobile-open');
        overlay.classList.remove('active');
        hamburger.classList.remove('active');
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
