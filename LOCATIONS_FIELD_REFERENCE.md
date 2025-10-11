# 🗺️ Locations System - Field Reference

Complete field reference for the VbN Locations system.

---

## **📋 Database Tables Overview:**

### **1. Main `locations` Table**
### **2. `location_ownership` Junction Table** 
### **3. `location_items` Junction Table**
### **4. `location_access` Permission Table**

---

## **🗄️ Locations Table - Complete Field List:**

### **Basic Information Fields:**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `id` | INT AUTO_INCREMENT | Yes | Primary key |
| `name` | VARCHAR(255) | Yes | Location name |
| `type` | VARCHAR(100) | Yes | Dropdown: Haven, Elysium, Domain, Hunting Ground, Gathering Place, Business, Chantry, Temple, Wilderness, Other |
| `summary` | VARCHAR(500) | No | Short one-line description |
| `description` | TEXT | No | Full POV-style description |
| `notes` | TEXT | No | GM/admin notes (private) |
| `status` | VARCHAR(50) | Yes | Dropdown: Active, Abandoned, Destroyed, Under Construction, Contested, Hidden |
| `status_notes` | VARCHAR(255) | No | Additional status details |

### **Geography Fields:**

| Field | Type | Description |
|-------|------|-------------|
| `district` | VARCHAR(100) | Phoenix area/neighborhood |
| `address` | VARCHAR(255) | Optional street address |
| `latitude` | DECIMAL(10,8) | GPS coordinate |
| `longitude` | DECIMAL(11,8) | GPS coordinate |

### **Ownership & Control Fields:**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `owner_type` | VARCHAR(50) | Yes | Dropdown: Individual, Coterie, Clan, Faction, Contested, Public |
| `owner_notes` | TEXT | No | Who specifically owns/controls |
| `faction` | VARCHAR(50) | No | Dropdown: Camarilla, Anarch, Independent, Sabbat, Mortal, None |
| `access_control` | VARCHAR(50) | Yes | Dropdown: Public, Open, Restricted, Private, Threshold, Elysium |
| `access_notes` | TEXT | No | Specific access rules/requirements |

### **Security Features Fields:**

| Field | Type | Default | Description |
|-------|------|---------|-------------|
| `security_level` | INT(1) | 3 | Radio 1-5: Minimal to Maximum |
| `security_locks` | TINYINT(1) | 0 | Checkbox: Has locks & deadbolts |
| `security_alarms` | TINYINT(1) | 0 | Checkbox: Has alarm systems |
| `security_guards` | TINYINT(1) | 0 | Checkbox: Has guards/security |
| `security_hidden_entrance` | TINYINT(1) | 0 | Checkbox: Has hidden entrances |
| `security_sunlight_protected` | TINYINT(1) | 0 | Checkbox: Protected from sunlight |
| `security_warding_rituals` | TINYINT(1) | 0 | Checkbox: Has warding rituals |
| `security_cameras` | TINYINT(1) | 0 | Checkbox: Has security cameras |
| `security_reinforced` | TINYINT(1) | 0 | Checkbox: Reinforced structure |
| `security_notes` | TEXT | NULL | Additional security details |

### **Utility Features Fields:**

| Field | Type | Default | Description |
|-------|------|---------|-------------|
| `utility_blood_storage` | TINYINT(1) | 0 | Checkbox: Blood storage/refrigeration |
| `utility_computers` | TINYINT(1) | 0 | Checkbox: Computer systems |
| `utility_library` | TINYINT(1) | 0 | Checkbox: Library/archives |
| `utility_medical` | TINYINT(1) | 0 | Checkbox: Medical facilities |
| `utility_workshop` | TINYINT(1) | 0 | Checkbox: Workshop/crafting area |
| `utility_hidden_caches` | TINYINT(1) | 0 | Checkbox: Hidden item caches |
| `utility_armory` | TINYINT(1) | 0 | Checkbox: Armory/weapons storage |
| `utility_communications` | TINYINT(1) | 0 | Checkbox: Communications equipment |
| `utility_notes` | TEXT | NULL | Utility features details |

### **Social Features Fields:**

| Field | Type | Description |
|-------|------|-------------|
| `social_features` | TEXT | Social importance, status, facilities |
| `capacity` | INT | How many people can gather |
| `prestige_level` | INT(1) | Dropdown 0-5: None to Legendary |

