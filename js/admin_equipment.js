/**
 * Admin Equipment Manager JavaScript
 */

let selectedCharacterId = null;
let allItems = [];

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Load all items from PHP data
    if (typeof itemsData !== 'undefined') {
        allItems = itemsData;
    }
    
    initializeCharacterSelection();
    initializeTabs();
    initializeSearch();
    initializeCategoryToggles();
});

// Character selection
function initializeCharacterSelection() {
    document.querySelectorAll('.character-item').forEach(item => {
        item.addEventListener('click', function() {
            // Remove active class from all
            document.querySelectorAll('.character-item').forEach(i => i.classList.remove('active'));
            
            // Add active to clicked
            this.classList.add('active');
            
            // Set selected character
            selectedCharacterId = this.dataset.characterId;
            const characterName = this.querySelector('h4').textContent;
            
            // Show equipment interface
            document.getElementById('no-character-selected').style.display = 'none';
            document.getElementById('equipment-interface').style.display = 'block';
            document.getElementById('selected-character-name').textContent = characterName;
            
            // Load character's equipment
            loadCharacterEquipment();
        });
    });
}

// Tab switching
function initializeTabs() {
    document.querySelectorAll('.equipment-tab-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const tab = this.dataset.tab;
            
            // Update buttons
            document.querySelectorAll('.equipment-tab-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            // Update content
            document.querySelectorAll('.equipment-tab-content').forEach(c => c.classList.remove('active'));
            document.getElementById('tab-' + tab).classList.add('active');
        });
    });
}

// Initialize search functionality
function initializeSearch() {
    // Character search
    document.getElementById('character-search').addEventListener('input', function(e) {
        const search = e.target.value.toLowerCase();
        document.querySelectorAll('.character-item').forEach(item => {
            const text = item.textContent.toLowerCase();
            item.style.display = text.includes(search) ? 'block' : 'none';
        });
    });

    // Type filter dropdown
    document.getElementById('type-filter').addEventListener('change', function(e) {
        filterItems();
    });

    // Item search
    document.getElementById('item-search').addEventListener('input', function(e) {
        filterItems();
    });
}

// Filter items by both type and search term
function filterItems() {
    const typeFilter = document.getElementById('type-filter').value.toLowerCase();
    const searchTerm = document.getElementById('item-search').value.toLowerCase();
    
    // Filter cards
    document.querySelectorAll('.add-item-card').forEach(card => {
        const text = card.textContent.toLowerCase();
        const itemType = card.dataset.itemType ? card.dataset.itemType.toLowerCase() : '';
        
        // Check type filter
        const typeMatch = (typeFilter === 'all') || (itemType === typeFilter);
        
        // Check search filter
        const searchMatch = text.includes(searchTerm);
        
        // Show only if both match
        card.style.display = (typeMatch && searchMatch) ? 'block' : 'none';
    });
    
    // Show/hide category sections based on visible items
    document.querySelectorAll('.category-section').forEach(section => {
        const visibleCards = section.querySelectorAll('.add-item-card[style*="display: block"], .add-item-card:not([style*="display: none"])');
        section.style.display = visibleCards.length > 0 ? 'block' : 'none';
    });
}

// Category collapse/expand
function initializeCategoryToggles() {
    document.querySelectorAll('.category-header').forEach(header => {
        header.addEventListener('click', function() {
            const items = this.nextElementSibling;
            const icon = this.querySelector('i');
            
            if (items.style.display === 'none') {
                items.style.display = 'grid';
                icon.className = 'fas fa-caret-down';
            } else {
                items.style.display = 'none';
                icon.className = 'fas fa-caret-right';
            }
        });
    });
}

// Load character equipment
async function loadCharacterEquipment() {
    try {
        const response = await fetch(`api_get_equipment.php?character_id=${selectedCharacterId}`);
        const data = await response.json();
        
        const grid = document.getElementById('current-inventory-grid');
        
        if (!data.success || data.equipment.length === 0) {
            grid.innerHTML = '<div class="empty-state"><i class="fas fa-box-open"></i><p>No items in inventory</p></div>';
            return;
        }
        
        grid.innerHTML = data.equipment.map(item => `
            <div class="inventory-item-card">
                <h4>${item.name}</h4>
                <div class="item-meta">
                    <span>${item.category}</span>
                    ${item.damage != 'N/A' ? `<span>⚔️ ${item.damage}</span>` : ''}
                </div>
                <p style="font-size: 0.9em; margin: 8px 0;">${item.description.substring(0, 80)}...</p>
                <div class="item-controls">
                    <div class="quantity-control">
                        <label>Qty:</label>
                        <input type="number" value="${item.quantity}" min="1" 
                               onchange="updateQuantity(${item.id}, this.value)">
                    </div>
                    <label>
                        <input type="checkbox" ${item.equipped ? 'checked' : ''} 
                               onchange="toggleEquipped(${item.id}, this.checked)">
                        Equipped
                    </label>
                    <button class="remove-item-btn" onclick="removeItemFromCharacter(${item.id})">
                        <i class="fas fa-trash"></i> Remove
                    </button>
                </div>
            </div>
        `).join('');
        
    } catch (error) {
        console.error('Error loading equipment:', error);
        showNotification('Failed to load equipment', 'error');
    }
}

// Add item to character
async function addItemToCharacter(itemId) {
    if (!selectedCharacterId) {
        showNotification('Please select a character first', 'error');
        return;
    }
    
    try {
        const response = await fetch('api_admin_add_equipment.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                character_id: selectedCharacterId,
                item_id: itemId,
                quantity: 1
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification('Item added successfully!', 'success');
            loadCharacterEquipment();
        } else {
            showNotification(data.message || 'Failed to add item', 'error');
        }
    } catch (error) {
        console.error('Error adding item:', error);
        showNotification('Failed to add item', 'error');
    }
}

// Remove item from character
async function removeItemFromCharacter(equipmentId) {
    if (!confirm('Remove this item from character?')) return;
    
    try {
        const response = await fetch('api_admin_remove_equipment.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ equipment_id: equipmentId })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification('Item removed successfully!', 'success');
            loadCharacterEquipment();
        } else {
            showNotification(data.message || 'Failed to remove item', 'error');
        }
    } catch (error) {
        console.error('Error removing item:', error);
        showNotification('Failed to remove item', 'error');
    }
}

// Update quantity
async function updateQuantity(equipmentId, quantity) {
    try {
        const response = await fetch('api_admin_update_equipment.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                equipment_id: equipmentId,
                quantity: parseInt(quantity)
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification('Quantity updated!', 'success');
        } else {
            showNotification('Failed to update quantity', 'error');
        }
    } catch (error) {
        console.error('Error updating quantity:', error);
    }
}

// Toggle equipped status
async function toggleEquipped(equipmentId, equipped) {
    try {
        const response = await fetch('api_admin_update_equipment.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                equipment_id: equipmentId,
                equipped: equipped ? 1 : 0
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification('Equipment status updated!', 'success');
        } else {
            showNotification('Failed to update status', 'error');
        }
    } catch (error) {
        console.error('Error updating status:', error);
    }
}

// Show notification
function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.textContent = message;
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 3000);
}

