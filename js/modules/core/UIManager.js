/**
 * UIManager.js - Centralized UI management and DOM utilities
 * Provides consistent DOM manipulation and UI state management
 */

class UIManager {
    constructor() {
        this.cache = new Map();
        this.observers = new Map();
        this.animations = new Map();
        
        this.init();
    }
    
    /**
     * Initialize the UI manager
     */
    init() {
        this.setupResizeObserver();
        this.setupIntersectionObserver();
        this.setupAnimationObserver();
    }
    
    /**
     * Get element by selector with caching
     */
    getElement(selector, useCache = true) {
        if (useCache && this.cache.has(selector)) {
            const cached = this.cache.get(selector);
            if (document.contains(cached)) {
                return cached;
            } else {
                this.cache.delete(selector);
            }
        }
        
        const element = document.querySelector(selector);
        if (element && useCache) {
            this.cache.set(selector, element);
        }
        
        return element;
    }
    
    /**
     * Get multiple elements by selector
     */
    getElements(selector) {
        return Array.from(document.querySelectorAll(selector));
    }
    
    /**
     * Create element with attributes and content
     */
    createElement(tagName, attributes = {}, content = '') {
        const element = document.createElement(tagName);
        
        // Set attributes
        Object.keys(attributes).forEach(key => {
            if (key === 'className') {
                element.className = attributes[key];
            } else if (key === 'innerHTML') {
                element.innerHTML = attributes[key];
            } else if (key === 'textContent') {
                element.textContent = attributes[key];
            } else {
                element.setAttribute(key, attributes[key]);
            }
        });
        
        // Set content
        if (content) {
            element.innerHTML = content;
        }
        
        return element;
    }
    
    /**
     * Update element content
     */
    updateContent(element, content) {
        if (typeof element === 'string') {
            element = this.getElement(element);
        }
        
        if (element) {
            element.innerHTML = content;
        }
    }
    
    /**
     * Update element text content
     */
    updateText(element, text) {
        if (typeof element === 'string') {
            element = this.getElement(element);
        }
        
        if (element) {
            element.textContent = text;
        }
    }
    
    /**
     * Update element classes
     */
    updateClasses(element, classes) {
        if (typeof element === 'string') {
            element = this.getElement(element);
        }
        
        if (element) {
            Object.keys(classes).forEach(className => {
                if (classes[className]) {
                    element.classList.add(className);
                } else {
                    element.classList.remove(className);
                }
            });
        }
    }
    
    /**
     * Update element attributes
     */
    updateAttributes(element, attributes) {
        if (typeof element === 'string') {
            element = this.getElement(element);
        }
        
        if (element) {
            Object.keys(attributes).forEach(key => {
                element.setAttribute(key, attributes[key]);
            });
        }
    }
    
    /**
     * Show element with animation
     */
    show(element, animation = 'fadeIn') {
        if (typeof element === 'string') {
            element = this.getElement(element);
        }
        
        if (element) {
            // Remove any existing animation classes
            element.classList.remove('fadeIn', 'fadeOut');
            element.style.display = 'block';
            // Temporarily disable animations to test
            // this.animate(element, animation);
        } else {
        }
    }
    
    /**
     * Hide element with animation
     */
    hide(element, animation = 'fadeOut') {
        if (typeof element === 'string') {
            element = this.getElement(element);
        }
        
        if (element) {
            // Remove any existing animation classes
            element.classList.remove('fadeIn', 'fadeOut');
            // Temporarily disable animations to test
            element.style.display = 'none';
            // this.animate(element, animation, () => {
            //     element.style.display = 'none';
            // });
        } else {
        }
    }
    
    /**
     * Toggle element visibility
     */
    toggle(element, animation = 'fadeIn') {
        if (typeof element === 'string') {
            element = this.getElement(element);
        }
        
        if (element) {
            if (element.style.display === 'none' || !element.style.display) {
                this.show(element, animation);
            } else {
                this.hide(element, animation);
            }
        }
    }
    
