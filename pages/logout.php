<?php
require_once '../includes/auth.php';
if ($_SERVER['REQUEST_METHOD']==='POST') {
 checkCsrf(); header('Clear-Site-Data: "cache", "storage"'); $_SESSION=[];
 setcookie(session_name(),'', ['expires'=>time()-3600,'path'=>'/','httponly'=>true,'samesite'=>'Lax']);
 session_destroy(); header('Location: login.php'); exit;
}
include '../includes/header.php'; include '../includes/navbar.php';
?>
<main class="container py-5"><h1>Log out</h1><form method="post"><?php echo csrfField(); ?><button class="logout-button" type="submit">LOGOUT</button></form></main>
<?php include '../includes/footer.php'; ?>
