# Cursor AI Prompt: Use Laws Agent for VbN System Design & Character Creation

**TASK:** Use the Laws Agent (MCP tool `query_laws_agent`) to design, validate, and implement game systems and character creation features for the VbN (Vampire by Night) computer RPG—a faithful adaptation of *Laws of the Night Revised* from the World of Darkness universe.

---

## 🎯 PROJECT CONTEXT

- **Project:** VbN (computer RPG — Laws of the Night Revised / World of Darkness)
- **Stack:** PHP, JavaScript (vanilla), HTML, CSS, MySQL
- **Laws Agent:** MCP tool `query_laws_agent` available (31 rulebooks, ~4,500 pages)
- **Goal:** Faithful reproduction of game mechanics and character creation rules
- **Current Focus:** Character creator system and NPC generation

---

## 🔧 PRIMARY REQUIREMENTS

### 1. **MANDATORY: Use Laws Agent for All Rules**
- **Every game mechanic, rule, or design decision MUST be validated with the Laws Agent**
- Use `query_laws_agent` MCP tool before implementing any rule or system
- Include Laws Agent citations (book name + page number) for every rule implemented
- Example queries:
  - "How does Celerity work in Laws of the Night Revised? Provide core effects, activation, blood costs, and page references."
  - "What are the character creation rules for starting traits, abilities, and disciplines in Laws of the Night Revised?"
  - "What disciplines does clan Ventrue have in Laws of the Night Revised?"
  - "How do blood pools work? What is the starting blood pool for a neonate?"
  - "What are the rules for generation and how does it affect disciplines?"

### 2. **System Design & Validation**
Design and validate these core systems using Laws Agent queries:

**Character Creation:**
- Starting attributes (Physical/Social/Mental trait allocations)
- Starting abilities (allocation rules, specializations)
- Starting disciplines (clan disciplines, in-clan costs, out-of-clan costs)
- Blood pool mechanics (generation-based starting pools, maximum pools)
- Generation rules (how it affects disciplines, blood pool, starting XP)
- XP costs (traits, abilities, disciplines, backgrounds)
- Human vs Ghoul rules (if applicable)

**NPC Templates:**
- Generate NPC templates with proper clan disciplines
- Include generation-appropriate stats
- Validate discipline powers and costs
- Backgrounds appropriate to NPC roles

**Combat & Challenges:**
- Challenge resolution mechanics
- Trait + Ability difficulties
- Discipline activation rules
- Blood point costs for powers

### 3. **Output Format Requirements**
For each major mechanic designed, provide:

1. **Laws Agent Evidence Block:**
   - Query used: "How does [MECHANIC] work in Laws of the Night Revised?"
   - Laws Agent summary response (2-4 sentences)
   - Source citations: `[Book Name, Page X]`

2. **Implementation Design:**
   - Database schema changes (if needed)
   - PHP functions/methods to implement the rule
   - JavaScript validation/calculation logic
   - UI/UX considerations for character creator

3. **JSON Templates:**
   - Character creation templates (starting stats)
   - NPC templates (5+ examples across different clans)
   - Discipline power definitions with costs
   - Background definitions with point costs

4. **Code Integration Notes:**
   - Where to add code (file paths, line numbers if possible)
   - Database migration SQL (if needed)
   - Test cases for QA

### 4. **File Organization Rules** ⚠️
- **DO NOT edit files in the root directory** unless explicitly requested
- Use external CSS files in `css/` folder (no embedded `<style>` blocks)
- Use external JavaScript files in `js/` folder (no embedded `<script>` blocks)
- Follow existing project structure and naming conventions
- Apply all current workspace rules and standards

---

## 📋 DELIVERABLES

### Phase 1: Laws Agent Audit
Run Laws Agent queries for core domains:

1. **Character Creation Rules**
   - Starting trait points and allocation
   - Starting ability points and allocation
   - Starting discipline points (in-clan vs out-of-clan)
   - Starting XP (if any)
   - Generation selection and effects

2. **Discipline System**
   - List all 13 clans and their disciplines
   - Each discipline's powers (levels 1-5)
   - Blood point costs for each power
   - Activation requirements/challenges

3. **Blood Pool Mechanics**
   - Starting blood pool by generation
   - Maximum blood pool by generation
   - Blood point spending rules
   - Regeneration rules

4. **Attributes & Abilities**
   - All Physical/Social/Mental traits
   - All ability categories and specific abilities
   - Specialization rules
   - Ability maximums

5. **Backgrounds**
   - List of available backgrounds
   - Point costs and maximums
   - What each background provides

### Phase 2: Design Documents
Create structured design docs with Laws Agent citations:

