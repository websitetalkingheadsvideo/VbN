{
  "category": "Rules Summary",
  "type": "Supernatural Template",
  "name": "Ghoul",
  "source": "Laws of the Night: Revised",
  "description": "Mortals empowered by vampire Vitae. Ghouls exist between the mortal and vampiric states—servants, enforcers, and addicts to their masters' blood.",
  "details": {
    "creation": {
      "method": "Mortal drinks one point of vampire Vitae.",
      "duration": "Effects last one lunar month per feeding.",
      "maintenance": "Must drink Vitae monthly or lose powers and age rapidly."
    },
    "blood_bond": {
      "automatic": "One-step blood bond upon first drink.",
      "progression": "Three separate feedings create a complete bond.",
      "effect": "Emotional dependency and loyalty to domitor."
    },
    "physical_effects": {
      "enhancements": ["Increased strength", "Faster healing", "Extended lifespan"],
      "healing": "May spend Vitae to heal like a vampire (1 point per level of bashing/lethal).",
      "limitations": ["No sunlight weakness", "No frenzy at fire", "Cannot enter torpor"]
    },
    "disciplines": {
      "access": "May learn domitor’s Clan Disciplines only.",
      "limit": "Usually up to 1 or 2 dots total.",
      "cost": "Experience cost as per vampire rules."
    },
    "blood_pool": {
      "total_capacity": 10,
      "vampiric_blood_limit": 1,
      "uses": ["Heal damage", "Fuel Disciplines", "Enhance attributes (if allowed)"]
    },
    "aging": {
      "while_ghoul": "Aging halts with continued Vitae use.",
      "without_vitae": "Rapid aging resumes; may die if centuries old."
    },
    "drawbacks": {
      "addiction": "Psychological dependence on Vitae.",
      "status": "Considered property under Camarilla law.",
      "mortality": "Still vulnerable to mundane harm and death."
    },
    "roleplay_notes": {
      "themes": ["Addiction", "Loyalty", "Power imbalance"],
      "story_uses": ["Bodyguards", "Spies", "Long-term servants seeking Embrace"]
    }
  },
  "mechanics": {
    "blood_pool_max": 10,
    "vitae_storage": 1,
    "can_learn_disciplines": true,
    "discipline_max": 2,
    "requires_vitae_maintenance": true,
    "bond_steps": 3,
    "sunlight_vulnerability": false,
    "torpor": false,
    "healing_rate": "1 blood point per health level"
  }
}


{
  "character_name": "Example Ghoul Name",
  "player_name": "NPC or Player Name",
  "template": "Ghoul",
  "chronicle": "Example Chronicle",
  "concept": "Mortal servant addicted to vampire Vitae",
  "domitor": {
    "name": "Domitor Name",
    "clan": "Clan of Domitor",
    "relationship": "Loyal / Bound / Rebellious / Obsessive"
  },
  "attributes": {
    "physical": {
      "strength": 3,
      "dexterity": 2,
      "stamina": 3
    },
    "social": {
      "charisma": 2,
      "manipulation": 2,
      "appearance": 2
    },
    "mental": {
      "perception": 2,
      "intelligence": 2,
      "wits": 2
    }
  },
  "abilities": {
    "talents": ["Alertness", "Athletics", "Brawl"],
    "skills": ["Drive", "Firearms", "Stealth"],
    "knowledges": ["Investigation", "Law", "Occult"]
  },
  "advantages": {
    "backgrounds": {
      "herd": 1,
      "resources": 1,
      "retainers": 0
    },
    "disciplines": {
      "potence": 1
    },
    "virtues": {
      "conscience": 2,
      "self_control": 3,
      "courage": 3
    }
  },
  "mechanics": {
    "blood_pool_max": 10,
    "current_vitae": 1,
    "human_blood": 9,
    "vitae_source": "Domitor feedings",
    "requires_monthly_feeding": true,
    "blood_bond_steps": 1,
    "max_discipline_dots": 2,
    "sunlight_vulnerability": false,
    "torpor": false,
    "can_heal_with_vitae": true,
    "aging_suspended": true
  },
  "morality": {
    "humanity": 6,
    "path": "Humanity",
    "derangements": []
  },
  "equipment": ["Handgun", "Phone", "Keys", "Street Clothes"],
  "notes": "Ghouls are loyal retainers and addicts, caught between mortal life and vampiric servitude. Monthly Vitae feeding required to maintain powers and youth."
}
