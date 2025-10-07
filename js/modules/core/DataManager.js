/**
 * DataManager.js - Centralized data management and API communication
 * Handles data fetching, caching, and persistence
 */

class DataManager {
    constructor() {
        this.cache = new Map();
        this.pendingRequests = new Map();
        this.retryAttempts = new Map();
        this.maxRetries = 3;
        this.cacheTimeout = 5 * 60 * 1000; // 5 minutes
        
        this.init();
    }
    
    /**
     * Initialize the data manager
     */
    init() {
        this.setupCacheCleanup();
        this.setupRequestInterceptors();
    }
    
    /**
     * Fetch data from API with caching and error handling
     */
    async fetchData(url, options = {}) {
        const cacheKey = this.generateCacheKey(url, options);
        
        // Check cache first
        if (options.useCache !== false) {
            const cached = this.getFromCache(cacheKey);
            if (cached) {
                return cached;
            }
        }
        
        // Check if request is already pending
        if (this.pendingRequests.has(cacheKey)) {
            return this.pendingRequests.get(cacheKey);
        }
        
        // Create request promise
        const requestPromise = this.makeRequest(url, options);
        this.pendingRequests.set(cacheKey, requestPromise);
        
        try {
            const result = await requestPromise;
            
            // Cache successful result
            if (options.useCache !== false) {
                this.setCache(cacheKey, result, options.cacheTimeout);
            }
            
            return result;
        } catch (error) {
            // Handle retry logic
            if (this.shouldRetry(error, cacheKey)) {
                return this.retryRequest(url, options, cacheKey);
            }
            
            throw error;
        } finally {
            // Remove from pending requests
            this.pendingRequests.delete(cacheKey);
        }
    }
    
