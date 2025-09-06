/**
 * Menu Management System for HLK Championship 2025
 * Compatible with Apache XAMPP
 * Handles profile dropdown menu based on login status
 */

class MenuManager {
    constructor() {
        this.isDropdownOpen = false;
        this.dropdownContainer = null;
        this.profileDropdown = null;
        this.init();
    }

    /**
     * Initialize the menu system
     */
    init() {
        // Wait for DOM to be ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.setup());
        } else {
            this.setup();
        }
    }

    /**
     * Setup the menu system
     */
    setup() {
        this.dropdownContainer = document.querySelector('.profile-dropdown-container');
        this.profileDropdown = document.getElementById('profileMenu');
        
        if (!this.dropdownContainer || !this.profileDropdown) {
            console.warn('Menu elements not found');
            return;
        }

        // Check login status and render appropriate menu
        this.checkLoginStatusAndRender();
        
        // Setup event listeners
        this.setupEventListeners();
    }

    /**
     * Setup event listeners
     */
    setupEventListeners() {
        // Close dropdown when clicking outside
        document.addEventListener('click', (event) => {
            if (this.dropdownContainer && !this.dropdownContainer.contains(event.target)) {
                this.closeDropdown();
            }
        });

        // Handle escape key
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && this.isDropdownOpen) {
                this.closeDropdown();
            }
        });
    }

    /**
     * Check login status from server and render menu
     */
    async checkLoginStatusAndRender() {
        try {
            // Add some debug info for XAMPP environment
            console.log('MenuManager: Checking login status...');
            console.log('MenuManager: Current URL:', window.location.href);
            
            const response = await fetch('./php/session.php', {
                method: 'GET',
                credentials: 'same-origin', // Include cookies
                headers: {
                    'Content-Type': 'application/json',
                }
            });

            console.log('MenuManager: Session API response status:', response.status);

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            console.log('MenuManager: Session API response data:', data);
            
            if (data.ok && data.user) {
                // User is logged in
                console.log('MenuManager: User is logged in:', data.user.name);
                this.renderLoggedInMenu(data.user);
                this.dropdownContainer.classList.add('logged-in');
            } else {
                // User is not logged in
                console.log('MenuManager: User is not logged in');
                this.renderLoggedOutMenu();
                this.dropdownContainer.classList.remove('logged-in');
            }
        } catch (error) {
            console.error('MenuManager: Error checking login status:', error);
            // Fallback to logged out state
            this.renderLoggedOutMenu();
            this.dropdownContainer.classList.remove('logged-in');
            
            // Only show error in development (localhost)
            if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
                console.warn('MenuManager: Development mode - API error details:', error.message);
            }
        }
    }

    /**
     * Render menu for logged-in users
     * @param {Object} user - User information from database
     */
    renderLoggedInMenu(user) {
        const initials = this.generateInitials(user.name);
        
        this.profileDropdown.innerHTML = `
            <div class="dropdown-header">
                <div class="user-avatar">
                    <span class="user-initials">${initials}</span>
                </div>
                <div class="user-info">
                    <h4>${this.escapeHtml(user.name)}</h4>
                    <p>${this.escapeHtml(user.class)} - ${this.escapeHtml(user.grade)}</p>
                </div>
            </div>
            <div class="dropdown-content">
                <a href="#" class="dropdown-item" onclick="menuManager.viewProfile(); return false;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <circle cx="12" cy="7" r="4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Hồ sơ cá nhân
                </a>
                <a href="#" class="dropdown-item" onclick="menuManager.viewTournaments(); return false;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Giải đấu của tôi
                </a>
                <a href="#" class="dropdown-item" onclick="menuManager.viewNotifications(); return false;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M13.73 21a2 2 0 0 1-3.46 0" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Thông báo
                </a>
                <a href="#" class="dropdown-item" onclick="menuManager.viewSettings(); return false;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1 1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Cài đặt
                </a>
                <div class="dropdown-divider"></div>
                <a href="#" class="dropdown-item logout-item" onclick="menuManager.logout(); return false;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <polyline points="16,17 21,12 16,7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <line x1="21" y1="12" x2="9" y2="12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Đăng xuất
                </a>
            </div>
        `;
    }

    /**
     * Render menu for logged-out users
     */
    renderLoggedOutMenu() {
        this.profileDropdown.innerHTML = `
            <div class="dropdown-content auth-menu">
                <a href="login.html" class="dropdown-item login-item">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <polyline points="10,17 15,12 10,7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <line x1="15" y1="12" x2="3" y2="12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Đăng nhập
                </a>
                <a href="register.html" class="dropdown-item register-item">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <circle cx="8.5" cy="7" r="4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <line x1="20" y1="8" x2="20" y2="14" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <line x1="23" y1="11" x2="17" y2="11" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Đăng ký
                </a>
            </div>
        `;
    }

    /**
     * Generate user initials from full name
     * @param {string} name - User's full name
     * @returns {string} - User initials (max 2 characters)
     */
    generateInitials(name) {
        if (!name) return 'HV';
        
        const words = name.trim().split(' ');
        if (words.length === 1) {
            return words[0].charAt(0).toUpperCase();
        }
        
        // Take first letter of first word and first letter of last word
        const firstInitial = words[0].charAt(0).toUpperCase();
        const lastInitial = words[words.length - 1].charAt(0).toUpperCase();
        
        return firstInitial + lastInitial;
    }

    /**
     * Escape HTML to prevent XSS
     * @param {string} text - Text to escape
     * @returns {string} - Escaped text
     */
    escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    /**
     * Toggle dropdown menu
     */
    toggleDropdown() {
        if (this.isDropdownOpen) {
            this.closeDropdown();
        } else {
            this.openDropdown();
        }
    }

    /**
     * Open dropdown menu
     */
    openDropdown() {
        if (this.profileDropdown) {
            this.profileDropdown.classList.add('show');
            this.isDropdownOpen = true;
            
            // Update button aria-expanded
            const profileBtn = document.querySelector('.profile-btn');
            if (profileBtn) {
                profileBtn.setAttribute('aria-expanded', 'true');
            }
        }
    }

    /**
     * Close dropdown menu
     */
    closeDropdown() {
        if (this.profileDropdown) {
            this.profileDropdown.classList.remove('show');
            this.isDropdownOpen = false;
            
            // Update button aria-expanded
            const profileBtn = document.querySelector('.profile-btn');
            if (profileBtn) {
                profileBtn.setAttribute('aria-expanded', 'false');
            }
        }
    }

    /**
     * Menu action handlers
     */
    viewProfile() {
        alert('Chức năng xem hồ sơ đang được phát triển!');
        this.closeDropdown();
    }

    viewTournaments() {
        alert('Chức năng xem giải đấu đang được phát triển!');
        this.closeDropdown();
    }

    viewNotifications() {
        alert('Chức năng thông báo đang được phát triển!');
        this.closeDropdown();
    }

    viewSettings() {
        alert('Chức năng cài đặt đang được phát triển!');
        this.closeDropdown();
    }

    /**
     * Logout user
     */
    async logout() {
        const confirmLogout = confirm('Bạn có chắc muốn đăng xuất?');
        if (!confirmLogout) {
            return;
        }

        try {
            const response = await fetch('./php/logout.php', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                }
            });

            if (response.ok) {
                // Successfully logged out
                this.renderLoggedOutMenu();
                this.dropdownContainer.classList.remove('logged-in');
                this.closeDropdown();
                
                // Show success message
                alert('Đã đăng xuất thành công!');
                
                // Optionally redirect to home page
                // window.location.href = 'index.html';
            } else {
                throw new Error('Logout failed');
            }
        } catch (error) {
            console.error('Logout error:', error);
            alert('Có lỗi xảy ra khi đăng xuất. Vui lòng thử lại!');
        }
    }

    /**
     * Refresh menu (useful after login/logout)
     */
    refresh() {
        this.checkLoginStatusAndRender();
    }
}

// Create global instance
const menuManager = new MenuManager();

// Global function for backward compatibility
function toggleProfileDropdown() {
    menuManager.toggleDropdown();
}

// Export for module usage (if needed)
if (typeof module !== 'undefined' && module.exports) {
    module.exports = MenuManager;
}
