
<?php
/**
 * Langkah 2 alur Premium: halaman pembayaran QRIS.
 * Data transaksi dibaca dari database berdasarkan ID di sesi server (bukan dari URL).
 * Menampilkan halaman ini TIDAK berarti pembayaran sudah terjadi.
 */
declare(strict_types=1);

require __DIR__ . '/private/bootstrap.php';
pb_start_session();
pb_no_cache();

$tx = pb_session_transaction();
if ($tx === null) {
    pb_redirect('mulai-premium.php');
}
if ($tx['confirmation_time'] !== null) {
    pb_redirect('konfirmasi.php');
}

$pageTitle = 'Pembayaran PrioriBuy Premium — PrioriBuy';
$pageDesc  = 'Scan QRIS untuk membayar PrioriBuy Premium.';
$navActive = 'premium';
$noindex   = true;
require __DIR__ . '/private/partials/head.php';
?>
<section class="page-hero">
  <div class="container">
    <div class="eyebrow eyebrow--pink reveal"><span class="dot"></span>Pembayaran</div>
    <h1 class="reveal reveal-delay-1">PrioriBuy Premium</h1>
    <p class="lead-text reveal reveal-delay-2"><span class="price-inline"><?= pb_h(pb_format_idr((int) $tx['price'])) ?></span> / bulan</p>
  </div>
</section>

<section class="section-pad" style="padding-top:40px; background:#fff;">
  <div class="container">
    <div class="row justify-content-center gy-4">

      <div class="col-lg-5 col-md-9">
        <div class="summary-card reveal">
          <h5 class="mb-3">Ringkasan Pembelian</h5>
          <div class="summary-row"><span class="k">Produk</span><span class="v"><?= pb_h($tx['product']) ?></span></div>
          <div class="summary-row"><span class="k">Harga</span><span class="v"><?= pb_h(pb_format_idr((int) $tx['price'])) ?> / bulan</span></div>
          <div class="summary-row"><span class="k">ID Transaksi</span><span class="v"><?= pb_h($tx['transaction_id']) ?></span></div>
          <div class="summary-row"><span class="k">Email</span><span class="v"><?= pb_h($tx['email']) ?></span></div>
          <div class="summary-row"><span class="k">Status</span><span class="v"><span class="status-pill"><i class="bi bi-hourglass-split"></i><?= pb_h(pb_status_label((string) $tx['payment_status'], false)) ?></span></span></div>
          <p class="form-hint mt-3">Email salah? <a href="mulai-premium.php" style="color:var(--blue-deep);font-weight:600;">Ubah email</a>.</p>
        </div>
      </div>

      <div class="col-lg-5 col-md-9">
        <div class="summary-card text-center reveal reveal-delay-2">
          <div class="qris-frame">
            <img src="qris.png" alt="QRIS pembayaran PrioriBuy Premium" width="340" height="339">
          </div>
          <p class="mt-3 mb-1"><b>Scan QRIS di atas untuk melakukan pembayaran.</b></p>
          <p class="form-hint mb-4">Pastikan nominal pembayaran sesuai, lalu tekan tombol di bawah setelah Anda selesai membayar.</p>

          <form method="post" action="konfirmasi.php" data-submit-once>
            <?= pb_csrf_field() ?>
            <button class="btn btn-pb-primary btn-lg w-100" type="submit">Saya Sudah Bayar</button>
          </form>
          <p class="form-hint mt-3">Pembayaran akan diperiksa terlebih dahulu sebelum status Premium diaktifkan.</p>
        </div>
      </div>

    </div>
  </div>
</section>
<?php require __DIR__ . '/private/partials/foot.php'; ?>
