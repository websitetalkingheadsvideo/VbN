-- Complete SQL Script: Populate Coterie and Relationships Data
-- Assumes Coterie and Relationships columns already exist on characters table
-- Run this in your database management tool (phpMyAdmin, etc.)

-- This script updates the Coterie and Relationships JSON columns for each character
-- Character names are matched to character_id in your database

-- Bayside Bob
UPDATE characters 
SET Coterie = '[{"name":"Camelback Road","type":"faction","role":null,"description":"staurant on Camelback Road, serves as a front for Anarch meetings and is the de facto gathering place for","source":"biography"},{"name":"Polynesian","type":"role","role":null,"description":"ki-style Polynesian restaurant on Camelback Road, serves as a front for Anarch meetings and is the de facto g","source":"biography"},{"name":"Phoenix","type":"membership","role":null,"description":"b arrived in Phoenix early in 1981 and has been a part of the city''s undead scene ever since. By 1994, he h","source":"biography"},{"name":"Anarch","type":"informal_group","role":null,"description":"serves as a front for Anarch meetings and is the de facto gathering place for Anarchs in Phoenix. Despite t","source":"biography"}]',
    Relationships = '[{"character_name":"His","character_id":null,"type":"mentor","subtype":null,"strength":null,"description":"His mentor is an older Toreador who introduced him to the arts and social scene.","source":"backgroundDetails.Mentor"}]'
WHERE character_name = 'Bayside Bob';

-- Betty
UPDATE characters 
SET Coterie = NULL,
    Relationships = '[{"character_name":"Alistaire","character_id":null,"type":"sire","subtype":null,"strength":null,"description":"Alistaire","source":"sire"},{"character_name":"Alistaire","character_id":null,"type":"mentor","subtype":null,"strength":null,"description":"Alistaire (sire) - Provides guidance","source":"backgroundDetails.Mentor"},{"character_name":"Nosferatu","character_id":null,"type":"ally","subtype":null,"strength":null,"description":"Nosferatu #3 and #4 (her tech team)","source":"backgroundDetails.Allies"},{"character_name":"Terry","character_id":null,"type":"ally","subtype":null,"strength":null,"description":"Terry (fellow childe of Alistaire)","source":"backgroundDetails.Allies"},{"character_name":"Tech","character_id":null,"type":"contact","subtype":null,"strength":null,"description":"Tech industry contacts from mortal life","source":"backgroundDetails.Contacts"},{"character_name":"underground hacker networks","character_id":null,"type":"contact","subtype":null,"strength":null,"description":"underground hacker networks","source":"backgroundDetails.Contacts"},{"character_name":"BBS operators","character_id":null,"type":"contact","subtype":null,"strength":null,"description":"BBS operators","source":"backgroundDetails.Contacts"},{"character_name":"computer science academics.","character_id":null,"type":"contact","subtype":null,"strength":null,"description":"computer science academics.","source":"backgroundDetails.Contacts"}]'
WHERE character_name = 'Betty';

-- Cordelia Fairchild
UPDATE characters 
SET Coterie = '[{"name":"Harpy","type":"role","role":null,"description":"nevitable: by the 1950s she was recognized as the Harpy of the domain, her wit and cruelty wrapped in hon","source":"biography"}]',
    Relationships = '[{"character_name":"Unknown","character_id":null,"type":"sire","subtype":null,"strength":null,"description":"Unknown — rumored to have been an art patron in 1910s San Francisco","source":"sire"},{"character_name":"Private","character_id":null,"type":"contact","subtype":null,"strength":null,"description":"Private investigator network","source":"backgroundDetails.Contacts"},{"character_name":"gossip columnists","character_id":null,"type":"contact","subtype":null,"strength":null,"description":"gossip columnists","source":"backgroundDetails.Contacts"},{"character_name":"social secretaries","character_id":null,"type":"contact","subtype":null,"strength":null,"description":"social secretaries","source":"backgroundDetails.Contacts"},{"character_name":"and art dealers.","character_id":null,"type":"contact","subtype":null,"strength":null,"description":"and art dealers.","source":"backgroundDetails.Contacts"}]'
