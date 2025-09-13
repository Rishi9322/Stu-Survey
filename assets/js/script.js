// Modern JavaScript for EduSurvey Pro

// Initialize everything when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    initializeModernFeatures();
    initializeAnimations();
    initializeFormHandlers();
    initializeCharts();
    initializeDataTables();
});

// Modern feature initialization
function initializeModernFeatures() {
    // Header scroll effect
    const header = document.getElementById('header');
    if (header) {
        window.addEventListener('scroll', throttle(() => {
            if (window.scrollY > 100) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        }, 10));
    }

    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // Mobile menu toggle
    const navbarToggler = document.querySelector('.navbar-toggler');
    const navbarCollapse = document.querySelector('.navbar-collapse');
    
    if (navbarToggler && navbarCollapse) {
        navbarToggler.addEventListener('click', function() {
            navbarCollapse.classList.toggle('show');
        });

        // Close menu when clicking outside
        document.addEventListener('click', function(e) {
            if (!navbarToggler.contains(e.target) && !navbarCollapse.contains(e.target)) {
                navbarCollapse.classList.remove('show');
            }
        });
    }

    // Enhanced button interactions
    document.querySelectorAll('.btn').forEach(button => {
        button.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px)';
        });
        
        button.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
}

// Throttle function for performance
function throttle(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Animation initialization
function initializeAnimations() {
    // Intersection Observer for fade-in animations
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-fade-in-up');
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);

    // Observe elements for animation
    document.querySelectorAll('.feature-card, .testimonial-card, .stat-card').forEach(el => {
        observer.observe(el);
    });

    // Parallax effect for hero particles
    const heroParticles = document.querySelector('.hero-particles');
    if (heroParticles) {
        window.addEventListener('scroll', throttle(() => {
            const scrolled = window.pageYOffset;
            const rate = scrolled * -0.5;
            heroParticles.style.transform = `translateY(${rate}px)`;
        }, 10));
    }

    // Counter animation for statistics
    animateCounters();
}

// Form handlers
function initializeFormHandlers() {
    // Role selection in login and registration forms
    const roleOptions = document.querySelectorAll('.role-option');
    const additionalFields = document.querySelectorAll('.additional-fields');
    
    if (roleOptions.length > 0) {
        roleOptions.forEach(option => {
            option.addEventListener('click', function() {
                // Remove active class from all options
                roleOptions.forEach(opt => opt.classList.remove('active'));
                
                // Add active class to clicked option
                this.classList.add('active');
                
                // Set the hidden input value
                const roleInput = document.getElementById('role');
                if (roleInput) {
                    roleInput.value = this.getAttribute('data-role');
                }
                
                // Hide all additional fields
                if (additionalFields && additionalFields.length > 0) {
                    additionalFields.forEach(field => field.classList.remove('active'));
                    
                    // Show the relevant additional fields
                    const role = this.getAttribute('data-role');
                    if (role !== 'admin') {
                        const fieldsElement = document.getElementById(`${role}-fields`);
                        if (fieldsElement) {
                            fieldsElement.classList.add('active');
                        }
                    }
                }
            });
        });
    }
    
    // Enhanced form validation
    const forms = document.querySelectorAll('.needs-validation');
    
    if (forms.length > 0) {
        Array.from(forms).forEach(form => {
            form.addEventListener('submit', event => {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                    
                    // Add shake animation to invalid form
                    form.classList.add('shake');
                    setTimeout(() => form.classList.remove('shake'), 500);
                }
                
                form.classList.add('was-validated');
            }, false);
        });
    }

    // Real-time form validation
    document.querySelectorAll('.form-control').forEach(input => {
        input.addEventListener('blur', function() {
            validateField(this);
        });
        
        input.addEventListener('input', function() {
            if (this.classList.contains('is-invalid')) {
                validateField(this);
            }
        });
    });

    // Survey form submit confirmation
    const surveyForm = document.getElementById('survey-form');
    
    if (surveyForm) {
        surveyForm.addEventListener('submit', function(event) {
            const confirmed = confirm('Are you sure you want to submit this survey? You won\'t be able to change your answers later.');
            
            if (!confirmed) {
                event.preventDefault();
            } else {
                // Show loading state
                const submitBtn = this.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Submitting...';
                    submitBtn.disabled = true;
                }
            }
        });
    }

    // Password visibility toggle
    document.querySelectorAll('.password-toggle').forEach(toggle => {
        toggle.addEventListener('click', function() {
            const input = this.previousElementSibling;
            const icon = this.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        });
    });
}

