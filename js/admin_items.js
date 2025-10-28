/**
 * Admin Items Management JavaScript
 * Handles table operations, CRUD, filtering, and equipment assignment
 */

let allItems = [];
let filteredItems = [];
let currentPage = 1;
let itemsPerPage = 20;
let currentSort = { column: 'id', direction: 'asc' };
let currentFilter = 'all';
let currentTypeFilter = 'all';
let currentRarityFilter = 'all';
let currentSearchTerm = '';
let currentItemId = null;

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    initializeEventListeners();
    loadItems();
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

    // Rarity filter
    document.getElementById('rarityFilter').addEventListener('change', function() {
        currentRarityFilter = this.value;
        applyFilters();
    });

    // Search
    document.getElementById('itemSearch').addEventListener('input', function() {
        currentSearchTerm = this.value.toLowerCase();
        applyFilters();
    });

    // Page size
    document.getElementById('pageSize').addEventListener('change', function() {
        itemsPerPage = parseInt(this.value);
        currentPage = 1;
        applyFilters();
    });

    // Form submission
    document.getElementById('itemForm').addEventListener('submit', handleFormSubmit);
}

async function loadItems() {
    try {
        const response = await fetch('api_items.php');
        const data = await response.json();
        
        if (data.success) {
            allItems = data.items;
            applyFilters();
        } else {
            showNotification('Failed to load items: ' + data.error, 'error');
        }
    } catch (error) {
        console.error('Error loading items:', error);
        showNotification('Failed to load items', 'error');
    }
}

function applyFilters() {
    filteredItems = allItems.filter(item => {
        // Type filter
        if (currentFilter !== 'all') {
            if (currentFilter === 'weapons' && item.type !== 'Weapon') return false;
            if (currentFilter === 'armor' && item.type !== 'Armor') return false;
            if (currentFilter === 'tools' && item.type !== 'Tool') return false;
            if (currentFilter === 'consumables' && item.type !== 'Consumable') return false;
            if (currentFilter === 'artifacts' && item.type !== 'Artifact') return false;
        }

        // Type dropdown filter
        if (currentTypeFilter !== 'all' && item.type !== currentTypeFilter) return false;

        // Rarity filter
        if (currentRarityFilter !== 'all' && item.rarity !== currentRarityFilter) return false;

        // Search filter
        if (currentSearchTerm && !item.name.toLowerCase().includes(currentSearchTerm)) return false;

        return true;
    });

    // Apply sorting
    sortItems();
    
    // Reset to first page
    currentPage = 1;
    
    // Render table
    renderTable();
    renderPagination();
}

function sortItems() {
    filteredItems.sort((a, b) => {
        let aVal = a[currentSort.column];
        let bVal = b[currentSort.column];

        // Handle numeric columns
        if (['id', 'price'].includes(currentSort.column)) {
            aVal = parseInt(aVal) || 0;
            bVal = parseInt(bVal) || 0;
        }

        // Handle string columns
        if (typeof aVal === 'string') {
            aVal = aVal.toLowerCase();
            bVal = bVal.toLowerCase();
        }

        if (currentSort.direction === 'asc') {
            return aVal > bVal ? 1 : -1;
        } else {
            return aVal < bVal ? 1 : -1;
        }
    });
}

