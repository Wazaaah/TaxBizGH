// assets/js/dashboard.js

document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    $('[data-toggle="tooltip"]').tooltip();

    // Tax Tips Rotation
    const tips = [
        "Remember to keep all your receipts for tax deductions!",
        "File your tax returns before the deadline to avoid penalties.",
        "Consider consulting a tax professional for complex transactions.",
        "Stay updated with the latest tax regulations and changes.",
        "Maintain separate business and personal accounts for better tracking."
    ];

    let currentTipIndex = 0;
    const tipElement = document.querySelector('.tip-item p');

    function rotateTips() {
        tipElement.style.opacity = '0';
        setTimeout(() => {
            currentTipIndex = (currentTipIndex + 1) % tips.length;
            tipElement.textContent = tips[currentTipIndex];
            tipElement.style.opacity = '1';
        }, 500);
    }

    // Rotate tips every 5 seconds
    setInterval(rotateTips, 5000);

    // Add click handlers for action buttons
    document.querySelectorAll('.btn-info').forEach(btn => {
        btn.addEventListener('click', function(e) {
            const id = this.closest('a').getAttribute('href').split('=')[1];
            viewCalculationDetails(id);
        });
    });

    document.querySelectorAll('.btn-success').forEach(btn => {
        btn.addEventListener('click', function(e) {
            const id = this.closest('a').getAttribute('href').split('=')[1];
            downloadCalculation(id);
        });
    });
});

// Function to view calculation details
function viewCalculationDetails(id) {
    window.location.href = `view_calculation.php?id=${id}`;
}

// Function to download calculation
function downloadCalculation(id) {
    window.location.href = `download_calculation.php?id=${id}`;
}

// Refresh KPI data
function refreshKPIData() {
    $.ajax({
        url: 'handlers/get_kpi_data.php',
        method: 'GET',
        success: function(response) {
            if(response.success) {
                updateKPIValues(response.data);
            }
        }
    });
}

// Update KPI values
function updateKPIValues(data) {
    const elements = {
        revenue: document.querySelector('.kpi-card:nth-child(1) .kpi-value'),
        expenses: document.querySelector('.kpi-card:nth-child(2) .kpi-value'),
        tax: document.querySelector('.kpi-card:nth-child(3) .kpi-value')
    };

    for (const [key, value] of Object.entries(data)) {
        if (elements[key]) {
            elements[key].textContent = formatCurrency(value);
        }
    }
}

// Format currency
function formatCurrency(amount) {
    return 'GHS ' + new Intl.NumberFormat('en-GH', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(amount);
}