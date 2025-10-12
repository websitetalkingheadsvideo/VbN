# CSS File Analysis & Reorganization Plan

## Current Issues Identified:

### 1. **Mobile CSS Scattered Throughout File**
- Tablet CSS at line 20-42
- Main mobile CSS at line 2644-3000+ (large section)
- Additional mobile sections at:
  - Line 3340 (Morality responsive)
  - Line 3749 (Merits & Flaws responsive) 
  - Line 3956 (Health & Willpower responsive)

### 2. **Potential Simplification Opportunities**

#### **Redundant Properties:**
- Multiple font-family declarations for same elements
- Repeated color values (#780606, #8a0202, etc.)
- Duplicate padding/margin patterns
- Similar gradient patterns

#### **Over-Complex Selectors:**
- Deep nesting in some sections
- Overly specific selectors that could be simplified
- Redundant class combinations

#### **Inconsistent Naming:**
- Mix of BEM-like naming and traditional CSS
- Some classes could be more semantic

### 3. **Reorganization Plan**

#### **Phase 1: Move All Mobile CSS to Bottom**
1. Extract tablet CSS (lines 20-42)
2. Extract main mobile CSS (lines 2644-3000+)
3. Extract scattered mobile sections
4. Consolidate all responsive CSS at bottom

#### **Phase 2: Analyze for Simplification**
1. Identify redundant properties
2. Consolidate similar rules
3. Simplify complex selectors
4. Create CSS custom properties for repeated values

#### **Phase 3: Restructure for Maintainability**
1. Group related styles together
2. Add clear section headers
3. Organize by component/feature
4. Ensure logical flow

## Next Steps:
1. Extract all mobile CSS sections
2. Create consolidated responsive section at bottom
3. Analyze main CSS for simplification opportunities
4. Implement improvements systematically
