<?php
include 'header.php';
if (!isset($_SESSION['kullanici_rol']) || $_SESSION['kullanici_rol'] !== 'admin') {
    header('location:index.php');
    exit;
}

// İstatistikler
try {
    $toplam_kullanici = $db->query("SELECT COUNT(*) FROM kullanici WHERE rol='hasta'")->fetchColumn();
    $toplam_doktor = $db->query("SELECT COUNT(*) FROM kullanici WHERE rol='doktor'")->fetchColumn();
    $toplam_randevu = $db->query("SELECT COUNT(*) FROM randevu")->fetchColumn();
    $aktif_randevu = $db->query("SELECT COUNT(*) FROM randevu WHERE durum='aktif'")->fetchColumn();

    $kullanicilar = $db->query("SELECT kullanici_id, kullanici_tc, kullanici_adsoyad, kullanici_email, kullanici_telefon, rol, aktif, kayit_tarihi FROM kullanici ORDER BY kayit_tarihi DESC")->fetchAll(PDO::FETCH_ASSOC);

    $son_randevular = $db->query("SELECT r.*, k.kullanici_adsoyad FROM randevu r LEFT JOIN kullanici k ON r.kullanici_id = k.kullanici_id ORDER BY r.olusturma_tarihi DESC LIMIT 20")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $toplam_kullanici = $toplam_doktor = $toplam_randevu = $aktif_randevu = 0;
    $kullanicilar = [];
    $son_randevular = [];
}

