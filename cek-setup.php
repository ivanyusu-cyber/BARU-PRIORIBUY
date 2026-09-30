<?php
/**
 * Halaman uji setup (sementara). Memeriksa koneksi database, ekstensi PHP, dan mengirim
 * SATU email tes ke ADMIN_EMAIL. Dilindungi SETUP_KEY di private/config.php.
 * >>> HAPUS FILE INI dari hosting setelah setup selesai. <<<
 */
declare(strict_types=1);

require __DIR__ . '/private/bootstrap.php';
require __DIR__ . '/private/mailer.php';
pb_start_session();
pb_no_cache();

$setupKey = (string) pb_config('SETUP_KEY', '');
$enabled  = !pb_is_placeholder($setupKey);
$message  = '';
$checks   = [];

if ($enabled && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $given = $_POST['setup_key'] ?? '';
    if (!pb_csrf_valid()) {
        $message = 'Sesi formulir kedaluwarsa. Muat ulang halaman.';
    } elseif (!is_string($given) || !hash_equals($setupKey, $given)) {
        usleep(700000);
        $message = 'Kunci setup salah.';
    } else {
        // 1) database + tabel
        try {
            $pdo = pb_db_connect();
            $pdo->query('SELECT COUNT(*) FROM transactions')->fetchColumn();
            $checks[] = [true, 'Database & tabel transactions', 'Terhubung dan tabel ditemukan.'];
        } catch (Throwable $e) {
            error_log('PrioriBuy cek-setup DB: ' . $e->getMessage());
            $checks[] = [false, 'Database & tabel transactions', 'Gagal. Periksa DB_HOST, DB_NAME, DB_USER, DB_PASS di private/config.php dan pastikan schema.sql sudah diimpor.'];
        }
        // 2) ekstensi
        $checks[] = [function_exists('curl_init'), 'Ekstensi cURL (untuk driver apps_script)', function_exists('curl_init') ? 'Tersedia.' : 'Tidak tersedia.'];
        $checks[] = [extension_loaded('openssl'), 'Ekstensi OpenSSL (untuk driver smtp)', extension_loaded('openssl') ? 'Tersedia.' : 'Tidak tersedia.'];
        // 3) email tes ke admin
        $admin = (string) pb_config('ADMIN_EMAIL', '');
        if (pb_is_placeholder($admin)) {
            $checks[] = [false, 'Email tes', 'ADMIN_EMAIL belum diisi.'];
        } else {
            $stamp = pb_format_dt(pb_now());
            $text  = "Ini adalah email tes dari sistem PrioriBuy.\nDriver: " . (string) pb_config('MAIL_DRIVER', '') . "\nWaktu: " . $stamp . "\n";
            $html  = pb_email_html('Tes Email PrioriBuy', 'Ini adalah email tes dari sistem PrioriBuy.', [['Driver', (string) pb_config('MAIL_DRIVER', '')], ['Waktu', $stamp]], 'Jika email ini masuk, pengiriman email server-side sudah berfungsi.');
            [$ok, $err] = pb_send_mail($admin, 'Tes Email PrioriBuy', $text, $html, null);
            $checks[] = [$ok, 'Email tes ke ADMIN_EMAIL', $ok ? 'Terkirim. Periksa inbox (dan folder spam) di ' . $admin . '.' : 'Gagal: ' . $err];
        }
    }
}

$pageTitle = 'Cek Setup — PrioriBuy';
$pageDesc  = 'Halaman uji setup sementara.';
$navActive = '';
$noindex   = true;
require __DIR__ . '/private/partials/head.php';
?>
<section class="page-hero">
  <div class="container">
    <h1>Cek Setup</h1>
    <p class="lead-text">Halaman sementara untuk menguji database dan pengiriman email. Hapus file <b>cek-setup.php</b> setelah selesai.</p>
  </div>
</section>

<section class="section-pad" style="padding-top:40px; background:#fff;">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-7 col-md-10">
        <div class="form-card">
          <?php if (!$enabled): ?>
            <div class="alert-pb alert-pb--info"><i class="bi bi-info-circle"></i><span>Halaman ini nonaktif. Isi <b>SETUP_KEY</b> di private/config.php untuk mengaktifkannya.</span></div>
          <?php else: ?>
            <?php if ($message !== ''): ?>
              <div class="alert-pb alert-pb--error" role="alert"><i class="bi bi-exclamation-circle"></i><span><?= pb_h($message) ?></span></div>
            <?php endif; ?>

            <?php foreach ($checks as $c): ?>
              <div class="alert-pb <?= $c[0] ? 'alert-pb--ok' : 'alert-pb--error' ?>">
                <i class="bi <?= $c[0] ? 'bi-check-circle' : 'bi-x-circle' ?>"></i>
                <span><b><?= pb_h($c[1]) ?></b><br><?= pb_h($c[2]) ?></span>
              </div>
            <?php endforeach; ?>

            <form method="post" action="cek-setup.php" autocomplete="off">
              <?= pb_csrf_field() ?>
              <label class="form-label-pb" for="setup_key">Kunci setup</label>
              <input class="form-control-pb" type="password" id="setup_key" name="setup_key" required>
              <p class="form-hint">Nilai SETUP_KEY dari private/config.php. Email tes hanya dikirim ke ADMIN_EMAIL.</p>
              <button class="btn btn-pb-primary w-100 mt-3" type="submit">Jalankan Pemeriksaan</button>
            </form>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</section>
<?php require __DIR__ . '/private/partials/foot.php'; ?>
