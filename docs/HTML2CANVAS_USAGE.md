# HTML2Canvas Integration - FREE Image Export

## Overview
This implementation uses **html2canvas** - a 100% FREE, client-side JavaScript library that converts HTML elements to images. No API keys, no external services, no cost!

## What You Get
- ✅ Convert character sheets to PNG images
- ✅ Download images locally
- ✅ Share images (if browser supports)
- ✅ Get base64 for server upload
- ✅ Works completely offline
- ✅ Zero cost forever

## Files Created
1. `js/html2canvas-integration.js` - Integration functions
2. `test_html2canvas.html` - Test page to verify it works

## How to Use

### 1. Include html2canvas in Your HTML
Add this in your `<head>` or before closing `</body>`:

```html
<!-- Load from CDN (FREE) -->
<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>

<!-- Load our integration -->
<script src="js/html2canvas-integration.js"></script>
```

### 2. Add Download Button to Your Character Sheet

```html
<!-- Add this button somewhere on your page -->
<button onclick="convertToImage('character-sheet', 'my-vampire.png')">
    Download Character Sheet as Image
</button>

<!-- Make sure your character sheet has an ID -->
<div id="character-sheet">
    <!-- Your character sheet HTML here -->
</div>
```

### 3. Available Functions

#### Download Image
```javascript
// Basic usage
convertToImage('character-sheet', 'my-character.png');

// With custom options
convertToImage('character-sheet', 'my-character.png', {
    backgroundColor: '#1a0f0f',
    scale: 3, // Higher = better quality (but larger file)
    width: 1200,
    height: 1600
});
```

#### Get Base64 (for upload to server)
```javascript
const base64Image = await getImageAsBase64('character-sheet');
// Now you can send this to your PHP backend to save
```

#### Share Image
```javascript
// Uses Web Share API if available, falls back to download
shareCharacterSheet('character-sheet', 'My Awesome Vampire');
```

## Testing

1. Open `test_html2canvas.html` in your browser
2. Click "Download as PNG" - should download a character sheet image
3. Click "Get Base64 (Console)" - check browser console for output
4. Click "Share Image" - should open share dialog (or download)

## Integration with Your VbN Project

### Add to Character Creator (lotn_char_create.php)

```html
<!-- At the bottom of the page, before </body> -->
<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
<script src="js/html2canvas-integration.js"></script>
```

### Add Export Button

```html
<!-- Add this near your Save button -->
<button type="button" 
        onclick="convertToImage('character-sheet-container', 'vampire-character.png')"
        style="background: #8b0000; color: #e8d5c4; padding: 10px 20px; border: none; cursor: pointer;">
    📷 Download as Image
</button>
```

### Make Sure Container Has ID

```html
<!-- Wrap your character sheet in a div with an ID -->
<div id="character-sheet-container">
    <!-- All your character sheet HTML -->
</div>
```

## Advanced: Upload to Server

If you want to save images to your server:

```javascript
// 1. Get the image as base64
const imageData = await getImageAsBase64('character-sheet');

// 2. Send to PHP
const response = await fetch('save_character_image.php', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json'
    },
    body: JSON.stringify({
        image: imageData,
        character_id: characterId
    })
});
```

**PHP Side (save_character_image.php):**
```php
<?php
session_start();
require_once 'includes/auth_check.php';

$data = json_decode(file_get_contents('php://input'), true);
$imageData = $data['image'];
$characterId = $data['character_id'];

// Remove data:image/png;base64, prefix
$imageData = str_replace('data:image/png;base64,', '', $imageData);
$imageData = base64_decode($imageData);

// Save to file
$filename = "character_images/{$characterId}.png";
file_put_contents($filename, $imageData);

echo json_encode(['success' => true, 'filename' => $filename]);
?>
```

## Options Reference

```javascript
{
    backgroundColor: '#1a0f0f',  // Background color
    scale: 2,                     // Quality multiplier (1-3)
    width: 800,                   // Output width (optional)
    height: 1200,                 // Output height (optional)
    logging: false,               // Console logging
    useCORS: true,                // Allow external images
    allowTaint: true,             // Allow cross-origin
    windowWidth: 1920,            // Viewport width for rendering
    windowHeight: 1080            // Viewport height for rendering
}
```

## Tips for Best Results

1. **Higher Scale = Better Quality** (but larger files)
   - scale: 1 = Standard quality
   - scale: 2 = High quality (recommended)
   - scale: 3 = Very high quality (large file)

2. **Hide UI Elements Before Export**
   ```javascript
   // Hide buttons/controls before capture
   document.getElementById('controls').style.display = 'none';
   await convertToImage('character-sheet', 'output.png');
   document.getElementById('controls').style.display = 'block';
   ```

3. **Style for Print**
   - Use solid backgrounds (not transparent)
   - Ensure good contrast
   - Use web-safe fonts or include font files

## Browser Compatibility

Works in all modern browsers:
- ✅ Chrome/Edge
- ✅ Firefox
- ✅ Safari
- ✅ Opera
- ✅ Mobile browsers

## Cost Analysis

| Feature | Cost |
|---------|------|
| html2canvas library | FREE |
| CDN hosting | FREE |
| Image generation | FREE |
| Storage (client-side) | FREE |
| API calls needed | ZERO |
| Monthly limit | UNLIMITED |

**Total Cost: $0.00 forever** 🎉

## Troubleshooting

### Images not showing in output
- Make sure images use CORS headers
- Try setting `allowTaint: true` and `useCORS: true`

### Poor quality output
- Increase `scale` option (2 or 3)
- Ensure source HTML is not scaled/zoomed

### Download not working
- Check browser console for errors
- Verify element ID exists
- Make sure html2canvas loaded successfully

## Next Steps

1. Test with `test_html2canvas.html`
2. Add to your character creator page
3. Style the export button to match your theme
4. Consider adding server-side storage if needed

