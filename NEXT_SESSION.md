# Next Session: Character Portrait System Implementation

## Session Summary - v0.6.4 Complete ✅

**Version:** 0.6.4  
**Date:** October 26, 2025  
**Status:** Committed and Pushed

### What We Accomplished:
1. ✅ **Complete NPC Agent Briefing System** - Admin page for viewing and managing all NPCs
2. ✅ **Agent Briefing Modal** - Comprehensive character information display with nature/demeanor, traits, abilities, disciplines, backgrounds, biography, and notes
3. ✅ **Notes Management System** - Two editable fields:
   - **Agent Notes**: AI-formatted briefing for playing the character during sessions
   - **Acting Notes**: Post-session observations and developments
4. ✅ **Notes-Only Edit Modal** - Quick edit modal separate from full character editor
5. ✅ **NPC Player Name Standardization** - All NPCs now use `player_name = 'NPC'` (purple badge)
6. ✅ **Database Migration** - Added agentNotes and actingNotes TEXT columns
7. ✅ **NPC Normalization** - Created migration script to fix inconsistent player_name values
8. ✅ **Updated .gitignore** - Added .cursor/, .env, and config.env to prevent API key commits

### Key Files Created:
- `admin/admin_npc_briefing.php` - Main NPC briefing page with sortable table
- `admin/api_npc_briefing.php` - API to fetch NPC briefing data
- `admin/api_update_npc_notes.php` - API to save notes
- `js/admin_npc_briefing.js` - Client-side modal and table interactions
- `database/add_npc_briefing_fields.php` - Database migration for notes columns
- `database/normalize_npc_player_names.php` - NPC player_name standardization
- Documentation files (3 markdown files)

### Key Files Modified:
- `admin/admin_panel.php` - Added NPC Briefing nav, updated player_name filter
- `admin/admin_sire_childe.php` - Added NPC Briefing nav
- `admin/admin_sire_childe_enhanced.php` - Added NPC Briefing nav
- `.gitignore` - Added environment files to prevent API key commits
- `VERSION.md` - Updated to v0.6.4 with complete changelog
- `ToDo.MD` - Marked NPC Briefing as complete

---

## Next Session: Character Portrait System 🖼️

### Main Goal
Add character portrait/image functionality to the character sheet and character creator so users can upload and display character portraits.

### Planned Features

#### 1. **Character Image Upload System**
- Add image upload field to character creator (Basic Info tab)
- Support common formats (JPG, PNG, WEBP, GIF)
- Client-side image preview before save
- Image validation (size, format, dimensions)
- Optional image cropping/resizing

#### 2. **Database Schema Update**
Add character image field to characters table:
```sql
ALTER TABLE characters 
ADD COLUMN character_image VARCHAR(255) DEFAULT NULL 
AFTER biography;
```

**Storage Strategy Options:**
- **Option A: File System Storage (Recommended)**
  - Store in `images/characters/` folder
  - Database stores filename only: `character_123_portrait.jpg`
  - Pros: Better performance, easier to manage, smaller database
  - Cons: Need to manage file uploads and deletions

- **Option B: Database Base64 Storage**
  - Store image as base64 TEXT in database
  - Pros: Single source of truth, easier backups
  - Cons: Larger database, slower queries

**Recommendation:** Option A (File System Storage)

#### 3. **Image Storage Implementation**
- Create `images/characters/` folder
- Handle file uploads in `save_character.php`
- Store filename in database: `character_<id>_portrait.<ext>`
- Delete old images when uploading new ones
- Delete images when character is deleted

**File Upload Security:**
```php
if (isset($_FILES['character_image'])) {
    $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    $filename = $_FILES['character_image']['name'];
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    
    if (in_array($ext, $allowed) && $_FILES['character_image']['size'] <= 2097152) {
        $new_filename = 'character_' . $character_id . '_' . time() . '.' . $ext;
        move_uploaded_file($_FILES['character_image']['tmp_name'], 
                          'images/characters/' . $new_filename);
    }
}
```

#### 4. **Character Sheet Display**
- Add character portrait to character sheet (character_sheet.php)
- Position in header or sidebar
- Fallback to default silhouette if no image
- Responsive sizing for mobile
- Circular or square frame styling

#### 5. **Character Creator Integration**
- Add image upload control to Basic Info tab
- Real-time preview in character preview sidebar
- Save image with character data
- Handle image in `save_character.php`
- Load existing image in `load_character.php`

**JavaScript Preview:**
```javascript
document.getElementById('characterImage').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(event) {
            document.getElementById('imagePreview').src = event.target.result;
        };
        reader.readAsDataURL(file);
    }
});
```

#### 6. **Admin Panel Integration**
- Display character portraits in admin character list
- Thumbnail view option (50x50px)
- Click to view full-size image
- Show placeholder if no image

#### 7. **Image Management**
- Maximum file size enforcement (e.g., 2MB)
- Image compression/optimization on upload
- Handle character deletion (delete image file too)
- Default fallback images (male/female/generic silhouette)

