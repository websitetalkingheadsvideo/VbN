<?php
/**
 * Admin - Create/Edit Location
 * Prototype for visualizing location data structure
 */

session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// TODO: Add proper admin check
// if (!$_SESSION['is_admin']) { header('Location: dashboard.php'); exit(); }

define('LOTN_VERSION', '0.2.1');

// Get location ID if editing
$location_id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$is_edit = $location_id !== null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $is_edit ? 'Edit' : 'Create' ?> Location - VbN Admin</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/admin_location.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="admin-container">
        <div class="admin-header">
            <h1><i class="fas fa-map-marker-alt"></i> <?= $is_edit ? 'Edit' : 'Create' ?> Location</h1>
            <p>Define location properties, features, and relationships</p>
            <a href="admin_locations.php" class="btn-secondary">← Back to Locations</a>
        </div>

        <form id="location-form" class="location-form">
            <input type="hidden" id="location_id" value="<?= $location_id ?>">

            <!-- Basic Information Section -->
            <section class="form-section">
                <h2><i class="fas fa-info-circle"></i> Basic Information</h2>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="name">Location Name *</label>
                        <input type="text" id="name" name="name" required>
                    </div>

                    <div class="form-group">
                        <label for="location_type">Type of Location *</label>
                        <select id="location_type" name="location_type" required>
                            <option value="">-- Select Type --</option>
                            <option value="Haven">Haven</option>
                            <option value="Elysium">Elysium</option>
                            <option value="Domain">Domain</option>
                            <option value="Hunting Ground">Hunting Ground</option>
                            <option value="Nightclub">Nightclub</option>
                            <option value="Gathering Place">Gathering Place (Club, Rack)</option>
                            <option value="Business">Business/Mortal Front</option>
                            <option value="Chantry">Chantry</option>
                            <option value="Temple">Temple/Sacred Site</option>
                            <option value="Wilderness">Wilderness Area</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="summary">Short Summary</label>
                    <input type="text" id="summary" name="summary" maxlength="500" 
                           placeholder="Brief one-line description">
                    <small>Max 500 characters - Quick overview for lists</small>
                </div>

                <div class="form-group">
                    <label for="description">Full Description (POV Style)</label>
                    <textarea id="description" name="description" rows="6"
                              placeholder="Describe the location as if the character is experiencing it..."></textarea>
                    <small>Write from the character's perspective - what they see, hear, smell, feel</small>
                </div>

                <div class="form-group">
                    <label for="notes">GM Notes (Private)</label>
                    <textarea id="notes" name="notes" rows="4"
                              placeholder="Admin/storyteller notes not visible to players..."></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="status">Status *</label>
                        <select id="status" name="status" required>
                            <option value="Active">Active</option>
                            <option value="Abandoned">Abandoned</option>
                            <option value="Destroyed">Destroyed</option>
                            <option value="Under Construction">Under Construction</option>
                            <option value="Contested">Contested</option>
                            <option value="Hidden">Hidden/Secret</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="status_notes">Status Notes</label>
                        <input type="text" id="status_notes" name="status_notes" 
                               placeholder="Additional details about status...">
                    </div>
                </div>
            </section>

            <!-- Geography Section -->
            <section class="form-section">
                <h2><i class="fas fa-map"></i> Geography</h2>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="district">District/Area</label>
                        <input type="text" id="district" name="district" 
                               placeholder="e.g., Downtown Phoenix, Scottsdale, Tempe">
                    </div>

                    <div class="form-group">
                        <label for="address">Street Address</label>
                        <input type="text" id="address" name="address" 
                               placeholder="Optional street address">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="latitude">Latitude</label>
                        <input type="number" id="latitude" name="latitude" step="0.000001" 
                               placeholder="33.4484">
                    </div>

                    <div class="form-group">
                        <label for="longitude">Longitude</label>
                        <input type="number" id="longitude" name="longitude" step="0.000001" 
                               placeholder="-112.0740">
                    </div>
                </div>
            </section>

            <!-- Ownership & Control Section -->
            <section class="form-section">
                <h2><i class="fas fa-key"></i> Ownership & Control</h2>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="owner_type">Ownership Type *</label>
                        <select id="owner_type" name="owner_type" required>
                            <option value="">-- Select Type --</option>
                            <option value="Individual">Individual Character</option>
                            <option value="Coterie">Coterie (Shared Group)</option>
                            <option value="Clan">Clan Property</option>
                            <option value="Faction">Faction Territory (Camarilla/Anarch)</option>
                            <option value="Contested">Contested (Multiple Claimants)</option>
                            <option value="Public">Public/Neutral</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="owner_notes">Ownership Notes</label>
                        <textarea id="owner_notes" name="owner_notes" rows="2"
                                  placeholder="Who specifically owns/controls this location?"></textarea>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="faction">Faction Affiliation</label>
                        <select id="faction" name="faction">
                            <option value="">-- None/Neutral --</option>
                            <option value="Camarilla">Camarilla</option>
                            <option value="Anarch">Anarch</option>
                            <option value="Independent">Independent</option>
                            <option value="Sabbat">Sabbat</option>
                            <option value="Mortal">Mortal Controlled</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="access_control">Access Control *</label>
                        <select id="access_control" name="access_control" required>
                            <option value="">-- Select Access Level --</option>
                            <option value="Public">Public (Anyone Can Enter)</option>
                            <option value="Open">Open (Sect Members)</option>
                            <option value="Restricted">Restricted (Specific Groups)</option>
                            <option value="Private">Private (Invitation Only)</option>
                            <option value="Threshold">Threshold (Vampire Invitation Rules)</option>
                            <option value="Elysium">Elysium (Special Rules)</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="access_notes">Access Control Details</label>
                    <textarea id="access_notes" name="access_notes" rows="2"
                              placeholder="Who can enter? What are the specific rules or requirements?"></textarea>
                </div>
            </section>

            <!-- Security Features Section -->
            <section class="form-section">
                <h2><i class="fas fa-shield-alt"></i> Security Features</h2>
                
                <div class="form-group">
                    <label>Security Level</label>
                    <div class="radio-group">
                        <label><input type="radio" name="security_level" value="1"> 1 - Minimal</label>
                        <label><input type="radio" name="security_level" value="2"> 2 - Basic</label>
                        <label><input type="radio" name="security_level" value="3" checked> 3 - Moderate</label>
                        <label><input type="radio" name="security_level" value="4"> 4 - High</label>
                        <label><input type="radio" name="security_level" value="5"> 5 - Maximum</label>
                    </div>
                </div>

                <div class="checkbox-grid">
                    <label class="checkbox-label">
                        <input type="checkbox" name="security_locks" value="1">
                        <span>Locks & Deadbolts</span>
                    </label>
                    <label class="checkbox-label">
                        <input type="checkbox" name="security_alarms" value="1">
                        <span>Alarm Systems</span>
                    </label>
                    <label class="checkbox-label">
                        <input type="checkbox" name="security_guards" value="1">
                        <span>Guards/Security Personnel</span>
                    </label>
                    <label class="checkbox-label">
                        <input type="checkbox" name="security_hidden_entrance" value="1">
                        <span>Hidden Entrances</span>
                    </label>
                    <label class="checkbox-label">
                        <input type="checkbox" name="security_sunlight_protected" value="1">
                        <span>Sunlight Protection</span>
                    </label>
                    <label class="checkbox-label">
                        <input type="checkbox" name="security_warding_rituals" value="1">
                        <span>Warding Rituals</span>
                    </label>
                    <label class="checkbox-label">
                        <input type="checkbox" name="security_cameras" value="1">
                        <span>Security Cameras</span>
                    </label>
                    <label class="checkbox-label">
                        <input type="checkbox" name="security_reinforced" value="1">
                        <span>Reinforced Structure</span>
                    </label>
                </div>

                <div class="form-group">
                    <label for="security_notes">Security Details</label>
                    <textarea id="security_notes" name="security_notes" rows="2"
                              placeholder="Additional security details, special measures, weaknesses..."></textarea>
                </div>
            </section>

            <!-- Utility Features Section -->
            <section class="form-section">
                <h2><i class="fas fa-tools"></i> Utility Features</h2>
                
                <div class="checkbox-grid">
                    <label class="checkbox-label">
                        <input type="checkbox" name="utility_blood_storage" value="1">
                        <span>Blood Storage/Refrigeration</span>
                    </label>
                    <label class="checkbox-label">
                        <input type="checkbox" name="utility_computers" value="1">
                        <span>Computer Systems</span>
                    </label>
                    <label class="checkbox-label">
                        <input type="checkbox" name="utility_library" value="1">
                        <span>Library/Archives</span>
                    </label>
                    <label class="checkbox-label">
                        <input type="checkbox" name="utility_medical" value="1">
                        <span>Medical Facilities</span>
                    </label>
                    <label class="checkbox-label">
                        <input type="checkbox" name="utility_workshop" value="1">
                        <span>Workshop/Crafting Area</span>
                    </label>
                    <label class="checkbox-label">
                        <input type="checkbox" name="utility_hidden_caches" value="1">
                        <span>Hidden Item Caches</span>
                    </label>
                    <label class="checkbox-label">
                        <input type="checkbox" name="utility_armory" value="1">
                        <span>Armory/Weapons Storage</span>
                    </label>
                    <label class="checkbox-label">
                        <input type="checkbox" name="utility_communications" value="1">
                        <span>Communications Equipment</span>
                    </label>
                </div>

                <div class="form-group">
                    <label for="utility_notes">Utility Features Details</label>
                    <textarea id="utility_notes" name="utility_notes" rows="2"
                              placeholder="Describe utility features, equipment, special capabilities..."></textarea>
                </div>
            </section>

            <!-- Social Features Section -->
            <section class="form-section">
                <h2><i class="fas fa-users"></i> Social Features</h2>
                
                <div class="form-group">
                    <label for="social_features">Social Importance & Features</label>
                    <textarea id="social_features" name="social_features" rows="4"
                              placeholder="Status/prestige value, meeting space capacity, entertainment facilities, mortal fronts, social significance..."></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="capacity">Capacity (People)</label>
                        <input type="number" id="capacity" name="capacity" min="1" 
                               placeholder="How many can gather here?">
                    </div>

                    <div class="form-group">
                        <label for="prestige_level">Prestige Level</label>
                        <select id="prestige_level" name="prestige_level">
                            <option value="0">None</option>
                            <option value="1">Minor</option>
                            <option value="2">Moderate</option>
                            <option value="3">Significant</option>
                            <option value="4">Major</option>
                            <option value="5">Legendary</option>
                        </select>
                    </div>
                </div>
            </section>

            <!-- Supernatural Features Section -->
            <section class="form-section">
                <h2><i class="fas fa-magic"></i> Supernatural Features</h2>
                
                <div class="form-group">
                    <label class="toggle-label">
                        <input type="checkbox" id="has_supernatural" name="has_supernatural" value="1">
                        <span>This location has supernatural properties</span>
                    </label>
                </div>

                <div id="supernatural-fields" style="display: none;">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="node_points">Node Points</label>
                            <input type="number" id="node_points" name="node_points" min="0" max="10" 
                                   placeholder="0-10">
                            <small>Magical energy available at this location (typically 0-5 for minor, 6-10 for major nodes)</small>
                        </div>

                        <div class="form-group">
                            <label for="node_type">Node Type</label>
                            <select id="node_type" name="node_type">
                                <option value="">None</option>
                                <option value="Standard">Standard</option>
                                <option value="Corrupted">Corrupted</option>
                                <option value="Pure">Pure</option>
                                <option value="Ancient">Ancient</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="ritual_space">Ritual Space Description</label>
                        <textarea id="ritual_space" name="ritual_space" rows="3"
                                  placeholder="Describe ritual spaces, altars, circles, preparation areas..."></textarea>
                    </div>

                    <div class="form-group">
                        <label for="magical_protection">Magical Protection</label>
                        <textarea id="magical_protection" name="magical_protection" rows="3"
                                  placeholder="Wards, shields, protective spells, anti-scrying measures..."></textarea>
                    </div>

                    <div class="form-group">
                        <label for="cursed_blessed">Cursed/Blessed Properties</label>
                        <textarea id="cursed_blessed" name="cursed_blessed" rows="3"
                                  placeholder="Supernatural blessings, curses, hauntings, spiritual significance..."></textarea>
                    </div>
                </div>
            </section>

            <!-- Relationships Section -->
            <section class="form-section">
                <h2><i class="fas fa-sitemap"></i> Location Relationships</h2>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="parent_location">Part of Larger Location</label>
                        <select id="parent_location" name="parent_location">
                            <option value="">-- None (Standalone) --</option>
                            <option value="1">Example Building</option>
                            <option value="2">Example District</option>
                            <!-- Will be populated dynamically -->
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="relationship_type">Relationship Type</label>
                        <select id="relationship_type" name="relationship_type">
                            <option value="">-- None --</option>
                            <option value="Room In">Room/Suite In</option>
                            <option value="Floor Of">Floor Of</option>
                            <option value="Building In">Building In (District)</option>
                            <option value="Connected To">Connected To</option>
                            <option value="Part Of">Part Of (Complex)</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="relationship_notes">Relationship Notes</label>
                    <textarea id="relationship_notes" name="relationship_notes" rows="2"
                              placeholder="Describe how this location relates to others, connections, access routes..."></textarea>
                </div>
            </section>

            <!-- Image Section -->
            <section class="form-section">
                <h2><i class="fas fa-image"></i> Location Image</h2>
                
                <div class="form-group">
                    <label for="image">Image URL</label>
                    <input type="url" id="image" name="image" 
                           placeholder="https://example.com/location-image.jpg">
                    <small>Optional image to represent this location</small>
                </div>
            </section>

            <!-- Form Actions -->
            <div class="form-actions">
                <button type="submit" class="btn-primary">
                    <i class="fas fa-save"></i> <?= $is_edit ? 'Update' : 'Create' ?> Location
                </button>
                <button type="button" class="btn-secondary" onclick="window.location.href='admin_locations.php'">
                    <i class="fas fa-times"></i> Cancel
                </button>
            </div>
        </form>
    </div>

    <script src="js/admin_location.js"></script>
</body>
</html>

