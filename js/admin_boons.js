/**
 * Boon Ledger JavaScript
 * Handles modal interactions and AJAX operations for boons
 */

let currentBoonId = null;
let deleteBoonId = null;

// Load boons on page load
document.addEventListener('DOMContentLoaded', function() {
    loadBoons();
    
    // Status filter
    const statusFilter = document.getElementById('statusFilter');
    if (statusFilter) {
        statusFilter.addEventListener('change', function() {
            loadBoons();
        });
    }
    
    // Form submission
    const boonForm = document.getElementById('boonForm');
    if (boonForm) {
        boonForm.addEventListener('submit', function(e) {
            e.preventDefault();
            saveBoon();
        });
    }
    
    // Delete confirmation
    const confirmDeleteBtn = document.getElementById('confirmDeleteBoonBtn');
    if (confirmDeleteBtn) {
        confirmDeleteBtn.addEventListener('click', function() {
            if (deleteBoonId) {
                deleteBoon(deleteBoonId);
            }
        });
    }
    
    // Giver dropdown change handler
    const giverSelect = document.getElementById('giverSelect');
    const giverCustom = document.getElementById('giverNameCustom');
    if (giverSelect && giverCustom) {
        giverSelect.addEventListener('change', function() {
            if (this.value === '__CUSTOM__') {
                giverCustom.style.display = 'block';
                giverCustom.required = true;
            } else {
                giverCustom.style.display = 'none';
                giverCustom.required = false;
                giverCustom.value = '';
            }
            updateGiverName();
        });
        giverCustom.addEventListener('input', updateGiverName);
    }
    
    // Receiver dropdown change handler
    const receiverSelect = document.getElementById('receiverSelect');
    const receiverCustom = document.getElementById('receiverNameCustom');
    if (receiverSelect && receiverCustom) {
        receiverSelect.addEventListener('change', function() {
            if (this.value === '__CUSTOM__') {
                receiverCustom.style.display = 'block';
                receiverCustom.required = true;
            } else {
                receiverCustom.style.display = 'none';
                receiverCustom.required = false;
                receiverCustom.value = '';
            }
            updateReceiverName();
        });
        receiverCustom.addEventListener('input', updateReceiverName);
    }
});

function updateGiverName() {
    const select = document.getElementById('giverSelect');
    const custom = document.getElementById('giverNameCustom');
    const hidden = document.getElementById('giverName');
    
    if (select && custom && hidden) {
        if (select.value === '__CUSTOM__') {
            hidden.value = custom.value.trim();
        } else {
            hidden.value = select.value;
        }
    }
}

function updateReceiverName() {
    const select = document.getElementById('receiverSelect');
    const custom = document.getElementById('receiverNameCustom');
    const hidden = document.getElementById('receiverName');
    
    if (select && custom && hidden) {
        if (select.value === '__CUSTOM__') {
            hidden.value = custom.value.trim();
        } else {
            hidden.value = select.value;
        }
    }
}

function loadBoons() {
    const statusFilter = document.getElementById('statusFilter');
    const status = statusFilter ? statusFilter.value : 'all';
    
    let url = 'api_boons.php?action=list';
    if (status !== 'all') {
        url += '&status=' + encodeURIComponent(status);
    }
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderBoonsTable(data.data);
            } else {
                showNotification('Error loading boons: ' + (data.error || 'Unknown error'), 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Failed to load boons', 'error');
        });
}

function renderBoonsTable(boons) {
    const tbody = document.getElementById('boonsTableBody');
    
    if (!tbody) return;
    
    if (boons.length === 0) {
        tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted">No boons found.</td></tr>';
        return;
    }
    
    tbody.innerHTML = boons.map(boon => {
        const createdDate = boon.date_created ? new Date(boon.date_created).toLocaleDateString() : '—';
        const description = boon.description ? (boon.description.length > 50 ? boon.description.substring(0, 50) + '...' : boon.description) : '—';
        
        return `
            <tr data-boon-id="${boon.boon_id}">
                <td>${boon.boon_id}</td>
                <td>${escapeHtml(boon.giver_name)}</td>
                <td>${escapeHtml(boon.receiver_name)}</td>
                <td class="text-center">${renderBoonTypeBadge(boon.boon_type)}</td>
                <td class="text-center">${renderStatusBadge(boon.status)}</td>
                <td title="${escapeHtml(boon.description || '')}">${escapeHtml(description)}</td>
                <td class="text-center">${createdDate}</td>
                <td class="actions text-center">
                    <div class="btn-group btn-group-sm" role="group">
                        <button class="action-btn view-btn" onclick="editBoon(${boon.boon_id})" title="Edit Boon">✏️</button>
                        ${boon.status === 'Owed' ? `<button class="action-btn paid-btn" onclick="markBoonPaid(${boon.boon_id})" title="Mark Paid">✓</button>` : ''}
                        ${boon.status !== 'Broken' ? `<button class="action-btn broken-btn" onclick="markBoonBroken(${boon.boon_id})" title="Mark Broken">✗</button>` : ''}
                        <button class="action-btn delete-btn" onclick="confirmDeleteBoon(${boon.boon_id}, '${escapeHtml(boon.giver_name)}', '${escapeHtml(boon.receiver_name)}')" title="Delete Boon">🗑️</button>
                    </div>
                </td>
            </tr>
        `;
    }).join('');
}