### Implementation Order

1. **Database Migration**
   - Run SQL to add character_image column
   - Verify column exists

2. **File Upload Handler**
   - Create upload validation logic
   - Implement file saving in save_character.php
   - Add image deletion on character delete

3. **Character Creator UI**
   - Add file input to Basic Info tab
   - Add preview div in sidebar
   - Wire up JavaScript for preview
   - Style the upload control

4. **Character Sheet Display**
   - Add image display in character_sheet.php
   - Style portrait frame
   - Add fallback image logic

5. **Admin Panel Updates**
   - Show thumbnails in character list
   - Update view modal to show portraits

### Files to Modify:
- `lotn_char_create.php` - Add image upload field
- `save_character.php` - Handle image upload/save
- `load_character.php` - Load existing image
- `character_sheet.php` - Display character portrait
- `admin/admin_panel.php` - Show thumbnails in list
- `admin/view_character_api.php` - Include image in view modal
- `admin/delete_character_api.php` - Delete image file with character
- `js/modules/main.js` - Handle image preview and validation
- `css/lotn_char_create.css` - Style image upload controls

### Files to Create:
- `database/add_character_image_column.php` - Database migration script
- `images/characters/` - Directory for character portraits
- `images/character-default.svg` - Default placeholder image

### Technical Considerations

**File Upload PHP:**
```php
// In save_character.php
if (isset($_FILES['character_image'])) {
    $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    $filename = $_FILES['character_image']['name'];
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    
    if (in_array($ext, $allowed) && $_FILES['character_image']['size'] <= 2097152) {
        $new_filename = 'character_' . $character_id . '_' . time() . '.' . $ext;
        move_uploaded_file($_FILES['character_image']['tmp_name'], 
                          'images/characters/' . $new_filename);
        
        // Save to database
        $stmt = $conn->prepare("UPDATE characters SET character_image = ? WHERE id = ?");
        $stmt->bind_param("si", $new_filename, $character_id);
        $stmt->execute();
    }
}
```

**Image Preview JavaScript:**
```javascript
// Real-time image preview
function previewImage(input, previewId) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(event) {
            document.getElementById(previewId).src = event.target.result;
            document.getElementById(previewId).style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    }
}
```

**Character Sheet Display:**
```php
// In character_sheet.php
<div class="character-portrait">
    <?php if (!empty($character['character_image'])): ?>
        <img src="images/characters/<?php echo htmlspecialchars($character['character_image']); ?>" 
             alt="<?php echo htmlspecialchars($character['character_name']); ?>">
    <?php else: ?>
        <img src="images/character-default.svg" alt="No portrait">
    <?php endif; ?>
</div>
```

### Default/Fallback Images Needed:
- Default male silhouette
- Default female silhouette  
- Default generic vampire silhouette
- Or: Single placeholder image for all characters

### Future Enhancements (Not This Session):
- AI-generated character portraits (Stable Diffusion, DALL-E)
- Multiple character images (gallery)
- Character image backgrounds/frames
- Clan-specific image templates
- Character image filters/effects
- PDF character sheets with images

---

## Quick Start for Next Session

1. **Create character images folder**: `mkdir images/characters`
2. **Run database migration** to add `character_image` column
3. **Add upload field** to Basic Info tab in character creator
4. **Implement file upload** in `save_character.php`
5. **Add image display** to character sheet and preview
6. **Test with sample images**
7. **Add image management** to delete_character_api.php

## Questions to Decide Before Starting:
1. File system storage vs database base64? (Recommend file system)
2. Maximum image size limit? (Recommend 2MB)
3. Resize/compress images server-side or client-side?
4. Allow multiple images per character or just one portrait?
5. Default fallback image style (silhouette, clan logo, custom)?

---

## Current Project State

### Version: 0.6.4
### Last Updated: October 26, 2025

### Working Features:
- ✅ Complete character creation system
- ✅ Database-driven questionnaire
- ✅ Admin panel with character management
- ✅ NPC Briefing System with notes management
- ✅ Gothic theme with custom logo
- ✅ SVG favicon
- ✅ HTML2Canvas integration (ready to use)
- ✅ Character loading/saving
- ✅ Mobile responsive design

### Ready for Enhancement:
- 🖼️ Character image upload system (next session)
- 🔄 Character sheet image export (tools ready, needs integration)
- 📄 PDF character sheet generation (future)

### Tech Stack:
- PHP 8.4
- MySQL database
- Vanilla JavaScript (no frameworks)
- External CSS files
- html2canvas (for image export)
- SVG graphics

---

**Session Goal**: Implement a complete character image system that allows users to upload, display, and manage character portraits throughout the application.

**Success Criteria**:
1. Users can upload images in character creator
2. Images display on character sheets
3. Images show in admin panel
4. Images are properly stored and managed
5. System has proper fallbacks for missing images
6. Mobile-friendly image display

