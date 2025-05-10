<link href="/assets/css/style.css" rel="stylesheet">
<script src="https://www.gstatic.com/firebasejs/9.0.0/firebase-app-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/9.0.0/firebase-auth-compat.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/icontype/icontype.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css" />
<link type="text/css" rel="stylesheet" href="https://www.gstatic.com/firebasejs/ui/6.0.1/firebase-ui-auth.css" />
<script src="https://www.gstatic.com/firebasejs/ui/6.0.1/firebase-ui-auth.js"></script>
<!-- Ionicons -->
<script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
<script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
<!-- Custom Scripts -->
<script src="/assets/js/search.js" defer></script>
<!-- Smooth Navigation for guests -->
<script src="/assets/js/smooth-navigation.js" defer></script>
<!-- Chat System -->
<link href="/assets/css/chat.css" rel="stylesheet">
<script src="/assets/js/chat/index.js" defer></script>

<script>
// Add class to body to identify logged in users
document.addEventListener('DOMContentLoaded', function() {
    <?php if (isset($_SESSION['user_id'])): ?>
    document.body.classList.add('user-logged-in');
    <?php endif; ?>
});
</script>

