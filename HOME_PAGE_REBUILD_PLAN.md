# 🏰 Valley by Night - Home Page Rebuild Plan

## 📋 Project Overview

**Goal:** Create a professional home page with consistent header/footer navigation for the Valley by Night chronicle management system.

**Tagline:** *"On your first night among the Kindred, the Prince dies—and the city of Phoenix bleeds intrigue"*

**Setting:** Phoenix, 1994 - A neonate's first night becomes a deadly investigation when the Prince is murdered.

---

## 🎯 Taskmaster Project Created

**Tag:** `home-page-rebuild`  
**Total Tasks:** 12  
**Status:** Ready to begin

### To switch to this project:
```bash
task-master use-tag home-page-rebuild
```

---

## 📊 Task Breakdown (12 Tasks)

### ⚡ High Priority Tasks (6)

**Task 1: Rename index.php to character_sheet.php** ✓  
- Preserve existing character sheet interface
- Frees up index.php for new home page
- Dependencies: None
- **Start with this!**

**Task 2: Create Header Component** ✓  
- File: `includes/header.php`
- Title: "Valley by Night- A Vampire Tale"
- Logo placeholder (80x80px, left side)
- Username display (right side)
- Version number (right side)
- Gothic styling
- Dependencies: Task 1

**Task 3: Create Footer Component** ✓  
- File: `includes/footer.php`
- Title with link to index.php
- Copyright with current year
- Gothic styling consistent with header
- Dependencies: Task 2

**Task 4: Create New Home Dashboard** ✓  
- File: `index.php` (new)
- Session authentication check
- User role detection (player/admin)
- Include header & footer
- Chronicle tagline & summary display
- Gothic styling
- Dependencies: Tasks 1, 2, 3

**Task 5: Build Player Dashboard View** ✓  
- Welcome message with username
- "Create New Character" button
- List user's characters:
  * Character name, clan, concept
  * Badge for unfinalized characters
  * Click to view/edit
- Chat room link (placeholder)
- Chronicle info display
- Dependencies: Task 4

**Task 6: Build Admin Dashboard View** ✓  
- Welcome message with username
- Statistics panel:
  * Total characters
  * Number of PCs
  * Number of NPCs
- Admin action links:
  * Create Character → lotn_char_create.php
  * AI Locations → admin/admin_locations.php
  * Items Database → admin/admin_equipment.php
  * Character List → admin/admin_panel.php
  * AI Plots (disabled/greyed out - future)
- Chronicle info display
- Dependencies: Task 4

---

### 🔧 Medium Priority Tasks (5)

**Task 7: Database Query Functions**
- Fetch user's characters with clan info
- Count total characters, PCs, NPCs
- Check character finalized status
- Dependencies: Tasks 4, 5, 6

**Task 8: Apply Gothic Styling**
- Dark background: #1a0f0f
- Blood red accents: #8B0000
- Cream text: #f5e6d3
- Card-based layout
- Responsive design
- Hover effects
- Dependencies: Tasks 4, 5, 6

**Task 9: Update Character Sheet Links**
- Find all links to index.php (for viewing characters)
- Update to character_sheet.php
- Check in: dashboard, admin pages, character list
- Dependencies: Task 1

**Task 10: Add Headers/Footers to Existing Pages**
- Add to: dashboard.php, lotn_char_create.php, chat.php, character_sheet.php, users.php
- Ensure consistent navigation
- Dependencies: Tasks 2, 3

**Task 11: System Integration Testing**
- Test login flow
- Test player dashboard & character list
- Test admin dashboard & statistics
- Test mobile responsiveness
- Verify all links work
- Dependencies: Tasks 4-10

---

### 📝 Low Priority Tasks (1)

**Task 12: Update Documentation**
- Update START_HERE.md
- Document new page structure
- Header/footer usage guidelines
- Update version to v0.5.0
- Dependencies: Task 11

---

## 🗂️ File Structure After Completion

```
VbN/
├── index.php (NEW - home dashboard)
├── character_sheet.php (RENAMED from index.php)
├── lotn_char_create.php (updated with header/footer)
├── dashboard.php (updated with header/footer)
├── login.php
├── chat.php (updated with header/footer)
├── users.php (updated with header/footer)
│
├── includes/
│   ├── header.php (NEW)
│   ├── footer.php (NEW)
│   └── db.php
│
├── admin/
│   ├── admin_locations.php
│   ├── admin_equipment.php
│   └── admin_panel.php
│
└── reference/game-lore/
    ├── Valley_by_Night_Chronicle_Summary.docx
    └── Valley_by_Night_Prologue_Gangrel_Brujah.pdf
```

---

## 🎨 Design Specifications

### Header Layout
```
┌──────────────────────────────────────────────────────────────┐
│ [Logo 80x80]  VALLEY BY NIGHT - A VAMPIRE TALE   User: John │
│               Placeholder                         v0.5.0     │
└──────────────────────────────────────────────────────────────┘
```

