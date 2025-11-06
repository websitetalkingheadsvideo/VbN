Valley by Night — Image Plan

Purpose
- Document a consistent process to: (1) structure character data, (2) enrich it from the database, and (3) generate and approve character portraits in the Valley by Night style.

Scope
- Source of truth for character fields and image workflow.
- Aligned with reference/Valley_by_Night_Character_Art_Guide.json.
- Uses includes/connect.php for DB access.

JSON Schema
- File: reference/Characters/characters.json
- Image directory: reference/Characters/Images (storage location)
- Image format: webp (default 1024x1024; square portrait framing per art guide)

Schema (flattened traits list)
{
  "file_name": "Valley_by_Night_Characters.json",
  "image_dir": "reference/Characters/Images",
  "image_format": "webp",
  "characters": [
    {
      "name": "Adrian Leclair",
      "clan": "",
      "description": "",
      "notes": "",
      "traits": [],
      "image": "Adrian Leclair.webp"
    }
  ]
}

Notes
- traits is a single flat array that merges positive and negative traits; duplicates removed.
- description maps to biography or equivalent narrative text from the DB.
- image value is filename only (no path), using the exact character name with .webp extension.
- Images are stored under reference/Characters/Images; paths are not stored in JSON.
- Keep names “as written” for display; when saving files on Windows, sanitize disallowed characters (e.g., remove ").
- If a non-square size is needed later (e.g., 1024x1045), keep composition square-cropped visually to match the guide.

Database Enrichment
- Connector: includes/connect.php (mysqli, utf8mb4).
- Tables referenced (current app schema):
  - characters: id, character_name, clan, biography (→ description), notes
  - character_traits: trait_name, trait_category (Physical/Social/Mental), trait_type (positive/negative)
  - character_negative_traits (legacy in some scripts): merge into a single traits list if present

Process
1) For each name in characters.json → find matching row in characters by character_name (case-insensitive exact match preferred).
2) Write back the following fields:
   - clan → characters[i].clan
   - biography → characters[i].description
   - notes → characters[i].notes
3) Traits:
   - Read from character_traits (and character_negative_traits if present).
   - Collect trait_name values across all categories/types.
   - Normalize spacing/case minimally; do not rewrite names.
   - Dedupe; sort stable (by insertion order from DB query) for consistency.
   - Save to characters[i].traits (flat array of strings).
4) If a character is missing in DB, leave fields blank and log a warning.

SQL Reference (illustrative)
- Character core:
  SELECT id, character_name, clan, biography, notes FROM characters WHERE character_name = ? LIMIT 1;
- Traits (positive/negative in one table):
  SELECT trait_name FROM character_traits WHERE character_id = ? ORDER BY trait_category, trait_name;
- Traits (if negative stored separately):
  SELECT trait_name FROM character_negative_traits WHERE character_id = ? ORDER BY trait_category, trait_name;

Clan Style Guide (General)
- Tone: World of Darkness cinematic realism; noir, elegant, emotional restraint. Always avoid direct sunlight; night or interior lighting only.
- Global lighting: moody, medium-high contrast; single key light (warm or cool), soft fill, minimal rim; shallow depth of field; subtle haze.
- Palette: amber, crimson, violet, cool teal shadows; low-to-medium saturation; cinematic warm/cool grade.

By Clan (visual motifs for prompts)
- Brujah: Urban grit, protest posters, chipped concrete; leather/denim; kinetic posture; accents in crimson/amber; bruised neon.
- Gangrel: Natural grit; desert or woodland night; windblown hair, dust; animalistic calm; moonlit teal; scuffed fabrics.
- Malkavian: Uneven or split lighting; reflections and glass; asymmetry; gentle surreal notes (not cartoonish); violet/teal contrasts.
- Nosferatu: Deep shadow framing; industrial basements, brick, wet surfaces; obscured features; rim light catching texture; grayscale with sickly green/teal.
- Toreador: Poised elegance; satin, glass, gallery backdrops; precise posture; soft amber key with violet accent; jewelry sparkle.
- Tremere: Arcane academia; tomes, runes, lab glass; candlelight and cool teal fill; composed intensity; structured tailoring.
- Ventrue: Executive luxury; marble, polished wood, city skyline bokeh; immaculate tailoring; cool key with warm accent; restrained expression.
- Giovanni (Hecata): Muted luxury; marble and antique frames; grayscale warmth; funereal refinement; candlelight gold accents.
- Followers of Set (Setites): Temple or lounge shadows; candle/gold motifs; serpentine patterns; deep shadows with warm glints.
- Lasombra: High-contrast chiaroscuro; modern sanctums; negative space; cool monochrome with violet hints; assertive silhouette.
- Tzimisce: Organic/architectural textures; unsettling but elegant; surgical fabrics; cold teal key with crimson accent.
- Ravnos: Traveling artist vibe; patterned textiles; caravan lights; nocturnal warmth with violet; restless posture.
- Assamite (Banu Haqim): Ascetic precision; geometric patterns; blade or script motifs; moonlit cools with amber candle pinpoints.
- Caitiff/Thin-Blood: Improvised style; thrift/patchwork; urban nights; tired neon; vulnerable intensity.

Prompt Template
- From reference/Valley_by_Night_Character_Art_Guide.json
  Square portrait of [Character Name], a [Clan or Role] from Valley by Night, depicted in a cinematic World of Darkness style. [Brief physical traits and attire]. Lighting is moody and dramatic, with [color accent or environment]. The image feels intimate and realistic, like a film still, with subtle haze and emotional tension.

Example Prompt (generic)
- Square portrait of Adrian Leclair, a Ventrue from Valley by Night, depicted in a cinematic World of Darkness style. Clean-cut features, tailored navy suit, understated signet ring. Lighting is moody with a cool key and warm amber accents from city bokeh. The image feels intimate and realistic, like a film still, with shallow depth of field, subtle haze, and emotional restraint.

Image Generation & Storage
- Generation tool: any free local model (e.g., SDXL/Stable Diffusion) or service capable of following the guide.
- Output: webp, 1024x1024, saved to reference/Characters/Images/<Character Name>.webp
- Naming: Use exact character name (quotes/parentheses allowed) as filename stem.
- Quality guidelines: keep faces/eyes sharp; avoid flat lighting; avoid comedic/cartoon tone; no explicit gore.

Approval Workflow
1) For each character in the JSON order:
   - I propose a concrete prompt (using clan + a few traits) for your approval.
2) You approve or request edits (prompt tweaks, lighting, color, attire, set dressing).
3) I generate the image and show a preview/output note.
4) On approval, I save to reference/Characters/Images/<name>.webp and move to the next character.

Operational Checklist
- [ ] Maintain deduped character list (as provided, with duplicates removed).
- [ ] Enrich JSON from DB for clan, description, notes, traits.
- [ ] Draft prompts per character; get approval.
- [ ] Generate images; save as webp, 1024x1024.
- [ ] Keep tone/lighting consistent with the art guide; never show sunlight.

Next Steps
1) Create reference/Characters/characters.json with the flattened traits schema and the provided names (deduped).
2) Run a small PHP or CLI script that uses includes/connect.php to enrich clan, description (biography), notes, and traits.
3) Start approval loop for prompts; proceed character by character.
