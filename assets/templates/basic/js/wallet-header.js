document.addEventListener('DOMContentLoaded', function(){
    function initWalletSliders(){
        document.querySelectorAll('.wallet-slider').forEach(function(slider){
            const slides = slider.querySelectorAll('.wallet-slide');
            if (!slides.length) return;
            const effect = slider.classList.contains('fade') ? 'fade' : (slider.classList.contains('parallax') ? 'parallax' : 'slide');
            if (effect === 'slide'){
                let idx = 0;
                setInterval(function(){
                    idx = (idx + 1) % slides.length;
                    slides.forEach(function(s, i){ s.style.transform = `translateX(${(i - idx) * 120}px)`; s.style.opacity = 1; });
                }, 3000);
            } else if (effect === 'fade'){
                let idx = 0;
                setInterval(function(){
                    slides.forEach(function(s, i){ s.style.opacity = 0; });
                    slides[idx].style.opacity = 1;
                    idx = (idx + 1) % slides.length;
                }, 3000);
            } else if (effect === 'parallax'){
                // very simple parallax: shift according to mouse
                slider.addEventListener('mousemove', function(e){
                    const rect = slider.getBoundingClientRect();
                    const relX = (e.clientX - rect.left) / rect.width - 0.5;
                    slides.forEach(function(s, i){ s.style.transform = `translateX(${relX * 8 * (i+1)}px)`; });
                });
            }
        });
    }
    initWalletSliders();

    // Wallet header background slider (auto) for wallet-header-card
    function initWalletHeaderBackground(){
        document.querySelectorAll('.wallet-header-card').forEach(function(card){
            const slider = card.querySelector('.whc-bg-slider');
            if(!slider) return;
            const slides = slider.querySelectorAll('.whc-bg-slide');
            if(!slides.length) return;
            let idx = 0;
            // position slides
            slides.forEach(function(s, i){
                s.classList.remove('is-active','is-left','is-right');
                if(i === 0) s.classList.add('is-active');
                else s.classList.add('is-right');
            });

            setInterval(function(){
                const prev = idx;
                idx = (idx + 1) % slides.length;
                slides.forEach(function(s, i){
                    s.classList.remove('is-active','is-left','is-right');
                    if(i === idx) s.classList.add('is-active');
                    else if(i < idx) s.classList.add('is-left');
                    else s.classList.add('is-right');
                });
            }, 3500);
        });
    }
    initWalletHeaderBackground();

    // responsive background selection: choose appropriate bg URL based on viewport width
    function applyResponsiveBackgrounds(){
        document.querySelectorAll('.whc-bg-slide').forEach(function(slide){
            const bgSm = slide.getAttribute('data-bg-sm');
            const bgMd = slide.getAttribute('data-bg-md');
            const bgLg = slide.getAttribute('data-bg-lg');
            const bgDefault = slide.getAttribute('data-bg-default');
            const w = window.innerWidth;
            let url = bgDefault || '';
            if (w <= 640 && bgSm) url = bgSm;
            else if (w <= 1024 && bgMd) url = bgMd;
            else if (w > 1024 && bgLg) url = bgLg;
            if (url) slide.style.backgroundImage = 'url("' + url + '")';
        });
    }
    applyResponsiveBackgrounds();
    window.addEventListener('resize', function(){
        applyResponsiveBackgrounds();
    });
});
