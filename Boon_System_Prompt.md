# Prompt: Create Standalone Boon System and add it to admin_panel.php

You are an expert PHP/MySQL and Bootstrap developer working on the **Valley by Night** project.

Right now we do **not** have the full Agents system in the database. We still want to start tracking **boons** (favors owed) between NPCs, PCs, or future Agents.

Your job is to:
1. Create a **Boon Ledger** page in the admin area.
2. Create a **boons** table in the database that works even if the Agents table does not exist yet.
3. Add a **link/button** from `admin_panel.php` to the new Boon Ledger page.
4. Write the code so it can be upgraded later to reference real Agent/Character IDs.

Do **not** create fallbacks. Follow these instructions only.

---

## 1. Database: create `boons` table

Make a new table that does **not** require foreign keys for now.

```sql
CREATE TABLE boons (
  boon_id INT AUTO_INCREMENT PRIMARY KEY,
  giver_name VARCHAR(150) NOT NULL,
  receiver_name VARCHAR(150) NOT NULL,
  -- placeholders for future integration with agents/characters
  giver_ref INT NULL,
  receiver_ref INT NULL,
  boon_type ENUM('Trivial','Minor','Major','Life') NOT NULL DEFAULT 'Trivial',
  status ENUM('Owed','Called','Paid','Broken') NOT NULL DEFAULT 'Owed',
  description TEXT,
  related_event VARCHAR(255),
  date_created DATETIME DEFAULT CURRENT_TIMESTAMP,
  date_updated DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_status (status)
);
```

Notes:
- `giver_name` and `receiver_name` are text for now because the Agents system is not in place yet.
- `giver_ref` and `receiver_ref` are optional future fields you will hook up later to `agents` or `characters`.
- No foreign keys right now.

---

## 2. Boon Ledger page

Create a new admin page (for example: `admin/boon_ledger.php`) that:

- Lists all boons in a Bootstrap table:
  - `ID | Giver | Receiver | Type | Status | Description | Created | Actions`
- Has a **“New Boon”** button that opens a Bootstrap modal form.
- The form fields should be:
  - Giver Name (text)
  - Receiver Name (text)
  - Boon Type (select: Trivial, Minor, Major, Life)
  - Description (textarea)
- On submit, insert into `boons`.

Actions per row:
- Edit / View (load into modal)
- Mark Paid
- Mark Broken
- Delete

Use the same styling patterns as the existing admin pages (buttons, cards, responsive table). Do not introduce horizontal scrollbars.

---

## 3. Link from `admin_panel.php`

Edit `https://vbn.talkingheads.video/admin/admin_panel.php` and add a new link/button/card to the **Boon Ledger**.

- Label it: **Boons**
- Link to: `boon_ledger.php` (or whatever filename you created)
- Match existing Bootstrap layout so the panel still looks the same.
- If the panel uses a grid of cards, add one more card in the same style.
- Do **not** add anything that forces a scrollbar; let Bootstrap wrap to the next row.

Example structure (adjust to match actual file):

```html
<div class="col-md-3 mb-3">
  <a href="boon_ledger.php" class="btn btn-primary btn-block">Boons</a>
</div>
```

If the admin panel uses cards instead of buttons, mirror that.

---

## 4. Prepare for future Agent integration

In the Boon Ledger code, add comments/placeholders like:

```php
// TODO: when agents/characters table is available,
// replace giver_name/receiver_name with dropdowns populated from that table.
// Use giver_ref/receiver_ref for the real IDs.
```

Do **not** try to query a table that doesn’t exist yet.

---

## 5. Taskmaster plan

1. Create `boons` table (no foreign keys yet).
2. Build `boon_ledger.php` with list + create/edit modal.
3. Add CRUD handlers (create, update status, delete).
4. Update `admin_panel.php` to add “Boons” link/button.
5. Test creating, editing, and deleting boons from the admin.
6. Output a short report of files changed and where to plug in Agents later.

---

## 6. Deliverables

- `admin/boon_ledger.php` (or matching admin path)
- SQL to create `boons`
- Updated `admin_panel.php` with “Boons” entry
- Inline TODOs for future Agent/Character integration
