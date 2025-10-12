<?php
/**
 * Admin - Create Location from Story
 * AI-powered narrative-to-database feature
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Location from Story - VbN Admin</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/admin_location.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .story-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            margin-top: 2rem;
        }

        .story-input-section, .story-preview-section {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            padding: 1.5rem;
        }

        .story-textarea {
            width: 100%;
            min-height: 400px;
            background: rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 4px;
            color: #fff;
            padding: 1rem;
            font-family: 'Georgia', serif;
            font-size: 1rem;
            line-height: 1.6;
            resize: vertical;
        }

        .story-textarea::placeholder {
            color: rgba(255, 255, 255, 0.4);
            font-style: italic;
        }

        .parse-button {
            width: 100%;
            margin-top: 1rem;
            padding: 1rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 4px;
            color: white;
            font-size: 1.1rem;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
        }

        .parse-button:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }

        .parse-button:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .parse-button .spinner {
            display: none;
            animation: spin 1s linear infinite;
        }

        .parse-button.loading .spinner {
            display: inline-block;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .preview-empty {
            text-align: center;
            padding: 3rem;
            color: rgba(255, 255, 255, 0.4);
        }

        .preview-empty i {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.3;
        }

        .field-group {
            margin-bottom: 1rem;
            padding: 0.75rem;
            background: rgba(0, 0, 0, 0.2);
            border-radius: 4px;
            border-left: 3px solid transparent;
        }

        .field-group.confidence-high {
            border-left-color: #10b981;
        }

        .field-group.confidence-medium {
            border-left-color: #f59e0b;
        }

        .field-group.confidence-low {
            border-left-color: #ef4444;
        }

        .field-label {
            font-size: 0.85rem;
            color: rgba(255, 255, 255, 0.6);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0.25rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .confidence-badge {
            font-size: 0.7rem;
            padding: 0.15rem 0.5rem;
            border-radius: 3px;
            font-weight: bold;
        }

        .confidence-high .confidence-badge {
            background: rgba(16, 185, 129, 0.2);
            color: #10b981;
        }

        .confidence-medium .confidence-badge {
            background: rgba(245, 158, 11, 0.2);
            color: #f59e0b;
        }

        .confidence-low .confidence-badge {
            background: rgba(239, 68, 68, 0.2);
            color: #ef4444;
        }

        .field-value {
            color: #fff;
            font-size: 0.95rem;
        }

        .field-value input,
        .field-value textarea,
        .field-value select {
            width: 100%;
            background: rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 4px;
            color: #fff;
            padding: 0.5rem;
        }

        .field-value textarea {
            resize: vertical;
            min-height: 60px;
        }

        .checkbox-group {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .checkbox-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.25rem 0.75rem;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 4px;
            font-size: 0.85rem;
        }

        .section-header {
            font-size: 1.1rem;
            font-weight: bold;
            margin: 1.5rem 0 1rem 0;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid rgba(255, 255, 255, 0.2);
            color: #667eea;
        }

        .save-actions {
            position: sticky;
            bottom: 0;
            background: rgba(26, 26, 46, 0.95);
            padding: 1rem;
            margin: 1rem -1.5rem -1.5rem -1.5rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            gap: 1rem;
        }

        .btn-save {
            flex: 1;
            padding: 1rem;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border: none;
            border-radius: 4px;
            color: white;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-save:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(16, 185, 129, 0.4);
        }

        .btn-save:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .btn-cancel {
            padding: 1rem 2rem;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 4px;
            color: white;
            cursor: pointer;
            transition: all 0.3s;
        }

        .example-link {
            display: inline-block;
            margin-top: 0.5rem;
            color: #667eea;
            text-decoration: none;
            font-size: 0.9rem;
        }

        .example-link:hover {
            text-decoration: underline;
        }

        @media (max-width: 1200px) {
            .story-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="admin-header">
            <h1><i class="fas fa-magic"></i> Create Location from Story</h1>
            <p>Write or paste a narrative description, and AI will extract structured location data</p>
            <a href="admin_locations.php" class="btn-secondary">← Back to Locations</a>
        </div>

        <div class="story-container">
            <!-- Input Section -->
            <div class="story-input-section">
                <h2><i class="fas fa-pen-fancy"></i> Location Narrative</h2>
                <p style="color: rgba(255,255,255,0.6); font-size: 0.9rem; margin-bottom: 1rem;">
                    Describe the location as if you're telling a story. Include details about its appearance, 
                    atmosphere, ownership, security, features, and any supernatural elements.
                </p>
                
                <textarea 
                    id="story-textarea" 
                    class="story-textarea" 
                    placeholder="The abandoned warehouse sits in the industrial district of South Phoenix, its windows long since boarded up against prying eyes. Once a legitimate shipping depot, it now serves as a haven for the Nosferatu clan, who have transformed its underground levels into a labyrinth of tunnels and chambers. The main floor appears derelict, but hidden behind false walls are state-of-the-art computer systems and a small armory. A ward placed by the local Tremere prevents unwanted supernatural visitors from entering without invitation. The location can hold perhaps 20 individuals comfortably, though few beyond the clan ever receive invitations to enter..."
                ></textarea>

                <a href="#" class="example-link" id="load-example">
                    <i class="fas fa-lightbulb"></i> Load example narrative
                </a>

                <button id="parse-button" class="parse-button">
                    <i class="fas fa-wand-magic-sparkles"></i>
                    <i class="fas fa-spinner spinner"></i>
                    <span class="button-text">Parse Story with AI</span>
                </button>

                <div style="margin-top: 1rem; padding: 1rem; background: rgba(102, 126, 234, 0.1); border-radius: 4px; font-size: 0.85rem; color: rgba(255,255,255,0.7);">
                    <i class="fas fa-info-circle"></i> 
                    AI will analyze your narrative and extract all possible location fields. 
                    Review and adjust the extracted data in the preview panel before saving.
                </div>
            </div>

            <!-- Preview Section -->
            <div class="story-preview-section">
                <h2><i class="fas fa-eye"></i> Extracted Data Preview</h2>
                
                <div id="preview-content">
                    <div class="preview-empty">
                        <i class="fas fa-arrow-left"></i>
                        <p>Write or paste a location narrative on the left, then click "Parse Story with AI" to see extracted fields here.</p>
                    </div>
                </div>

                <div id="save-actions" class="save-actions" style="display: none;">
                    <button id="save-button" class="btn-save">
                        <i class="fas fa-save"></i> Save Location to Database
                    </button>
                    <button id="cancel-button" class="btn-cancel">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="js/location_story_parser.js"></script>
</body>
</html>

