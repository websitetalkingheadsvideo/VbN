<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Untitled Document</title>
</head>

<body>
	<?php 
error_reporting(2);
include 'includes/connect.php';

// Create users table
$create_table = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    role VARCHAR(20) NOT NULL DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL
)";

if (mysqli_query($conn, $create_table)) {
    echo "Users table created successfully<br>";
} else {
    echo "Error creating table: " . mysqli_error($conn) . "<br>";
}

// Create characters table
$create_characters = "CREATE TABLE IF NOT EXISTS characters (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    character_name VARCHAR(100) NOT NULL,
    player_name VARCHAR(100) NOT NULL,
    chronicle VARCHAR(100) DEFAULT 'Valley by Night',
    nature VARCHAR(50) NOT NULL,
    demeanor VARCHAR(50) NOT NULL,
    concept VARCHAR(200) NOT NULL,
    clan VARCHAR(50) NOT NULL,
    generation INT NOT NULL,
    sire VARCHAR(100),
    pc BOOLEAN DEFAULT TRUE,
    biography TEXT,
    equipment TEXT,
    total_xp INT DEFAULT 30,
    spent_xp INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)";

if (mysqli_query($conn, $create_characters)) {
    echo "Characters table created successfully<br>";
} else {
    echo "Error creating characters table: " . mysqli_error($conn) . "<br>";
}

// Create character_traits table
$create_character_traits = "CREATE TABLE IF NOT EXISTS character_traits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    character_id INT NOT NULL,
    trait_name VARCHAR(100) NOT NULL,
    trait_category ENUM('Physical', 'Social', 'Mental') NOT NULL,
    trait_type ENUM('positive', 'negative') DEFAULT 'positive',
    xp_cost INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (character_id) REFERENCES characters(id) ON DELETE CASCADE
)";

if (mysqli_query($conn, $create_character_traits)) {
    echo "Character traits table created successfully<br>";
} else {
    echo "Error creating character_traits table: " . mysqli_error($conn) . "<br>";
}

// Create character_abilities table
$create_character_abilities = "CREATE TABLE IF NOT EXISTS character_abilities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    character_id INT NOT NULL,
    ability_name VARCHAR(100) NOT NULL,
    specialization VARCHAR(100),
    level INT DEFAULT 1,
    xp_cost INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (character_id) REFERENCES characters(id) ON DELETE CASCADE
)";

if (mysqli_query($conn, $create_character_abilities)) {
    echo "Character abilities table created successfully<br>";
} else {
    echo "Error creating character_abilities table: " . mysqli_error($conn) . "<br>";
}

// Create character_disciplines table
$create_character_disciplines = "CREATE TABLE IF NOT EXISTS character_disciplines (
    id INT AUTO_INCREMENT PRIMARY KEY,
    character_id INT NOT NULL,
    discipline_name VARCHAR(100) NOT NULL,
    level ENUM('Basic', 'Intermediate', 'Advanced') NOT NULL,
    xp_cost INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (character_id) REFERENCES characters(id) ON DELETE CASCADE
)";

if (mysqli_query($conn, $create_character_disciplines)) {
    echo "Character disciplines table created successfully<br>";
} else {
    echo "Error creating character_disciplines table: " . mysqli_error($conn) . "<br>";
}

// Create character_backgrounds table
$create_character_backgrounds = "CREATE TABLE IF NOT EXISTS character_backgrounds (
    id INT AUTO_INCREMENT PRIMARY KEY,
    character_id INT NOT NULL,
    background_name VARCHAR(100) NOT NULL,
    level INT NOT NULL CHECK (level >= 1 AND level <= 5),
    xp_cost INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (character_id) REFERENCES characters(id) ON DELETE CASCADE
)";

if (mysqli_query($conn, $create_character_backgrounds)) {
    echo "Character backgrounds table created successfully<br>";
} else {
    echo "Error creating character_backgrounds table: " . mysqli_error($conn) . "<br>";
}

// Create character_merits_flaws table
$create_character_merits_flaws = "CREATE TABLE IF NOT EXISTS character_merits_flaws (
    id INT AUTO_INCREMENT PRIMARY KEY,
    character_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    type ENUM('merit', 'flaw') NOT NULL,
    point_value INT NOT NULL,
    description TEXT,
    xp_bonus INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (character_id) REFERENCES characters(id) ON DELETE CASCADE
)";

if (mysqli_query($conn, $create_character_merits_flaws)) {
    echo "Character merits & flaws table created successfully<br>";
} else {
    echo "Error creating character_merits_flaws table: " . mysqli_error($conn) . "<br>";
}

