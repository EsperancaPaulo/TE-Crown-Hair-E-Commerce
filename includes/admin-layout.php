<?php
function adminStart($title) {
 include __DIR__.'/header.php';
 echo '<div class="admin-layout"><aside class="admin-sidebar"><div class="admin-brand"><img src="/assets/images/logo/logo.png" alt="TE_Crown Hair"><h2>TE_Crown Hair</h2></div><nav class="admin-navigation" aria-label="Administration">';
 foreach (['index'=>'Dashboard','products'=>'Products','categories'=>'Categories','customers'=>'Customers','orders'=>'Orders'] as $page=>$label) echo '<a class="admin-nav-link" href="'.$page.'.php">'.$label.'</a>';
 echo '<a class="admin-nav-link" href="../index.php">View website</a></nav><form action="../pages/logout.php" method="post">'.csrfField().'<button class="logout-button" type="submit">LOGOUT</button></form></aside><main class="admin-main"><h1>'.e($title).'</h1>';
}
function adminEnd() { echo '</main></div>'; include __DIR__.'/footer.php'; }
