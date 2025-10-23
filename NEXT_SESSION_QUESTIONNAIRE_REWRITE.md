# 📝 Next Session: Questionnaire Question Rewrite

## 🎯 **Session Goal**
Rewrite and improve the questionnaire questions to better align with vampire clan characteristics and create more meaningful clan differentiation.

---

## 📊 **Current Status - v0.2.6**

### ✅ **Completed Features**
- **Enhanced UI**: Fade-out transitions, results display with 400px clan logos
- **Category System**: Questions organized by categories (embrace, personality, powers, etc.)
- **Cinematic Animations**: Category entries with 75% transparency and smooth transitions
- **Clan Tracking**: Real-time clan score tracking with popup display
- **Results Display**: Beautiful clan reveal with descriptions and all scores
- **Action Buttons**: Retake questionnaire and create character options

### 🗂️ **Current Question Categories**
1. **Embrace** - How you became a vampire
2. **Personality** - Character traits and behaviors
3. **Perspective** - Views on humanity and immortality
4. **Powers** - Preferred supernatural abilities
5. **Motivation** - Personal goals and drives
6. **Supernatural** - Views on other supernatural beings
7. **Secrets** - Hidden aspects of character
8. **Fears** - Greatest immortal fears
9. **Scenario** - Situational responses (multiple questions)
10. **Workplace** - Professional conflict resolution
11. **Family** - Family crisis management
12. **Social** - Social situation responses
13. **Moral** - Ethical dilemma handling
14. **Power** - Leadership and authority
15. **Life** - Major life decisions

---

## 🔄 **Question Rewrite Objectives**

### **Primary Goals**
1. **Better Clan Differentiation**: Ensure each clan has distinct, meaningful choices
2. **Improved Balance**: More even distribution of clan points across questions
3. **Enhanced Thematic Coherence**: Questions that better reflect vampire lore
4. **Clearer Answer Options**: More distinct and meaningful answer choices
5. **Better Scenarios**: More engaging and vampire-specific situations

### **Clan Focus Areas**

#### **Ventrue (Blue Bloods)**
- Leadership, authority, tradition
- Political maneuvering, social hierarchy
- Responsibility, duty, ruling others
- Wealth, power, influence

#### **Tremere (Warlocks)**
- Knowledge, secrets, magic
- Scholarly pursuits, research
- Mystical arts, blood magic
- Hidden knowledge, ancient lore

#### **Brujah (Rabble)**
- Passion, rebellion, justice
- Fighting for beliefs, challenging authority
- Emotional intensity, social causes
- Revolutionary spirit, direct action

#### **Nosferatu (Sewer Rats)**
- Information, secrets, shadows
- Hidden knowledge, surveillance
- Resourcefulness, survival
- Understanding true power through information

#### **Malkavian (Lunatics)**
- Madness, insight, different perspective
- Prophetic visions, hidden wisdom
- Unconventional thinking, riddles
- Seeing what others cannot

#### **Toreador (Degenerates)**
- Art, beauty, aesthetics
- Passion for the finer things
- Emotional depth, artistic expression
- Appreciation of human creativity

#### **Gangrel (Outlanders)**
- Nature, survival, independence
- Animal instincts, primal power
- Self-reliance, wilderness
- Connection to the natural world

---

## 📋 **Rewrite Strategy**

### **Question Types to Improve**
1. **Generic Questions**: Make more vampire-specific
2. **Weak Clan Differentiation**: Ensure each answer clearly favors specific clans
3. **Unbalanced Scoring**: Redistribute clan weights for better balance
4. **Vague Scenarios**: Make more concrete and engaging
5. **Missing Categories**: Add questions for underrepresented areas

### **New Question Categories to Consider**
- **Hunting**: How you approach feeding
- **Domain**: How you establish territory
- **Allies**: How you build relationships
- **Enemies**: How you handle threats
- **Tradition**: Views on vampire society
- **Innovation**: Approach to change
- **Mortality**: Views on death and dying
- **Legacy**: What you want to leave behind

### **Scoring Improvements**
- **Current**: 1-3 points per answer
- **Proposed**: 1-5 points for stronger differentiation
- **Balance**: Ensure no single clan dominates
- **Variety**: Mix of high-impact and subtle questions

---

## 🛠️ **Technical Considerations**

### **Database Structure**
- Questions stored in `questionnaire_questions` table
- Fields: `category`, `question`, `answer1-4`, `clanWeight1-4`
- Clan weights in format: `"clan:points,clan:points"`

### **Files to Modify**
- `populate_questionnaire.php` - Update question data
- `questionnaire.php` - May need minor adjustments
- Database - Run update script to replace questions

### **Testing Requirements**
- Test all 20 questions load properly
- Verify clan tracking works correctly
- Check results display shows proper clan
- Ensure animations work smoothly

---

## 🎯 **Success Metrics**

### **Clan Balance**
- No single clan should score >40% more than others on average
- Each clan should have 3-5 "strong" questions (3+ points)
- Each clan should have 2-3 "weak" questions (1 point or less)

### **Question Quality**
- Each answer should clearly favor 1-2 clans
- Questions should be engaging and vampire-themed
- Scenarios should be realistic and meaningful
- Categories should be well-distributed

### **User Experience**
- Questions should feel immersive and thematic
- Results should feel accurate and meaningful
- Transitions should remain smooth
- Overall flow should be engaging

---

## 📝 **Next Steps**

1. **Review Current Questions**: Analyze existing questions for improvement areas
2. **Draft New Questions**: Create improved versions with better clan differentiation
3. **Balance Scoring**: Ensure fair distribution of clan points
4. **Test Implementation**: Verify all technical aspects work correctly
5. **User Testing**: Get feedback on question quality and results accuracy

---

**Ready for next session: Questionnaire Question Rewrite! 🧛‍♂️**
