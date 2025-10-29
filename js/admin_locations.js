/**
 * Admin Locations Management JavaScript
 * Handles table operations, CRUD, filtering, and character assignment
 */

let allLocations = [];
let filteredLocations = [];
let currentPage = 1;
let locationsPerPage = 20;
let currentSort = { column: 'id', direction: 'asc' };
let currentFilter = 'all';
let currentTypeFilter = 'all';
let currentStatusFilter = 'all';
let currentOwnerFilter = 'all';
let currentSearchTerm = '';
let currentLocationId = null;

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    initializeEventListeners();
    loadLocations();
});

function initializeEventListeners() {
    // Filter buttons
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            currentFilter = this.dataset.filter;
            applyFilters();
        });
    });

    // Type filter
    document.getElementById('typeFilter').addEventListener('change', function() {
        currentTypeFilter = this.value;
        applyFilters();
    });

    // Status filter
    document.getElementById('statusFilter').addEventListener('change', function() {
        currentStatusFilter = this.value;
        applyFilters();
    });

    // Owner filter
    document.getElementById('ownerFilter').addEventListener('change', function() {
        currentOwnerFilter = this.value;
        applyFilters();
    });

    // Search
    document.getElementById('locationSearch').addEventListener('input', function() {
        currentSearchTerm = this.value.toLowerCase();
        applyFilters();
    });

    // Page size
    document.getElementById('pageSize').addEventListener('change', function() {
        locationsPerPage = parseInt(this.value);
        currentPage = 1;
        applyFilters();
    });

    // Form submission
    document.getElementById('locationForm').addEventListener('submit', handleFormSubmit);
}

async function loadLocations() {
    try {
        const response = await fetch('api_locations.php');
        const data = await response.json();
        
        if (data.success) {
            allLocations = data.locations;
            applyFilters();
        } else {
            showNotification('Failed to load locations: ' + data.error, 'error');
        }
    } catch (error) {
        console.error('Error loading locations:', error);
        showNotification('Failed to load locations', 'error');
    }
}

function applyFilters() {
    filteredLocations = allLocations.filter(location => {
        // Type filter
        if (currentFilter !== 'all') {
            if (currentFilter === 'havens' && location.type !== 'Haven') return false;
            if (currentFilter === 'elysiums' && location.type !== 'Elysium') return false;
            if (currentFilter === 'domains' && location.type !== 'Domain') return false;
            if (currentFilter === 'hunting-grounds' && location.type !== 'Hunting Ground') return false;
            if (currentFilter === 'nightclubs' && location.type !== 'Nightclub') return false;
            if (currentFilter === 'businesses' && location.type !== 'Business') return false;
        }

        // Type dropdown filter
        if (currentTypeFilter !== 'all' && location.type !== currentTypeFilter) return false;

        // Status filter
        if (currentStatusFilter !== 'all' && location.status !== currentStatusFilter) return false;

        // Owner filter
        if (currentOwnerFilter !== 'all' && location.owner_type !== currentOwnerFilter) return false;

        // Search filter
        if (currentSearchTerm && !location.name.toLowerCase().includes(currentSearchTerm)) return false;

        return true;
    });

    // Apply sorting
    sortTable(currentSort.column, currentSort.direction);
    
    // Update pagination
    currentPage = 1;
    updateTable();
}

function sortTable(column, direction) {
    currentSort = { column, direction };
    
    filteredLocations.sort((a, b) => {
        let aVal = a[column];
        let bVal = b[column];
        
        // Handle different data types
        if (column === 'id' || column === 'security_level') {
            aVal = parseInt(aVal) || 0;
            bVal = parseInt(bVal) || 0;
        } else if (column === 'created_at') {
            aVal = new Date(aVal);
            bVal = new Date(bVal);
        } else {
            aVal = (aVal || '').toString().toLowerCase();
            bVal = (bVal || '').toString().toLowerCase();
        }
        
        if (direction === 'asc') {
            return aVal > bVal ? 1 : aVal < bVal ? -1 : 0;
        } else {
            return aVal < bVal ? 1 : aVal > bVal ? -1 : 0;
        }
    });
    
    updateTable();
}

