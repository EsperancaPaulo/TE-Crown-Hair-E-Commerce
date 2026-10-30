<?php
function productUpload($file, $existing=null) {
 if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) return $existing;
 if ($file['error'] !== UPLOAD_ERR_OK || $file['size'] > 5*1024*1024) throw new RuntimeException('Choose an image smaller than 5 MB.');
 $mime=(new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
 $types=['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];
 if (!isset($types[$mime]) || !getimagesize($file['tmp_name'])) throw new RuntimeException('Choose a valid JPG, PNG or WEBP image.');
 $name=bin2hex(random_bytes(16)).'.'.$types[$mime];
 if (!move_uploaded_file($file['tmp_name'],__DIR__.'/../assets/images/products/'.$name)) throw new RuntimeException('The image could not be saved.');
 return $name;
}
