<?php
/**
 * PrioriBuy — inti sistem server-side (konfigurasi, database, sesi, validasi, transaksi).
 * File ini tidak menghasilkan output apa pun bila dibuka langsung.
 * Kompatibel PHP 7.4+.
 */
declare(strict_types=1);

date_default_timezone_set('Asia/Jakarta');

const PB_PRODUCT_NAME   = 'PrioriBuy Premium';
const PB_PRODUCT_PRICE  = 49000;      // rupiah per bulan
const PB_STATUS_PENDING = 'PENDING';  // status awal; hanya diubah manual oleh admin

/* ------------------------------------------------------------------ util dasar */

function pb_h($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Tampilkan pesan error umum (tanpa detail teknis) lalu berhenti. */
function pb_fail(string $message, int $code = 500): void
{
    http_response_code($code);
    header('Content-Type: text/html; charset=UTF-8');
    echo '<!DOCTYPE html><html lang="id"><head><meta charset="UTF-8">'
       . '<meta name="viewport" content="width=device-width, initial-scale=1.0">'
       . '<meta name="robots" content="noindex"><title>Terjadi Kesalahan — PrioriBuy</title></head>'
       . '<body style="font-family:Arial,sans-serif;background:#FAFAFA;color:#1C2541;text-align:center;padding:80px 20px;">'
       . '<h2>Terjadi kesalahan</h2><p>' . pb_h($message) . '</p>'
       . '<p><a href="index.html" style="color:#2455D6;">Kembali ke Beranda</a></p></body></html>';
    exit;
}

function pb_is_placeholder($value): bool
{
    if (!is_string($value)) {
        return true;
    }
    $value = trim($value);
    return $value === '' || strncmp($value, 'ISI_', 4) === 0;
}

function pb_redirect(string $path): void
{
    header('Location: ' . $path, true, 303);
    exit;
}

/** Halaman berisi data pribadi tidak boleh disimpan cache / diindeks. */
function pb_no_cache(): void
{
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    header('X-Robots-Tag: noindex, nofollow');
}

/* ------------------------------------------------------------------ konfigurasi */

function pb_config(?string $key = null, $default = null)
{
    static $config = null;
    if ($config === null) {
        $file = __DIR__ . '/config.php';
        if (!is_file($file)) {
            error_log('PrioriBuy: private/config.php tidak ditemukan.');
            pb_fail('Konfigurasi server belum dibuat.');
        }
        $config = require $file;
        if (!is_array($config)) {
            error_log('PrioriBuy: private/config.php harus mengembalikan array.');
            pb_fail('Konfigurasi server tidak valid.');
        }
    }
    if ($key === null) {
        return $config;
    }
    return array_key_exists($key, $config) ? $config[$key] : $default;
}

/* ------------------------------------------------------------------ sesi & CSRF */

function pb_start_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https')
        || (($_SERVER['SERVER_PORT'] ?? '') === '443');

    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.gc_maxlifetime', '7200');
    session_name('PBSESSID');
    session_set_cookie_params([
        'lifetime' => 7200,
        'path'     => '/',
        'secure'   => $https,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

function pb_csrf_token(): string
{
    pb_start_session();
    if (empty($_SESSION['csrf']) || !is_string($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function pb_csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . pb_h(pb_csrf_token()) . '">';
}

function pb_csrf_valid(): bool
{
    pb_start_session();
    $sent = $_POST['csrf'] ?? '';
    return is_string($sent)
        && !empty($_SESSION['csrf'])
        && is_string($_SESSION['csrf'])
        && hash_equals($_SESSION['csrf'], $sent);
}

/* ------------------------------------------------------------------ database */

/** Membuka koneksi PDO; melempar exception bila gagal (dipakai cek-setup.php). */
function pb_db_connect(): PDO
{
    $c = pb_config();
    foreach (['DB_HOST', 'DB_NAME', 'DB_USER'] as $k) {
        if (pb_is_placeholder($c[$k] ?? null)) {
            throw new RuntimeException($k . ' belum diisi di private/config.php');
        }
    }
    $dsn = 'mysql:host=' . $c['DB_HOST'] . ';dbname=' . $c['DB_NAME'] . ';charset=utf8mb4';
    return new PDO($dsn, (string) $c['DB_USER'], (string) ($c['DB_PASS'] ?? ''), [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
}

function pb_db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }
    try {
        $pdo = pb_db_connect();
    } catch (Throwable $e) {
        error_log('PrioriBuy DB: ' . $e->getMessage());
        pb_fail('Sistem belum dapat memproses transaksi saat ini. Silakan coba lagi nanti.');
    }
    return $pdo;
}

/* ------------------------------------------------------------------ format */

function pb_now(): string
{
    return date('Y-m-d H:i:s');
}

function pb_format_idr(int $amount): string
{
    return 'Rp' . number_format($amount, 0, ',', '.');
}

function pb_format_dt(?string $datetime): string
{
    if ($datetime === null || $datetime === '') {
        return '-';
    }
    $t = strtotime($datetime);
    if ($t === false) {
        return $datetime;
    }
    $months = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli',
               'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    return date('j', $t) . ' ' . $months[(int) date('n', $t)] . ' ' . date('Y', $t) . ', ' . date('H.i', $t) . ' WIB';
}

function pb_status_label(string $status, bool $confirmed): string
{
    if ($status === PB_STATUS_PENDING) {
        return $confirmed ? 'Menunggu Verifikasi' : 'Menunggu Pembayaran';
    }
    $labels = ['VERIFIED' => 'Terverifikasi', 'REJECTED' => 'Tidak Terverifikasi'];
    return $labels[$status] ?? $status;
}

/* ------------------------------------------------------------------ validasi email */

/** @return array{0:bool,1:string} [valid?, email-yang-dinormalisasi atau pesan error] */
function pb_validate_email(string $raw): array
{
    $email = trim($raw);
    if ($email === '') {
        return [false, 'Email wajib diisi.'];
    }
    if (strlen($email) > 254) {
        return [false, 'Email terlalu panjang.'];
    }
    // tolak spasi/kontrol, tanda kutip, kurung, HTML, dan karakter berbahaya lain
    if (preg_match('/[\x00-\x20\x7f<>"\'`;\\\\(),:\[\]{}|]/', $email) || $email !== strip_tags($email)) {
        return [false, 'Format email tidak valid. Gunakan format nama@email.com.'];
    }
    if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
        return [false, 'Format email tidak valid. Gunakan format nama@email.com.'];
    }
    $at = strrpos($email, '@');
    $domain = $at === false ? '' : substr($email, $at + 1);
    if ($domain === '' || strpos($domain, '.') === false || !preg_match('/^[A-Za-z0-9.-]+$/', $domain)) {
        return [false, 'Format email tidak valid. Gunakan format nama@email.com.'];
    }
    return [true, strtolower($email)];
}

/* ------------------------------------------------------------------ transaksi */

function pb_generate_transaction_id(): string
{
    $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'; // tanpa 0/O/1/I agar mudah dibaca
    $id = '';
    for ($i = 0; $i < 8; $i++) {
        $id .= $chars[random_int(0, strlen($chars) - 1)];
    }
    return 'PB-' . $id;
}

function pb_client_hash(): string
{
    $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
    return hash('sha256', $ip . '|' . (string) pb_config('APP_SECRET', ''));
}

function pb_recent_transaction_count(string $ipHash): int
{
    $since = date('Y-m-d H:i:s', time() - 3600);
    $st = pb_db()->prepare('SELECT COUNT(*) FROM transactions WHERE ip_hash = :h AND created_at >= :s');
    $st->execute([':h' => $ipHash, ':s' => $since]);
    return (int) $st->fetchColumn();
}

/** Membuat transaksi baru berstatus PENDING dan mengembalikan ID transaksi. */
function pb_create_transaction(string $email): string
{
    $st = pb_db()->prepare(
        'INSERT INTO transactions (transaction_id, email, product, price, created_at, payment_status, ip_hash)
         VALUES (:tid, :email, :product, :price, :created, :status, :ip)'
    );
    for ($i = 0; $i < 5; $i++) {
        $tid = pb_generate_transaction_id();
        try {
            $st->execute([
                ':tid'     => $tid,
                ':email'   => $email,
                ':product' => PB_PRODUCT_NAME,
                ':price'   => PB_PRODUCT_PRICE,
                ':created' => pb_now(),
                ':status'  => PB_STATUS_PENDING,
                ':ip'      => pb_client_hash(),
            ]);
            return $tid;
        } catch (PDOException $e) {
            // 1062 = duplicate key (ID kebetulan sama) -> coba ID lain
            if ((int) ($e->errorInfo[1] ?? 0) === 1062) {
                continue;
            }
            throw $e;
        }
    }
    throw new RuntimeException('Gagal membuat ID transaksi unik.');
}

function pb_find_transaction(string $tid): ?array
{
    $st = pb_db()->prepare('SELECT * FROM transactions WHERE transaction_id = :t LIMIT 1');
    $st->execute([':t' => $tid]);
    $row = $st->fetch();
    return is_array($row) ? $row : null;
}

/**
 * Transaksi milik sesi ini. Hanya ID transaksi yang disimpan di sesi server;
 * email tidak pernah dikirim lewat URL.
 */
function pb_session_transaction(): ?array
{
    pb_start_session();
    $tid = $_SESSION['tx_id'] ?? null;
    if (!is_string($tid) || !preg_match('/^PB-[A-Z0-9]{8}$/', $tid)) {
        return null;
    }
    return pb_find_transaction($tid);
}

/** Tandai (klaim) pengiriman email agar tidak terkirim ganda saat klik berulang. */
function pb_claim_mail_flag(string $tid, string $column): bool
{
    if (!in_array($column, ['buyer_email_sent_at', 'admin_email_sent_at'], true)) {
        return false;
    }
    $st = pb_db()->prepare("UPDATE transactions SET $column = :t WHERE transaction_id = :id AND $column IS NULL");
    $st->execute([':t' => pb_now(), ':id' => $tid]);
    return $st->rowCount() === 1;
}

function pb_release_mail_flag(string $tid, string $column): void
{
    if (!in_array($column, ['buyer_email_sent_at', 'admin_email_sent_at'], true)) {
        return;
    }
    $st = pb_db()->prepare("UPDATE transactions SET $column = NULL WHERE transaction_id = :id");
    $st->execute([':id' => $tid]);
}

/**
 * Dipanggil saat pengguna menekan "Saya Sudah Bayar".
 *  1. mencatat waktu konfirmasi (sekali saja)
 *  2. status TETAP 'PENDING' — tidak pernah diubah di sini
 *  3. mengirim email ke pembeli dan admin (masing-masing sekali; yang gagal bisa dicoba ulang)
 */
function pb_process_confirmation(string $tid): void
{
    $pdo = pb_db();

    $st = $pdo->prepare(
        "UPDATE transactions SET confirmation_time = :t
          WHERE transaction_id = :id AND confirmation_time IS NULL AND payment_status = 'PENDING'"
    );
    $st->execute([':t' => pb_now(), ':id' => $tid]);

    $tx = pb_find_transaction($tid);
    if ($tx === null || $tx['confirmation_time'] === null) {
        return;
    }

    require_once __DIR__ . '/mailer.php';
    $errors = [];

    if ($tx['buyer_email_sent_at'] === null && pb_claim_mail_flag($tid, 'buyer_email_sent_at')) {
        [$ok, $err] = pb_send_buyer_confirmation($tx);
        if (!$ok) {
            pb_release_mail_flag($tid, 'buyer_email_sent_at');
            $errors[] = 'pembeli: ' . $err;
        }
    }
    if ($tx['admin_email_sent_at'] === null && pb_claim_mail_flag($tid, 'admin_email_sent_at')) {
        [$ok, $err] = pb_send_admin_notification($tx);
        if (!$ok) {
            pb_release_mail_flag($tid, 'admin_email_sent_at');
            $errors[] = 'admin: ' . $err;
        }
    }

    $msg = $errors ? substr(implode(' | ', $errors), 0, 255) : null;
    $up = $pdo->prepare('UPDATE transactions SET mail_error = :m WHERE transaction_id = :id');
    $up->execute([':m' => $msg, ':id' => $tid]);
}
