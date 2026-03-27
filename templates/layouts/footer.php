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
    }
</script>

<?php echo $extraScripts ?? ''; ?>

</body>
</html>
