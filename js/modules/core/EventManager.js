/**
 * EventManager.js - Centralized event management and delegation
 * Provides efficient event handling and custom event system
 */

class EventManager {
    constructor() {
        this.listeners = new Map();
        this.delegatedListeners = new Map();
        this.customEvents = new Map();
        this.eventQueue = [];
        this.isProcessing = false;
        
        this.init();
    }
    
    /**
     * Initialize the event manager
     */
    init() {
        this.setupEventQueue();
        this.setupGlobalListeners();
    }
    
    /**
     * Add event listener
     */
    addListener(element, event, handler, options = {}) {
        if (typeof element === 'string') {
            element = document.querySelector(element);
        }
        
        if (!element) return null;
        
        const listenerId = this.generateListenerId();
        const wrappedHandler = this.wrapHandler(handler, listenerId);
        
        // Store listener info
        this.listeners.set(listenerId, {
            element,
            event,
            handler: wrappedHandler,
            originalHandler: handler,
            options,
            active: true
        });
        
        // Add actual event listener
        element.addEventListener(event, wrappedHandler, options);
        
        return listenerId;
    }
    
    /**
     * Add delegated event listener
     */
    addDelegatedListener(container, selector, event, handler, options = {}) {
        if (typeof container === 'string') {
            container = document.querySelector(container);
        }
        
        if (!container) return null;
        
        const listenerId = this.generateListenerId();
        const wrappedHandler = this.wrapDelegatedHandler(selector, handler, listenerId);
        
        // Store delegated listener info
        this.delegatedListeners.set(listenerId, {
            container,
            selector,
            event,
            handler: wrappedHandler,
            originalHandler: handler,
            options,
            active: true
        });
        
        // Add actual event listener
        container.addEventListener(event, wrappedHandler, options);
        
        return listenerId;
    }
    
    /**
     * Remove event listener
     */
    removeListener(listenerId) {
        const listener = this.listeners.get(listenerId);
        if (listener) {
            listener.element.removeEventListener(listener.event, listener.handler, listener.options);
            this.listeners.delete(listenerId);
            return true;
        }
        
        const delegatedListener = this.delegatedListeners.get(listenerId);
        if (delegatedListener) {
            delegatedListener.container.removeEventListener(delegatedListener.event, delegatedListener.handler, delegatedListener.options);
            this.delegatedListeners.delete(listenerId);
            return true;
        }
        
        return false;
    }
    
    /**
     * Remove all listeners for an element
     */
    removeAllListeners(element) {
        if (typeof element === 'string') {
            element = document.querySelector(element);
        }
        
        if (!element) return 0;
        
        let removedCount = 0;
        
        // Remove direct listeners
        this.listeners.forEach((listener, id) => {
            if (listener.element === element) {
                this.removeListener(id);
                removedCount++;
            }
        });
        
        // Remove delegated listeners
        this.delegatedListeners.forEach((listener, id) => {
            if (listener.container === element) {
                this.removeListener(id);
                removedCount++;
            }
        });
        
        return removedCount;
    }
    
    /**
     * Dispatch custom event
     */
    dispatchCustomEvent(eventName, detail = {}, target = document) {
        const event = new CustomEvent(eventName, {
            detail,
            bubbles: true,
            cancelable: true
        });
        
        target.dispatchEvent(event);
        
        // Store custom event
        this.customEvents.set(eventName, {
            detail,
            target,
            timestamp: Date.now()
        });
        
        return event;
    }
    
    /**
     * Listen for custom event
     */
    onCustomEvent(eventName, handler, target = document) {
        const listenerId = this.generateListenerId();
        const wrappedHandler = this.wrapHandler(handler, listenerId);
        
        // Store listener info
        this.listeners.set(listenerId, {
            element: target,
            event: eventName,
            handler: wrappedHandler,
            originalHandler: handler,
            options: {},
            active: true,
            isCustom: true
        });
        
        // Add actual event listener
        target.addEventListener(eventName, wrappedHandler);
        
        return listenerId;
    }
    
    /**
     * Emit event to specific target
     */
    emit(target, eventName, detail = {}) {
        if (typeof target === 'string') {
            target = document.querySelector(target);
        }
        
        if (!target) return null;
        
        return this.dispatchCustomEvent(eventName, detail, target);
    }
    
    /**
     * Emit global event
     */
    emitGlobal(eventName, detail = {}) {
        return this.dispatchCustomEvent(eventName, detail, document);
    }
    
    /**
     * Wrap handler with error handling and logging
     */
    wrapHandler(handler, listenerId) {
        return (event) => {
            try {
                // Add listener ID to event
                event.listenerId = listenerId;
                
                // Call original handler
                handler(event);
                
                // Log successful execution
                this.logEvent(event, 'success');
                
            } catch (error) {
                console.error(`Error in event handler ${listenerId}:`, error);
                this.logEvent(event, 'error', error);
            }
        };
    }
    
    /**
     * Wrap delegated handler
     */
    wrapDelegatedHandler(selector, handler, listenerId) {
        return (event) => {
            try {
                // Find the actual element that matches the selector
                let target = event.target;
                
                // If target is not an Element (e.g., Text node), find the closest Element
                if (!target.matches || typeof target.matches !== 'function') {
                    target = target.closest ? target.closest(selector) : null;
                    if (!target) return;
                } else {
                    // Check if target matches selector
                    if (!target.matches(selector)) return;
                }
                
                // Add listener ID to event
                event.listenerId = listenerId;
                
                // Call original handler
                handler(event);
                
                // Log successful execution
                this.logEvent(event, 'success');
                
            } catch (error) {
                console.error(`Error in delegated event handler ${listenerId}:`, error);
                this.logEvent(event, 'error', error);
            }
        };
    }
    