function renderTable() {
    const tbody = document.querySelector('#itemsTable tbody');
    const startIndex = (currentPage - 1) * itemsPerPage;
    const endIndex = startIndex + itemsPerPage;
    const pageItems = filteredItems.slice(startIndex, endIndex);

    if (pageItems.length === 0) {
        tbody.innerHTML = '<tr><td colspan="10" class="empty-state">No items found.</td></tr>';
        return;
    }

    tbody.innerHTML = pageItems.map(item => `
        <tr class="item-row" data-type="${item.type.toLowerCase()}" data-rarity="${item.rarity}">
            <td>${item.id}</td>
            <td><strong>${escapeHtml(item.name)}</strong></td>
            <td><span class="badge-${getTypeClass(item.type)}">${escapeHtml(item.type)}</span></td>
            <td>${escapeHtml(item.category)}</td>
            <td>${escapeHtml(item.damage || 'N/A')}</td>
            <td>${escapeHtml(item.range || 'N/A')}</td>
            <td><span class="badge-${item.rarity}">${escapeHtml(item.rarity)}</span></td>
            <td>$${parseInt(item.price).toLocaleString()}</td>
            <td>${formatDate(item.created_at)}</td>
            <td class="actions">
                <button class="action-btn view-btn" onclick="viewItem(${item.id})" title="View Item">👁️</button>
                <button class="action-btn edit-btn" onclick="editItem(${item.id})" title="Edit Item">✏️</button>
                <button class="action-btn assign-btn" onclick="assignItem(${item.id}, '${escapeHtml(item.name)}')" title="Assign to Characters">🎯</button>
                <button class="action-btn delete-btn" onclick="deleteItem(${item.id}, '${escapeHtml(item.name)}')" title="Delete Item">🗑️</button>
            </td>
        </tr>
    `).join('');

    // Add sorting event listeners
    document.querySelectorAll('#itemsTable th[data-sort]').forEach(th => {
        th.addEventListener('click', function() {
            const column = this.dataset.sort;
            
            if (currentSort.column === column) {
                currentSort.direction = currentSort.direction === 'asc' ? 'desc' : 'asc';
            } else {
                currentSort.column = column;
                currentSort.direction = 'asc';
            }

            // Update sort indicators
            document.querySelectorAll('#itemsTable th').forEach(h => {
                h.classList.remove('sorted-asc', 'sorted-desc');
            });
            this.classList.add(`sorted-${currentSort.direction}`);

            sortItems();
            renderTable();
        });
    });
}

function renderPagination() {
    const totalPages = Math.ceil(filteredItems.length / itemsPerPage);
    const paginationInfo = document.getElementById('paginationInfo');
    const paginationButtons = document.getElementById('paginationButtons');

    if (totalPages <= 1) {
        paginationInfo.textContent = `Showing ${filteredItems.length} items`;
        paginationButtons.innerHTML = '';
        return;
    }

    const startItem = (currentPage - 1) * itemsPerPage + 1;
    const endItem = Math.min(currentPage * itemsPerPage, filteredItems.length);
    paginationInfo.textContent = `Showing ${startItem}-${endItem} of ${filteredItems.length} items`;

    let buttons = '';
    
    // Previous button
    if (currentPage > 1) {
        buttons += `<button class="page-btn" onclick="goToPage(${currentPage - 1})">‹ Previous</button>`;
    }

    // Page numbers
    const startPage = Math.max(1, currentPage - 2);
    const endPage = Math.min(totalPages, currentPage + 2);

    if (startPage > 1) {
        buttons += `<button class="page-btn" onclick="goToPage(1)">1</button>`;
        if (startPage > 2) {
            buttons += `<span style="padding: 8px 12px; color: #b8a090;">...</span>`;
        }
    }

    for (let i = startPage; i <= endPage; i++) {
        const activeClass = i === currentPage ? 'active' : '';
        buttons += `<button class="page-btn ${activeClass}" onclick="goToPage(${i})">${i}</button>`;
    }

    if (endPage < totalPages) {
        if (endPage < totalPages - 1) {
            buttons += `<span style="padding: 8px 12px; color: #b8a090;">...</span>`;
        }
        buttons += `<button class="page-btn" onclick="goToPage(${totalPages})">${totalPages}</button>`;
    }

    // Next button
    if (currentPage < totalPages) {
        buttons += `<button class="page-btn" onclick="goToPage(${currentPage + 1})">Next ›</button>`;
    }

    paginationButtons.innerHTML = buttons;
}

function goToPage(page) {
    currentPage = page;
    renderTable();
    renderPagination();
}

