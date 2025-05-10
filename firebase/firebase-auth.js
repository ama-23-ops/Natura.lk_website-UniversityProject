// Initialize Firebase
firebase.initializeApp(firebaseConfig);

// FirebaseUI config for email/password sign-in
var uiConfig = {
  signInSuccessUrl: 'index.php', // Redirect to home page after successful login
  signInOptions: [
    firebase.auth.EmailAuthProvider.PROVIDER_ID
  ],
};

// Initialize the FirebaseUI Widget using Firebase
var ui = new firebaseui.auth.AuthUI(firebase.auth());
// The start method will wait until the DOM is loaded.
ui.start('#firebaseui-auth-container', uiConfig);