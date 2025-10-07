/**
 * NotificationManager.js - Centralized notification and user feedback system
 * Provides consistent user feedback through toasts, modals, and alerts
 */

class NotificationManager {
    constructor(uiManager) {
        this.uiManager = uiManager;
        this.notifications = new Map();
        this.notificationQueue = [];
        this.isProcessing = false;
        this.defaultOptions = {
            duration: 3000,
            position: 'top-right',
            type: 'info',
            closable: true,
            animated: true
        };
        
        this.init();
    }
    
    /**
     * Initialize the notification manager
     */
    init() {
        this.setupNotificationContainer();
        this.setupQueueProcessor();
        this.setupKeyboardShortcuts();
    }
    
    /**
     * Setup notification container
     */
    setupNotificationContainer() {
        // Create notification container if it doesn't exist
        let container = document.getElementById('notification-container');
        if (!container) {
            container = this.uiManager.createElement('div', {
                id: 'notification-container',
                className: 'notification-container'
            });
            
            // Position container
            container.style.position = 'fixed';
            container.style.top = '20px';
            container.style.right = '20px';
            container.style.zIndex = '10000';
            container.style.pointerEvents = 'none';
            
            document.body.appendChild(container);
        }
        
        this.container = container;
    }
    
    /**
     * Setup queue processor
     */
    setupQueueProcessor() {
        // Process notification queue every 100ms
        setInterval(() => {
            if (this.notificationQueue.length > 0 && !this.isProcessing) {
                this.processNotificationQueue();
            }
        }, 100);
    }
    
    /**
     * Setup keyboard shortcuts
     */
    setupKeyboardShortcuts() {
        document.addEventListener('keydown', (e) => {
            // Ctrl/Cmd + Shift + N to show notification history
            if ((e.ctrlKey || e.metaKey) && e.shiftKey && e.key === 'N') {
                e.preventDefault();
                this.showNotificationHistory();
            }
        });
    }
    
    /**
     * Show notification
     */
    show(message, options = {}) {
        const notificationOptions = { ...this.defaultOptions, ...options };
        
        // Add to queue
        this.notificationQueue.push({
            message,
            options: notificationOptions,
            timestamp: Date.now()
        });
    }
    
    /**
     * Show success notification
     */
    success(message, options = {}) {
        this.show(message, { ...options, type: 'success' });
    }
    
    /**
     * Show error notification
     */
    error(message, options = {}) {
        this.show(message, { ...options, type: 'error', duration: 5000 });
    }
    
    /**
     * Show warning notification
     */
    warning(message, options = {}) {
        this.show(message, { ...options, type: 'warning', duration: 4000 });
    }
    
    /**
     * Show info notification
     */
    info(message, options = {}) {
        this.show(message, { ...options, type: 'info' });
    }
    
    /**
     * Show toast notification
     */
    toast(message, options = {}) {
        this.show(message, { ...options, type: 'toast' });
    }
    
    /**
     * Process notification queue
     */
    processNotificationQueue() {
        this.isProcessing = true;
        
        while (this.notificationQueue.length > 0) {
            const notification = this.notificationQueue.shift();
            this.createNotification(notification.message, notification.options);
        }
        
        this.isProcessing = false;
    }
    
    /**
     * Create notification element
     */
    createNotification(message, options) {
        const notificationId = this.generateNotificationId();
        const notification = this.uiManager.createElement('div', {
            className: `notification notification-${options.type}`,
            'data-notification-id': notificationId
        });
        
        // Set notification content
        notification.innerHTML = `
            <div class="notification-content">
                <div class="notification-message">${message}</div>
                ${options.closable ? '<button class="notification-close">×</button>' : ''}
            </div>
            <div class="notification-progress"></div>
        `;
        
        // Add event listeners
        if (options.closable) {
            const closeButton = notification.querySelector('.notification-close');
            closeButton.addEventListener('click', () => {
                this.hideNotification(notificationId);
            });
        }
        
        // Add click to close
        notification.addEventListener('click', () => {
            if (options.closable) {
                this.hideNotification(notificationId);
            }
        });
        
        // Position notification
        this.positionNotification(notification, options.position);
        
        // Add to container
        this.container.appendChild(notification);
        
        // Store notification
        this.notifications.set(notificationId, {
            element: notification,
            options,
            timestamp: Date.now()
        });
        
        // Show animation
        if (options.animated) {
            this.uiManager.animate(notification, 'slideIn');
        }
        
        // Auto-hide
        if (options.duration > 0) {
            setTimeout(() => {
                this.hideNotification(notificationId);
            }, options.duration);
        }
        
        return notificationId;
    }
    
    /**
     * Position notification
     */
    positionNotification(notification, position) {
        const positions = {
            'top-right': { top: '20px', right: '20px' },
            'top-left': { top: '20px', left: '20px' },
            'bottom-right': { bottom: '20px', right: '20px' },
            'bottom-left': { bottom: '20px', left: '20px' },
            'top-center': { top: '20px', left: '50%', transform: 'translateX(-50%)' },
            'bottom-center': { bottom: '20px', left: '50%', transform: 'translateX(-50%)' }
        };
        
        const pos = positions[position] || positions['top-right'];
        Object.assign(notification.style, pos);
    }
    
