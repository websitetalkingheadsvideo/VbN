# NPC Agent Briefing - Quick Start Guide

## 🚀 Get Started in 3 Steps

### Step 1: Run Database Migration
Visit this URL in your browser (admin login required):
```
http://vbn.talkingheads.video/database/add_npc_briefing_fields.php
```

You should see:
- ✅ Successfully added agentNotes column
- ✅ Successfully added actingNotes column
- ✅ Both columns verified successfully!

### Step 2: Access NPC Briefing
Navigate to the new admin page:
```
http://vbn.talkingheads.video/admin/admin_npc_briefing.php
```

Or click **"📋 NPC Briefing"** from any admin page navigation bar.

### Step 3: View a Character
1. Find an NPC in the list (try searching or using clan filter)
2. Click the **📋** button to open the briefing
3. Review all character information in **Agent View** mode
4. Switch to **Edit Notes** mode to add your briefing notes
5. Click **Save Notes** when done

## 📋 What You'll See

### The NPC List
- **Stats**: Total NPCs | Active | Retired
- **Filters**: Clan dropdown, name search
- **Table**: ID, Name, Clan, Generation, Status, Created Date
- **Actions**: 📋 Briefing button, ✏️ Edit button

### The Briefing Modal

#### Agent View Mode (Read-Only)
Perfect for quick reference during sessions:
- **Core Identity**: Nature, Demeanor, Concept, Clan, Generation, Sire
- **Traits**: Physical, Social, Mental (with negative traits highlighted)
- **Key Abilities**: "Occult x4, Academics x3, Science x3"
- **Disciplines**: "Thaumaturgy x3, Auspex x2"
- **Backgrounds**: Resource levels and descriptions
- **Biography**: Full character background
- **Agent Notes**: Your AI-formatted briefing (editable)
- **Acting Notes**: Your post-session notes (editable)

#### Edit Notes Mode
Add or update your notes:
- Large text areas for both note types
- **Agent Notes**: AI-formatted briefing for playing the character
- **Acting Notes**: Your observations after playing them

## 💡 Usage Tips

### For Agent Notes
Include information like:
- Character voice/speech patterns
- Key personality traits
- Important relationships
- Goals and motivations
- Secrets they're hiding
- How they typically react to situations

Example:
```
VOICE: Deep, commanding, slight British accent
PERSONALITY: Calculated, patient, values tradition
GOALS: Expand clan influence, protect the Masquerade
RELATIONSHIPS: Respects Prince, distrusts Tremere
SECRETS: Owes a major boon to a Nosferatu elder
REACTIONS: Stays calm under pressure, quick to delegate
```

### For Acting Notes
Record what happened when you played them:
```
Session 12 (Oct 26, 2025):
- Appeared at Elysium, subtly challenged the Primogen
- Players noticed the character is more cunning than they thought
- Made a deal with the Malkavian PC - owes them a minor boon
- Next appearance: Should follow up on the deal
```

## 🎯 Common Workflows

### Before a Session
1. Open NPC Briefing page
2. Search for NPCs you plan to use
3. Review their Agent Notes
4. Refresh yourself on key abilities and traits
5. Keep the modal open during play for quick reference

### After a Session
1. Return to NPCs you played
2. Switch to Edit Notes mode
3. Add Acting Notes about what happened
4. Update Agent Notes if the character evolved
5. Save changes

### Bulk Preparation
1. List all your NPCs
2. Filter by clan if doing clan-specific prep
3. Add Agent Notes to multiple characters
4. Use consistent formatting for easy scanning

## 🔍 Features

- **Sorting**: Click any column header to sort
- **Filtering**: Use clan dropdown or name search
- **Pagination**: Change page size (20/50/100 per page)
- **Quick Access**: One-click briefing from table
- **Keyboard**: Press ESC to close modal
- **Mobile**: Works on tablets (responsive design)

## ⚙️ Navigation

The NPC Briefing page is accessible from:
- Admin Panel (👥 Characters)
- Sire/Childe pages
- All admin pages with navigation bar

Look for the **📋 NPC Briefing** button in the admin navigation.

## 🎭 Use Cases

### During Live Play
- Quick reference for NPC stats and abilities
- Check what traits the character has
- Review their personality (nature/demeanor)
- Reference your acting notes from previous appearances

### Prep Work
- Create formatted briefings for all recurring NPCs
- Document speech patterns and mannerisms
- Track NPC development across sessions
- Maintain consistency in portrayal

### Collaboration
- Share formatted notes with co-STs
- Document NPC interactions for continuity
- Track plot threads involving NPCs
- Maintain shared chronicle information

## 🚨 Troubleshooting

### "Unauthorized" Error
- Make sure you're logged in as admin
- Check your session hasn't expired

### No NPCs Showing
- Verify characters have `player_name = 'ST/NPC'`
- Check the database migration ran successfully
- Try refreshing the page

### Save Notes Not Working
- Check browser console for errors
- Verify you're in Edit Notes mode
- Ensure API endpoint is accessible

### Modal Not Opening
- Check browser console for JavaScript errors
- Verify `admin_npc_briefing.js` is loading
- Try hard refresh (Ctrl+F5)

## 📚 Files Reference

- **Main Page**: `admin/admin_npc_briefing.php`
- **JavaScript**: `js/admin_npc_briefing.js`
- **Briefing API**: `admin/api_npc_briefing.php`
- **Save API**: `admin/api_update_npc_notes.php`
- **Migration**: `database/add_npc_briefing_fields.php`

## ✅ Quick Test

1. Run migration ✓
2. Open NPC Briefing page ✓
3. Click briefing button on any NPC ✓
4. Verify all character info displays ✓
5. Switch to Edit Notes ✓
6. Add test notes ✓
7. Save notes ✓
8. Reopen briefing to confirm save ✓

---

**Ready to use!** 🎉

Start by running the database migration, then explore your NPCs!

