# Next Session: Questionnaire Question Rewrite & Enhancement

## Current Status (v0.4.2)
✅ **Questionnaire System Complete** - All 39 questions are working with database-driven system
✅ **Category System Fixed** - Removed Pre-Embrace/Post-Embrace, reassigned to appropriate categories
✅ **Visual Design Enhanced** - Dark-red headers, gold borders, professional results page
✅ **Testing Mode Working** - Direct access to Brujah, Tremere, and Gangrel results
✅ **Scoring Logic Fixed** - Clan scores only update on Next button click

## Next Session Goals

### 1. Question Content Review & Rewrite
- **Review all 39 questions** for clarity, relevance, and vampire theme
- **Rewrite poorly worded questions** to be more engaging and thematic
- **Ensure questions feel like vampire character creation** rather than generic personality tests
- **Add more vampire-specific scenarios** and dilemmas

### 2. Question Quality Improvements
- **Make questions more immersive** - Use vampire terminology and concepts
- **Improve answer options** - Make them more distinct and meaningful
- **Add atmospheric descriptions** - Enhance the gothic feel
- **Ensure cultural sensitivity** - Review for any problematic content

### 3. Potential New Questions
- **Consider adding 5-10 new questions** to make it a 45-50 question system
- **Focus on vampire-specific themes**:
  - Blood drinking preferences and methods
  - Relationship with humanity and mortals
  - Clan-specific power fantasies
  - Supernatural encounters and reactions
  - Vampire society and politics
  - Feeding habits and hunting methods

### 4. Category Enhancement
- **Review current categories** for better organization
- **Consider adding new categories**:
  - "Feeding" - Questions about blood drinking and hunting
  - "Supernatural" - Questions about other supernatural beings
  - "Politics" - Questions about vampire society and power structures
  - "Humanity" - Questions about maintaining human connections

### 5. Technical Improvements
- **Question difficulty balancing** - Ensure questions aren't too easy or hard
- **Clan scoring optimization** - Fine-tune the scoring weights
- **Question flow improvement** - Better progression from basic to complex
- **Mobile optimization** - Ensure all questions work well on mobile

## Files to Work With

### Primary Files:
- `populate_complete_39_questions.php` - Contains all question data
- `questionnaire.php` - Main questionnaire page
- `js/questionnaire.js` - Frontend functionality
- `css/questionnaire.css` - Styling

### Reference Files:
- `reference/Questions_1.md` - Original question set 1
- `reference/Questions_2.md` - Original question set 2  
- `reference/Questions_3.md` - Original question set 3
- `NEXT_SESSION_QUESTIONNAIRE.md` - Original questionnaire plan

## Current Question Categories (17 total):
1. **embrace** - The Embrace & Transformation (Question 21)
2. **personality** - Core Personality Traits (Questions 22-23)
3. **perspective** - Worldview & Philosophy (Question 24)
4. **powers** - Supernatural Abilities (Question 25)
5. **motivation** - Personal Goals & Drives (Question 26)
6. **supernatural** - Other Supernatural Beings (Question 27)
7. **secrets** - Hidden Truths & Secrets (Question 28)
8. **fears** - Deepest Fears & Dreads (Question 29)
9. **scenario** - Hypothetical Scenarios (Questions 11, 13, 15, 17, 18, 30-34)
10. **workplace** - Professional Situations (Questions 5, 8, 35)
11. **family** - Family & Relationships (Questions 3, 10, 36)
12. **social** - Social Interactions (Questions 1, 4, 6, 7, 9, 12, 37)
13. **moral** - Moral Dilemmas (Questions 2, 20, 38)
14. **power** - Power & Authority (Questions 14, 16, 19, 39)
15. **life** - Life-Changing Decisions (Question 40)

## Testing Access
- **Brujah**: `questionnaire.php?test=brujah`
- **Tremere**: `questionnaire.php?test=tremere`
- **Gangrel**: `questionnaire.php?test=gangrel`

## Success Criteria
- [ ] All questions feel authentically vampire-themed
- [ ] Questions are engaging and immersive
- [ ] Answer options are distinct and meaningful
- [ ] Question flow feels natural and progressive
- [ ] All questions work perfectly on mobile
- [ ] Clan scoring feels balanced and accurate
- [ ] Overall experience feels like vampire character creation

## Notes
- Current system is fully functional and ready for content improvements
- Database-driven system makes it easy to update questions
- Admin interface available for question management
- All visual styling is complete and professional
- Testing mode allows easy validation of changes