    /**
     * Hide notification
     */
    hideNotification(notificationId) {
        const notification = this.notifications.get(notificationId);
        if (!notification) return;
        
        const { element, options } = notification;
        
        // Hide animation
        if (options.animated) {
            this.uiManager.animate(element, 'slideOut', () => {
                this.removeNotification(notificationId);
            });
        } else {
            this.removeNotification(notificationId);
        }
    }
    
    /**
     * Remove notification
     */
    removeNotification(notificationId) {
        const notification = this.notifications.get(notificationId);
        if (!notification) return;
        
        const { element } = notification;
        
        // Remove from DOM
        if (element.parentNode) {
            element.parentNode.removeChild(element);
        }
        
        // Remove from map
        this.notifications.delete(notificationId);
    }
    
    /**
     * Clear all notifications
     */
    clearAll() {
        const count = this.notifications.size;
        
        this.notifications.forEach((notification, id) => {
            this.removeNotification(id);
        });
        
        return count;
    }
    
    /**
     * Clear notifications by type
     */
    clearByType(type) {
        let count = 0;
        
        this.notifications.forEach((notification, id) => {
            if (notification.options.type === type) {
                this.removeNotification(id);
                count++;
            }
        });
        
        return count;
    }
    
    /**
     * Show modal notification
     */
    showModal(title, message, options = {}) {
        const modalOptions = {
            type: 'modal',
            closable: true,
            animated: true,
            ...options
        };
        
        const modalContent = `
            <div class="modal-header">
                <h3>${title}</h3>
                ${modalOptions.closable ? '<button class="modal-close">×</button>' : ''}
            </div>
            <div class="modal-body">
                <p>${message}</p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary modal-ok">OK</button>
                ${modalOptions.showCancel ? '<button class="btn btn-secondary modal-cancel">Cancel</button>' : ''}
            </div>
        `;
        
        return this.uiManager.createModal(modalContent, modalOptions);
    }
    
    /**
     * Show confirmation dialog
     */
    showConfirmation(title, message, options = {}) {
        return new Promise((resolve) => {
            const modalOptions = {
                type: 'confirmation',
                showCancel: true,
                ...options
            };
            
            const modal = this.showModal(title, message, modalOptions);
            
            // Handle OK button
            const okButton = modal.querySelector('.modal-ok');
            okButton.addEventListener('click', () => {
                this.uiManager.hide(modal);
                resolve(true);
            });
            
            // Handle Cancel button
            const cancelButton = modal.querySelector('.modal-cancel');
            if (cancelButton) {
                cancelButton.addEventListener('click', () => {
                    this.uiManager.hide(modal);
                    resolve(false);
                });
            }
            
            // Handle close button
            const closeButton = modal.querySelector('.modal-close');
            if (closeButton) {
                closeButton.addEventListener('click', () => {
                    this.uiManager.hide(modal);
                    resolve(false);
                });
            }
        });
    }
    
    /**
     * Show loading notification
     */
    showLoading(message = 'Loading...', options = {}) {
        const loadingOptions = {
            type: 'loading',
            duration: 0, // Don't auto-hide
            closable: false,
            ...options
        };
        
        return this.show(message, loadingOptions);
    }
    
    /**
     * Hide loading notification
     */
    hideLoading(notificationId) {
        this.hideNotification(notificationId);
    }
    
    /**
     * Show notification history
     */
    showNotificationHistory() {
        const history = Array.from(this.notifications.values())
            .sort((a, b) => b.timestamp - a.timestamp)
            .slice(0, 20); // Show last 20 notifications
        
        const historyContent = `
            <div class="notification-history">
                <h3>Notification History</h3>
                <div class="history-list">
                    ${history.map(notification => `
                        <div class="history-item notification-${notification.options.type}">
                            <span class="history-message">${notification.element.querySelector('.notification-message').textContent}</span>
                            <span class="history-time">${new Date(notification.timestamp).toLocaleTimeString()}</span>
                        </div>
                    `).join('')}
                </div>
            </div>
        `;
        
        this.uiManager.createModal(historyContent, { type: 'history' });
    }
    
    /**
     * Generate notification ID
     */
    generateNotificationId() {
        return `notification_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;
    }
    
    /**
     * Get notification statistics
     */
    getStats() {
        const typeCounts = {};
        let totalCount = 0;
        
        this.notifications.forEach(notification => {
            const type = notification.options.type;
            typeCounts[type] = (typeCounts[type] || 0) + 1;
            totalCount++;
        });
        
        return {
            totalNotifications: totalCount,
            typeCounts,
            queuedNotifications: this.notificationQueue.length,
            isProcessing: this.isProcessing
        };
    }
    
    /**
     * Get notification history
     */
    getHistory(limit = 50) {
        return Array.from(this.notifications.values())
            .sort((a, b) => b.timestamp - a.timestamp)
            .slice(0, limit)
            .map(notification => ({
                message: notification.element.querySelector('.notification-message').textContent,
                type: notification.options.type,
                timestamp: notification.timestamp
            }));
    }
}

// Export for use in other modules
window.NotificationManager = NotificationManager;
