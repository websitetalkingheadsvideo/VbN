#!/usr/bin/env python3
"""
Process characters starting from ID 63 (Piston) and continue upward.
Fills in missing biography, notes, appearance, and character_image fields.
"""

import json
import os
import shutil
from pathlib import Path

# Paths
JSON_FILE = r"G:\VbN\reference\Characters\character_field_work.json"
IMAGES_SOURCE = r"G:\VbN\reference\Characters\Images"
IMAGES_DEST = r"G:\VbN\uploads\characters"
REFERENCES_DIR = r"G:\VbN\reference\Characters\Images\References"
IMAGE_PLAN = r"G:\VbN\reference\Characters\Image Plan.md"

def is_valid_text(field_value):
    """Check if field contains actual text (at least one sentence ending with punctuation)."""
    if not field_value or not isinstance(field_value, str):
        return False
    text = field_value.strip()
    if len(text) < 10:  # Too short to be meaningful
        return False
    # Check if it ends with sentence punctuation
    if text[-1] in '.!?':
        return True
    # Or if it has multiple sentences
    if text.count('.') + text.count('!') + text.count('?') > 0:
        return True
    return False

def find_image_file(character_name, images_dir):
    """Find image file matching character name (case-insensitive, flexible naming)."""
    if not os.path.exists(images_dir):
        return None
    
    # Normalize character name for matching
    name_lower = character_name.lower().replace(' ', '_').replace('"', '').replace("'", "")
    
    for filename in os.listdir(images_dir):
        if filename.startswith('.'):
            continue
        
        # Check exact match (case-insensitive)
        file_lower = filename.lower()
        name_base = name_lower.split('.')[0] if '.' in name_lower else name_lower
        
        # Remove extension for comparison
        file_base = os.path.splitext(filename)[0].lower().replace(' ', '_').replace('"', '').replace("'", "")
        
        if name_base == file_base or name_lower in file_base or file_base in name_lower:
            return filename
    
    return None

def generate_appearance(character):
    """Generate appearance description for character based on their data."""
    name = character.get('character_name', '')
    biography = character.get('biography', '')
    notes = character.get('notes', '')
    
    # Extract clan hints from notes/biography
    clan_hints = []
    if 'Brujah' in notes or 'Brujah' in biography:
        clan_hints.append('Brujah')
    if 'Gangrel' in notes or 'Gangrel' in biography:
        clan_hints.append('Gangrel')
    if 'Toreador' in notes or 'Toreador' in biography:
        clan_hints.append('Toreador')
    if 'Nosferatu' in notes or 'Nosferatu' in biography:
        clan_hints.append('Nosferatu')
    if 'Malkavian' in notes or 'Malkavian' in biography:
        clan_hints.append('Malkavian')
    if 'Tremere' in notes or 'Tremere' in biography:
        clan_hints.append('Tremere')
    if 'Ventrue' in notes or 'Ventrue' in biography:
        clan_hints.append('Ventrue')
    if 'Giovanni' in notes or 'Giovanni' in biography:
        clan_hints.append('Giovanni')
    if 'Setite' in notes or 'Setite' in biography or 'Followers of Set' in notes:
        clan_hints.append('Setite')
    if 'Ravnos' in notes or 'Ravnos' in biography:
        clan_hints.append('Ravnos')
    
    clan = clan_hints[0] if clan_hints else 'Kindred'
    
    # Generate appearance based on character context
    # This is a placeholder - actual generation will be done by AI
    return f"[Appearance to be generated for {name}]"

def generate_biography(character):
    """Generate biography for character."""
    name = character.get('character_name', '')
    # Placeholder - actual generation will be done by AI
    return f"[Biography to be generated for {name}]"

def generate_notes(character):
    """Generate GM notes for character."""
    name = character.get('character_name', '')
    # Placeholder - actual generation will be done by AI
    return f"[Notes to be generated for {name}]"

def create_image_prompt(character, image_plan_path):
    """Create image prompt file for character."""
    name = character.get('character_name', '')
    prompt_file = os.path.join(REFERENCES_DIR, f"{name}.prompt.txt")
    
    # Check if prompt already exists
    if os.path.exists(prompt_file):
        return
    
    # Read image plan for style reference
    with open(image_plan_path, 'r', encoding='utf-8') as f:
        plan_content = f.read()
    
    # Generate prompt based on character data
    # This is a placeholder - actual generation will be done by AI
    prompt_text = f"[Image prompt to be generated for {name}]\n\nBased on Image Plan guidelines."
    
    os.makedirs(REFERENCES_DIR, exist_ok=True)
    with open(prompt_file, 'w', encoding='utf-8') as f:
        f.write(prompt_text)

def process_character(character, start_id):
    """Process a single character, filling in missing fields."""
    char_id = character.get('id', 0)
    
    # Only process characters with ID >= start_id
    if char_id < start_id:
        return False
    
    updated = False
    name = character.get('character_name', '')
    
    print(f"\nProcessing ID {char_id}: {name}")
    
    # 1. Check biography
    if not is_valid_text(character.get('biography', '')):
        print(f"  - Biography missing/invalid, will generate")
        # Will generate via AI
        updated = True
    
    # 2. Check notes
    if not character.get('notes', '').strip():
        print(f"  - Notes missing, will generate")
        # Will generate via AI
        updated = True
    
    # 3. Check appearance
    if not character.get('appearance', '').strip():
        print(f"  - Appearance missing, will generate")
        # Will generate via AI
        updated = True
    
    # 4. Check character_image
    if not character.get('character_image', '').strip():
        print(f"  - character_image missing, checking Images folder...")
        image_file = find_image_file(name, IMAGES_SOURCE)
        if image_file:
            print(f"    Found: {image_file}")
            character['character_image'] = image_file
            # Copy to destination
            src_path = os.path.join(IMAGES_SOURCE, image_file)
            dest_path = os.path.join(IMAGES_DEST, image_file)
            os.makedirs(IMAGES_DEST, exist_ok=True)
            if not os.path.exists(dest_path):
                shutil.copy2(src_path, dest_path)
                print(f"    Copied to {IMAGES_DEST}")
            updated = True
        else:
            print(f"    No image found, will create prompt")
            create_image_prompt(character, IMAGE_PLAN)
            updated = True
    
    return updated

def main():
    """Main processing function."""
    # Load JSON
    with open(JSON_FILE, 'r', encoding='utf-8') as f:
        data = json.load(f)
    
    characters = data.get('characters', [])
    
    # Process starting from ID 63
    start_id = 63
    updated_count = 0
    
    for character in characters:
        if process_character(character, start_id):
            updated_count += 1
    
    # Save updated JSON
    with open(JSON_FILE, 'w', encoding='utf-8') as f:
        json.dump(data, f, indent=4, ensure_ascii=False)
    
    print(f"\n\nProcessed {updated_count} characters starting from ID {start_id}")
    print(f"JSON saved to {JSON_FILE}")

if __name__ == '__main__':
    main()

