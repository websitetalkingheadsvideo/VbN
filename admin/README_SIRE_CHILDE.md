# Sire/Childe Relationship Tracker

## Overview
The Sire/Childe Relationship Tracker is an admin tool for managing vampire lineage and blood bonds in the Valley by Night chronicle.

## Features

### 📊 Statistics Dashboard
- Total vampires in the city
- Count of vampires with sires
- Count of sireless vampires  
- Number of childer (vampires who have sired others)

### 🔍 Filtering & Search
- **All Relationships**: View all vampire relationships
- **Sires Only**: Show only vampires who have sired others
- **Childer Only**: Show only vampires with sires
- **Sireless**: Show vampires without sires
- **Search**: Find vampires by name or sire name

### 📋 Relationship Management
- **Add Relationship**: Set a vampire's sire
- **Edit Relationship**: Modify existing sire/childe relationships
- **View Details**: Access full character information
- **Remove Sire**: Make a vampire sireless

### 🌳 Family Tree
- Visual representation of vampire lineages
- Organized by generation
- Shows sire/childe connections
- Displays clan affiliations

## Database Structure

The system uses the existing `characters` table with the `sire` field:
- `sire` (VARCHAR): Name of the vampire who sired this character
- `NULL` or empty string indicates a sireless vampire

## Usage

### Setting Up Relationships
1. Navigate to Admin Panel → Sire/Childe
2. Click "Add Relationship"
3. Select the vampire from the dropdown
4. Choose their sire (or leave blank for sireless)
5. Add any notes about the relationship
6. Save

### Viewing Family Trees
1. Click "Family Tree" button
2. View the hierarchical display of vampire lineages
3. Organized by generation (highest to lowest)

### Filtering Data
- Use filter buttons to show specific relationship types
- Use search box to find specific vampires or sires
- All filters work together for precise results

## Technical Details

### Files
- `admin_sire_childe.php` - Main admin interface
- `api_sire_childe.php` - Backend API for data operations
- `css/admin_sire_childe.css` - Styling

### API Endpoints
- `POST api_sire_childe.php` - Update relationship data
- `GET api_sire_childe.php?action=tree` - Get family tree data

### Security
- Admin-only access required
- Input validation and sanitization
- SQL injection protection via prepared statements

## Integration

The sire/childe tracker integrates with:
- Character management system
- Admin navigation
- Existing character database
- Character view system

## Future Enhancements

Potential future features:
- Blood bond tracking
- Relationship status (loyal, estranged, hostile)
- Embrace date tracking
- Visual relationship diagrams
- Export family tree data
- Relationship notes and history
