/**
 * Equipment Manager Module
 * Manages character equipment inventory
 */

import { EventManager } from '../core/EventManager.js';
import { NotificationManager } from '../core/NotificationManager.js';

export class EquipmentManager {
    constructor() {
        this.allItems = [];
        this.characterEquipment = [];
        this.currentFilter = 'all';
        this.searchTerm = '';
    }

    /**
     * Initialize the equipment system
     */
    async init() {
        console.log('Initializing Equipment Manager...');
        
        // Load all available items from database
        await this.loadAllItems();
        
        // Load character's current equipment if editing
        const characterId = document.getElementById('character_id')?.value;
        if (characterId) {
            await this.loadCharacterEquipment(characterId);
        }
        
        // Setup UI event handlers
        this.setupEventHandlers();
        
        // Render initial state
        this.renderItemCatalog();
        this.renderCharacterInventory();
        
        console.log('Equipment Manager initialized');
    }

    /**
     * Load all available items from database
     */
    async loadAllItems() {
        try {
            const response = await fetch('api_items.php');
            const data = await response.json();
            
            if (data.success) {
                this.allItems = data.items;
                console.log(`Loaded ${data.count} items from database`);
            } else {
                throw new Error('Failed to load items');
            }
        } catch (error) {
            console.error('Error loading items:', error);
            NotificationManager.show('Failed to load items', 'error');
        }
    }

    /**
     * Load character's current equipment
     */
    async loadCharacterEquipment(characterId) {
        try {
            const response = await fetch(`api_get_equipment.php?character_id=${characterId}`);
            const data = await response.json();
            
            if (data.success) {
                this.characterEquipment = data.equipment;
                console.log(`Loaded ${data.equipment.length} items for character`);
            }
        } catch (error) {
            console.error('Error loading character equipment:', error);
        }
    }