### **Supernatural Features Fields:**

| Field | Type | Default | Description |
|-------|------|---------|-------------|
| `has_supernatural` | TINYINT(1) | 0 | Toggle: Location has supernatural properties |
| `node_points` | INT | NULL | 0-10: Magical energy available (0-5 minor, 6-10 major) |
| `node_type` | VARCHAR(50) | NULL | Dropdown: None, Standard, Corrupted, Pure, Ancient |
| `ritual_space` | TEXT | NULL | Description of ritual areas, altars, circles |
| `magical_protection` | TEXT | NULL | Wards, shields, protective spells |
| `cursed_blessed` | TEXT | NULL | Supernatural blessings, curses, hauntings |

### **Relationship Fields:**

| Field | Type | Description |
|-------|------|-------------|
| `parent_location_id` | INT | FK to locations.id - "Part of" relationship |
| `relationship_type` | VARCHAR(50) | Dropdown: Room In, Floor Of, Building In, Connected To, Part Of |
| `relationship_notes` | TEXT | Connection details, access routes |

### **Media Fields:**

| Field | Type | Description |
|-------|------|-------------|
| `image` | VARCHAR(255) | Location image URL |

### **Meta Fields:**

| Field | Type | Description |
|-------|------|-------------|
| `created_at` | TIMESTAMP | Auto-generated on creation |
| `updated_at` | TIMESTAMP | Auto-updated on modification |

---

## **📊 Junction Tables:**

### **location_ownership Table:**
Links characters to locations (many-to-many).

| Field | Type | Description |
|-------|------|-------------|
| `id` | INT AUTO_INCREMENT | Primary key |
| `location_id` | INT | FK to locations.id |
| `character_id` | INT | FK to characters.id |
| `ownership_type` | VARCHAR(50) | Owner, Resident, Guest, Tenant |
| `notes` | TEXT | Ownership details |
| `created_at` | TIMESTAMP | When assigned |

### **location_items Table:**
Links items to locations (many-to-many).

| Field | Type | Description |
|-------|------|-------------|
| `id` | INT AUTO_INCREMENT | Primary key |
| `location_id` | INT | FK to locations.id |
| `item_id` | INT | FK to items.id |
| `quantity` | INT | How many of this item |
| `hidden` | TINYINT(1) | Is item hidden/secret |
| `locked` | TINYINT(1) | Is item locked away |
| `location_notes` | TEXT | Where in location, condition |
| `created_at` | TIMESTAMP | When placed |

---

## **🎮 UI Implementation:**

### **Dropdown Fields:**
- Location Type
- Status
- Ownership Type
- Faction
- Access Control
- Node Type
- Prestige Level
- Parent Location
- Relationship Type

### **Checkbox Fields:**
- 8 Security features
- 8 Utility features
- Has Supernatural (toggle)

### **Text Fields:**
- Name (required)
- Summary (500 char limit)
- Description (POV style)
- Notes (GM private)
- Status Notes
- District
- Address
- Owner Notes
- Access Notes
- Security Notes
- Utility Notes
- Social Features
- Ritual Space
- Magical Protection
- Cursed/Blessed
- Relationship Notes
- Image URL

### **Number Fields:**
- Latitude
- Longitude
- Capacity
- Node Points (0-10)

### **Radio Fields:**
- Security Level (1-5)

---

## **🔗 Relationships:**

**Location → Location (Parent/Child):**
- Room is part of Building
- Building is part of District
- Connected via tunnels/passages

**Location → Characters (Ownership):**
- Via `location_ownership` junction table
- Multiple owners supported (coteries, clans)

**Location → Items (Contents):**
- Via `location_items` junction table
- Track quantity, hidden status, location within

---

## **✅ Next Steps:**

1. ✅ Research & field definition - COMPLETE
2. 🔄 Database schema design - IN PROGRESS
3. ⏳ Table implementation
4. ⏳ API development
5. ⏳ Admin interface completion

---

**Note:** Node points range (0-10) based on VtM LARP conventions:
- 0: No magical energy
- 1-2: Minor node, barely detectable
- 3-5: Standard node, useful for rituals
- 6-8: Major node, significant power
- 9-10: Ancient/legendary node, extremely rare

