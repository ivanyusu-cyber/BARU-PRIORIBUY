<?php
/* PrioriBuy: halaman pendaftaran email yang tidak bergantung pada bootstrap aplikasi. */
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

$formError = '';
$emailValue = isset($_SESSION['pb_registration_email']) && is_string($_SESSION['pb_registration_email']) ? $_SESSION['pb_registration_email'] : '';
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $emailRaw = isset($_POST['email']) && is_string($_POST['email']) ? trim($_POST['email']) : '';
    $honeypot = isset($_POST['website']) && is_string($_POST['website']) ? trim($_POST['website']) : '';
    $emailValue = $emailRaw;
    if ($honeypot !== '') {
        $formError = 'Permintaan tidak dapat diproses.';
    } elseif ($emailRaw === '' || strlen($emailRaw) > 254 || !filter_var($emailRaw, FILTER_VALIDATE_EMAIL)) {
        $formError = 'Silakan masukkan alamat email yang valid.';
    } else {
        $_SESSION['pb_registration_email'] = strtolower($emailRaw);
        session_write_close();
        header('Location: pembayaran.php', true, 303);
        exit;
    }
}

$pageTitle = 'Mulai PrioriBuy — PrioriBuy';
$pageDesc = 'Masukkan email untuk mendaftar dan melanjutkan ke pembayaran PrioriBuy.';
$navActive = 'download';
$noindex = true;
$pageScripts = array('assets/js/premium-flow.js');
require __DIR__ . '/private/partials/head.php';
?>
<section class="page-hero">
  <div class="container">
    <div class="eyebrow eyebrow--pink reveal"><span class="dot"></span>Pendaftaran</div>
    <h1 class="reveal reveal-delay-1">Masukkan Email Anda</h1>
    <p class="lead-text reveal reveal-delay-2">Daftar PrioriBuy — <span class="price-inline">Rp49.000</span>.</p>
  </div>
</section>
<section class="section-pad" style="padding-top:40px; background:#fff;">
  <div class="container"><div class="row justify-content-center"><div class="col-lg-6 col-md-9">
    <div class="form-card reveal">
      <div id="formError" class="alert-pb alert-pb--error" role="alert" style="<?php echo $formError !== '' ? 'display:flex;' : 'display:none;'; ?>"><i class="bi bi-exclamation-circle"></i><span><?php echo pb_h($formError); ?></span></div>
      <form id="formMulai" method="post" action="mulai-premium.php" novalidate>
        <div class="hp-field" aria-hidden="true"><label for="website">Jangan diisi</label><input type="text" id="website" name="website" tabindex="-1" autocomplete="off"></div>
        <label class="form-label-pb" for="email">Email</label>
        <input class="form-control-pb" type="email" id="email" name="email" placeholder="nama@email.com" maxlength="254" autocomplete="email" inputmode="email" value="<?php echo pb_h($emailValue); ?>" required>
        <p class="form-hint">Email ini akan digunakan untuk mendaftar PrioriBuy dan otomatis dicantumkan pada pesan konfirmasi WhatsApp.</p>
        <button class="btn btn-pb-primary w-100 mt-4" type="submit">Lanjut ke Pembayaran</button>
      </form>
      <noscript><p class="form-hint mt-3">JavaScript tidak aktif; formulir tetap dapat diproses oleh server.</p></noscript>
    </div>
  </div></div></div>
</section>
<?php require __DIR__ . '/private/partials/foot.php'; ?>
