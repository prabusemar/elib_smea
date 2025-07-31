document.addEventListener('DOMContentLoaded', function () {
    // Mobile Menu Toggle
    const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
    const navMenu = document.querySelector('.nav-menu');
    const navActions = document.querySelector('.nav-actions');

    if (mobileMenuBtn && navMenu) {
        mobileMenuBtn.addEventListener('click', function () {
            navMenu.classList.toggle('active');

            // For mobile view, toggle nav actions along with menu
            if (window.innerWidth <= 1150) {
                if (navMenu.classList.contains('active')) {
                    navActions.style.transform = 'translateY(0)';
                } else {
                    navActions.style.transform = 'translateY(-150%)';
                }
            }
        });
    }

    // Smooth Scrolling for Anchor Links
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
                if (navActions) navActions.style.transform = 'translateY(-150%)';
            }
        });
    });

    // Sticky Header
    window.addEventListener('scroll', function () {
        const header = document.querySelector('header');
        if (header) {
            header.classList.toggle('sticky', window.scrollY > 0);
        }
    });

    // Tab Functionality
    const tabButtons = document.querySelectorAll('.tab-btn');
    tabButtons.forEach(button => {
        button.addEventListener('click', () => {
            tabButtons.forEach(btn => btn.classList.remove('active'));
            button.classList.add('active');
        });
    });

    // User Dropdown Functionality
    const userDropdown = document.querySelector('.user-dropdown');
    if (userDropdown) {
        const profileBtn = userDropdown.querySelector('.user-profile-btn');
        const dropdownContent = userDropdown.querySelector('.dropdown-content');

        // Toggle dropdown on click
        profileBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            const isOpen = dropdownContent.style.display === 'block';
            dropdownContent.style.display = isOpen ? 'none' : 'block';
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function (e) {
            if (!userDropdown.contains(e.target)) {
                dropdownContent.style.display = 'none';
            }
        });

        // Close dropdown on mobile when clicking a link
        dropdownContent.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', function () {
                if (window.innerWidth <= 1150) {
                    dropdownContent.style.display = 'none';
                    if (navMenu) navMenu.classList.remove('active');
                    if (navActions) navActions.style.transform = 'translateY(-150%)';
                }
            });
        });
    }

    // Responsive adjustments
    function handleResponsive() {
        const userDropdown = document.querySelector('.user-dropdown');
        if (userDropdown) {
            const dropdownContent = userDropdown.querySelector('.dropdown-content');

            if (window.innerWidth <= 1150) {
                // Mobile view
                dropdownContent.style.position = 'relative';
                dropdownContent.style.width = '100%';
                dropdownContent.style.boxShadow = 'none';
            } else {
                // Desktop view
                dropdownContent.style.position = 'absolute';
                dropdownContent.style.width = 'auto';
                dropdownContent.style.boxShadow = '0 8px 16px rgba(0, 0, 0, 0.1)';
            }
        }
    }

    // Run on load and resize
    handleResponsive();
    window.addEventListener('resize', handleResponsive);
});

// In your navbar.js, add this to handle role-based items
document.querySelectorAll('.nav-menu a, .dropdown-content a').forEach(link => {
    if (link.href.includes('/staff/') || link.href.includes('/admin/')) {
        link.addEventListener('click', function (e) {
            // Add any special handling for staff/admin links if needed
            console.log('Accessing staff/admin feature:', this.textContent);
        });
    }
});