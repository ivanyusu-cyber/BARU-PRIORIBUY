<?php
declare(strict_types=1);
/* Jalur kompatibilitas: gunakan endpoint pendaftaran yang sudah dipakai flow hosting. */
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Location: mulai-premium.php', true, 302);
exit;
