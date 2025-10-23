<?php
// Complete questionnaire population with ALL 39 questions
include "includes/connect.php";

// Clear existing data first
mysqli_query($conn, "DELETE FROM questionnaire_questions");

// Questions from Questions_3.md (Pre-Embrace Questions 1-10)
$questions3 = [
    [
        "category" => "social",
        "question" => "It is lunch hour at your workplace cafeteria. You are in line when your ex-partner walks in with someone new - the same person they swore was \"just a friend\" during your relationship. Your ex loudly announces, \"Oh look, there is the one I told you about,\" followed by laughter. Several coworkers turn to watch. Your ex brushes past you deliberately, still chuckling. The cafeteria has gone quiet.",
        "answer1" => "You finish getting lunch calmly and spend the next week learning everything about their new relationship while carefully adjusting how others perceive them",
        "answer2" => "You follow them outside immediately and make clear such disrespect will not be tolerated",
        "answer3" => "You watch without reacting, cataloging every detail, knowing this information will be useful later",
        "answer4" => "",
        "clanWeight1" => "ventrue:3,tremere:2",
        "clanWeight2" => "brujah:3,nosferatu:2",
        "clanWeight3" => "tremere:2,nosferatu:2,malkavian:2",
        "clanWeight4" => ""
    ],
    [
        "category" => "moral",
        "question" => "You are walking to your car after work when you notice someone keying the driver side door. As you get closer, you recognize them - a neighbor you have had ongoing disputes with about noise complaints. They see you approaching, drop the keys in their pocket, and stand their ground with arms crossed. Two other people are nearby loading groceries into their cars, clearly aware of what is happening.",
        "answer1" => "You pull out your phone and calmly document the damage while asking them questions designed to get them to incriminate themselves on recording",
        "answer2" => "You feel the surge of anger and step forward, demanding they pay for damages right now or face the consequences",
        "answer3" => "You turn away without acknowledging them, already planning how to make their life systematically more difficult through official channels they will not see coming",
        "answer4" => "",
        "clanWeight1" => "ventrue:3,toreador:2",
        "clanWeight2" => "brujah:3,gangrel:2",
        "clanWeight3" => "tremere:3,nosferatu:2",
        "clanWeight4" => ""
    ],
    [
        "category" => "family",
        "question" => "You are at a family dinner when your sibling suddenly announces they have been offered your dream job - the position you have been working toward for three years. They knew you were interviewing for it. Everyone at the table congratulates them enthusiastically. Your parent says, \"Well, I guess they just wanted someone more qualified.\" Your sibling smirks at you across the table while accepting praise.",
        "answer1" => "You keep your expression neutral and spend the rest of dinner observing family dynamics, noting who supports whom and why",
        "answer2" => "You excuse yourself immediately, knowing if you stay you will say something that cannot be taken back",
        "answer3" => "You smile and raise your glass in a toast, already calculating how to position yourself for an even better opportunity while subtly undermining their success",
        "answer4" => "",
        "clanWeight1" => "malkavian:3,nosferatu:2",
        "clanWeight2" => "gangrel:3,brujah:2",
        "clanWeight3" => "ventrue:3,toreador:2",
        "clanWeight4" => ""
    ],
    [
        "category" => "social",
        "question" => "Your best friend promised to help you move apartments this weekend - you have been planning it for a month. An hour before they are supposed to arrive, they text: \"Sorry, got a better offer. Going to the beach instead. You understand, right?\" You are standing in your half-packed apartment, the moving truck arrives in three hours, and you have already given notice to your landlord.",
        "answer1" => "You text back something cordial and start calling other people, making note of who actually shows up when you need them",
        "answer2" => "You call them immediately, voice tight with anger, making it clear this friendship is over if they do not show up",
        "answer3" => "You say nothing, finish the move alone, and begin quietly distancing yourself while ensuring they feel the loss of your friendship in practical ways",
        "answer4" => "",
        "clanWeight1" => "toreador:3,gangrel:2",
        "clanWeight2" => "brujah:3",
        "clanWeight3" => "nosferatu:3,tremere:2",
        "clanWeight4" => ""
    ],
    [
        "category" => "workplace",
        "question" => "You are in a meeting where your manager presents \"their\" idea to upper management - except it is your idea, word-for-word from the proposal you sent them last week. They are getting praise and a potential promotion for it. They glance at you once during the presentation with a slight smile. Nobody else in the room knows this was originally yours.",
        "answer1" => "You remain silent during the meeting, but immediately begin documenting everything and building a case to present to HR through proper channels",
        "answer2" => "You interrupt the presentation to point out that this was your idea, demanding credit in front of everyone present",
        "answer3" => "You let them have this moment while quietly building your own network above them, ensuring when you make your move, they have no support",
        "answer4" => "",
        "clanWeight1" => "ventrue:3,tremere:2",
        "clanWeight2" => "brujah:3,gangrel:2",
        "clanWeight3" => "nosferatu:3,toreador:2",
        "clanWeight4" => ""
    ],
    [
        "category" => "social",
        "question" => "You are at a bar with friends when someone from your past - a former bully from high school - recognizes you and starts loudly making fun of embarrassing things from years ago. Your current friends are watching. The bully is surrounded by their own group, clearly performing for them. Other patrons are starting to pay attention.",
        "answer1" => "You smile politely and engage them in conversation, subtly steering the discussion to topics where you can make them look foolish without appearing aggressive",
        "answer2" => "You stand up and tell them exactly what you think of them, ready to physically back up your words if necessary",
        "answer3" => "You appear completely unbothered, almost detached, as if watching the situation from outside yourself while memorizing everything about them",
        "answer4" => "",
        "clanWeight1" => "ventrue:3,toreador:2",
        "clanWeight2" => "brujah:3,gangrel:2",
        "clanWeight3" => "malkavian:3,nosferatu:2",
        "clanWeight4" => ""
    ],
    [
        "category" => "social",
        "question" => "You discover your romantic partner has been seeing someone else for months. Mutual friends knew but said nothing. When you confront your partner, they shrug and say, \"You were too boring. I needed excitement.\" They are meeting the other person tonight at a restaurant you introduced them to. Your partner leaves, expecting no consequences.",
        "answer1" => "You take a long walk to clear your head, then systematically separate your lives - finances, belongings, social circles - with surgical precision",
        "answer2" => "You show up at that restaurant tonight because they need to understand this does not end on their terms",
        "answer3" => "You withdraw into yourself, processing the betrayal privately while your mind catalogs patterns you missed and possibilities for what comes next",
        "answer4" => "",
        "clanWeight1" => "ventrue:3,gangrel:2",
        "clanWeight2" => "brujah:3",
        "clanWeight3" => "malkavian:3,tremere:2",
        "clanWeight4" => ""
    ],
    [
        "category" => "workplace",
        "question" => "You are waiting for an important job interview when another candidate - someone you know casually - walks by and \"accidentally\" spills coffee all over your clothes. They apologize profusely while obviously suppressing a smile. Your interview is in ten minutes. You are now stained and disheveled. The receptionist looks sympathetic but says nothing.",
        "answer1" => "You ask to reschedule, explaining what happened while remaining professional, then research everything about this person for future reference",
        "answer2" => "You walk into the interview exactly as you are and use the incident to demonstrate how you handle unexpected problems under pressure",
        "answer3" => "You clean up as best you can and go into the interview, but you are already planning how to ensure this person never succeeds in this industry",
        "answer4" => "",
        "clanWeight1" => "tremere:3,toreador:2",
        "clanWeight2" => "brujah:3,gangrel:2",
        "clanWeight3" => "ventrue:3,nosferatu:2",
        "clanWeight4" => ""
    ],
    [
        "category" => "social",
        "question" => "You hear from multiple sources that someone has been spreading false rumors about you - claiming you are unreliable, dishonest, and difficult to work with. You trace it back to a colleague who smiled to your face yesterday. The damage is already done; people are treating you differently. You have a major project presentation next week that could define your career.",
        "answer1" => "You focus entirely on making your presentation flawless while carefully rebuilding relationships one conversation at a time",
        "answer2" => "You confront them directly in front of witnesses, forcing them to either admit the lies or defend them publicly",
        "answer3" => "You say nothing but begin collecting evidence of their own misconduct, knowing the truth will surface when you choose to reveal it",
        "answer4" => "",
        "clanWeight1" => "ventrue:3,toreador:2",
        "clanWeight2" => "brujah:3,gangrel:2",
        "clanWeight3" => "tremere:3,nosferatu:2,malkavian:2",
        "clanWeight4" => ""
    ],
    [
        "category" => "family",
        "question" => "Your sibling just inherited your grandmother house - the one you helped care for, visited weekly, and maintained for years. Your sibling visited twice in the last decade but was always your grandmother \"favorite.\" At the will reading, they do not even look at you. Your parents say, \"She made her choice. Do not make this awkward.\"",
        "answer1" => "You accept it publicly with grace, then quietly work through legal channels to ensure the estate is handled fairly regarding other assets",
        "answer2" => "You walk out immediately, cutting contact with everyone involved until they acknowledge what is wrong with this",
        "answer3" => "You attend the funeral and family events as if nothing happened, studying everyone reactions and relationships, storing it all away",
        "answer4" => "",
        "clanWeight1" => "ventrue:3,tremere:2",
        "clanWeight2" => "brujah:3,gangrel:2",
        "clanWeight3" => "malkavian:3,nosferatu:2,toreador:2",
        "clanWeight4" => ""
    ]
];

