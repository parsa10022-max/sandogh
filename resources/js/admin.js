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

            document.body.classList.toggle('reports-sidebar-open');

        } else {

            document.body.classList.toggle('reports-sidebar-collapsed');

        }

    });


    /*
    |--------------------------------------------------------------------------
    | Overlay
    |--------------------------------------------------------------------------
    */

    if (overlay) {

        overlay.addEventListener('click', () => {

            document.body.classList.remove('reports-sidebar-open');

        });

    }


    /*
    |--------------------------------------------------------------------------
    | ESC
    |--------------------------------------------------------------------------
    */

    document.addEventListener('keydown', (event) => {

        if (event.key === 'Escape') {

            document.body.classList.remove('reports-sidebar-open');

        }

    });


    /*
    |--------------------------------------------------------------------------
    | Close After Clicking Sidebar Link - Mobile
    |--------------------------------------------------------------------------
    */

    sidebar.querySelectorAll('.reports-sidebar-link').forEach((link) => {

        link.addEventListener('click', () => {

            if (window.innerWidth <= 991) {

                document.body.classList.remove('reports-sidebar-open');

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

            document.body.classList.remove('reports-sidebar-open');

        }

    });

});
