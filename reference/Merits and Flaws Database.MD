
{
  "merits": {
    "physical": [
      {
        "name": "Acute Senses",
        "cost": 1,
        "category": "Physical",
        "description": "One of your senses (sight, hearing, smell, touch, or taste) is unusually sharp. You reduce the difficulty of rolls involving that sense by 2.",
        "effects": {
          "sense_bonus": 2
        }
      },
      {
        "name": "Ambidextrous",
        "cost": 1,
        "category": "Physical",
        "description": "You can use either hand with equal facility; off-hand penalties vanish.",
        "effects": {
          "remove_offhand_penalty": true
        }
      },
      {
        "name": "Catlike Balance",
        "cost": 1,
        "category": "Physical",
        "description": "You have superior balance and agility. Tasks involving movement on narrow surfaces or recovering from stumbles are easier.",
        "effects": {
          "balance_bonus": true
        }
      },
      {
        "name": "Bruiser",
        "cost": 1,
        "category": "Physical",
        "description": "Your intimidating physical presence gives you an edge. Intimidation rolls versus those who haven’t shown dominance over you get –1 to difficulty (i.e. one extra die).",
        "effects": {
          "intimidation_bonus_against_unproven": 1
        }
      },
      {
        "name": "Daredevil",
        "cost": 3,
        "category": "Physical",
        "description": "You excel at high-risk stunts, acrobatics or recklessness. In daring maneuvers, you reduce difficulty or gain bonus dice.",
        "effects": {
          "stunt_bonus": true
        }
      },
      {
        "name": "Efficient Digestion",
        "cost": 3,
        "category": "Physical",
        "description": "You derive more nourishment from blood than is typical. When feeding, you gain extra Blood points (without exceeding your pool).",
        "effects": {
          "extra_blood_per_feed": 1
        }
      },
      {
        "name": "Eat Food",
        "cost": 1,
        "category": "Physical",
        "description": "Though you do not truly depend on it, you can eat mortal food without suffering the usual penalties or complications.",
        "effects": {
          "normal_food_tolerance": true
        }
      }
    ],
    "mental": [
      {
        "name": "Common Sense",
        "cost": 1,
        "category": "Mental",
        "description": "You tend to avoid obvious mistakes and pitfalls; you get a bonus or reduced difficulty on practical reasoning or everyday judgments.",
        "effects": {
          "practical_rolls_bonus": true
        }
      },
      {
        "name": "Concentration",
        "cost": 1,
        "category": "Mental",
        "description": "You can maintain focus even under stress or distraction. You take fewer penalties when concentrating on a task under duress.",
        "effects": {
          "resist_interference_bonus": true
        }
      },
      {
        "name": "Eidetic Memory",
        "cost": 2,
        "category": "Mental",
        "description": "You recall details with sharp precision. You can reconstruct conversations, documents, or scenes with high fidelity.",
        "effects": {
          "recall_difficulty_reduced": true
        }
      },
      {
        "name": "Iron Will",
        "cost": 3,
        "category": "Mental",
        "description": "Your mental fortitude is exceptional; you resist fear, influence, or coercion more easily.",
        "effects": {
          "willpower_resistance_bonus": true
        }
      },
      {
        "name": "Light Sleeper",
        "cost": 2,
        "category": "Mental",
        "description": "You awaken easily to threats or disturbances. Surprises or stealth attacks are less likely to catch you off guard.",
        "effects": {
          "reduced_surprise_penalty": true
        }
      },
      {
        "name": "Time Sense",
        "cost": 1,
        "category": "Mental",
        "description": "You have an internal sense of passing time. You can estimate durations accurately, notice when time is being manipulated, or detect anomalies.",
        "effects": {
          "time_estimation_bonus": true
        }
      }
    ],
    "social": [
      {
        "name": "Natural Leader",
        "cost": 1,
        "category": "Social",
        "description": "You inspire others, take command, or rally loyalty. Leadership or persuasion rolls in group settings are easier.",
        "effects": {
          "group_influence_bonus": true
        }
      },
      {
        "name": "Reputation",
        "cost": 2,
        "category": "Social",
        "description": "You have standing or renown in your community or among other Kindred. Add 3 dice to social interactions with those who recognize you. :contentReference[oaicite:0]{index=0}",
        "effects": {
          "bonus_dice_social_recognizers": 3
        }
      },
      {
        "name": "Prestigious Sire",
        "cost": 1,
        "category": "Social",
        "description": "Your sire’s reputation benefits you; in certain social contexts with your sire’s allies, you gain advantage or easier access. :contentReference[oaicite:1]{index=1}",
        "effects": {
          "sire_association_bonus": true
        }
      }
    ],
    "supernatural": [
      {
        "name": "Oracular Ability",
        "cost": 3,
        "category": "Supernatural",
        "description": "You sometimes receive prophetic insight, visions, or omens that guide your actions or warn of danger. :contentReference[oaicite:2]{index=2}",
        "effects": {
          "vision_trigger": true
        }
      },
      {
        "name": "Medium",
        "cost": 2,
        "category": "Supernatural",
        "description": "You are especially attuned to spirits. You may see or converse with ghosts more easily than others. :contentReference[oaicite:3]{index=3}",
        "effects": {
          "spirit_sense_bonus": true
        }
      },
      {
        "name": "Magic Resistance",
        "cost": 2,
        "category": "Supernatural",
        "description": "You resist magical or supernatural effects more strongly. The difficulty or potency of such effects against you is reduced.",
        "effects": {
          "resist_supernatural_effects": true
        }
      },
      {
        "name": "Lucky",
        "cost": 3,
        "category": "Supernatural",
        "description": "Fortune occasionally favors you. In critical situations, you may reroll a die or reduce difficulty.",
        "effects": {
          "bonus_die_once": true
        }
      },
      {
        "name": "Danger Sense",
        "cost": 2,
        "category": "Supernatural",
        "description": "You have a sense of impending danger more reliable than mere awareness. When threatened, your Storyteller secretly rolls vs. your Perception + Alertness and may warn you in advance. :contentReference[oaicite:4]{index=4}",
        "effects": {
          "foreshadow_warning": true
        }
      }
    ]
  },
  "flaws": {
    "physical": [
      {
        "name": "Bad Sight",
        "cost": 1,
        "category": "Physical",
        "description": "Your vision is impaired. Rolls relying on sight have +2 difficulty (i.e. fewer dice). :contentReference[oaicite:5]{index=5}",
        "effects": {
          "vision_penalty": 2
        }
      },
      {
        "name": "Hard of Hearing",
        "cost": 1,
        "category": "Physical",
        "description": "Your hearing is deficient. Rolls based on hearing have +2 difficulty.",
        "effects": {
          "hearing_penalty": 2
        }
      },
      {
        "name": "One Eye",
        "cost": 2,
        "category": "Physical",
        "description": "You have lost or never had one eye. You suffer penalties on depth perception, peripheral checks, and ranged tasks when that side is obscured. :contentReference[oaicite:6]{index=6}",
        "effects": {
          "depth_perception_penalty": 2
        }
      },
      {
        "name": "Fourteenth Generation",
        "cost": 2,
        "category": "Physical",
        "description": "Your vampiric lineage is weak. You are more vulnerable, perhaps with fewer innate advantages or lower resistance thresholds. :contentReference[oaicite:7]{index=7}",
        "effects": {
          "lineage_weakness": true
        }
      },
      {
        "name": "Monstrous",
        "cost": 3,
        "category": "Physical",
        "description": "Your appearance is grotesque or disturbing, reducing your Appearance to 0 and causing penalties in social interactions. :contentReference[oaicite:8]{index=8}",
        "effects": {
          "appearance_override": 0,
          "social_penalty": true
        }
      },
      {
        "name": "Slow Healing",
        "cost": 3,
        "category": "Physical",
        "description": "Your body is slow to recover. Healing takes doubled time or more, and aggravated damage may not heal. :contentReference[oaicite:9]{index=9}",
        "effects": {
          "healing_rate_multiplier": 2
        }
      }
    ],
    "mental": [
      {
        "name": "Nightmares",
        "cost": 1,
        "category": "Mental",
        "description": "You are plagued by disturbing dreams that disturb rest, impose fatigue or stress penalties upon waking.",
        "effects": {
          "rest_penalty": true
        }
      },
      {
        "name": "Phobia",
        "cost": "1-3",
        "category": "Mental",
        "description": "You suffer an irrational fear of a specific object, creature or situation. When confronted, you incur significant penalties or may be forced to flee. :contentReference[oaicite:10]{index=10}",
        "effects": {
          "phobia_trigger_penalty": true
        }
      },
      {
        "name": "Short Fuse",
        "cost": 2,
        "category": "Mental",
        "description": "You have little patience or impulse control. Under provocation or stress, you may snap, suffer penalties, or lose control.",
        "effects": {
          "impulse_penalty": true
        }
      },
      {
        "name": "Soft-Hearted",
        "cost": 1,
        "category": "Mental",
        "description": "You are emotionally susceptible. Manipulation, appeals to empathy or emotional strife are more effective against you.",
        "effects": {
          "emotional_susceptibility": true
        }
      },
      {
        "name": "Weak-Willed",
        "cost": 2,
        "category": "Mental",
        "description": "Your willpower is fragile. You suffer extra difficulty resisting mental pressure, charms or coercion. :contentReference[oaicite:11]{index=11}",
        "effects": {
          "willpower_resist_penalty": 1
        }
      }
    ],
    "social": [
      {
        "name": "Dark Secret",
        "cost": 1,
        "category": "Social",
        "description": "You hold a secret that, if revealed, could ruin or shame you. Discovery inflicts social penalties or dramatic consequences.",
        "effects": {
          "social_penalty_if_revealed": true
        }
      },
      {
        "name": "Prey Exclusion",
        "cost": 1,
        "category": "Social",
        "description": "You refuse to feed on a specified class (e.g. children, animals, etc.). Violating this leads to negative consequences (frenzy, Humanity loss). :contentReference[oaicite:12]{index=12}",
        "effects": {
          "feeding_restriction": true
        }
      },
      {
        "name": "Sire’s Resentment",
        "cost": 1,
        "category": "Social",
        "description": "Your sire harbors ill will toward you. You incur penalties in social dealings involving your sire’s allies or in family politics. :contentReference[oaicite:13]{index=13}",
        "effects": {
          "social_penalty_related_to_sire": true
        }
      },
      {
        "name": "Clan Enmity",
        "cost": 2,
        "category": "Social",
        "description": "One clan holds a grudge against you. You suffer a –2 dice (or +2 difficulty) on social rolls involving that clan. :contentReference[oaicite:14]{index=14}",
        "effects": {
          "penalty_against_clan": 2
        }
      }
    ],
    "supernatural": [
      {
        "name": "Cursed",
        "cost": "1-5",
        "category": "Supernatural",
        "description": "You bear a supernatural curse. Depending on severity, you endure sporadic hindrances, vulnerabilities, or stigma. :contentReference[oaicite:15]{index=15}",
        "effects": {
          "curse_effect": true
        }
      },
      {
        "name": "Haunted",
        "cost": 3,
        "category": "Supernatural",
        "description": "Spirits or supernatural forces cling to you. You suffer interference, disturbances, or heightened sensitivity to ghostly presence. :contentReference[oaicite:16]{index=16}",
        "effects": {
          "spiritual_interference": true
        }
      },
      {
        "name": "Hunted",
        "cost": 4,
        "category": "Supernatural",
        "description": "You are actively pursued by someone or something (vampire hunters, enemies, spirits). You are at constant risk of ambush, betrayal, or exposure. :contentReference[oaicite:17]{index=17}",
        "effects": {
          "persistent_threat": true
        }
      },
      {
        "name": "Grip of the Damned",
        "cost": 4,
        "category": "Supernatural",
        "description": "A supernatural compulsion or curse has hold over you. At times you lose control, are compelled, or the “grip” imposes hardship or interference. :contentReference[oaicite:18]{index=18}",
        "effects": {
          "compulsion_interference": true
        }
      }
    ]
  }
}