    /**
     * Setup event handlers for UI interactions
     */
    setupEventHandlers() {
        // Category filter buttons
        document.querySelectorAll('.equipment-filter-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                this.currentFilter = e.target.dataset.category;
                this.updateFilterButtons();
                this.renderItemCatalog();
            });
        });

        // Search input
        const searchInput = document.getElementById('equipment-search');
        if (searchInput) {
            searchInput.addEventListener('input', (e) => {
                this.searchTerm = e.target.value.toLowerCase();
                this.renderItemCatalog();
            });
        }

        // Add to inventory buttons (delegated event)
        document.getElementById('equipment-catalog')?.addEventListener('click', (e) => {
            if (e.target.classList.contains('add-item-btn')) {
                const itemId = parseInt(e.target.dataset.itemId);
                this.addItemToInventory(itemId);
            }
        });

        // Remove from inventory buttons (delegated event)
        document.getElementById('character-inventory')?.addEventListener('click', (e) => {
            if (e.target.classList.contains('remove-item-btn')) {
                const itemId = parseInt(e.target.dataset.itemId);
                this.removeItemFromInventory(itemId);
            }
        });
    }

    /**
     * Update filter button active states
     */
    updateFilterButtons() {
        document.querySelectorAll('.equipment-filter-btn').forEach(btn => {
            if (btn.dataset.category === this.currentFilter) {
                btn.classList.add('active');
            } else {
                btn.classList.remove('active');
            }
        });
    }

    /**
     * Filter items based on current filter and search
     */
    getFilteredItems() {
        return this.allItems.filter(item => {
            // Category filter
            if (this.currentFilter !== 'all' && item.category !== this.currentFilter) {
                return false;
            }
            
            // Search filter
            if (this.searchTerm && !item.name.toLowerCase().includes(this.searchTerm)) {
                return false;
            }
            
            return true;
        });
    }

    /**
     * Render the item catalog
     */
    renderItemCatalog() {
        const catalogElement = document.getElementById('equipment-catalog');
        if (!catalogElement) return;

        const filteredItems = this.getFilteredItems();

        if (filteredItems.length === 0) {
            catalogElement.innerHTML = '<div class="no-items">No items found</div>';
            return;
        }

        const html = filteredItems.map(item => `
            <div class="equipment-item" data-rarity="${item.rarity}">
                <div class="item-header">
                    <h4>${item.name}</h4>
                    <span class="item-type">${item.type}</span>
                </div>
                <div class="item-stats">
                    ${item.damage !== 'N/A' ? `<span class="stat">Damage: ${item.damage}</span>` : ''}
                    <span class="stat">Range: ${item.range}</span>
                    <span class="stat rarity-${item.rarity}">${item.rarity}</span>
                </div>
                <p class="item-description">${item.description}</p>
                ${this.renderRequirements(item.requirements)}
                <div class="item-footer">
                    <span class="item-price">$${item.price}</span>
                    <button class="add-item-btn" data-item-id="${item.id}">
                        Add to Inventory
                    </button>
                </div>
                ${item.notes ? `<small class="item-notes">${item.notes}</small>` : ''}
            </div>
        `).join('');

        catalogElement.innerHTML = html;
    }

    /**
     * Render requirements badges
     */
    renderRequirements(requirements) {
        if (!requirements || Object.keys(requirements).length === 0) {
            return '';
        }

        const badges = Object.entries(requirements)
            .map(([key, value]) => `<span class="req-badge">${key} ${value}</span>`)
            .join('');

        return `<div class="item-requirements">${badges}</div>`;
    }

    /**
     * Render character's inventory
     */
    renderCharacterInventory() {
        const inventoryElement = document.getElementById('character-inventory');
        if (!inventoryElement) return;

        if (this.characterEquipment.length === 0) {
            inventoryElement.innerHTML = '<div class="no-items">No items in inventory</div>';
            return;
        }

        const html = this.characterEquipment.map(equip => {
            const item = this.allItems.find(i => i.id === equip.item_id);
            if (!item) return '';

            return `
                <div class="inventory-item">
                    <div class="item-info">
                        <strong>${item.name}</strong>
                        <span class="item-category">${item.category}</span>
                    </div>
                    <div class="item-actions">
                        <input type="number" 
                               class="item-quantity" 
                               value="${equip.quantity}" 
                               min="1" 
                               data-item-id="${item.id}">
                        <label>
                            <input type="checkbox" 
                                   class="item-equipped" 
                                   ${equip.equipped ? 'checked' : ''} 
                                   data-item-id="${item.id}">
                            Equipped
                        </label>
                        <button class="remove-item-btn" data-item-id="${item.id}">Remove</button>
                    </div>
                </div>
            `;
        }).join('');

        inventoryElement.innerHTML = html;
    }

    /**
     * Add item to character inventory
     */
    addItemToInventory(itemId) {
        const item = this.allItems.find(i => i.id === itemId);
        if (!item) return;

        // Check if item already in inventory
        const existing = this.characterEquipment.find(e => e.item_id === itemId);
        if (existing) {
            existing.quantity++;
        } else {
            this.characterEquipment.push({
                item_id: itemId,
                quantity: 1,
                equipped: false,
                custom_notes: ''
            });
        }

        this.renderCharacterInventory();
        NotificationManager.show(`Added ${item.name} to inventory`, 'success');
        
        // Emit event for other managers
        EventManager.emit('equipment:changed', this.characterEquipment);
    }

    /**
     * Remove item from character inventory
     */
    removeItemFromInventory(itemId) {
        const index = this.characterEquipment.findIndex(e => e.item_id === itemId);
        if (index > -1) {
            const item = this.allItems.find(i => i.id === itemId);
            this.characterEquipment.splice(index, 1);
            this.renderCharacterInventory();
            NotificationManager.show(`Removed ${item.name} from inventory`, 'info');
            
            // Emit event
            EventManager.emit('equipment:changed', this.characterEquipment);
        }
    }

    /**
     * Get equipment data for saving
     */
    getData() {
        return this.characterEquipment;
    }

    /**
     * Validate equipment data
     */
    validate() {
        // Equipment is optional, always valid
        return {
            valid: true,
            errors: []
        };
    }
}