// Field validation function
function validateField(field) {
    const value = field.value.trim();
    const type = field.type;
    let isValid = true;
    let message = '';

    // Remove existing validation classes
    field.classList.remove('is-valid', 'is-invalid');

    // Check if field is required and empty
    if (field.hasAttribute('required') && !value) {
        isValid = false;
        message = 'This field is required.';
    }
    // Email validation
    else if (type === 'email' && value) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(value)) {
            isValid = false;
            message = 'Please enter a valid email address.';
        }
    }
    // Password validation
    else if (field.name === 'password' && value) {
        if (value.length < 6) {
            isValid = false;
            message = 'Password must be at least 6 characters long.';
        }
    }
    // Confirm password validation
    else if (field.name === 'confirm_password' && value) {
        const passwordField = document.querySelector('input[name="password"]');
        if (passwordField && value !== passwordField.value) {
            isValid = false;
            message = 'Passwords do not match.';
        }
    }

    // Apply validation result
    field.classList.add(isValid ? 'is-valid' : 'is-invalid');
    
    // Update feedback message
    const feedback = field.parentNode.querySelector('.invalid-feedback');
    if (feedback && !isValid) {
        feedback.textContent = message;
    }

    return isValid;
}

// Counter animation
function animateCounters() {
    const counters = document.querySelectorAll('.stat-number');
    
    counters.forEach(counter => {
        const target = parseInt(counter.textContent.replace(/[^\d]/g, ''));
        const duration = 2000; // 2 seconds
        const step = target / (duration / 16); // 60fps
        let current = 0;
        
        const updateCounter = () => {
            current += step;
            if (current < target) {
                counter.textContent = Math.floor(current).toLocaleString();
                requestAnimationFrame(updateCounter);
            } else {
                counter.textContent = target.toLocaleString() + (counter.textContent.includes('%') ? '%' : '') + (counter.textContent.includes('+') ? '+' : '');
            }
        };
        
        // Start animation when element is visible
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    updateCounter();
                    observer.unobserve(entry.target);
                }
            });
        });
        
        observer.observe(counter);
    });
}

// Throttle function for performance
function throttle(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Animation initialization
function initializeAnimations() {
    // Intersection Observer for fade-in animations
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-fade-in-up');
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);

    // Observe elements for animation
    document.querySelectorAll('.feature-card, .testimonial-card, .stat-card').forEach(el => {
        observer.observe(el);
    });

    // Counter animations
    const counters = document.querySelectorAll('.counter');
    counters.forEach(counter => {
        const target = parseInt(counter.getAttribute('data-target'));
        const increment = target / 50;
        let current = 0;

        const updateCounter = () => {
            if (current < target) {
                counter.textContent = Math.ceil(current);
                current += increment;
                requestAnimationFrame(updateCounter);
            } else {
                counter.textContent = target;
            }
        };

        // Start animation when element is visible
        const counterObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    updateCounter();
                    counterObserver.unobserve(entry.target);
                }
            });
        });

        counterObserver.observe(counter);
    });
}

// Form handlers initialization
function initializeFormHandlers() {
    // Enhanced form validation
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!validateForm(this)) {
                e.preventDefault();
                return false;
            }
        });
    });
}

// Form validation function
function validateForm(form) {
    let isValid = true;
    const inputs = form.querySelectorAll('input[required], textarea[required], select[required]');
    
    inputs.forEach(input => {
        if (!validateField(input)) {
            isValid = false;
        }
    });

    return isValid;
}

