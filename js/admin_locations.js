/**
 * Admin Locations List JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {
    initializeFilters();
});

/**
 * Initialize filter functionality
 */
function initializeFilters() {
    const typeFilter = document.getElementById('type-filter');
    const statusFilter = document.getElementById('status-filter');
    const searchInput = document.getElementById('search');
    
    typeFilter.addEventListener('change', filterLocations);
    statusFilter.addEventListener('change', filterLocations);
    searchInput.addEventListener('input', filterLocations);
}

/**
 * Filter locations based on selected criteria
 */
function filterLocations() {
    const typeFilter = document.getElementById('type-filter').value.toLowerCase();
    const statusFilter = document.getElementById('status-filter').value.toLowerCase();
    const searchTerm = document.getElementById('search').value.toLowerCase();
    
    document.querySelectorAll('.location-card').forEach(card => {
        const cardType = card.dataset.type.toLowerCase();
        const cardStatus = card.dataset.status.toLowerCase();
        const cardText = card.textContent.toLowerCase();
        
        const typeMatch = typeFilter === 'all' || cardType === typeFilter;
        const statusMatch = statusFilter === 'all' || cardStatus === statusFilter;
        const searchMatch = cardText.includes(searchTerm);
        
        card.style.display = (typeMatch && statusMatch && searchMatch) ? 'block' : 'none';
    });
}

/**
 * View location details
 */
function viewLocation(locationId) {
    window.location.href = `view_location.php?id=${locationId}`;
}

/**
 * Delete location
 */
async function deleteLocation(locationId, locationName) {
    if (!confirm(`Are you sure you want to delete "${locationName}"?\n\nThis action cannot be undone.`)) {
        return;
    }
    
    try {
        const response = await fetch('api_delete_location.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ location_id: locationId })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification('Location deleted successfully', 'success');
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            showNotification(data.message || 'Failed to delete location', 'error');
        }
    } catch (error) {
        console.error('Error deleting location:', error);
        showNotification('Error deleting location', 'error');
    }
}

/**
 * Show notification
 */
function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.textContent = message;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 20px;
        border-radius: 6px;
        color: white;
        font-weight: bold;
        z-index: 9999;
        animation: slideIn 0.3s;
        background: ${type === 'success' ? '#4CAF50' : '#f44336'};
    `;
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 3000);
}

