<?php
require_once '../includes/auth.php'; requireAdmin();
require_once '../includes/db.php'; require_once '../includes/enquiries.php'; require_once '../includes/admin-layout.php';
$error='';$editing=null;$labels=['added'=>'Category added.','updated'=>'Category updated.','deleted'=>'Category deleted.'];
if ($_SERVER['REQUEST_METHOD']==='POST') {
    checkCsrf();
    try {
        $action=enquiryText('action',10);
        $id=filter_var($_POST['categoryId'] ?? 0,FILTER_VALIDATE_INT,['options'=>['min_range'=>0]]);
        if (!in_array($action,['add','edit','delete'],true) || ($action!=='add' && !$id)) throw new InvalidArgumentException('Choose a valid category action.');
        if ($action!=='add') {
            $stmt=$conn->prepare('SELECT * FROM categories WHERE id=?');$stmt->bind_param('i',$id);$stmt->execute();$editing=$stmt->get_result()->fetch_assoc();
            if (!$editing) throw new InvalidArgumentException('Category not found.');
        }
        if ($action==='delete') {
            $stmt=$conn->prepare('DELETE FROM categories WHERE id=?');$stmt->bind_param('i',$id);
            if (!$stmt->execute()) throw new InvalidArgumentException($stmt->errno===1451?'This category contains products. Move those products before deleting it.':'The category could not be deleted.');
            header('Location: categories.php?success=deleted',true,303);exit;
        }
        $name=enquiryText('categoryName',100);$description=enquiryText('description',2000,false);
        if ($action==='add') { $stmt=$conn->prepare('INSERT INTO categories(name,description) VALUES (?,?)');$stmt->bind_param('ss',$name,$description); }
        else { $stmt=$conn->prepare('UPDATE categories SET name=?,description=? WHERE id=?');$stmt->bind_param('ssi',$name,$description,$id); }
        if (!$stmt->execute()) throw new InvalidArgumentException($stmt->errno===1062?'This category already exists.':'The category could not be saved.');
        header('Location: categories.php?success='.($action==='add'?'added':'updated'),true,303);exit;
    } catch (InvalidArgumentException $exception) {http_response_code(422);$error=$exception->getMessage();}
    catch (Throwable $exception) {http_response_code(503);$error='The category could not be saved. Please try again.';}
}
$editId=filter_var($_GET['edit'] ?? 0,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]) ?: 0;
if ($editId) {$stmt=$conn->prepare('SELECT * FROM categories WHERE id=?');$stmt->bind_param('i',$editId);$stmt->execute();$editing=$stmt->get_result()->fetch_assoc();if (!$editing) {http_response_code(404);exit('Category not found.');}}
$name=is_string($_POST['categoryName'] ?? null) ? $_POST['categoryName'] : ($editing['name'] ?? '');
$description=is_string($_POST['description'] ?? null) ? $_POST['description'] : ($editing['description'] ?? '');
$categories=$conn->query('SELECT c.*,COUNT(p.id) product_count FROM categories c LEFT JOIN products p ON p.category_id=c.id GROUP BY c.id ORDER BY c.name')->fetch_all(MYSQLI_ASSOC);
$productCount=array_sum(array_column($categories,'product_count'));
adminStart('Categories'); ?>
<p class="admin-page-intro">Organise your collection so customers can find their next favourite.</p>
<div class="admin-summary-strip"><div><span>Product categories</span><strong><?php echo count($categories); ?></strong></div><div><span>Products organised</span><strong><?php echo $productCount; ?></strong></div></div>
<?php if ($error): ?><p class="enquiry-notice enquiry-error" role="alert"><?php echo e($error); ?></p><?php endif; ?>
<?php $success=is_string($_GET['success'] ?? null) ? ($labels[$_GET['success']] ?? '') : ''; if ($success): ?><p class="enquiry-notice" role="status"><?php echo e($success); ?></p><?php endif; ?>
<div class="admin-category-grid">
<section class="admin-panel admin-category-form" aria-labelledby="categoryFormHeading"><p class="account-eyebrow">COLLECTION SETTINGS</p><h2 id="categoryFormHeading"><?php echo $editing?'Edit category':'Add a category'; ?></h2><p>Give your category a clear name and a short description.</p><form method="post"><?php echo csrfField(); ?><input type="hidden" name="action" value="<?php echo $editing?'edit':'add'; ?>"><input type="hidden" name="categoryId" value="<?php echo (int)($editing['id'] ?? 0); ?>"><label for="categoryName">Category name</label><input id="categoryName" name="categoryName" class="form-control" maxlength="100" required value="<?php echo e($name); ?>"><label for="categoryDescription">Description <span>(optional)</span></label><textarea id="categoryDescription" name="description" class="form-control" maxlength="2000" rows="5"><?php echo e($description); ?></textarea><button class="admin-edit-button" type="submit"><?php echo $editing?'Save category':'Add category'; ?></button><?php if ($editing): ?><a class="admin-text-link" href="categories.php">Cancel editing</a><?php endif; ?></form></section>
<section class="admin-panel admin-category-list" aria-labelledby="categoryListHeading"><header class="admin-enquiry-heading"><div><p class="account-eyebrow">YOUR CATALOGUE</p><h2 id="categoryListHeading">All categories</h2></div><span class="account-order-count"><?php echo count($categories); ?> total</span></header><div class="admin-table-wrapper" tabindex="0" aria-label="Data table; scroll horizontally to view all columns on small screens"><table class="admin-table"><thead><tr><th>Category</th><th>Products</th><th>Actions</th></tr></thead><tbody>
<?php if (!$categories): ?><tr><td colspan="3" class="admin-empty-cell">No categories yet. Add your first category to start organising products.</td></tr><?php endif; ?>
<?php foreach ($categories as $category): ?><tr><td><strong><?php echo e($category['name']); ?></strong><span class="admin-cell-note"><?php echo e($category['description'] ?? ''); ?></span></td><td><span class="account-order-count"><?php echo (int)$category['product_count']; ?> products</span></td><td><div class="admin-category-actions"><a class="admin-edit-button" href="categories.php?edit=<?php echo (int)$category['id']; ?>#categoryFormHeading">Edit</a><form method="post"><?php echo csrfField(); ?><input type="hidden" name="action" value="delete"><input type="hidden" name="categoryId" value="<?php echo (int)$category['id']; ?>"><button class="admin-delete-button" type="submit" <?php echo $category['product_count']?'disabled title="Move products before deleting this category"':''; ?>>Delete</button></form></div></td></tr><?php endforeach; ?></tbody></table></div><p class="admin-form-hint">Categories containing products are protected from deletion.</p></section></div>
<?php adminEnd(); ?>
