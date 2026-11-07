<?php
// Run inside the local wordpress container; quarantines only HTML masquerading as images.
require '/var/www/html/wp-load.php';
if (!defined('RM_LOCAL_PREVIEW') || RM_LOCAL_PREVIEW !== true || wp_get_environment_type() !== 'local') {
    throw new RuntimeException('Local preview only.');
}
$root = realpath(wp_upload_dir()['basedir']);
if (!$root) { throw new RuntimeException('Uploads directory missing.'); }
$quarantine = '/tmp/renomacs-invalid-media-' . gmdate('YmdHis');
$count = 0;
$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS));
foreach ($files as $file) {
    if ($file->isLink() || !$file->isFile()) { continue; }
    $source = $file->getRealPath();
    if (strpos($source, $root . DIRECTORY_SEPARATOR) !== 0) { throw new RuntimeException('Unexpected file path.'); }
    $head = file_get_contents($source, false, null, 0, 512);
    if (!preg_match('/<!doctype html|<html/i', $head)) { continue; }
    $target = $quarantine . substr($source, strlen($root));
    if (!is_dir(dirname($target))) { mkdir(dirname($target), 0700, true); }
    if (!rename($source, $target)) { throw new RuntimeException('Could not quarantine invalid download.'); }
    $count++;
}
echo 'Quarantined HTML responses: ' . $count . PHP_EOL;