WHERE character_name = 'Cordelia Fairchild';

-- Duke Tiki
UPDATE characters 
SET Coterie = NULL,
    Relationships = '[{"character_name":"He","character_id":null,"type":"mentor","subtype":null,"strength":null,"description":"He is a mentor to Bob and occasionally to other Kindred artists","source":"backgroundDetails.Mentor"}]'
WHERE character_name = 'Duke Tiki';

-- Jax 'The Ghost Dealer'
UPDATE characters 
SET Coterie = NULL,
    Relationships = '[{"character_name":"None","character_id":null,"type":"ally","subtype":null,"strength":null,"description":"None; mostly works alone.","source":"backgroundDetails.Allies"},{"character_name":"Street","character_id":null,"type":"contact","subtype":null,"strength":null,"description":"Street psychics","source":"backgroundDetails.Contacts"},{"character_name":"pawnshop mystics","character_id":null,"type":"contact","subtype":null,"strength":null,"description":"pawnshop mystics","source":"backgroundDetails.Contacts"},{"character_name":"and superstitious mortals provide intel or customers.","character_id":null,"type":"contact","subtype":null,"strength":null,"description":"and superstitious mortals provide intel or customers.","source":"backgroundDetails.Contacts"}]'
WHERE character_name LIKE 'Jax%';

-- Leo (skip if no data - leave as NULL)
-- UPDATE characters 
-- SET Coterie = NULL,
--     Relationships = NULL
-- WHERE character_name = 'Leo';

-- Lucien Marchand
UPDATE characters 
SET Coterie = NULL,
    Relationships = '[{"character_name":"Ghouled","character_id":null,"type":"sire","subtype":null,"strength":null,"description":"N/A - Ghouled by Étienne Duvalier in 1927","source":"sire"}]'
WHERE character_name = 'Lucien Marchand';

-- Pistol Pete (skip if no data - leave as NULL)
-- UPDATE characters 
-- SET Coterie = NULL,
--     Relationships = NULL
-- WHERE character_name = 'Pistol Pete';

-- Piston
UPDATE characters 
SET Coterie = '[{"name":"Butch Reed (burglary specialist) and Basher (street enforcer), both former Hell''s Angels","type":"coterie","role":null,"description":null,"source":"research_notes.coterie"},{"name":"Gangrel","type":"faction","role":null,"description":"ted diablerie on a Gangrel during the Los Angeles Anarch faction wars (late 1980s/early 1990s). A former H","source":"biography"}]',
    Relationships = '[{"character_name":"Fred Osmond","character_id":null,"type":"sire","subtype":null,"strength":null,"description":"Fred Osmond","source":"sire"},{"character_name":"Butch Reed","character_id":null,"type":"ally","subtype":null,"strength":null,"description":"Butch Reed and Basher","source":"backgroundDetails.Allies"},{"character_name":"Phoenix Anarch","character_id":null,"type":"ally","subtype":null,"strength":null,"description":"plus some Phoenix Anarch connections","source":"backgroundDetails.Allies"},{"character_name":"Street","character_id":null,"type":"contact","subtype":null,"strength":null,"description":"Street contacts from Hell''s Angels days and current criminal operations","source":"backgroundDetails.Contacts"}]'
WHERE character_name = 'Piston';

