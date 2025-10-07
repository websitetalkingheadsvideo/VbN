/**
 * UIManager.js - Centralized DOM manipulation and element caching
 * Manages DOM queries, element caching, and UI updates
 */

class UIManager {
    constructor() {
        this.cache = new Map();
        this.observers = new Map();
        this.animationFrameId = null;
        this.pendingUpdates = new Set();
    }
    
    /**
     * Get element with caching
     */
    getElement(selector, parent = document) {
        const cacheKey = `${parent.constructor.name}-${selector}`;
        
        if (this.cache.has(cacheKey)) {
            const cached = this.cache.get(cacheKey);
            // Verify element still exists in DOM
            if (cached && document.contains(cached)) {
                return cached;
            } else {
                this.cache.delete(cacheKey);
            }
        }
        
        const element = parent.querySelector(selector);
        if (element) {
            this.cache.set(cacheKey, element);
        }
        
        return element;
    }
    
    /**
     * Get multiple elements with caching
     */
    getElements(selector, parent = document) {
        const cacheKey = `${parent.constructor.name}-${selector}-all`;
        
        if (this.cache.has(cacheKey)) {
            const cached = this.cache.get(cacheKey);
            // Verify elements still exist in DOM
            if (cached && cached.length > 0 && document.contains(cached[0])) {
                return cached;
            } else {
                this.cache.delete(cacheKey);
            }
        }
        
        const elements = Array.from(parent.querySelectorAll(selector));
        if (elements.length > 0) {
            this.cache.set(cacheKey, elements);
        }
        
        return elements;
    }
    
    /**
     * Clear cache for specific selector
     */
    clearCache(selector = null) {
        if (selector) {
            for (const key of this.cache.keys()) {
                if (key.includes(selector)) {
                    this.cache.delete(key);
                }
            }
        } else {
            this.cache.clear();
        }
    }
    
    /**
     * Update element content safely
     */
    updateContent(element, content, method = 'innerHTML') {
        if (!element) return false;
        
        try {
            if (method === 'innerHTML') {
                element.innerHTML = content;
            } else if (method === 'textContent') {
                element.textContent = content;
            } else if (method === 'value') {
                element.value = content;
            }
            return true;
        } catch (error) {
            console.error('Error updating element content:', error);
            return false;
        }
    }
    
    /**
     * Update element attributes
     */
    updateAttributes(element, attributes) {
        if (!element) return false;
        
        try {
            Object.entries(attributes).forEach(([key, value]) => {
                if (value === null || value === undefined) {
                    element.removeAttribute(key);
                } else {
                    element.setAttribute(key, value);
                }
            });
            return true;
        } catch (error) {
            console.error('Error updating element attributes:', error);
            return false;
        }
    }
    
    /**
     * Update element classes
     */
    updateClasses(element, classes) {
        if (!element) return false;
        
        try {
            if (typeof classes === 'string') {
                element.className = classes;
            } else if (Array.isArray(classes)) {
                element.className = classes.join(' ');
            } else if (typeof classes === 'object') {
                Object.entries(classes).forEach(([className, shouldAdd]) => {
                    if (shouldAdd) {
                        element.classList.add(className);
                    } else {
                        element.classList.remove(className);
                    }
                });
            }
            return true;
        } catch (error) {
            console.error('Error updating element classes:', error);
            return false;
        }
    }
    
    /**
     * Show/hide element with animation
     */
    toggleElement(element, show, animation = 'fade') {
        if (!element) return false;
        
        try {
            if (show) {
                element.style.display = '';
                element.classList.remove('hidden');
                
                if (animation === 'fade') {
                    element.style.opacity = '0';
                    element.style.transition = 'opacity 0.3s ease';
                    requestAnimationFrame(() => {
                        element.style.opacity = '1';
                    });
                } else if (animation === 'slide') {
                    element.style.maxHeight = '0';
                    element.style.overflow = 'hidden';
                    element.style.transition = 'max-height 0.3s ease';
                    requestAnimationFrame(() => {
                        element.style.maxHeight = element.scrollHeight + 'px';
                    });
                }
            } else {
                if (animation === 'fade') {
                    element.style.transition = 'opacity 0.3s ease';
                    element.style.opacity = '0';
                    setTimeout(() => {
                        element.style.display = 'none';
                    }, 300);
                } else if (animation === 'slide') {
                    element.style.maxHeight = '0';
                    element.style.transition = 'max-height 0.3s ease';
                    setTimeout(() => {
                        element.style.display = 'none';
                    }, 300);
                } else {
                    element.style.display = 'none';
                }
            }
            return true;
        } catch (error) {
            console.error('Error toggling element:', error);
            return false;
        }
    }
    