// Questions from Questions_2.md (Post-Embrace Questions 11-20)
$questions2 = [
    [
        "category" => "scenario",
        "question" => "You have been a vampire for three weeks. Your sire left you two nights ago with minimal instruction. The Beast gnaws at you - you need to feed. You spot a lone jogger on a dark trail, someone stumbling drunk from a bar, and a homeless person sleeping in an alley. All are vulnerable. The hunger intensifies with each passing moment.",
        "answer1" => "You approach the drunk outside the bar, using conversation to lead them away from witnesses while appearing helpful and concerned",
        "answer2" => "You take the jogger immediately - fast, direct, trusting your newfound speed and strength to overwhelm them before they can scream",
        "answer3" => "You watch all three for nearly an hour, learning their patterns and the area blind spots before making a calculated choice",
        "answer4" => "",
        "clanWeight1" => "ventrue:3,toreador:2",
        "clanWeight2" => "brujah:3,gangrel:2",
        "clanWeight3" => "tremere:3,nosferatu:2,malkavian:2",
        "clanWeight4" => ""
    ],
    [
        "category" => "social",
        "question" => "It is your first night at Elysium. An elder you have never met approaches and asks, \"Your sire never taught you proper etiquette, did they?\" Others nearby have stopped talking to watch. You do not actually know what you did wrong, but the elder clearly expects an answer that will entertain the audience.",
        "answer1" => "You apologize gracefully and ask the elder to enlighten you, turning the moment into an opportunity to learn while appearing humble",
        "answer2" => "You meet their eyes and respond that your sire taught you to value honesty over empty courtesy",
        "answer3" => "You remain perfectly still, observing everyone reactions, saying nothing until you understand the real game being played",
        "answer4" => "",
        "clanWeight1" => "ventrue:3,toreador:2",
        "clanWeight2" => "brujah:3,gangrel:2",
        "clanWeight3" => "malkavian:3,nosferatu:2",
        "clanWeight4" => ""
    ],
    [
        "category" => "scenario",
        "question" => "You are feeding in what you thought was a secluded parking garage when a security guard rounds the corner, flashlight in hand. Your victim is slumped against you, neck bleeding. The guard freezes, reaching for their radio. You have seconds before they call it in.",
        "answer1" => "You drop the victim and immediately begin explaining - car accident, helping someone, please call an ambulance - while positioning yourself to control the situation",
        "answer2" => "You move before they can react, using your supernatural speed to ensure they cannot make that call",
        "answer3" => "You lock eyes with them and speak, your voice carrying an unnatural weight that makes them reconsider everything they think they saw",
        "answer4" => "",
        "clanWeight1" => "ventrue:3,toreador:2",
        "clanWeight2" => "brujah:3,gangrel:2,nosferatu:2",
        "clanWeight3" => "tremere:3,ventrue:2",
        "clanWeight4" => ""
    ],
    [
        "category" => "power",
        "question" => "Another young vampire has been spreading word that you are weak, easy prey, and do not deserve your hunting grounds. Tonight, you find them in your territory, feeding on someone you had been watching. They look up as you approach, blood on their mouth, and smile. \"What are you going to do about it?\"",
        "answer1" => "You make note of exactly what they are doing and who might care, then walk away to report this breach through proper channels",
        "answer2" => "You answer their question physically, immediately and without words",
        "answer3" => "You stand at the edge of shadows, studying them until they grow uncomfortable, then leave without engaging",
        "answer4" => "",
        "clanWeight1" => "tremere:3,nosferatu:2",
        "clanWeight2" => "brujah:3,gangrel:2",
        "clanWeight3" => "malkavian:3,nosferatu:2",
        "clanWeight4" => ""
    ],
    [
        "category" => "scenario",
        "question" => "An elder you barely know sends you an expensive gift - a painting, a rare book, or jewelry worth thousands. There is no note, no explanation. Other kindred notice and start treating you differently, some with envy, others with suspicion. You do not know what this means or what is expected in return.",
        "answer1" => "You research the elder extensively and arrange a formal meeting to thank them while carefully determining what they want",
        "answer2" => "You return the gift with a polite note saying you cannot accept what you have not earned",
        "answer3" => "You keep the gift but change nothing about your behavior, watching to see who reacts and how",
        "answer4" => "",
        "clanWeight1" => "ventrue:3,tremere:2",
        "clanWeight2" => "gangrel:3,brujah:2",
        "clanWeight3" => "malkavian:3,nosferatu:2,toreador:2",
        "clanWeight4" => ""
    ],
    [
        "category" => "power",
        "question" => "You are at a gathering when a kindred you trust pulls you aside and whispers, \"Do you think the Prince is making the right decisions?\" You notice someone else within possible hearing range. The question feels like a trap, but your friend seems genuinely troubled and is waiting for your answer.",
        "answer1" => "You deflect diplomatically, suggesting you discuss this somewhere more private while gauging whether your friend is testing you or truly concerned",
        "answer2" => "You say what you actually think - the Prince decisions are theirs to make and yours to follow or leave the city",
        "answer3" => "You respond with a question that reveals nothing about your own position while learning what they really want to know",
        "answer4" => "",
        "clanWeight1" => "ventrue:3,toreador:2",
        "clanWeight2" => "gangrel:3,brujah:2",
        "clanWeight3" => "tremere:3,nosferatu:2,malkavian:2",
        "clanWeight4" => ""
    ],
    [
        "category" => "scenario",
        "question" => "Your sire promised to introduce you to someone important who could help establish you in kindred society. The meeting was scheduled for tonight. Your sire does not show up, does not send word. You wait for three hours. You later hear they were seen across town at a party, laughing with others.",
        "answer1" => "You arrange the introduction yourself through other connections, demonstrating you do not need your sire help to advance",
        "answer2" => "You find them that night and make clear that abandoning you has consequences, sire or not",
        "answer3" => "You say nothing but begin building your own network separately, becoming less dependent on them with each passing night",
        "answer4" => "",
        "clanWeight1" => "ventrue:3,toreador:2",
        "clanWeight2" => "brujah:3",
        "clanWeight3" => "tremere:3,nosferatu:2,gangrel:2",
        "clanWeight4" => ""
    ],
    [
        "category" => "scenario",
        "question" => "You have not fed in four days. The Beast is screaming. You are at a crowded nightclub and everyone looks like prey. Your hands are shaking. A kindred you know slightly notices your state and offers you their wrist - fresh vitae, right now, but it means owing them. They are watching your reaction closely.",
        "answer1" => "You accept gratefully but immediately begin planning how to repay this debt in a way that benefits you both",
        "answer2" => "You refuse and leave to hunt on your own, regardless of the risk of losing control",
        "answer3" => "You take what is offered without comment, filing away this moment and what it reveals about them",
        "answer4" => "",
        "clanWeight1" => "ventrue:3,toreador:2",
        "clanWeight2" => "brujah:3,gangrel:2",
        "clanWeight3" => "malkavian:3,nosferatu:2",
        "clanWeight4" => ""
    ],
    [
        "category" => "power",
        "question" => "The Prince offers you formal permission to claim one of three territories as your domain. The first is wealthy and comfortable but already has established kindred nearby who might resent you. The second is rough but uncontested. The third is small but positioned between two powerful elders who will take notice of whoever claims it.",
        "answer1" => "You choose the position between the elders, seeing the opportunity to build relationships with both",
        "answer2" => "You take the rough territory where you will not have to navigate anyone else politics",
        "answer3" => "You request time to observe all three areas before deciding, ignoring the subtle pressure to choose immediately",
        "answer4" => "",
        "clanWeight1" => "ventrue:3,toreador:2",
        "clanWeight2" => "gangrel:3,brujah:2",
        "clanWeight3" => "tremere:3,malkavian:2",
        "clanWeight4" => ""
    ],
    [
        "category" => "moral",
        "question" => "Your sire orders you to do something that violates your personal code - destroy someone reputation who helped you, betray a confidence, or harm an innocent. They offer no explanation beyond \"because I command it.\" Refusing could mean severing the only connection you have in kindred society.",
        "answer1" => "You do what is ordered but document everything, ensuring that if this comes back on you, the responsibility is clearly placed",
        "answer2" => "You refuse directly, making clear you will face the consequences rather than compromise yourself",
        "answer3" => "You appear to comply while finding a way to achieve your sire goal through methods that do not violate your principles",
        "answer4" => "",
        "clanWeight1" => "tremere:3,nosferatu:2",
        "clanWeight2" => "brujah:3,gangrel:2",
        "clanWeight3" => "ventrue:3,toreador:2,malkavian:2",
        "clanWeight4" => ""
    ]
];

