<?php
session_start();
if (isset($_SESSION['kullanici_rol']) && $_SESSION['kullanici_rol'] === 'admin') {
    header('location:../admin_panel.php');
    exit;
}

global $lang;
$dil = isset($_COOKIE['dil']) && in_array($_COOKIE['dil'], ['tr','en'], true) ? $_COOKIE['dil'] : 'tr';
$dil_dosyasi = __DIR__ . "/../lang/$dil.php";
if (file_exists($dil_dosyasi)) {
    include $dil_dosyasi;
} else {
    include __DIR__ . "/../lang/tr.php";
}
function __($key) {
    global $lang;
    return isset($lang[$key]) ? $lang[$key] : $key;
}

$hata = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admin_giris'])) {
    $username = isset($_POST['kullanici_adi']) ? trim($_POST['kullanici_adi']) : '';
    $password = isset($_POST['sifre']) ? $_POST['sifre'] : '';

    if (!$username || !$password) {
        $hata = __('admin_giris_hata_bos');
    } else {
        try {
            $db = new PDO('mysql:host=127.0.0.1;port=3306;dbname=hastane_otomasyonu;charset=utf8mb4', 'root', '', [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);

            $sorgu = $db->prepare("SELECT * FROM kullanici WHERE kullanici_adi = ? AND rol = 'admin' AND aktif = 1");
            $sorgu->execute([$username]);
            $kullanici = $sorgu->fetch();

            if ($kullanici && password_verify($password, $kullanici['kullanici_password'])) {
                session_regenerate_id(true);
                $_SESSION['kullanici_tc'] = $kullanici['kullanici_tc'];
                $_SESSION['kullanici_adsoyad'] = $kullanici['kullanici_adsoyad'];
                $_SESSION['kullanici_rol'] = 'admin';
                header('location:../admin_panel.php');
                exit;
            } else {
                $hata = __('admin_giris_hata_yanlis');
            }
        } catch (PDOException $e) {
            $hata = __('sistem_hatasi');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo __('admin_giris'); ?> - <?php echo __('site_title'); ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #0f1923; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .login-card { background: #1a2332; border-radius: 16px; padding: 40px; width: 100%; max-width: 400px; box-shadow: 0 20px 60px rgba(0,0,0,0.5); }
        .logo { text-align: center; margin-bottom: 30px; }
        .logo-icon { font-size: 48px; }
        .logo h1 { color: #fff; font-size: 20px; margin-top: 10px; }
        .logo p { color: #8899aa; font-size: 13px; margin-top: 4px; }
        .form-group { margin-bottom: 18px; }
        label { display: block; color: #c0ccda; font-size: 12px; font-weight: 600; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.5px; }
        input { width: 100%; padding: 12px 14px; background: #0f1923; border: 1px solid #2a3a4a; border-radius: 8px; color: #fff; font-size: 14px; outline: none; transition: border 0.2s; }
        input:focus { border-color: #4a9eff; }
        .btn { width: 100%; padding: 13px; background: #4a9eff; border: none; border-radius: 8px; color: #fff; font-size: 15px; font-weight: 700; cursor: pointer; transition: background 0.2s; margin-top: 8px; }
        .btn:hover { background: #3a7ecc; }
        .error { background: rgba(255,60,60,0.15); color: #ff6b6b; padding: 10px 14px; border-radius: 8px; font-size: 13px; margin-bottom: 18px; text-align: center; border: 1px solid rgba(255,60,60,0.2); }
        .footer { text-align: center; margin-top: 24px; }
        .footer a { color: #4a9eff; font-size: 13px; text-decoration: none; }
        .footer a:hover { text-decoration: underline; }
        .divider { border: none; border-top: 1px solid #2a3a4a; margin: 22px 0; }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="logo">
            <div class="logo-icon">🔧</div>
            <h1><?php echo __('admin_paneli_baslik'); ?></h1>
            <p><?php echo __('admin_yonetim'); ?></p>
        </div>

        <?php if ($hata): ?>
        <div class="error"><?php echo htmlspecialchars($hata); ?></div>
        <?php endif; ?>

        <form method="post">
            <div class="form-group">
                <label for="kullanici_adi"><?php echo __('kullanici_adi'); ?></label>
                <input type="text" id="kullanici_adi" name="kullanici_adi" placeholder="<?php echo __('admin_giris_placeholder_kadi'); ?>" required autocomplete="off">
            </div>
            <div class="form-group">
                <label for="sifre"><?php echo __('sifre'); ?></label>
                <input type="password" id="sifre" name="sifre" placeholder="<?php echo __('admin_giris_placeholder_sifre'); ?>" required>
            </div>
            <button type="submit" name="admin_giris" class="btn"><?php echo __('giris_yap'); ?></button>
        </form>

        <hr class="divider">
        <div class="footer">
            <a href="../index.php">← <?php echo __('ana_giris_don'); ?></a>
        </div>
    </div>
</body>
</html>