    /**
     * Create element with attributes and content
     */
    createElement(tag, attributes = {}, content = '') {
        try {
            const element = document.createElement(tag);
            
            // Set attributes
            Object.entries(attributes).forEach(([key, value]) => {
                if (key === 'className' || key === 'class') {
                    element.className = value;
                } else if (key === 'innerHTML') {
                    element.innerHTML = value;
                } else if (key === 'textContent') {
                    element.textContent = value;
                } else {
                    element.setAttribute(key, value);
                }
            });
            
            // Set content
            if (content) {
                if (typeof content === 'string') {
                    element.innerHTML = content;
                } else if (Array.isArray(content)) {
                    content.forEach(child => {
                        if (typeof child === 'string') {
                            element.appendChild(document.createTextNode(child));
                        } else if (child instanceof Node) {
                            element.appendChild(child);
                        }
                    });
                }
            }
            
            return element;
        } catch (error) {
            console.error('Error creating element:', error);
            return null;
        }
    }
    
    /**
     * Batch DOM updates for performance
     */
    batchUpdate(updates) {
        updates.forEach(update => {
            this.pendingUpdates.add(update);
        });
        
        if (this.animationFrameId) {
            cancelAnimationFrame(this.animationFrameId);
        }
        
        this.animationFrameId = requestAnimationFrame(() => {
            this.processPendingUpdates();
        });
    }
    
    /**
     * Process pending updates
     */
    processPendingUpdates() {
        this.pendingUpdates.forEach(update => {
            try {
                update();
            } catch (error) {
                console.error('Error in batch update:', error);
            }
        });
        
        this.pendingUpdates.clear();
        this.animationFrameId = null;
    }
    
    /**
     * Setup intersection observer for lazy loading
     */
    setupIntersectionObserver(callback, options = {}) {
        const defaultOptions = {
            root: null,
            rootMargin: '0px',
            threshold: 0.1
        };
        
        const observer = new IntersectionObserver(callback, { ...defaultOptions, ...options });
        this.observers.set('intersection', observer);
        
        return observer;
    }
    
    /**
     * Setup mutation observer for DOM changes
     */
    setupMutationObserver(callback, options = {}) {
        const defaultOptions = {
            childList: true,
            subtree: true,
            attributes: true,
            attributeOldValue: true
        };
        
        const observer = new MutationObserver(callback);
        this.observers.set('mutation', observer);
        
        return observer;
    }
    
    /**
     * Scroll element into view
     */
    scrollIntoView(element, options = {}) {
        if (!element) return false;
        
        try {
            const defaultOptions = {
                behavior: 'smooth',
                block: 'center',
                inline: 'nearest'
            };
            
            element.scrollIntoView({ ...defaultOptions, ...options });
            return true;
        } catch (error) {
            console.error('Error scrolling element into view:', error);
            return false;
        }
    }
    
    /**
     * Get element position and dimensions
     */
    getElementInfo(element) {
        if (!element) return null;
        
        try {
            const rect = element.getBoundingClientRect();
            const computedStyle = window.getComputedStyle(element);
            
            return {
                rect,
                position: {
                    top: rect.top,
                    left: rect.left,
                    right: rect.right,
                    bottom: rect.bottom
                },
                size: {
                    width: rect.width,
                    height: rect.height
                },
                scroll: {
                    scrollTop: element.scrollTop,
                    scrollLeft: element.scrollLeft
                },
                style: {
                    display: computedStyle.display,
                    visibility: computedStyle.visibility,
                    opacity: computedStyle.opacity
                }
            };
        } catch (error) {
            console.error('Error getting element info:', error);
            return null;
        }
    }
    
    /**
     * Check if element is visible
     */
    isElementVisible(element) {
        if (!element) return false;
        
        try {
            const rect = element.getBoundingClientRect();
            const style = window.getComputedStyle(element);
            
            return (
                rect.width > 0 &&
                rect.height > 0 &&
                style.display !== 'none' &&
                style.visibility !== 'hidden' &&
                style.opacity !== '0'
            );
        } catch (error) {
            console.error('Error checking element visibility:', error);
            return false;
        }
    }
    
    /**
     * Clean up observers and cache
     */
    cleanup() {
        // Clean up observers
        this.observers.forEach(observer => {
            observer.disconnect();
        });
        this.observers.clear();
        
        // Clear cache
        this.cache.clear();
        
        // Cancel pending updates
        if (this.animationFrameId) {
            cancelAnimationFrame(this.animationFrameId);
            this.animationFrameId = null;
        }
        
        this.pendingUpdates.clear();
    }
    
    /**
     * Get cache statistics
     */
    getCacheStats() {
        return {
            size: this.cache.size,
            keys: Array.from(this.cache.keys()),
            observers: this.observers.size,
            pendingUpdates: this.pendingUpdates.size
        };
    }
}

// Export for use in other modules
window.UIManager = UIManager;
