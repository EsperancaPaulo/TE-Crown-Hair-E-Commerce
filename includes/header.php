<?php require_once __DIR__.'/auth.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>TE_Crown Hair</title>

    <!-- Bootstrap CSS -->
    <link
        href="/assets/vendor/bootstrap/bootstrap.min.css"
        rel="stylesheet"
    >

    <!-- Custom CSS -->
    <link rel="stylesheet" href="/assets/css/style.css">
<script src="/assets/js/cart-storage.js"></script>
<script>
// Discard prototype order history and clear a previous signed-in customer's cart.
try {
 const owner=<?php echo json_encode(isset($_SESSION['user_id']) ? (string)$_SESSION['user_id'] : 'guest'); ?>;
 const previous=localStorage.getItem('teCrownCartOwner');
 localStorage.removeItem('teCrownLastOrder');
 if (previous && previous!==owner && previous!=='guest') localStorage.removeItem('teCrownCart');
 localStorage.setItem('teCrownCartOwner',owner);
} catch (_) {}
window.addEventListener('pageshow',event=>{if(event.persisted)window.location.reload();});
</script>
</head>

<body>
