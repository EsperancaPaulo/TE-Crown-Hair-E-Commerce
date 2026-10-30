<?php
require_once __DIR__.'/order-view.php';

function adminSidebar($section) {
    $items=['Dashboard'=>['index','▦'],'Products'=>['products','🛍'],'Categories'=>['categories','▤'],'Customers'=>['customers','👥'],'Orders'=>['orders','🛒'],'Enquiries'=>['enquiries','✉']]; ?>
    <aside class="admin-sidebar">
        <div class="admin-brand"><img src="/assets/images/logo/logo.png" alt="TE_Crown Hair Logo"><div><strong>TE_Crown Hair</strong><span>Admin Panel</span></div></div>
        <nav class="admin-navigation" aria-label="Administration">
            <?php foreach ($items as $label=>[$page,$icon]): ?><a href="<?php echo $page; ?>.php" class="admin-nav-link<?php echo $section===$label?' active':''; ?>" <?php echo $section===$label?'aria-current="page"':''; ?>><span aria-hidden="true"><?php echo $icon; ?></span><?php echo $label; ?></a><?php endforeach; ?>
        </nav>
        <div class="admin-sidebar-bottom"><a href="/index.php" class="admin-nav-link"><span aria-hidden="true">🌐</span>View Website</a><form action="../pages/logout.php" method="post"><?php echo csrfField(); ?><button class="admin-nav-link admin-logout" type="submit">Logout</button></form></div>
    </aside>
<?php }

function adminShellStart($section) {
    include __DIR__.'/header.php';
    echo '<div class="admin-layout">';
    adminSidebar($section);
    echo '<main class="admin-main"><div class="admin-topbar"><div class="admin-page-label">'.e(['Dashboard'=>'Store Overview','Products'=>'Product Management','Categories'=>'Category Management','Customers'=>'Customer Management','Orders'=>'Order Management','Enquiries'=>'Enquiry Management'][$section] ?? $section).'</div><div class="admin-user"><span>Administrator</span></div></div>';
}

function adminStart($title) {
    adminShellStart($title);
    echo '<div class="admin-page-header"><h1>'.e($title).'</h1></div>';
}

function adminEnd() {
    echo '</main></div>';
    include __DIR__.'/admin-footer.php';
}
