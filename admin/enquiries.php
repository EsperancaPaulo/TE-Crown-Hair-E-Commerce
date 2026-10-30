<?php
require_once '../includes/auth.php';
requireAdmin();
require_once '../includes/db.php';
require_once '../includes/enquiries.php';
require_once '../includes/admin-layout.php';
$labels=enquiryStatuses();
$error='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    checkCsrf();
    try {
        $id=filter_var($_POST['id'] ?? null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
        $revision=filter_var($_POST['revision'] ?? null,FILTER_VALIDATE_INT,['options'=>['min_range'=>0]]);
        $status=enquiryText('status',20);
        $reply=enquiryText('reply',5000,false);
        $key=enquiryRequestKey();
        if (!$id || $revision===false || $revision===null || !isset($labels[$status])) throw new InvalidArgumentException('Choose a valid enquiry and status.');
        mysqli_report(MYSQLI_REPORT_ERROR|MYSQLI_REPORT_STRICT);
        $conn->begin_transaction();
        $stmt=$conn->prepare('SELECT * FROM enquiries WHERE id=? FOR UPDATE');
        $stmt->bind_param('i',$id);$stmt->execute();$current=$stmt->get_result()->fetch_assoc();
        if (!$current) throw new InvalidArgumentException('Enquiry not found.');
        $stmt=$conn->prepare('SELECT enquiry_id FROM enquiry_replies WHERE request_key=?');
        $stmt->bind_param('s',$key);$stmt->execute();$previous=$stmt->get_result()->fetch_assoc();
        if ($previous) {
            if ((int)$previous['enquiry_id']!==$id) throw new InvalidArgumentException('Please reload the form and try again.');
            $conn->commit();header('Location: enquiries.php?id='.$id,true,303);exit;
        }
        if ((int)$current['revision']!==$revision) throw new InvalidArgumentException('This enquiry has changed. Review the latest conversation below before submitting again.');
        if ($status==='replied' && $reply==='') {
            $stmt=$conn->prepare('SELECT id FROM enquiry_replies WHERE enquiry_id=? LIMIT 1');$stmt->bind_param('i',$id);$stmt->execute();
            if (!$stmt->get_result()->num_rows) throw new InvalidArgumentException('Write a response before marking this enquiry Replied.');
        }
        if ($reply!=='') {
            $stmt=$conn->prepare('INSERT INTO enquiry_replies(enquiry_id,admin_id,message,request_key) VALUES (?,?,?,?)');
            $stmt->bind_param('iiss',$id,$_SESSION['user_id'],$reply,$key);$stmt->execute();
        }
        $stmt=$conn->prepare('UPDATE enquiries SET status=?,revision=revision+1,updated_at=UTC_TIMESTAMP() WHERE id=?');
        $stmt->bind_param('si',$status,$id);$stmt->execute();
        $conn->commit();
        header('Location: enquiries.php?id='.$id.'&saved=1',true,303);exit;
    } catch (InvalidArgumentException $exception) { try {$conn->rollback();}catch(Throwable $ignored){} http_response_code(422);$error=$exception->getMessage(); }
    catch (Throwable $exception) { try {$conn->rollback();}catch(Throwable $ignored){} http_response_code(503);$error='The update could not be saved. Please try again.'; }
}
$id=filter_var($_GET['id'] ?? $_POST['id'] ?? 0,FILTER_VALIDATE_INT,['options'=>['min_range'=>0]]) ?: 0;
$enquiry=null;
if ($id) {
    $stmt=$conn->prepare('SELECT * FROM enquiries WHERE id=?');$stmt->bind_param('i',$id);$stmt->execute();$enquiry=$stmt->get_result()->fetch_assoc();
    if (!$enquiry) {http_response_code(404);exit('Enquiry not found.');}
}
adminStart('Enquiries'); ?>
<p class="admin-page-intro">Customer questions, progress and responses in one place. Replies are saved to the customer’s account; no email is sent.</p>
<?php if ($error): ?><p class="enquiry-notice enquiry-error" role="alert"><?php echo e($error); ?></p><?php endif; ?>
<?php if (isset($_GET['saved'])): ?><p class="enquiry-notice" role="status"><?php echo $enquiry && $enquiry['user_id']===null ? 'Guest enquiry updated. Any saved response is internal only; no email has been sent.' : 'Enquiry updated. The customer can see this response and status in their account.'; ?></p><?php endif; ?>
<?php if ($enquiry): ?>
<p><a class="admin-text-link" href="enquiries.php">All enquiries</a></p>
<section class="admin-panel enquiry-admin-detail"><header class="admin-enquiry-heading"><div><p class="account-eyebrow">ENQUIRY #EQ<?php echo $id; ?></p><h2><?php echo e($enquiry['subject']); ?></h2></div><span class="account-status enquiry-status-<?php echo e($enquiry['status']); ?>"><?php echo e($labels[$enquiry['status']]); ?></span></header>
<p class="admin-enquiry-contact"><?php echo e($enquiry['name'].' · '.$enquiry['email']); ?></p>
<?php if ($enquiry['user_id']===null): ?><p class="enquiry-notice">Guest enquiry. This record is not linked to an account. Responses saved here are internal only; the guest cannot read them online, and no email is sent. Arrange any direct response separately using the contact details they supplied.</p><?php endif; ?>
<p class="account-enquiry-date">Received <?php echo e(enquiryDate($enquiry['created_at'])); ?> · Updated <?php echo e(enquiryDate($enquiry['updated_at'])); ?> SAST</p>
<article class="enquiry-message enquiry-message-customer"><header><strong>Original message</strong></header><p><?php echo e($enquiry['message']); ?></p></article>
<?php foreach (enquiryReplies($conn,$id) as $reply): ?><article class="enquiry-message enquiry-message-admin"><header><strong>TE_Crown Hair response</strong><time><?php echo e(enquiryDate($reply['created_at'])); ?> SAST</time></header><p><?php echo e($reply['message']); ?></p></article><?php endforeach; ?>
<form method="post" class="enquiry-response-form"><?php echo csrfField(); ?><input type="hidden" name="id" value="<?php echo $id; ?>"><input type="hidden" name="revision" value="<?php echo (int)$enquiry['revision']; ?>"><input type="hidden" name="request_key" value="<?php echo bin2hex(random_bytes(32)); ?>">
<label for="reply">Response message <span>(optional for status-only updates)</span></label><textarea id="reply" name="reply" class="form-control" maxlength="5000" rows="5" aria-describedby="replyHelp"><?php echo e(is_string($_POST['reply'] ?? null) ? $_POST['reply'] : ''); ?></textarea><small id="replyHelp">Up to 5,000 characters. <?php echo $enquiry['user_id']===null ? 'Guest replies are internal only.' : 'Visible only to this customer and authorised administrators.'; ?></small>
<label for="enquiryStatus">Enquiry status</label><select id="enquiryStatus" name="status" class="form-control"><?php foreach ($labels as $key=>$label): ?><option value="<?php echo $key; ?>" <?php echo $key===$enquiry['status']?'selected':''; ?>><?php echo e($label); ?></option><?php endforeach; ?></select><button type="submit" class="admin-edit-button">Save response and status</button></form></section>
<?php else:
$filter=is_string($_GET['status'] ?? null) ? $_GET['status'] : '';
if ($filter!=='' && !isset($labels[$filter])) $filter='';
$page=filter_var($_GET['page'] ?? 1,FILTER_VALIDATE_INT,['options'=>['min_range'=>1,'max_range'=>100000]]) ?: 1;$offset=($page-1)*30;
$stmt=$conn->prepare("SELECT * FROM enquiries WHERE (?='' OR status=?) ORDER BY id DESC LIMIT 31 OFFSET ?");$stmt->bind_param('ssi',$filter,$filter,$offset);$stmt->execute();$items=$stmt->get_result()->fetch_all(MYSQLI_ASSOC);$hasNext=count($items)>30;$items=array_slice($items,0,30);
?>
<section class="admin-panel"><form method="get" class="admin-enquiry-filter"><label for="statusFilter">Filter by status</label><select id="statusFilter" name="status"><option value="">All enquiries</option><?php foreach ($labels as $key=>$label): ?><option value="<?php echo $key; ?>" <?php echo $key===$filter?'selected':''; ?>><?php echo e($label); ?></option><?php endforeach; ?></select><button class="admin-edit-button" type="submit">Apply filter</button></form>
<div class="admin-table-wrapper" tabindex="0" aria-label="Data table; scroll horizontally to view all columns on small screens"><table class="admin-table"><thead><tr><th>Reference / subject</th><th>From</th><th>Status</th><th>Updated (SAST)</th><th>Action</th></tr></thead><tbody>
<?php if (!$items): ?><tr><td colspan="5" class="admin-empty-cell">No enquiries match this view. New Contact messages will appear here.</td></tr><?php endif; ?>
<?php foreach ($items as $item): ?><tr><td><strong>#EQ<?php echo (int)$item['id']; ?></strong><span class="admin-cell-note"><?php echo e($item['subject']); ?></span></td><td><?php echo e($item['name']); ?><span class="admin-cell-note"><?php echo $item['user_id']===null?'Guest · not linked to an account':'Registered customer'; ?></span></td><td><span class="account-status enquiry-status-<?php echo e($item['status']); ?>"><?php echo e($labels[$item['status']]); ?></span></td><td><?php echo e(enquiryDate($item['updated_at'])); ?></td><td><a class="admin-edit-button" href="enquiries.php?id=<?php echo (int)$item['id']; ?>">View / respond</a></td></tr><?php endforeach; ?></tbody></table></div>
<nav class="enquiry-pagination" aria-label="Enquiry pages"><?php if ($page>1): ?><a href="?page=<?php echo $page-1; ?>&amp;status=<?php echo e($filter); ?>">Previous page</a><?php endif; ?><span>Page <?php echo $page; ?></span><?php if ($hasNext): ?><a href="?page=<?php echo $page+1; ?>&amp;status=<?php echo e($filter); ?>">Next page</a><?php endif; ?></nav></section>
<?php endif; adminEnd(); ?>