// CRUD Operations
async function handleFormSubmit(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const data = Object.fromEntries(formData.entries());
    
    // Clean up data
    Object.keys(data).forEach(key => {
        if (data[key] === '') data[key] = null;
    });

    // Validate JSON requirements
    if (data.requirements) {
        try {
            JSON.parse(data.requirements);
        } catch (error) {
            showNotification('Invalid JSON in Requirements field', 'error');
            return;
        }
    }

    const isEdit = data.id && data.id !== '';
    const url = 'api_admin_items_crud.php';
    const method = isEdit ? 'PUT' : 'POST';

    try {
        const response = await fetch(url, {
            method: method,
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });

        const result = await response.json();

        if (result.success) {
            showNotification(result.message, 'success');
            closeItemModal();
            loadItems(); // Reload items
        } else {
            showNotification(result.message || 'Failed to save item', 'error');
        }
    } catch (error) {
        console.error('Error saving item:', error);
        showNotification('Failed to save item', 'error');
    }
}

function openAddItemModal() {
    document.getElementById('itemModalTitle').textContent = 'Add New Item';
    document.getElementById('itemForm').reset();
    document.getElementById('itemId').value = '';
    document.getElementById('itemModal').classList.add('active');
}

function editItem(itemId) {
    const item = allItems.find(i => i.id == itemId);
    if (!item) return;

    document.getElementById('itemModalTitle').textContent = 'Edit Item';
    document.getElementById('itemId').value = item.id;
    document.getElementById('itemName').value = item.name;
    document.getElementById('itemType').value = item.type;
    document.getElementById('itemCategory').value = item.category;
    document.getElementById('itemDamage').value = item.damage || '';
    document.getElementById('itemRange').value = item.range || '';
    document.getElementById('itemRarity').value = item.rarity;
    document.getElementById('itemPrice').value = item.price;
    document.getElementById('itemDescription').value = item.description;
    document.getElementById('itemRequirements').value = item.requirements ? JSON.stringify(item.requirements, null, 2) : '';
    document.getElementById('itemImage').value = item.image || '';
    document.getElementById('itemNotes').value = item.notes || '';
    
    document.getElementById('itemModal').classList.add('active');
}

function viewItem(itemId) {
    const item = allItems.find(i => i.id == itemId);
    if (!item) return;

    document.getElementById('viewItemName').textContent = item.name;
    
    const content = `
        <div class="info-grid">
            <div>
                <h3>Basic Information</h3>
                <p><strong>Name:</strong> ${escapeHtml(item.name)}</p>
                <p><strong>Type:</strong> <span class="badge-${getTypeClass(item.type)}">${escapeHtml(item.type)}</span></p>
                <p><strong>Category:</strong> ${escapeHtml(item.category)}</p>
                <p><strong>Rarity:</strong> <span class="badge-${item.rarity}">${escapeHtml(item.rarity)}</span></p>
                <p><strong>Price:</strong> $${parseInt(item.price).toLocaleString()}</p>
            </div>
            <div>
                <h3>Combat Stats</h3>
                <p><strong>Damage:</strong> ${escapeHtml(item.damage || 'N/A')}</p>
                <p><strong>Range:</strong> ${escapeHtml(item.range || 'N/A')}</p>
                <p><strong>Requirements:</strong></p>
                <pre style="background: rgba(139, 0, 0, 0.2); padding: 10px; border-radius: 4px; margin-top: 5px;">${item.requirements ? JSON.stringify(item.requirements, null, 2) : 'None'}</pre>
            </div>
        </div>
        <div>
            <h3>Description</h3>
            <p>${escapeHtml(item.description)}</p>
        </div>
        ${item.notes ? `
        <div>
            <h3>Notes</h3>
            <p>${escapeHtml(item.notes)}</p>
        </div>
        ` : ''}
        ${item.image ? `
        <div>
            <h3>Image</h3>
            <img src="${escapeHtml(item.image)}" alt="${escapeHtml(item.name)}" style="max-width: 200px; border-radius: 4px;">
        </div>
        ` : ''}
    `;
    
    document.getElementById('viewItemContent').innerHTML = content;
    document.getElementById('viewModal').classList.add('active');
}

function assignItem(itemId, itemName) {
    currentItemId = itemId;
    document.getElementById('assignItemName').textContent = itemName;
    
    // Reset character selections
    document.querySelectorAll('.character-item').forEach(item => {
        item.classList.remove('selected');
        item.querySelector('.quantity-input').value = 1;
    });
    
    document.getElementById('assignModal').classList.add('active');
}

