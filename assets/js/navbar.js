document.addEventListener('DOMContentLoaded', function () {
    // ====================== VARIABLES ======================
    const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
    const navMenu = document.querySelector('.nav-menu');
    const navActions = document.querySelector('.nav-actions');
    const userDropdown = document.querySelector('.user-dropdown');
    const dropdownToggle = document.getElementById('dropdownToggle');
    const dropdownMenu = document.getElementById('dropdownMenu');
    const header = document.querySelector('header');

    // ====================== MOBILE MENU TOGGLE ======================
    if (mobileMenuBtn && navMenu) {
        mobileMenuBtn.addEventListener('click', function () {
            navMenu.classList.toggle('active');

            // Toggle nav actions in mobile view
            if (window.innerWidth <= 1150) {
                if (navMenu.classList.contains('active')) {
                    navActions.style.display = 'flex';
                    navActions.style.transform = 'translateY(0)';
                } else {
                    navActions.style.transform = 'translateY(-150%)';
                    setTimeout(() => {
                        navActions.style.display = 'none';
                    }, 300);
                }
            }
        });
    }

    // ====================== USER DROPDOWN FUNCTIONALITY ======================
    if (dropdownToggle && dropdownMenu) {
        // Toggle dropdown on click
        dropdownToggle.addEventListener('click', function (e) {
            e.stopPropagation();
            dropdownMenu.classList.toggle('show');

            // Close other dropdowns if any
            document.querySelectorAll('.dropdown-content').forEach(dropdown => {
                if (dropdown !== dropdownMenu && dropdown.classList.contains('show')) {
                    dropdown.classList.remove('show');
                }
            });
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function (e) {
            if (!userDropdown.contains(e.target)) {
                dropdownMenu.classList.remove('show');
            }
        });

        // Close dropdown when clicking a link (for mobile)
        dropdownMenu.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', function () {
                if (window.innerWidth <= 1150) {
                    dropdownMenu.classList.remove('show');
                    if (navMenu) navMenu.classList.remove('active');
                    if (navActions) {
                        navActions.style.transform = 'translateY(-150%)';
                        setTimeout(() => {
                            navActions.style.display = 'none';
                        }, 300);
                    }
                }
            });
        });
    }

    // ====================== SMOOTH SCROLLING ======================
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            // Skip smooth scroll for dropdown links
            if (this.closest('.dropdown-content')) return;

            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));

            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth'
                });

                // Close mobile menu if open
                if (navMenu) navMenu.classList.remove('active');
                if (navActions) {
                    navActions.style.transform = 'translateY(-150%)';
                    setTimeout(() => {
                        navActions.style.display = 'none';
                    }, 300);
                }
            }
        });
    });

    // ====================== STICKY HEADER ======================
    if (header) {
        window.addEventListener('scroll', function () {
            header.classList.toggle('sticky', window.scrollY > 0);
        });
    }

    // ====================== RESPONSIVE ADJUSTMENTS ======================
    function handleResponsive() {
        if (dropdownMenu) {
            if (window.innerWidth <= 1150) {
                // Mobile view adjustments
                dropdownMenu.style.position = 'static';
                dropdownMenu.style.width = '100%';
                dropdownMenu.style.boxShadow = 'none';
                dropdownMenu.style.borderRadius = '0';
            } else {
                // Desktop view adjustments
                dropdownMenu.style.position = 'absolute';
                dropdownMenu.style.width = 'auto';
                dropdownMenu.style.boxShadow = '0 8px 16px rgba(0, 0, 0, 0.1)';
                dropdownMenu.style.borderRadius = '4px';
            }
        }
    }

    // Initialize and add resize listener
    handleResponsive();
    window.addEventListener('resize', handleResponsive);

    // ====================== ROLE-BASED ITEMS HANDLING ======================
    document.querySelectorAll('.dropdown-content a').forEach(link => {
        if (link.href.includes('/staff/') || link.href.includes('/admin/')) {
            link.addEventListener('click', function () {
                console.log('Accessing restricted feature:', this.textContent);
                // Add any additional role-based handling here
            });
        }
    });

    // ====================== TAB FUNCTIONALITY (if needed) ======================
    const tabButtons = document.querySelectorAll('.tab-btn');
    tabButtons.forEach(button => {
        button.addEventListener('click', () => {
            tabButtons.forEach(btn => btn.classList.remove('active'));
            button.classList.add('active');
        });
    });
});

// ====================== GLOBAL CLICK HANDLER ======================
// For closing dropdowns when clicking anywhere on the page
window.addEventListener('click', function (e) {
    if (!e.target.closest('.user-dropdown')) {
        document.querySelectorAll('.dropdown-content').forEach(dropdown => {
            dropdown.classList.remove('show');
        });
    }
});