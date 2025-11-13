# Cursor Prompt: Fix View Character Modal in VbN Admin Panel

You are Cursor working on the VbN project.

## Goal
The “View Character” link on https://vbn.talkingheads.video/admin/admin_panel.php should open a working Bootstrap modal that actually loads the character data. Right now the modal shows “Error loading character.” Also, the modal markup isn’t Bootstrap, so convert it to Bootstrap-based markup consistent with @Bootstrap.mdc.

---

## Rules (Follow All)
1. **DO NOT remove or rename any page that is linked to in any way from** `https://vbn.talkingheads.video/` **or from** `https://vbn.talkingheads.video/admin/admin_panel.php`, **or any page those pages link to.**
2. **DO NOT create fallbacks** unless the user explicitly asks for a specific fallback.
3. **DO NOT use CLI or PowerShell to connect to remote databases.**
4. **Match current project styles** and follow the rules in the `rules` folder if present.
5. If you are unsure about a file location or existing pattern, **ask** — don’t guess and don’t delete.
6. **Reuse the solution we used last time this modal broke.** Check for old commits or documentation.

---

## Taskmaster Plan

### 1. Recon
- Locate the “View Character” link/button in `admin/admin_panel.php`.
- Determine how it currently opens the modal (data attributes, JS, AJAX).
- Identify where the modal HTML lives and confirm it’s not Bootstrap-based.

### 2. Find the Failing Data Source
- Identify the PHP endpoint serving the modal (e.g., `admin/get_character.php`).
- Trace how it loads character data via ID.
- Fix parameter or format mismatches between frontend JS and backend PHP.

### 3. Make the Modal Bootstrap
Replace current markup with:

```html
<div class="modal fade" id="characterModal" tabindex="-1" aria-labelledby="characterModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="characterModalLabel">Character Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="characterModalBody">
        <!-- Character content loads here -->
      </div>
    </div>
  </div>
</div>
```

Use the **same Bootstrap version already loaded** on the admin panel.

### 4. Wire Up “View Character” Link
Ensure each “View Character” button calls:

```js
function openCharacterModal(characterId) {
  if (!characterId) return;
  const modalEl = document.getElementById('characterModal');
  const modalBody = document.getElementById('characterModalBody');
  modalBody.innerHTML = 'Loading...';

  fetch('admin/get_character.php?id=' + encodeURIComponent(characterId))
    .then(resp => resp.text())
    .then(html => {
      modalBody.innerHTML = html;
    })
    .catch(err => {
      modalBody.innerHTML = 'Error loading character.';
    });

  const modal = new bootstrap.Modal(modalEl);
  modal.show();
}
```

If backend returns JSON, parse and render it instead of HTML.

### 5. Reuse Prior Fix
- Search the project for prior fixes mentioning “character modal” or “Error loading character.”
- Match previous patterns for consistency.

### 6. Preserve Existing Pages
Only modify:
- `admin_panel.php`
- Modal markup
- JS function
- PHP endpoint

Do **not** rename, move, or delete any live pages or image assets.

### 7. Output
- Updated modal and JS code.
- Fixed PHP endpoint (if parameter mismatch).
- Summary of what was changed.

---

### Questions to Ask if Unsure
1. What PHP file serves the character detail?
2. Is the modal supposed to render HTML or JSON?
3. Which Bootstrap version is loaded on admin pages?

**Do not delete or archive anything before confirming.**
