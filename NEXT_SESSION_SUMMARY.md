# Next Session: Character Image Functionality

## Summary of Current Session (v0.6.3)

### What We Accomplished:
1. ✅ **Created Custom VbN Logo** - Gothic SVG logo with animated hover effects
2. ✅ **Implemented Logo Animations** - Smooth scale and glow effects on hover
3. ✅ **Added SVG Favicon** - Professional favicon for browser tabs and bookmarks
4. ✅ **Integrated HTML2Canvas** - Free, client-side image export system (no API costs)
5. ✅ **Created Documentation** - Complete usage guide for image export functionality
6. ✅ **Built Testing Tools** - Favicon generator and logo animation test pages

### Key Files Created:
- `images/vbn-logo.svg` - Main header logo (80x80)
- `images/favicon.svg` - Favicon for browser tabs (32x32)
- `js/logo-animation.js` - Logo hover animation script
- `js/html2canvas-integration.js` - Image export functions
- `docs/HTML2CANVAS_USAGE.md` - Complete documentation
- `create_favicon.html` - Favicon generator tool
- `test_html2canvas.html` - Image export testing page

### Logo Features Implemented:
- **Animated Hover Effects**: Logo scales up 10% and glows bright red
- **Gothic Theme**: Dark red/black gradient background with blood-red "VbN" text
- **Scalable SVG**: Vector format looks perfect at any size
- **Clickable**: Logo links to homepage
- **Smooth Transitions**: 0.3s animation duration for professional feel

### HTML2Canvas Integration Ready:
- **Three Core Functions**:
  - `convertToImage()` - Download as PNG
  - `getImageAsBase64()` - Get data for server upload
  - `shareCharacterSheet()` - Share via Web Share API
- **Zero Cost**: 100% free, unlimited usage
- **Offline Capable**: Works completely client-side
- **Quality Control**: Adjustable scale (1-3) for different needs

---

## Next Session Goals: Character Image Upload & Display

### Primary Objective:
Add character portrait/image functionality to the character sheet and character creator.

### Planned Features:

#### 1. **Character Image Upload System**
- [ ] Add image upload field to character creator (Basic Info tab)
- [ ] Support common formats (JPG, PNG, WEBP, GIF)
- [ ] Client-side image preview before save
- [ ] Image validation (size, format, dimensions)
- [ ] Optional image cropping/resizing

#### 2. **Database Schema Updates**
- [ ] Add `character_image` field to `characters` table (VARCHAR for filename or TEXT for base64)
- [ ] Decide: Store as filename (with images folder) vs base64 in database
- [ ] Add migration script for database update

#### 3. **Image Storage Strategy**
**Option A: File System Storage (Recommended)**
- Store in `images/characters/` folder
- Database stores filename only: `character_123_portrait.jpg`
- Pros: Better performance, easier to manage, smaller database
- Cons: Need to manage file uploads, deletions

**Option B: Database Base64 Storage**
- Store image as base64 TEXT in database
- Pros: Single source of truth, easier backups
- Cons: Larger database, slower queries

#### 4. **Character Sheet Display**
- [ ] Add character portrait to character sheet (character_sheet.php)
- [ ] Position in header or sidebar
- [ ] Fallback to default silhouette if no image
- [ ] Responsive sizing for mobile

#### 5. **Character Creator Integration**
- [ ] Add image upload control to Basic Info tab
- [ ] Real-time preview in character preview sidebar
- [ ] Save image with character data
- [ ] Handle image in `save_character.php`
- [ ] Load existing image in `load_character.php`

#### 6. **Admin Panel Integration**
- [ ] Display character portraits in admin character list
- [ ] Thumbnail view option
- [ ] Click to view full-size image

#### 7. **Image Management**
- [ ] Delete old image when uploading new one
- [ ] Delete image when character is deleted
- [ ] Image compression/optimization
- [ ] Maximum file size enforcement (e.g., 2MB)

### Technical Considerations:

**File Upload PHP:**
```php
// Handle file upload
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

**JavaScript Preview:**
```javascript
// Preview image before upload
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

### Using HTML2Canvas for Character Sheet Export:
Once we have character images working, we can:
1. Export character sheets as images (already have the tools)
2. Include character portrait in the exported image
3. Share character sheets on social media
4. Generate PDF versions (future enhancement)

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

### Database Migration Script Needed:
```sql
-- Add character_image column to characters table
ALTER TABLE characters 
ADD COLUMN character_image VARCHAR(255) DEFAULT NULL 
AFTER biography;

-- Add index for faster queries
CREATE INDEX idx_character_image ON characters(character_image);
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

---

## Quick Start for Next Session:

1. **Create character images folder**: `mkdir images/characters`
2. **Run database migration** to add `character_image` column
3. **Add upload field** to Basic Info tab in character creator
4. **Implement file upload** in `save_character.php`
5. **Add image display** to character sheet and preview
6. **Test with sample images**

## Questions to Decide:
1. File system storage vs database base64?
2. Maximum image size limit? (Recommend 2MB)
3. Resize/compress images server-side or client-side?
4. Allow multiple images per character or just one portrait?
5. Default fallback image style (silhouette, clan logo, custom)?

---

## Current Project State:

### Version: 0.6.3
### Last Updated: January 26, 2025

### Working Features:
- ✅ Complete character creation system
- ✅ Database-driven questionnaire
- ✅ Admin panel with character management
- ✅ Gothic theme with custom logo
- ✅ SVG favicon
- ✅ HTML2Canvas integration (ready to use)
- ✅ Character loading/saving
- ✅ Mobile responsive design

### Ready for Enhancement:
- 🔄 Character image upload system (next session)
- 🔄 Character sheet image export (tools ready, needs integration)
- 🔄 PDF character sheet generation (future)

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
