// Strict, modular JS for character image upload and preview
(() => {
    'use strict';

    // Confirm script loaded
    console.log('[character-image] script loaded');

    /**
     * Finds an element by id and returns null if missing (no throw here to allow progressive enhancement)
     */
    const byId = (id) => document.getElementById(id);

    const imageFileInput = byId('imageFile');
    const uploadButton = byId('uploadImageBtn');
    const exitEditorButton = byId('exitEditorBtn');
    const previewImg = byId('imagePreview');
    const previewContainer = byId('imagePreviewContainer');
    const characterSheetRoot = byId('characterSheetRoot');

    // Global button click logger (covers all buttons on the page)
    document.addEventListener('click', (event) => {
        const btn = event.target.closest('button');
        if (!btn) return;
        const id = btn.id || '(no-id)';
        const label = (btn.textContent || '').trim();
        const disabled = btn.disabled === true;
        console.log('[character-image] Button clicked:', { id, label, disabled });
    }, { capture: true });

    // Mutation Observer to log when buttons become disabled/enabled (e.g., Next button)
    const logButtonStateChange = (btn) => {
        const id = btn.id || '(no-id)';
        const label = (btn.textContent || '').trim();
        const disabled = btn.disabled === true;
        console.log('[character-image] Button state changed:', { id, label, disabled });
    };

    const observeButtonState = (root) => {
        const observer = new MutationObserver((mutations) => {
            for (const m of mutations) {
                if (m.type === 'attributes' && m.target instanceof HTMLButtonElement && m.attributeName === 'disabled') {
                    logButtonStateChange(m.target);
                }
            }
        });
        observer.observe(root, { attributes: true, subtree: true, attributeFilter: ['disabled'] });
        // Initial snapshot log for common buttons like Next/Save
        const buttons = root.querySelectorAll('button');
        buttons.forEach((b) => logButtonStateChange(b));
    };

    observeButtonState(document.documentElement);

    const getCharacterId = () => {
        if (!characterSheetRoot) return '';
        const val = characterSheetRoot.getAttribute('data-character-id');
        return typeof val === 'string' ? val : '';
    };

    const assertFileSelected = (file) => {
        if (!file) {
            console.error('No file selected for upload');
            alert('Please select an image file first.');
            throw new Error('No file selected');
        }
    };

    const validateMimeAndSize = (file) => {
        const allowed = ['image/jpeg', 'image/png', 'image/webp'];
        const maxBytes = 5 * 1024 * 1024; // 5MB
        if (!allowed.includes(file.type)) {
            const msg = `Unsupported file type: ${file.type}. Allowed: JPG, PNG, WEBP.`;
            console.error(msg);
            alert(msg);
            throw new Error('Bad MIME');
        }
        if (file.size > maxBytes) {
            const msg = 'File too large. Max size is 5MB.';
            console.error(msg);
            alert(msg);
            throw new Error('File too large');
        }
    };

    const previewFile = (file) => {
        console.log('Image preview requested for:', file.name);
        const reader = new FileReader();
        reader.onload = () => {
            if (previewImg) {
                previewImg.src = String(reader.result);
                console.log('Image preview loaded');
            }
        };
        reader.onerror = (e) => {
            console.error('Image preview failed:', e);
            alert('Failed to load preview. See console for details.');
        };
        reader.readAsDataURL(file);
    };

    const sendUpload = async (file, characterId) => {
        const form = new FormData();
        form.append('image', file);
        if (characterId) form.append('characterId', characterId);
        console.log('Upload request sent');
        const resp = await fetch('upload_character_image.php', {
            method: 'POST',
            body: form,
        });
        const isJson = resp.headers.get('content-type')?.includes('application/json');
        if (!isJson) {
            const text = await resp.text();
            console.error('Upload failed: non-JSON response', text);
            throw new Error('Non-JSON response from upload endpoint');
        }
        const data = await resp.json();
        if (!resp.ok || !data?.success) {
            const err = data?.error || `HTTP ${resp.status}`;
            console.error('Upload failed:', err);
            throw new Error(String(err));
        }
        console.log('Upload successful:', data);
        return data;
    };

    const handleFileChange = () => {
        if (!imageFileInput || !imageFileInput.files || imageFileInput.files.length === 0) return;
        const file = imageFileInput.files[0];
        console.log('[character-image] File selected:', file.name);
        try {
            validateMimeAndSize(file);
            previewFile(file);
        } catch (err) {
            console.error('[character-image] File validation/preview error:', err);
        }
    };

    const handleUploadClick = async () => {
        console.log('[character-image] Upload button clicked');
        try {
            const file = imageFileInput?.files?.[0];
            assertFileSelected(file);
            validateMimeAndSize(file);
            const { filePath } = await sendUpload(file, getCharacterId());
            // Persist into a hidden input if present for form submit integration
            const hidden = byId('imagePath');
            if (hidden) {
                hidden.value = filePath;
            }
            // Propagate into app state if present so saves include imagePath
            try {
                if (window.characterCreationApp && window.characterCreationApp.modules && window.characterCreationApp.modules.stateManager) {
                    window.characterCreationApp.modules.stateManager.setStateProperty('imagePath', filePath);
                }
            } catch (e) {
                // non-fatal
            }
            // Update preview to canonical URL if server may transform/stage
            if (previewImg && filePath) {
                previewImg.src = filePath;
            }
            console.log('[character-image] Image uploaded; path set to hidden input.', { filePath });
            alert('Image uploaded successfully.');
        } catch (error) {
            console.error('[character-image] Image upload failed:', error);
            alert('Error uploading image. Please try again or check console for details.');
        }
    };

    const handleExitClick = () => {
        console.log('[character-image] Exit Editor button pressed');
        if (history.length > 1) {
            history.back();
        } else {
            window.location.href = 'dashboard.php';
        }
    };

    // Wire up listeners if elements exist
    if (imageFileInput) {
        imageFileInput.addEventListener('change', handleFileChange);
    }
    if (uploadButton) {
        uploadButton.addEventListener('click', (e) => {
            e.preventDefault();
            void handleUploadClick();
        });
    }
    if (exitEditorButton) {
        exitEditorButton.addEventListener('click', (e) => {
            e.preventDefault();
            handleExitClick();
        });
    }
})();


