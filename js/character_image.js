/**
 * Character Image Upload Module
 * Handles image upload, preview, and removal for character portraits
 */

class CharacterImageManager {
    constructor(characterId = null) {
        this.characterId = characterId;
        this.currentImagePath = null;
        this.characterData = null;
        this.selectedFile = null;
        
        this.init();
    }
    
    init() {
        // Set up file input change handler
        const fileInput = document.getElementById('characterImageInput');
        if (fileInput) {
            fileInput.addEventListener('change', (e) => this.handleFileSelect(e));
        }
        
        // Set up upload button
        const uploadBtn = document.getElementById('uploadCharacterImageBtn');
        if (uploadBtn) {
            uploadBtn.addEventListener('click', (e) => this.handleUploadImage(e));
        }
        
        // Set up remove button
        const removeBtn = document.getElementById('removeCharacterImageBtn');
        if (removeBtn) {
            removeBtn.addEventListener('click', (e) => this.handleRemoveImage(e));
        }
    }
    
    /**
     * Handle file selection with preview
     */
    handleFileSelect(event) {
        const file = event.target.files[0];
        if (!file) return;
        
        // Validate file type
        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        if (!allowedTypes.includes(file.type)) {
            this.showError('Invalid file type. Please select a JPG, PNG, or GIF image.');
            event.target.value = ''; // Clear selection
            return;
        }
        
        // Validate file size (2MB max)
        const maxSize = 2 * 1024 * 1024; // 2MB
        if (file.size > maxSize) {
            this.showError('File size exceeds 2MB limit. Please select a smaller image.');
            event.target.value = '';
            return;
        }
        
        // Show preview
        const reader = new FileReader();
        reader.onload = (e) => {
            this.showPreview(e.target.result);
            // Store file for later upload
            this.selectedFile = file;
        };
        reader.readAsDataURL(file);
    }
    
    /**
     * Handle remove button click
     */
    async handleRemoveImage(event) {
        event.preventDefault();
        await this.removeImage();
    }
    
    /**
     * Handle upload button click
     */
    async handleUploadImage(event) {
        event.preventDefault();
        if (this.selectedFile && this.characterId) {
            const success = await this.uploadImage(this.selectedFile);
            if (success) {
                // Hide upload button
                const uploadBtn = document.getElementById('uploadCharacterImageBtn');
                if (uploadBtn) uploadBtn.style.display = 'none';
                
                // Clear file input
                const fileInput = document.getElementById('characterImageInput');
                if (fileInput) fileInput.value = '';
                this.selectedFile = null;
            }
        }
    }
    
    /**
     * Upload image to server
     */
    async uploadImage(file) {
        if (!this.characterId) {
            this.showError('Cannot upload image: Character ID not set');
            return;
        }
        
        const formData = new FormData();
        formData.append('image', file);
        formData.append('character_id', this.characterId);
        
        try {
            const response = await fetch('upload_character_image.php', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                this.currentImagePath = result.image_path;
                this.showSuccess('Image uploaded successfully!');
                return true;
            } else {
                this.showError(result.message || 'Upload failed');
                return false;
            }
        } catch (error) {
            this.showError('Upload error: ' + error.message);
            return false;
        }
    }
    
    /**
     * Remove image from server
     */
    async removeImage() {
        if (!this.characterId) {
            this.showError('Cannot remove image: Character ID not set');
            return;
        }
        
        if (!confirm('Are you sure you want to remove the character image?')) {
            return;
        }
        
        const formData = new FormData();
        formData.append('character_id', this.characterId);
        
        try {
            const response = await fetch('remove_character_image.php', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                this.currentImagePath = null;
                this.clearPreview();
                this.showSuccess('Image removed successfully!');
                return true;
            } else {
                this.showError(result.message || 'Remove failed');
                return false;
            }
        } catch (error) {
            this.showError('Remove error: ' + error.message);
            return false;
        }
    }
    
