/**
 * Admin Chat Module
 * Initializes and manages the admin chat interface
 */

// Only define AdminChat if it doesn't exist yet
if (!window.AdminChat) {
  window.AdminChat = {
    /**
     * Initialize admin chat
     */
    init: function() {
      console.log('Initializing admin chat interface...');
      
      // Initialize UI with admin flag set to true
      ChatUI.init(true);
      
      // Set up admin-specific features
      this.setupAdminFeatures();
      
      // Poll for new messages (simulates real-time updates)
      this.startPolling();
      
      return this;
    },
    
    /**
     * Set up admin-specific features
     */
    setupAdminFeatures: function() {
      // Update the notification badge regularly
      this.updateBadgeInterval = setInterval(() => {
        ChatUI.updateNotificationBadge();
      }, 5000);
      
      // Add keyboard shortcuts
      this.setupKeyboardShortcuts();
      
      // Add quick responses feature
      this.addQuickResponseFeature();
      
      // Listen for new user messages
      window.addEventListener('chat-message-received', (event) => {
        // Update UI if needed for new messages
        const { userId, message } = event.detail;
        
        // If this user is currently active in the admin panel
        if (ChatUI.activeUserId === userId && message.sender !== 'admin') {
          ChatStorage.markConversationAsRead(userId);
        }
      });
    },
    
    /**
     * Start polling for updates
     */
    startPolling: function() {
      // Check for new conversations frequently (every 3 seconds)
      this.pollingInterval = setInterval(() => {
        // Force update conversation list
        if (ChatUI && typeof ChatUI.updateConversationList === 'function') {
          // First update conversation list
          ChatUI.updateConversationList();
          
          // Then update active conversation if one is selected
          if (ChatUI.activeUserId && typeof ChatUI.loadMessages === 'function') {
            ChatUI.loadMessages(ChatUI.activeUserId);
          }
          
          // Update notification badge
          if (typeof ChatUI.updateNotificationBadge === 'function') {
            ChatUI.updateNotificationBadge();
          }
        } else {
          console.error('ChatUI not initialized properly');
        }
      }, 3000);
      
      // Log that polling has started
      console.log('Admin polling started, interval ID:', this.pollingInterval);
    },
    
    /**
     * Set up keyboard shortcuts for admin
     */
    setupKeyboardShortcuts: function() {
      document.addEventListener('keydown', (e) => {
        // Alt+C to toggle chat panel
        if (e.altKey && e.key === 'c') {
          e.preventDefault();
          ChatUI.toggleChatPanel();
        }
        
        // Esc to close panel if open
        if (e.key === 'Escape' && ChatUI.isChatVisible()) {
          ChatUI.toggleChatPanel(false);
        }
      });
    },
    
    /**
     * Add quick response feature to the admin panel
     */
    addQuickResponseFeature: function() {
      // Wait for the DOM to be ready
      setTimeout(() => {
        const adminForm = document.getElementById('admin-chat-form');
        if (!adminForm) return;
        
        // Create quick response container
        const quickResponseContainer = document.createElement('div');
        quickResponseContainer.className = 'flex flex-wrap gap-2 mb-2';
        quickResponseContainer.id = 'quick-responses';
        
        // Add some predefined quick responses
        const quickResponses = [
          "Hello! How can I help you?",
          "Thank you for your patience.",
          "Let me check that for you.",
          "Is there anything else you need help with?",
          "I'll get back to you shortly."
        ];
        
        // Create buttons for each quick response
        quickResponses.forEach(response => {
          const button = document.createElement('button');
          button.type = 'button';
          button.className = 'px-3 py-1 bg-gray-100 hover:bg-teal-100 text-sm rounded-full text-gray-700 transition-colors';
          button.textContent = response;
          button.onclick = () => this.insertQuickResponse(response);
          
          quickResponseContainer.appendChild(button);
        });
        
        // Insert before the form
        adminForm.parentNode.insertBefore(quickResponseContainer, adminForm);
      }, 500);
    },
    
    /**
     * Insert quick response text into the input field
     */
    insertQuickResponse: function(text) {
      const input = document.getElementById('admin-message-input');
      if (!input) return;
      
      input.value = text;
      input.focus();
    },
    
    /**
     * Clean up resources when admin leaves the page
     */
    cleanup: function() {
      // Clear intervals
      if (this.updateBadgeInterval) {
        clearInterval(this.updateBadgeInterval);
      }
      
      if (this.pollingInterval) {
        clearInterval(this.pollingInterval);
      }
    }
  };

  // Add this before initializing the admin panel
  function checkAdminStatus() {
    return fetch('/check_session.php')
      .then(response => response.json())
      .then(data => {
        if (!data.is_admin) {
          throw new Error("Not authenticated as admin");
        }
        return data;
      });
  }

  // Initialize when document is ready - only add listener if AdminChat was just defined
  document.addEventListener('DOMContentLoaded', () => {
    // Only initialize on admin pages
    if (window.isAdminPage) {
      checkAdminStatus()
        .then(() => {
          // Initialize admin features here
          window.adminChat = window.AdminChat.init();
        })
        .catch(error => {
          console.error("Admin authentication failed:", error);
          // Redirect to login page or show error
          window.location.href = '/admin/login.php';
        });
      
      // Clean up before page unload
      window.addEventListener('beforeunload', () => {
        if (window.adminChat) {
          window.adminChat.cleanup();
        }
      });
    }
  });
}
