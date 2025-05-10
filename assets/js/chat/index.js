/**
 * Chat System Entry Point
 * Detects whether to load admin or user chat interface
 */

(function() {
  // Check if chat system is already initialized
  if (window.chatSystemInitialized) {
    console.log('Chat system already initialized, skipping.');
    return;
  }
  
  window.chatSystemInitialized = true;
  console.log('Initializing chat system...');
  
  // Create a style element for important CSS to ensure chat button displays correctly
  const styleElement = document.createElement('style');
  styleElement.textContent = `
    #chat-container {
      position: fixed !important;
      bottom: 20px !important;
      right: 20px !important;
      z-index: 10000 !important;
    }
    #chat-button {
      position: fixed !important;
      bottom: 20px !important;
      right: 20px !important;
      display: flex !important;
      visibility: visible !important;
      z-index: 10000 !important;
      width: 60px !important;
      height: 60px !important;
    }
    #chat-notification-badge {
      position: absolute !important;
      top: -5px !important;
      right: -5px !important;
    }
  `;
  document.head.appendChild(styleElement);
  
  // Load required scripts
  function loadScript(url, callback) {
    // Check if script already exists
    const existingScript = document.querySelector(`script[src="${url}"]`);
    if (existingScript) {
      console.log(`Script already loaded: ${url}`);
      if (callback) callback();
      return;
    }
    
    const script = document.createElement('script');
    script.type = 'text/javascript';
    script.src = url;
    
    // Handle callback after load
    script.onload = function() {
      console.log(`Loaded: ${url}`);
      if (callback) callback();
    };
    
    script.onerror = function() {
      console.error(`Error loading script: ${url}`);
    };
    
    document.head.appendChild(script);
  }
  
  // Determine if current page is admin
  function isAdminPage() {
    // Check if the current URL contains admin path
    return window.location.pathname.includes('/admin/');
  }
  
  // Set global flag for admin status
  window.isAdminPage = isAdminPage();
  console.log('Is admin page:', window.isAdminPage);
  
  // Initialize chat
  function initializeChat() {
    console.log('All chat scripts loaded, initializing UI');
    
    // Make sure DOM is fully loaded and ready
    if (document.readyState !== 'complete') {
      console.log('DOM not ready, waiting...');
      window.addEventListener('load', function() {
        console.log('DOM now ready, initializing chat');
        startChatInitialization();
      });
    } else {
      startChatInitialization();
    }
  }
  
  // Start chat initialization with proper delays
  function startChatInitialization() {
    // Delay initialization slightly to ensure DOM is fully ready
    setTimeout(function() {
      try {
        if (window.isAdminPage && window.AdminChat) {
          console.log('Initializing admin chat...');
          window.adminChat = window.AdminChat.init();
        } else if (window.UserChat) {
          console.log('Initializing user chat...');
          window.userChat = window.UserChat.init();
        } else {
          console.error('Chat modules not found');
        }
      } catch (error) {
        console.error('Error initializing chat:', error);
      }
    }, 1000); // Increased timeout for better reliability
  }
  
  // Load dependencies in sequence with better error handling
  function loadAllScripts() {
    loadScript('/assets/js/chat/storage.js', function() {
      loadScript('/assets/js/chat/ui.js', function() {
        if (window.isAdminPage) {
          loadScript('/assets/js/chat/admin.js', function() {
            console.log('Admin chat system loaded');
            initializeChat();
          });
        } else {
          loadScript('/assets/js/chat/user.js', function() {
            console.log('User chat system loaded');
            initializeChat();
          });
        }
      });
    });
  }

  // Make sure the DOM is at least in interactive state before proceeding
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', loadAllScripts);
  } else {
    loadAllScripts();
  }
})();
