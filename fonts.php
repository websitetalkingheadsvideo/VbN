<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gothic Font Preview - LOTN Character Creator</title>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600&family=Crimson+Text:ital,wght@0,400;0,600;1,400&family=Playfair+Display:ital,wght@0,400;0,600;1,400&family=Libre+Baskerville:ital,wght@0,400;0,700;1,400&family=Source+Serif+Pro:ital,wght@0,400;0,600;1,400&family=Merriweather:ital,wght@0,300;0,400;0,700;1,300;1,400&family=IM+Fell+English:ital@0;1&family=MedievalSharp&family=Nosifer&family=Creepster&family=Butcherman&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Source Serif Pro', serif;
            background: linear-gradient(135deg, #000713, #2E5740, #17212D, #1D2523);
            color: #e0e0e0;
            padding: 15px;
            display: flex;
            gap: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        h1 {
            text-align: center;
            color: #780606;
            margin-bottom: 40px;
            font-size: 2.5em;
            font-family: 'Libre Baskerville', serif;
        }
        
        .font-section {
            background: #0a0a0a;
            border: 2px solid #780606;
            border-radius: 8px;
            margin-bottom: 30px;
            padding: 25px;
        }
        
        .font-name {
            color: #780606;
            font-size: 1.8em;
            font-weight: bold;
            margin-bottom: 15px;
            border-bottom: 1px solid #780606;
            padding-bottom: 10px;
            font-family: 'Libre Baskerville', serif;
        }
        
        .font-preview {
            font-size: 1.1em;
            line-height: 1.7;
            margin-bottom: 15px;
        }
        
        .font-info {
            color: #888;
            font-size: 0.9em;
            font-style: italic;
            font-weight: 600;
        }
        
        .clan-title {
            color: #780606;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        /* Font-specific styles */
        .cinzel { font-family: 'Cinzel', serif; }
        .crimson-text { font-family: 'Crimson Text', serif; }
        .playfair { font-family: 'Playfair Display', serif; }
        .libre-baskerville { font-family: 'Libre Baskerville', serif; }
        .source-serif { font-family: 'Source Serif Pro', serif; }
        .merriweather { font-family: 'Merriweather', serif; }
        .im-fell { font-family: 'IM Fell English', serif; }
        .medieval { font-family: 'MedievalSharp', cursive; }
        .nosifer { font-family: 'Nosifer', fantasy; }
        .creepster { font-family: 'Creepster', fantasy; }
        .butcherman { font-family: 'Butcherman', fantasy; }
        
        .back-link {
            text-align: center;
            margin-top: 40px;
        }
        
        .back-link a {
            color: #780606;
            text-decoration: none;
            font-size: 1.2em;
            padding: 10px 20px;
            border: 2px solid #780606;
            border-radius: 5px;
            transition: all 0.3s ease;
        }
        
        .back-link a:hover {
            background: #780606;
            color: #1a1a1a;
        }
        
        .brand {
            font-family: 'IM Fell English', serif;
            color: #5a0202;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 class="brand">⚜ Laws of the Night: Character Creation ⚜</h1>
            <div class="header-right">
                <div class="user-info">
                    <span class="user-label">Font Preview</span>
                    <span class="user-name">Design System</span>
                </div>
                <div class="version-info">
                    <span class="version">v0.4.0</span>
                </div>
            </div>
            <div class="xp-tracker">
                <div class="label">Font System</div>
                <div class="xp-display">3 Fonts</div>
                <div class="xp-label">Typography</div>
            </div>
        </div>
        
        <div class="font-section">
            <div class="font-name libre-baskerville">Libre Baskerville - Headers</div>
            <div class="font-preview libre-baskerville">
                <div class="clan-title">Ventrue - The Blue Bloods</div>
                The Ventrue are the self-proclaimed kings and queens of the night. They believe themselves to be the natural rulers of Kindred society, and they have the power and influence to back up their claims. They are the politicians, the CEOs, the leaders who pull the strings from behind the scenes. Their disciplines of Dominate, Fortitude, and Presence make them formidable opponents both in combat and in the political arena.
            </div>
            <div class="font-info">Used for: Headers, titles, and section names. Classic and elegant serif font.</div>
        </div>
        
        <div class="font-section">
            <div class="font-name source-serif">Source Serif Pro - Body Text</div>
            <div class="font-preview source-serif">
                <div class="clan-title">Toreador - The Artists</div>
                The Toreador are the artists, the musicians, the poets of the Kindred world. They are driven by their passions and their appreciation for beauty in all its forms. They can be both the most inspiring and the most dangerous of vampires, as their emotions run deep and their reactions can be unpredictable. Their disciplines of Auspex, Celerity, and Presence make them both perceptive and charismatic.
            </div>
            <div class="font-info">Used for: Body text, descriptions, and general content. Clean and professional serif font.</div>
        </div>
        
        <div class="font-section">
            <div class="font-name im-fell">IM Fell English - Branding</div>
            <div class="font-preview im-fell">
                <div class="clan-title">Brujah - The Rebels</div>
                The Brujah are the rebels, the revolutionaries, the ones who refuse to accept the status quo. They are passionate and impulsive, driven by their ideals and their emotions. They are the warriors and the philosophers, the ones who fight for what they believe in. Their disciplines of Celerity, Potence, and Presence make them both incredibly fast and incredibly strong.
            </div>
            <div class="font-info">Used for: Branding elements, game titles, and special headers. Authentic Old English style.</div>
        </div>
        
        <div class="font-section">
            <div class="font-name nosifer">Nosifer - Warnings & Special Effects</div>
            <div class="font-preview nosifer">
                <div class="clan-title">⚠️ WARNING MESSAGES:</div>
                <div style="color: #ff0000; margin: 10px 0;">DANGER! Character not saved!</div>
                <div style="color: #ff0000; margin: 10px 0;">BEWARE! Invalid selection!</div>
                <div style="color: #ff0000; margin: 10px 0;">ERROR! Cannot proceed!</div>
                
                <div class="clan-title" style="margin-top: 20px;">🩸 BLOOD & XP WARNINGS:</div>
                <div style="color: #ff0000; margin: 10px 0;">Blood points depleted!</div>
                <div style="color: #ff0000; margin: 10px 0;">XP cost exceeds available points!</div>
                <div style="color: #ff0000; margin: 10px 0;">Herd requires feeding!</div>
                
                <div class="clan-title" style="margin-top: 20px;">⚡ SPECIAL EFFECTS:</div>
                <div style="color: #ff0000; margin: 10px 0;">FINALIZE CHARACTER?</div>
                <div style="color: #ff0000; margin: 10px 0;">DELETE CHARACTER?</div>
                <div style="color: #ff0000; margin: 10px 0;">DISCIPLINE SIDE EFFECTS!</div>
            </div>
            <div class="font-info">Used for: Warnings, errors, and special effects. Creepy blood-dripping style with #ff0000 color.</div>
        </div>
        
        <div class="back-link">
            <a href="lotn_char_create.php">← Back to Character Creator</a>
        </div>
    </div>
</body>
</html>
