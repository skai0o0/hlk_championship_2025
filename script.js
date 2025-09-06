// Theme Toggle Functionality
function toggleTheme() {
    const currentTheme = document.documentElement.getAttribute('data-theme');
    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
    
    // Set the new theme
    if (newTheme === 'dark') {
        document.documentElement.setAttribute('data-theme', 'dark');
    } else {
        document.documentElement.removeAttribute('data-theme');
    }
    
    // Save theme preference to localStorage
    localStorage.setItem('theme', newTheme);
    
    // Add a subtle animation effect
    document.body.style.transition = 'background-color 0.3s ease, color 0.3s ease';
}

// Load theme preference on page load
function loadTheme() {
    const savedTheme = localStorage.getItem('theme');
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    
    // Use saved theme, or default to system preference
    const theme = savedTheme || (prefersDark ? 'dark' : 'light');
    
    if (theme === 'dark') {
        document.documentElement.setAttribute('data-theme', 'dark');
    }
}

// Initialize theme when page loads
document.addEventListener('DOMContentLoaded', function() {
    loadTheme();
    
    // Listen for system theme changes
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function(e) {
        if (!localStorage.getItem('theme')) {
            // Only auto-switch if user hasn't manually set a theme
            if (e.matches) {
                document.documentElement.setAttribute('data-theme', 'dark');
            } else {
                document.documentElement.removeAttribute('data-theme');
            }
        }
    });
});

// Additional smooth animations for theme transitions
document.addEventListener('DOMContentLoaded', function() {
    // Add transition class to body for smooth theme switching
    document.body.classList.add('theme-transition');
    
    // Enhanced progress bar animations for dark theme
    const progressSteps = document.querySelectorAll('.progress-step');
    progressSteps.forEach((step, index) => {
        // Add slight delay for staggered animation
        step.style.setProperty('--animation-delay', `${index * 0.1}s`);
    });

    // Initialize dropdown menu
    initializeDropdownMenu();
});
