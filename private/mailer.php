<?php
/**
 * PrioriBuy — pengiriman email SERVER-SIDE.
 * Driver (pilih di private/config.php -> MAIL_DRIVER):
 *   - apps_script : relay lewat Google Apps Script (HTTPS). Tidak perlu menyimpan password Gmail.
 *   - smtp        : SMTP langsung (mis. smtp.gmail.com + App Password).
 * Tidak ada credential di HTML/JS/URL. Semua fungsi mengembalikan [bool berhasil, string pesan-error-singkat].
 * File ini tidak menghasilkan output apa pun bila dibuka langsung.
 */
declare(strict_types=1);

/* ------------------------------------------------------------------ template email */

function pb_email_html(string $title, string $intro, array $rows, string $note): string
{
    $rowsHtml = '';
    foreach ($rows as $row) {
        $rowsHtml .= '<tr>'
            . '<td style="padding:10px 0;border-bottom:1px solid #EEF1F7;color:#5B6B8C;font-size:14px;width:42%;vertical-align:top;">' . pb_h($row[0]) . '</td>'
            . '<td style="padding:10px 0;border-bottom:1px solid #EEF1F7;color:#1C2541;font-size:14px;font-weight:bold;vertical-align:top;">' . pb_h($row[1]) . '</td>'
            . '</tr>';
    }
    return '<!DOCTYPE html><html lang="id"><head><meta charset="UTF-8"></head>'
        . '<body style="margin:0;padding:0;background:#F3F8FF;font-family:Arial,Helvetica,sans-serif;color:#1C2541;">'
        . '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#F3F8FF;padding:24px 12px;"><tr><td align="center">'
        . '<table role="presentation" width="560" cellpadding="0" cellspacing="0" style="max-width:560px;width:100%;background:#FFFFFF;border-radius:16px;overflow:hidden;">'
        . '<tr><td style="background:#3E7BFA;padding:22px 28px;color:#FFFFFF;font-size:20px;font-weight:bold;">PrioriBuy</td></tr>'
        . '<tr><td style="padding:28px;">'
        . '<h1 style="margin:0 0 12px;font-size:20px;color:#1C2541;">' . pb_h($title) . '</h1>'
        . '<p style="margin:0 0 18px;font-size:14px;line-height:1.7;color:#5B6B8C;">' . pb_h($intro) . '</p>'
        . '<table role="presentation" width="100%" cellpadding="0" cellspacing="0">' . $rowsHtml . '</table>'
        . '<p style="margin:20px 0 0;font-size:14px;line-height:1.7;color:#1C2541;">' . pb_h($note) . '</p>'
        . '</td></tr>'
        . '<tr><td style="padding:16px 28px;background:#FAFAFA;color:#8C9AB8;font-size:12px;">PrioriBuy — Belanja Lebih Bijak, Keuangan Lebih Baik</td></tr>'
        . '</table></td></tr></table></body></html>';
}

/** @return array{subject:string,text:string,html:string} */
function pb_build_buyer_mail(array $tx): array
{
    $price  = pb_format_idr((int) $tx['price']);
    $time   = pb_format_dt($tx['confirmation_time']);
    $status = 'Menunggu Verifikasi';
    $note   = 'Konfirmasi pembayaran Anda telah kami terima. Pembayaran akan diperiksa terlebih dahulu sebelum status Premium diaktifkan.';

    $text = "Halo,\n\n"
        . "Terima kasih telah melakukan konfirmasi pembayaran. Berikut ringkasannya:\n\n"
        . 'Produk          : ' . $tx['product'] . "\n"
        . 'ID Transaksi    : ' . $tx['transaction_id'] . "\n"
        . 'Harga           : ' . $price . " / bulan\n"
        . 'Email Pembeli   : ' . $tx['email'] . "\n"
        . 'Waktu Konfirmasi: ' . $time . "\n"
        . 'Status          : ' . $status . "\n\n"
        . $note . "\n\n"
        . "Simpan ID transaksi ini untuk keperluan bantuan.\n\n"
        . "Salam,\nTim PrioriBuy\n";

    $html = pb_email_html(
        'Konfirmasi Pembayaran PrioriBuy Premium',
        'Terima kasih telah melakukan konfirmasi pembayaran. Berikut ringkasannya:',
        [
            ['Produk', (string) $tx['product']],
            ['ID Transaksi', (string) $tx['transaction_id']],
            ['Harga', $price . ' / bulan'],
            ['Email Pembeli', (string) $tx['email']],
            ['Waktu Konfirmasi', $time],
            ['Status', $status],
        ],
        $note . ' Simpan ID transaksi ini untuk keperluan bantuan.'
    );

    return ['subject' => 'Konfirmasi Pembayaran PrioriBuy Premium', 'text' => $text, 'html' => $html];
}

