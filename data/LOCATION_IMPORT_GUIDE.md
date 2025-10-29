# Location Import Guide

## Quick Start

**To import any location JSON file:**

1. **Upload JSON file** to `data/` folder (e.g., `tremere-chantry.json`)
2. **Run import script** via web browser:
   ```
   https://www.websitetalkingheads.com/vbn/data/import_location.php?file=tremere-chantry.json
   ```
3. **Check results** - Script will show success or detailed error

## JSON File Format

Locations must be in JSON format matching the template structure:

### Required Fields:
- `location_name` - Full name of the location
- `type` - Location type (Haven, Elysium, Domain, Hunting Ground, Nightclub, Business, Chantry, Temple, Wilderness, Other)
- `status` - Current status (Active, Abandoned, Destroyed, Contested, Hidden)
- `ownership` - Object containing ownership information
  - `owner_type` - Personal, Clan, Sect, Coterie, NPC, Contested, Public
  - `access_control` - Open, Restricted, Private, Secret, Invitation Only

### Optional Fields:

#### Basic Information:
- `summary` - Brief description for quick reference
- `description` - Detailed description of the location
- `notes` - Additional notes, plot hooks, etc.
- `status_notes` - Details about the current status

#### Geography:
- `geography` - Object containing location details
  - `district` - District or area name
  - `address` - Physical address
  - `latitude` - GPS latitude (decimal)
  - `longitude` - GPS longitude (decimal)

#### Ownership Details:
- `ownership` - Object containing ownership information
  - `owner_notes` - Details about the owner
  - `faction` - Camarilla, Sabbat, Independent, etc.
  - `access_notes` - Details about access requirements

#### Security Features:
- `security` - Object containing security information
  - `security_level` - Security level (1-10, default: 3)
  - `security_locks` - Has locks (true/false)
  - `security_alarms` - Has alarm system (true/false)
  - `security_guards` - Has guards (true/false)
  - `security_hidden_entrance` - Has hidden entrance (true/false)
  - `security_sunlight_protected` - Protected from sunlight (true/false)
  - `security_warding_rituals` - Has magical protection (true/false)
  - `security_cameras` - Has surveillance (true/false)
  - `security_reinforced` - Reinforced structure (true/false)
  - `security_notes` - Additional security details

#### Utility Features:
- `utilities` - Object containing utility features
  - `utility_blood_storage` - Blood storage facilities (true/false)
  - `utility_computers` - Computer systems (true/false)
  - `utility_library` - Library or books (true/false)
  - `utility_medical` - Medical facilities (true/false)
  - `utility_workshop` - Workshop or lab (true/false)
  - `utility_hidden_caches` - Hidden storage (true/false)
  - `utility_armory` - Weapons storage (true/false)
  - `utility_communications` - Communication systems (true/false)
  - `utility_notes` - Additional utility details

#### Social Features:
- `social` - Object containing social information
  - `social_features` - Description of social amenities
  - `capacity` - Maximum occupancy (number)
  - `prestige_level` - Prestige level (0-10)

#### Supernatural Features:
- `supernatural` - Object containing supernatural information
  - `has_supernatural` - Has supernatural elements (true/false)
  - `node_points` - Ley line node points (number)
  - `node_type` - Type of supernatural node
  - `ritual_space` - Description of ritual areas
  - `magical_protection` - Magical protections
  - `cursed_blessed` - Curses or blessings

#### Relationships:
- `relationships` - Object containing relationship information
  - `parent_location_id` - ID of parent location (number or null)
  - `relationship_type` - Type of relationship
  - `relationship_notes` - Details about the relationship

#### Media:
- `media` - Object containing media information
  - `image` - Image filename

## Database Mapping

### Column Name Mappings:
- **locations table:**
  - JSON `location_name` → DB `name`
  - JSON `geography.district` → DB `district`
  - JSON `geography.address` → DB `address`
  - JSON `geography.latitude` → DB `latitude`
  - JSON `geography.longitude` → DB `longitude`
  - JSON `ownership.owner_type` → DB `owner_type`
  - JSON `ownership.faction` → DB `faction`
  - JSON `ownership.access_control` → DB `access_control`
  - JSON `security.security_level` → DB `security_level`
  - JSON `utilities.*` → DB `utility_*` columns
  - JSON `social.capacity` → DB `capacity`
  - JSON `social.prestige_level` → DB `prestige_level`
  - JSON `supernatural.has_supernatural` → DB `has_supernatural`
  - JSON `supernatural.node_points` → DB `node_points`
  - JSON `relationships.parent_location_id` → DB `parent_location_id`

