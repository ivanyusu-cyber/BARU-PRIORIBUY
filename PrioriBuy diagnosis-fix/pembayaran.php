<?php
/* PrioriBuy: halaman pembayaran manual QRIS, menggunakan email dari sesi server. */
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_name('PBSESSID');
    if (PHP_VERSION_ID >= 70300) {
        session_set_cookie_params(array('lifetime' => 7200, 'path' => '/', 'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'), 'httponly' => true, 'samesite' => 'Lax'));
    }
    session_start();
}
if (!function_exists('pb_h')) {
    function pb_h($value) { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
}
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('X-Robots-Tag: noindex, nofollow');
$email = isset($_SESSION['pb_registration_email']) && is_string($_SESSION['pb_registration_email']) ? $_SESSION['pb_registration_email'] : '';
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: mulai-premium.php', true, 303);
    exit;
}
$pageTitle = 'Pembayaran PrioriBuy — PrioriBuy';
$pageDesc = 'Scan QRIS untuk membayar PrioriBuy, lalu konfirmasi lewat WhatsApp admin.';
$navActive = 'download';
$noindex = true;
$pageScripts = array('assets/js/premium-flow.js');
require __DIR__ . '/private/partials/head.php';
?>
<section class="page-hero"><div class="container"><div class="eyebrow eyebrow--pink reveal"><span class="dot"></span>Pembayaran</div><h1 class="reveal reveal-delay-1">PrioriBuy</h1><p class="lead-text reveal reveal-delay-2"><span class="price-inline">Rp49.000</span></p></div></section>
<section class="section-pad" style="padding-top:40px; background:#fff;"><div class="container"><div class="row justify-content-center gy-4">
<div class="col-lg-5 col-md-9"><div class="summary-card reveal"><h5 class="mb-3">Ringkasan Pembayaran</h5><div class="summary-row"><span class="k">Produk</span><span class="v">PrioriBuy</span></div><div class="summary-row"><span class="k">Nominal</span><span class="v">Rp49.000</span></div><div class="summary-row"><span class="k">Email</span><span class="v" id="sumEmail"><?php echo pb_h($email); ?></span></div><div class="summary-row"><span class="k">Status</span><span class="v"><span class="status-pill"><i class="bi bi-hourglass-split"></i>Menunggu Pembayaran</span></span></div><p class="form-hint mt-3">Email salah? <a href="mulai-premium.php" style="color:var(--blue-deep);font-weight:600;">Ubah email</a>.</p><hr><h6 class="mb-2">Langkah-langkah</h6><ol class="ps-3 mb-0" style="font-size:0.9rem;"><li>Scan QRIS dan transfer tepat <b>Rp49.000</b>.</li><li>Simpan screenshot bukti transfer.</li><li>Tekan tombol konfirmasi di bawah QRIS untuk membuka WhatsApp admin.</li><li>Lengkapi nama dan tanggal transfer, lampirkan bukti pembayaran, lalu kirim pesan.</li></ol></div></div>
<div class="col-lg-5 col-md-9"><div class="summary-card text-center reveal reveal-delay-2"><div class="qris-frame"><img src="qris.png" alt="QRIS pembayaran PrioriBuy" width="340" height="478"></div><p class="mt-3 mb-1"><b>Scan QRIS di atas untuk melakukan pembayaran.</b></p><p class="form-hint mb-4">Pastikan nominal pembayaran sesuai, lalu tekan tombol di bawah setelah selesai membayar.</p><div id="payError" class="alert-pb alert-pb--error text-start" role="alert" style="display:none;"><i class="bi bi-exclamation-circle"></i><span></span></div><button id="btnSudahBayar" data-email="<?php echo pb_h($email); ?>" class="btn btn-pb-primary btn-lg w-100" type="button"><i class="bi bi-whatsapp me-2"></i>Saya Sudah Bayar — Konfirmasi via WhatsApp</button><div id="waNext" class="alert-pb alert-pb--ok text-start mt-3" role="status" style="display:none;"><i class="bi bi-whatsapp"></i><span>WhatsApp admin dibuka dengan pesan yang sudah terisi. Lengkapi nama dan tanggal transfer, lampirkan bukti pembayaran, lalu kirim sendiri. Jika WhatsApp tidak terbuka, <a id="waLink" href="#" target="_blank" rel="noopener noreferrer" style="font-weight:600;">klik di sini</a>.</span></div><p class="form-hint mt-3">Pembayaran akan diperiksa admin secara manual. Website tidak mengonfirmasi pembayaran secara otomatis.</p></div></div>
</div></div></section>
<?php require __DIR__ . '/private/partials/foot.php'; ?>