    /**
     * Show image preview
     */
    showPreview(imageData) {
        const preview = document.getElementById('characterImagePreview');
        const placeholder = document.getElementById('characterImagePlaceholder');
        
        if (preview) {
            preview.src = imageData;
            preview.style.display = 'block';
            preview.classList.remove('clan-icon');
        }
        
        if (placeholder) {
            placeholder.style.display = 'none';
        }
        
        // Show upload button
        const uploadBtn = document.getElementById('uploadCharacterImageBtn');
        if (uploadBtn) {
            uploadBtn.style.display = 'inline-block';
        }
        
        // Hide remove button (no image uploaded yet)
        const removeBtn = document.getElementById('removeCharacterImageBtn');
        if (removeBtn) {
            removeBtn.style.display = 'none';
        }
    }
    
    /**
     * Clear preview and show placeholder
     */
    clearPreview() {
        const preview = document.getElementById('characterImagePreview');
        if (preview) {
            preview.style.display = 'none';
        }
        
        const placeholder = document.getElementById('characterImagePlaceholder');
        if (placeholder) {
            placeholder.style.display = 'block';
        }
        
        const uploadBtn = document.getElementById('uploadCharacterImageBtn');
        if (uploadBtn) {
            uploadBtn.style.display = 'none';
        }
        
        const removeBtn = document.getElementById('removeCharacterImageBtn');
        if (removeBtn) {
            removeBtn.style.display = 'none';
        }
    }
    
    /**
     * Set character ID and load image
     */
    setCharacterId(characterId, characterData = null) {
        this.characterId = characterId;
        this.characterData = characterData;
        
        // Load image if character data provided
        if (characterData && characterData.character && characterData.character.character_image) {
            this.currentImagePath = characterData.character.character_image;
            this.displayImage(characterData.character.character_image, characterData.character.clan);
        } else if (characterData && characterData.character) {
            // Show clan SVG or default
            this.displayImage(null, characterData.character.clan);
        }
    }
    
    /**
     * Display character image with fallbacks
     */
    displayImage(imagePath, clan) {
        const preview = document.getElementById('characterImagePreview');
        const placeholder = document.getElementById('characterImagePlaceholder');
        
        if (imagePath) {
            // User uploaded image
            if (preview) {
                preview.src = imagePath;
                preview.style.display = 'block';
                preview.classList.remove('clan-icon');
            }
            if (placeholder) placeholder.style.display = 'none';
            
            // Show remove button
            const removeBtn = document.getElementById('removeCharacterImageBtn');
            if (removeBtn) {
                removeBtn.style.display = 'inline-block';
            }
            
            // Hide upload button
            const uploadBtn = document.getElementById('uploadCharacterImageBtn');
            if (uploadBtn) {
                uploadBtn.style.display = 'none';
            }
        } else {
            // No user image - determine if character is NPC or finalized
            const character = this.characterData?.character;
            const isNPC = character?.pc == 0 || character?.player_name === 'ST/NPC';
            const isFinalized = character?.status === 'finalized';
            
            if (isNPC || isFinalized) {
                // Show clan SVG
                const clanSVG = `/images/svgs/${clan}.svg`;
                if (preview) {
                    preview.src = clanSVG;
                    preview.style.display = 'block';
                    preview.classList.add('clan-icon');
                }
                if (placeholder) placeholder.style.display = 'none';
            } else {
                // Show default placeholder for editable characters
                if (preview) {
                    preview.style.display = 'none';
                    preview.src = '';
                }
                if (placeholder) placeholder.style.display = 'flex';
            }
        }
    }
    
    /**
     * Helper functions for notification
     */
    showSuccess(message) {
        // Use existing notification system if available
        if (typeof showNotification === 'function') {
            showNotification('✅ ' + message, 'success');
        } else {
            console.log('Success:', message);
        }
    }
    
    showError(message) {
        // Use existing notification system if available
        if (typeof showNotification === 'function') {
            showNotification('❌ ' + message, 'error');
        } else {
            console.error('Error:', message);
            alert('Error: ' + message);
        }
    }
}

// Global instance
let characterImageManager = null;

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    characterImageManager = new CharacterImageManager();
});

