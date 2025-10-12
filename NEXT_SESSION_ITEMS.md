# 🗡️ Valley by Night - Items Database Next Session

**Date:** October 12, 2025  
**Current Version:** v0.6.0 ✅ PUSHED  
**Next Focus:** Items Database Management

---

## 📊 Current Status - v0.6.0

### ✅ Completed This Session:

**Home Page Rebuild (v0.5.0):**
- Gothic header/footer components
- Role-based home dashboard (Player & Admin views)
- File reorganization (88 → 18 root files)
- Session authentication & role detection
- Live character statistics

**Admin Panel (v0.6.0):**
- Complete character management interface
- List, filter (All/PCs/NPCs), search, sort
- Pagination (20/50/100 per page)
- View modal (Compact/Full toggle)
- Edit integration (→ character creator)
- Delete with confirmation & CASCADE
- Status tracking (Draft/Finalized/Active/Dead/Missing)
- Universal paths for subfolders

---

## 🎯 Next Session: Items Database

### Current Files:

**Admin Page:**
- `admin/admin_equipment.php` - Needs gothic rebuild

**API Endpoints:**
- `admin/api_admin_add_equipment.php`
- `admin/api_admin_update_equipment.php`
- `admin/api_admin_remove_equipment.php`
- `admin/api_get_equipment.php`

**Data Files:**
- `data/Item Database Example.json`
- `data/Items Database.json`
- `data/Items Database_v2.json`
- `data/Items Database-Mundane.json`

**Database Tables:**
- `items` - Master items table
- `character_equipment` - Links items to characters

---

## 🗡️ Items Database Features to Build

### 1. Items List Interface
- Table displaying all items
- Columns: ID, Name, Type, Category, Rarity, Description
- Gothic styling matching admin panel
- Sortable columns

### 2. Filter System
- **By Type:** All, Weapons, Armor, Mundane, Supernatural
- **By Rarity:** Common, Uncommon, Rare, Unique
- Active state styling

### 3. Search & Pagination
- Real-time search by item name
- 20/50/100 items per page
- Page navigation buttons

### 4. Item Management
- **Add Item** - Form to create new items
- **Edit Item** - Modify existing items
- **Delete Item** - With confirmation
- **View Details** - Modal popup

### 5. JSON Import
- Button to import items from JSON files
- Select which JSON file to import
- Preview before import
- Batch import with progress

### 6. Character Assignment
- Assign items to characters
- Track quantity/equipped status
- View which characters have which items

### 7. Item Categories
**Weapons:**
- Melee (swords, stakes, knives)
- Ranged (guns, bows, crossbows)
- Improvised

**Armor:**
- Light, Medium, Heavy
- Modern tactical gear

**Mundane:**
- Tools, supplies, vehicles
- Everyday items

**Supernatural:**
- Artifacts, talismans
- Blood magic items
- Ritual components

---

## 📋 Database Structure to Check

```sql
-- Check items table structure
DESCRIBE items;

-- Check character_equipment table
DESCRIBE character_equipment;

-- Sample query
SELECT * FROM items LIMIT 10;
```

### Expected Fields:
- `id` - Primary key
- `name` - Item name
- `type` - Weapon/Armor/Mundane/Supernatural
- `category` - Subcategory
- `description` - Item details
- `rarity` - Common/Uncommon/Rare/Unique
- `weight`, `value`, `properties` (depends on table structure)

---

## 🎨 Design Specifications

**Match existing gothic theme:**
- Dark red backgrounds (#1a0f0f, #2a1515)
- Blood red accents (#8B0000)
- Cream text (#f5e6d3)
- Card-based layout
- Hover effects
- Responsive design

**UI Layout:**
```
╔════════════════════════════════════════════════╗
║  🗡️ Items Database                            ║
╠════════════════════════════════════════════════╣
║  [Stats: Total, Weapons, Armor, Etc.]          ║
╠════════════════════════════════════════════════╣
║  [All][Weapons][Armor][Mundane] 🔍 Search      ║
║  Per page: [20▼]                               ║
╠════════════════════════════════════════════════╣
║  ID | Name ▲ | Type | Category | Rarity | ... ║
╠════════════════════════════════════════════════╣
║  1  | Stake  | Weapon | Melee | Common | 👁️✏️🗑️ ║
║  2  | Glock  | Weapon | Ranged | Common | ... ║
╠════════════════════════════════════════════════╣
║  Showing 1-20 of 156 | [1] [2] [Next →]       ║
╚════════════════════════════════════════════════╝
```

---

## 📁 Files to Create/Modify

**New Files:**
```
admin/
  ├── admin_equipment.php (rebuild with gothic theme)
  ├── add_item_api.php (create new items)
  ├── update_item_api.php (edit items)
  ├── delete_item_api.php (remove items)
  ├── import_items_api.php (JSON import)
  └── view_item_api.php (item details)

js/
  └── items_database.js (search, filter, sort, pagination)
```

**Existing to Review:**
```
data/
  ├── Items Database.json (master list)
  ├── Items Database_v2.json
  ├── Items Database-Mundane.json
  └── Item Database Example.json

admin/
  ├── api_admin_add_equipment.php (existing)
  ├── api_admin_update_equipment.php (existing)
  ├── api_admin_remove_equipment.php (existing)
  └── api_get_equipment.php (existing)
```

---

## 🔑 Key Considerations

1. **Item Properties:**
   - Damage values for weapons
   - Armor ratings
   - Weight/encumbrance
   - Cost in dollars or resources
   - Special abilities/properties

2. **Vampire-Specific Items:**
   - Stakes (wooden, silver)
   - Ritual components
   - Blood storage
   - Haven security items

3. **Modern Setting (1994):**
   - Firearms common
   - No smartphones
   - Pagers, payphones
   - 90s technology level

4. **Import Considerations:**
   - Validate JSON structure
   - Handle duplicates
   - Map JSON fields to database
   - Show import progress

---

## 🚀 Session Start Checklist

1. ✅ Review `admin/admin_equipment.php` - See current implementation
2. ✅ Check `items` table structure - `DESCRIBE items`
3. ✅ Review item JSON files - See data structure
4. ✅ Create Taskmaster project - `task-master add-tag items-database`
5. ✅ Build new admin_equipment.php with gothic theme

---

## 📖 Reference Documentation

- **Equipment Integration Guide:** `reference/setup-guides/EQUIPMENT_INTEGRATION_GUIDE.md`
- **Item JSONs:** `data/Items Database*.json`
- **Admin Panel Template:** `admin/admin_panel.php` (use as reference)

---

**Ready to build a beautiful gothic items database!** 🗡️🛡️⚔️

*Session completed: October 12, 2025, 2:54 AM*  
*Current Version: v0.6.0*  
*Next Target: v0.7.0 - Items Database*