    /**
     * Animate element
     */
    animate(element, animation, callback) {
        if (typeof element === 'string') {
            element = this.getElement(element);
        }
        
        if (!element) return;
        
        // Remove existing animation classes
        element.classList.remove('fadeIn', 'fadeOut', 'slideIn', 'slideOut', 'bounce', 'shake');
        
        // Add animation class
        element.classList.add(animation);
        
        // Set up animation end listener
        const handleAnimationEnd = () => {
            element.classList.remove(animation);
            element.removeEventListener('animationend', handleAnimationEnd);
            
            if (callback && typeof callback === 'function') {
                callback();
            }
        };
        
        element.addEventListener('animationend', handleAnimationEnd);
        
        // Store animation reference
        this.animations.set(element, {
            animation,
            callback,
            startTime: Date.now()
        });
    }
    
    /**
     * Scroll element into view
     */
    scrollIntoView(element, options = {}) {
        if (typeof element === 'string') {
            element = this.getElement(element);
        }
        
        if (element) {
            element.scrollIntoView({
                behavior: 'smooth',
                block: 'center',
                inline: 'nearest',
                ...options
            });
        }
    }
    
    /**
     * Focus element
     */
    focus(element) {
        if (typeof element === 'string') {
            element = this.getElement(element);
        }
        
        if (element && element.focus) {
            element.focus();
        }
    }
    
    /**
     * Blur element
     */
    blur(element) {
        if (typeof element === 'string') {
            element = this.getElement(element);
        }
        
        if (element && element.blur) {
            element.blur();
        }
    }
    
    /**
     * Get element position
     */
    getPosition(element) {
        if (typeof element === 'string') {
            element = this.getElement(element);
        }
        
        if (element) {
            const rect = element.getBoundingClientRect();
            return {
                top: rect.top,
                left: rect.left,
                right: rect.right,
                bottom: rect.bottom,
                width: rect.width,
                height: rect.height
            };
        }
        
        return null;
    }
    
    /**
     * Check if element is visible
     */
    isVisible(element) {
        if (typeof element === 'string') {
            element = this.getElement(element);
        }
        
        if (!element) return false;
        
        const rect = element.getBoundingClientRect();
        return (
            rect.top >= 0 &&
            rect.left >= 0 &&
            rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
            rect.right <= (window.innerWidth || document.documentElement.clientWidth)
        );
    }
    
    /**
     * Check if element is in viewport
     */
    isInViewport(element) {
        if (typeof element === 'string') {
            element = this.getElement(element);
        }
        
        if (!element) return false;
        
        const rect = element.getBoundingClientRect();
        return (
            rect.top >= 0 &&
            rect.left >= 0 &&
            rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
            rect.right <= (window.innerWidth || document.documentElement.clientWidth)
        );
    }
    
    /**
     * Setup resize observer
     */
    setupResizeObserver() {
        if (window.ResizeObserver) {
            this.resizeObserver = new ResizeObserver(entries => {
                entries.forEach(entry => {
                    const element = entry.target;
                    const event = new CustomEvent('resize', {
                        detail: {
                            width: entry.contentRect.width,
                            height: entry.contentRect.height
                        }
                    });
                    element.dispatchEvent(event);
                });
            });
        }
    }
    
    /**
     * Setup intersection observer
     */
    setupIntersectionObserver() {
        if (window.IntersectionObserver) {
            this.intersectionObserver = new IntersectionObserver(entries => {
                entries.forEach(entry => {
                    const element = entry.target;
                    const event = new CustomEvent('intersection', {
                        detail: {
                            isIntersecting: entry.isIntersecting,
                            intersectionRatio: entry.intersectionRatio
                        }
                    });
                    element.dispatchEvent(event);
                });
            });
        }
    }
    
    /**
     * Setup animation observer
     */
    setupAnimationObserver() {
        if (window.AnimationObserver) {
            this.animationObserver = new AnimationObserver(entries => {
                entries.forEach(entry => {
                    const element = entry.target;
                    const animation = this.animations.get(element);
                    
                    if (animation && animation.callback) {
                        animation.callback();
                        this.animations.delete(element);
                    }
                });
            });
        }
    }
    
