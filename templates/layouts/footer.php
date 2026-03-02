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
    }
</script>

<?php echo $extraScripts ?? ''; ?>

</body>
</html>
