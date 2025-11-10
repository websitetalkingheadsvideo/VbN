Image Prompt References

Purpose
- Store all per‑character prompt files in one place for consistency.

Layout
- This folder contains `<Character Name>.prompt.txt` and any companion files (e.g., `.negative.txt`).
- Final images remain in `reference/Characters/Images/` as `<Character Name>.webp`.
- Canonical character data stays at `reference/Characters/characters.json`.

Usage
- When generating an image, open the matching `.prompt.txt` here.
- If a `.negative.txt` exists, reference it from the render tool.
- Do not place final images in this folder.

Notes
- All prompts enforce “no daylight” for Masquerade‑safe visuals.
- Add variants by appending sections within the same `.prompt.txt` file.