// Original 19 questions from character_questionnaire.php (Questions 21-39)
$originalQuestions = [
    [
        "category" => "embrace",
        "question" => "How did you transition to vampire?",
        "answer1" => "Voluntary transformation",
        "answer2" => "Ritualistic Embrace",
        "answer3" => "Accidental discovery",
        "answer4" => "Supernatural encounter",
        "clanWeight1" => "ventrue:3,tremere:2",
        "clanWeight2" => "tremere:3,nosferatu:2",
        "clanWeight3" => "brujah:3,gangrel:2",
        "clanWeight4" => "malkavian:3,nosferatu:2"
    ],
    [
        "category" => "personality",
        "question" => "Select your top three personality traits:",
        "answer1" => "Passionate",
        "answer2" => "Calculating",
        "answer3" => "Impulsive",
        "answer4" => "Compassionate",
        "clanWeight1" => "brujah:2,toreador:2",
        "clanWeight2" => "ventrue:2,tremere:2",
        "clanWeight3" => "brujah:2,gangrel:2",
        "clanWeight4" => "toreador:2,malkavian:1"
    ],
    [
        "category" => "personality",
        "question" => "Select your top three personality traits (continued):",
        "answer1" => "Sardonic",
        "answer2" => "Pragmatic",
        "answer3" => "",
        "answer4" => "",
        "clanWeight1" => "malkavian:2,nosferatu:2",
        "clanWeight2" => "ventrue:2,tremere:2",
        "clanWeight3" => "",
        "clanWeight4" => ""
    ],
    [
        "category" => "perspective",
        "question" => "How do you view human society now that you are immortal?",
        "answer1" => "Superior and detached",
        "answer2" => "Curious about change",
        "answer3" => "Determined to protect or control",
        "answer4" => "Conflicted by your newfound perspective",
        "clanWeight1" => "ventrue:3,tremere:2",
        "clanWeight2" => "toreador:3,malkavian:2",
        "clanWeight3" => "ventrue:2,tremere:3",
        "clanWeight4" => "toreador:2,malkavian:3"
    ],
    [
        "category" => "powers",
        "question" => "If supernatural powers could represent your essence, what would they be?",
        "answer1" => "Strength/Combat ability",
        "answer2" => "Social manipulation",
        "answer3" => "Mystical knowledge",
        "answer4" => "Survival skill",
        "clanWeight1" => "brujah:3,gangrel:2",
        "clanWeight2" => "ventrue:3,tremere:2",
        "clanWeight3" => "tremere:3,malkavian:2",
        "clanWeight4" => "gangrel:3,nosferatu:2"
    ],
    [
        "category" => "motivation",
        "question" => "What is your most significant personal goal or motivation?",
        "answer1" => "Survival",
        "answer2" => "Revenge",
        "answer3" => "Knowledge",
        "answer4" => "Redemption",
        "clanWeight1" => "gangrel:3,nosferatu:2",
        "clanWeight2" => "brujah:3,nosferatu:2",
        "clanWeight3" => "tremere:3,malkavian:2",
        "clanWeight4" => "toreador:3,malkavian:1"
    ],
    [
        "category" => "supernatural",
        "question" => "How do you view other supernatural beings in the world?",
        "answer1" => "They are threats to be eliminated",
        "answer2" => "Potential allies or partners",
        "answer3" => "Curious subjects of study",
        "answer4" => "Annoying complications",
        "clanWeight1" => "brujah:3,gangrel:2",
        "clanWeight2" => "tremere:3,toreador:2",
        "clanWeight3" => "tremere:2,malkavian:3",
        "clanWeight4" => "nosferatu:3,gangrel:2"
    ],
    [
        "category" => "secrets",
        "question" => "Do you have a secret that even close companions do not know about?",
        "answer1" => "A hidden talent",
        "answer2" => "A past trauma",
        "answer3" => "A forbidden desire",
        "answer4" => "A supernatural weakness",
        "clanWeight1" => "toreador:3,tremere:2",
        "clanWeight2" => "nosferatu:3,malkavian:2",
        "clanWeight3" => "malkavian:3,toreador:2",
        "clanWeight4" => "nosferatu:2,gangrel:3"
    ],
    [
        "category" => "fears",
        "question" => "What is your greatest fear as an immortal being?",
        "answer1" => "Solitude",
        "answer2" => "Loss of humanity",
        "answer3" => "Exposure of your true nature",
        "answer4" => "Becoming too powerful",
        "clanWeight1" => "nosferatu:3,malkavian:2",
        "clanWeight2" => "toreador:3,tremere:2",
        "clanWeight3" => "nosferatu:2,tremere:3",
        "clanWeight4" => "gangrel:3,brujah:2"
    ],
    [
        "category" => "scenario",
        "question" => "You have just been Embraced by a powerful Ventrue elder in your hometown. What happens when your first attempt to influence local politics goes wrong?",
        "answer1" => "You are publicly humiliated, forced underground and learn the harsh lessons of supernatural power",
        "answer2" => "You realize your true potential as a social manipulator",
        "answer3" => "You discover unexpected allies within the political landscape",
        "answer4" => "",
        "clanWeight1" => "nosferatu:3,brujah:2",
        "clanWeight2" => "ventrue:3,tremere:2",
        "clanWeight3" => "tremere:2,toreador:3",
        "clanWeight4" => ""
    ],
    [
        "category" => "scenario",
        "question" => "Your first supernatural encounter reveals something terrifying about your new immortal nature. How do you process this revelation?",
        "answer1" => "You become withdrawn, seeking isolation and understanding",
        "answer2" => "You decide to embrace the mystery and explore your newfound abilities",
        "answer3" => "You seek out other vampires who might explain or validate your experience",
        "answer4" => "",
        "clanWeight1" => "nosferatu:3,gangrel:2",
        "clanWeight2" => "malkavian:3,tremere:2",
        "clanWeight3" => "toreador:3,ventrue:2",
        "clanWeight4" => ""
    ],
    [
        "category" => "scenario",
        "question" => "During your first hunt in the modern world, you realize that surviving as a vampire requires more than just supernatural strength. How do you respond?",
        "answer1" => "You struggle with the ethical implications of taking human life",
        "answer2" => "You become ruthless and efficient",
        "answer3" => "You seek balance between hunger and restraint",
        "answer4" => "",
        "clanWeight1" => "toreador:3,tremere:2",
        "clanWeight2" => "brujah:3,gangrel:2",
        "clanWeight3" => "ventrue:3,tremere:2",
        "clanWeight4" => ""
    ],
    [
        "category" => "scenario",
        "question" => "Your clan elder reveals an ancient secret about your specific bloodline. What is your reaction?",
        "answer1" => "You feel deeply connected to a hidden legacy",
        "answer2" => "You view it as another burden of supernatural existence",
        "answer3" => "You become protective of the knowledge, sensing its potential power",
        "answer4" => "",
        "clanWeight1" => "tremere:3,ventrue:2",
        "clanWeight2" => "nosferatu:3,malkavian:2",
        "clanWeight3" => "tremere:2,ventrue:3",
        "clanWeight4" => ""
    ],
    [
        "category" => "scenario",
        "question" => "When confronted with a supernatural threat that threatens not just humans but other vampires, how do you respond?",
        "answer1" => "You seek diplomatic resolution and compromise",
        "answer2" => "You charge directly into combat",
        "answer3" => "You analyze strategically before taking action",
        "answer4" => "",
        "clanWeight1" => "ventrue:3,tremere:2",
        "clanWeight2" => "brujah:3,gangrel:2",
        "clanWeight3" => "tremere:3,ventrue:2",
        "clanWeight4" => ""
    ],
    [
        "category" => "workplace",
        "question" => "You have been working on a major project for six months when your colleague, who you considered a friend, presents your ideas as their own to the board of directors. They get the promotion you were promised, and when you confront them privately, they shrug and say, \"That is just how business works. You should have been smarter about protecting your ideas.\" Your boss is impressed with their \"innovation\" and has no idea it was yours.",
        "answer1" => "You quietly begin documenting everything and building relationships with higher-ups, planning to expose the theft when it will cause maximum damage to their reputation",
        "answer2" => "You confront them publicly at the next meeting, demanding recognition for your work and making it clear that such betrayal will not be tolerated",
        "answer3" => "You accept the loss but begin studying their methods, learning how to play the political game so this never happens to you again",
        "answer4" => "",
        "clanWeight1" => "ventrue:3,tremere:2",
        "clanWeight2" => "brujah:3,gangrel:2",
        "clanWeight3" => "tremere:2,ventrue:3",
        "clanWeight4" => ""
    ],
    [
        "category" => "family",
        "question" => "Your sibling has been struggling with addiction for years, and tonight they have stolen money from your parents savings to buy drugs. Your parents are devastated and do not know what to do. Your sibling is passed out on the couch, and you are the only one who can see the full scope of the problem. Your parents are asking you what they should do.",
        "answer1" => "You take charge of the situation, researching treatment options and creating a structured plan to help your sibling while supporting your parents through the process",
        "answer2" => "You tell your parents the hard truth - that your sibling needs to face consequences for their actions and that enabling them will only make things worse",
        "answer3" => "You focus on protecting your parents emotional well-being, handling the immediate crisis while keeping them from the worst details of your siblings situation",
        "answer4" => "",
        "clanWeight1" => "tremere:3,ventrue:2",
        "clanWeight2" => "brujah:3,gangrel:2",
        "clanWeight3" => "toreador:3,malkavian:2",
        "clanWeight4" => ""
    ],
    [
        "category" => "social",
        "question" => "You are at a party when you witness a popular person in your social circle bullying someone who is clearly uncomfortable but too shy to stand up for themselves. The bully is making cruel jokes at their expense, and everyone is laughing along to avoid being the next target. The victim looks to you with pleading eyes, but speaking up could cost you your social standing.",
        "answer1" => "You subtly redirect the conversation to a different topic, defusing the situation without directly confronting the bully or drawing attention to yourself",
        "answer2" => "You call out the bully directly, making it clear that such behavior is unacceptable and that you will not stand by while someone is being mistreated",
        "answer3" => "You wait until later to approach the victim privately, offering support and advice on how to handle similar situations in the future",
        "answer4" => "",
        "clanWeight1" => "tremere:3,ventrue:2",
        "clanWeight2" => "brujah:3,gangrel:2",
        "clanWeight3" => "toreador:3,malkavian:2",
        "clanWeight4" => ""
    ],
    [
        "category" => "moral",
        "question" => "You discover that your best friend has been cheating on their partner, who is also your friend. The cheater confides in you, asking you to keep their secret and help them cover their tracks. Meanwhile, their partner is planning to propose next month and has asked you to help plan the engagement. You are caught between loyalty to both friends.",
        "answer1" => "You refuse to help with the deception and give the cheater an ultimatum - they either come clean or you will tell their partner yourself",
        "answer2" => "You agree to keep the secret but begin distancing yourself from the situation, avoiding involvement in either the deception or the engagement planning",
        "answer3" => "You use your knowledge strategically, positioning yourself to help both friends while ensuring that when the truth comes out, you are seen as someone who tried to prevent disaster",
        "answer4" => "",
        "clanWeight1" => "toreador:3,tremere:2",
        "clanWeight2" => "nosferatu:3,malkavian:2",
        "clanWeight3" => "ventrue:3,tremere:2",
        "clanWeight4" => ""
    ],
    [
        "category" => "power",
        "question" => "You are offered a promotion that would put you in a position of authority over people you have worked alongside for years. The promotion comes with a significant salary increase and more responsibility, but it also means you will have to make difficult decisions that could affect your former peers livelihoods. Some of them are counting on you to look out for them.",
        "answer1" => "You accept the promotion and immediately begin building relationships with your new peers, learning how to balance the needs of your former colleagues with the demands of your new role",
        "answer2" => "You accept the promotion but make it clear to everyone that your loyalty to your friends will not be compromised by your new responsibilities",
        "answer3" => "You take the promotion and focus on excelling in your new role, knowing that the best way to help your former colleagues is to be successful enough to advocate for them from a position of strength",
        "answer4" => "",
        "clanWeight1" => "toreador:3,tremere:2",
        "clanWeight2" => "brujah:3,gangrel:2",
        "clanWeight3" => "ventrue:3,tremere:2",
        "clanWeight4" => ""
    ],
    [
        "category" => "life",
        "question" => "You are at a crossroads in your life. You have been offered your dream job in another city, but it would mean leaving behind your aging parents, who need help with daily tasks, and your younger sibling, who is struggling with mental health issues. Your family is depending on you, but this opportunity may never come again. You have to decide tonight.",
        "answer1" => "You take the job but create a detailed plan to support your family from afar, including arranging for professional care and regular visits home",
        "answer2" => "You turn down the job, choosing to stay and care for your family, knowing that opportunities will come again but family needs are immediate",
        "answer3" => "You negotiate with the company for a delayed start date, giving you time to set up support systems for your family while still pursuing your dreams",
        "answer4" => "",
        "clanWeight1" => "tremere:3,ventrue:2",
        "clanWeight2" => "toreador:3,malkavian:2",
        "clanWeight3" => "ventrue:3,tremere:2",
        "clanWeight4" => ""
    ]
];