function renderBoonTypeBadge(type) {
    const colors = {
        'Trivial': '#666',
        'Minor': '#8B6508',
        'Major': '#8B0000',
        'Life': '#1a0f0f'
    };
    const color = colors[type] || '#666';
    return `<span class="boon-type-badge" style="background-color:${color};">${escapeHtml(type)}</span>`;
}

function renderStatusBadge(status) {
    const colors = {
        'Owed': '#8B6508',
        'Called': '#B22222',
        'Paid': '#1a6b3a',
        'Broken': '#3a3a3a'
    };
    const color = colors[status] || '#666';
    return `<span class="boon-status-badge" style="background-color:${color};">${escapeHtml(status)}</span>`;
}

function openBoonModal(boonId = null) {
    currentBoonId = boonId;
    const modal = document.getElementById('boonModal');
    const form = document.getElementById('boonForm');
    const title = document.getElementById('modalTitle');
    
    if (modal && form && title) {
        if (boonId) {
            title.textContent = 'Edit Boon';
            loadBoon(boonId);
        } else {
            title.textContent = 'New Boon';
            form.reset();
            document.getElementById('boonId').value = '';
            document.getElementById('boonStatus').value = 'Owed';
            // Reset dropdowns and custom fields
            document.getElementById('giverSelect').value = '';
            document.getElementById('receiverSelect').value = '';
            document.getElementById('giverNameCustom').style.display = 'none';
            document.getElementById('receiverNameCustom').style.display = 'none';
            document.getElementById('giverName').value = '';
            document.getElementById('receiverName').value = '';
        }
        modal.classList.add('active');
    }
}

function closeBoonModal() {
    const modal = document.getElementById('boonModal');
    if (modal) {
        modal.classList.remove('active');
    }
    currentBoonId = null;
}

