/**
 * Location Story Parser
 * Handles AI-powered narrative-to-database conversion
 */

let extractedData = null;

// Example narrative for demonstration
const EXAMPLE_NARRATIVE = `The Red Velvet Lounge sits in the heart of Downtown Phoenix, a high-end nightclub that serves as neutral ground for the city's Kindred. By day, it operates as a legitimate business, but when the sun sets, it transforms into Elysium - a place where vampires of all factions can gather under the protection of the Prince's decree.

The main floor features a spacious dance area with a capacity of around 200 guests, both mortal and Kindred. The VIP section on the second floor is reserved for important vampires and their guests, accessible only with proper invitation. Behind the bar, a hidden door leads to underground chambers where sensitive meetings take place, protected by powerful wards placed by the Tremere Primogen.

Security is extensive: reinforced steel doors, state-of-the-art alarm systems, and several ghouls posing as security guards ensure no trouble enters. The owner, Marcus Devereaux, an influential Toreador, has established this as one of the most prestigious gathering places in the city. The location also houses a small computer network in the private offices for monitoring security feeds and managing the mortal business front.`;

document.addEventListener('DOMContentLoaded', () => {
    initializeEventListeners();
});

function initializeEventListeners() {
    const parseButton = document.getElementById('parse-button');
    const saveButton = document.getElementById('save-button');
    const cancelButton = document.getElementById('cancel-button');
    const loadExampleLink = document.getElementById('load-example');

    if (parseButton) {
        parseButton.addEventListener('click', handleParse);
    }

    if (saveButton) {
        saveButton.addEventListener('click', handleSave);
    }

    if (cancelButton) {
        cancelButton.addEventListener('click', handleCancel);
    }

    if (loadExampleLink) {
        loadExampleLink.addEventListener('click', (e) => {
            e.preventDefault();
            document.getElementById('story-textarea').value = EXAMPLE_NARRATIVE;
        });
    }
}

async function handleParse() {
    const textarea = document.getElementById('story-textarea');
    const narrative = textarea.value.trim();

    if (!narrative) {
        alert('Please enter a location narrative first.');
        return;
    }

    const parseButton = document.getElementById('parse-button');
    const buttonText = parseButton.querySelector('.button-text');
    
    // Show loading state
    parseButton.classList.add('loading');
    parseButton.disabled = true;
    buttonText.textContent = 'Parsing with AI...';

    try {
        const response = await fetch('api_parse_location_story.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ narrative })
        });

        const result = await response.json();

        if (!result.success) {
            throw new Error(result.message || 'Failed to parse narrative');
        }

        extractedData = result.data;
        displayExtractedData(extractedData);
        
        // Show save actions
        document.getElementById('save-actions').style.display = 'flex';

    } catch (error) {
        console.error('Parse error:', error);
        alert('Failed to parse narrative: ' + error.message);
    } finally {
        // Reset button state
        parseButton.classList.remove('loading');
        parseButton.disabled = false;
        buttonText.textContent = 'Parse Story with AI';
    }
}

