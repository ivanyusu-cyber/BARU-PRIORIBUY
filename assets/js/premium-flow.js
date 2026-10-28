/**
 * Alur PrioriBuy (tombol Download & Coba Sekarang), tanpa database:
 *   mulai-premium.php (email) -> pembayaran.php (QRIS Rp49.000) -> tombol konfirmasi -> WhatsApp admin.
 * - Email disimpan di sessionStorage (TIDAK di URL).
 * - Tombol konfirmasi hanya MEMBUKA WhatsApp dengan pesan terisi; pengguna mengirim sendiri
 *   dan melampirkan screenshot bukti transfer secara manual.
 * - Status SELALU "PENDING": tidak ada klaim pembayaran berhasil/terverifikasi.
 *   Verifikasi dilakukan manual oleh admin.
 * - (Nama file dipertahankan demi kompatibilitas; EmailJS tidak lagi dipakai di alur ini.)
 */
(function () {
  'use strict';

  var KEY = 'pb_tx';
  var PRODUCT = 'PrioriBuy';
  var PRICE = 'Rp49.000';
  var PRICE_WA = 'Rp 49.000';
  var STATUS = 'PENDING';
  var START_PAGE = 'mulai-premium.php';
  // Nomor admin: 085800080181 -> format internasional tanpa 0 awal, tanpa "+" dan spasi.
  var WA_NUMBER = '6285800080181';
  var ERR_EMAIL = 'Silakan masukkan alamat email yang valid.';

  /* ---------- penyimpanan sesi ---------- */
  function load() {
    try { var r = sessionStorage.getItem(KEY); return r ? JSON.parse(r) : null; } catch (e) { return null; }
  }
  function save(tx) {
    try { sessionStorage.setItem(KEY, JSON.stringify(tx)); return true; } catch (e) { return false; }
  }

  /* ---------- validasi & sanitasi email ---------- */
  function validEmail(raw) {
    if (typeof raw !== 'string') return { ok: false, msg: ERR_EMAIL };
    var v = raw.trim();
    if (v === '' || v.length > 254) return { ok: false, msg: ERR_EMAIL };
    if (/[\u0000-\u0020\u007f<>"'`;\\(),:\[\]{}|]/.test(v)) return { ok: false, msg: ERR_EMAIL };
    if (!/^[A-Za-z0-9._%+\-]+@[A-Za-z0-9\-]+(\.[A-Za-z0-9\-]+)*\.[A-Za-z]{2,}$/.test(v)) return { ok: false, msg: ERR_EMAIL };
    return { ok: true, value: v.toLowerCase() };
  }

  /* ---------- pesan & tautan WhatsApp ---------- */
  // Nama & tanggal transfer sengaja dikosongkan: dilengkapi pengguna sendiri di WhatsApp.
  function buildWaMessage(email) {
    return [
      'alo Kak, aku mau pakai Prioribuy',
      '',
      'Nama: ',
      'Email (untuk daftar): ' + email,
      'Nominal TF: ' + PRICE_WA,
      'Tanggal TF: ',
      '',
      '(sertakan bukti screenshoot pembayaran)'
    ].join('\n');
  }
  function buildWaUrl(email) {
    return 'https://wa.me/' + WA_NUMBER + '?text=' + encodeURIComponent(buildWaMessage(email));
  }

  function $(id) { return document.getElementById(id); }
  function setText(id, text) { var el = $(id); if (el) el.textContent = text; }
  function showError(id, msg) {
    var box = $(id);
    if (!box) return;
    var span = box.querySelector('span');
    if (span) span.textContent = msg;
    box.style.display = 'flex';
  }
  function hideError(id) { var box = $(id); if (box) box.style.display = 'none'; }

  /* ============ HALAMAN 1: mulai-premium.php ============ */
  var form = $('formMulai');
  if (form) {
    var emailInput = $('email');
    var existing = load();
    if (existing && existing.email && emailInput && !emailInput.value) {
      emailInput.value = existing.email;
    }
    form.addEventListener('submit', function (ev) {
      ev.preventDefault();
      hideError('formError');
      var hp = $('website');
      if (hp && hp.value) { showError('formError', 'Permintaan tidak dapat diproses.'); return; }
      var r = validEmail(emailInput.value);
      if (!r.ok) {
        emailInput.classList.add('is-invalid');
        showError('formError', r.msg);
        return;
      }
      emailInput.classList.remove('is-invalid');
      var tx = { email: r.value, product: PRODUCT, price: PRICE, payment_status: STATUS };
      if (!save(tx)) {
        showError('formError', 'Browser Anda memblokir penyimpanan sesi. Aktifkan penyimpanan lokal lalu coba lagi.');
        return;
      }
      window.location.href = 'pembayaran.php';
    });
  }

  /* ============ HALAMAN 2: pembayaran.php ============ */
  var payBtn = $('btnSudahBayar');
  if (payBtn) {
    var tx0 = load();
    if (!tx0 || !tx0.email) { window.location.replace(START_PAGE); return; }
    setText('sumEmail', tx0.email);

    payBtn.addEventListener('click', function () {
      hideError('payError');

      var tx = load();
      if (!tx || !tx.email) { window.location.replace(START_PAGE); return; }

      // Validasi dulu: WhatsApp tidak dibuka jika email belum valid.
      var r = validEmail(tx.email);
      if (!r.ok) { showError('payError', r.msg); return; }

      tx.email = r.value;
      tx.payment_status = STATUS;
      save(tx);

      var url = buildWaUrl(r.value);
      var waLink = $('waLink');
      if (waLink) waLink.setAttribute('href', url);

      // Dibuka langsung dari klik pengguna (aman dari pemblokir pop-up);
      // jika diblokir, pindah halaman ke tautan yang sama.
      var w = null;
      try { w = window.open(url, '_blank'); } catch (e) { w = null; }
      if (w) { try { w.opener = null; } catch (e2) { /* abaikan */ } }
      else { window.location.href = url; return; }

      var next = $('waNext');
      if (next) next.style.display = 'flex';
      payBtn.innerHTML = '<i class="bi bi-whatsapp me-2"></i>Buka WhatsApp Lagi';
    });
  }

  /* dipakai untuk pengujian */
  window.PBFlow = { validEmail: validEmail, buildWaMessage: buildWaMessage, buildWaUrl: buildWaUrl, WA_NUMBER: WA_NUMBER };
})();
