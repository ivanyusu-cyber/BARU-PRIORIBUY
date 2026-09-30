<?php
/**
 * Langkah 3 alur Premium: konfirmasi "Saya Sudah Bayar".
 * POST  -> catat waktu konfirmasi, status TETAP 'PENDING', kirim email ke pembeli & admin (server-side), lalu redirect.
 * GET   -> tampilkan halaman konfirmasi (hanya jika sesi ini sudah mengonfirmasi).
 * Tombol ini BUKAN bukti pembayaran: status tidak pernah menjadi PAID/VERIFIED dari sini.
 */
declare(strict_types=1);

require __DIR__ . '/private/bootstrap.php';
pb_start_session();
pb_no_cache();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tx = pb_session_transaction();
    if ($tx === null) {
        pb_redirect('mulai-premium.php');
    }
    if (!pb_csrf_valid()) {
        pb_redirect('pembayaran.php');
    }
    // jeda 30 detik antar percobaan kirim email (mencegah klik beruntun menghabiskan kuota email)
    $last = isset($_SESSION['mail_attempt_at']) ? (int) $_SESSION['mail_attempt_at'] : 0;
    if ($tx['confirmation_time'] === null || (time() - $last) >= 30) {
        $_SESSION['mail_attempt_at'] = time();
        try {
            pb_process_confirmation((string) $tx['transaction_id']);
        } catch (Throwable $e) {
            error_log('PrioriBuy konfirmasi: ' . $e->getMessage());
        }
    }
    pb_redirect('konfirmasi.php');
}

$tx = pb_session_transaction();
if ($tx === null) {
    pb_redirect('mulai-premium.php');
}
if ($tx['confirmation_time'] === null) {
    pb_redirect('pembayaran.php');
}
$mailSent = $tx['buyer_email_sent_at'] !== null;

$pageTitle = 'Konfirmasi Pembayaran — PrioriBuy';
$pageDesc  = 'Konfirmasi pembayaran PrioriBuy Premium diterima dan menunggu verifikasi.';
$navActive = 'premium';
$noindex   = true;
require __DIR__ . '/private/partials/head.php';
?>
<section class="page-hero">
  <div class="container">
    <div class="confirm-icon reveal"><i class="bi bi-hourglass-split"></i></div>
    <h1 class="reveal reveal-delay-1">Konfirmasi Pembayaran Diterima</h1>
    <p class="lead-text reveal reveal-delay-2">Konfirmasi pembayaran Anda telah diterima dan sedang menunggu verifikasi.</p>
  </div>
</section>

<section class="section-pad" style="padding-top:40px; background:#fff;">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-6 col-md-9">
        <div class="summary-card reveal">
          <div class="summary-row"><span class="k">ID Transaksi</span><span class="v"><?= pb_h($tx['transaction_id']) ?></span></div>
          <div class="summary-row"><span class="k">Email</span><span class="v"><?= pb_h($tx['email']) ?></span></div>
          <div class="summary-row"><span class="k">Produk</span><span class="v"><?= pb_h($tx['product']) ?> — <?= pb_h(pb_format_idr((int) $tx['price'])) ?></span></div>
          <div class="summary-row"><span class="k">Waktu Konfirmasi</span><span class="v"><?= pb_h(pb_format_dt($tx['confirmation_time'])) ?></span></div>
          <div class="summary-row"><span class="k">Status</span><span class="v"><span class="status-pill"><i class="bi bi-hourglass-split"></i><?= pb_h(pb_status_label((string) $tx['payment_status'], true)) ?></span></span></div>

          <?php if ($mailSent): ?>
            <div class="alert-pb alert-pb--ok mt-4 mb-0" role="status"><i class="bi bi-envelope-check"></i><span>Informasi konfirmasi telah dikirim ke email Anda.</span></div>
          <?php else: ?>
            <div class="alert-pb alert-pb--warn mt-4" role="status"><i class="bi bi-exclamation-triangle"></i><span>Konfirmasi Anda sudah tercatat, tetapi email informasi belum berhasil terkirim. Simpan ID transaksi di atas untuk keperluan bantuan.</span></div>
            <form method="post" action="konfirmasi.php" data-submit-once>
              <?= pb_csrf_field() ?>
              <button class="btn btn-pb-outline w-100" type="submit">Kirim Ulang Email</button>
            </form>
          <?php endif; ?>
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
