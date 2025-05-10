/**
 * Chat Storage System
 * Uses server-side storage via AJAX calls to chat.php
 */

if (!window.ChatStorage) {
  window.ChatStorage = {
    /**
     * Initialize user ID - use session user_id or generate guest ID
     */
    initUserId: function() {
      // Check if we already have a guest user ID
      let guestId = localStorage.getItem('chat_guest_id');
      
      // If not, create one
      if (!guestId) {
        guestId = Math.floor(Math.random() * 1000000);
        localStorage.setItem('chat_guest_id', guestId);
      }
      
      return guestId;
    },
    
    /**
     * Save a message to storage via AJAX
     */
    saveMessage: function(userId, message) {
      // Determine sender and receiver based on message sender
      const isAdmin = message.sender === 'admin';
      const sender = isAdmin ? 'admin' : 'user';
      const receiver = isAdmin ? 'user' : 'admin';
      
      // Create form data
      const formData = new FormData();
      formData.append('content', message.text);
      formData.append('sender', sender);
      formData.append('receiver', receiver);
      formData.append('user_id', userId);
      
      // Return promise that resolves with saved message
      return fetch('/chat.php?action=send', {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        if (data.status === 'success') {
          // Create saved message object with real ID from server
          const savedMessage = {
            id: data.message_id, // Assuming server returns message_id
            text: message.text,
            sender: message.sender,
            timestamp: message.timestamp
          };
          
          // Dispatch event with saved message
          window.dispatchEvent(new CustomEvent('chat-message-received', { 
            detail: { userId, message: savedMessage } 
          }));
          
          return savedMessage;
        } else {
          throw new Error(data.message || 'Error saving message');
        }
      });
    },
    
    /**
     * Get messages for a user
     */
    getMessages: function(userId) {
      // Return a promise that resolves with messages
      return new Promise((resolve, reject) => {
        fetch(`/chat.php?action=getUserChats&user_id=${userId}`)
          .then(response => response.json())
          .then(data => {
            if (data.status === 'success') {
              // Transform the data to match our expected format
              const messages = data.chats.map(chat => ({
                id: chat.id,
                text: chat.content,
                sender: chat.sender === 'admin' ? 'admin' : 'user',
                timestamp: chat.created_at
              }));
              resolve(messages);
            } else {
              reject(new Error(data.message));
            }
          })
          .catch(error => {
            reject(error);
          });
      });
    },
    
    /**
     * Get all conversations (for admin panel)
     */
    getConversations: function() {
      return new Promise((resolve, reject) => {
        // First check if user is admin
        fetch('/check_session.php')
          .then(response => response.json())
          .then(session => {
            // Add admin token to request
            const url = `/chat.php?action=getAllChats${session.is_admin ? '&admin=true' : ''}`;
            return fetch(url);
          })
          .then(response => response.json())
          .then(data => {
            if (data.status === 'success') {
              // Group messages by user_id to create conversations
              const conversations = {};
              
              data.chats.forEach(chat => {
                const userId = chat.user_id;
                
                if (!conversations[userId]) {
                  // Create new conversation
                  conversations[userId] = {
                    userId: userId,
                    userName: chat.user_name || `User ${userId}`,
                    lastMessage: {
                      text: chat.content,
                      sender: chat.sender,
                      timestamp: chat.created_at
                    },
                    unread: chat.is_read ? 0 : 1
                  };
                } else {
                  // Update last message if this one is newer
                  const currentTimestamp = new Date(conversations[userId].lastMessage.timestamp);
                  const newTimestamp = new Date(chat.created_at);
                  
                  if (newTimestamp > currentTimestamp) {
                    conversations[userId].lastMessage = {
                      text: chat.content,
                      sender: chat.sender,
                      timestamp: chat.created_at
                    };
                  }
                  
                  // Increment unread count if message is not read
                  if (!chat.is_read && chat.sender !== 'admin') {
                    conversations[userId].unread++;
                  }
                }
              });
              
              resolve(conversations);
            } else {
              reject(new Error(data.message));
            }
          })
          .catch(error => {
            reject(error);
          });
      });
    },
    
    /**
     * Mark conversation as read
     */
    markConversationAsRead: function(userId) {
      // This would typically update a read status in the database
      // For now, we'll just notify listeners that the conversation was read
      window.dispatchEvent(new CustomEvent('chat-conversation-updated'));
    },
    
    /**
     * Get total unread count for admin
     */
    getTotalUnreadCount: function() {
      // Use the correct path - remove /admin/ prefix
      return fetch('/check_session.php')
        .then(response => response.json())
        .then(data => {
          // Process response
        })
        .catch(error => {
          console.error('Error checking session:', error);
          return 0;
        });
    }
  };
}
