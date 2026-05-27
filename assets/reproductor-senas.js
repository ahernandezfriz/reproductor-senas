/**
 * Reproductor de Video con Lengua de Señas - v1.0.7
 * Vanilla JS, sin dependencias. Compatible con múltiples instancias por página.
 */
(function () {
    'use strict';

    var INIT_FLAG = '__vspInitialized';

    function fmt(s) {
        var m = Math.floor(s / 60);
        return m + ':' + ('0' + Math.floor(s % 60)).slice(-2);
    }

    function initPlayer(wrap) {
        var vMain     = wrap.querySelector('.vsp-main');
        var vSigns    = wrap.querySelector('.vsp-signs');
        var signsWrap = wrap.querySelector('.vsp-signs-wrap');
        var dragHdl   = wrap.querySelector('.vsp-drag-handle');
        var btnPlay   = wrap.querySelector('.vsp-btn-play');
        var btnMute   = wrap.querySelector('.vsp-btn-mute');
        var btnSigns  = wrap.querySelector('.vsp-btn-signs');
        var signsLbl  = wrap.querySelector('.vsp-signs-label');
        var progBg    = wrap.querySelector('.vsp-progress-bg');
        var progFill  = wrap.querySelector('.vsp-progress-fill');
        var timeCur   = wrap.querySelector('.vsp-time-cur');
        var timeDur   = wrap.querySelector('.vsp-time-dur');
        var volRange  = wrap.querySelector('.vsp-vol');
        var overlay   = wrap.querySelector('.vsp-overlay-play');
        var stage     = wrap.querySelector('.vsp-stage');
        var btnFs     = wrap.querySelector('.vsp-btn-fs');

        if (!vMain || !progBg || !progFill || !timeCur || !timeDur || !stage) return;

        var signsOn = !!(btnSigns && vSigns && signsWrap);

        /* ---------- Play / Pause ---------- */
        function setPlaying(playing) {
            wrap.classList.toggle('is-playing', playing);
            if (btnPlay) btnPlay.setAttribute('aria-label', playing ? 'Pausar' : 'Reproducir');
        }

        function togglePlay() {
            if (vMain.paused) {
                vMain.play();
                if (signsOn && vSigns) vSigns.play();
            } else {
                vMain.pause();
                if (vSigns) vSigns.pause();
            }
        }

        function bindControlClick(el, handler) {
            if (!el) return;
            el.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                handler(e);
            });
        }

        bindControlClick(btnPlay, togglePlay);
        bindControlClick(overlay, togglePlay);

        // Accesibilidad: Enter/Space en el overlay
        if (overlay) {
            overlay.addEventListener('keydown', function (e) {
                if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); togglePlay(); }
            });
        }

        vMain.addEventListener('play',  function () { setPlaying(true); });
        vMain.addEventListener('pause', function () { setPlaying(false); });
        vMain.addEventListener('ended', function () {
            setPlaying(false);
            if (vSigns) vSigns.pause();
        });

        /* ---------- Metadatos y progreso ---------- */
        vMain.addEventListener('loadedmetadata', function () {
            timeDur.textContent = fmt(vMain.duration);
        });

        vMain.addEventListener('timeupdate', function () {
            var pct = vMain.duration ? (vMain.currentTime / vMain.duration) * 100 : 0;
            progFill.style.width = pct.toFixed(2) + '%';
            timeCur.textContent  = fmt(vMain.currentTime);
            progBg.setAttribute('aria-valuenow', Math.round(pct));

            // Re-sincronizar señas si hay desfase > 0.4s
            if (signsOn && vSigns && Math.abs(vSigns.currentTime - vMain.currentTime) > 0.4) {
                vSigns.currentTime = vMain.currentTime;
            }
        });

        /* ---------- Barra de progreso clicable ---------- */
        function seekTo(e) {
            var r     = progBg.getBoundingClientRect();
            var ratio = Math.min(1, Math.max(0, (e.clientX - r.left) / r.width));
            vMain.currentTime = ratio * vMain.duration;
            if (signsOn && vSigns) vSigns.currentTime = vMain.currentTime;
        }

        bindControlClick(progBg, seekTo);

        // Drag en la barra de progreso
        var seekDragging = false;
        progBg.addEventListener('mousedown', function (e) {
            seekDragging = true;
            seekTo(e);
        });
        document.addEventListener('mousemove', function (e) {
            if (seekDragging) seekTo(e);
        });
        document.addEventListener('mouseup', function () { seekDragging = false; });

        // Accesibilidad teclado en la barra
        progBg.addEventListener('keydown', function (e) {
            if (!vMain.duration) return;
            if (e.key === 'ArrowRight') vMain.currentTime = Math.min(vMain.duration, vMain.currentTime + 5);
            if (e.key === 'ArrowLeft')  vMain.currentTime = Math.max(0, vMain.currentTime - 5);
        });

        /* ---------- Volumen ---------- */
        if (volRange) {
            volRange.addEventListener('input', function () {
                vMain.volume = parseFloat(volRange.value);
                if (vMain.muted && vMain.volume > 0) {
                    vMain.muted = false;
                    wrap.classList.remove('is-muted');
                }
            });
        }

        if (btnMute) {
            bindControlClick(btnMute, function () {
                vMain.muted = !vMain.muted;
                wrap.classList.toggle('is-muted', vMain.muted);
                btnMute.setAttribute('aria-label', vMain.muted ? 'Activar sonido' : 'Silenciar');
            });
        }

        /* ---------- Lengua de señas ---------- */
        if (btnSigns && vSigns && signsWrap) {
            function applySignsState() {
                signsWrap.style.display = signsOn ? 'block' : 'none';
                signsWrap.setAttribute('aria-hidden', signsOn ? 'false' : 'true');
                if (signsLbl) signsLbl.textContent = signsOn ? 'Desactivar señas' : 'Activar señas';
                btnSigns.setAttribute('aria-pressed', String(signsOn));

                if (signsOn) {
                    vSigns.currentTime = vMain.currentTime;
                    if (!vMain.paused) vSigns.play();
                } else {
                    vSigns.pause();
                }
            }

            bindControlClick(btnSigns, function () {
                signsOn = !signsOn;
                applySignsState();
            });

            // Estado inicial: señas activadas por defecto.
            applySignsState();

            /* -- Drag del recuadro (mouse) -- */
            var dragging = false, ox = 0, oy = 0;

            function startDrag(clientX, clientY) {
                dragging = true;
                ox = clientX - signsWrap.offsetLeft;
                oy = clientY - signsWrap.offsetTop;
            }

            function moveDrag(clientX, clientY) {
                if (!dragging) return;
                var stageRect = stage.getBoundingClientRect();
                var maxX = stageRect.width  - signsWrap.offsetWidth;
                var maxY = stageRect.height - signsWrap.offsetHeight;
                signsWrap.style.left   = Math.min(maxX, Math.max(0, clientX - ox)) + 'px';
                signsWrap.style.top    = Math.min(maxY, Math.max(0, clientY - oy)) + 'px';
                signsWrap.style.right  = 'auto';
                signsWrap.style.bottom = 'auto';
            }

            if (dragHdl) {
                dragHdl.addEventListener('mousedown', function (e) {
                    startDrag(e.clientX, e.clientY);
                    e.preventDefault();
                });
                dragHdl.addEventListener('touchstart', function (e) {
                    startDrag(e.touches[0].clientX, e.touches[0].clientY);
                }, { passive: true });
            }

            document.addEventListener('mousemove',  function (e) { moveDrag(e.clientX, e.clientY); });
            document.addEventListener('mouseup',    function ()  { dragging = false; });
            document.addEventListener('touchmove',  function (e) { moveDrag(e.touches[0].clientX, e.touches[0].clientY); }, { passive: true });
            document.addEventListener('touchend',   function ()  { dragging = false; });
        }

        /* ---------- Pantalla completa ---------- */
        if (btnFs) {
            bindControlClick(btnFs, function () {
                if (!document.fullscreenElement) {
                    (stage.requestFullscreen || stage.webkitRequestFullscreen || stage.mozRequestFullScreen).call(stage);
                } else {
                    (document.exitFullscreen || document.webkitExitFullscreen || document.mozCancelFullScreen).call(document);
                }
            });

            document.addEventListener('fullscreenchange', function () {
                var isFs = !!document.fullscreenElement;
                wrap.classList.toggle('is-fullscreen', isFs);
                btnFs.setAttribute('aria-label', isFs ? 'Salir de pantalla completa' : 'Pantalla completa');
            });
        }
    }

    /* ---------- Inicializar reproductores (página, modales, contenido dinámico) ---------- */
    function initAll(root) {
        var scope = root && root.querySelectorAll ? root : document;
        scope.querySelectorAll('.vsp-wrapper').forEach(function (wrap) {
            if (wrap[INIT_FLAG]) return;
            wrap[INIT_FLAG] = true;
            initPlayer(wrap);
        });
    }

    function ensureInitFromTarget(target) {
        if (!target || !target.closest) return;
        var wrap = target.closest('.vsp-wrapper');
        if (!wrap || wrap[INIT_FLAG]) return;
        wrap[INIT_FLAG] = true;
        initPlayer(wrap);
    }

    function boot() {
        initAll(document);

        // Contenido insertado después de DOMContentLoaded (popups AJAX, tabs, etc.)
        if (typeof MutationObserver !== 'undefined') {
            var pending = null;
            var observer = new MutationObserver(function () {
                if (pending) return;
                pending = requestAnimationFrame(function () {
                    pending = null;
                    initAll(document);
                });
            });
            observer.observe(document.documentElement, { childList: true, subtree: true });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }

    // Fallback: inicializa al primer contacto del usuario (útil en popups clonados/inyección tardía).
    document.addEventListener('pointerdown', function (e) {
        ensureInitFromTarget(e.target);
    }, true);
    document.addEventListener('touchstart', function (e) {
        ensureInitFromTarget(e.target);
    }, { passive: true, capture: true });
    document.addEventListener('mousedown', function (e) {
        ensureInitFromTarget(e.target);
    }, true);

    // Elementor Popup (evento jQuery; no llega a addEventListener nativo)
    function onModalOpen() {
        requestAnimationFrame(function () { initAll(document); });
    }

    if (window.jQuery) {
        window.jQuery(document).on('elementor/popup/show', onModalOpen);

        // Hook oficial de Elementor frontend para widgets renderizados dinámicamente.
        window.jQuery(window).on('elementor/frontend/init', function () {
            if (!window.elementorFrontend || !window.elementorFrontend.hooks) return;
            window.elementorFrontend.hooks.addAction('frontend/element_ready/global', function ($scope) {
                var node = $scope && $scope[0] ? $scope[0] : document;
                initAll(node);
            });
        });
    }

    // Bootstrap 5 / modales con atributo data-bs-toggle
    document.addEventListener('shown.bs.modal', onModalOpen);

    window.VSP = { init: initAll, initPlayer: initPlayer };

})();
