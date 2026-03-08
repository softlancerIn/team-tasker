// Scripts for firebase and firebase messaging
importScripts('https://www.gstatic.com/firebasejs/9.0.0/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/9.0.0/firebase-messaging-compat.js');

// Initialize the Firebase app in the service worker by passing in the
// messagingSenderId.
// These values will be filled dynamically if we use a better approach, 
// but for now, we expect them to be set or the user can manually edit this file.
firebase.initializeApp({
    apiKey: "AIzaSyCjrfTpZpDvdmKHfuCeaI20dGxdyJBvLxA",
    authDomain: "team-tasker-1.firebaseapp.com",
    projectId: "team-tasker-1",
    storageBucket: "team-tasker-1.firebasestorage.app",
    messagingSenderId: "834885945870",
    appId: "1:834885945870:web:7a50a34b2db67287e50a5f",
    measurementId: "G-15LNT6DJ3C"
});

const messaging = firebase.messaging();

messaging.onBackgroundMessage(function (payload) {
    console.log('[firebase-messaging-sw.js] Received background message ', payload);
    // Customize notification here
    const notificationTitle = payload.notification.title;
    const notificationOptions = {
        body: payload.notification.body,
        icon: '/images/logo.png' // Adjust path to your logo
    };

    self.registration.showNotification(notificationTitle,
        notificationOptions);
});
