/**
 * ValidationManager.js - Centralized validation and form validation system
 * Provides consistent validation rules and error handling
 */

class ValidationManager {
    constructor(notificationManager) {
        this.notificationManager = notificationManager;
        this.validationRules = new Map();
        this.customValidators = new Map();
        this.validationErrors = new Map();
        
        this.init();
    }
    
    /**
     * Initialize the validation manager
     */
    init() {
        this.setupDefaultRules();
        this.setupCustomValidators();
    }
    
    /**
     * Setup default validation rules
     */
    setupDefaultRules() {
        // Character name validation
        this.addRule('characterName', {
            required: true,
            minLength: 2,
            maxLength: 50,
            pattern: /^[a-zA-Z0-9\s\-_]+$/,
            message: 'Character name must be 2-50 characters and contain only letters, numbers, spaces, hyphens, and underscores'
        });
        
        // Player name validation
        this.addRule('playerName', {
            required: true,
            minLength: 2,
            maxLength: 30,
            pattern: /^[a-zA-Z0-9\s\-_]+$/,
            message: 'Player name must be 2-30 characters and contain only letters, numbers, spaces, hyphens, and underscores'
        });
        
        // Generation validation
        this.addRule('generation', {
            required: true,
            min: 1,
            max: 13,
            type: 'number',
            message: 'Generation must be between 1 and 13'
        });
        
        // Clan validation
        this.addRule('clan', {
            required: true,
            enum: ['Brujah', 'Gangrel', 'Malkavian', 'Nosferatu', 'Toreador', 'Tremere', 'Ventrue', 'Caitiff'],
            message: 'Please select a valid clan'
        });
        
        // Attributes validation
        this.addRule('attributes', {
            required: true,
            custom: 'validateAttributes',
            message: 'All attributes must be between 1 and 5'
        });
        
        // Traits validation
        this.addRule('traits', {
            required: true,
            custom: 'validateTraits',
            message: 'Traits must meet minimum requirements'
        });
        
        // Abilities validation
        this.addRule('abilities', {
            required: true,
            custom: 'validateAbilities',
            message: 'Abilities must meet minimum requirements'
        });
        
        // Disciplines validation
        this.addRule('disciplines', {
            required: true,
            custom: 'validateDisciplines',
            message: 'At least one discipline must be selected'
        });
        
        // Backgrounds validation
        this.addRule('backgrounds', {
            required: true,
            custom: 'validateBackgrounds',
            message: 'Backgrounds must meet minimum requirements'
        });
        
        // Merits and Flaws validation
        this.addRule('meritsFlaws', {
            required: false,
            custom: 'validateMeritsFlaws',
            message: 'Merits and flaws must be valid'
        });
        
        // Virtues validation
        this.addRule('virtues', {
            required: true,
            custom: 'validateVirtues',
            message: 'Virtues must be between 1 and 5'
        });
        
        // Humanity validation
        this.addRule('humanity', {
            required: true,
            min: 0,
            max: 10,
            type: 'number',
            message: 'Humanity must be between 0 and 10'
        });
    }
    
