# 🗡️ Equipment System Integration Guide

Complete step-by-step guide to integrate the Items Database into your VbN character system.

---

## 📋 **What You Have Now:**

- ✅ `Items Database.json` - 30 items (weapons, armor, tools, magical artifacts)
- ✅ `create_items_tables.sql` - Database table schemas
- ✅ `import_items.php` - Import script to populate database
- ✅ `api_items.php` - Get all items (with filters)
- ✅ `api_get_equipment.php` - Get character's equipment
- ✅ `js/modules/systems/EquipmentManager.js` - Frontend JavaScript module
- ✅ `save_character_final.php` - Updated to save equipment

---

## 🚀 **Step-by-Step Integration:**

### **Step 1: Create Database Tables**

Run the SQL file to create the necessary tables:

```bash
# Option A: Via XAMPP phpMyAdmin
1. Access database at vdb5.pit.pair.com
2. Select your "vbn" database
3. Click "Import" tab
4. Choose "create_items_tables.sql"
5. Click "Go"

# Option B: Via command line
mysql -u root -p vbn < create_items_tables.sql
```

**Expected Result:** Two new tables created:
- `items` - Master catalog of all available items
- `character_equipment` - Links characters to their items

---

### **Step 2: Import Items into Database**

Run the import script to populate the items table:

```bash
# Navigate to your project folder in browser
http://vbn.talkingheads.video/import_items.php
```

**Expected Output:**
```
📦 Starting import of 30 items...
✅ Imported: 9mm Pistol (Firearms)
✅ Imported: .38 Revolver (Firearms)
... (30 items total)
📊 Import Complete:
   ✅ Successfully imported: 30 items
```

**Verify in Database:**
```sql
SELECT COUNT(*) FROM items;  -- Should return 30
SELECT * FROM items LIMIT 5; -- View first 5 items
```

---

### **Step 3: Add Equipment Tab to Character Sheet**

Add the Equipment tab HTML to `lotn_char_create.php`:

```html
<!-- Add this after the Appearance tab button -->
<button class="tab-button" data-tab="equipment">
    <i class="fas fa-swords"></i>
    <span>Equipment</span>
</button>

<!-- Add this tab content section -->
<div id="equipment" class="tab-content">
    <div class="equipment-section">
        <!-- Filter Buttons -->
        <div class="equipment-filters">
            <button class="equipment-filter-btn active" data-category="all">All</button>
            <button class="equipment-filter-btn" data-category="Firearms">Firearms</button>
            <button class="equipment-filter-btn" data-category="Melee">Melee</button>
            <button class="equipment-filter-btn" data-category="Protective Gear">Armor</button>
            <button class="equipment-filter-btn" data-category="Utility">Tools</button>
            <button class="equipment-filter-btn" data-category="Communication">Electronics</button>
            <button class="equipment-filter-btn" data-category="Protection">Magical</button>
        </div>

        <!-- Search Bar -->
        <div class="equipment-search">
            <input type="text" 
                   id="equipment-search" 
                   placeholder="Search items..."
                   class="form-input">
        </div>

        <!-- Two-Column Layout -->
        <div class="equipment-layout">
            <!-- Available Items Catalog -->
            <div class="equipment-catalog-container">
                <h3>Available Items</h3>
                <div id="equipment-catalog" class="equipment-catalog">
                    <!-- Items will be rendered here by JavaScript -->
                </div>
            </div>

            <!-- Character Inventory -->
            <div class="inventory-container">
                <h3>Your Inventory</h3>
                <div id="character-inventory" class="character-inventory">
                    <!-- Character's equipment will be rendered here -->
                </div>
            </div>
        </div>
    </div>
</div>
```

---

### **Step 4: Add Equipment Styles to CSS**

Add these styles to `css/style.css`:

