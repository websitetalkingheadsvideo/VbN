/**
 * StateManager.js - Centralized state management for the character creation system
 * Manages all application state and provides reactive updates
 */

class StateManager {
    constructor() {
        this.state = {
            // Basic character information
            characterName: '',
            playerName: '',
            chronicle: '',
            concept: '',
            clan: '',
            generation: 13,
            
            // Attributes
            attributes: {
                Physical: { Strength: 1, Dexterity: 1, Stamina: 1 },
                Social: { Charisma: 1, Manipulation: 1, Appearance: 1 },
                Mental: { Perception: 1, Intelligence: 1, Wits: 1 }
            },
            
            // Traits
            traits: {
                Physical: [],
                Social: [],
                Mental: []
            },
            
            // Negative traits
            negativeTraits: {
                Physical: [],
                Social: [],
                Mental: []
            },
            
            // Abilities
            abilities: {
                Physical: [],
                Social: [],
                Mental: [],
                Optional: []
            },
            
            // Disciplines
            disciplines: [],
            disciplinePowers: {},
            
            // Backgrounds
            backgrounds: {},
            
            // Merits and Flaws
            selectedMeritsFlaws: [],
            
            // Virtues and Humanity
            virtues: {
                Conscience: 1,
                SelfControl: 1
            },
            humanity: 7,
            
            // Health and Willpower
            health: 0,
            willpower: 0,
            
            // Starting Cash
            startingCash: 0,
            
            // UI State
            currentTab: 'basic',
            sheetMode: 'creation',
            isDirty: false,
            lastSaved: null
        };
        
        this.listeners = new Map();
        this.history = [];
        this.historyIndex = -1;
        this.maxHistorySize = 50;
        this.autoSaveTimeout = null;
        
        this.init();
    }
    
    /**
     * Initialize the state manager
     */
    init() {
        // Load state from localStorage if available
        this.loadState();
        
        // Set up auto-save
        this.setupAutoSave();
        
        // Set up history tracking
        this.setupHistoryTracking();
    }
    
    /**
     * Get current state
     */
    getState() {
        return { ...this.state };
    }
    
    /**
     * Get specific state property
     */
    getStateProperty(path) {
        return this.getNestedProperty(this.state, path);
    }
    
    /**
     * Set state with optional callback
     */
    setState(newState, callback) {
        const oldState = { ...this.state };
        
        // Merge new state with existing state
        this.state = this.deepMerge(this.state, newState);
        
        // Add to history
        this.addToHistory(oldState, this.state);
        
        // Mark as dirty
        this.state.isDirty = true;
        
        // Notify listeners
        this.notifyListeners(newState, oldState);
        
        // Execute callback if provided
        if (callback && typeof callback === 'function') {
            callback(this.state, oldState);
        }
        
        // Auto-save (debounced)
        if (this.state.isDirty && !this.autoSaveTimeout) {
            this.autoSaveTimeout = setTimeout(() => {
                this.saveState();
                this.autoSaveTimeout = null;
            }, 1000); // 1 second delay
        }
    }
    
    /**
     * Set specific state property
     */
    setStateProperty(path, value, callback) {
        const oldState = { ...this.state };
        
        // Set nested property
        this.setNestedProperty(this.state, path, value);
        
        // Add to history
        this.addToHistory(oldState, this.state);
        
        // Mark as dirty
        this.state.isDirty = true;
        
        // Notify listeners
        this.notifyListeners({ [path]: value }, oldState);
        
        // Execute callback if provided
        if (callback && typeof callback === 'function') {
            callback(this.state, oldState);
        }
        
        // Auto-save (debounced)
        if (this.state.isDirty && !this.autoSaveTimeout) {
            this.autoSaveTimeout = setTimeout(() => {
                this.saveState();
                this.autoSaveTimeout = null;
            }, 1000); // 1 second delay
        }
    }
    