function updateTable() {
    const tbody = document.querySelector('#locationsTable tbody');
    const startIndex = (currentPage - 1) * locationsPerPage;
    const endIndex = startIndex + locationsPerPage;
    const pageLocations = filteredLocations.slice(startIndex, endIndex);
    
    tbody.innerHTML = pageLocations.map(location => `
        <tr>
            <td>${location.id}</td>
            <td><strong>${escapeHtml(location.name)}</strong></td>
            <td><span class="badge-${location.type.toLowerCase().replace(' ', '-')}">${escapeHtml(location.type)}</span></td>
            <td><span class="badge-${location.status.toLowerCase()}">${escapeHtml(location.status)}</span></td>
            <td>${escapeHtml(location.district || 'N/A')}</td>
            <td>${escapeHtml(location.owner_type || 'N/A')}</td>
            <td>${formatDate(location.created_at)}</td>
            <td class="actions">
                <button class="action-btn view-btn" onclick="viewLocation(${location.id})" title="View Location">👁️</button>
                <button class="action-btn edit-btn" onclick="editLocation(${location.id})" title="Edit Location">✏️</button>
                <button class="action-btn assign-btn" onclick="assignLocation(${location.id}, '${escapeHtml(location.name)}')" title="Assign Characters">🎯</button>
                <button class="action-btn delete-btn" onclick="deleteLocation(${location.id}, '${escapeHtml(location.name)}')" title="Delete Location">🗑️</button>
            </td>
        </tr>
    `).join('');

    // Add sorting event listeners
    document.querySelectorAll('#locationsTable th[data-sort]').forEach(th => {
        th.addEventListener('click', function() {
            const column = this.dataset.sort;
            const direction = currentSort.column === column && currentSort.direction === 'asc' ? 'desc' : 'asc';
            
            // Update sort indicators
            document.querySelectorAll('#locationsTable th').forEach(h => {
                h.classList.remove('sorted-asc', 'sorted-desc');
            });
            this.classList.add(direction === 'asc' ? 'sorted-asc' : 'sorted-desc');
            
            sortTable(column, direction);
        });
    });
    
    updatePagination();
}

function updatePagination() {
    const totalPages = Math.ceil(filteredLocations.length / locationsPerPage);
    const paginationInfo = document.getElementById('paginationInfo');
    const paginationButtons = document.getElementById('paginationButtons');
    
    // Update info
    const startIndex = (currentPage - 1) * locationsPerPage + 1;
    const endIndex = Math.min(currentPage * locationsPerPage, filteredLocations.length);
    paginationInfo.textContent = `Showing ${startIndex}-${endIndex} of ${filteredLocations.length} locations`;
    
    // Update buttons
    paginationButtons.innerHTML = '';
    
    // Previous button
    const prevBtn = document.createElement('button');
    prevBtn.className = 'page-btn';
    prevBtn.textContent = '← Previous';
    prevBtn.disabled = currentPage === 1;
    prevBtn.onclick = () => changePage(currentPage - 1);
    paginationButtons.appendChild(prevBtn);
    
    // Page numbers
    const startPage = Math.max(1, currentPage - 2);
    const endPage = Math.min(totalPages, currentPage + 2);
    
    for (let i = startPage; i <= endPage; i++) {
        const pageBtn = document.createElement('button');
        pageBtn.className = `page-btn ${i === currentPage ? 'active' : ''}`;
        pageBtn.textContent = i;
        pageBtn.onclick = () => changePage(i);
        paginationButtons.appendChild(pageBtn);
    }
    
    // Next button
    const nextBtn = document.createElement('button');
    nextBtn.className = 'page-btn';
    nextBtn.textContent = 'Next →';
    nextBtn.disabled = currentPage === totalPages;
    nextBtn.onclick = () => changePage(currentPage + 1);
    paginationButtons.appendChild(nextBtn);
}

function changePage(page) {
    const totalPages = Math.ceil(filteredLocations.length / locationsPerPage);
    if (page >= 1 && page <= totalPages) {
        currentPage = page;
        updateTable();
    }
}