$aktif_sekme = isset($_GET['tab']) ? $_GET['tab'] : 'dashboard';
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title><?php echo __('site_title'); ?> - <?php echo __('admin_panel'); ?></title>
    <style>
        .admin-tabs { display: flex; gap: 0; margin-bottom: 25px; border-radius: 8px; overflow: hidden; border: 1px solid var(--border); }
        .admin-tab { flex: 1; padding: 12px 20px; text-align: center; font-weight: 600; font-size: 14px; cursor: pointer; text-decoration: none; color: var(--text-muted); background: var(--card-bg); transition: var(--transition); }
        .admin-tab:hover { background: var(--hover-bg); }
        .admin-tab.active { background: var(--primary); color: #fff; }
        .user-avatar-sm { width: 32px; height: 32px; border-radius: 50%; background: var(--primary); color: #fff; display: inline-flex; align-items: center; justify-content: center; font-weight: 700; font-size: 13px; }
        .badge-role { display: inline-block; padding: 2px 10px; border-radius: 12px; font-size: 11px; font-weight: 600; }
        .badge-role.hasta { background: #e8f5e9; color: #2e7d32; }
        .badge-role.doktor { background: #e3f2fd; color: #1565c0; }
        .badge-role.admin { background: #fff3e0; color: #e65100; }
        .btn-sm { padding: 5px 12px; font-size: 12px; border-radius: 6px; cursor: pointer; border: none; font-weight: 600; transition: var(--transition); }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="page-container">
        <div class="page-header">
            <h2>🔧 <?php echo __('admin_panel'); ?></h2>
            <p><?php echo __('admin_panel_desc'); ?></p>
        </div>

        <?php if (isset($_GET['durum'])) mesaj_goster($_GET['durum']); ?>

        <!-- Admin Tabs -->
        <div class="admin-tabs">
            <a href="?tab=dashboard" class="admin-tab <?php echo $aktif_sekme === 'dashboard' ? 'active' : ''; ?>">📊 <?php echo __('genel_bakis'); ?></a>
            <a href="?tab=kullanicilar" class="admin-tab <?php echo $aktif_sekme === 'kullanicilar' ? 'active' : ''; ?>">👥 <?php echo __('kullanicilar_tab'); ?></a>
            <a href="?tab=randevular" class="admin-tab <?php echo $aktif_sekme === 'randevular' ? 'active' : ''; ?>">📅 <?php echo __('randevular_tab'); ?></a>
            <a href="?tab=doktor-ekle" class="admin-tab <?php echo $aktif_sekme === 'doktor-ekle' ? 'active' : ''; ?>">👨‍⚕️ <?php echo __('doktor_ekle'); ?></a>
        </div>

        <?php if ($aktif_sekme === 'dashboard'): ?>
        <!-- Dashboard -->
        <div class="stat-row" style="margin-bottom: 25px;">
            <div class="stat-card"><div class="stat-icon">👤</div><div class="stat-value"><?php echo $toplam_kullanici; ?></div><div class="stat-label"><?php echo __('hasta'); ?></div></div>
            <div class="stat-card"><div class="stat-icon">👨‍⚕️</div><div class="stat-value"><?php echo $toplam_doktor; ?></div><div class="stat-label"><?php echo __('doktor'); ?></div></div>
            <div class="stat-card"><div class="stat-icon">📅</div><div class="stat-value"><?php echo $toplam_randevu; ?></div><div class="stat-label"><?php echo __('toplam_randevu'); ?></div></div>
            <div class="stat-card"><div class="stat-icon">✅</div><div class="stat-value"><?php echo $aktif_randevu; ?></div><div class="stat-label"><?php echo __('aktif_randevu'); ?></div></div>
        </div>

        <div class="card">
            <h3 class="card-title"><span class="card-icon">📋</span> <?php echo __('son_randevular'); ?></h3>
            <div class="table-container">
                <table>
                    <thead><tr><th><?php echo __('hasta'); ?></th><th><?php echo __('doktor'); ?></th><th><?php echo __('klinik'); ?></th><th><?php echo __('tarih'); ?></th><th><?php echo __('saat'); ?></th><th><?php echo __('durum'); ?></th></tr></thead>
                    <tbody>
                        <?php if (empty($son_randevular)): ?>
                        <tr><td colspan="6" class="empty-state"><?php echo __('henuz_randevu_yok'); ?></td></tr>
                        <?php else: ?>
                        <?php foreach ($son_randevular as $r): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($r['kullanici_adsoyad'] ?? __('silinmis')); ?></td>
                            <td><?php echo htmlspecialchars($r['randevu_doktoru']); ?></td>
                            <td><?php echo htmlspecialchars($r['randevu_klinik']); ?></td>
                            <td><?php echo date('d.m.Y', strtotime($r['randevu_tarih'])); ?></td>
                            <td><?php echo $r['randevu_saat'] ? date('H:i', strtotime($r['randevu_saat'])) : '-'; ?></td>
                            <td><span style="color: <?php echo $r['durum'] === 'aktif' ? 'var(--success)' : '#e74c3c'; ?>; font-weight:600;"><?php echo __($r['durum'] ?? 'aktif'); ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php elseif ($aktif_sekme === 'kullanicilar'): ?>
        <!-- Kullanıcı Yönetimi -->
        <div class="card">
            <h3 class="card-title"><span class="card-icon">👥</span> <?php echo __('tum_kullanicilar'); ?></h3>
            <div class="table-container">
                <table>
                    <thead><tr><th><?php echo __('ad_soyad'); ?></th><th><?php echo __('tc_kimlik'); ?></th><th><?php echo __('eposta'); ?></th><th><?php echo __('telefon'); ?></th><th><?php echo __('rol'); ?></th><th><?php echo __('durum'); ?></th><th><?php echo __('islem'); ?></th></tr></thead>
                    <tbody>
                        <?php foreach ($kullanicilar as $k): 
                            $bas_harf = mb_substr($k['kullanici_adsoyad'], 0, 1, 'UTF-8');
                        ?>
                        <tr>
                            <td>
                                <div style="display:flex; align-items:center; gap:8px;">
                                    <span class="user-avatar-sm"><?php echo htmlspecialchars($bas_harf); ?></span>
                                    <strong><?php echo htmlspecialchars($k['kullanici_adsoyad']); ?></strong>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($k['kullanici_tc']); ?></td>
                            <td><?php echo htmlspecialchars($k['kullanici_email']); ?></td>
                            <td><?php echo htmlspecialchars($k['kullanici_telefon']); ?></td>
                            <td><span class="badge-role <?php echo $k['rol']; ?>"><?php echo ucfirst($k['rol']); ?></span></td>
                            <td><span style="color: <?php echo $k['aktif'] ? 'var(--success)' : '#e74c3c'; ?>; font-weight:600;"><?php echo $k['aktif'] ? __('aktif') : __('pasif'); ?></span></td>
                            <td>
                                <?php if ($k['rol'] !== 'admin'): ?>
                                <form action="islem.php" method="post" style="display:inline;">
                                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                    <input type="hidden" name="admin_kullanici_id" value="<?php echo $k['kullanici_id']; ?>">
                                    <button type="submit" name="admin_kullanici_toggle" class="btn-sm" style="background:<?php echo $k['aktif'] ? '#fee' : '#e8f5e9'; ?>; color:<?php echo $k['aktif'] ? '#c62828' : '#2e7d32'; ?>;">
                                        <?php echo $k['aktif'] ? __('pasif_yap') : __('aktif_yap'); ?>
                                    </button>
                                </form>
                                <?php else: ?>
                                <span style="color:var(--text-muted);font-size:12px;">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php elseif ($aktif_sekme === 'randevular'): ?>
        <!-- Tüm Randevular -->
        <div class="card">
            <h3 class="card-title"><span class="card-icon">📅</span> <?php echo __('tum_randevular'); ?></h3>
            <div class="table-container">
                <table>
                    <thead><tr><th><?php echo __('hasta'); ?></th><th><?php echo __('doktor'); ?></th><th><?php echo __('klinik'); ?></th><th><?php echo __('tarih'); ?></th><th><?php echo __('saat'); ?></th><th><?php echo __('durum'); ?></th></tr></thead>
                    <tbody>
                        <?php 
                        try {
                            $tum_randevular = $db->query("SELECT r.*, k.kullanici_adsoyad FROM randevu r LEFT JOIN kullanici k ON r.kullanici_id = k.kullanici_id ORDER BY r.olusturma_tarihi DESC")->fetchAll(PDO::FETCH_ASSOC);
                            if (empty($tum_randevular)): ?>
                            <tr><td colspan="6" class="empty-state"><?php echo __('henuz_randevu_yok'); ?></td></tr>
                            <?php else: ?>
                            <?php foreach ($tum_randevular as $r): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($r['kullanici_adsoyad'] ?? __('silinmis')); ?></td>
                                <td><?php echo htmlspecialchars($r['randevu_doktoru']); ?></td>
                                <td><?php echo htmlspecialchars($r['randevu_klinik']); ?></td>
                                <td><?php echo date('d.m.Y', strtotime($r['randevu_tarih'])); ?></td>
                                <td><?php echo $r['randevu_saat'] ? date('H:i', strtotime($r['randevu_saat'])) : '-'; ?></td>
                                <td><span style="color: <?php echo $r['durum'] === 'aktif' ? 'var(--success)' : '#e74c3c'; ?>; font-weight:600;"><?php echo __($r['durum'] ?? 'aktif'); ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; 
                        } catch (PDOException $e) {
                            echo "<tr><td colspan='6' class='empty-state'>" . __('veri_yukleme_hatasi') . "</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php elseif ($aktif_sekme === 'doktor-ekle'): ?>
        <!-- Doktor Ekleme Formu -->
        <div class="dashboard-grid">
            <div class="card">
                <h3 class="card-title"><span class="card-icon">👨‍⚕️</span> <?php echo __('yeni_doktor_ekle'); ?></h3>
                <form action="islem.php" method="post">
                    <?php echo csrf_input(); ?>
                    <div class="form-group">
                        <label for="adsoyad"><?php echo __('ad_soyad'); ?></label>
                        <input type="text" id="adsoyad" name="kullanici_adsoyad" class="form-control" placeholder="Dr. Ad Soyad" required>
                    </div>
                    <div class="form-group">
                        <label for="tc"><?php echo __('tc_kimlik'); ?></label>
                        <input type="text" id="tc" name="kullanici_tc" class="form-control" placeholder="11 Haneli TC" maxlength="11" required pattern="\d{11}">
                    </div>
                    <div class="form-group">
                        <label for="email"><?php echo __('eposta'); ?></label>
                        <input type="email" id="email" name="kullanici_email" class="form-control" placeholder="doktor@hastane.com" required>
                    </div>
                    <div class="form-group">
                        <label for="telefon"><?php echo __('telefon'); ?></label>
                        <input type="text" id="telefon" name="kullanici_telefon" class="form-control" placeholder="05XX XXX XX XX" required>
                    </div>
                    <div class="form-group">
                        <label for="sifre"><?php echo __('sifre'); ?> (<?php echo __('en_az_6_karakter'); ?>)</label>
                        <input type="password" id="sifre" name="kullanici_password" class="form-control" placeholder="Varsayılan şifre" required minlength="6">
                    </div>
                    <hr style="border-color: var(--border); margin: 20px 0;">
                    <div class="form-group">
                        <label for="sehir"><?php echo __('sehir'); ?></label>
                        <input type="text" id="sehir" name="doktor_sehir" class="form-control" placeholder="İstanbul" required>
                    </div>
                    <div class="form-group">
                        <label for="hastane"><?php echo __('hastane'); ?></label>
                        <input type="text" id="hastane" name="doktor_hastane" class="form-control" placeholder="Özel Hastane" required>
                    </div>
                    <div class="form-group">
                        <label for="klinik"><?php echo __('klinik'); ?></label>
                        <input type="text" id="klinik" name="doktor_klinik" class="form-control" placeholder="Kardiyoloji" required>
                    </div>
                    <button type="submit" class="btn btn-primary" name="admin_doktor_ekle"><?php echo __('doktor_olustur'); ?></button>
                </form>
            </div>
            <div>
                <div class="card">
                    <h3 class="card-title"><span class="card-icon">ℹ️</span> <?php echo __('bilgilendirme'); ?></h3>
                    <ul class="info-list">
                        <li><?php echo __('doktor_ekle_bilgi1'); ?></li>
                        <li><?php echo __('doktor_ekle_bilgi2'); ?></li>
                        <li><?php echo __('doktor_ekle_bilgi3'); ?></li>
                    </ul>
                </div>
            </div>
        </div>
        <?php endif; ?>

    </div>
</body>
</html>