    /**
     * Subscribe to state changes
     */
    subscribe(path, callback) {
        if (!this.listeners.has(path)) {
            this.listeners.set(path, new Set());
        }
        
        this.listeners.get(path).add(callback);
        
        // Return unsubscribe function
        return () => {
            const listeners = this.listeners.get(path);
            if (listeners) {
                listeners.delete(callback);
                if (listers.size === 0) {
                    this.listeners.delete(path);
                }
            }
        };
    }
    
    /**
     * Unsubscribe from state changes
     */
    unsubscribe(path, callback) {
        const listeners = this.listeners.get(path);
        if (listeners) {
            listeners.delete(callback);
            if (listeners.size === 0) {
                this.listeners.delete(path);
            }
        }
    }
    
    /**
     * Notify listeners of state changes
     */
    notifyListeners(newState, oldState) {
        // Notify specific path listeners
        Object.keys(newState).forEach(path => {
            const listeners = this.listeners.get(path);
            if (listeners) {
                listeners.forEach(callback => {
                    try {
                        callback(newState[path], oldState[path], this.state);
                    } catch (error) {
                        console.error(`Error in state listener for ${path}:`, error);
                    }
                });
            }
        });
        
        // Notify global listeners
        const globalListeners = this.listeners.get('*');
        if (globalListeners) {
            globalListeners.forEach(callback => {
                try {
                    callback(newState, oldState, this.state);
                } catch (error) {
                    console.error('Error in global state listener:', error);
                }
            });
        }
    }
    
    /**
     * Add state to history
     */
    addToHistory(oldState, newState) {
        // Remove any history after current index
        this.history = this.history.slice(0, this.historyIndex + 1);
        
        // Add new state to history
        this.history.push({
            state: { ...newState },
            timestamp: Date.now()
        });
        
        // Update history index
        this.historyIndex = this.history.length - 1;
        
        // Limit history size
        if (this.history.length > this.maxHistorySize) {
            this.history.shift();
            this.historyIndex--;
        }
    }
    
    /**
     * Undo last state change
     */
    undo() {
        if (this.historyIndex > 0) {
            this.historyIndex--;
            const previousState = this.history[this.historyIndex];
            this.state = { ...previousState.state };
            this.notifyListeners(this.state, {});
            return true;
        }
        return false;
    }
    
    /**
     * Redo last undone state change
     */
    redo() {
        if (this.historyIndex < this.history.length - 1) {
            this.historyIndex++;
            const nextState = this.history[this.historyIndex];
            this.state = { ...nextState.state };
            this.notifyListeners(this.state, {});
            return true;
        }
        return false;
    }
    
    /**
     * Reset state to initial values
     */
    reset() {
        const oldState = { ...this.state };
        
        // Reset to initial state
        this.state = {
            characterName: '',
            playerName: '',
            chronicle: '',
            concept: '',
            clan: '',
            generation: 13,
            attributes: {
                Physical: { Strength: 1, Dexterity: 1, Stamina: 1 },
                Social: { Charisma: 1, Manipulation: 1, Appearance: 1 },
                Mental: { Perception: 1, Intelligence: 1, Wits: 1 }
            },
            traits: { Physical: [], Social: [], Mental: [] },
            negativeTraits: { Physical: [], Social: [], Mental: [] },
            abilities: { Physical: [], Social: [], Mental: [], Optional: [] },
            disciplines: [],
            disciplinePowers: {},
            backgrounds: {},
            selectedMeritsFlaws: [],
            virtues: { Conscience: 1, SelfControl: 1 },
            humanity: 7,
            health: 0,
            willpower: 0,
            startingCash: 0,
            currentTab: 'basic',
            sheetMode: 'creation',
            isDirty: false,
            lastSaved: null
        };
        
        // Clear history
        this.history = [];
        this.historyIndex = -1;
        
        // Notify listeners
        this.notifyListeners(this.state, oldState);
        
        // Clear localStorage
        this.clearState();
    }
    
