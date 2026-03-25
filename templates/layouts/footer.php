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
        const studyGroupLink = document.getElementById('study-group-link');
        if (studyGroupLink) {
            studyGroupLink.addEventListener('click', async function(e) {
                // Mark all notifications as viewed
                try {
                    await fetch('/study-group/mark-all-viewed', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        }
                    });
                    // Remove the badge
                    const badge = this.querySelector('.notification-badge');
                    if (badge) {
                        badge.remove();
                    }
                } catch (error) {
                    console.error('Error marking notifications as viewed:', error);
                }
            });
        }
    }
</script>

<?php echo $extraScripts ?? ''; ?>

</body>
</html>