    /**
     * Setup custom validators
     */
    setupCustomValidators() {
        // Attributes validator
        this.addCustomValidator('validateAttributes', (value) => {
            const errors = [];
            
            if (!value || typeof value !== 'object') {
                return ['Attributes must be an object'];
            }
            
            const categories = ['Physical', 'Social', 'Mental'];
            const attributes = ['Strength', 'Dexterity', 'Stamina', 'Charisma', 'Manipulation', 'Appearance', 'Perception', 'Intelligence', 'Wits'];
            
            categories.forEach(category => {
                if (!value[category]) {
                    errors.push(`${category} attributes are required`);
                    return;
                }
                
                const categoryAttrs = value[category];
                const categoryNames = attributes.slice(categories.indexOf(category) * 3, (categories.indexOf(category) + 1) * 3);
                
                categoryNames.forEach(attrName => {
                    if (!categoryAttrs[attrName]) {
                        errors.push(`${attrName} is required`);
                    } else if (categoryAttrs[attrName] < 1 || categoryAttrs[attrName] > 5) {
                        errors.push(`${attrName} must be between 1 and 5`);
                    }
                });
            });
            
            return errors;
        });
        
        // Traits validator
        this.addCustomValidator('validateTraits', (value) => {
            const errors = [];
            
            if (!value || typeof value !== 'object') {
                return ['Traits must be an object'];
            }
            
            const requirements = {
                Physical: { min: 7, max: 10 },
                Social: { min: 5, max: 10 },
                Mental: { min: 3, max: 10 }
            };
            
            Object.keys(requirements).forEach(category => {
                const count = value[category] ? value[category].length : 0;
                const requirement = requirements[category];
                
                if (count < requirement.min) {
                    errors.push(`${category} traits: ${count}/${requirement.min} required`);
                } else if (count > requirement.max) {
                    errors.push(`${category} traits: ${count}/${requirement.max} maximum exceeded`);
                }
            });
            
            return errors;
        });
        
        // Abilities validator
        this.addCustomValidator('validateAbilities', (value) => {
            const errors = [];
            
            if (!value || typeof value !== 'object') {
                return ['Abilities must be an object'];
            }
            
            const requirements = {
                Physical: { min: 3, max: 5 },
                Social: { min: 3, max: 5 },
                Mental: { min: 3, max: 5 },
                Optional: { min: 0, max: 5 }
            };
            
            Object.keys(requirements).forEach(category => {
                const count = value[category] ? value[category].length : 0;
                const requirement = requirements[category];
                
                if (count < requirement.min) {
                    errors.push(`${category} abilities: ${count}/${requirement.min} required`);
                } else if (count > requirement.max) {
                    errors.push(`${category} abilities: ${count}/${requirement.max} maximum exceeded`);
                }
            });
            
            return errors;
        });
        
        // Disciplines validator
        this.addCustomValidator('validateDisciplines', (value) => {
            const errors = [];
            
            if (!Array.isArray(value)) {
                return ['Disciplines must be an array'];
            }
            
            if (value.length < 1) {
                errors.push('At least one discipline must be selected');
            } else if (value.length > 3) {
                errors.push('Maximum 3 disciplines allowed');
            }
            
            return errors;
        });
        
        // Backgrounds validator
        this.addCustomValidator('validateBackgrounds', (value) => {
            const errors = [];
            
            if (!value || typeof value !== 'object') {
                return ['Backgrounds must be an object'];
            }
            
            const totalPoints = Object.values(value).reduce((total, level) => total + level, 0);
            
            if (totalPoints < 1) {
                errors.push('At least 1 background point required');
            } else if (totalPoints > 5) {
                errors.push('Maximum 5 background points allowed');
            }
            
            return errors;
        });
        
        // Merits and Flaws validator
        this.addCustomValidator('validateMeritsFlaws', (value) => {
            const errors = [];
            
            if (!Array.isArray(value)) {
                return ['Merits and flaws must be an array'];
            }
            
            const totalCost = value.reduce((total, item) => {
                const cost = item.variableCost !== null ? item.variableCost : item.cost;
                return total + cost;
            }, 0);
            
            if (totalCost > 10) {
                errors.push(`Total merits/flaws cost (${totalCost}) is too high`);
            }
            
            if (totalCost < -10) {
                errors.push(`Total merits/flaws cost (${totalCost}) is too low`);
            }
            
            return errors;
        });
        
        // Virtues validator
        this.addCustomValidator('validateVirtues', (value) => {
            const errors = [];
            
            if (!value || typeof value !== 'object') {
                return ['Virtues must be an object'];
            }
            
            const virtues = ['Conscience', 'SelfControl'];
            
            virtues.forEach(virtue => {
                if (!value[virtue]) {
                    errors.push(`${virtue} is required`);
                } else if (value[virtue] < 1 || value[virtue] > 5) {
                    errors.push(`${virtue} must be between 1 and 5`);
                }
            });
            
            return errors;
        });
    }
    
    /**
     * Add validation rule
     */
    addRule(field, rule) {
        this.validationRules.set(field, rule);
    }
    
    /**
     * Add custom validator
     */
    addCustomValidator(name, validator) {
        this.customValidators.set(name, validator);
    }
    
