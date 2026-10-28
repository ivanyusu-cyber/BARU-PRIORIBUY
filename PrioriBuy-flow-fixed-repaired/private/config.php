<?php
/**
 * PrioriBuy — KONFIGURASI SERVER (RAHASIA)
 * ---------------------------------------------------------------
 * File ini hanya dibaca oleh PHP di server. Jangan pernah menyalinnya ke
 * HTML/JS/URL, dan jangan membagikannya. Folder private/ diblokir lewat .htaccess.
 *
 * Nilai yang diawali "ISI_" adalah PLACEHOLDER — ganti dengan data asli Anda.
 * Selama masih "ISI_" atau kosong, fitur terkait dianggap belum dikonfigurasi
 * (tidak ada data palsu yang dipakai).
 */
return [

    // --- Umum ---------------------------------------------------
    // String acak panjang (30+ karakter) untuk mengacak hash IP. Bebas, cukup Anda saja yang tahu.
    'APP_SECRET'   => 'ISI_STRING_ACAK_PANJANG',
    // Batas pembuatan transaksi per pengguna (per IP) per jam, untuk mencegah spam.
    'RATE_LIMIT_PER_HOUR' => 20,
    // Kunci untuk membuka halaman cek-setup.php (hapus file itu setelah setup selesai).
    'SETUP_KEY'    => 'ISI_KUNCI_SETUP_SEMENTARA',

    // --- Database MySQL (dari panel InfinityFree -> MySQL Databases) ---
    'DB_HOST'      => 'ISI_DB_HOST',      // contoh bentuknya: sqlXXX.infinityfree.com
    'DB_NAME'      => 'ISI_DB_NAME',
    'DB_USER'      => 'ISI_DB_USER',
    'DB_PASS'      => 'ISI_DB_PASSWORD',

    // --- Email ---------------------------------------------------
    'MAIL_FROM'      => 'pintardigitalproduct@gmail.com',   // email pengirim PrioriBuy
    'MAIL_FROM_NAME' => 'PrioriBuy',
    'ADMIN_EMAIL'    => 'pintardigitalproduct@gmail.com',   // penerima notifikasi pembayaran

    // Pilih salah satu: 'apps_script' (disarankan) atau 'smtp'
    'MAIL_DRIVER'    => 'apps_script',

    // A) Driver apps_script  (lihat PANDUAN.md bagian "Setup Email")
    'MAIL_API_URL'   => 'ISI_URL_DEPLOYMENT_APPS_SCRIPT',   // berakhiran /exec
    'MAIL_API_KEY'   => 'ISI_MAIL_API_KEY',                 // kunci rahasia yang sama dengan RELAY_TOKEN di Apps Script

    // B) Driver smtp (hanya jika MAIL_DRIVER = 'smtp')
    'MAIL_HOST'       => 'smtp.gmail.com',
    'MAIL_PORT'       => 465,                               // 465 untuk 'ssl', 587 untuk 'tls'
    'MAIL_ENCRYPTION' => 'ssl',
    'MAIL_USERNAME'   => 'pintardigitalproduct@gmail.com',
    'MAIL_PASSWORD'   => 'ISI_MAIL_PASSWORD',               // App Password Gmail (bukan password login Gmail)
];