// Combine all questions
$allQuestions = array_merge($questions3, $questions2, $originalQuestions);

// Insert questions into database
$inserted = 0;
$errors = 0;

foreach ($allQuestions as $question) {
    $sql = "INSERT INTO questionnaire_questions (category, question, answer1, answer2, answer3, answer4, clanWeight1, clanWeight2, clanWeight3, clanWeight4) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = mysqli_prepare($conn, $sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ssssssssss", 
            $question["category"],
            $question["question"], 
            $question["answer1"],
            $question["answer2"],
            $question["answer3"],
            $question["answer4"],
            $question["clanWeight1"],
            $question["clanWeight2"],
            $question["clanWeight3"],
            $question["clanWeight4"]
        );
        
        if (mysqli_stmt_execute($stmt)) {
            $inserted++;
        } else {
            $errors++;
            echo "Error inserting question: " . mysqli_error($conn) . "<br>";
        }
        mysqli_stmt_close($stmt);
    } else {
        $errors++;
        echo "Error preparing statement: " . mysqli_error($conn) . "<br>";
    }
}

echo "<h2>Complete 39-Question Population!</h2>";
echo "<p>Successfully inserted: $inserted questions</p>";
echo "<p>Errors: $errors</p>";

// Show breakdown by category
$result = mysqli_query($conn, "SELECT category, COUNT(*) as count FROM questionnaire_questions GROUP BY category ORDER BY category");
if ($result) {
    echo "<h3>Questions by Category:</h3>";
    echo "<table border=1><tr><th>Category</th><th>Count</th><th>Description</th></tr>";
    
    $descriptions = [
        "embrace" => "The Embrace & Transformation (Question 21)",
        "personality" => "Core Personality Traits (Questions 22-23)",
        "perspective" => "Worldview & Philosophy (Question 24)",
        "powers" => "Supernatural Abilities (Question 25)",
        "motivation" => "Personal Goals & Drives (Question 26)",
        "supernatural" => "Other Supernatural Beings (Question 27)",
        "secrets" => "Hidden Truths & Secrets (Question 28)",
        "fears" => "Deepest Fears & Dreads (Question 29)",
        "scenario" => "Hypothetical Scenarios (Questions 1, 3, 5, 7, 9, 11, 13, 15, 17, 19, 30-34)",
        "workplace" => "Professional Situations (Questions 5, 8, 35)",
        "family" => "Family & Relationships (Questions 3, 10, 36)",
        "social" => "Social Interactions (Questions 1, 4, 6, 7, 9, 12, 37)",
        "moral" => "Moral Dilemmas (Questions 2, 20, 38)",
        "power" => "Power & Authority (Questions 14, 16, 18, 39)",
        "life" => "Life-Changing Decisions (Question 40)"
    ];
    
    while ($row = mysqli_fetch_assoc($result)) {
        $desc = isset($descriptions[$row["category"]]) ? $descriptions[$row["category"]] : "Unknown";
        echo "<tr><td>" . ucfirst(str_replace("_", " ", $row["category"])) . "</td><td>" . $row["count"] . "</td><td>" . $desc . "</td></tr>";
    }
    echo "</table>";
}

mysqli_close($conn);
?>