// Field validation function
function validateField(field) {
    const value = field.value.trim();
    let isValid = true;
    let message = '';

    // Remove existing error states
    field.classList.remove('is-invalid');
    const existingError = field.parentNode.querySelector('.invalid-feedback');
    if (existingError) {
        existingError.remove();
    }

    // Required field validation
    if (field.hasAttribute('required') && value === '') {
        isValid = false;
        message = 'This field is required';
    }

    // Show error if invalid
    if (!isValid) {
        field.classList.add('is-invalid');
        const errorDiv = document.createElement('div');
        errorDiv.className = 'invalid-feedback';
        errorDiv.textContent = message;
        field.parentNode.appendChild(errorDiv);
    }

    return isValid;
}

// DataTables initialization
function initializeDataTables() {
    // Check if DataTables library is loaded
    if (typeof $.fn.DataTable === 'undefined') {
        return;
    }

    // Initialize all tables with data-table class
    $('.data-table').each(function() {
        $(this).DataTable({
            responsive: true,
            pageLength: 25,
            order: [[0, 'asc']]
        });
    });
}

// Sort table function (for non-DataTable tables)
function sortTable(table, column, asc = true) {
    const tbody = table.querySelector('tbody');
    if (!tbody) return;
    
    const rows = Array.from(tbody.querySelectorAll('tr'));
    
    // Sort the rows
    const sortedRows = rows.sort((a, b) => {
        const aCol = a.querySelectorAll('td')[column].textContent.trim();
        const bCol = b.querySelectorAll('td')[column].textContent.trim();
        
        // Check if the columns contain numbers
        const aNum = parseFloat(aCol);
        const bNum = parseFloat(bCol);
        
        if (!isNaN(aNum) && !isNaN(bNum)) {
            return asc ? aNum - bNum : bNum - aNum;
        } else {
            return asc ? aCol.localeCompare(bCol) : bCol.localeCompare(aCol);
        }
    });
    
    // Remove existing rows
    while (tbody.firstChild) {
        tbody.removeChild(tbody.firstChild);
    }
    
    // Add sorted rows
    tbody.append(...sortedRows);
}

// Function to initialize charts using Chart.js
function initializeCharts() {
    // Check if Chart.js is loaded and if chart containers exist
    if (typeof Chart === 'undefined' || !document.querySelector('.chart-container')) {
        return;
    }
    
    // Rating distribution chart
    const ratingChartElement = document.getElementById('rating-distribution-chart');
    
    if (ratingChartElement) {
        const ratingLabels = ['Bad', 'Neutral', 'Good'];
        const ratingData = JSON.parse(ratingChartElement.getAttribute('data-ratings'));
        
        new Chart(ratingChartElement, {
            type: 'pie',
            data: {
                labels: ratingLabels,
                datasets: [{
                    data: ratingData,
                    backgroundColor: ['#e74c3c', '#f39c12', '#2ecc71']
                }]
            },
            options: {
                responsive: true,
                legend: {
                    position: 'bottom'
                },
                title: {
                    display: true,
                    text: 'Rating Distribution'
                }
            }
        });
    }
    
    // Survey completion chart
    const completionChartElement = document.getElementById('survey-completion-chart');
    
    if (completionChartElement) {
        const completionData = JSON.parse(completionChartElement.getAttribute('data-completion'));
        
        new Chart(completionChartElement, {
            type: 'bar',
            data: {
                labels: ['Students', 'Teachers'],
                datasets: [{
                    label: 'Completed',
                    data: [completionData.students_completed, completionData.teachers_completed],
                    backgroundColor: '#2ecc71'
                }, {
                    label: 'Total',
                    data: [completionData.total_students, completionData.total_teachers],
                    backgroundColor: '#3498db'
                }]
            },
            options: {
                responsive: true,
                legend: {
                    position: 'bottom'
                },
                title: {
                    display: true,
                    text: 'Survey Completion Status'
                },
                scales: {
                    yAxes: [{
                        ticks: {
                            beginAtZero: true
                        }
                    }]
                }
            }
        });
    }
    
    // Teacher ratings chart
    const teacherRatingChartElement = document.getElementById('teacher-ratings-chart');
    
    if (teacherRatingChartElement) {
        const teacherData = JSON.parse(teacherRatingChartElement.getAttribute('data-teachers'));
        
        new Chart(teacherRatingChartElement, {
            type: 'horizontalBar',
            data: {
                labels: teacherData.names,
                datasets: [{
                    label: 'Average Rating',
                    data: teacherData.ratings,
                    backgroundColor: '#3498db'
                }]
            },
            options: {
                responsive: true,
                legend: {
                    position: 'bottom'
                },
                title: {
                    display: true,
                    text: 'Teacher Ratings'
                },
                scales: {
                    xAxes: [{
                        ticks: {
                            beginAtZero: true,
                            max: 3,
                            callback: function(value) {
                                if (value === 0) return 'Bad';
                                if (value === 1) return 'Neutral';
                                if (value === 2) return 'Good';
                                if (value === 3) return 'Excellent';
                                return value;
                            }
                        }
                    }]
                }
            }
        });
    }
}

