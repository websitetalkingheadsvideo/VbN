/**
 * HTML2Canvas Integration for VbN Character Sheets
 * Converts character sheets to downloadable images
 * 100% FREE - No API keys or external services required
 */

/**
 * Convert a DOM element to an image and download it
 * @param {string} elementId - The ID of the element to convert
 * @param {string} filename - The name for the downloaded file
 * @param {object} options - Additional html2canvas options
 */
async function convertToImage(elementId, filename = 'character-sheet.png', options = {}) {
    try {
        // Get the element to convert
        const element = document.getElementById(elementId);
        
        if (!element) {
            console.error(`Element with ID "${elementId}" not found`);
            return;
        }

        // Show loading indicator if available
        const loadingElement = document.getElementById('image-loading');
        if (loadingElement) {
            loadingElement.style.display = 'block';
        }

        // Default options
        const defaultOptions = {
            backgroundColor: '#1a0f0f',
            scale: 2, // Higher quality
            logging: false,
            useCORS: true, // Allow loading images from other domains
            allowTaint: true
        };

        // Merge with custom options
        const finalOptions = { ...defaultOptions, ...options };

        // Convert to canvas
        const canvas = await html2canvas(element, finalOptions);

        // Convert canvas to blob
        canvas.toBlob(function(blob) {
            // Create download link
            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.download = filename;
            link.href = url;
            link.click();

            // Cleanup
            URL.revokeObjectURL(url);

            // Hide loading indicator
            if (loadingElement) {
                loadingElement.style.display = 'none';
            }
        }, 'image/png');

    } catch (error) {
        console.error('Error converting to image:', error);
        alert('Failed to convert character sheet to image. Please try again.');
    }
}

/**
 * Get image as base64 string instead of downloading
 * Useful for uploading to server or displaying inline
 */
async function getImageAsBase64(elementId, options = {}) {
    try {
        const element = document.getElementById(elementId);
        
        if (!element) {
            console.error(`Element with ID "${elementId}" not found`);
            return null;
        }

        const defaultOptions = {
            backgroundColor: '#1a0f0f',
            scale: 2,
            logging: false,
            useCORS: true,
            allowTaint: true
        };

        const finalOptions = { ...defaultOptions, ...options };
        const canvas = await html2canvas(element, finalOptions);
        
        return canvas.toDataURL('image/png');
    } catch (error) {
        console.error('Error getting image as base64:', error);
        return null;
    }
}

/**
 * Share character sheet image (if browser supports Web Share API)
 */
async function shareCharacterSheet(elementId, title = 'My Character Sheet') {
    try {
        const element = document.getElementById(elementId);
        
        if (!element) {
            console.error(`Element with ID "${elementId}" not found`);
            return;
        }

        const canvas = await html2canvas(element, {
            backgroundColor: '#1a0f0f',
            scale: 2,
            logging: false,
            useCORS: true,
            allowTaint: true
        });

        // Convert to blob
        canvas.toBlob(async function(blob) {
            if (navigator.share && navigator.canShare) {
                const file = new File([blob], 'character-sheet.png', { type: 'image/png' });
                
                if (navigator.canShare({ files: [file] })) {
                    await navigator.share({
                        files: [file],
                        title: title,
                        text: 'Check out my Vampire character!'
                    });
                } else {
                    // Fallback to download if sharing not supported
                    convertToImage(elementId);
                }
            } else {
                // Fallback to download
                convertToImage(elementId);
            }
        }, 'image/png');

    } catch (error) {
        console.error('Error sharing character sheet:', error);
        // Fallback to download
        convertToImage(elementId);
    }
}

// Export functions for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        convertToImage,
        getImageAsBase64,
        shareCharacterSheet
    };
}

