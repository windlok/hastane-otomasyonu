<?php
$dil = isset($_GET['dil']) && in_array($_GET['dil'], ['tr', 'en'], true) ? $_GET['dil'] : 'tr';
setcookie('dil', $dil, time() + 86400 * 365, '/');
$geri = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'anasayfa.php';
header("location: $geri");
