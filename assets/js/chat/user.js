/**
 * User Chat Module
 * Initializes and manages the user chat interface
 */

// Only define UserChat if it doesn't exist yet
if (!window.UserChat) {
  window.UserChat = {
    /**
     * Initialize user chat
     */
    init: function() {
      console.log('Initializing user chat interface...');
      
      // Initialize UI with admin flag set to false
      ChatUI.init(false);
      
      // Set up user-specific features
      this.setupUserFeatures();
      
      // Poll for new messages
      this.startPolling();
      
      return this;
    },
    
    /**
     * Set up user-specific features
     */
    setupUserFeatures: function() {
      // Update notification badge
      this.updateBadgeInterval = setInterval(() => {
        ChatUI.updateNotificationBadge();
      }, 5000);
      
      // Add typing indicator feature
      this.setupTypingIndicator();
      
      // Add seen status indicators
      this.setupSeenIndicators();
    },
    
    /**
     * Start polling
     */
    startPolling: function() {
      // Check for new messages periodically
      this.pollingInterval = setInterval(() => {
        if (ChatUI && ChatUI.userId) {
          // Fetch messages to check for updates
          ChatStorage.getMessages(ChatUI.userId)
            .then(messages => {
              // Check if there are new messages from admin
              const adminMessages = messages.filter(m => m.sender === 'admin');
              
              // Update notification badge if chat is not visible
              if (adminMessages.length > 0 && !ChatUI.isChatVisible()) {
                ChatUI.updateNotificationBadge();
              }
              
              // If chat is currently open, update the messages
              if (ChatUI.isChatVisible()) {
                // Reload messages if the chat UI exists
                if (ChatUI && typeof ChatUI.loadMessages === 'function') {
                  ChatUI.loadMessages(ChatUI.userId);
                }
              }
            })
            .catch(error => {
              console.error('Error polling messages:', error);
            });
        }
      }, 3000);
    },
    
    /**
     * Fix overlay issue that prevents clicking on other elements
     */
    fixOverlayIssue: function() {
      setTimeout(() => {
        // Fix chat container to only capture events on its children
        const chatContainer = document.getElementById('chat-container');
        if (chatContainer) {
          // Set container to not capture pointer events
          chatContainer.style.pointerEvents = 'none';
          
          // But allow button and panel to capture events
          const chatButton = document.getElementById('chat-button');
          const chatPanel = document.getElementById('chat-panel');
          
          if (chatButton) chatButton.style.pointerEvents = 'auto';
          if (chatPanel) chatPanel.style.pointerEvents = 'auto';
          
          // Ensure container size is minimal
          chatContainer.style.width = 'auto';
          chatContainer.style.height = 'auto';
        }
      }, 500); // Give time for the elements to be created
    },
    
    /**
     * Set up typing indicator
     */
    setupTypingIndicator: function() {
      // Get the message input
      const messageInput = document.getElementById('message-input');
      if (!messageInput) return;
      
      // Keep the basic typing indicator setup but remove the automatic showing part
      let typingTimeout = null;
    },
    
    /**
     * Set up seen indicators
     * Empty implementation to avoid automatic mock responses
     */
    setupSeenIndicators: function() {
      // Intentionally left empty - no automatic seen indicators
      console.log("Seen indicators disabled - using real-time communication only");
    },
    
    /**
     * Show typing indicator in the chat
     */
    showTypingIndicator: function() {
      const container = document.getElementById('messages-container');
      if (!container) return;
      
      // Remove any existing typing indicators
      this.hideTypingIndicator();
      
      // Create typing indicator
      const typingDiv = document.createElement('div');
      typingDiv.id = 'typing-indicator';
      typingDiv.className = 'flex items-start mb-4';
      typingDiv.innerHTML = `
        <div class="w-8 h-8 rounded-full bg-teal-100 flex-shrink-0 flex items-center justify-center">
          <i class="fas fa-headset text-teal-600 text-sm"></i>
        </div>
        <div class="ml-2 bg-gray-100 rounded-lg p-3 shadow-sm max-w-[80%] flex items-center">
          <div class="typing-animation">
            <span></span>
            <span></span>
            <span></span>
          </div>
        </div>
      `;
      
      container.appendChild(typingDiv);
      ChatUI.scrollToBottom();
      
      // Add the CSS for typing animation if it doesn't exist
      if (!document.getElementById('typing-animation-style')) {
        const style = document.createElement('style');
        style.id = 'typing-animation-style';
        style.textContent = `
          .typing-animation {
            display: flex;
            align-items: center;
            column-gap: 4px;
          }
          
          .typing-animation span {
            height: 8px;
            width: 8px;
            background: #9CA3AF;
            border-radius: 50%;
            display: block;
            animation: typing 1.5s infinite ease-in-out;
          }
          
          .typing-animation span:nth-child(1) {
            animation-delay: 0s;
          }
          
          .typing-animation span:nth-child(2) {
            animation-delay: 0.3s;
          }
          
          .typing-animation span:nth-child(3) {
            animation-delay: 0.6s;
          }
          
          @keyframes typing {
            0%, 100% {
              transform: translateY(0);
              opacity: 0.5;
            }
            50% {
              transform: translateY(-5px);
              opacity: 1;
            }
          }
        `;
        document.head.appendChild(style);
      }
    },
    
    /**
     * Hide typing indicator
     */
    hideTypingIndicator: function() {
      const typingIndicator = document.getElementById('typing-indicator');
      if (typingIndicator) {
        typingIndicator.remove();
      }
    },
    
    /**
     * Show message seen indicator
     */
    showMessageSeen: function() {
      const container = document.getElementById('messages-container');
      if (!container) return;
      
      // Remove existing seen indicator
      const existingIndicator = container.querySelector('.message-seen-indicator');
      if (existingIndicator) {
        existingIndicator.remove();
      }
      
      // Add new one
      const seenDiv = document.createElement('div');
      seenDiv.className = 'message-seen-indicator flex justify-end my-1';
      seenDiv.innerHTML = `
        <span class="text-xs text-gray-400 flex items-center">
          <i class="fas fa-check-double mr-1"></i> Seen
        </span>
      `;
      
      container.appendChild(seenDiv);
      ChatUI.scrollToBottom();
    },
    
    /**
     * Clean up resources
     */
    cleanup: function() {
      if (this.updateBadgeInterval) {
        clearInterval(this.updateBadgeInterval);
      }
      
      if (this.pollingInterval) {
        clearInterval(this.pollingInterval);
      }
      
      // Remove chat container from DOM
      const chatContainer = document.getElementById('chat-container');
      if (chatContainer) {
        chatContainer.remove();
      }
    }
  };

  // Initialize when document is ready - only add listener if UserChat was just defined
  document.addEventListener('DOMContentLoaded', () => {
    // Don't initialize on admin pages
    if (!window.isAdminPage) {
      window.userChat = window.UserChat.init();
      
      // Clean up before page unload
      window.addEventListener('beforeunload', () => {
        if (window.userChat) {
          window.userChat.cleanup();
        }
      });
    }
  });
}
