<?php
/**
 * Langkah 3 alur Premium: Konfirmasi Pembayaran Diterima.
 * Halaman ini hanya terbuka jika kedua email EmailJS (pembeli & admin) sudah terkirim
 * (ditandai di sessionStorage oleh assets/js/premium-flow.js). Status TETAP PENDING.
 */
declare(strict_types=1);

require __DIR__ . '/private/bootstrap.php';
pb_no_cache();

$pageTitle   = 'Konfirmasi Pembayaran — PrioriBuy';
$pageDesc    = 'Konfirmasi pembayaran PrioriBuy Premium diterima dan menunggu verifikasi.';
$navActive   = 'premium';
$noindex     = true;
$pageScripts = ['assets/js/premium-flow.js'];
require __DIR__ . '/private/partials/head.php';
?>
<section class="page-hero">
  <div class="container">
    <div class="confirm-icon reveal"><i class="bi bi-hourglass-split"></i></div>
    <h1 class="reveal reveal-delay-1">Konfirmasi Pembayaran Diterima</h1>
    <p class="lead-text reveal reveal-delay-2">Terima kasih. Konfirmasi pembayaran Anda telah kami terima dan sedang menunggu verifikasi manual.</p>
  </div>
</section>

<section class="section-pad" style="padding-top:40px; background:#fff;">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-6 col-md-9">
        <div class="summary-card reveal" id="konfirmasiBox">
          <div class="summary-row"><span class="k">ID Transaksi</span><span class="v" id="cId">-</span></div>
          <div class="summary-row"><span class="k">Email</span><span class="v" id="cEmail">-</span></div>
          <div class="summary-row"><span class="k">Produk</span><span class="v" id="cProduct">PrioriBuy Premium — Rp49.000</span></div>
          <div class="summary-row"><span class="k">Waktu Konfirmasi</span><span class="v" id="cTime">-</span></div>
          <div class="summary-row"><span class="k">Status</span><span class="v"><span class="status-pill"><i class="bi bi-hourglass-split"></i>Menunggu Verifikasi</span></span></div>

          <p class="form-hint mt-3 mb-0">Pembayaran Anda sedang menunggu verifikasi manual.</p>
          <div class="alert-pb alert-pb--ok mt-3 mb-0" role="status"><i class="bi bi-envelope-check"></i><span>Detail konfirmasi telah dikirim ke email Anda.</span></div>
        </div>

        <div class="d-flex flex-wrap justify-content-center gap-3 mt-4 reveal">
          <a href="index.html" class="btn btn-pb-outline">Kembali ke Beranda</a>
          <a href="download.html" class="btn btn-pb-primary">Download Aplikasi</a>
        </div>
      </div>
    </div>
  </div>
</section>
<?php require __DIR__ . '/private/partials/foot.php'; ?>