function loadBoon(boonId) {
    fetch(`api_boons.php?action=get&id=${boonId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data) {
                const boon = data.data;
                document.getElementById('boonId').value = boon.boon_id;
                
                // Set giver name - check if it's in the dropdown or needs custom
                const giverSelect = document.getElementById('giverSelect');
                const giverCustom = document.getElementById('giverNameCustom');
                const giverName = boon.giver_name || '';
                
                if (giverSelect && giverCustom) {
                    // Check if the name exists in dropdown options
                    let foundInDropdown = false;
                    for (let option of giverSelect.options) {
                        if (option.value === giverName && option.value !== '__CUSTOM__' && option.value !== '') {
                            giverSelect.value = giverName;
                            giverCustom.style.display = 'none';
                            giverCustom.value = '';
                            foundInDropdown = true;
                            break;
                        }
                    }
                    
                    if (!foundInDropdown && giverName) {
                        // Use custom field
                        giverSelect.value = '__CUSTOM__';
                        giverCustom.style.display = 'block';
                        giverCustom.value = giverName;
                    }
                }
                document.getElementById('giverName').value = giverName;
                
                // Set receiver name - check if it's in the dropdown or needs custom
                const receiverSelect = document.getElementById('receiverSelect');
                const receiverCustom = document.getElementById('receiverNameCustom');
                const receiverName = boon.receiver_name || '';
                
                if (receiverSelect && receiverCustom) {
                    // Check if the name exists in dropdown options
                    let foundInDropdown = false;
                    for (let option of receiverSelect.options) {
                        if (option.value === receiverName && option.value !== '__CUSTOM__' && option.value !== '') {
                            receiverSelect.value = receiverName;
                            receiverCustom.style.display = 'none';
                            receiverCustom.value = '';
                            foundInDropdown = true;
                            break;
                        }
                    }
                    
                    if (!foundInDropdown && receiverName) {
                        // Use custom field
                        receiverSelect.value = '__CUSTOM__';
                        receiverCustom.style.display = 'block';
                        receiverCustom.value = receiverName;
                    }
                }
                document.getElementById('receiverName').value = receiverName;
                
                document.getElementById('boonType').value = boon.boon_type || 'Trivial';
                document.getElementById('boonStatus').value = boon.status || 'Owed';
                document.getElementById('description').value = boon.description || '';
                document.getElementById('relatedEvent').value = boon.related_event || '';
            } else {
                showNotification('Error loading boon: ' + (data.error || 'Unknown error'), 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Failed to load boon', 'error');
        });
}

function editBoon(boonId) {
    openBoonModal(boonId);
}

function saveBoon() {
    const form = document.getElementById('boonForm');
    if (!form) return;
    
    // Update hidden fields before getting form data
    updateGiverName();
    updateReceiverName();
    
    // Debug: Check hidden field values
    const giverName = document.getElementById('giverName').value;
    const receiverName = document.getElementById('receiverName').value;
    
    console.log('Giver Name:', giverName);
    console.log('Receiver Name:', receiverName);
    
    if (!giverName.trim() || !receiverName.trim()) {
        showNotification('Giver and Receiver names are required. Please select a character or enter a custom name.', 'error');
        return;
    }
    
    const formData = new FormData(form);
    const boonId = formData.get('boon_id');
    
    const data = {
        boon_id: boonId || null,
        giver_name: giverName.trim(),
        receiver_name: receiverName.trim(),
        boon_type: formData.get('boon_type'),
        status: formData.get('status'),
        description: formData.get('description'),
        related_event: formData.get('related_event')
    };
    
    console.log('Sending data:', data);
    
    const method = boonId ? 'PUT' : 'POST';
    const url = 'api_boons.php';
    
    fetch(url, {
        method: method,
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => {
        console.log('Response status:', response.status);
        if (!response.ok) {
            throw new Error('HTTP error! status: ' + response.status);
        }
        return response.json();
    })
    .then(result => {
        console.log('API Response:', result);
        if (result.success) {
            showNotification(result.message || 'Boon saved successfully', 'success');
            closeBoonModal();
            loadBoons();
        } else {
            const errorMsg = result.error || 'Failed to save boon';
            console.error('API Error:', errorMsg);
            showNotification('Error: ' + errorMsg, 'error');
        }
    })
    .catch(error => {
        console.error('Fetch Error:', error);
        showNotification('Failed to save boon: ' + error.message, 'error');
    });
}

function markBoonPaid(boonId) {
    updateBoonStatus(boonId, 'Paid');
}

function markBoonBroken(boonId) {
    if (confirm('Are you sure you want to mark this boon as Broken? This action cannot be easily undone.')) {
        updateBoonStatus(boonId, 'Broken');
    }
}

function updateBoonStatus(boonId, status) {
    fetch('api_boons.php', {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            boon_id: boonId,
            status: status
        })
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            showNotification('Boon status updated', 'success');
            loadBoons();
        } else {
            showNotification('Error: ' + (result.error || 'Failed to update status'), 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Failed to update boon status', 'error');
    });
}

function confirmDeleteBoon(boonId, giverName, receiverName) {
    deleteBoonId = boonId;
    const modal = document.getElementById('deleteBoonModal');
    const info = document.getElementById('deleteBoonInfo');
    
    if (modal && info) {
        info.textContent = `${giverName} → ${receiverName}`;
        modal.classList.add('active');
    }
}

function closeDeleteBoonModal() {
    const modal = document.getElementById('deleteBoonModal');
    if (modal) {
        modal.classList.remove('active');
    }
    deleteBoonId = null;
}

function deleteBoon(boonId) {
    fetch('api_boons.php', {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            boon_id: boonId
        })
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            showNotification('Boon deleted successfully', 'success');
            closeDeleteBoonModal();
            loadBoons();
        } else {
            showNotification('Error: ' + (result.error || 'Failed to delete boon'), 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Failed to delete boon', 'error');
    });
}

function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;
    
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${type === 'success' ? '#1a6b3a' : '#8B0000'};
        color: #f5e6d3;
        padding: 12px 20px;
        border-radius: 5px;
        font-family: var(--font-body), 'Source Serif Pro', serif;
        font-weight: bold;
        z-index: 10000;
        box-shadow: 0 4px 8px rgba(0,0,0,0.3);
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        if (notification.parentNode) {
            notification.parentNode.removeChild(notification);
        }
    }, 3000);
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Close modals when clicking outside
window.addEventListener('click', function(event) {
    const boonModal = document.getElementById('boonModal');
    const deleteModal = document.getElementById('deleteBoonModal');
    
    if (event.target === boonModal) {
        closeBoonModal();
    }
    if (event.target === deleteModal) {
        closeDeleteBoonModal();
    }
});