### Footer Layout
```
┌──────────────────────────────────────────────────────────────┐
│              Valley by Night (link to home)                  │
│              © 2025 All Rights Reserved                      │
└──────────────────────────────────────────────────────────────┘
```

### Player Dashboard Layout
```
┌─ Welcome, John! ──────────────────────────────────────────┐
│                                                            │
│  "On your first night among the Kindred, the Prince      │
│   dies—and the city of Phoenix bleeds intrigue"           │
│                                                            │
│  [Chronicle Summary Panel - Gothic Card]                  │
│                                                            │
├─ Your Characters ─────────────────────────────────────────┤
│  [+ Create New Character]                                 │
│                                                            │
│  ┌─ Marcus Kane (Brujah) ─────────────┐ [DRAFT BADGE]    │
│  │ Concept: Street Fighter              │                 │
│  └─────────────────────────────────────┘                  │
│                                                            │
│  ┌─ Sarah Winters (Toreador) ──────────┐                 │
│  │ Concept: Artist                      │                 │
│  └─────────────────────────────────────┘                  │
│                                                            │
├─ Actions ─────────────────────────────────────────────────┤
│  [💬 Chat Room] (placeholder)                            │
└───────────────────────────────────────────────────────────┘
```

### Admin Dashboard Layout
```
┌─ Welcome, Admin! ─────────────────────────────────────────┐
│                                                            │
│  "On your first night among the Kindred, the Prince      │
│   dies—and the city of Phoenix bleeds intrigue"           │
│                                                            │
├─ Chronicle Statistics ────────────────────────────────────┤
│  Total Characters: 28  │  PCs: 3  │  NPCs: 25           │
└───────────────────────────────────────────────────────────┘
│                                                            │
├─ Character Management ────────────────────────────────────┤
│  [+ Create New Character]                                 │
│  [📋 Character List (Edit/Delete)]                        │
│                                                            │
├─ Game Management ─────────────────────────────────────────┤
│  [🏛️ AI Locations Manager]                               │
│  [⚔️ Items Database]                                      │
│  [📖 AI Plots Manager] (disabled - coming soon)          │
└───────────────────────────────────────────────────────────┘
```

---

## 🎨 Color Scheme

- **Background:** #1a0f0f (Very dark red-brown)
- **Accents:** #8B0000 (Dark red / blood red)
- **Text:** #f5e6d3 (Cream / parchment)
- **Cards:** rgba(139, 0, 0, 0.1) with border
- **Hover:** Lighten accent color

---

## 🔑 Key Features

### Authentication
- Check session on index.php load
- Redirect to login.php if not authenticated
- Load user data from users table
- Determine role (player/admin)

### Player View
- Shows only their characters
- Indicates draft vs finalized
- Links to create/edit characters
- Chat room (placeholder)

### Admin View
- Character statistics
- Full admin controls
- Locations manager
- Items database
- Future plots system

### Chronicle Info
Both views display:
- Tagline in prominent position
- Chronicle summary in gothic card
- Atmospheric text about Phoenix 1994

---

## 📚 Database Queries Needed

```sql
-- Get player's characters
SELECT c.*, cl.name as clan_name 
FROM characters c 
LEFT JOIN clans cl ON c.clan_id = cl.id 
WHERE c.user_id = ? 
ORDER BY c.finalized DESC, c.character_name ASC;

-- Get character statistics (admin)
SELECT 
  COUNT(*) as total,
  SUM(CASE WHEN is_npc = 0 THEN 1 ELSE 0 END) as pcs,
  SUM(CASE WHEN is_npc = 1 THEN 1 ELSE 0 END) as npcs
FROM characters;
```

---

## ✅ Success Criteria

- [x] PRD created and documented
- [x] 12 tasks created in Taskmaster
- [ ] Header component works on all pages
- [ ] Footer component works on all pages
- [ ] Players see only their content
- [ ] Admins see statistics and tools
- [ ] Mobile responsive
- [ ] No broken links
- [ ] Chronicle info displays beautifully
- [ ] Gothic theme consistent throughout

---

## 🚀 Getting Started

1. **Switch to project:**
   ```bash
   task-master use-tag home-page-rebuild
   ```

2. **View next task:**
   ```bash
   task-master next
   ```

3. **Start with Task 1:**
   ```bash
   task-master set-status --id=1 --status=in-progress
   ```

4. **Follow dependency chain:**
   Task 1 → Tasks 2 & 3 → Task 4 → Tasks 5 & 6 → Etc.

---

## 📖 Documentation

- **PRD:** `reference/setup-guides/HOME_PAGE_REBUILD_PRD.txt`
- **This Plan:** `HOME_PAGE_REBUILD_PLAN.md`
- **Chronicle Docs:** `reference/game-lore/`

---

**Ready to transform your chronicle management system into a beautiful, gothic web application!** 🦇

*Project created: October 12, 2025*  
*Target Version: v0.5.0*