```css
/* Equipment Section */
.equipment-section {
    padding: 20px;
}

.equipment-filters {
    display: flex;
    gap: 10px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}

.equipment-filter-btn {
    padding: 8px 16px;
    border: 2px solid var(--border-color);
    background: var(--bg-secondary);
    color: var(--text-primary);
    border-radius: 4px;
    cursor: pointer;
    transition: all 0.3s;
}

.equipment-filter-btn:hover {
    background: var(--accent-color);
    border-color: var(--accent-color);
}

.equipment-filter-btn.active {
    background: var(--accent-color);
    border-color: var(--accent-color);
    color: white;
}

.equipment-search {
    margin-bottom: 20px;
}

.equipment-layout {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 20px;
}

/* Item Catalog */
.equipment-catalog {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 15px;
    max-height: 600px;
    overflow-y: auto;
}

.equipment-item {
    border: 2px solid var(--border-color);
    border-radius: 8px;
    padding: 15px;
    background: var(--bg-secondary);
    transition: all 0.3s;
}

.equipment-item:hover {
    border-color: var(--accent-color);
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

.equipment-item[data-rarity="uncommon"] {
    border-left: 4px solid #4CAF50;
}

.equipment-item[data-rarity="rare"] {
    border-left: 4px solid #2196F3;
}

.item-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}

.item-header h4 {
    margin: 0;
    color: var(--accent-color);
}

.item-type {
    font-size: 0.85em;
    color: var(--text-secondary);
}

.item-stats {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    margin-bottom: 10px;
    font-size: 0.9em;
}

.item-stats .stat {
    padding: 2px 8px;
    background: var(--bg-primary);
    border-radius: 3px;
}

.rarity-common { color: #9E9E9E; }
.rarity-uncommon { color: #4CAF50; }
.rarity-rare { color: #2196F3; }

.item-description {
    font-size: 0.9em;
    line-height: 1.4;
    margin-bottom: 10px;
    color: var(--text-secondary);
}

.item-requirements {
    display: flex;
    gap: 5px;
    flex-wrap: wrap;
    margin-bottom: 10px;
}

.req-badge {
    font-size: 0.8em;
    padding: 2px 6px;
    background: var(--accent-color);
    color: white;
    border-radius: 3px;
}

.item-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 10px;
    border-top: 1px solid var(--border-color);
}

.item-price {
    font-weight: bold;
    color: var(--accent-color);
}

.add-item-btn {
    padding: 5px 15px;
    background: var(--accent-color);
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    transition: all 0.3s;
}

.add-item-btn:hover {
    background: var(--accent-hover);
    transform: scale(1.05);
}

.item-notes {
    display: block;
    margin-top: 8px;
    font-style: italic;
    color: var(--text-secondary);
    font-size: 0.85em;
}

/* Character Inventory */
.character-inventory {
    max-height: 600px;
    overflow-y: auto;
}

.inventory-item {
    padding: 15px;
    background: var(--bg-secondary);
    border: 2px solid var(--border-color);
    border-radius: 8px;
    margin-bottom: 10px;
}

.inventory-item .item-info {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}

.inventory-item .item-category {
    font-size: 0.85em;
    color: var(--text-secondary);
}

.inventory-item .item-actions {
    display: flex;
    gap: 10px;
    align-items: center;
    flex-wrap: wrap;
}

.item-quantity {
    width: 60px;
    padding: 5px;
    border: 1px solid var(--border-color);
    border-radius: 4px;
    background: var(--bg-primary);
    color: var(--text-primary);
}

.remove-item-btn {
    padding: 5px 10px;
    background: #f44336;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}

.remove-item-btn:hover {
    background: #d32f2f;
}

.no-items {
    text-align: center;
    padding: 40px;
    color: var(--text-secondary);
    font-style: italic;
}
```

---

### **Step 5: Initialize Equipment Manager**

Update `js/modules/main.js` to import and initialize EquipmentManager:

```javascript
import { EquipmentManager } from './systems/EquipmentManager.js';

// Add to initialization
const equipmentManager = new EquipmentManager();
await equipmentManager.init();

// Add to managers object
window.CharacterManagers = {
    ...window.CharacterManagers,
    equipment: equipmentManager
};
```

