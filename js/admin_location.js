/**
 * Admin Location Creation/Editing JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {
    initializeForm();
    initializeSupernatualToggle();
    initializeFormSubmit();
});

/**
 * Initialize form
 */
function initializeForm() {
    // If editing, load location data
    const locationId = document.getElementById('location_id').value;
    if (locationId) {
        loadLocationData(locationId);
    }
}

/**
 * Initialize supernatural features toggle
 */
function initializeSupernatualToggle() {
    const checkbox = document.getElementById('has_supernatural');
    const fields = document.getElementById('supernatural-fields');
    
    checkbox.addEventListener('change', function() {
        if (this.checked) {
            fields.style.display = 'block';
        } else {
            fields.style.display = 'none';
            // Clear supernatural fields
            document.getElementById('node_points').value = '';
            document.getElementById('node_type').value = '';
            document.getElementById('ritual_space').value = '';
            document.getElementById('magical_protection').value = '';
            document.getElementById('cursed_blessed').value = '';
        }
    });
}

/**
 * Load location data for editing
 */
async function loadLocationData(locationId) {
    try {
        const response = await fetch(`api_get_location.php?id=${locationId}`);
        const data = await response.json();
        
        if (data.success) {
            populateForm(data.location);
        } else {
            showNotification('Failed to load location data', 'error');
        }
    } catch (error) {
        console.error('Error loading location:', error);
        showNotification('Error loading location data', 'error');
    }
}

/**
 * Populate form with location data
 */
function populateForm(location) {
    // Basic Information
    document.getElementById('name').value = location.name || '';
    document.getElementById('location_type').value = location.type || '';
    document.getElementById('summary').value = location.summary || '';
    document.getElementById('description').value = location.description || '';
    document.getElementById('notes').value = location.notes || '';
    document.getElementById('status').value = location.status || 'Active';
    document.getElementById('status_notes').value = location.status_notes || '';
    
    // Geography
    document.getElementById('district').value = location.district || '';
    document.getElementById('address').value = location.address || '';
    document.getElementById('latitude').value = location.latitude || '';
    document.getElementById('longitude').value = location.longitude || '';
    
    // Ownership & Control
    document.getElementById('owner_type').value = location.owner_type || '';
    document.getElementById('owner_notes').value = location.owner_notes || '';
    document.getElementById('faction').value = location.faction || '';
    document.getElementById('access_control').value = location.access_control || '';
    document.getElementById('access_notes').value = location.access_notes || '';
    
    // Security
    if (location.security_level) {
        document.querySelector(`input[name="security_level"][value="${location.security_level}"]`).checked = true;
    }
    
    // Security checkboxes
    const securityFields = [
        'locks', 'alarms', 'guards', 'hidden_entrance', 
        'sunlight_protected', 'warding_rituals', 'cameras', 'reinforced'
    ];
    securityFields.forEach(field => {
        const checkbox = document.querySelector(`input[name="security_${field}"]`);
        if (checkbox && location[`security_${field}`]) {
            checkbox.checked = true;
        }
    });
    
    document.getElementById('security_notes').value = location.security_notes || '';
    
    // Utility checkboxes
    const utilityFields = [
        'blood_storage', 'computers', 'library', 'medical',
        'workshop', 'hidden_caches', 'armory', 'communications'
    ];
    utilityFields.forEach(field => {
        const checkbox = document.querySelector(`input[name="utility_${field}"]`);
        if (checkbox && location[`utility_${field}`]) {
            checkbox.checked = true;
        }
    });
    
    document.getElementById('utility_notes').value = location.utility_notes || '';
    
    // Social
    document.getElementById('social_features').value = location.social_features || '';
    document.getElementById('capacity').value = location.capacity || '';
    document.getElementById('prestige_level').value = location.prestige_level || '0';
    
    // Supernatural
    if (location.has_supernatural) {
        document.getElementById('has_supernatural').checked = true;
        document.getElementById('supernatural-fields').style.display = 'block';
        document.getElementById('node_points').value = location.node_points || '';
        document.getElementById('node_type').value = location.node_type || '';
        document.getElementById('ritual_space').value = location.ritual_space || '';
        document.getElementById('magical_protection').value = location.magical_protection || '';
        document.getElementById('cursed_blessed').value = location.cursed_blessed || '';
    }
    
    // Relationships
    document.getElementById('parent_location').value = location.parent_location_id || '';
    document.getElementById('relationship_type').value = location.relationship_type || '';
    document.getElementById('relationship_notes').value = location.relationship_notes || '';
    
    // Image
    document.getElementById('image').value = location.image || '';
}

/**
 * Initialize form submission
 */
