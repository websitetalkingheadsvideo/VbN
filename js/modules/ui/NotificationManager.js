/**
 * NotificationManager.js - Centralized notification and user feedback system
 * Manages notifications, alerts, and user feedback messages
 */

class NotificationManager {
    constructor() {
        this.notifications = new Map();
        this.container = null;
        this.defaultOptions = {
            duration: 5000,
            position: 'top-right',
            type: 'info',
            closable: true,
            animated: true
        };
        this.setupContainer();
    }
    
    /**
     * Setup notification container
     */
    setupContainer() {
        this.container = document.createElement('div');
        this.container.id = 'notification-container';
        this.container.className = 'notification-container';
        this.container.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 10000;
            pointer-events: none;
        `;
        
        document.body.appendChild(this.container);
    }
    
    /**
     * Show notification
     */
    show(message, options = {}) {
        const config = { ...this.defaultOptions, ...options };
        const id = Date.now() + Math.random();
        
        const notification = this.createElement(message, config, id);
        this.notifications.set(id, notification);
        
        this.container.appendChild(notification);
        
        // Auto-remove after duration
        if (config.duration > 0) {
            setTimeout(() => {
                this.remove(id);
            }, config.duration);
        }
        
        return id;
    }
    
    /**
     * Create notification element
     */
    createElement(message, config, id) {
        const notification = document.createElement('div');
        notification.className = `notification notification-${config.type}`;
        notification.dataset.id = id;
        
        // Set styles based on type
        const typeStyles = this.getTypeStyles(config.type);
        notification.style.cssText = `
            background: ${typeStyles.background};
            color: ${typeStyles.color};
            padding: 15px 20px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
            margin-bottom: 10px;
            max-width: 300px;
            word-wrap: break-word;
            pointer-events: auto;
            position: relative;
            border-left: 4px solid ${typeStyles.borderColor};
            transform: translateX(100%);
            transition: transform 0.3s ease;
        `;
        
        // Add content
        const content = document.createElement('div');
        content.className = 'notification-content';
        content.innerHTML = message;
        notification.appendChild(content);
        
        // Add close button if closable
        if (config.closable) {
            const closeBtn = document.createElement('button');
            closeBtn.className = 'notification-close';
            closeBtn.innerHTML = '×';
            closeBtn.style.cssText = `
                position: absolute;
                top: 5px;
                right: 10px;
                background: none;
                border: none;
                color: inherit;
                font-size: 18px;
                cursor: pointer;
                padding: 0;
                width: 20px;
                height: 20px;
                display: flex;
                align-items: center;
                justify-content: center;
            `;
            closeBtn.addEventListener('click', () => this.remove(id));
            notification.appendChild(closeBtn);
        }
        
        // Animate in
        if (config.animated) {
            requestAnimationFrame(() => {
                notification.style.transform = 'translateX(0)';
            });
        }
        
        return notification;
    }
    
    /**
     * Get styles for notification type
     */
    getTypeStyles(type) {
        const styles = {
            info: {
                background: '#17a2b8',
                color: '#fff',
                borderColor: '#138496'
            },
            success: {
                background: '#28a745',
                color: '#fff',
                borderColor: '#1e7e34'
            },
            warning: {
                background: '#ffc107',
                color: '#212529',
                borderColor: '#e0a800'
            },
            error: {
                background: '#dc3545',
                color: '#fff',
                borderColor: '#bd2130'
            }
        };
        
        return styles[type] || styles.info;
    }
    
    /**
     * Remove notification
     */
    remove(id) {
        const notification = this.notifications.get(id);
        if (!notification) return false;
        
        try {
            // Animate out
            notification.style.transform = 'translateX(100%)';
            notification.style.transition = 'transform 0.3s ease';
            
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
                this.notifications.delete(id);
            }, 300);
            
            return true;
        } catch (error) {
            console.error('Error removing notification:', error);
            return false;
        }
    }
    
    /**
     * Clear all notifications
     */
    clear() {
        this.notifications.forEach((notification, id) => {
            this.remove(id);
        });
    }
    
    /**
     * Show success notification
     */
    success(message, options = {}) {
        return this.show(message, { ...options, type: 'success' });
    }
    
    /**
     * Show error notification
     */
    error(message, options = {}) {
        return this.show(message, { ...options, type: 'error' });
    }
    
    /**
     * Show warning notification
     */
    warning(message, options = {}) {
        return this.show(message, { ...options, type: 'warning' });
    }
    
    /**
     * Show info notification
     */
    info(message, options = {}) {
        return this.show(message, { ...options, type: 'info' });
    }
    
    /**
     * Show loading notification
     */
    loading(message, options = {}) {
        const loadingMessage = `
            <div style="display: flex; align-items: center; gap: 10px;">
                <div class="spinner" style="
                    width: 20px;
                    height: 20px;
                    border: 2px solid #f3f3f3;
                    border-top: 2px solid #3498db;
                    border-radius: 50%;
                    animation: spin 1s linear infinite;
                "></div>
                <span>${message}</span>
            </div>
        `;
        
        return this.show(loadingMessage, { ...options, duration: 0, closable: false });
    }
    
    /**
     * Show confirmation dialog
     */
    confirm(message, options = {}) {
        return new Promise((resolve) => {
            const id = this.show(`
                <div class="confirmation-dialog">
                    <p>${message}</p>
                    <div style="margin-top: 15px; display: flex; gap: 10px; justify-content: flex-end;">
                        <button class="btn btn-secondary" data-action="cancel">Cancel</button>
                        <button class="btn btn-primary" data-action="confirm">Confirm</button>
                    </div>
                </div>
            `, { ...options, duration: 0, closable: false });
            
            const notification = this.notifications.get(id);
            if (notification) {
                notification.addEventListener('click', (e) => {
                    const action = e.target.dataset.action;
                    if (action === 'confirm' || action === 'cancel') {
                        this.remove(id);
                        resolve(action === 'confirm');
                    }
                });
            }
        });
    }
    
    /**
     * Show toast notification
     */
    toast(message, options = {}) {
        return this.show(message, {
            ...options,
            duration: 3000,
            position: 'bottom-right'
        });
    }
    
    /**
     * Show persistent notification
     */
    persistent(message, options = {}) {
        return this.show(message, {
            ...options,
            duration: 0,
            closable: true
        });
    }
    
    /**
     * Update notification content
     */
    update(id, message) {
        const notification = this.notifications.get(id);
        if (!notification) return false;
        
        try {
            const content = notification.querySelector('.notification-content');
            if (content) {
                content.innerHTML = message;
                return true;
            }
            return false;
        } catch (error) {
            console.error('Error updating notification:', error);
            return false;
        }
    }
    
    /**
     * Get notification count
     */
    getCount() {
        return this.notifications.size;
    }
    
    /**
     * Check if notification exists
     */
    exists(id) {
        return this.notifications.has(id);
    }
    
    /**
     * Clean up
     */
    cleanup() {
        this.clear();
        if (this.container && this.container.parentNode) {
            this.container.parentNode.removeChild(this.container);
        }
        this.container = null;
    }
}

// Add CSS for spinner animation
const style = document.createElement('style');
style.textContent = `
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    .notification-container {
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    }
    
    .notification {
        font-size: 14px;
        line-height: 1.4;
    }
    
    .notification-close {
        opacity: 0.7;
        transition: opacity 0.2s ease;
    }
    
    .notification-close:hover {
        opacity: 1;
    }
    
    .confirmation-dialog button {
        padding: 8px 16px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 14px;
        transition: background-color 0.2s ease;
    }
    
    .confirmation-dialog .btn-primary {
        background-color: #007bff;
        color: white;
    }
    
    .confirmation-dialog .btn-primary:hover {
        background-color: #0056b3;
    }
    
    .confirmation-dialog .btn-secondary {
        background-color: #6c757d;
        color: white;
    }
    
    .confirmation-dialog .btn-secondary:hover {
        background-color: #545b62;
    }
`;
document.head.appendChild(style);

// Export for use in other modules
window.NotificationManager = NotificationManager;
