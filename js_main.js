// Global Variables
const baseUrl = '/ParkNova';

// --- THEME TOGGLE LOGIC ---
const initTheme = () => {
    // Check local storage or OS preference setup immediately avoid flicker
    const savedTheme = localStorage.getItem('parknova_theme');
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

    if (savedTheme === 'dark' || (!savedTheme && prefersDark)) {
        document.documentElement.setAttribute('data-theme', 'dark');
    } else {
        document.documentElement.setAttribute('data-theme', 'light');
    }
};
initTheme();

document.addEventListener('DOMContentLoaded', function () {
    // Theme Switcher Button Binding
    const themeToggleBtn = document.getElementById('themeToggleBtn');
    if (themeToggleBtn) {
        // Function to update icon based on current theme
        const updateIcon = (theme) => {
            const isDark = theme === 'dark';
            // Simple Font Awesome icons: moon for light mode (to switch to dark), sun for dark mode (to switch to light)
            themeToggleBtn.innerHTML = isDark ? '<i class="fa-solid fa-sun"></i>' : '<i class="fa-solid fa-moon"></i>';
            themeToggleBtn.title = isDark ? "Switch to Light Mode" : "Switch to Dark Mode";
        };

        // Initial icon state
        const initialTheme = document.documentElement.getAttribute('data-theme') || 'light';
        updateIcon(initialTheme);

        themeToggleBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const activeTheme = document.documentElement.getAttribute('data-theme');
            const newTheme = activeTheme === 'dark' ? 'light' : 'dark';

            document.documentElement.setAttribute('data-theme', newTheme);
            localStorage.setItem('parknova_theme', newTheme);
            updateIcon(newTheme);
            
            // Subtle click feedback
            this.style.transform = 'scale(0.9)';
            setTimeout(() => { this.style.transform = ''; }, 100);
        });
    }


    // 1. Initialize Tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // 2. Scroll Animation observer
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
            }
        });
    }, { threshold: 0.1 });

    document.querySelectorAll('.animate-up').forEach((el) => {
        observer.observe(el);
    });

    // 3. Search Parking AJAX
    const searchForm = document.getElementById('searchForm');
    const searchInput = document.getElementById('searchInput');
    const searchResults = document.getElementById('searchResults');
    let searchTimeout;

    if (searchInput && searchResults) {
        const fetchResults = () => {
            const query = searchInput.value.trim();
            searchResults.innerHTML = '<div class="col-12 text-center py-5"><div class="spinner-border text-primary" role="status"></div></div>';

            fetch(`${baseUrl}/ajax_get_parking.php?q=${encodeURIComponent(query)}`)
                .then(response => response.text())
                .then(html => {
                    searchResults.innerHTML = html;
                })
                .catch(error => {
                    searchResults.innerHTML = '<div class="col-12"><div class="alert alert-danger">Error loading results.</div></div>';
                });
        };

        // Live typing
        searchInput.addEventListener('input', function () {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(fetchResults, 400); // 400ms debounce
        });

        // Form submit
        if (searchForm) {
            searchForm.addEventListener('submit', function (e) {
                e.preventDefault();
                clearTimeout(searchTimeout);
                fetchResults();
            });
        }

        // Initial load
        fetchResults();
    }

    // 4. Booking Calculation Logic
    const bookingForm = document.getElementById('bookingForm');
    if (bookingForm) {
        const entryTime = document.getElementById('entry_time');
        const exitTime = document.getElementById('exit_time');
        const priceElement = document.getElementById('totalPriceStr');
        const submitBtn = document.getElementById('bookBtn');
        const pph = parseFloat(priceElement ? priceElement.dataset.price : 0);

        const calculateTotal = () => {
            if (entryTime.value && exitTime.value) {
                const entry = new Date(`2000-01-01T${entryTime.value}`);
                const exit = new Date(`2000-01-01T${exitTime.value}`);

                let diff = (exit - entry) / (1000 * 60 * 60); // hours

                // If exit is on next day (e.g. 23:00 to 02:00)
                if (diff <= 0) {
                    diff += 24;
                }

                if (diff > 0) {
                    const total = diff * pph;
                    priceElement.innerHTML = `₹${total.toFixed(2)}`;
                    submitBtn.disabled = false;
                } else {
                    priceElement.innerHTML = "₹0.00";
                    submitBtn.disabled = true;
                }
            }
        };

        entryTime.addEventListener('change', calculateTotal);
        exitTime.addEventListener('change', calculateTotal);
    }

    // 5. Bootstrap Custom Form Validations
    const forms = document.querySelectorAll('.needs-validation');
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();

                // Find first invalid element and focus it for UX
                const firstInvalid = form.querySelector(':invalid');
                if (firstInvalid) {
                    firstInvalid.focus();
                }
            }
            form.classList.add('was-validated');
        }, false);
    });

    // 6. Real-time Password Matching Validation (Registration/Profile)
    const pwdInput = document.getElementById('password');
    const confirmPwdInput = document.getElementById('confirm_password');

    if (pwdInput && confirmPwdInput) {
        const checkMatch = () => {
            if (confirmPwdInput.value !== pwdInput.value && confirmPwdInput.value.length > 0) {
                confirmPwdInput.setCustomValidity("Passwords do not match.");
            } else {
                confirmPwdInput.setCustomValidity("");
            }
        };
        pwdInput.addEventListener('input', checkMatch);
        confirmPwdInput.addEventListener('input', checkMatch);
    }
});


