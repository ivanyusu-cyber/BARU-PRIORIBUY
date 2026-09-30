<?php
/**
 * Langkah 1 alur Premium: pengguna memasukkan email.
 * Email divalidasi di server, transaksi PENDING dibuat, lalu pengguna diarahkan ke pembayaran.
 * Email TIDAK dikirim lewat URL — hanya ID transaksi yang disimpan di sesi server.
 */
declare(strict_types=1);

require __DIR__ . '/private/bootstrap.php';
pb_start_session();
pb_no_cache();

$error    = '';
$oldEmail = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $raw = $_POST['email'] ?? '';
    $oldEmail = is_string($raw) ? substr(trim($raw), 0, 254) : '';

    if (!pb_csrf_valid()) {
        $error = 'Sesi formulir sudah kedaluwarsa. Muat ulang halaman ini, lalu coba lagi.';
    } elseif (!empty($_POST['website'])) {
        // kolom jebakan (honeypot) terisi -> kemungkinan bot
        $error = 'Permintaan tidak dapat diproses.';
    } elseif (!is_string($raw)) {
        $error = 'Format email tidak valid. Gunakan format nama@email.com.';
    } else {
        [$valid, $result] = pb_validate_email($raw);
        if (!$valid) {
            $error = $result;
        } elseif (pb_recent_transaction_count(pb_client_hash()) >= (int) pb_config('RATE_LIMIT_PER_HOUR', 20)) {
            $error = 'Terlalu banyak percobaan dari perangkat ini. Silakan coba lagi nanti.';
        } else {
            try {
                // pakai ulang transaksi yang masih menunggu pembayaran bila email sama
                $existing = pb_session_transaction();
                if ($existing !== null
                    && $existing['confirmation_time'] === null
                    && $existing['payment_status'] === PB_STATUS_PENDING
                    && $existing['email'] === $result) {
                    $tid = (string) $existing['transaction_id'];
                } else {
                    $tid = pb_create_transaction($result);
                }
                session_regenerate_id(true);
                $_SESSION['tx_id'] = $tid;
                pb_redirect('pembayaran.php');
            } catch (Throwable $e) {
                error_log('PrioriBuy mulai-premium: ' . $e->getMessage());
                $error = 'Terjadi kesalahan pada server. Silakan coba lagi.';
            }
        }
    }
}

$pageTitle = 'Mulai Premium — PrioriBuy';
$pageDesc  = 'Masukkan email untuk melanjutkan pembelian PrioriBuy Premium.';
$navActive = 'premium';
$noindex   = true;
require __DIR__ . '/private/partials/head.php';
?>
<section class="page-hero">
  <div class="container">
    <div class="eyebrow eyebrow--pink reveal"><span class="dot"></span>Mulai Premium</div>
    <h1 class="reveal reveal-delay-1">Masukkan Email Anda</h1>
    <p class="lead-text reveal reveal-delay-2">PrioriBuy Premium — <span class="price-inline">Rp49.000</span> / bulan.</p>
  </div>
</section>

<section class="section-pad" style="padding-top:40px; background:#fff;">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-6 col-md-9">
        <div class="form-card reveal">
          <?php if ($error !== ''): ?>
            <div class="alert-pb alert-pb--error" role="alert"><i class="bi bi-exclamation-circle"></i><span><?= pb_h($error) ?></span></div>
          <?php endif; ?>

          <form method="post" action="mulai-premium.php" data-submit-once>
            <?= pb_csrf_field() ?>
            <div class="hp-field" aria-hidden="true">
              <label for="website">Jangan diisi</label>
              <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
            </div>

            <label class="form-label-pb" for="email">Email</label>
            <input class="form-control-pb<?= $error !== '' ? ' is-invalid' : '' ?>" type="email" id="email" name="email"
                   placeholder="nama@email.com" maxlength="254" autocomplete="email" inputmode="email" required
                   value="<?= pb_h($oldEmail) ?>">
            <p class="form-hint">Email digunakan untuk mengirim informasi terkait pembelian PrioriBuy Premium.</p>

            <button class="btn btn-pb-primary w-100 mt-4" type="submit">Lanjut ke Pembayaran</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>
<?php require __DIR__ . '/private/partials/foot.php'; ?>