// CRUD Functions
function openAddLocationModal() {
    document.getElementById('locationModalTitle').textContent = 'Add New Location';
    document.getElementById('locationForm').reset();
    document.getElementById('locationId').value = '';
    document.getElementById('locationModal').classList.add('active');
}

function editLocation(id) {
    const location = allLocations.find(loc => loc.id == id);
    if (!location) return;
    
    document.getElementById('locationModalTitle').textContent = 'Edit Location';
    document.getElementById('locationId').value = location.id;
    document.getElementById('locationName').value = location.name;
    document.getElementById('locationType').value = location.type;
    document.getElementById('locationStatus').value = location.status;
    document.getElementById('locationDistrict').value = location.district || '';
    document.getElementById('locationOwnerType').value = location.owner_type;
    document.getElementById('locationFaction').value = location.faction || '';
    document.getElementById('locationAccessControl').value = location.access_control || '';
    document.getElementById('locationSecurityLevel').value = location.security_level || 3;
    document.getElementById('locationDescription').value = location.description || '';
    document.getElementById('locationSummary').value = location.summary || '';
    document.getElementById('locationNotes').value = location.notes || '';
    
    document.getElementById('locationModal').classList.add('active');
}

async function viewLocation(id) {
    const location = allLocations.find(loc => loc.id == id);
    if (!location) return;
    
    document.getElementById('viewLocationName').textContent = location.name;
    
    // Show loading state
    document.getElementById('viewLocationContent').innerHTML = '<div class="loading">Loading location details...</div>';
    document.getElementById('viewModal').classList.add('active');
    
    try {
        // Fetch character assignments
        const response = await fetch(`api_admin_location_assignments.php?location_id=${id}`);
        const data = await response.json();
        
        let assignmentsHtml = '';
        if (data.success && data.assignments.length > 0) {
            assignmentsHtml = `
                <div class="view-section">
                    <h3>Assigned Characters (${data.count})</h3>
                    <div class="assignments-list">
                        ${data.assignments.map(assignment => `
                            <div class="assignment-item">
                                <div class="assignment-character">
                                    <strong>${escapeHtml(assignment.character_name)}</strong>
                                    <small>${escapeHtml(assignment.clan)} - ${escapeHtml(assignment.player_name)}</small>
                                </div>
                                <div class="assignment-type">
                                    <span class="assignment-badge assignment-${assignment.assignment_type.toLowerCase().replace(' ', '-')}">
                                        ${escapeHtml(assignment.assignment_type)}
                                    </span>
                                </div>
                                ${assignment.notes ? `<div class="assignment-notes"><small>${escapeHtml(assignment.notes)}</small></div>` : ''}
                            </div>
                        `).join('')}
                    </div>
                </div>
            `;
        } else {
            assignmentsHtml = `
                <div class="view-section">
                    <h3>Assigned Characters</h3>
                    <p class="no-assignments">No characters assigned to this location.</p>
                </div>
            `;
        }
        
        const content = `
            <div class="view-section">
                <h3>Basic Information</h3>
                <p><strong>Name:</strong> ${escapeHtml(location.name)}</p>
                <p><strong>Type:</strong> <span class="badge-${location.type.toLowerCase().replace(' ', '-')}">${escapeHtml(location.type)}</span></p>
                <p><strong>Status:</strong> <span class="badge-${location.status.toLowerCase()}">${escapeHtml(location.status)}</span></p>
                <p><strong>District:</strong> ${escapeHtml(location.district || 'N/A')}</p>
            </div>
            
            <div class="view-section">
                <h3>Ownership & Control</h3>
                <p><strong>Owner Type:</strong> ${escapeHtml(location.owner_type || 'N/A')}</p>
                <p><strong>Faction:</strong> ${escapeHtml(location.faction || 'N/A')}</p>
                <p><strong>Access Control:</strong> ${escapeHtml(location.access_control || 'N/A')}</p>
                <p><strong>Security Level:</strong> ${location.security_level || 3}</p>
            </div>
            
            ${assignmentsHtml}
            
            ${location.description ? `
            <div class="view-section">
                <h3>Description</h3>
                <p>${escapeHtml(location.description)}</p>
            </div>
            ` : ''}
            
            ${location.summary ? `
            <div class="view-section">
                <h3>Summary</h3>
                <p>${escapeHtml(location.summary)}</p>
            </div>
            ` : ''}
            
            ${location.notes ? `
            <div class="view-section">
                <h3>Notes</h3>
                <p>${escapeHtml(location.notes)}</p>
            </div>
            ` : ''}
        `;
        
        document.getElementById('viewLocationContent').innerHTML = content;
        
    } catch (error) {
        console.error('Error loading assignments:', error);
        document.getElementById('viewLocationContent').innerHTML = `
            <div class="view-section">
                <h3>Error</h3>
                <p>Failed to load character assignments.</p>
            </div>
        `;
    }
}

