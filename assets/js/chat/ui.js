/**
 * Chat UI Component
 * Handles rendering of chat interfaces
 */

// Only define ChatUI if it doesn't exist yet
if (!window.ChatUI) {
  window.ChatUI = {
    /**
     * Initialize the chat UI
     */
    init: function (isAdmin = false) {
      console.log('Initializing ChatUI, isAdmin:', isAdmin);
      this.isAdmin = isAdmin;

      // First, remove any existing chat elements to prevent duplicates
      this.removeExistingChatElements();

      // Create chat container with only the button initially
      console.log('Creating chat container');
      this.createChatContainer();

      // Set user ID and then continue initialization
      if (isAdmin) {
        this.userId = 'admin'; // Admin has fixed ID
        this.continueInit();
      } else {
        // Get user ID properly and wait for it to resolve
        this.getUserId().then(id => {
          this.userId = id;
          this.continueInit();
        }).catch(error => {
          console.error('Error getting user ID:', error);
          // Fallback to localStorage
          let userId = localStorage.getItem('chat_guest_id');
          if (!userId) {
            userId = Math.floor(Math.random() * 1000000);
            localStorage.setItem('chat_guest_id', userId);
          }
          this.userId = userId;
          this.continueInit();
        });
      }
    },

    /**
     * Continue initialization after user ID is set
     */
    continueInit: function () {
      // Create the chat button initially
      this.createChatButton();
      
      // Set up event listeners only after elements are created
      setTimeout(() => {
        try {
          this.setupEventListeners();
          console.log('Event listeners set up');
          
          // Update notification badge
          this.updateNotificationBadge();
          
          // If admin, update conversation list
          if (this.isAdmin) {
            this.updateConversationList();
          }
        } catch (error) {
          console.error('Error in continueInit:', error);
        }
      }, 300); // Increased timeout
    },

    /**
     * Remove any existing chat elements
     */
    removeExistingChatElements: function () {
      const existingContainer = document.getElementById('chat-container');
      if (existingContainer) {
        console.log('Removing existing chat container');
        existingContainer.remove();
      }
    },

    /**
     * Ensure the chat button is visible
     */
    ensureChatButtonIsVisible: function () {
      const chatButton = document.getElementById('chat-button');
      if (chatButton) {
        console.log('Making chat button visible');
        // Make sure button is visible and properly positioned
        chatButton.style.display = 'flex';
        chatButton.style.visibility = 'visible';
        chatButton.style.zIndex = '10000';
        chatButton.style.position = 'fixed';
        chatButton.style.bottom = '20px';
        chatButton.style.right = '20px';
      } else {
        console.error('Chat button not found');
      }
    },

    /**
     * Get or create a user ID
     */
    getUserId: function () {
      // First try to get ID from session
      return fetch('check_session.php')
        .then(response => response.json())
        .then(data => {
          if (data.user_id) {
            return data.user_id; // Use session user ID
          } else {
            // Use or create guest ID from localStorage
            let userId = localStorage.getItem('chat_guest_id');
            if (!userId) {
              // Generate a random ID for the user
              userId = Math.floor(Math.random() * 1000000);
              localStorage.setItem('chat_guest_id', userId);
            }
            return userId;
          }
        })
        .catch(error => {
          console.error('Error getting user ID:', error);
          // Fallback to localStorage
          let userId = localStorage.getItem('chat_guest_id');
          if (!userId) {
            userId = Math.floor(Math.random() * 1000000);
            localStorage.setItem('chat_guest_id', userId);
          }
          return userId;
        });
    },

    /**
     * Create the chat container
     */
    createChatContainer: function () {
      const chatContainer = document.createElement('div');
      chatContainer.id = 'chat-container';
      chatContainer.className = 'fixed bottom-4 right-4 z-50 flex flex-col items-end';

      // Apply inline styles for stronger positioning
      chatContainer.style.position = 'fixed';
      chatContainer.style.bottom = '20px';
      chatContainer.style.right = '20px';
      chatContainer.style.zIndex = '10000';
      chatContainer.style.display = 'flex';
      chatContainer.style.flexDirection = 'column';
      chatContainer.style.alignItems = 'flex-end';
      chatContainer.style.width = 'auto';
      chatContainer.style.height = 'auto';

      // Append to body
      document.body.appendChild(chatContainer);
      console.log('Chat container added to DOM');
    },

    /**
     * Create chat button (only when panel is not visible)
     */
    createChatButton: function() {
      // Check if button already exists or if panel is visible
      const existingButton = document.getElementById('chat-button');
      const existingPanel = document.getElementById('chat-panel');
      
      if (existingButton || existingPanel) {
        // Don't create button if it exists or if panel is visible
        return null;
      }
      
      const container = document.getElementById('chat-container');
      if (!container) return null;
      
      // Create new button
      const chatButton = document.createElement('button');
      chatButton.id = 'chat-button';
      chatButton.className = 'chat-button bg-teal-600 text-white rounded-full w-14 h-14 flex items-center justify-center shadow-lg hover:bg-teal-700 transition-all duration-300 relative';

      // Apply inline styles for stronger positioning
      chatButton.style.width = '60px';
      chatButton.style.height = '60px';
      chatButton.style.display = 'flex';
      chatButton.style.backgroundColor = '#0d9488';
      chatButton.style.borderRadius = '50%';

      chatButton.setAttribute('type', 'button');
      chatButton.innerHTML = `
        <i class="fas fa-comments text-xl"></i>
        <span id="chat-notification-badge" class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center opacity-0 transition-opacity duration-300">0</span>
      `;
      
      // Set up click event for the button
      chatButton.addEventListener('click', () => {
        this.toggleChatPanel(true);
      });

      container.appendChild(chatButton);
      console.log('Chat button created and added to DOM');
      return chatButton;
    },
    
    /**
     * Remove chat button from DOM
     */
    removeChatButton: function() {
      const button = document.getElementById('chat-button');
      if (button) {
        button.remove();
        console.log('Chat button removed from DOM');
      }
    },

    /**
     * Create chat panel - only when needed
     */
    createChatPanel: function() {
      // Check if panel already exists
      const existingPanel = document.getElementById('chat-panel');
      if (existingPanel) return existingPanel;
      
      const container = document.getElementById('chat-container');
      if (!container) return null;
      
      // Create new panel
      const chatPanel = document.createElement('div');
      chatPanel.id = 'chat-panel';
      chatPanel.className = 'bg-white rounded-lg shadow-xl w-80 md:w-96 overflow-hidden mb-4 transition-all duration-300 transform origin-bottom-right';
      
      container.appendChild(chatPanel);

      // Initialize panel content based on user type
      if (this.isAdmin) {
        chatPanel.classList.add('admin-panel', 'md:w-[750px]');
        this.initAdminPanel();
      } else {
        this.initUserChatPanel(chatPanel);
      }
      
      console.log('Chat panel created and added to DOM');
      return chatPanel;
    },

    /**
     * Remove chat panel from DOM
     */
    removeChatPanel: function() {
      const panel = document.getElementById('chat-panel');
      if (panel) {
        panel.remove();
        console.log('Chat panel removed from DOM');
      }
    },

    /**
     * Initialize the user chat panel
     */
    initUserChatPanel: function (panel) {
      panel.innerHTML = `
        <div class="bg-teal-600 text-white p-4 flex justify-between items-center">
          <div class="flex items-center">
            <div class="w-8 h-8 rounded-full bg-teal-100 flex items-center justify-center mr-3">
              <i class="fas fa-headset text-teal-600"></i>
            </div>
            <div>
              <h3 class="font-bold">Customer Support</h3>
              <div class="text-xs flex items-center">
                <span class="inline-block w-2 h-2 bg-green-400 rounded-full mr-1"></span>
                Online
              </div>
            </div>
          </div>
          <button id="close-chat" class="text-white hover:text-gray-200">
            <i class="fas fa-times"></i>
          </button>
        </div>
        
        <div id="messages-container" class="p-4 h-80 overflow-y-auto bg-gray-50">
          <div class="flex justify-center mb-4">
            <span class="bg-gray-200 text-gray-500 text-xs px-3 py-1 rounded-full">Today</span>
          </div>
          <div class="admin-message mb-4">
            <div class="flex items-start">
              <div class="w-8 h-8 rounded-full bg-teal-100 flex-shrink-0 flex items-center justify-center">
                <i class="fas fa-headset text-teal-600 text-sm"></i>
              </div>
              <div class="ml-2 bg-white rounded-lg p-3 shadow-sm max-w-[80%]">
                <p>Hello! How can I help you today?</p>
              </div>
            </div>
          </div>
        </div>
        
        <div class="p-4 border-t border-gray-200">
          <form id="chat-form" class="flex">
            <input 
              type="text" 
              id="message-input" 
              placeholder="Type a message..." 
              class="flex-1 border border-gray-300 rounded-l-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500"
              required
            >
            <button 
              type="submit" 
              class="bg-teal-600 text-white px-4 py-2 rounded-r-lg hover:bg-teal-700 transition-colors"
            >
              <i class="fas fa-paper-plane"></i>
            </button>
          </form>
        </div>
      `;
      
      // Set up form submission handler
      const chatForm = panel.querySelector('#chat-form');
      if (chatForm) {
        chatForm.addEventListener('submit', this.handleMessageSubmit.bind(this));
      }
      
      // Set up close button handler
      const closeButton = panel.querySelector('#close-chat');
      if (closeButton) {
        closeButton.addEventListener('click', () => {
          this.toggleChatPanel(false);
        });
      }
    },

    /**
     * Initialize admin panel
     */
    initAdminPanel: function () {
      const panel = document.getElementById('chat-panel');
      if (!panel) {
        console.error('Chat panel not found');
        return;
      }
    
      panel.innerHTML = `
        <div class="admin-chat-container flex h-[500px]">
          <!-- Left side: Conversation list -->
          <div class="conversation-sidebar w-1/3 border-r border-gray-200">
            <div class="bg-teal-600 text-white p-4">
              <h3 class="font-bold">Conversations</h3>
            </div>
            <div id="conversation-list" class="h-[calc(500px-57px)] overflow-y-auto">
              <!-- Conversations will be loaded here -->
              <div class="p-4 text-center text-gray-500">
                <i class="fas fa-spinner fa-spin mr-2"></i> Loading conversations...
              </div>
            </div>
          </div>
          
          <!-- Right side: Chat messages -->
          <div class="chat-content w-2/3 flex flex-col">
            <div class="bg-teal-600 text-white p-4 flex justify-between items-center">
              <h3 id="active-chat-name" class="font-bold">Select a conversation</h3>
              <button id="close-admin-chat" class="text-white hover:text-gray-200">
                <i class="fas fa-times"></i>
              </button>
            </div>
            
            <div id="admin-messages-container" class="flex-grow p-4 overflow-y-auto bg-gray-50 h-[calc(500px-125px)]">
              <div class="flex items-center justify-center h-full text-gray-500">
                <p>Select a conversation from the list</p>
              </div>
            </div>
            
            <div class="p-4 border-t border-gray-200">
              <form id="admin-chat-form" class="flex">
                <input 
                  type="text" 
                  id="admin-message-input" 
                  placeholder="Type a message..." 
                  class="flex-1 border border-gray-300 rounded-l-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500"
                  required
                  disabled
                >
                <button 
                  type="submit" 
                  class="bg-teal-600 text-white px-4 py-2 rounded-r-lg hover:bg-teal-700 transition-colors disabled:bg-gray-400"
                  disabled
                >
                  <i class="fas fa-paper-plane"></i>
                </button>
              </form>
            </div>
          </div>
        </div>
      `;
      
      // Set up form submission handler
      const adminForm = panel.querySelector('#admin-chat-form');
      if (adminForm) {
        adminForm.addEventListener('submit', this.handleMessageSubmit.bind(this));
      }
      
      // Set up close button handler
      const closeButton = panel.querySelector('#close-admin-chat');
      if (closeButton) {
        closeButton.addEventListener('click', () => {
          this.toggleChatPanel(false);
        });
      }
    
      // Give DOM time to update before trying to access elements
      setTimeout(() => {
        this.updateConversationList();
      }, 100);
    },

    /**
     * Initialize user chat interface
     */
    initUserChat: function () {
      // Load conversation history
      this.loadMessages(this.userId);

      // Update unread count
      this.updateNotificationBadge();
    },

    /**
     * Set up event listeners
     */
    setupEventListeners: function () {
      // No need to set up chat button event listener here
      // It's now added when the button is created
    },

    /**
     * Toggle the chat panel
     */
    toggleChatPanel: function (show = null) {
      console.log('Toggling chat panel, show:', show);

      const panel = document.getElementById('chat-panel');
      const button = document.getElementById('chat-button');
      
      const isCurrentlyVisible = panel !== null;
      const shouldShow = show !== null ? show : !isCurrentlyVisible;

      console.log('Current visibility:', isCurrentlyVisible, 'Should show:', shouldShow);

      if (shouldShow) {
        // First, remove the chat button
        this.removeChatButton();
        
        // Then create the panel if it doesn't exist
        console.log('Creating and showing chat panel');
        const panel = this.createChatPanel();

        // Mark as read when opening
        if (!this.isAdmin) {
          ChatStorage.markConversationAsRead(this.userId);
          this.updateNotificationBadge();
        }

        // Initialize panel content if needed
        if (!this.isAdmin) {
          this.loadMessages(this.userId);
        }

        // Scroll to bottom of messages
        setTimeout(() => {
          this.scrollToBottom();
        }, 300);
      } else {
        // First, remove the panel
        console.log('Removing chat panel');
        this.removeChatPanel();
        
        // Then create the button
        this.createChatButton();
      }
    },

    /**
     * Check if chat is currently visible
     */
    isChatVisible: function () {
      const panel = document.getElementById('chat-panel');
      return panel !== null;
    },

    /**
     * Add a message to the UI for user chat
     */
    addMessageToUI: function (text, sender, messageId) {
      const messagesContainer = document.getElementById('messages-container');
      if (!messagesContainer) return; // Add this check to prevent errors if container not found
      
      const messageDiv = document.createElement('div');
      messageDiv.className = `${sender}-message mb-4 ${sender === 'user' ? 'flex justify-end' : ''}`;
      messageDiv.dataset.messageId = messageId;

      if (sender === 'user') {
        messageDiv.innerHTML = `
          <div class="bg-teal-500 text-white rounded-lg p-3 shadow-sm max-w-[80%]">
            <p>${this.escapeHtml(text)}</p>
          </div>
        `;
      } else {
        messageDiv.innerHTML = `
          <div class="flex items-start">
            <div class="w-8 h-8 rounded-full bg-teal-100 flex-shrink-0 flex items-center justify-center">
              <i class="fas fa-headset text-teal-600 text-sm"></i>
            </div>
            <div class="ml-2 bg-white rounded-lg p-3 shadow-sm max-w-[80%]">
              <p>${this.escapeHtml(text)}</p>
            </div>
          </div>
        `;
      }

      messagesContainer.appendChild(messageDiv);
      this.scrollToBottom();
    },

    /**
     * Add a message to the admin UI
     */
    addAdminMessageToUI: function (text, sender, messageId) {
      const messagesContainer = document.getElementById('admin-messages-container');
      if (!messagesContainer) return; // Add this check to prevent errors if container not found
      
      const messageDiv = document.createElement('div');
      messageDiv.className = `${sender}-message mb-4 ${sender === 'admin' ? 'flex justify-end' : ''}`;
      messageDiv.dataset.messageId = messageId;

      if (sender === 'admin') {
        messageDiv.innerHTML = `
          <div class="bg-teal-500 text-white rounded-lg p-3 shadow-sm max-w-[80%]">
            <p>${this.escapeHtml(text)}</p>
          </div>
        `;
      } else {
        messageDiv.innerHTML = `
          <div class="flex items-start">
            <div class="w-8 h-8 rounded-full bg-blue-100 flex-shrink-0 flex items-center justify-center">
              <i class="fas fa-user text-blue-600 text-sm"></i>
            </div>
            <div class="ml-2 bg-white rounded-lg p-3 shadow-sm max-w-[80%]">
              <p>${this.escapeHtml(text)}</p>
            </div>
          </div>
        `;
      }

      messagesContainer.appendChild(messageDiv);
      this.scrollToBottom('admin-messages-container');
    },

    /**
     * Load messages for a conversation
     */
    loadMessages: function (userId) {
      const container = this.isAdmin
        ? document.getElementById('admin-messages-container')
        : document.getElementById('messages-container');

      // Keep track of existing message IDs
      const existingMessageIds = new Set(
        Array.from(container.querySelectorAll('[data-message-id]'))
          .map(el => el.dataset.messageId)
      );

      // If first load, clear container and show loading
      if (existingMessageIds.size === 0) {
        container.innerHTML = `
          <div class="text-center p-4 text-gray-500" id="loading-messages">
            <i class="fas fa-spinner fa-spin mr-2"></i> Loading messages...
          </div>
        `;
      }

      // Fetch messages
      ChatStorage.getMessages(userId)
        .then(messages => {
          // Remove loading indicator if it exists
          document.getElementById('loading-messages')?.remove();

          if (messages.length === 0 && !this.isAdmin && existingMessageIds.size === 0) {
            // Add welcome message for empty user chat
            return;
          }

          // Add date separator if first load
          if (existingMessageIds.size === 0 && messages.length > 0) {
            const dateDiv = document.createElement('div');
            dateDiv.className = 'flex justify-center mb-4';
            dateDiv.innerHTML = `
              <span class="bg-gray-200 text-gray-500 text-xs px-3 py-1 rounded-full">Today</span>
            `;
            container.appendChild(dateDiv);
          }

          // Add only new messages
          messages.forEach(message => {
            if (!existingMessageIds.has(message.id.toString())) {
              if (this.isAdmin) {
                this.addAdminMessageToUI(message.text, message.sender, message.id);
              } else {
                this.addMessageToUI(message.text, message.sender, message.id);
              }
            }
          });
        })
        .catch(error => {
          console.error('Error loading messages:', error);
          if (existingMessageIds.size === 0) {
            container.innerHTML = `
              <div class="text-center p-4 text-red-500">
                <i class="fas fa-exclamation-circle mr-2"></i> 
                Error loading messages. Please try again.
              </div>
            `;
          }
        });
    },

    /**
     * Update the conversation list for admin panel
     */
    updateConversationList: function () {
      if (!this.isAdmin) return;

      const container = document.getElementById('conversation-list');
      
      // Add null check
      if (!container) {
        console.error('Conversation list container not found');
        return;
      }

      // Remember the active userId for highlighting
      const activeUserId = this.activeUserId;

      // Show loading indicator only on first load
      if (!this.conversationsLoaded) {
        container.innerHTML = `
          <div id="conversations-loading" class="p-4 text-center text-gray-500">
            <i class="fas fa-spinner fa-spin mr-2"></i> Loading conversations...
          </div>
        `;
      }

      ChatStorage.getConversations()
        .then(conversations => {
          // Mark that we've loaded conversations at least once
          this.conversationsLoaded = true;
          
          // Remove loading indicator if it exists
          const loadingEl = document.getElementById('conversations-loading');
          if (loadingEl) loadingEl.remove();

          if (Object.keys(conversations).length === 0) {
            // If no conversations and container is empty or only has the "No conversations" message, show the message
            if (container.children.length === 0 || 
                (container.children.length === 1 && container.firstChild.textContent.includes('No active conversations'))) {
              container.innerHTML = `
                <div id="no-conversations" class="p-4 text-center text-gray-500">
                  No active conversations
                </div>
              `;
            }
            return;
          }

          // Remove the "no conversations" message if it exists
          const noConversationsEl = document.getElementById('no-conversations');
          if (noConversationsEl) noConversationsEl.remove();

          // Keep track of conversation elements that need to be kept
          const currentConvIds = new Set();
          
          // Sort conversations by timestamp (most recent first)
          const sortedConversations = Object.values(conversations).sort((a, b) => {
            return new Date(b.lastMessage.timestamp) - new Date(a.lastMessage.timestamp);
          });

          // Add or update each conversation
          sortedConversations.forEach(conv => {
            const convId = `conversation-${conv.userId}`;
            currentConvIds.add(convId);
            
            // Check if conversation element already exists
            let convEl = document.getElementById(convId);
            const isActive = activeUserId === conv.userId;
            
            // Format timestamp
            const timestamp = this.formatTimestamp(conv.lastMessage.timestamp);
            
            // Display user name or ID
            const displayName = conv.userName || `User ${conv.userId}`;
            
            // Create the inner HTML content
            const innerContent = `
              <div class="flex items-center justify-between">
                <div class="flex items-center">
                  <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center mr-3">
                    <i class="fas fa-user text-blue-600"></i>
                  </div>
                  <div>
                    <h4 class="font-semibold text-gray-800">${displayName}</h4>
                    <p class="text-sm text-gray-500 truncate max-w-[150px]">
                      ${conv.lastMessage.sender === 'admin' ? 'You: ' : ''}${this.escapeHtml(conv.lastMessage.text)}
                    </p>
                  </div>
                </div>
                <div class="flex flex-col items-end">
                  <span class="text-xs text-gray-400">${timestamp}</span>
                  ${conv.unread > 0 ? `<span class="bg-teal-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center mt-1">${conv.unread}</span>` : ''}
                </div>
              </div>
            `;
            
            if (convEl) {
              // Update existing conversation element
              if (convEl.innerHTML !== innerContent) {
                convEl.innerHTML = innerContent;
              }
              
              // Update active state class
              const activeClass = 'bg-teal-50 border-l-4 border-l-teal-500';
              if (isActive && !convEl.classList.contains('bg-teal-50')) {
                convEl.classList.add(...activeClass.split(' '));
              } else if (!isActive && convEl.classList.contains('bg-teal-50')) {
                convEl.classList.remove(...activeClass.split(' '));
              }
            } else {
              // Create new conversation element if it doesn't exist
              convEl = document.createElement('div');
              convEl.id = convId;
              convEl.className = `p-3 border-b border-gray-100 hover:bg-gray-50 cursor-pointer transition-colors ${isActive ? 'bg-teal-50 border-l-4 border-l-teal-500' : ''}`;
              convEl.dataset.userId = conv.userId;
              convEl.innerHTML = innerContent;
              
              // Add click event
              convEl.addEventListener('click', () => {
                this.openAdminConversation(conv.userId);
              });
              
              container.appendChild(convEl);
            }
          });

          // Remove conversation elements that no longer exist
          Array.from(container.children).forEach(child => {
            // Skip non-conversation elements
            if (!child.id || !child.id.startsWith('conversation-')) return;
            
            if (!currentConvIds.has(child.id)) {
              child.remove();
            }
          });
          
          // Sort the DOM elements to match the sorted conversations
          sortedConversations.forEach(conv => {
            const convEl = document.getElementById(`conversation-${conv.userId}`);
            if (convEl) {
              container.appendChild(convEl); // This moves it to the end in the right order
            }
          });
        })
        .catch(error => {
          console.error('Error loading conversations:', error);
          // Only show error if we haven't loaded conversations yet
          if (!this.conversationsLoaded) {
            container.innerHTML = `
              <div class="p-4 text-center text-red-500">
                <i class="fas fa-exclamation-circle mr-2"></i> 
                Error loading conversations
              </div>
            `;
          }
        });
    },

    /**
     * Open a conversation in the admin panel
     */
    openAdminConversation: function (userId) {
      this.activeUserId = userId;

      // Update header
      document.getElementById('active-chat-name').textContent = `User ${userId}`;

      // Enable input
      const input = document.getElementById('admin-message-input');
      const button = input.nextElementSibling;
      input.disabled = false;
      button.disabled = false;

      // Mark as read
      ChatStorage.markConversationAsRead(userId);

      // Load messages
      this.loadMessages(userId);

      // Update conversation list to reflect read status
      this.updateConversationList();
    },

    /**
     * Update notification badge
     */
    updateNotificationBadge: function () {
      const badge = document.getElementById('chat-notification-badge');
      if (!badge) return; // Badge might not exist if button is not in the DOM
      
      if (this.isAdmin) {
        // For admin, get total unread count
        ChatStorage.getTotalUnreadCount().then(unreadCount => {
          if (unreadCount > 0) {
            badge.textContent = unreadCount > 9 ? '9+' : unreadCount;
            badge.classList.remove('opacity-0');
          } else {
            badge.classList.add('opacity-0');
          }
        }).catch(error => {
          console.error('Error getting unread count:', error);
          badge.classList.add('opacity-0');
        });
      } else {
        // For users, check if there are unread messages using the getMessages function
        ChatStorage.getMessages(this.userId).then(messages => {
          // Count admin messages that might be unread
          const adminMessages = messages.filter(m => m.sender === 'admin');
          const hasUnread = adminMessages.length > 0 && !this.isChatVisible();

          if (hasUnread) {
            badge.textContent = adminMessages.length > 9 ? '9+' : adminMessages.length;
            badge.classList.remove('opacity-0');
          } else {
            badge.classList.add('opacity-0');
          }
        }).catch(error => {
          console.error('Error getting messages:', error);
          badge.classList.add('opacity-0');
        });
      }
    },

    /**
     * Format timestamp
     */
    formatTimestamp: function (timestamp) {
      const date = new Date(timestamp);
      const now = new Date();
      const diffMs = now - date;
      const diffMins = Math.floor(diffMs / 60000);
      const diffHours = Math.floor(diffMins / 60);

      if (diffMins < 1) {
        return 'Just now';
      } else if (diffMins < 60) {
        return `${diffMins}m ago`;
      } else if (diffHours < 24) {
        return `${diffHours}h ago`;
      } else {
        return date.toLocaleDateString();
      }
    },

    /**
     * Escape HTML to prevent XSS
     */
    escapeHtml: function (text) {
      const div = document.createElement('div');
      div.textContent = text;
      return div.innerHTML;
    },

    /**
     * Scroll to bottom of messages container
     */
    scrollToBottom: function (containerId = 'messages-container') {
      const container = document.getElementById(containerId);
      if (container) {
        container.scrollTop = container.scrollHeight;
      }
    },

    /**
     * Handle message submission from the chat form
     * @param {Event} e - Form submit event
     */
    handleMessageSubmit: function (e) {
      e.preventDefault();
      const messageInput = document.querySelector(this.isAdmin ? '#admin-message-input' : '#message-input');
      const message = messageInput.value.trim();

      if (!message) return;

      // Clear input
      messageInput.value = '';

      // Get the appropriate user ID
      const userId = this.isAdmin ? this.activeUserId : this.userId;

      // Don't proceed if we're in admin mode but no user is selected
      if (this.isAdmin && !userId) {
        console.warn('No user selected in admin mode');
        return;
      }

      // Create message object with temporary ID
      const tempId = 'temp_' + Date.now();
      const messageObj = {
        id: tempId,
        text: message,
        sender: this.isAdmin ? 'admin' : 'user',
        timestamp: new Date().toISOString()
      };

      // Add message to UI with temporary ID - Make sure we use the right function based on admin status
      if (this.isAdmin) {
        this.addAdminMessageToUI(messageObj.text, messageObj.sender, tempId);
      } else {
        this.addMessageToUI(messageObj.text, messageObj.sender, tempId);
      }

      // Save message to storage and update with real ID
      ChatStorage.saveMessage(userId, messageObj)
        .then(savedMessage => {
          // If we get a real ID back, update the message element's data-message-id
          if (savedMessage && savedMessage.id) {
            const messageEl = document.querySelector(`[data-message-id="${tempId}"]`);
            if (messageEl) {
              messageEl.dataset.messageId = savedMessage.id;
            }
          }
        })
        .catch(error => {
          console.error('Error saving message:', error);
        });

      // Scroll to bottom - use the appropriate container ID
      this.scrollToBottom(this.isAdmin ? 'admin-messages-container' : 'messages-container');
    }
  };
}

// Export the ChatUI object
window.ChatUI = ChatUI;