## Location Types

### Available Types:
- **Haven** - Personal residence for Kindred
- **Elysium** - Neutral meeting ground
- **Domain** - Territory controlled by a Kindred
- **Hunting Ground** - Area for feeding
- **Nightclub** - Entertainment venue
- **Gathering Place** - Social meeting spot
- **Business** - Commercial establishment
- **Chantry** - Tremere magical facility
- **Temple** - Religious or spiritual site
- **Wilderness** - Natural or remote area
- **Other** - Custom location type

## Status Options

### Available Statuses:
- **Active** - Currently in use
- **Abandoned** - No longer maintained
- **Destroyed** - Physically destroyed
- **Contested** - Under dispute
- **Hidden** - Secret or concealed

## Owner Types

### Available Owner Types:
- **Personal** - Owned by individual Kindred
- **Clan** - Owned by vampire clan
- **Sect** - Owned by Camarilla/Sabbat
- **Coterie** - Owned by group of Kindred
- **NPC** - Owned by non-player character
- **Contested** - Ownership disputed
- **Public** - Publicly accessible

## Access Control Levels

### Available Access Levels:
- **Open** - Publicly accessible
- **Restricted** - Limited access
- **Private** - Personal use only
- **Secret** - Hidden from most
- **Invitation Only** - Requires invitation

## Examples

### Simple Location (Nightclub):
```json
{
  "location_name": "The Midnight Lounge",
  "type": "Nightclub",
  "summary": "Exclusive nightclub catering to Kindred clientele",
  "status": "Active",
  "ownership": {
    "owner_type": "Personal",
    "faction": "Camarilla",
    "access_control": "Restricted"
  },
  "security": {
    "security_level": 5,
    "security_locks": true,
    "security_alarms": true,
    "security_guards": true,
    "security_sunlight_protected": true
  }
}
```

### Complex Location (Haven):
```json
{
  "location_name": "The Crimson Haven",
  "type": "Haven",
  "summary": "Luxurious Victorian mansion serving as private haven",
  "description": "Three-story Victorian mansion with ornate Gothic architecture...",
  "status": "Active",
  "geography": {
    "district": "Noble Heights",
    "address": "1237 Crimson Hill Drive",
    "latitude": 34.0522,
    "longitude": -118.2437
  },
  "ownership": {
    "owner_type": "Personal",
    "owner_notes": "Marcus Blackwood, Ventrue Primogen",
    "faction": "Camarilla",
    "access_control": "Invitation Only"
  },
  "security": {
    "security_level": 8,
    "security_locks": true,
    "security_alarms": true,
    "security_guards": true,
    "security_sunlight_protected": true,
    "security_warding_rituals": true,
    "security_cameras": true,
    "security_reinforced": true
  },
  "utilities": {
    "utility_blood_storage": true,
    "utility_computers": true,
    "utility_library": true,
    "utility_medical": true,
    "utility_hidden_caches": true,
    "utility_armory": true,
    "utility_communications": true
  },
  "social": {
    "social_features": "Grand ballroom, private study, wine cellar",
    "capacity": 50,
    "prestige_level": 9
  },
  "supernatural": {
    "has_supernatural": true,
    "node_points": 3,
    "node_type": "Ley Line Convergence",
    "ritual_space": "Consecrated chamber in basement",
    "magical_protection": "Warded against scrying and intrusion"
  }
}
```

## Troubleshooting

### Common Issues:
1. **Missing required fields** - Ensure all required fields are present
2. **Invalid JSON format** - Validate JSON syntax
3. **Wrong data types** - Check that numbers are numbers, booleans are true/false
4. **Database connection** - Ensure database is accessible
5. **File permissions** - Ensure import script can read the JSON file

### Error Messages:
- `Missing required field: [field]` - Add the missing field to your JSON
- `Invalid JSON format` - Check JSON syntax with a validator
- `Failed to create location` - Check database connection and table structure
- `File not found` - Ensure the JSON file is in the data/ directory