/** @return array{subject:string,text:string,html:string} */
function pb_build_admin_mail(array $tx): array
{
    $price = pb_format_idr((int) $tx['price']);
    $time  = pb_format_dt($tx['confirmation_time']);

    $text = "Ada pengguna yang mengirim konfirmasi pembayaran.\n\n"
        . "ID Transaksi:\n" . $tx['transaction_id'] . "\n\n"
        . "Email Pembeli:\n" . $tx['email'] . "\n\n"
        . "Produk:\n" . $tx['product'] . "\n\n"
        . "Harga:\n" . $price . "\n\n"
        . "Waktu Konfirmasi:\n" . $time . "\n\n"
        . "Status:\nPENDING / MENUNGGU VERIFIKASI\n\n"
        . "Periksa mutasi pembayaran secara manual, lalu ubah status transaksi di database setelah pembayaran terverifikasi.\n";

    $html = pb_email_html(
        'Ada Konfirmasi Pembayaran PrioriBuy Premium',
        'Ada pengguna yang mengirim konfirmasi pembayaran.',
        [
            ['ID Transaksi', (string) $tx['transaction_id']],
            ['Email Pembeli', (string) $tx['email']],
            ['Produk', (string) $tx['product']],
            ['Harga', $price],
            ['Waktu Konfirmasi', $time],
            ['Status', 'PENDING / MENUNGGU VERIFIKASI'],
        ],
        'Periksa mutasi pembayaran secara manual, lalu ubah status transaksi di database setelah pembayaran terverifikasi.'
    );

    return ['subject' => 'Ada Konfirmasi Pembayaran PrioriBuy Premium', 'text' => $text, 'html' => $html];
}

function pb_send_buyer_confirmation(array $tx): array
{
    $m = pb_build_buyer_mail($tx);
    $replyTo = (string) pb_config('MAIL_FROM', '');
    return pb_send_mail((string) $tx['email'], $m['subject'], $m['text'], $m['html'], $replyTo !== '' ? $replyTo : null);
}

function pb_send_admin_notification(array $tx): array
{
    $admin = (string) pb_config('ADMIN_EMAIL', '');
    if (pb_is_placeholder($admin)) {
        return [false, 'ADMIN_EMAIL belum diisi'];
    }
    $m = pb_build_admin_mail($tx);
    // Reply-To = email pembeli (sudah divalidasi ketat), agar admin bisa langsung membalas
    return pb_send_mail($admin, $m['subject'], $m['text'], $m['html'], (string) $tx['email']);
}

/* ------------------------------------------------------------------ dispatcher */

/**
 * @return array{0:bool,1:string}
 */
function pb_send_mail(string $to, string $subject, string $text, string $html, ?string $replyTo = null): array
{
    if (!preg_match('/^[^\r\n]+$/', $to) || filter_var($to, FILTER_VALIDATE_EMAIL) === false) {
        return [false, 'alamat penerima tidak valid'];
    }
    if ($replyTo !== null && (!preg_match('/^[^\r\n]+$/', $replyTo) || filter_var($replyTo, FILTER_VALIDATE_EMAIL) === false)) {
        $replyTo = null;
    }
    $subject = str_replace(["\r", "\n"], ' ', $subject);
    $driver  = strtolower((string) pb_config('MAIL_DRIVER', 'apps_script'));

    try {
        if ($driver === 'apps_script') {
            return pb_mail_apps_script($to, $subject, $text, $html, $replyTo);
        }
        if ($driver === 'smtp') {
            return pb_mail_smtp($to, $subject, $text, $html, $replyTo);
        }
        return [false, 'MAIL_DRIVER tidak dikenal (gunakan apps_script atau smtp)'];
    } catch (Throwable $e) {
        error_log('PrioriBuy mail: ' . $e->getMessage());
        return [false, 'kesalahan internal saat mengirim email'];
    }
}

/* ------------------------------------------------------------------ driver: Google Apps Script (HTTPS) */