-- Rembrandt Jones
UPDATE characters 
SET Coterie = '[{"name":"Marginal","type":"role","role":"Primogen","description":"Marginal within Toreador circles. Comic relief to most. Some pretentious theorists write papers about him. Trying desperately to impress the Toreador Primogen.","source":"backgroundDetails"}]',
    Relationships = '[{"character_name":"Las Vegas Toreador","character_id":null,"type":"sire","subtype":null,"strength":null,"description":"Las Vegas Toreador (gaudy casino strip artist)","source":"sire"},{"character_name":"Deep","character_id":null,"type":"contact","subtype":null,"strength":null,"description":"Deep connections in Roadside Americana/kitschy nostalgia subculture: Muffler Men collectors","source":"backgroundDetails.Contacts"},{"character_name":"vintage motel sign preservationists","character_id":null,"type":"contact","subtype":null,"strength":null,"description":"vintage motel sign preservationists","source":"backgroundDetails.Contacts"},{"character_name":"Route","character_id":null,"type":"contact","subtype":null,"strength":null,"description":"Route 66 nostalgists","source":"backgroundDetails.Contacts"},{"character_name":"Googie","character_id":null,"type":"contact","subtype":null,"strength":null,"description":"Googie architecture enthusiasts","source":"backgroundDetails.Contacts"},{"character_name":"Vegas","character_id":null,"type":"contact","subtype":null,"strength":null,"description":"vintage Vegas memorabilia dealers","source":"backgroundDetails.Contacts"},{"character_name":"neon sign restoration communities","character_id":null,"type":"contact","subtype":null,"strength":null,"description":"neon sign restoration communities","source":"backgroundDetails.Contacts"},{"character_name":"He","character_id":null,"type":"contact","subtype":null,"strength":null,"description":"photographers of abandoned roadside attractions. He doesn''t fully realize this is where he''s successful.","source":"backgroundDetails.Contacts"}]'
WHERE character_name = 'Rembrandt Jones';

-- Sabine
UPDATE characters 
SET Coterie = '[{"name":"Talon","type":"role","role":"Talon","description":"Talon to the Harpy Cordelia Prescott, childer of Toreador Primogen","source":"backgroundDetails"}]',
    Relationships = '[{"character_name":"Toreador Primogen","character_id":null,"type":"sire","subtype":null,"strength":null,"description":"Toreador Primogen (Phoenix)","source":"sire"},{"character_name":"Cordelia Prescott","character_id":null,"type":"mentor","subtype":null,"strength":null,"description":"Being trained by Cordelia Prescott in Kindred social politics, also guided by Toreador Primogen","source":"backgroundDetails.Mentor"},{"character_name":"Sebastian","character_id":null,"type":"twin","subtype":"brother","strength":"inseparable","description":"Twin brother Sebastian (inseparable)","source":"backgroundDetails.Allies"},{"character_name":"Sebastian","character_id":null,"type":"special_rapport","subtype":null,"strength":"special","description":"Supernatural bond with twin brother Sebastian. Can sense his emotions even at distance, feel his physical pain or peril, know when he''s in trouble or lying. He knows the same about her. They are inseparable.","source":"merits_flaws"},{"character_name":"High","character_id":null,"type":"contact","subtype":null,"strength":null,"description":"High society connections from Ontario and Phoenix, art gallery world, modeling industry","source":"backgroundDetails.Contacts"}]'
WHERE character_name = 'Sabine';

-- Sasha (skip if no data - leave as NULL)
-- UPDATE characters 
-- SET Coterie = NULL,
--     Relationships = NULL
-- WHERE character_name = 'Sasha';

-- Sebastian
UPDATE characters 
SET Coterie = '[{"name":"Talon","type":"role","role":"Talon","description":"Talon to the Harpy Cordelia Prescott, childer of Toreador Primogen","source":"backgroundDetails"}]',
    Relationships = '[{"character_name":"Toreador Primogen","character_id":null,"type":"sire","subtype":null,"strength":null,"description":"Toreador Primogen (Phoenix)","source":"sire"},{"character_name":"Cordelia Prescott","character_id":null,"type":"mentor","subtype":null,"strength":null,"description":"Being trained by Cordelia Prescott in Kindred social politics, also guided by Toreador Primogen","source":"backgroundDetails.Mentor"},{"character_name":"Sabine","character_id":null,"type":"twin","subtype":"sister","strength":"inseparable","description":"Twin sister Sabine (inseparable)","source":"backgroundDetails.Allies"},{"character_name":"Sabine","character_id":null,"type":"special_rapport","subtype":null,"strength":"special","description":"Supernatural bond with twin sister Sabine. Can sense her emotions even at distance, feel her physical pain or peril, know when she''s in trouble or lying. She knows the same about him. They are inseparable.","source":"merits_flaws"},{"character_name":"High","character_id":null,"type":"contact","subtype":null,"strength":null,"description":"High society connections from Ontario and Phoenix, art gallery world, modeling industry","source":"backgroundDetails.Contacts"}]'