// Create character_morality table
$create_character_morality = "CREATE TABLE IF NOT EXISTS character_morality (
    id INT AUTO_INCREMENT PRIMARY KEY,
    character_id INT NOT NULL,
    path_name VARCHAR(50) DEFAULT 'Humanity',
    path_rating INT DEFAULT 7 CHECK (path_rating >= 1 AND path_rating <= 10),
    conscience INT DEFAULT 1 CHECK (conscience >= 1 AND conscience <= 5),
    self_control INT DEFAULT 1 CHECK (self_control >= 1 AND self_control <= 5),
    courage INT DEFAULT 1 CHECK (courage >= 1 AND courage <= 5),
    willpower_permanent INT DEFAULT 5 CHECK (willpower_permanent >= 1 AND willpower_permanent <= 10),
    willpower_current INT DEFAULT 5,
    humanity INT DEFAULT 7 CHECK (humanity >= 1 AND humanity <= 10),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (character_id) REFERENCES characters(id) ON DELETE CASCADE
)";

if (mysqli_query($conn, $create_character_morality)) {
    echo "Character morality table created successfully<br>";
} else {
    echo "Error creating character_morality table: " . mysqli_error($conn) . "<br>";
}

// Create character_derangements table
$create_character_derangements = "CREATE TABLE IF NOT EXISTS character_derangements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    character_id INT NOT NULL,
    derangement_name VARCHAR(100) NOT NULL,
    description TEXT,
    severity ENUM('mild', 'moderate', 'severe') DEFAULT 'mild',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (character_id) REFERENCES characters(id) ON DELETE CASCADE
)";

if (mysqli_query($conn, $create_character_derangements)) {
    echo "Character derangements table created successfully<br>";
} else {
    echo "Error creating character_derangements table: " . mysqli_error($conn) . "<br>";
}

// Create character_equipment table
$create_character_equipment = "CREATE TABLE IF NOT EXISTS character_equipment (
    id INT AUTO_INCREMENT PRIMARY KEY,
    character_id INT NOT NULL,
    item_name VARCHAR(200) NOT NULL,
    item_type VARCHAR(50),
    description TEXT,
    quantity INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (character_id) REFERENCES characters(id) ON DELETE CASCADE
)";

if (mysqli_query($conn, $create_character_equipment)) {
    echo "Character equipment table created successfully<br>";
} else {
    echo "Error creating character_equipment table: " . mysqli_error($conn) . "<br>";
}

// Create character_influences table
$create_character_influences = "CREATE TABLE IF NOT EXISTS character_influences (
    id INT AUTO_INCREMENT PRIMARY KEY,
    character_id INT NOT NULL,
    influence_type VARCHAR(100) NOT NULL,
    level INT NOT NULL CHECK (level >= 1 AND level <= 5),
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (character_id) REFERENCES characters(id) ON DELETE CASCADE
)";

if (mysqli_query($conn, $create_character_influences)) {
    echo "Character influences table created successfully<br>";
} else {
    echo "Error creating character_influences table: " . mysqli_error($conn) . "<br>";
}

// Create character_rituals table
$create_character_rituals = "CREATE TABLE IF NOT EXISTS character_rituals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    character_id INT NOT NULL,
    ritual_name VARCHAR(200) NOT NULL,
    ritual_type ENUM('Thaumaturgy', 'Necromancy', 'Other') NOT NULL,
    level INT NOT NULL CHECK (level >= 1 AND level <= 5),
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (character_id) REFERENCES characters(id) ON DELETE CASCADE
)";

if (mysqli_query($conn, $create_character_rituals)) {
    echo "Character rituals table created successfully<br>";
} else {
    echo "Error creating character_rituals table: " . mysqli_error($conn) . "<br>";
}

// Create character_status table
$create_character_status = "CREATE TABLE IF NOT EXISTS character_status (
    id INT AUTO_INCREMENT PRIMARY KEY,
    character_id INT NOT NULL,
    sect_status VARCHAR(100),
    clan_status VARCHAR(100),
    city_status VARCHAR(100),
    health_levels VARCHAR(50) DEFAULT 'Healthy',
    blood_pool_current INT DEFAULT 10,
    blood_pool_maximum INT DEFAULT 10,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (character_id) REFERENCES characters(id) ON DELETE CASCADE
)";

if (mysqli_query($conn, $create_character_status)) {
    echo "Character status table created successfully<br>";
} else {
    echo "Error creating character_status table: " . mysqli_error($conn) . "<br>";
}

// Create admin account
$admin_username = "admin";
$admin_password = "admin123"; // Change this!
$admin_email = "admin@example.com";

$hashed_password = password_hash($admin_password, PASSWORD_DEFAULT);

$insert_admin = "INSERT INTO users (username, password, email, role) 
                 VALUES ('$admin_username', '$hashed_password', '$admin_email', 'admin')";

if (mysqli_query($conn, $insert_admin)) {
    echo "Admin account created successfully<br>";
    echo "Username: $admin_username<br>";
    echo "Password: $admin_password<br>";
} else {
    echo "Error creating admin: " . mysqli_error($conn) . "<br>";
}

mysqli_close($conn);
?>
</body>
</html>