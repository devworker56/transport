// TransportGabon - Custom JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Initialize popovers
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });

    // Form validation
    var forms = document.querySelectorAll('.needs-validation');
    Array.prototype.slice.call(forms).forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });

    // Password confirmation validation
    var password = document.getElementById('password');
    var confirmPassword = document.getElementById('confirm_password');
    
    if (password && confirmPassword) {
        function validatePassword() {
            if (password.value !== confirmPassword.value) {
                confirmPassword.setCustomValidity("Les mots de passe ne correspondent pas");
            } else {
                confirmPassword.setCustomValidity('');
            }
        }
        
        password.onchange = validatePassword;
        confirmPassword.onkeyup = validatePassword;
    }

    // Image preview for file uploads
    document.querySelectorAll('input[type=file]').forEach(function(input) {
        if (input.accept && input.accept.split(',').includes('image/*')) {
            input.addEventListener('change', function(e) {
                var file = e.target.files[0];
                if (file) {
                    var reader = new FileReader();
                    var previewId = input.id + '_preview';
                    var previewElement = document.getElementById(previewId);
                    
                    if (!previewElement) {
                        previewElement = document.createElement('div');
                        previewElement.id = previewId;
                        previewElement.className = 'mt-2';
                        input.parentNode.appendChild(previewElement);
                    }
                    
                    reader.onload = function(e) {
                        previewElement.innerHTML = '<img src="' + e.target.result + '" class="img-thumbnail" style="max-height: 150px;">';
                    };
                    
                    reader.readAsDataURL(file);
                }
            });
        }
    });

    // Auto-dismiss alerts after 5 seconds
    var alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            var bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });

    // Confirm before deleting
    var deleteButtons = document.querySelectorAll('a[href*="action=delete"], .btn-delete');
    deleteButtons.forEach(function(button) {
        button.addEventListener('click', function(e) {
            if (!confirm('Êtes-vous sûr de vouloir effectuer cette action? Cette action est irréversible.')) {
                e.preventDefault();
            }
        });
    });

    // Dynamic province selection for forms
    var provinceSelects = document.querySelectorAll('select[name="province"]');
    var gabonProvinces = [
        'Estuaire', 'Haut-Ogooué', 'Moyen-Ogooué', 'Ngounié', 
        'Nyanga', 'Ogooué-Ivindo', 'Ogooué-Lolo', 'Ogooué-Maritime', 'Woleu-Ntem'
    ];

    provinceSelects.forEach(function(select) {
        // Only populate if empty
        if (select.options.length <= 1) {
            gabonProvinces.forEach(function(province) {
                var option = document.createElement('option');
                option.value = province;
                option.textContent = province;
                select.appendChild(option);
            });
        }
    });

    // Vehicle type selection
    var vehicleTypeSelects = document.querySelectorAll('select[name="vehicle_type"]');
    var vehicleTypes = [
        {value: 'pickup', text: 'Pickup'},
        {value: 'van', text: 'Van'},
        {value: 'truck', text: 'Camion'},
        {value: 'other', text: 'Autre'}
    ];

    vehicleTypeSelects.forEach(function(select) {
        // Only populate if empty
        if (select.options.length <= 1) {
            vehicleTypes.forEach(function(type) {
                var option = document.createElement('option');
                option.value = type.value;
                option.textContent = type.text;
                select.appendChild(option);
            });
        }
    });

    // Calculate delivery cost estimate
    var calculateCostButton = document.getElementById('calculate-cost');
    if (calculateCostButton) {
        calculateCostButton.addEventListener('click', function() {
            var province = document.getElementById('province').value;
            var weight = parseFloat(document.getElementById('weight_kg').value) || 0;
            
            if (!province) {
                alert('Veuillez sélectionner une province d\'abord');
                return;
            }
            
            // Base rates by province (example rates)
            var baseRates = {
                'Estuaire': 15000,
                'Haut-Ogooué': 45000,
                'Moyen-Ogooué': 30000,
                'Ngounié': 40000,
                'Nyanga': 50000,
                'Ogooué-Ivindo': 55000,
                'Ogooué-Lolo': 45000,
                'Ogooué-Maritime': 35000,
                'Woleu-Ntem': 60000
            };
            
            var baseRate = baseRates[province] || 30000;
            var weightRate = weight * 500; // 500 FCFA per kg
            var estimatedCost = baseRate + weightRate;
            
            document.getElementById('estimated_cost').value = Math.round(estimatedCost / 1000) * 1000;
            document.getElementById('cost_estimate').classList.remove('d-none');
        });
    }

    // Table sorting functionality
    var sortableTables = document.querySelectorAll('table.sortable');
    sortableTables.forEach(function(table) {
        var headers = table.querySelectorAll('th[data-sort]');
        headers.forEach(function(header, index) {
            header.style.cursor = 'pointer';
            header.addEventListener('click', function() {
                sortTable(table, index, header.getAttribute('data-sort'));
            });
        });
    });

    // AJAX functions for dynamic content loading
    window.loadContent = function(url, containerId, callback) {
        var container = document.getElementById(containerId);
        if (!container) return;
        
        container.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Chargement...</span></div></div>';
        
        fetch(url)
            .then(response => response.text())
            .then(data => {
                container.innerHTML = data;
                if (callback && typeof callback === 'function') {
                    callback();
                }
            })
            .catch(error => {
                container.innerHTML = '<div class="alert alert-danger">Erreur de chargement: ' + error + '</div>';
            });
    };

    // Utility function to format numbers with spaces
    window.formatNumber = function(number) {
        return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, " ");
    };

    // Utility function to format dates
    window.formatDate = function(dateString) {
        var options = { year: 'numeric', month: 'long', day: 'numeric' };
        return new Date(dateString).toLocaleDateString('fr-FR', options);
    };

    // Initialize any charts if Chart.js is available
    if (typeof Chart !== 'undefined') {
        initCharts();
    }
});

