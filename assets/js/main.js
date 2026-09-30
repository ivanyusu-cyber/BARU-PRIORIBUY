/* PrioriBuy — script bersama semua halaman */
(function(){
  'use strict';

  /* navbar: state saat scroll + tombol kembali ke atas */
  var nav = document.getElementById('mainNav');
  var toTop = document.getElementById('backToTop');
  function onScroll(){
    if (nav) nav.classList.toggle('scrolled', window.scrollY > 30);
    if (toTop) toTop.classList.toggle('show', window.scrollY > 500);
  }
  window.addEventListener('scroll', onScroll, {passive:true});
  onScroll();
  if (toTop) toTop.addEventListener('click', function(){ window.scrollTo({top:0, behavior:'smooth'}); });

  /* tutup menu mobile setelah link diklik */
  document.querySelectorAll('#navMenu .nav-link').forEach(function(l){
    l.addEventListener('click', function(){
      var menu = document.getElementById('navMenu');
      if (menu && menu.classList.contains('show') && window.bootstrap){
        bootstrap.Collapse.getOrCreateInstance(menu).hide();
      }
    });
  });

  /* animasi muncul saat scroll */
  var reveals = document.querySelectorAll('.reveal');
  if ('IntersectionObserver' in window){
    var observer = new IntersectionObserver(function(entries){
      entries.forEach(function(e){
        if (e.isIntersecting){ e.target.classList.add('is-visible'); observer.unobserve(e.target); }
      });
    }, {threshold:0.12});
    reveals.forEach(function(el){ observer.observe(el); });
  } else {
    reveals.forEach(function(el){ el.classList.add('is-visible'); });
  }

  /* form: cegah klik ganda */
  document.querySelectorAll('form[data-submit-once]').forEach(function(f){
    f.addEventListener('submit', function(){
      var b = f.querySelector('button[type="submit"]');
      if (b){ setTimeout(function(){ b.disabled = true; }, 0); }
    });
  });

  /* Beranda: demo prioritas di hero */
  var demoData = {
    tinggi:{icon:'bi-bag-heart', note:'Sepatu lama sudah rusak, disarankan dibeli minggu ini.'},
    sedang:{icon:'bi-hourglass-split', note:'Masih layak dipakai, tunggu sampai gajian berikutnya.'},
    rendah:{icon:'bi-pause-circle', note:'Tunda dulu, evaluasi ulang bulan depan sebelum membeli.'}
  };
  document.querySelectorAll('.priority-pill').forEach(function(btn){
    btn.addEventListener('click', function(){
      document.querySelectorAll('.priority-pill').forEach(function(b){ b.classList.remove('active'); });
      btn.classList.add('active');
      var lvl = btn.dataset.level, d = demoData[lvl];
      var iconBox = document.getElementById('demoIcon');
      var note = document.getElementById('demoNote');
      if (!d || !iconBox || !note) return;
      iconBox.innerHTML = '<i class="bi ' + d.icon + '"></i>';
      note.textContent = d.note;
      note.className = 'item-note ' + lvl;
      iconBox.style.background = lvl==='tinggi' ? 'var(--pink-bg)' : lvl==='sedang' ? 'var(--blue-bg)' : 'var(--green-bg)';
      iconBox.style.color = lvl==='tinggi' ? 'var(--pink)' : lvl==='sedang' ? 'var(--blue)' : 'var(--green)';
    });
  });

  /* Beranda: tab preview aplikasi + grafik mini */
  var miniChart;
  function initMiniChart(){
    if (miniChart || !window.Chart) return;
    var ctx = document.getElementById('miniChart');
    if (!ctx) return;
    miniChart = new Chart(ctx, {
      type:'doughnut',
      data:{
        labels:['Tinggi','Sedang','Rendah'],
        datasets:[{ data:[45,35,20], backgroundColor:['#E85D9E','#3E7BFA','#3FA873'], borderWidth:0 }]
      },
      options:{
        plugins:{legend:{position:'bottom', labels:{boxWidth:10, font:{size:10, family:'Poppins'}}}},
        cutout:'62%'
      }
    });
  }
  document.querySelectorAll('.preview-tab-btn').forEach(function(btn){
    btn.addEventListener('click', function(){
      document.querySelectorAll('.preview-tab-btn').forEach(function(b){ b.classList.remove('active'); });
      document.querySelectorAll('.preview-screen').forEach(function(s){ s.classList.remove('active'); });
      btn.classList.add('active');
      var target = document.getElementById(btn.dataset.target);
      if (target) target.classList.add('active');
      if (btn.dataset.target === 'screen-statistik'){ initMiniChart(); }
    });
  });
})();
