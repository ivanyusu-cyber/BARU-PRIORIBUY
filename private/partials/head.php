<?php /* Partial: head + navbar. Butuh $pageTitle, $pageDesc, $navActive, $noindex. */ ?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= pb_h($pageTitle) ?></title>
<meta name="description" content="<?= pb_h($pageDesc) ?>">
<?php if (!empty($noindex)): ?><meta name="robots" content="noindex, nofollow">
<?php endif; ?><link rel="icon" type="image/png" href="assets/img/favicon-48.png">
<link rel="apple-touch-icon" href="assets/img/apple-touch-icon.png">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,400;0,500;0,600;0,700;0,800;1,500&display=swap" rel="stylesheet">

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="assets/css/style.css">
<noscript><style>.reveal{opacity:1;transform:none;}</style></noscript>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar navbar-pb navbar-expand-lg" id="mainNav">
  <div class="container">
    <a class="navbar-brand" href="index.html">
      <img src="assets/img/logo-horizontal.png" alt="PrioriBuy Logo" width="136" height="34">
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu" aria-controls="navMenu" aria-expanded="false" aria-label="Buka menu">
      <i class="bi bi-list fs-2"></i>
    </button>
    <div class="collapse navbar-collapse justify-content-end" id="navMenu">
      <ul class="navbar-nav align-items-lg-center gap-lg-1">
        <li class="nav-item"><a class="nav-link<?= $navActive === 'beranda' ? ' active' : '' ?>" href="index.html"<?= $navActive === 'beranda' ? ' aria-current="page"' : '' ?>>Beranda</a></li>
        <li class="nav-item"><a class="nav-link<?= $navActive === 'tentang' ? ' active' : '' ?>" href="tentang.html"<?= $navActive === 'tentang' ? ' aria-current="page"' : '' ?>>Tentang</a></li>
        <li class="nav-item"><a class="nav-link<?= $navActive === 'fitur' ? ' active' : '' ?>" href="fitur.html"<?= $navActive === 'fitur' ? ' aria-current="page"' : '' ?>>Fitur</a></li>
        <li class="nav-item"><a class="nav-link<?= $navActive === 'cara-kerja' ? ' active' : '' ?>" href="cara-kerja.html"<?= $navActive === 'cara-kerja' ? ' aria-current="page"' : '' ?>>Cara Kerja</a></li>
        <li class="nav-item"><a class="nav-link<?= $navActive === 'premium' ? ' active' : '' ?>" href="premium.html"<?= $navActive === 'premium' ? ' aria-current="page"' : '' ?>>Premium</a></li>
        <li class="nav-item"><a class="nav-link<?= $navActive === 'faq' ? ' active' : '' ?>" href="faq.html"<?= $navActive === 'faq' ? ' aria-current="page"' : '' ?>>FAQ</a></li>
        <li class="nav-item"><a class="nav-link<?= $navActive === 'kontak' ? ' active' : '' ?>" href="kontak.html"<?= $navActive === 'kontak' ? ' aria-current="page"' : '' ?>>Kontak</a></li>
        <li class="nav-item ms-lg-3 mt-2 mt-lg-0">
          <a class="btn btn-pb-primary btn-pb-sm btn-nav-cta" href="https://drive.google.com/drive/folders/1Ci_4rXnQCx0uQhJHQwMpRNOG5PWsRauc" target="_blank" rel="noopener noreferrer">Coba Sekarang</a>
        </li>
      </ul>
    </div>
  </div>
</nav>

