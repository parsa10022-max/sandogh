document.addEventListener('DOMContentLoaded', () => {

    const sidebar = document.getElementById('adminSidebar');
    const toggle = document.getElementById('adminSidebarToggle');
    const overlay = document.getElementById('adminSidebarOverlay');

    if (!sidebar || !toggle) {
        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Sidebar Toggle
    |--------------------------------------------------------------------------
    */

    toggle.addEventListener('click', () => {

        if (window.innerWidth <= 991) {

            document.body.classList.toggle('admin-sidebar-open');

        } else {

            document.body.classList.toggle('admin-sidebar-collapsed');

        }

    });


    /*
    |--------------------------------------------------------------------------
    | Overlay
    |--------------------------------------------------------------------------
    */

    if (overlay) {

        overlay.addEventListener('click', () => {

            document.body.classList.remove('admin-sidebar-open');

        });

    }


    /*
    |--------------------------------------------------------------------------
    | ESC
    |--------------------------------------------------------------------------
    */

    document.addEventListener('keydown', (event) => {

        if (event.key === 'Escape') {

            document.body.classList.remove('admin-sidebar-open');

        }

    });


    /*
    |--------------------------------------------------------------------------
    | Close After Clicking Sidebar Link - Mobile
    |--------------------------------------------------------------------------
    */

    sidebar.querySelectorAll('.admin-sidebar-link').forEach((link) => {

        link.addEventListener('click', () => {

            if (window.innerWidth <= 991) {

                document.body.classList.remove('admin-sidebar-open');

            }

        });

    });


    /*
    |--------------------------------------------------------------------------
    | Resize
    |--------------------------------------------------------------------------
    */

    window.addEventListener('resize', () => {

        if (window.innerWidth > 991) {

            document.body.classList.remove('admin-sidebar-open');

        }

    });

});