function pb_mail_apps_script(string $to, string $subject, string $text, string $html, ?string $replyTo): array
{
    $url = (string) pb_config('MAIL_API_URL', '');
    $key = (string) pb_config('MAIL_API_KEY', '');
    if (pb_is_placeholder($url) || pb_is_placeholder($key)) {
        return [false, 'MAIL_API_URL / MAIL_API_KEY belum diisi'];
    }
    if (!preg_match('#^https://script\.google\.com/(a/macros/[A-Za-z0-9.\-]+|macros)/s/[A-Za-z0-9_\-]+/exec$#', $url)) {
        return [false, 'MAIL_API_URL tidak valid (harus berakhiran /exec dari Apps Script)'];
    }
    if (!function_exists('curl_init')) {
        return [false, 'ekstensi cURL tidak tersedia di hosting'];
    }

    $payload = json_encode([
        'token'     => $key,
        'to'        => $to,
        'subject'   => $subject,
        'text'      => $text,
        'html'      => $html,
        'from_name' => (string) pb_config('MAIL_FROM_NAME', 'PrioriBuy'),
        'reply_to'  => $replyTo,
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if ($payload === false) {
        return [false, 'gagal menyusun data email'];
    }

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,   // Apps Script membalas lewat redirect 302
        CURLOPT_MAXREDIRS      => 5,
        CURLOPT_CONNECTTIMEOUT => 8,
        CURLOPT_TIMEOUT        => 20,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
    ]);
    $body = curl_exec($ch);
    $errno = curl_errno($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($body === false || $errno !== 0) {
        return [false, 'tidak dapat menghubungi relay email (cURL error ' . $errno . ')'];
    }
    $json = json_decode((string) $body, true);
    if (!is_array($json)) {
        return [false, 'respons relay tidak valid (HTTP ' . $code . '); pastikan deployment Apps Script: akses = Anyone'];
    }
    if (!empty($json['ok'])) {
        return [true, ''];
    }
    $why = isset($json['error']) && is_string($json['error']) ? substr(preg_replace('/[^A-Za-z0-9_ .\-]/', '', $json['error']), 0, 60) : 'unknown';
    return [false, 'relay menolak: ' . $why];
}

/* ------------------------------------------------------------------ driver: SMTP */

function pb_mime_header(string $value): string
{
    return preg_match('/^[\x20-\x7e]*$/', $value) ? $value : '=?UTF-8?B?' . base64_encode($value) . '?=';
}

function pb_build_mime(string $from, string $fromName, string $to, string $subject, string $text, string $html, ?string $replyTo): string
{
    $boundary = '=_pb_' . bin2hex(random_bytes(12));
    $domain   = substr(strrchr($from, '@') ?: '@localhost', 1);
    $headers  = [
        'Date: ' . date('r'),
        'From: ' . pb_mime_header($fromName) . ' <' . $from . '>',
        'To: <' . $to . '>',
        'Subject: ' . pb_mime_header($subject),
        'Message-ID: <' . bin2hex(random_bytes(12)) . '@' . $domain . '>',
        'MIME-Version: 1.0',
        'Content-Type: multipart/alternative; boundary="' . $boundary . '"',
    ];
    if ($replyTo !== null) {
        $headers[] = 'Reply-To: <' . $replyTo . '>';
    }
    $part = function (string $type, string $content) use ($boundary): string {
        return '--' . $boundary . "\r\n"
            . 'Content-Type: ' . $type . '; charset=UTF-8' . "\r\n"
            . 'Content-Transfer-Encoding: base64' . "\r\n\r\n"
            . chunk_split(base64_encode($content), 76, "\r\n");
    };
    return implode("\r\n", $headers) . "\r\n\r\n"
        . $part('text/plain', $text)
        . $part('text/html', $html)
        . '--' . $boundary . "--\r\n";
}

function pb_mail_smtp(string $to, string $subject, string $text, string $html, ?string $replyTo): array
{
    $c = pb_config();
    $host = (string) ($c['MAIL_HOST'] ?? '');
    $port = (int) ($c['MAIL_PORT'] ?? 0);
    $enc  = strtolower((string) ($c['MAIL_ENCRYPTION'] ?? 'ssl'));   // ssl (465) atau tls (587)
    $user = (string) ($c['MAIL_USERNAME'] ?? '');
    $pass = (string) ($c['MAIL_PASSWORD'] ?? '');
    $from = (string) ($c['MAIL_FROM'] ?? '');
    $name = (string) ($c['MAIL_FROM_NAME'] ?? 'PrioriBuy');

    foreach (['MAIL_HOST' => $host, 'MAIL_USERNAME' => $user, 'MAIL_PASSWORD' => $pass, 'MAIL_FROM' => $from] as $k => $v) {
        if (pb_is_placeholder($v)) {
            return [false, $k . ' belum diisi'];
        }
    }
    if ($port < 1 || filter_var($from, FILTER_VALIDATE_EMAIL) === false) {
        return [false, 'MAIL_PORT / MAIL_FROM tidak valid'];
    }
    $name = str_replace(["\r", "\n"], ' ', $name);

    $timeout = 12;
    $ctx = stream_context_create(['ssl' => ['verify_peer' => true, 'verify_peer_name' => true, 'peer_name' => $host]]);
    $fp = @stream_socket_client(($enc === 'ssl' ? 'ssl://' : 'tcp://') . $host . ':' . $port, $errno, $errstr, $timeout, STREAM_CLIENT_CONNECT, $ctx);
    if (!$fp) {
        return [false, 'tidak dapat terhubung ke server SMTP (' . (int) $errno . ')'];
    }
    stream_set_timeout($fp, $timeout);

    $read = function () use ($fp): string {
        $data = '';
        while (($line = fgets($fp, 515)) !== false) {
            $data .= $line;
            if (strlen($line) < 4 || $line[3] === ' ') {
                break;
            }
        }
        return $data;
    };
    // Perintah (termasuk username/password) tidak pernah dicatat maupun ditampilkan; hanya balasan server yang dipotong pendek.
    $cmd = function (string $line, array $okCodes) use ($fp, $read): array {
        fwrite($fp, $line . "\r\n");
        $reply = $read();
        return [in_array((int) substr($reply, 0, 3), $okCodes, true), trim(strtok($reply, "\r\n") ?: '')];
    };
    $fail = function (string $step, string $reply) use ($fp): array {
        @fwrite($fp, "QUIT\r\n");
        @fclose($fp);
        return [false, 'SMTP gagal pada ' . $step . ': ' . substr(preg_replace('/[^\x20-\x7e]/', '', $reply), 0, 90)];
    };

    $greet = $read();
    if ((int) substr($greet, 0, 3) !== 220) {
        return $fail('koneksi awal', $greet);
    }
    $ehloHost = 'prioribuy.local';
    [$ok, $r] = $cmd('EHLO ' . $ehloHost, [250]);
    if (!$ok) {
        return $fail('EHLO', $r);
    }
    if ($enc === 'tls') {
        [$ok, $r] = $cmd('STARTTLS', [220]);
        if (!$ok) {
            return $fail('STARTTLS', $r);
        }
        if (!@stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
            return $fail('negosiasi TLS', 'gagal');
        }
        [$ok, $r] = $cmd('EHLO ' . $ehloHost, [250]);
        if (!$ok) {
            return $fail('EHLO (TLS)', $r);
        }
    }
    [$ok, $r] = $cmd('AUTH LOGIN', [334]);
    if (!$ok) {
        return $fail('AUTH', $r);
    }
    [$ok, $r] = $cmd(base64_encode($user), [334]);
    if (!$ok) {
        return $fail('username', $r);
    }
    [$ok, $r] = $cmd(base64_encode($pass), [235]);
    if (!$ok) {
        return $fail('login (periksa MAIL_USERNAME / App Password)', $r);
    }
    [$ok, $r] = $cmd('MAIL FROM:<' . $from . '>', [250]);
    if (!$ok) {
        return $fail('MAIL FROM', $r);
    }
    [$ok, $r] = $cmd('RCPT TO:<' . $to . '>', [250, 251]);
    if (!$ok) {
        return $fail('RCPT TO', $r);
    }
    [$ok, $r] = $cmd('DATA', [354]);
    if (!$ok) {
        return $fail('DATA', $r);
    }
    $message = pb_build_mime($from, $name, $to, $subject, $text, $html, $replyTo);
    $message = preg_replace('/^\./m', '..', $message);   // dot-stuffing
    fwrite($fp, $message . "\r\n.\r\n");
    $reply = $read();
    if ((int) substr($reply, 0, 3) !== 250) {
        return $fail('pengiriman pesan', trim(strtok($reply, "\r\n") ?: ''));
    }
    @fwrite($fp, "QUIT\r\n");
    @fclose($fp);
    return [true, ''];
}