function displayExtractedData(data) {
    const previewContent = document.getElementById('preview-content');
    
    let html = '';

    // Helper function to render a field
    const renderField = (label, fieldKey, type = 'text') => {
        const field = data[fieldKey];
        if (!field) return '';

        const confidence = field.confidence || 0;
        const value = field.value ?? '';
        
        let confidenceClass = 'confidence-low';
        let confidenceBadge = 'Low';
        
        if (confidence >= 0.8) {
            confidenceClass = 'confidence-high';
            confidenceBadge = 'High';
        } else if (confidence >= 0.5) {
            confidenceClass = 'confidence-medium';
            confidenceBadge = 'Medium';
        }

        let inputHtml = '';
        
        if (type === 'textarea') {
            inputHtml = `<textarea class="field-input" data-field="${fieldKey}" rows="3">${value}</textarea>`;
        } else if (type === 'number') {
            inputHtml = `<input type="number" class="field-input" data-field="${fieldKey}" value="${value}">`;
        } else if (type === 'select') {
            inputHtml = `<input type="text" class="field-input" data-field="${fieldKey}" value="${value}">`;
        } else if (type === 'checkbox') {
            const checked = value == 1 ? 'checked' : '';
            inputHtml = `<input type="checkbox" class="field-input" data-field="${fieldKey}" ${checked}>`;
        } else {
            inputHtml = `<input type="text" class="field-input" data-field="${fieldKey}" value="${value}">`;
        }

        return `
            <div class="field-group ${confidenceClass}">
                <div class="field-label">
                    <span>${label}</span>
                    <span class="confidence-badge" title="${field.reason}">${confidenceBadge} (${Math.round(confidence * 100)}%)</span>
                </div>
                <div class="field-value">
                    ${inputHtml}
                </div>
            </div>
        `;
    };

    // Basic Information
    html += '<div class="section-header">Basic Information</div>';
    html += renderField('Name', 'name');
    html += renderField('Type', 'type', 'select');
    html += renderField('Summary', 'summary');
    html += renderField('Description', 'description', 'textarea');
    html += renderField('Status', 'status', 'select');
    html += renderField('Status Notes', 'status_notes');

    // Geography
    html += '<div class="section-header">Geography</div>';
    html += renderField('District/Area', 'district');
    html += renderField('Address', 'address');
    html += renderField('Latitude', 'latitude', 'number');
    html += renderField('Longitude', 'longitude', 'number');

    // Ownership & Control
    html += '<div class="section-header">Ownership & Control</div>';
    html += renderField('Ownership Type', 'owner_type', 'select');
    html += renderField('Owner Notes', 'owner_notes', 'textarea');
    html += renderField('Faction', 'faction', 'select');
    html += renderField('Access Control', 'access_control', 'select');
    html += renderField('Access Notes', 'access_notes', 'textarea');

    // Security Features
    html += '<div class="section-header">Security Features</div>';
    html += renderField('Security Level (1-5)', 'security_level', 'number');
    
    html += '<div class="checkbox-group">';
    html += '<div class="checkbox-item">' + renderField('Locks', 'security_locks', 'checkbox') + '</div>';
    html += '<div class="checkbox-item">' + renderField('Alarms', 'security_alarms', 'checkbox') + '</div>';
    html += '<div class="checkbox-item">' + renderField('Guards', 'security_guards', 'checkbox') + '</div>';
    html += '<div class="checkbox-item">' + renderField('Hidden Entrance', 'security_hidden_entrance', 'checkbox') + '</div>';
    html += '<div class="checkbox-item">' + renderField('Sunlight Protected', 'security_sunlight_protected', 'checkbox') + '</div>';
    html += '<div class="checkbox-item">' + renderField('Warding Rituals', 'security_warding_rituals', 'checkbox') + '</div>';
    html += '<div class="checkbox-item">' + renderField('Cameras', 'security_cameras', 'checkbox') + '</div>';
    html += '<div class="checkbox-item">' + renderField('Reinforced', 'security_reinforced', 'checkbox') + '</div>';
    html += '</div>';
    
    html += renderField('Security Notes', 'security_notes', 'textarea');

    // Utility Features
    html += '<div class="section-header">Utility Features</div>';
    html += '<div class="checkbox-group">';
    html += '<div class="checkbox-item">' + renderField('Blood Storage', 'utility_blood_storage', 'checkbox') + '</div>';
    html += '<div class="checkbox-item">' + renderField('Computers', 'utility_computers', 'checkbox') + '</div>';
    html += '<div class="checkbox-item">' + renderField('Library', 'utility_library', 'checkbox') + '</div>';
    html += '<div class="checkbox-item">' + renderField('Medical', 'utility_medical', 'checkbox') + '</div>';
    html += '<div class="checkbox-item">' + renderField('Workshop', 'utility_workshop', 'checkbox') + '</div>';
    html += '<div class="checkbox-item">' + renderField('Hidden Caches', 'utility_hidden_caches', 'checkbox') + '</div>';
    html += '<div class="checkbox-item">' + renderField('Armory', 'utility_armory', 'checkbox') + '</div>';
    html += '<div class="checkbox-item">' + renderField('Communications', 'utility_communications', 'checkbox') + '</div>';
    html += '</div>';
    html += renderField('Utility Notes', 'utility_notes', 'textarea');

    // Social Features
    html += '<div class="section-header">Social Features</div>';
    html += renderField('Social Features', 'social_features', 'textarea');
    html += renderField('Capacity', 'capacity', 'number');
    html += renderField('Prestige Level (0-5)', 'prestige_level', 'number');

    // Supernatural Features
    if (data.has_supernatural && data.has_supernatural.value == 1) {
        html += '<div class="section-header">Supernatural Features</div>';
        html += renderField('Node Points (0-10)', 'node_points', 'number');
        html += renderField('Node Type', 'node_type', 'select');
        html += renderField('Ritual Space', 'ritual_space', 'textarea');
        html += renderField('Magical Protection', 'magical_protection', 'textarea');
        html += renderField('Cursed/Blessed', 'cursed_blessed', 'textarea');
    }

    previewContent.innerHTML = html;
}

async function handleSave() {
    if (!extractedData) {
        alert('No data to save. Please parse a narrative first.');
        return;
    }

    const saveButton = document.getElementById('save-button');
    saveButton.disabled = true;
    saveButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';

    try {
        // Collect edited values from the form
        const finalData = {};
        
        document.querySelectorAll('.field-input').forEach(input => {
            const fieldKey = input.dataset.field;
            let value;
            
            if (input.type === 'checkbox') {
                value = input.checked ? 1 : 0;
            } else if (input.type === 'number') {
                value = parseFloat(input.value) || 0;
            } else {
                value = input.value;
            }
            
            finalData[fieldKey] = value;
        });

        // Call the existing create location API
        const response = await fetch('api_create_location.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(finalData)
        });

        const result = await response.json();

        if (!result.success) {
            throw new Error(result.message || 'Failed to save location');
        }

        alert('✨ Location created successfully!');
        window.location.href = 'admin_locations.php';

    } catch (error) {
        console.error('Save error:', error);
        alert('Failed to save location: ' + error.message);
        saveButton.disabled = false;
        saveButton.innerHTML = '<i class="fas fa-save"></i> Save Location to Database';
    }
}

function handleCancel() {
    if (confirm('Are you sure? Any extracted data will be lost.')) {
        window.location.href = 'admin_locations.php';
    }
}