    /**
     * Observe element for resize
     */
    observeResize(element, callback) {
        if (this.resizeObserver) {
            this.resizeObserver.observe(element);
            
            const handleResize = (e) => {
                if (callback) {
                    callback(e.detail);
                }
            };
            
            element.addEventListener('resize', handleResize);
            
            return () => {
                this.resizeObserver.unobserve(element);
                element.removeEventListener('resize', handleResize);
            };
        }
        
        return () => {};
    }
    
    /**
     * Observe element for intersection
     */
    observeIntersection(element, callback, options = {}) {
        if (this.intersectionObserver) {
            this.intersectionObserver.observe(element);
            
            const handleIntersection = (e) => {
                if (callback) {
                    callback(e.detail);
                }
            };
            
            element.addEventListener('intersection', handleIntersection);
            
            return () => {
                this.intersectionObserver.unobserve(element);
                element.removeEventListener('intersection', handleIntersection);
            };
        }
        
        return () => {};
    }
    
    /**
     * Clear cache
     */
    clearCache() {
        this.cache.clear();
    }
    
    /**
     * Clear specific cache entry
     */
    clearCacheEntry(selector) {
        this.cache.delete(selector);
    }
    
    /**
     * Get cache statistics
     */
    getCacheStats() {
        return {
            size: this.cache.size,
            entries: Array.from(this.cache.keys())
        };
    }
    
    /**
     * Create tooltip
     */
    createTooltip(element, content, options = {}) {
        if (typeof element === 'string') {
            element = this.getElement(element);
        }
        
        if (!element) return null;
        
        const tooltip = this.createElement('div', {
            className: 'tooltip',
            textContent: content
        });
        
        // Position tooltip
        const position = this.getPosition(element);
        if (position) {
            tooltip.style.position = 'absolute';
            tooltip.style.top = (position.top - 30) + 'px';
            tooltip.style.left = position.left + 'px';
            tooltip.style.zIndex = '1000';
        }
        
        // Add to DOM
        document.body.appendChild(tooltip);
        
        // Show tooltip
        this.show(tooltip);
        
        // Auto-hide after delay
        setTimeout(() => {
            this.hide(tooltip, 'fadeOut');
            setTimeout(() => {
                if (tooltip.parentNode) {
                    tooltip.parentNode.removeChild(tooltip);
                }
            }, 300);
        }, options.delay || 3000);
        
        return tooltip;
    }
    
    /**
     * Create modal
     */
    createModal(content, options = {}) {
        const modal = this.createElement('div', {
            className: 'modal-overlay'
        });
        
        const modalContent = this.createElement('div', {
            className: 'modal-content',
            innerHTML: content
        });
        
        modal.appendChild(modalContent);
        
        // Add close button
        const closeButton = this.createElement('button', {
            className: 'modal-close',
            textContent: '×'
        });
        
        closeButton.addEventListener('click', () => {
            this.hide(modal, 'fadeOut');
            setTimeout(() => {
                if (modal.parentNode) {
                    modal.parentNode.removeChild(modal);
                }
            }, 300);
        });
        
        modalContent.appendChild(closeButton);
        
        // Add to DOM
        document.body.appendChild(modal);
        
        // Show modal
        this.show(modal, 'fadeIn');
        
        return modal;
    }
    
    /**
     * Create notification
     */
    createNotification(message, type = 'info', duration = 3000) {
        const notification = this.createElement('div', {
            className: `notification notification-${type}`,
            textContent: message
        });
        
        // Position notification
        notification.style.position = 'fixed';
        notification.style.top = '20px';
        notification.style.right = '20px';
        notification.style.zIndex = '1000';
        
        // Add to DOM
        document.body.appendChild(notification);
        
        // Show notification
        this.show(notification, 'slideIn');
        
        // Auto-hide
        setTimeout(() => {
            this.hide(notification, 'slideOut');
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 300);
        }, duration);
        
        return notification;
    }
    
    /**
     * Get UI statistics
     */
    getUIStats() {
        return {
            cacheSize: this.cache.size,
            observersCount: this.observers.size,
            animationsCount: this.animations.size,
            hasResizeObserver: !!this.resizeObserver,
            hasIntersectionObserver: !!this.intersectionObserver,
            hasAnimationObserver: !!this.animationObserver
        };
    }
}

// Export for use in other modules
window.UIManager = UIManager;