function assignLocation(id, name) {
    currentLocationId = id;
    document.getElementById('assignLocationName').textContent = name;
    document.getElementById('assignModal').classList.add('active');
}

function deleteLocation(id, name) {
    currentLocationId = id;
    document.getElementById('deleteLocationName').textContent = name;
    
    // Check for assignments
    fetch(`api_admin_location_assignments.php?location_id=${id}`)
        .then(response => response.json())
        .then(data => {
            const warning = document.getElementById('deleteWarning');
            if (data.success && data.count > 0) {
                warning.style.display = 'block';
                warning.innerHTML = `⚠️ <strong>This location has ${data.count} character assignment(s)</strong> - remove assignments first!`;
                document.getElementById('confirmDeleteBtn').disabled = true;
            } else {
                warning.style.display = 'none';
                document.getElementById('confirmDeleteBtn').disabled = false;
            }
        })
        .catch(error => {
            console.error('Error checking assignments:', error);
            document.getElementById('confirmDeleteBtn').disabled = false;
        });
    
    document.getElementById('deleteModal').classList.add('active');
}

// Modal Functions
function closeLocationModal() {
    document.getElementById('locationModal').classList.remove('active');
}

function closeViewModal() {
    document.getElementById('viewModal').classList.remove('active');
}

function closeAssignModal() {
    document.getElementById('assignModal').classList.remove('active');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.remove('active');
}

// Form Handling
async function handleFormSubmit(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const data = Object.fromEntries(formData.entries());
    
    const isEdit = data.id !== '';
    const url = 'api_admin_locations_crud.php';
    const method = isEdit ? 'PUT' : 'POST';
    
    try {
        const response = await fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            showNotification(result.message, 'success');
            closeLocationModal();
            loadLocations();
        } else {
            showNotification('Error: ' + result.error, 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('Failed to save location', 'error');
    }
}

async function assignCharactersToLocation() {
    const selectedCharacters = [];
    
    document.querySelectorAll('.character-item').forEach(item => {
        if (item.classList.contains('selected')) {
            const characterId = item.dataset.characterId;
            const assignmentType = item.querySelector('.assignment-type-select').value;
            selectedCharacters.push({
                character_id: characterId,
                assignment_type: assignmentType
            });
        }
    });
    
    if (selectedCharacters.length === 0) {
        showNotification('Please select at least one character', 'error');
        return;
    }
    
    try {
        const response = await fetch('api_admin_location_assignments.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                location_id: currentLocationId,
                assignments: selectedCharacters
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            showNotification(result.message, 'success');
            closeAssignModal();
        } else {
            showNotification('Error: ' + result.error, 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('Failed to assign characters', 'error');
    }
}

async function confirmDeleteLocation() {
    try {
        const response = await fetch(`api_delete_location_simple.php?id=${currentLocationId}`, {
            method: 'DELETE'
        });
        
        const result = await response.json();
        
        if (result.success) {
            showNotification(result.message, 'success');
            closeDeleteModal();
            loadLocations();
        } else {
            showNotification('Error: ' + result.error, 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('Failed to delete location', 'error');
    }
}

// Utility Functions
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString() + ' ' + date.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
}

function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.textContent = message;
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 5000);
}

// Character selection for assignment modal
document.addEventListener('click', function(e) {
    if (e.target.closest('.character-item')) {
        const item = e.target.closest('.character-item');
        item.classList.toggle('selected');
    }
});

// Close modals on escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeLocationModal();
        closeViewModal();
        closeAssignModal();
        closeDeleteModal();
    }
});