// Function to export table data to CSV
function exportTableToCSV(tableId, filename) {
    const table = document.getElementById(tableId);
    let csv = [];
    const rows = table.querySelectorAll('tr');
    
    for (let i = 0; i < rows.length; i++) {
        const row = [], cols = rows[i].querySelectorAll('td, th');
        
        for (let j = 0; j < cols.length; j++) {
            // Replace commas with semicolons to avoid CSV conflicts
            let text = cols[j].innerText.replace(/,/g, ';');
            // Wrap in quotes if contains semicolons
            if (text.includes(';')) {
                text = `"${text}"`;
            }
            row.push(text);
        }
        
        csv.push(row.join(','));
    }
    
    // Download CSV file
    downloadCSV(csv.join('\n'), filename);
}

// Function to download CSV
function downloadCSV(csv, filename) {
    const csvFile = new Blob([csv], {type: 'text/csv'});
    const downloadLink = document.createElement('a');
    
    downloadLink.download = filename;
    downloadLink.href = window.URL.createObjectURL(csvFile);
    downloadLink.style.display = 'none';
    
    document.body.appendChild(downloadLink);
    downloadLink.click();
    document.body.removeChild(downloadLink);
}

// Function to toggle password visibility
function togglePasswordVisibility(inputId, toggleButtonId) {
    const passwordInput = document.getElementById(inputId);
    const toggleButton = document.getElementById(toggleButtonId);
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleButton.innerHTML = '<i class="fas fa-eye-slash"></i>';
    } else {
        passwordInput.type = 'password';
        toggleButton.innerHTML = '<i class="fas fa-eye"></i>';
    }
}

// Function to handle dynamic form fields
function addFormField(containerId, fieldTemplate) {
    const container = document.getElementById(containerId);
    const fieldCount = container.getElementsByClassName('dynamic-field').length;
    const newField = document.createElement('div');
    
    newField.className = 'dynamic-field form-group';
    newField.innerHTML = fieldTemplate.replace(/\{index\}/g, fieldCount);
    
    container.appendChild(newField);
}

function removeFormField(button) {
    const field = button.closest('.dynamic-field');
    field.parentNode.removeChild(field);
}

// Function to filter table rows
function filterTable(inputId, tableId) {
    const input = document.getElementById(inputId);
    const filter = input.value.toUpperCase();
    const table = document.getElementById(tableId);
    const rows = table.getElementsByTagName('tr');
    
    for (let i = 1; i < rows.length; i++) {
        let visible = false;
        const cells = rows[i].getElementsByTagName('td');
        
        for (let j = 0; j < cells.length; j++) {
            const cell = cells[j];
            if (cell) {
                const text = cell.textContent || cell.innerText;
                if (text.toUpperCase().indexOf(filter) > -1) {
                    visible = true;
                    break;
                }
            }
        }
        
        rows[i].style.display = visible ? '' : 'none';
    }
}

// Function to preview image before upload
function previewImage(input, previewId) {
    const preview = document.getElementById(previewId);
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
        };
        
        reader.readAsDataURL(input.files[0]);
    }
}
