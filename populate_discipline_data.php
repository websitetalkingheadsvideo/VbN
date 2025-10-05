<?php
// Script to populate the database with discipline data
require_once 'includes/connect.php';

try {
    $pdo = new PDO("mysql:host=localhost;dbname=lotn_characters", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

echo "Starting discipline data population...\n";

// Define all disciplines with their categories
$disciplines = [
    // Clan Disciplines
    'Animalism' => 'Clan',
    'Auspex' => 'Clan',
    'Celerity' => 'Clan',
    'Dominate' => 'Clan',
    'Fortitude' => 'Clan',
    'Obfuscate' => 'Clan',
    'Potence' => 'Clan',
    'Presence' => 'Clan',
    'Protean' => 'Clan',
    
    // Blood Sorcery Disciplines
    'Thaumaturgy' => 'BloodSorcery',
    'Necromancy' => 'BloodSorcery',
    'Koldunic Sorcery' => 'BloodSorcery',
    
    // Advanced Disciplines
    'Obtenebration' => 'Advanced',
    'Chimerstry' => 'Advanced',
    'Dementation' => 'Advanced',
    'Quietus' => 'Advanced',
    'Vicissitude' => 'Advanced',
    'Serpentis' => 'Advanced',
    'Daimoinon' => 'Advanced',
    'Melpominee' => 'Advanced',
    'Valeren' => 'Advanced',
    'Mortis' => 'Advanced'
];

// Define all discipline powers
$disciplinePowers = [
    'Animalism' => [
        [1, 'Sense the Beast', 'The vampire can sense the presence of animals within a certain radius and understand their basic emotional state.'],
        [2, 'Feral Whispers', 'The vampire can communicate directly with animals, understanding their thoughts and conveying complex messages.'],
        [3, 'Quell the Beast', 'The vampire can calm and control the Beast within themselves or other vampires, reducing frenzy.'],
        [4, 'Beckoning', 'The vampire can call animals to their location from a considerable distance.'],
        [5, 'Animal Control', 'The vampire gains complete control over animals, able to command them to perform any action.']
    ],
    'Auspex' => [
        [1, 'Aura Perception', 'The vampire can see the emotional and spiritual auras surrounding living beings.'],
        [2, 'Telepathy', 'The vampire can read surface thoughts and emotions from other beings.'],
        [3, 'Psychometry', 'The vampire can read the history and emotional resonance of objects by touching them.'],
        [4, 'Premonition', 'The vampire gains glimpses of future events through dreams, visions, or sudden insights.'],
        [5, 'Sense the Unseen', 'The vampire can perceive supernatural phenomena, spirits, and otherworldly entities.']
    ],
    'Celerity' => [
        [1, 'Quickness', 'The vampire can move and react at superhuman speeds, allowing them to perform actions much faster than normal.'],
        [2, 'Sprint', 'The vampire can achieve incredible bursts of speed over short distances.'],
        [3, 'Enhanced Reflexes', 'The vampire\'s reaction time becomes so fast they can dodge bullets and catch arrows in flight.'],
        [4, 'Blur', 'The vampire moves so fast they become a blur, making them nearly impossible to target.'],
        [5, 'Accelerated Movement', 'The vampire can maintain superhuman speed for extended periods.']
    ],
    'Dominate' => [
        [1, 'Command', 'The vampire can issue simple, direct commands that mortals and weaker vampires must obey.'],
        [2, 'Mesmerize', 'The vampire can place a target in a trance-like state, making them highly suggestible.'],
        [3, 'Memory Alteration', 'The vampire can modify, erase, or implant false memories in a target\'s mind.'],
        [4, 'Suggestion', 'The vampire can plant subtle suggestions in a target\'s mind that they will act upon later.'],
        [5, 'Mental Domination', 'The vampire gains complete control over a target\'s mind, able to command them to perform any action.']
    ],
    'Fortitude' => [
        [1, 'Resistance', 'The vampire can resist physical damage and environmental hazards better than normal.'],
        [2, 'Endurance', 'The vampire can maintain physical activity and resist fatigue for extended periods.'],
        [3, 'Pain Tolerance', 'The vampire can ignore pain and continue functioning normally even when severely injured.'],
        [4, 'Damage Reduction', 'The vampire can reduce the damage taken from physical attacks.'],
        [5, 'Supernatural Stamina', 'The vampire gains almost supernatural levels of physical resilience.']
    ],
    'Obfuscate' => [
        [1, 'Cloak of Shadows', 'The vampire can blend into shadows and darkness, becoming difficult to see and track.'],
        [2, 'Vanish', 'The vampire can become completely invisible for short periods.'],
        [3, 'Mask of a Thousand Faces', 'The vampire can change their appearance to look like anyone they have seen.'],
        [4, 'Silent Movement', 'The vampire can move without making any sound, becoming completely silent.'],
        [5, 'Unseen Presence', 'The vampire can make others forget they ever saw them.']
    ],
    'Potence' => [
        [1, 'Prowess', 'The vampire gains superhuman physical strength, allowing them to perform feats far beyond mortal capabilities.'],
        [2, 'Shove', 'The vampire can deliver powerful shoves and pushes that can knock down or throw opponents great distances.'],
        [3, 'Knockdown', 'The vampire can deliver devastating blows that can knock down even the strongest opponents.'],
        [4, 'Crushing Blow', 'The vampire can deliver attacks so powerful they can crush through armor and break weapons.'],
        [5, 'Leap', 'The vampire can jump incredible distances and heights, covering great distances with a single bound.']
    ],
    'Presence' => [
        [1, 'Awe', 'The vampire can project an aura of majesty and power that makes others feel small and insignificant.'],
        [2, 'Dread Gaze', 'The vampire can project an aura of fear and intimidation that can cause others to flee or submit.'],
        [3, 'Entrancement', 'The vampire can charm and captivate others, making them highly susceptible to influence.'],
        [4, 'Majesty', 'The vampire can project an aura of divine authority that makes others feel compelled to worship them.'],
        [5, 'Inspire', 'The vampire can use their presence to inspire others to greatness, enhancing their abilities.']
    ],
    'Protean' => [
        [1, 'Shape of the Beast', 'The vampire can transform into a wolf or bat, gaining the abilities and instincts of the chosen animal form.'],
        [2, 'Claws', 'The vampire can extend razor-sharp claws from their fingers, making their hands into deadly weapons.'],
        [3, 'Feral Leap', 'The vampire can leap incredible distances and heights, covering great distances with a single bound.'],
        [4, 'Flight (Bat Form)', 'The vampire can transform into a bat and gain the ability to fly.'],
        [5, 'Natural Armor', 'The vampire can harden their skin to create natural armor that provides protection against physical attacks.']
    ],
    'Thaumaturgy' => [
        [1, 'Lure of Flames', 'The vampire can create and control fire, using their blood magic to summon flames.'],
        [2, 'Shield of Thorns', 'The vampire can create protective barriers using their blood magic.'],
        [3, 'Rite of Blood', 'The vampire can use their blood to power magical rituals and create mystical effects.'],
        [4, 'Circle of Protection', 'The vampire can create magical circles that provide protection against supernatural threats.'],
        [5, 'Blood Bond', 'The vampire can create mystical bonds between themselves and others using their blood.']
    ],
    'Necromancy' => [
        [1, 'Sense Death', 'The vampire can sense the presence of death, decay, and the recently deceased.'],
        [2, 'Command Dead', 'The vampire can command and control undead creatures, forcing them to obey their will.'],
        [3, 'Drain Life', 'The vampire can drain the life force from living beings, using their necromantic powers.'],
        [4, 'Haunt', 'The vampire can create ghostly manifestations and supernatural phenomena.'],
        [5, 'Animate Corpse', 'The vampire can raise the dead as undead servants.']
    ],
    'Obtenebration' => [
        [1, 'Shadow Cloak', 'The vampire can wrap themselves in shadows, becoming difficult to see.'],
        [2, 'Dark Tendrils', 'The vampire can create shadowy tendrils that can grab, constrict, and harm opponents.'],
        [3, 'Shroud of Night', 'The vampire can create areas of supernatural darkness that can block light.'],
        [4, 'Shadow Walk', 'The vampire can merge with shadows, becoming one with darkness.'],
        [5, 'Nightmarish Strike', 'The vampire can use their control over darkness to create attacks that cause both physical and psychological damage.']
    ],
    'Chimerstry' => [
        [1, 'Minor Illusion', 'The vampire can create small, simple illusions that can fool the senses.'],
        [2, 'Disguise', 'The vampire can create complex illusions that can change their appearance or the appearance of others.'],
        [3, 'Confusion', 'The vampire can create illusions that can confuse and disorient opponents.'],
        [4, 'Hallucinatory Image', 'The vampire can create complex, detailed illusions that can fool multiple senses.'],
        [5, 'Invisibility Illusion', 'The vampire can create illusions that can make themselves or others completely invisible.']
    ],
    'Dementation' => [
        [1, 'Awe of Madness', 'The vampire can project an aura of madness that can cause others to become confused and disoriented.'],
        [2, 'Fear Projection', 'The vampire can project intense fear into the minds of others.'],
        [3, 'Confusion', 'The vampire can create mental confusion in others, making them unable to distinguish between reality and illusion.'],
        [4, 'Irrational Fear', 'The vampire can create specific, irrational fears in others.'],
        [5, 'Frenzy Inducement', 'The vampire can cause others to enter a state of frenzy, making them lose control.']
    ],
    'Quietus' => [
        [1, 'Poison Glands', 'The vampire can produce and secrete various poisons from their body.'],
        [2, 'Silent Kill', 'The vampire can kill others silently and without leaving obvious signs of violence.'],
        [3, 'Respiratory Poison', 'The vampire can create poisons that can be delivered through the air.'],
        [4, 'Hemorrhage', 'The vampire can cause internal bleeding in others, creating wounds that can be fatal.'],
        [5, 'Lethal Strike', 'The vampire can deliver attacks that can cause instant death.']
    ],
    'Vicissitude' => [
        [1, 'Fleshcraft', 'The vampire can reshape and modify living flesh, changing the appearance and structure of themselves and others.'],
        [2, 'Alter Form', 'The vampire can make more dramatic changes to their own body, altering their shape and structure.'],
        [3, 'Skin Hardening', 'The vampire can harden their skin to create natural armor that provides protection against physical attacks.'],
        [4, 'Stretch Limb', 'The vampire can extend and stretch their limbs to reach distant objects or attack from unexpected angles.'],
        [5, 'Weaponize Flesh', 'The vampire can transform parts of their body into weapons.']
    ],
    'Serpentis' => [
        [1, 'Hypnotic Gaze', 'The vampire can use their eyes to hypnotize others, making them highly suggestible.'],
        [2, 'Venomous Bite', 'The vampire can produce and deliver venom through their bite.'],
        [3, 'Serpent\'s Strike', 'The vampire can attack with incredible speed and precision, striking like a snake.'],
        [4, 'Mesmerize', 'The vampire can create powerful hypnotic effects that can control the behavior of others.'],
        [5, 'Shape Serpent', 'The vampire can transform into a large serpent, gaining the abilities and instincts of a snake.']
    ],
    'Koldunic Sorcery' => [
        [1, 'Elemental Bolt', 'The vampire can create and project bolts of elemental energy.'],
        [2, 'Minor Ward', 'The vampire can create small protective barriers using elemental energy.'],
        [3, 'Fire Blast', 'The vampire can create powerful blasts of fire that can burn opponents and cause massive damage.'],
        [4, 'Ice Shard', 'The vampire can create and project shards of ice that can pierce through armor.'],
        [5, 'Earth Spike', 'The vampire can cause spikes of earth to erupt from the ground.']
    ],
    'Daimoinon' => [
        [1, 'Fear Aura', 'The vampire can project an aura of fear that can cause others to become terrified and potentially flee.'],
        [2, 'Infernal Grasp', 'The vampire can create shadowy hands that can grab, constrict, and harm opponents from a distance.'],
        [3, 'Summon Demon', 'The vampire can call upon infernal entities to aid them.'],
        [4, 'Curse', 'The vampire can place curses on others that can cause various negative effects over time.'],
        [5, 'Dark Inspiration', 'The vampire can use their connection to the infernal to inspire others to commit acts of evil or violence.']
    ],
    'Melpominee' => [
        [1, 'Captivating Song', 'The vampire can use their voice to create musical effects that can charm and captivate others.'],
        [2, 'Charm', 'The vampire can use their voice to create effects that can make others more susceptible to their influence.'],
        [3, 'Enthrall Audience', 'The vampire can use their voice to create effects that can captivate large groups of people.'],
        [4, 'Inspire Emotion', 'The vampire can use their voice to create specific emotional effects in others.'],
        [5, 'Hypnotic Performance', 'The vampire can use their voice to create powerful hypnotic effects that can control the behavior of others.']
    ],
    'Valeren' => [
        [1, 'Healing Touch', 'The vampire can use their supernatural abilities to heal wounds and injuries in others.'],
        [2, 'Restore Vitality', 'The vampire can use their supernatural abilities to restore energy and vitality to others.'],
        [3, 'Detox', 'The vampire can use their supernatural abilities to remove poisons and toxins from others.'],
        [4, 'Protective Ward', 'The vampire can use their supernatural abilities to create protective effects that can shield others from harm.'],
        [5, 'Ritual Aid', 'The vampire can use their supernatural abilities to enhance the effectiveness of rituals and ceremonies.']
    ],
    'Mortis' => [
        [1, 'Sense Death', 'The vampire can sense the presence of death, decay, and the recently deceased.'],
        [2, 'Drain Life', 'The vampire can drain the life force from living beings, using their connection to death.'],
        [3, 'Haunting Presence', 'The vampire can create ghostly manifestations and supernatural phenomena.'],
        [4, 'Wither', 'The vampire can cause living things to wither and decay, using their connection to death.'],
        [5, 'Deathly Chill', 'The vampire can create effects that can cause extreme cold and death-like conditions.']
    ]
];

// Define clans with their information
$clans = [
    'Assamite' => [
        'description' => 'Warrior-assassins with a strict code of honor',
        'weakness' => 'Must drink blood from those they have wronged',
        'theme' => 'Honor, vengeance, and martial prowess',
        'playstyle' => 'Combat-focused with strong moral code',
        'availability' => 'PC Available'
    ],
    'Brujah' => [
        'description' => 'Rebels and revolutionaries with a passion for change',
        'weakness' => 'Prone to frenzy when insulted or challenged',
        'theme' => 'Rebellion, passion, and social change',
        'playstyle' => 'Social and physical combat specialists',
        'availability' => 'PC Available'
    ],
    'Caitiff' => [
        'description' => 'Vampires without a clan, often outcasts',
        'weakness' => 'No clan weakness, but social stigma',
        'theme' => 'Independence, adaptability, and survival',
        'playstyle' => 'Flexible with access to any discipline',
        'availability' => 'PC Available'
    ],
    'Followers of Set' => [
        'description' => 'Corruptors and tempters who serve the Egyptian god Set',
        'weakness' => 'Cannot enter holy ground',
        'theme' => 'Corruption, temptation, and forbidden knowledge',
        'playstyle' => 'Social manipulation and corruption',
        'availability' => 'Admin Approval'
    ],
    'Gangrel' => [
        'description' => 'Wild and bestial vampires with animalistic traits',
        'weakness' => 'Gain animalistic features when using disciplines',
        'theme' => 'Nature, survival, and the wild',
        'playstyle' => 'Physical combat and wilderness survival',
        'availability' => 'PC Available'
    ],
    'Giovanni' => [
        'description' => 'Necromancers and death merchants',
        'weakness' => 'Cannot create blood bonds',
        'theme' => 'Death, necromancy, and family business',
        'playstyle' => 'Necromancy and social manipulation',
        'availability' => 'PC Available'
    ],
    'Lasombra' => [
        'description' => 'Shadow manipulators and political schemers',
        'weakness' => 'Cannot be seen in mirrors or photographs',
        'theme' => 'Shadows, politics, and manipulation',
        'playstyle' => 'Social manipulation and shadow magic',
        'availability' => 'PC Available'
    ],
    'Malkavian' => [
        'description' => 'Mad seers with prophetic visions',
        'weakness' => 'Must have a derangement',
        'theme' => 'Madness, prophecy, and hidden knowledge',
        'playstyle' => 'Information gathering and social manipulation',
        'availability' => 'PC Available'
    ],
    'Nosferatu' => [
        'description' => 'Information brokers and spies',
        'weakness' => 'Horrifying appearance that cannot be hidden',
        'theme' => 'Secrets, information, and hidden knowledge',
        'playstyle' => 'Information gathering and stealth',
        'availability' => 'PC Available'
    ],
    'Ravnos' => [
        'description' => 'Tricksters and illusionists',
        'weakness' => 'Must commit acts of deception regularly',
        'theme' => 'Illusion, trickery, and deception',
        'playstyle' => 'Illusion and social manipulation',
        'availability' => 'PC Available'
    ],
    'Toreador' => [
        'description' => 'Artists and socialites with refined tastes',
        'weakness' => 'Can be mesmerized by beauty or art',
        'theme' => 'Art, beauty, and social grace',
        'playstyle' => 'Social manipulation and artistic expression',
        'availability' => 'PC Available'
    ],
    'Tremere' => [
        'description' => 'Blood sorcerers and magical researchers',
        'weakness' => 'Cannot create childer without permission',
        'theme' => 'Magic, knowledge, and blood sorcery',
        'playstyle' => 'Blood magic and information gathering',
        'availability' => 'PC Available'
    ],
    'Tzimisce' => [
        'description' => 'Body manipulators and territorial lords',
        'weakness' => 'Must sleep in their native soil',
        'theme' => 'Transformation, territory, and body modification',
        'playstyle' => 'Body modification and territorial control',
        'availability' => 'PC Available'
    ],
    'Ventrue' => [
        'description' => 'Noble leaders and social manipulators',
        'weakness' => 'Can only feed from specific types of people',
        'theme' => 'Leadership, nobility, and social control',
        'playstyle' => 'Social manipulation and leadership',
        'availability' => 'PC Available'
    ]
];

// Define clan-discipline access mapping
$clanDisciplineAccess = [
    'Assamite' => ['Animalism', 'Celerity', 'Obfuscate', 'Quietus'],
    'Brujah' => ['Celerity', 'Potence', 'Presence'],
    'Caitiff' => ['Animalism', 'Auspex', 'Celerity', 'Dominate', 'Fortitude', 'Obfuscate', 'Potence', 'Presence', 'Protean', 'Thaumaturgy', 'Necromancy', 'Koldunic Sorcery', 'Obtenebration', 'Chimerstry', 'Dementation', 'Quietus', 'Vicissitude', 'Serpentis', 'Daimoinon', 'Melpominee', 'Valeren', 'Mortis'],
    'Followers of Set' => ['Animalism', 'Obfuscate', 'Presence', 'Serpentis'],
    'Gangrel' => ['Animalism', 'Fortitude', 'Protean'],
    'Giovanni' => ['Dominate', 'Fortitude', 'Necromancy', 'Mortis'],
    'Lasombra' => ['Dominate', 'Obfuscate', 'Obtenebration'],
    'Malkavian' => ['Auspex', 'Dementation', 'Obfuscate'],
    'Nosferatu' => ['Animalism', 'Fortitude', 'Obfuscate'],
    'Ravnos' => ['Animalism', 'Chimerstry', 'Fortitude'],
    'Toreador' => ['Auspex', 'Celerity', 'Presence'],
    'Tremere' => ['Auspex', 'Dominate', 'Thaumaturgy'],
    'Tzimisce' => ['Animalism', 'Auspex', 'Dominate', 'Vicissitude'],
    'Ventrue' => ['Dominate', 'Fortitude', 'Presence']
];

// Insert disciplines
echo "Inserting disciplines...\n";
$disciplineIds = [];
foreach ($disciplines as $name => $category) {
    $stmt = $pdo->prepare("INSERT INTO disciplines (name, category) VALUES (?, ?)");
    $stmt->execute([$name, $category]);
    $disciplineIds[$name] = $pdo->lastInsertId();
    echo "  - Inserted discipline: $name ($category)\n";
}

// Insert discipline powers
echo "\nInserting discipline powers...\n";
foreach ($disciplinePowers as $disciplineName => $powers) {
    $disciplineId = $disciplineIds[$disciplineName];
    foreach ($powers as $power) {
        $stmt = $pdo->prepare("INSERT INTO discipline_powers (discipline_id, level, name, description) VALUES (?, ?, ?, ?)");
        $stmt->execute([$disciplineId, $power[0], $power[1], $power[2]]);
        echo "  - Inserted power: $disciplineName Level {$power[0]} - {$power[1]}\n";
    }
}

// Insert clans
echo "\nInserting clans...\n";
$clanIds = [];
foreach ($clans as $name => $data) {
    $stmt = $pdo->prepare("INSERT INTO clans (name, description, weakness, theme, playstyle, availability) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$name, $data['description'], $data['weakness'], $data['theme'], $data['playstyle'], $data['availability']]);
    $clanIds[$name] = $pdo->lastInsertId();
    echo "  - Inserted clan: $name\n";
}

// Insert clan-discipline access
echo "\nInserting clan-discipline access...\n";
foreach ($clanDisciplineAccess as $clanName => $disciplineNames) {
    $clanId = $clanIds[$clanName];
    foreach ($disciplineNames as $disciplineName) {
        $disciplineId = $disciplineIds[$disciplineName];
        $stmt = $pdo->prepare("INSERT INTO clan_disciplines (clan_id, discipline_id) VALUES (?, ?)");
        $stmt->execute([$clanId, $disciplineId]);
        echo "  - Linked clan $clanName to discipline $disciplineName\n";
    }
}

echo "\n✅ Discipline data population completed successfully!\n";
echo "Total disciplines inserted: " . count($disciplines) . "\n";
echo "Total discipline powers inserted: " . array_sum(array_map('count', $disciplinePowers)) . "\n";
echo "Total clans inserted: " . count($clans) . "\n";
echo "Total clan-discipline links inserted: " . array_sum(array_map('count', $clanDisciplineAccess)) . "\n";
?>