    /**
     * Validate single field
     */
    validateField(field, value) {
        const rule = this.validationRules.get(field);
        if (!rule) return { isValid: true, errors: [] };
        
        const errors = [];
        
        // Required validation
        if (rule.required && (value === null || value === undefined || value === '')) {
            errors.push(rule.message || `${field} is required`);
            return { isValid: false, errors };
        }
        
        // Skip other validations if value is empty and not required
        if (!rule.required && (value === null || value === undefined || value === '')) {
            return { isValid: true, errors: [] };
        }
        
        // Type validation
        if (rule.type) {
            if (rule.type === 'number' && typeof value !== 'number') {
                errors.push(`${field} must be a number`);
            } else if (rule.type === 'string' && typeof value !== 'string') {
                errors.push(`${field} must be a string`);
            } else if (rule.type === 'boolean' && typeof value !== 'boolean') {
                errors.push(`${field} must be a boolean`);
            }
        }
        
        // Min/Max validation
        if (rule.min !== undefined && value < rule.min) {
            errors.push(`${field} must be at least ${rule.min}`);
        }
        
        if (rule.max !== undefined && value > rule.max) {
            errors.push(`${field} must be at most ${rule.max}`);
        }
        
        // Length validation
        if (rule.minLength !== undefined && value.length < rule.minLength) {
            errors.push(`${field} must be at least ${rule.minLength} characters`);
        }
        
        if (rule.maxLength !== undefined && value.length > rule.maxLength) {
            errors.push(`${field} must be at most ${rule.maxLength} characters`);
        }
        
        // Pattern validation
        if (rule.pattern && !rule.pattern.test(value)) {
            errors.push(rule.message || `${field} format is invalid`);
        }
        
        // Enum validation
        if (rule.enum && !rule.enum.includes(value)) {
            errors.push(`${field} must be one of: ${rule.enum.join(', ')}`);
        }
        
        // Custom validation
        if (rule.custom) {
            const customValidator = this.customValidators.get(rule.custom);
            if (customValidator) {
                const customErrors = customValidator(value);
                if (customErrors && customErrors.length > 0) {
                    errors.push(...customErrors);
                }
            }
        }
        
        return {
            isValid: errors.length === 0,
            errors
        };
    }
    
    /**
     * Validate multiple fields
     */
    validateFields(data) {
        const results = {};
        let isValid = true;
        const allErrors = [];
        
        Object.keys(data).forEach(field => {
            const result = this.validateField(field, data[field]);
            results[field] = result;
            
            if (!result.isValid) {
                isValid = false;
                allErrors.push(...result.errors);
            }
        });
        
        return {
            isValid,
            results,
            errors: allErrors
        };
    }
    
    /**
     * Validate form
     */
    validateForm(formElement) {
        const formData = new FormData(formElement);
        const data = {};
        
        for (const [key, value] of formData.entries()) {
            data[key] = value;
        }
        
        return this.validateFields(data);
    }
    
    /**
     * Validate character data
     */
    validateCharacter(characterData) {
        const validation = this.validateFields(characterData);
        
        if (!validation.isValid) {
            this.notificationManager.error(`Character validation failed: ${validation.errors.join(', ')}`);
        }
        
        return validation;
    }
    
    /**
     * Clear validation errors
     */
    clearErrors(field = null) {
        if (field) {
            this.validationErrors.delete(field);
        } else {
            this.validationErrors.clear();
        }
    }
    
    /**
     * Get validation errors
     */
    getErrors(field = null) {
        if (field) {
            return this.validationErrors.get(field) || [];
        }
        
        const allErrors = [];
        this.validationErrors.forEach(errors => {
            allErrors.push(...errors);
        });
        
        return allErrors;
    }
    
    /**
     * Set validation errors
     */
    setErrors(field, errors) {
        this.validationErrors.set(field, errors);
    }
    
    /**
     * Show validation errors
     */
    showErrors(errors) {
        if (errors.length > 0) {
            this.notificationManager.error(`Validation errors: ${errors.join(', ')}`);
        }
    }
    
    /**
     * Validate and show errors
     */
    validateAndShow(data) {
        const validation = this.validateFields(data);
        
        if (!validation.isValid) {
            this.showErrors(validation.errors);
        }
        
        return validation;
    }
    
    /**
     * Get validation statistics
     */
    getStats() {
        return {
            totalRules: this.validationRules.size,
            customValidators: this.customValidators.size,
            validationErrors: this.validationErrors.size
        };
    }
    
    /**
     * Get validation rules
     */
    getRules() {
        return Array.from(this.validationRules.entries());
    }
    
    /**
     * Get custom validators
     */
    getCustomValidators() {
        return Array.from(this.customValidators.keys());
    }
}

// Export for use in other modules
window.ValidationManager = ValidationManager;