// Table sorting function
function sortTable(table, column, direction) {
    var tbody = table.querySelector('tbody');
    var rows = Array.from(tbody.querySelectorAll('tr'));
    var isNumeric = !isNaN(parseFloat(rows[0].cells[column].textContent));
    var multiplier = direction === 'asc' ? 1 : -1;
    
    rows.sort(function(a, b) {
        var aValue = a.cells[column].textContent.trim();
        var bValue = b.cells[column].textContent.trim();
        
        if (isNumeric) {
            aValue = parseFloat(aValue) || 0;
            bValue = parseFloat(bValue) || 0;
            return (aValue - bValue) * multiplier;
        } else {
            return aValue.localeCompare(bValue) * multiplier;
        }
    });
    
    // Remove existing rows
    while (tbody.firstChild) {
        tbody.removeChild(tbody.firstChild);
    }
    
    // Add sorted rows
    rows.forEach(function(row) {
        tbody.appendChild(row);
    });
    
    // Update sort indicators
    table.querySelectorAll('th').forEach(function(th, idx) {
        th.classList.remove('sort-asc', 'sort-desc');
        if (idx === column) {
            th.classList.add(direction === 'asc' ? 'sort-asc' : 'sort-desc');
        }
    });
}

// Initialize charts for dashboard
function initCharts() {
    // Driver statistics chart
    var driverStatsCtx = document.getElementById('driverStatsChart');
    if (driverStatsCtx) {
        var driverStatsChart = new Chart(driverStatsCtx, {
            type: 'doughnut',
            data: {
                labels: ['Approuvés', 'En attente', 'Rejetés'],
                datasets: [{
                    data: [driverStatsCtx.getAttribute('data-approved') || 0, 
                           driverStatsCtx.getAttribute('data-pending') || 0, 
                           driverStatsCtx.getAttribute('data-rejected') || 0],
                    backgroundColor: [
                        '#28a745',
                        '#ffc107',
                        '#dc3545'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }
    
    // Monthly deliveries chart
    var monthlyDeliveriesCtx = document.getElementById('monthlyDeliveriesChart');
    if (monthlyDeliveriesCtx) {
        var monthlyDeliveriesChart = new Chart(monthlyDeliveriesCtx, {
            type: 'line',
            data: {
                labels: JSON.parse(monthlyDeliveriesCtx.getAttribute('data-labels') || '[]'),
                datasets: [{
                    label: 'Livraisons mensuelles',
                    data: JSON.parse(monthlyDeliveriesCtx.getAttribute('data-values') || '[]'),
                    backgroundColor: 'rgba(13, 110, 253, 0.2)',
                    borderColor: 'rgba(13, 110, 253, 1)',
                    borderWidth: 2,
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }
}

// Function to update dashboard stats without page reload
function refreshDashboardStats() {
    fetch('../includes/ajax.php?action=dashboard_stats')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.querySelectorAll('[data-stat]').forEach(element => {
                    var stat = element.getAttribute('data-stat');
                    if (data[stat] !== undefined) {
                        element.textContent = data[stat];
                    }
                });
            }
        })
        .catch(error => console.error('Error:', error));
}

// Function to handle file upload with progress
function uploadFileWithProgress(formId, progressBarId, callback) {
    var form = document.getElementById(formId);
    var progressBar = document.getElementById(progressBarId);
    
    if (!form || !progressBar) return;
    
    var formData = new FormData(form);
    var xhr = new XMLHttpRequest();
    
    xhr.upload.addEventListener('progress', function(e) {
        if (e.lengthComputable) {
            var percentComplete = (e.loaded / e.total) * 100;
            progressBar.style.width = percentComplete + '%';
            progressBar.setAttribute('aria-valuenow', percentComplete);
            progressBar.textContent = Math.round(percentComplete) + '%';
        }
    });
    
    xhr.addEventListener('load', function() {
        if (xhr.status === 200) {
            var response = JSON.parse(xhr.responseText);
            if (callback && typeof callback === 'function') {
                callback(response);
            }
        }
    });
    
    xhr.open('POST', form.action);
    xhr.send(formData);
}

// Add these styles for sort indicators
document.head.insertAdjacentHTML('beforeend', `
    <style>
        th.sort-asc::after {
            content: " ▲";
            font-size: 0.8em;
        }
        th.sort-desc::after {
            content: " ▼";
            font-size: 0.8em;
        }
        .cursor-pointer {
            cursor: pointer;
        }
        .fade-in {
            animation: fadeIn 0.5s;
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
    </style>
`);