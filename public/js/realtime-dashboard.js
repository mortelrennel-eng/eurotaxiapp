// Real-time Dashboard Updates
class RealTimeDashboard {
    constructor() {
        this.updateInterval = 30000; // Update every 30 seconds
        this.init();
    }

    init() {
        // Start real-time updates
        this.startRealTimeUpdates();
        
        // Add last updated indicator
        this.addLastUpdatedIndicator();
        
        // Setup WebSocket connection if available
        this.setupWebSocket();
    }

    startRealTimeUpdates() {
        // Update dashboard data every 30 seconds
        setInterval(() => {
            this.updateDashboardData();
        }, this.updateInterval);

        // Also update on page visibility change (when user returns to tab)
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) {
                this.updateDashboardData();
            }
        });
    }

    async updateDashboardData() {
        try {
            // Fetch latest dashboard data
            const response = await fetch('/api/dashboard/realtime');
            const data = await response.json();
            
            if (data.success) {
                this.updateStats(data.stats);
                this.updateCharts(data.charts);
                this.updateAlerts(data.alerts);
                this.updateLastUpdatedTime();
            }
        } catch (error) {
            console.error('Error updating dashboard:', error);
        }
    }

    updateStats(stats) {
        // Update stat cards with animation
        const updates = [
            { selector: '[data-stat="active_units"]', value: stats.active_units },
            { selector: '[data-stat="today_boundary"]', value: this.formatCurrency(stats.today_boundary) },
            { selector: '[data-stat="net_income"]', value: this.formatCurrency(stats.net_income) },
            { selector: '[data-stat="maintenance_units"]', value: stats.maintenance_units },
            { selector: '[data-stat="active_drivers"]', value: stats.active_drivers },
            { selector: '[data-stat="avg_boundary"]', value: this.formatCurrency(stats.avg_boundary) },
            { selector: '[data-stat="coding_units"]', value: stats.coding_units }
        ];

        updates.forEach(update => {
            const element = document.querySelector(update.selector);
            if (element) {
                this.animateValue(element, update.value);
            }
        });
    }

    updateCharts(chartData) {
        if (!chartData) return;

        // Helper to check if a global variable is a valid Chart instance
        const isValidChart = (chart) => chart && typeof chart.update === 'function' && chart.data && chart.data.datasets;

        // Update weekly financial chart
        if (isValidChart(window.weeklyChart) && chartData.weekly_data) {
            window.weeklyChart.data.datasets[0].data = chartData.weekly_data.map(d => d.boundary);
            window.weeklyChart.data.datasets[1].data = chartData.weekly_data.map(d => d.expenses);
            window.weeklyChart.data.datasets[2].data = chartData.weekly_data.map(d => d.net);
            window.weeklyChart.update('none');
        }

        // Update unit status chart
        if (isValidChart(window.unitStatusChart) && chartData.unit_status_data) {
            window.unitStatusChart.data.datasets[0].data = chartData.unit_status_data.map(d => d.count);
            window.unitStatusChart.update('none');
        }

        // Update revenue trend chart
        if (isValidChart(window.revenueTrendChart) && chartData.revenue_trend) {
            window.revenueTrendChart.data.datasets[0].data = chartData.revenue_trend.map(d => d.revenue);
            window.revenueTrendChart.update('none');
        }

        // Update unit performance chart
        if (isValidChart(window.unitPerformanceChart) && chartData.unit_performance) {
            window.unitPerformanceChart.data.datasets[0].data = chartData.unit_performance.map(d => d.performance);
            window.unitPerformanceChart.data.datasets[1].data = chartData.unit_performance.map(d => d.target);
            window.unitPerformanceChart.update('none');
        }

        // Update expense breakdown chart
        if (isValidChart(window.expenseBreakdownChart) && chartData.expense_breakdown) {
            window.expenseBreakdownChart.data.datasets[0].data = chartData.expense_breakdown.map(d => d.amount);
            window.expenseBreakdownChart.data.labels = chartData.expense_breakdown.map(d => d.category);
            window.expenseBreakdownChart.update('none');
        }
    }

    updateAlerts(alerts) {
        const alertsContainer = document.querySelector('[data-alerts-container]');
        if (!alertsContainer) return;

        if (alerts.length === 0) {
            alertsContainer.innerHTML = '<p class="text-gray-500 text-center py-4">No active alerts</p>';
            return;
        }

        const alertsHtml = alerts.map(alert => `
            <div class="flex items-start gap-3 p-3 rounded-lg border ${this.getAlertClass(alert.severity)}">
                <div class="mt-0.5">
                    ${this.getAlertIcon(alert.severity)}
                </div>
                <div class="flex-1">
                    <p class="text-sm text-gray-900">${alert.message}</p>
                    <span class="text-xs text-gray-500 capitalize">${alert.alert_type}</span>
                </div>
            </div>
        `).join('');

        alertsContainer.innerHTML = `<div class="space-y-3">${alertsHtml}</div>`;
    }

    getAlertClass(severity) {
        const classes = {
            'high': 'bg-red-50 border-red-200',
            'critical': 'bg-red-50 border-red-200',
            'medium': 'bg-yellow-50 border-yellow-200',
            'low': 'bg-blue-50 border-blue-200'
        };
        return classes[severity] || 'bg-gray-50 border-gray-200';
    }

    getAlertIcon(severity) {
        if (['high', 'critical'].includes(severity)) {
            return '<i data-lucide="alert-triangle" class="w-5 h-5 text-red-600"></i>';
        } else if (severity === 'medium') {
            return '<i data-lucide="alert-triangle" class="w-5 h-5 text-yellow-600"></i>';
        } else {
            return '<i data-lucide="info" class="w-5 h-5 text-blue-600"></i>';
        }
    }

    animateValue(element, newValue) {
        if (element.textContent === newValue) return;
        
        element.style.transition = 'color 0.3s';
        element.style.color = '#22c55e'; // Green flash
        
        setTimeout(() => {
            element.textContent = newValue;
            element.style.color = '';
            
            // Re-initialize lucide icons if needed
            if (window.lucide) {
                window.lucide.createIcons();
                // Ensure code icon is available
                if (!document.querySelector('[data-lucide="code"]')) {
                    const codeIcon = document.createElement('i');
                    codeIcon.setAttribute('data-lucide', 'code');
                    codeIcon.className = 'w-8 h-8 text-purple-600';
                    window.lucide.createIcons();
                }
            }
        }, 150);
    }

    formatCurrency(value) {
        return new Intl.NumberFormat('en-PH', {
            style: 'currency',
            currency: 'PHP',
            minimumFractionDigits: 0
        }).format(value);
    }

    addLastUpdatedIndicator() {
        const indicator = document.createElement('div');
        indicator.className = 'fixed bottom-4 right-4 bg-gray-800 text-white text-xs px-3 py-2 rounded-lg shadow-lg z-50';
        indicator.innerHTML = `
            <div class="flex items-center gap-2">
                <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                <span>Last updated: <span id="lastUpdated">Just now</span></span>
            </div>
        `;
        document.body.appendChild(indicator);
    }

    updateLastUpdatedTime() {
        const lastUpdatedElement = document.getElementById('lastUpdated');
        if (lastUpdatedElement) {
            lastUpdatedElement.textContent = new Date().toLocaleTimeString();
        }
    }

    setupWebSocket() {
        // WebSocket setup for real-time updates (optional)
        if (typeof io !== 'undefined') {
            const socket = io();
            
            socket.on('dashboard:update', (data) => {
                this.updateStats(data.stats);
                this.updateCharts(data.charts);
                this.updateAlerts(data.alerts);
                this.updateLastUpdatedTime();
            });
        }
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.realTimeDashboard = new RealTimeDashboard();
});

// Make charts globally accessible for updates
window.weeklyChart = null;
window.unitStatusChart = null;

// Store chart instances when created
document.addEventListener('DOMContentLoaded', () => {
    // Wait for Chart.js to initialize
    setTimeout(() => {
        if (typeof Chart !== 'undefined') {
            Chart.helpers.each(Chart.instances, (instance) => {
                if (instance.canvas.id === 'weeklyChart') {
                    window.weeklyChart = instance;
                } else if (instance.canvas.id === 'unitStatusChart') {
                    window.unitStatusChart = instance;
                } else if (instance.canvas.id === 'revenueTrendChart') {
                    window.revenueTrendChart = instance;
                } else if (instance.canvas.id === 'unitPerformanceChart') {
                    window.unitPerformanceChart = instance;
                } else if (instance.canvas.id === 'expenseBreakdownChart') {
                    window.expenseBreakdownChart = instance;
                }
            });
        }
    }, 1000);
});