WHERE character_name = 'Sebastian';

-- Sofia Alvarez
UPDATE characters 
SET Coterie = NULL,
    Relationships = '[{"character_name":"Étienne Duvalier","character_id":null,"type":"sire","subtype":null,"strength":null,"description":"N/A - Ghouled by Étienne Duvalier in 2006","source":"sire"},{"character_name":"Art","character_id":null,"type":"contact","subtype":null,"strength":null,"description":"Art world connections","source":"backgroundDetails.Contacts"},{"character_name":"gallery staff","character_id":null,"type":"contact","subtype":null,"strength":null,"description":"gallery staff","source":"backgroundDetails.Contacts"},{"character_name":"architectural firms","character_id":null,"type":"contact","subtype":null,"strength":null,"description":"architectural firms","source":"backgroundDetails.Contacts"},{"character_name":"event planners","character_id":null,"type":"contact","subtype":null,"strength":null,"description":"event planners","source":"backgroundDetails.Contacts"}]'
WHERE character_name = 'Sofia Alvarez';

-- Tremere (skip if no data - leave as NULL)
-- UPDATE characters 
-- SET Coterie = NULL,
--     Relationships = NULL
-- WHERE character_name = 'Tremere';

-- Violet 'The Confidence Queen'
UPDATE characters 
SET Coterie = NULL,
    Relationships = '[{"character_name":"Buster","character_id":null,"type":"ally","subtype":null,"strength":null,"description":"A Nosferatu ''nephew'' named Buster handles her errands and protection during daylight.","source":"backgroundDetails.Allies"},{"character_name":"A crooked beat cop","character_id":null,"type":"contact","subtype":null,"strength":null,"description":"A crooked beat cop, a paramedic, and a pawnshop owner feed her street intel.","source":"backgroundDetails.Contacts"}]'
WHERE character_name LIKE 'Violet%';

-- Étienne Duvalier
UPDATE characters 
SET Coterie = '[{"name":"Toreador Primogen","type":"role","role":"Primogen","description":"Toreador Primogen of Phoenix, elder of elegance and taste, his approval is sought by all Toreador neonates. To disappoint him is social death","source":"backgroundDetails"}]',
    Relationships = '[{"character_name":"Prestigious Parisian Toreador","character_id":null,"type":"sire","subtype":null,"strength":null,"description":"Prestigious Parisian Toreador elder","source":"sire"},{"character_name":"Network","character_id":null,"type":"ally","subtype":null,"strength":null,"description":"Network of artists, curators, and high society figures across multiple cities who owe him favors or genuinely admire his patronage","source":"backgroundDetails.Allies"},{"character_name":"Museum","character_id":null,"type":"contact","subtype":null,"strength":null,"description":"Museum directors, art critics, private collectors, cultural institutions, wealthy patrons","source":"backgroundDetails.Contacts"},{"character_name":"Prince Solomon Reaves","character_id":null,"type":"rival","subtype":null,"strength":"special","description":"A subtle, simmering opposition to the Prince. Barely concealed contempt wrapped in perfect civility.","source":"merits_flaws"}]'
WHERE character_name LIKE 'Étienne%' OR character_name LIKE '%Duvalier%';

-- Verify updates (only check characters with actual JSON data)
SELECT character_name, 
       CASE 
         WHEN Coterie IS NULL OR Coterie = '' OR Coterie = '[]' THEN 0
         WHEN JSON_VALID(Coterie) THEN JSON_LENGTH(Coterie)
         ELSE 0
       END AS coterie_count,
       CASE 
         WHEN Relationships IS NULL OR Relationships = '' OR Relationships = '[]' THEN 0
         WHEN JSON_VALID(Relationships) THEN JSON_LENGTH(Relationships)
         ELSE 0
       END AS relationship_count
FROM characters
WHERE (Coterie IS NOT NULL AND Coterie != '' AND JSON_VALID(Coterie)) 
   OR (Relationships IS NOT NULL AND Relationships != '' AND JSON_VALID(Relationships))
ORDER BY character_name;