**For Each Mechanic:**
- Title: `[Mechanic Name] — Design Document`
- Laws Agent query and response
- Source citations
- Proposed implementation (code-ready)
- Database schema (if needed)
- Test cases

### Phase 3: NPC Templates
Generate 5+ NPC templates with:
- JSON character objects (ready for database import)
- Clan, generation, disciplines, traits, abilities
- Background notes and role-play hooks
- All validated with Laws Agent (disciplines match clan, generation matches disciplines, etc.)

**Example NPC Format:**
```json
{
  "character_name": "Marcus Devereaux",
  "clan": "Toreador",
  "generation": 10,
  "nature": "Perfectionist",
  "demeanor": "Bon Vivant",
  "concept": "Elysium Keeper and Art Collector",
  "disciplines": [
    {"name": "Auspex", "level": 3},
    {"name": "Celerity", "level": 2},
    {"name": "Presence", "level": 4}
  ],
  "law_agent_citation": "[Laws of the Night Revised, Page X] - Toreador have Auspex, Celerity, Presence"
}
```

### Phase 4: Implementation Plan
- Priority list for integrating designs
- File-by-file breakdown of changes needed
- Database migration scripts (if any)
- QA test checklist

### Phase 5: Ambiguity Report
Document any:
- Rules that are ambiguous or conflicting in source material
- Recommended house rules (with rationale and Laws Agent citations)
- Areas where faithful reproduction requires interpretation

---

## 🛠️ TECHNICAL REQUIREMENTS

### Code Standards
- Follow all current workspace rules (external CSS/JS, no root edits, etc.)
- Use Taskmaster for project management and task tracking
- Apply strict typing where possible (PHP type hints, JSDoc for JS)
- Sanitize all database inputs (prepared statements)
- Follow DRY, KISS, YAGNI principles
- Pure functions preferred (no global state modifications)

### Database Integration
- Existing tables: `characters`, `character_traits`, `character_abilities`, `character_disciplines`, `character_backgrounds`
- Use existing connection patterns (`includes/connect.php`)
- Ensure compatibility with existing character import/export system

### UI/UX
- Dark, elegant "World of Darkness" aesthetic
- Responsive design
- Maintain consistency with existing character creator interface

---

## 📝 EXAMPLE WORKFLOW

**Step 1: Query Laws Agent**
```
Query: "What are the starting character creation rules for Laws of the Night Revised? Include trait allocation, ability points, discipline points, and starting XP."
```

**Step 2: Document Response**
```
Laws Agent Response:
"Characters start with 7/5/3 trait allocation (Physical/Social/Mental), 13 ability points, 3 discipline points (1 must be in-clan), and 30 starting experience points..."

Sources:
- Laws of the Night Revised, Page 45-48
- Character Creation Chapter, Page 12
```

**Step 3: Design Implementation**
```php
// Proposed implementation in character_creation.php
function calculateStartingTraits() {
    return [
        'physical' => 7,
        'social' => 5,
        'mental' => 3
    ];
}
```

**Step 4: Create JSON Template**
```json
{
  "starting_traits": {"physical": 7, "social": 5, "mental": 3},
  "starting_abilities": 13,
  "starting_disciplines": 3,
  "starting_xp": 30,
  "law_agent_sources": ["Laws of the Night Revised, Page 45-48"]
}
```

---

## ⚠️ SAFETY & LEGAL NOTES

- **Do NOT reproduce long verbatim passages** from rulebooks (copyright)
- Summarize rules and cite sources
- Use short quoted excerpts only as permitted (fair use for game implementation)
- Always paraphrase and cite Laws Agent sources
- If Laws Agent finds ambiguous rules, document them and recommend a choice

---

## 🎯 SUCCESS CRITERIA

✅ Every game mechanic has Laws Agent validation  
✅ All source citations included (book + page)  
✅ Code is production-ready and follows workspace standards  
✅ NPC templates are valid according to Laws of the Night Revised  
✅ Database schema supports all mechanics  
✅ Test cases provided for QA  
✅ Implementation plan is clear and prioritized  

---

## 🚀 GETTING STARTED

**Begin by running these Laws Agent queries in sequence:**

1. "What are the complete character creation rules for Laws of the Night Revised? Include all starting points, allocations, and restrictions."

2. "List all 13 vampire clans in Laws of the Night Revised and their three clan disciplines."

3. "How do blood pools work in Laws of the Night Revised? Include starting pools by generation, maximum pools, and spending rules."

4. "What are all the disciplines available in Laws of the Night Revised and what powers does each level grant?"

5. "What backgrounds are available in Laws of the Night Revised and what do they cost in character creation?"

**Then use Taskmaster to create tasks for implementing each validated system.**

---

**Remember:** The Laws Agent is your canonical source. Every rules decision must reference it. If ambiguous, present options with recommendations.