async function assignItemsToCharacters() {
    const selectedCharacters = document.querySelectorAll('.character-item.selected');
    
    if (selectedCharacters.length === 0) {
        showNotification('Please select at least one character', 'error');
        return;
    }

    const assignments = [];
    selectedCharacters.forEach(char => {
        const charId = char.dataset.characterId;
        const quantity = parseInt(char.querySelector('.quantity-input').value);
        assignments.push({ character_id: charId, quantity });
    });

    try {
        const promises = assignments.map(assignment => 
            fetch('api_admin_add_equipment.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    character_id: assignment.character_id,
                    item_id: currentItemId,
                    quantity: assignment.quantity
                })
            })
        );

        const results = await Promise.all(promises);
        const responses = await Promise.all(results.map(r => r.json()));

        const successCount = responses.filter(r => r.success).length;
        
        if (successCount === assignments.length) {
            showNotification(`Item assigned to ${successCount} character(s) successfully!`, 'success');
            closeAssignModal();
        } else {
            showNotification(`Item assigned to ${successCount} of ${assignments.length} character(s)`, 'error');
        }
    } catch (error) {
        console.error('Error assigning items:', error);
        showNotification('Failed to assign items', 'error');
    }
}

function deleteItem(itemId, itemName) {
    currentItemId = itemId;
    document.getElementById('deleteItemName').textContent = itemName;
    
    // Check if item is assigned to characters by querying the database directly
    fetch(`api_admin_items_crud.php?check_assignments=${itemId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.assignment_count > 0) {
                document.getElementById('deleteWarning').style.display = 'block';
                document.getElementById('deleteWarning').innerHTML = 
                    `⚠️ <strong>This item is assigned to ${data.assignment_count} character(s)</strong> - remove assignments first!`;
            } else {
                document.getElementById('deleteWarning').style.display = 'none';
            }
        })
        .catch(error => {
            console.error('Error checking assignments:', error);
            document.getElementById('deleteWarning').style.display = 'none';
        });
    
    document.getElementById('deleteModal').classList.add('active');
}

async function confirmDelete() {
    try {
        const response = await fetch('api_admin_items_crud.php', {
            method: 'DELETE',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: currentItemId })
        });

        const result = await response.json();

        if (result.success) {
            showNotification(result.message, 'success');
            closeDeleteModal();
            loadItems(); // Reload items
        } else {
            showNotification(result.message || 'Failed to delete item', 'error');
        }
    } catch (error) {
        console.error('Error deleting item:', error);
        showNotification('Failed to delete item', 'error');
    }
}

// Modal functions
function closeItemModal() {
    document.getElementById('itemModal').classList.remove('active');
}

function closeViewModal() {
    document.getElementById('viewModal').classList.remove('active');
}

function closeAssignModal() {
    document.getElementById('assignModal').classList.remove('active');
    currentItemId = null;
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.remove('active');
    currentItemId = null;
}

// Character selection in assign modal
document.addEventListener('click', function(e) {
    if (e.target.closest('.character-item')) {
        const item = e.target.closest('.character-item');
        item.classList.toggle('selected');
    }
});

// Set up delete confirmation
document.getElementById('confirmDeleteBtn').addEventListener('click', confirmDelete);

// Utility functions
function getTypeClass(type) {
    const typeMap = {
        'Weapon': 'weapon',
        'Armor': 'armor', 
        'Tool': 'tool',
        'Consumable': 'consumable',
        'Artifact': 'artifact',
        'Misc': 'misc',
        'Miscellaneous': 'misc',
        'Equipment': 'equipment',
        'Accessory': 'accessory',
        'Clothing': 'clothing',
        'Vehicle': 'vehicle',
        'Book': 'book',
        'Food': 'food',
        'Drink': 'drink',
        'Drug': 'drug',
        'Electronic': 'electronic'
    };
    return typeMap[type] || 'misc';
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric' 
    });
}

function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.textContent = message;
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 3000);
}
