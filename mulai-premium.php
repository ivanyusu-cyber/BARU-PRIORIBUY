<?php
/**
 * Langkah 1 alur PrioriBuy (Download / Coba Sekarang): pengguna memasukkan email pendaftaran.
 * Email divalidasi di browser (assets/js/premium-flow.js) dan disimpan di sessionStorage.
 * Email TIDAK dikirim lewat URL. Semua tombol Download & Coba Sekarang mengarah ke halaman ini.
 */
declare(strict_types=1);

require __DIR__ . '/private/bootstrap.php';
pb_no_cache();

$pageTitle   = 'Mulai PrioriBuy — PrioriBuy';
$pageDesc    = 'Masukkan email untuk mendaftar dan melanjutkan ke pembayaran PrioriBuy.';
$navActive   = 'download';
$noindex     = true;
$pageScripts = ['assets/js/premium-flow.js'];
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
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-6 col-md-9">
        <div class="form-card reveal">
          <div id="formError" class="alert-pb alert-pb--error" role="alert" style="display:none;"><i class="bi bi-exclamation-circle"></i><span></span></div>

          <form id="formMulai" method="post" action="mulai-premium.php" novalidate>
            <div class="hp-field" aria-hidden="true">
              <label for="website">Jangan diisi</label>
              <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
            </div>

            <label class="form-label-pb" for="email">Email</label>
            <input class="form-control-pb" type="email" id="email" name="email"
                   placeholder="nama@email.com" maxlength="254" autocomplete="email" inputmode="email" required>
            <p class="form-hint">Email ini akan digunakan untuk mendaftar PrioriBuy dan otomatis dicantumkan pada pesan konfirmasi WhatsApp.</p>

            <button class="btn btn-pb-primary w-100 mt-4" type="submit">Lanjut ke Pembayaran</button>
          </form>
          <noscript><p class="form-hint mt-3">Aktifkan JavaScript di browser Anda untuk melanjutkan.</p></noscript>
        </div>
      </div>
    </div>
  </div>
</section>
<?php require __DIR__ . '/private/partials/foot.php'; ?>