    /**
     * Save state to localStorage
     */
    saveState() {
        try {
            const stateToSave = {
                ...this.state,
                lastSaved: Date.now()
            };
            
            localStorage.setItem('lotn_character_state', JSON.stringify(stateToSave));
            this.state.isDirty = false;
            this.state.lastSaved = Date.now();
            
            return true;
        } catch (error) {
            console.error('Error saving state:', error);
            return false;
        }
    }
    
    /**
     * Load state from localStorage
     */
    loadState() {
        try {
            const savedState = localStorage.getItem('lotn_character_state');
            if (savedState) {
                const parsedState = JSON.parse(savedState);
                this.state = this.deepMerge(this.state, parsedState);
                this.state.isDirty = false;
                return true;
            }
        } catch (error) {
            console.error('Error loading state:', error);
        }
        return false;
    }
    
    /**
     * Clear state from localStorage
     */
    clearState() {
        try {
            localStorage.removeItem('lotn_character_state');
            return true;
        } catch (error) {
            console.error('Error clearing state:', error);
            return false;
        }
    }
    
    /**
     * Setup auto-save functionality
     */
    setupAutoSave() {
        // Auto-save every 30 seconds
        setInterval(() => {
            if (this.state.isDirty) {
                this.saveState();
            }
        }, 30000);
        
        // Auto-save on page unload
        window.addEventListener('beforeunload', () => {
            if (this.state.isDirty) {
                this.saveState();
            }
        });
    }
    
    /**
     * Setup history tracking
     */
    setupHistoryTracking() {
        // Track undo/redo keyboard shortcuts
        document.addEventListener('keydown', (e) => {
            if (e.ctrlKey || e.metaKey) {
                if (e.key === 'z' && !e.shiftKey) {
                    e.preventDefault();
                    this.undo();
                } else if (e.key === 'z' && e.shiftKey) {
                    e.preventDefault();
                    this.redo();
                }
            }
        });
    }
    
    /**
     * Deep merge two objects
     */
    deepMerge(target, source) {
        const result = { ...target };
        
        for (const key in source) {
            if (source[key] && typeof source[key] === 'object' && !Array.isArray(source[key])) {
                result[key] = this.deepMerge(target[key] || {}, source[key]);
            } else {
                result[key] = source[key];
            }
        }
        
        return result;
    }
    
    /**
     * Get nested property value
     */
    getNestedProperty(obj, path) {
        return path.split('.').reduce((current, key) => {
            return current && current[key] !== undefined ? current[key] : undefined;
        }, obj);
    }
    
    /**
     * Set nested property value
     */
    setNestedProperty(obj, path, value) {
        const keys = path.split('.');
        const lastKey = keys.pop();
        const target = keys.reduce((current, key) => {
            if (!current[key] || typeof current[key] !== 'object') {
                current[key] = {};
            }
            return current[key];
        }, obj);
        
        target[lastKey] = value;
    }
    
    /**
     * Get state statistics
     */
    getStateStats() {
        return {
            isDirty: this.state.isDirty,
            lastSaved: this.state.lastSaved,
            historySize: this.history.length,
            historyIndex: this.historyIndex,
            canUndo: this.historyIndex > 0,
            canRedo: this.historyIndex < this.history.length - 1,
            listenersCount: this.listeners.size
        };
    }
    
    /**
     * Export state as JSON
     */
    exportState() {
        return JSON.stringify(this.state, null, 2);
    }
    
    /**
     * Import state from JSON
     */
    importState(jsonString) {
        try {
            const importedState = JSON.parse(jsonString);
            const oldState = { ...this.state };
            
            this.state = this.deepMerge(this.state, importedState);
            this.state.isDirty = true;
            
            this.notifyListeners(this.state, oldState);
            
            return true;
        } catch (error) {
            console.error('Error importing state:', error);
            return false;
        }
    }
}

// Export for use in other modules
window.StateManager = StateManager;