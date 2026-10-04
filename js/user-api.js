/**
 * Life is Character Radio - User API Client
 * Location: /js/user-api.js
 * 
 * Handles all AJAX calls to user API endpoints
 * 
 * Note: Expects window.API_BASE_URL to be set before this script loads
 */

const UserAPI = {
    baseURL: window.API_BASE_URL || (window.location.origin + '/api/user/'),
    
    /**
     * Make API request
     */
    async request(endpoint, options = {}) {
        const url = this.baseURL + endpoint;
        const config = {
            method: options.method || 'GET',
            headers: {
                'Content-Type': 'application/json',
                ...options.headers
            },
            ...options
        };
        
        if (options.body && typeof options.body === 'object') {
            config.body = JSON.stringify(options.body);
        }
        
        try {
            const response = await fetch(url, config);
            const data = await response.json();
            
            if (!response.ok) {
                throw { status: response.status, ...data };
            }
            
            return data;
        } catch (error) {
            throw error;
        }
    },
    
    /**
     * User Registration
     */
    async register(formData) {
        return this.request('register', {
            method: 'POST',
            body: formData
        });
    },
    
    /**
     * User Login
     */
    async login(email, password, remember = false, redirect = null) {
        return this.request('login', {
            method: 'POST',
            body: { email, password, remember, redirect }
        });
    },
    
    /**
     * User Logout
     */
    async logout() {
        return this.request('logout', {
            method: 'POST'
        });
    },
    
    /**
     * Activate Account
     */
    async activate(token) {
        return this.request('activate', {
            method: 'POST',
            body: { token }
        });
    },
    
    /**
     * Resend Activation Email
     */
    async resendActivation(email) {
        return this.request('resend-activation', {
            method: 'POST',
            body: { email }
        });
    },
    
    /**
     * Forgot Password
     */
    async forgotPassword(email) {
        return this.request('forgot-password', {
            method: 'POST',
            body: { email }
        });
    },
    
    /**
     * Reset Password
     */
    async resetPassword(token, password, confirmPassword) {
        return this.request('reset-password', {
            method: 'POST',
            body: { token, password, confirm_password: confirmPassword }
        });
    },
    
    /**
     * Get Dashboard Data
     */
    async getDashboard() {
        return this.request('dashboard');
    },
    
    /**
     * Get Profile
     */
    async getProfile() {
        return this.request('profile');
    },
    
    /**
     * Update Profile
     */
    async updateProfile(fullName, phone) {
        return this.request('profile', {
            method: 'PUT',
            body: { full_name: fullName, phone }
        });
    },
    
    /**
     * Update Password
     */
    async updatePassword(currentPassword, newPassword, confirmPassword) {
        return this.request('password', {
            method: 'PUT',
            body: { 
                current_password: currentPassword,
                new_password: newPassword,
                confirm_password: confirmPassword
            }
        });
    },
    
    /**
     * Get Transactions
     */
    async getTransactions(type = '', status = '') {
        let url = 'transactions';
        const params = [];
        if (type) params.push(`type=${type}`);
        if (status) params.push(`status=${status}`);
        if (params.length) url += '?' + params.join('&');
        
        return this.request(url);
    }
};

/**
 * UI Helper Functions
 */
const UIHelpers = {
    /**
     * Show loading state on button
     */
    showLoading(button, text = 'Processing...') {
        button.dataset.originalText = button.innerHTML;
        button.disabled = true;
        button.innerHTML = `
            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            ${text}
        `;
    },
    
    /**
     * Hide loading state on button
     */
    hideLoading(button) {
        button.disabled = false;
        if (button.dataset.originalText) {
            button.innerHTML = button.dataset.originalText;
        }
    },
    
    /**
     * Show error message
     */
    showError(container, message) {
        container.innerHTML = `
            <div class="bg-red-50 border border-red-200 text-red-800 rounded-lg p-4 mb-6">
                <div class="flex items-start">
                    <svg class="w-5 h-5 text-red-400 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    <span>${message}</span>
                </div>
            </div>
        `;
        container.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    },
    
    /**
     * Show success message
     */
    showSuccess(container, message) {
        container.innerHTML = `
            <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg p-4 mb-6">
                <div class="flex items-start">
                    <svg class="w-5 h-5 text-green-400 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span>${message}</span>
                </div>
            </div>
        `;
        container.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    },
    
    /**
     * Show validation errors
     */
    showValidationErrors(errors) {
        Object.keys(errors).forEach(field => {
            const input = document.querySelector(`[name="${field}"]`);
            if (input) {
                input.classList.add('border-red-500');
                const errorDiv = document.createElement('p');
                errorDiv.className = 'text-red-500 text-sm mt-1';
                errorDiv.textContent = errors[field];
                input.parentElement.appendChild(errorDiv);
            }
        });
    },
    
    /**
     * Clear validation errors
     */
    clearValidationErrors() {
        document.querySelectorAll('.border-red-500').forEach(el => {
            el.classList.remove('border-red-500');
        });
        document.querySelectorAll('.text-red-500').forEach(el => {
            if (el.tagName === 'P') el.remove();
        });
    },
    
    /**
     * Format currency
     */
    formatCurrency(amount) {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD'
        }).format(amount);
    },
    
    /**
     * Format date
     */
    formatDate(dateString) {
        return new Date(dateString).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
    },
    
    /**
     * Format date time
     */
    formatDateTime(dateString) {
        return new Date(dateString).toLocaleString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }
};
