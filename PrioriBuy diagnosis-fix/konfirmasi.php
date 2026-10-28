<?php
/**
 * Endpoint kompatibilitas: konfirmasi pembayaran kini dilakukan lewat WhatsApp
 * dari halaman pembayaran.php (tanpa EmailJS). Tidak ada lagi halaman
 * "pembayaran diterima" karena pembayaran baru bisa dipastikan lewat pemeriksaan manual admin.
 */
declare(strict_types=1);

header('Cache-Control: no-store');
header('Location: pembayaran.php', true, 302);
exit;
