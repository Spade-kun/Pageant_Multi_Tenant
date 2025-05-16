// Right Sidebar Toggle Functionality
document.addEventListener('DOMContentLoaded', function () {
    const body = document.querySelector('body');
    const sidebarToggle = document.querySelector('.sidebar-toggle');

    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function (e) {
            e.preventDefault();
            if (body.classList.contains('sidebar-right-layout')) {
                body.classList.toggle('sidebar-collapse');

                // Handle mobile view
                if (window.innerWidth <= 991) {
                    body.classList.toggle('nav-toggle');
                }
            }
        });
    }

    // Close sidebar on mobile when clicking outside
    document.addEventListener('click', function (e) {
        if (window.innerWidth <= 991 &&
            body.classList.contains('sidebar-right-layout') &&
            body.classList.contains('nav-toggle')) {

            const sidebar = document.querySelector('.sidebar');
            const sidebarToggleBtn = document.querySelector('.sidebar-toggle');

            if (!sidebar.contains(e.target) && !sidebarToggleBtn.contains(e.target)) {
                body.classList.remove('nav-toggle');
            }
        }
    });

    // Handle window resize
    let resizeTimer;
    window.addEventListener('resize', function () {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function () {
            if (window.innerWidth > 991) {
                body.classList.remove('nav-toggle');
            }
        }, 250);
    });
}); 