---

### **Step 6: Update Save Handler**

The `save_character_final.php` has already been updated. Just ensure your save function in JavaScript includes equipment:

```javascript
const characterData = {
    // ... existing character data
    equipment: window.CharacterManagers.equipment.getData()
};
```

---

## 🧪 **Testing the Integration:**

### **Test 1: Verify Database**
```sql
-- Check items imported
SELECT COUNT(*) FROM items;

-- View item categories
SELECT category, COUNT(*) as count 
FROM items 
GROUP BY category;

-- Check tables exist
SHOW TABLES LIKE '%equipment%';
```

### **Test 2: API Endpoints**
```bash
# Get all items
http://vbn.talkingheads.video/api_items.php

# Filter by category
http://vbn.talkingheads.video/api_items.php?category=Firearms

# Search items
http://vbn.talkingheads.video/api_items.php?search=pistol
```

### **Test 3: Frontend**
1. Open character creation page
2. Click Equipment tab
3. Verify items load
4. Click category filters (Firearms, Melee, etc.)
5. Search for an item
6. Add items to inventory
7. Save character
8. Reload - verify equipment persists

---

## 📊 **Database Structure Overview:**

```
items (Master Catalog)
├── id (INT, AUTO_INCREMENT)
├── name (VARCHAR)
├── type (VARCHAR) - Weapon, Armor, Tool, etc.
├── category (VARCHAR) - Firearms, Melee, Protective Gear, etc.
├── damage (VARCHAR)
├── range (VARCHAR)
├── requirements (JSON) - {"Physical": 2, "Firearms": 1}
├── description (TEXT)
├── rarity (VARCHAR) - common, uncommon, rare
├── price (INT)
├── image (VARCHAR)
├── notes (TEXT)
└── created_at (TIMESTAMP)

character_equipment (Character → Items Link)
├── id (INT, AUTO_INCREMENT)
├── character_id (INT, FK → characters.id)
├── item_id (INT, FK → items.id)
├── quantity (INT)
├── equipped (TINYINT)
├── custom_notes (TEXT)
├── created_at (TIMESTAMP)
└── updated_at (TIMESTAMP)
```

---

## 🎨 **Customization Ideas:**

### **Add More Items:**
1. Edit `Items Database.json`
2. Re-run `import_items.php` (or add via SQL INSERT)

### **Add Item Images:**
1. Create `/images/items/` folder
2. Add item images (e.g., `9mm-pistol.png`)
3. Update database: `UPDATE items SET image='9mm-pistol.png' WHERE name='9mm Pistol'`
4. Update CSS to display images in catalog

### **Add Weight/Encumbrance System:**
```sql
ALTER TABLE items ADD COLUMN weight DECIMAL(5,2) DEFAULT 0;
```

### **Add Item Bonuses Tracking:**
Create a system to apply item bonuses (e.g., +1 to Perception from Maglite)

---

## ✅ **Integration Checklist:**

- [ ] Database tables created (`items`, `character_equipment`)
- [ ] Items imported (30 items in database)
- [ ] API endpoints working (`api_items.php`, `api_get_equipment.php`)
- [ ] Equipment tab added to HTML
- [ ] CSS styles added
- [ ] EquipmentManager initialized
- [ ] Save handler updated
- [ ] Tested adding items to character
- [ ] Tested saving and loading character with equipment
- [ ] SFTP sync configured for deployment

---

## 🐛 **Troubleshooting:**

**Items not loading?**
- Check browser console for errors
- Verify `api_items.php` returns JSON
- Check database connection

**Can't add items?**
- Verify EquipmentManager is initialized
- Check for JavaScript errors
- Ensure character_equipment table exists

**Equipment not saving?**
- Check save_character_final.php has equipment code
- Verify JSON payload includes 'equipment' array
- Check MySQL error logs

---

**🎉 You're all set! Your VbN character system now has a fully functional equipment/inventory system!**