    /**
     * Generate unique listener ID
     */
    generateListenerId() {
        return `listener_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;
    }
    
    /**
     * Setup event queue for batch processing
     */
    setupEventQueue() {
        // Process event queue every 16ms (60fps)
        setInterval(() => {
            if (this.eventQueue.length > 0 && !this.isProcessing) {
                this.processEventQueue();
            }
        }, 16);
    }
    
    /**
     * Process event queue
     */
    processEventQueue() {
        this.isProcessing = true;
        
        while (this.eventQueue.length > 0) {
            const eventData = this.eventQueue.shift();
            this.processEvent(eventData);
        }
        
        this.isProcessing = false;
    }
    
    /**
     * Process individual event
     */
    processEvent(eventData) {
        const { event, handler, context } = eventData;
        
        try {
            handler.call(context, event);
        } catch (error) {
            console.error('Error processing queued event:', error);
        }
    }
    
    /**
     * Queue event for batch processing
     */
    queueEvent(event, handler, context = null) {
        this.eventQueue.push({ event, handler, context });
    }
    
    /**
     * Setup global listeners
     */
    setupGlobalListeners() {
        // Global error handler
        window.addEventListener('error', (event) => {
            console.error('Global error:', event.error);
            this.logEvent(event, 'global_error', event.error);
        });
        
        // Global unhandled rejection handler
        window.addEventListener('unhandledrejection', (event) => {
            console.error('Unhandled promise rejection:', event.reason);
            this.logEvent(event, 'unhandled_rejection', event.reason);
        });
        
        // Global resize handler
        window.addEventListener('resize', (event) => {
            this.emitGlobal('windowResize', { width: window.innerWidth, height: window.innerHeight });
        });
        
        // Global scroll handler
        window.addEventListener('scroll', (event) => {
            this.emitGlobal('windowScroll', { scrollX: window.scrollX, scrollY: window.scrollY });
        });
    }
    
    /**
     * Log event execution
     */
    logEvent(event, status, error = null) {
        if (typeof process !== 'undefined' && process.env && process.env.NODE_ENV === 'development') {
            console.log(`Event ${event.type} ${status}:`, {
                listenerId: event.listenerId,
                target: event.target,
                status,
                error
            });
        }
    }
    
    /**
     * Debounce function
     */
    debounce(func, wait, immediate = false) {
        let timeout;
        
        return function executedFunction(...args) {
            const later = () => {
                timeout = null;
                if (!immediate) func.apply(this, args);
            };
            
            const callNow = immediate && !timeout;
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
            
            if (callNow) func.apply(this, args);
        };
    }
    
    /**
     * Throttle function
     */
    throttle(func, limit) {
        let inThrottle;
        
        return function executedFunction(...args) {
            if (!inThrottle) {
                func.apply(this, args);
                inThrottle = true;
                setTimeout(() => inThrottle = false, limit);
            }
        };
    }
    
    /**
     * Create debounced event listener
     */
    addDebouncedListener(element, event, handler, delay = 300) {
        const debouncedHandler = this.debounce(handler, delay);
        return this.addListener(element, event, debouncedHandler);
    }
    
    /**
     * Create throttled event listener
     */
    addThrottledListener(element, event, handler, limit = 100) {
        const throttledHandler = this.throttle(handler, limit);
        return this.addListener(element, event, throttledHandler);
    }
    
    /**
     * Pause all listeners
     */
    pauseAllListeners() {
        this.listeners.forEach(listener => {
            listener.active = false;
        });
        
        this.delegatedListeners.forEach(listener => {
            listener.active = false;
        });
    }
    
    /**
     * Resume all listeners
     */
    resumeAllListeners() {
        this.listeners.forEach(listener => {
            listener.active = true;
        });
        
        this.delegatedListeners.forEach(listener => {
            listener.active = true;
        });
    }
    
    /**
     * Get event statistics
     */
    getEventStats() {
        return {
            totalListeners: this.listeners.size,
            delegatedListeners: this.delegatedListeners.size,
            customEvents: this.customEvents.size,
            queuedEvents: this.eventQueue.length,
            isProcessing: this.isProcessing
        };
    }
    
    /**
     * Get listeners for element
     */
    getListenersForElement(element) {
        const listeners = [];
        
        this.listeners.forEach((listener, id) => {
            if (listener.element === element) {
                listeners.push({ id, ...listener });
            }
        });
        
        this.delegatedListeners.forEach((listener, id) => {
            if (listener.container === element) {
                listeners.push({ id, ...listener, isDelegated: true });
            }
        });
        
        return listeners;
    }
    
    /**
     * Clear all listeners
     */
    clearAllListeners() {
        // Remove all direct listeners
        this.listeners.forEach((listener, id) => {
            this.removeListener(id);
        });
        
        // Remove all delegated listeners
        this.delegatedListeners.forEach((listener, id) => {
            this.removeListener(id);
        });
        
        // Clear custom events
        this.customEvents.clear();
        
        // Clear event queue
        this.eventQueue = [];
    }
    
    /**
     * Get custom event history
     */
    getCustomEventHistory(eventName = null) {
        if (eventName) {
            return this.customEvents.get(eventName);
        }
        
        return Array.from(this.customEvents.entries());
    }
}

// Export for use in other modules
window.EventManager = EventManager;