    /**
     * Make HTTP request
     */
    async makeRequest(url, options = {}) {
        const defaultOptions = {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            timeout: 10000,
            ...options
        };
        
        // Add request interceptor
        const interceptedOptions = this.interceptRequest(url, defaultOptions);
        
        // Create abort controller for timeout
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), interceptedOptions.timeout);
        
        try {
            const response = await fetch(url, {
                ...interceptedOptions,
                signal: controller.signal
            });
            
            clearTimeout(timeoutId);
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
            const data = await response.json();
            
            // Add response interceptor
            return this.interceptResponse(url, data, response);
            
        } catch (error) {
            clearTimeout(timeoutId);
            
            if (error.name === 'AbortError') {
                throw new Error('Request timeout');
            }
            
            throw error;
        }
    }
    
    /**
     * Retry request with exponential backoff
     */
    async retryRequest(url, options, cacheKey) {
        const retryCount = this.retryAttempts.get(cacheKey) || 0;
        
        if (retryCount >= this.maxRetries) {
            this.retryAttempts.delete(cacheKey);
            throw new Error(`Request failed after ${this.maxRetries} retries`);
        }
        
        // Exponential backoff: 1s, 2s, 4s, 8s...
        const delay = Math.pow(2, retryCount) * 1000;
        
        await new Promise(resolve => setTimeout(resolve, delay));
        
        this.retryAttempts.set(cacheKey, retryCount + 1);
        
        return this.fetchData(url, options);
    }
    
    /**
     * Check if request should be retried
     */
    shouldRetry(error, cacheKey) {
        const retryCount = this.retryAttempts.get(cacheKey) || 0;
        
        if (retryCount >= this.maxRetries) {
            return false;
        }
        
        // Retry on network errors or 5xx status codes
        return (
            error.message.includes('timeout') ||
            error.message.includes('network') ||
            error.message.includes('HTTP 5')
        );
    }
    
    /**
     * Generate cache key
     */
    generateCacheKey(url, options) {
        const key = `${url}_${JSON.stringify(options)}`;
        return btoa(key).replace(/[^a-zA-Z0-9]/g, '');
    }
    
    /**
     * Get data from cache
     */
    getFromCache(cacheKey) {
        const cached = this.cache.get(cacheKey);
        
        if (!cached) return null;
        
        // Check if cache has expired
        if (Date.now() - cached.timestamp > cached.timeout) {
            this.cache.delete(cacheKey);
            return null;
        }
        
        return cached.data;
    }
    
    /**
     * Set data in cache
     */
    setCache(cacheKey, data, timeout = this.cacheTimeout) {
        this.cache.set(cacheKey, {
            data,
            timestamp: Date.now(),
            timeout
        });
    }
    
    /**
     * Clear cache
     */
    clearCache(pattern = null) {
        if (pattern) {
            const regex = new RegExp(pattern);
            for (const key of this.cache.keys()) {
                if (regex.test(key)) {
                    this.cache.delete(key);
                }
            }
        } else {
            this.cache.clear();
        }
    }
    
    /**
     * Setup cache cleanup
     */
    setupCacheCleanup() {
        // Clean up expired cache entries every minute
        setInterval(() => {
            const now = Date.now();
            for (const [key, cached] of this.cache.entries()) {
                if (now - cached.timestamp > cached.timeout) {
                    this.cache.delete(key);
                }
            }
        }, 60000);
    }
    
    /**
     * Setup request interceptors
     */
    setupRequestInterceptors() {
        this.requestInterceptors = [];
        this.responseInterceptors = [];
    }
    
    /**
     * Add request interceptor
     */
    addRequestInterceptor(interceptor) {
        this.requestInterceptors.push(interceptor);
    }
    
    /**
     * Add response interceptor
     */
    addResponseInterceptor(interceptor) {
        this.responseInterceptors.push(interceptor);
    }
    
    /**
     * Intercept request
     */
    interceptRequest(url, options) {
        let interceptedOptions = { ...options };
        
        for (const interceptor of this.requestInterceptors) {
            interceptedOptions = interceptor(url, interceptedOptions);
        }
        
        return interceptedOptions;
    }
    
    /**
     * Intercept response
     */
    interceptResponse(url, data, response) {
        let interceptedData = data;
        
        for (const interceptor of this.responseInterceptors) {
            interceptedData = interceptor(url, interceptedData, response);
        }
        
        return interceptedData;
    }
    
    /**
     * Fetch discipline data
     */
    async fetchDisciplineData() {
        try {
            const data = await this.fetchData('api_disciplines.php', {
                method: 'GET',
                useCache: true,
                cacheTimeout: 10 * 60 * 1000 // 10 minutes
            });
            
            return data;
        } catch (error) {
            console.error('Failed to fetch discipline data:', error);
            throw error;
        }
    }
    
    /**
     * Save character data
     */
    async saveCharacter(characterData) {
        try {
            const response = await this.fetchData('save_character.php', {
                method: 'POST',
                body: JSON.stringify(characterData),
                useCache: false
            });
            
            return response;
        } catch (error) {
            console.error('Failed to save character:', error);
            throw error;
        }
    }
    
    /**
     * Load character data
     */
    async loadCharacter(characterId) {
        try {
            const data = await this.fetchData(`load_character.php?id=${characterId}`, {
                method: 'GET',
                useCache: true,
                cacheTimeout: 5 * 60 * 1000 // 5 minutes
            });
            
            return data;
        } catch (error) {
            console.error('Failed to load character:', error);
            throw error;
        }
    }
    
    /**
     * Get character list
     */
    async getCharacterList() {
        try {
            const data = await this.fetchData('get_characters.php', {
                method: 'GET',
                useCache: true,
                cacheTimeout: 2 * 60 * 1000 // 2 minutes
            });
            
            return data;
        } catch (error) {
            console.error('Failed to get character list:', error);
            throw error;
        }
    }
    
    /**
     * Delete character
     */
    async deleteCharacter(characterId) {
        try {
            const response = await this.fetchData(`delete_character.php?id=${characterId}`, {
                method: 'DELETE',
                useCache: false
            });
            
            // Clear related cache entries
            this.clearCache(`.*character.*${characterId}.*`);
            
            return response;
        } catch (error) {
            console.error('Failed to delete character:', error);
            throw error;
        }
    }
    
    /**
     * Upload file
     */
    async uploadFile(file, endpoint = 'upload.php') {
        try {
            const formData = new FormData();
            formData.append('file', file);
            
            const response = await this.fetchData(endpoint, {
                method: 'POST',
                body: formData,
                useCache: false,
                headers: {} // Let browser set Content-Type for FormData
            });
            
            return response;
        } catch (error) {
            console.error('Failed to upload file:', error);
            throw error;
        }
    }
    
    /**
     * Get cache statistics
     */
    getCacheStats() {
        const now = Date.now();
        let expiredCount = 0;
        let totalSize = 0;
        
        for (const [key, cached] of this.cache.entries()) {
            if (now - cached.timestamp > cached.timeout) {
                expiredCount++;
            }
            totalSize += JSON.stringify(cached.data).length;
        }
        
        return {
            totalEntries: this.cache.size,
            expiredEntries: expiredCount,
            totalSize: totalSize,
            pendingRequests: this.pendingRequests.size,
            retryAttempts: this.retryAttempts.size
        };
    }
    
    /**
     * Get pending requests
     */
    getPendingRequests() {
        return Array.from(this.pendingRequests.keys());
    }
    
    /**
     * Cancel pending request
     */
    cancelPendingRequest(cacheKey) {
        if (this.pendingRequests.has(cacheKey)) {
            this.pendingRequests.delete(cacheKey);
            return true;
        }
        return false;
    }
    
    /**
     * Cancel all pending requests
     */
    cancelAllPendingRequests() {
        const count = this.pendingRequests.size;
        this.pendingRequests.clear();
        return count;
    }
    
    /**
     * Get data manager statistics
     */
    getStats() {
        return {
            cache: this.getCacheStats(),
            pendingRequests: this.pendingRequests.size,
            retryAttempts: this.retryAttempts.size,
            maxRetries: this.maxRetries,
            cacheTimeout: this.cacheTimeout
        };
    }
}

// Export for use in other modules
window.DataManager = DataManager;