function initializeFormSubmit() {
    const form = document.getElementById('location-form');
    
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        if (!validateForm()) {
            return;
        }
        
        const locationData = collectFormData();
        const locationId = document.getElementById('location_id').value;
        
        try {
            const url = locationId ? 'api_update_location.php' : 'api_create_location.php';
            const response = await fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(locationData)
            });
            
            const data = await response.json();
            
            if (data.success) {
                showNotification(
                    locationId ? 'Location updated successfully!' : 'Location created successfully!', 
                    'success'
                );
                setTimeout(() => {
                    window.location.href = 'admin_locations.php';
                }, 1500);
            } else {
                showNotification(data.message || 'Failed to save location', 'error');
            }
        } catch (error) {
            console.error('Error saving location:', error);
            showNotification('Error saving location', 'error');
        }
    });
}

/**
 * Validate form
 */
function validateForm() {
    const requiredFields = ['name', 'location_type', 'status', 'owner_type', 'access_control'];
    let isValid = true;
    
    requiredFields.forEach(fieldId => {
        const field = document.getElementById(fieldId);
        if (!field.value.trim()) {
            field.style.borderColor = '#f44336';
            isValid = false;
        } else {
            field.style.borderColor = '';
        }
    });
    
    if (!isValid) {
        showNotification('Please fill in all required fields', 'error');
    }
    
    return isValid;
}

/**
 * Collect form data
 */
function collectFormData() {
    const data = {
        id: document.getElementById('location_id').value || null,
        name: document.getElementById('name').value,
        type: document.getElementById('location_type').value,
        summary: document.getElementById('summary').value,
        description: document.getElementById('description').value,
        notes: document.getElementById('notes').value,
        status: document.getElementById('status').value,
        status_notes: document.getElementById('status_notes').value,
        
        district: document.getElementById('district').value,
        address: document.getElementById('address').value,
        latitude: document.getElementById('latitude').value,
        longitude: document.getElementById('longitude').value,
        
        owner_type: document.getElementById('owner_type').value,
        owner_notes: document.getElementById('owner_notes').value,
        faction: document.getElementById('faction').value,
        access_control: document.getElementById('access_control').value,
        access_notes: document.getElementById('access_notes').value,
        
        security_level: document.querySelector('input[name="security_level"]:checked')?.value || 3,
        security_locks: document.querySelector('input[name="security_locks"]')?.checked ? 1 : 0,
        security_alarms: document.querySelector('input[name="security_alarms"]')?.checked ? 1 : 0,
        security_guards: document.querySelector('input[name="security_guards"]')?.checked ? 1 : 0,
        security_hidden_entrance: document.querySelector('input[name="security_hidden_entrance"]')?.checked ? 1 : 0,
        security_sunlight_protected: document.querySelector('input[name="security_sunlight_protected"]')?.checked ? 1 : 0,
        security_warding_rituals: document.querySelector('input[name="security_warding_rituals"]')?.checked ? 1 : 0,
        security_cameras: document.querySelector('input[name="security_cameras"]')?.checked ? 1 : 0,
        security_reinforced: document.querySelector('input[name="security_reinforced"]')?.checked ? 1 : 0,
        security_notes: document.getElementById('security_notes').value,
        
        utility_blood_storage: document.querySelector('input[name="utility_blood_storage"]')?.checked ? 1 : 0,
        utility_computers: document.querySelector('input[name="utility_computers"]')?.checked ? 1 : 0,
        utility_library: document.querySelector('input[name="utility_library"]')?.checked ? 1 : 0,
        utility_medical: document.querySelector('input[name="utility_medical"]')?.checked ? 1 : 0,
        utility_workshop: document.querySelector('input[name="utility_workshop"]')?.checked ? 1 : 0,
        utility_hidden_caches: document.querySelector('input[name="utility_hidden_caches"]')?.checked ? 1 : 0,
        utility_armory: document.querySelector('input[name="utility_armory"]')?.checked ? 1 : 0,
        utility_communications: document.querySelector('input[name="utility_communications"]')?.checked ? 1 : 0,
        utility_notes: document.getElementById('utility_notes').value,
        
        social_features: document.getElementById('social_features').value,
        capacity: document.getElementById('capacity').value,
        prestige_level: document.getElementById('prestige_level').value,
        
        has_supernatural: document.getElementById('has_supernatural').checked ? 1 : 0,
        node_points: document.getElementById('node_points').value,
        node_type: document.getElementById('node_type').value,
        ritual_space: document.getElementById('ritual_space').value,
        magical_protection: document.getElementById('magical_protection').value,
        cursed_blessed: document.getElementById('cursed_blessed').value,
        
        parent_location_id: document.getElementById('parent_location').value || null,
        relationship_type: document.getElementById('relationship_type').value,
        relationship_notes: document.getElementById('relationship_notes').value,
        
        image: document.getElementById('image').value
    };
    
    return data;
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

