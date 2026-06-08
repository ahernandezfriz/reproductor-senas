/**
 * Reproductor de Video con Lengua de Señas - v1.1.0
 * Vanilla JS, sin dependencias. Compatible con múltiples instancias por página.
 */
(function () {
    'use strict';

    var INIT_FLAG = '__vspInitialized';

    function fmt(s) {
        var m = Math.floor(s / 60);
        return m + ':' + ('0' + Math.floor(s % 60)).slice(-2);
    }

    function parseVttTime(value) {
        var parts = value.trim().split(':');
        var h = 0;
        var m = 0;
        var s = 0;
        if (parts.length === 3) {
            h = parseFloat(parts[0]);
            m = parseFloat(parts[1]);
            s = parseFloat(parts[2]);
        } else {
            m = parseFloat(parts[0]);
            s = parseFloat(parts[1]);
        }
        return (h * 3600) + (m * 60) + s;
    }

    function decodeBase64Utf8(b64) {
        if (!b64) return '';
        try {
            var binary = atob(b64);
            if (typeof TextDecoder !== 'undefined') {
                var bytes = new Uint8Array(binary.length);
                var n;
                for (n = 0; n < binary.length; n++) {
                    bytes[n] = binary.charCodeAt(n);
                }
                return new TextDecoder('utf-8').decode(bytes);
            }
            return decodeURIComponent(escape(binary));
        } catch (err) {
            return '';
        }
    }

    function parseVtt(text) {
        var cues = [];
        var lines;
        var i;

        if (!text) return cues;

        text = text.replace(/^\uFEFF/, '');
        lines = text.replace(/\r\n/g, '\n').replace(/\r/g, '\n').split('\n');
        i = 0;

        while (i < lines.length) {
            while (i < lines.length && !lines[i].trim()) {
                i++;
            }
            if (i >= lines.length) break;

            if (/^WEBVTT/i.test(lines[i]) || /^NOTE\b/i.test(lines[i]) || /^STYLE\b/i.test(lines[i])) {
                i++;
                continue;
            }

            if (lines[i].indexOf('-->') === -1) {
                i++;
            }
            if (i >= lines.length || lines[i].indexOf('-->') === -1) {
                continue;
            }

            var timeLine = lines[i];
            var times = timeLine.split('-->');
            var start = parseVttTime(times[0].trim().split(/\s+/)[0]);
            var end = parseVttTime(times[1].trim().split(/\s+/)[0]);
            i++;

            var parts = [];
            while (i < lines.length && lines[i].trim()) {
                if (i + 1 < lines.length && lines[i + 1].indexOf('-->') !== -1) {
                    break;
                }
                parts.push(lines[i].trim());
                i++;
            }

            if (parts.length && !isNaN(start) && !isNaN(end) && end > start) {
                cues.push({ start: start, end: end, text: parts.join('\n') });
            }
        }

        return cues;
    }

    function fetchVttContent(url) {
        var cfg = window.vspConfig || {};
        var params;
        var ajaxUrl = cfg.ajaxUrl;

        if (ajaxUrl && cfg.nonce) {
            params = new URLSearchParams();
            params.append('action', 'vsp_load_vtt');
            params.append('nonce', cfg.nonce);
            params.append('url', url);

            return fetch(ajaxUrl, {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
                body: params.toString()
            }).then(function (res) {
                return res.json();
            }).then(function (json) {
                if (!json || !json.success || !json.data || !json.data.content) {
                    throw new Error('VTT AJAX error');
                }
                return json.data.content;
            });
        }

        return fetch(url, { credentials: 'same-origin' }).then(function (res) {
            if (!res.ok) throw new Error('VTT fetch error');
            return res.text();
        });
    }

    function findCue(cues, time) {
        var i;
        for (i = 0; i < cues.length; i++) {
            if (time >= cues[i].start && time < cues[i].end) {
                return cues[i];
            }
        }
        return null;
    }

    function whenVimeoReady(cb) {
        var tries = 0;
        (function tick() {
            if (window.Vimeo && window.Vimeo.Player) {
                cb();
                return;
            }
            if (++tries < 120) {
                setTimeout(tick, 50);
            }
        }());
    }

    function createHtml5Media(el) {
        if (!el) return null;
        return {
            type: 'html5',
            el: el,
            get currentTime() { return el.currentTime; },
            set currentTime(t) { el.currentTime = t; },
            get duration() { return el.duration || 0; },
            get paused() { return el.paused; },
            get ended() { return el.ended; },
            get muted() { return el.muted; },
            set muted(v) { el.muted = v; },
            get volume() { return el.volume; },
            set volume(v) { el.volume = v; },
            get readyState() { return el.readyState; },
            play: function () { return el.play(); },
            pause: function () { return el.pause(); },
            addEventListener: function (ev, fn, opts) {
                el.addEventListener(ev, fn, opts);
            },
            bindLoad: function (onReady, onError) {
                var done = false;
                function finish() {
                    if (done) return;
                    done = true;
                    onReady();
                }
                el.addEventListener('loadedmetadata', finish, { once: true });
                el.addEventListener('error', onError, { once: true });
                if (el.readyState >= 1) finish();
            }
        };
    }

    function createVimeoMedia(iframe) {
        var player = new Vimeo.Player(iframe);
        var state = {
            currentTime: 0,
            duration: 0,
            paused: true,
            ended: false,
            muted: false,
            volume: 1,
            readyState: 0
        };
        var bus = {};

        function on(ev, fn) {
            if (!bus[ev]) bus[ev] = [];
            bus[ev].push(fn);
        }

        function emit(ev) {
            (bus[ev] || []).forEach(function (fn) { fn(); });
        }

        player.on('timeupdate', function (data) {
            state.currentTime = data.seconds;
            state.duration = data.duration || state.duration;
            emit('timeupdate');
        });
        player.on('play', function () {
            state.paused = false;
            state.ended = false;
            emit('play');
        });
        player.on('pause', function () {
            state.paused = true;
            emit('pause');
        });
        player.on('ended', function () {
            state.paused = true;
            state.ended = true;
            emit('ended');
        });

        return {
            type: 'vimeo',
            el: iframe,
            player: player,
            get currentTime() { return state.currentTime; },
            set currentTime(t) {
                player.setCurrentTime(t).catch(function () {});
                state.currentTime = t;
            },
            get duration() { return state.duration; },
            get paused() { return state.paused; },
            get ended() { return state.ended; },
            get muted() { return state.muted; },
            set muted(v) {
                state.muted = v;
                player.setMuted(!!v).catch(function () {});
            },
            get volume() { return state.volume; },
            set volume(v) {
                state.volume = v;
                player.setVolume(v).catch(function () {});
            },
            get readyState() { return state.readyState; },
            play: function () { return player.play(); },
            pause: function () { return player.pause(); },
            addEventListener: function (ev, fn, opts) {
                if (ev === 'loadedmetadata') {
                    player.ready().then(function () {
                        return Promise.all([
                            player.getDuration(),
                            player.getPaused(),
                            player.getVolume(),
                            player.getCurrentTime()
                        ]);
                    }).then(function (vals) {
                        state.duration = vals[0] || 0;
                        state.paused = vals[1];
                        state.volume = vals[2];
                        state.currentTime = vals[3] || 0;
                        state.readyState = 4;
                        fn();
                    }).catch(function () {});
                    return;
                }
                on(ev, fn);
            },
            bindLoad: function (onReady, onError) {
                player.ready().then(function () {
                    return Promise.all([ player.getDuration(), player.getPaused(), player.getCurrentTime() ]);
                }).then(function (vals) {
                    state.duration = vals[0] || 0;
                    state.paused = vals[1];
                    state.currentTime = vals[2] || 0;
                    state.readyState = 4;
                    onReady();
                }).catch(onError);
            }
        };
    }

    function bootstrapMedia(wrap, cb) {
        var mainType = wrap.getAttribute('data-main-type') || 'file';
        var senasType = wrap.getAttribute('data-senas-type') || 'file';

        function buildMain() {
            if (mainType === 'vimeo' && wrap.getAttribute('data-vimeo-id')) {
                var iframe = wrap.querySelector('.vsp-main-vimeo');
                if (!iframe) return Promise.reject(new Error('vimeo main'));
                return new Promise(function (resolve, reject) {
                    whenVimeoReady(function () {
                        try {
                            resolve(createVimeoMedia(iframe));
                        } catch (e) {
                            reject(e);
                        }
                    });
                });
            }
            var video = wrap.querySelector('video.vsp-main');
            if (!video) return Promise.reject(new Error('main video'));
            return Promise.resolve(createHtml5Media(video));
        }

        function buildSigns() {
            if (wrap.getAttribute('data-tiene-senas') !== '1') {
                return Promise.resolve(null);
            }
            if (senasType === 'vimeo' && wrap.getAttribute('data-senas-vimeo-id')) {
                var sIframe = wrap.querySelector('.vsp-signs-vimeo');
                if (!sIframe) return Promise.resolve(null);
                return new Promise(function (resolve) {
                    whenVimeoReady(function () {
                        resolve(createVimeoMedia(sIframe));
                    });
                });
            }
            var sVideo = wrap.querySelector('video.vsp-signs');
            return Promise.resolve(sVideo ? createHtml5Media(sVideo) : null);
        }

        Promise.all([ buildMain(), buildSigns() ])
            .then(function (parts) { cb(null, parts[0], parts[1]); })
            .catch(function (err) { cb(err, null, null); });
    }

    function vspI18n(key, fallback) {
        var cfg = window.vspConfig || {};
        return (cfg.i18n && cfg.i18n[key]) ? cfg.i18n[key] : fallback;
    }

    function initPlayer(wrap) {
        bootstrapMedia(wrap, function (err, mainMedia, signsMedia) {
            if (err || !mainMedia) {
                var loadErrorEl = wrap.querySelector('.vsp-load-error');
                var loadErrorText = wrap.querySelector('.vsp-load-error-text');
                wrap.classList.add('is-load-error');
                if (loadErrorEl) loadErrorEl.hidden = false;
                if (loadErrorText) {
                    loadErrorText.textContent = vspI18n('initError', 'Could not initialize the player. Check the main video URLs.');
                }
                return;
            }
            runPlayerInit(wrap, mainMedia, signsMedia);
        });
    }

    function runPlayerInit(wrap, vMain, vSigns) {
        var signsWrap = wrap.querySelector('.vsp-signs-wrap');
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
        var subsBox   = wrap.querySelector('.vsp-subtitles');
        var subsText  = wrap.querySelector('.vsp-subtitles-text');
        var btnSubs   = wrap.querySelector('.vsp-btn-subtitles');
        var subsLbl   = wrap.querySelector('.vsp-subtitles-label');

        if (!progBg || !progFill || !timeCur || !timeDur || !stage) return;

        if (vMain.type === 'vimeo') {
            wrap.classList.add('is-vimeo-main');
        }

        var signsOn = !!(btnSigns && vSigns && signsWrap);
        var subsOn  = !!(btnSubs && subsBox && subsText);
        var subsCues = [];

        var loadingEl     = wrap.querySelector('.vsp-loading');
        var loadingText   = wrap.querySelector('.vsp-loading-text');
        var loadErrorEl   = wrap.querySelector('.vsp-load-error');
        var loadErrorText = wrap.querySelector('.vsp-load-error-text');
        var assetWarnEl   = wrap.querySelector('.vsp-asset-warn');

        var LOAD_TIMEOUT_MS      = 45000;
        var SIGNS_TIMEOUT_MS     = 30000;
        var DURATION_TOLERANCE   = 2;
        var loadState = {
            main:  'pending',
            signs: vSigns ? 'pending' : 'skip',
            subs:  (btnSubs && subsBox) ? 'pending' : 'skip'
        };

        function showAssetWarn(msg) {
            if (!assetWarnEl || !msg) return;
            assetWarnEl.textContent = msg;
            assetWarnEl.hidden = false;
        }

        function showLoadError(msg) {
            wrap.classList.add('is-load-error');
            wrap.classList.remove('is-loading', 'is-ready');
            if (loadErrorEl) loadErrorEl.hidden = false;
            if (loadErrorText) loadErrorText.textContent = msg;
            if (loadingEl) loadingEl.hidden = true;
        }

        function updateLoadingMessage() {
            if (!loadingText) return;
            if (loadState.main === 'pending') {
                loadingText.textContent = vMain.type === 'vimeo'
                    ? vspI18n('loadingVimeo', 'Connecting to Vimeo…')
                    : vspI18n('loadingMain', 'Loading main video…');
            } else if (loadState.signs === 'pending') {
                loadingText.textContent = vSigns && vSigns.type === 'vimeo'
                    ? vspI18n('loadingSignsVimeo', 'Connecting sign language (Vimeo)…')
                    : vspI18n('loadingSigns', 'Loading sign language video…');
            } else if (loadState.subs === 'pending') {
                loadingText.textContent = vspI18n('loadingSubs', 'Loading subtitles…');
            } else {
                loadingText.textContent = vspI18n('preparing', 'Preparing player…');
            }
        }

        function checkDurationMatch() {
            if (!vSigns || !vMain.duration || !vSigns.duration) return;
            if (Math.abs(vMain.duration - vSigns.duration) > DURATION_TOLERANCE) {
                wrap.classList.add('is-duration-mismatch');
                showAssetWarn(vspI18n('durationMismatch', 'Main and sign language videos have different durations; drift may occur near the end.'));
            }
        }

        function setAssetState(asset, state) {
            loadState[asset] = state;
            updateLoadUi();
        }

        function setSubsReady(ok) {
            if (loadState.subs === 'skip') return;
            setAssetState('subs', ok ? 'ready' : 'error');
            if (!ok) {
                subsOn = false;
                wrap.classList.add('is-subs-error');
                wrap.classList.remove('is-subs-on');
                if (btnSubs) {
                    btnSubs.setAttribute('aria-pressed', 'false');
                    btnSubs.setAttribute('aria-label', vspI18n('subsUnavailable', 'Subtitles unavailable'));
                }
                if (subsLbl) subsLbl.textContent = vspI18n('subsUnavailable', 'Subtitles unavailable');
                showAssetWarn(vspI18n('subsLoadError', 'Could not load subtitles.'));
            }
        }

        function isStillLoading() {
            if (loadState.main === 'pending') return true;
            if (loadState.signs === 'pending') return true;
            return false;
        }

        function canPlayback() {
            return loadState.main === 'ready' && !wrap.classList.contains('is-load-error');
        }

        function updateLoadUi() {
            updateLoadingMessage();
            var loading = isStillLoading() && loadState.main !== 'error';
            var ready   = canPlayback() && !isStillLoading();

            wrap.classList.toggle('is-loading', loading);
            wrap.classList.toggle('is-ready', ready);

            if (loadingEl) {
                loadingEl.hidden = !loading;
                loadingEl.setAttribute('aria-busy', loading ? 'true' : 'false');
            }

            if (ready && vMain.duration) {
                checkDurationMatch();
                timeDur.textContent = fmt(vMain.duration);
            }
        }

        function degradeSigns(reason) {
            if (loadState.signs === 'error') return;
            loadState.signs = 'error';
            signsOn = false;
            wrap.classList.add('is-signs-error');
            if (signsWrap) {
                signsWrap.style.display = 'none';
                signsWrap.setAttribute('aria-hidden', 'true');
            }
            if (btnSigns) {
                btnSigns.disabled = true;
                btnSigns.setAttribute('aria-disabled', 'true');
            }
            showAssetWarn(reason || vspI18n('signsDegraded', 'Could not load the sign language video. The main video remains available.'));
            updateLoadUi();
        }

        function bindVideoAsset(media, asset) {
            if (!media || loadState[asset] === 'skip') return;

            var timeoutMs = asset === 'signs' ? SIGNS_TIMEOUT_MS : LOAD_TIMEOUT_MS;
            var timedOut  = false;

            function markReady() {
                if (loadState[asset] === 'ready' || loadState[asset] === 'error') return;
                setAssetState(asset, 'ready');
                if (asset === 'signs' && signsOn && vSigns) {
                    vSigns.currentTime = vMain.currentTime || 0;
                }
            }

            function markError() {
                if (loadState[asset] === 'error' || loadState[asset] === 'ready') return;
                if (asset === 'signs') {
                    degradeSigns(timedOut ? vspI18n('signsTimeout', 'The sign language video took too long to load.') : vspI18n('signsLoadError', 'Could not load the sign language video.'));
                    return;
                }
                setAssetState('main', 'error');
                showLoadError(
                    vMain.type === 'vimeo'
                        ? vspI18n('mainVimeoError', 'Could not load the Vimeo video. Check the link and privacy settings.')
                        : vspI18n('mainFileError', 'Could not load the main video. Check the URL or your connection.')
                );
            }

            media.bindLoad(markReady, markError);

            setTimeout(function () {
                if (loadState[asset] === 'pending') {
                    timedOut = true;
                    markError();
                }
            }, timeoutMs);
        }

        function syncSignsToMain() {
            if (!signsOn || !vSigns || loadState.signs !== 'ready') return;
            if (Math.abs(vSigns.currentTime - vMain.currentTime) > 0.05) {
                vSigns.currentTime = vMain.currentTime;
            }
        }

        function startSignsPlayback() {
            if (!signsOn || !vSigns || loadState.signs !== 'ready') return;
            syncSignsToMain();
            var signsPlay = vSigns.play();
            if (signsPlay && typeof signsPlay.catch === 'function') {
                signsPlay.catch(function () {});
            }
        }

        wrap.classList.add('is-loading');
        bindVideoAsset(vMain, 'main');
        bindVideoAsset(vSigns, 'signs');
        updateLoadUi();

        function updateSubtitlesDisplay() {
            if (!subsBox || !subsText) return;

            if (!subsOn || !subsCues.length) {
                subsText.textContent = '';
                subsBox.setAttribute('aria-hidden', 'true');
                subsBox.classList.remove('is-visible');
                return;
            }

            var cue = findCue(subsCues, vMain.currentTime);
            if (!cue) {
                subsText.textContent = '';
                subsBox.setAttribute('aria-hidden', 'true');
                subsBox.classList.remove('is-visible');
                return;
            }

            subsText.textContent = cue.text;
            subsBox.setAttribute('aria-hidden', 'false');
            subsBox.classList.add('is-visible');
        }

        /* ---------- Play / Pause ---------- */
        function setPlaying(playing) {
            wrap.classList.toggle('is-playing', playing);
            if (btnPlay) btnPlay.setAttribute('aria-label', playing ? vspI18n('pause', 'Pause') : vspI18n('play', 'Play'));
        }

        function togglePlay() {
            if (!canPlayback() || isStillLoading() || wrap.classList.contains('is-load-error')) {
                return;
            }

            if (vMain.paused) {
                var mainPlay = vMain.play();
                startSignsPlayback();
                if (mainPlay && typeof mainPlay.catch === 'function') {
                    mainPlay.catch(function () {});
                }
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

        var blockStageClick = false;
        var touchRevealOnly = false;

        function isUiTarget(target) {
            if (!target || !target.closest) return false;
            return !!target.closest('.vsp-controls, .vsp-signs-wrap, .vsp-overlay-play, .vsp-btn-signs, .vsp-subtitles, .vsp-btn-subtitles');
        }

        function onStageClick(e) {
            if (blockStageClick || touchRevealOnly) return;
            if (isUiTarget(e.target)) return;
            togglePlay();
        }

        stage.addEventListener('click', onStageClick);
        if (vMain.type === 'html5' && vMain.el) {
            vMain.el.addEventListener('click', function (e) {
                e.stopPropagation();
                onStageClick(e);
            });
        }

        // Accesibilidad: Enter/Space en el overlay
        if (overlay) {
            overlay.addEventListener('keydown', function (e) {
                if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); togglePlay(); }
            });
        }

        var controlsEl = wrap.querySelector('.vsp-controls');
        var controlsHideTimer = null;

        function isMainPlaying() {
            return !vMain.paused && !vMain.ended;
        }

        function syncControlsUi() {
            if (!controlsEl) return;
            var hidden = isMainPlaying() && !wrap.classList.contains('is-controls-visible');
            controlsEl.classList.toggle('vsp-controls--hidden', hidden);
            wrap.classList.toggle('vsp-controls-hidden', hidden);
        }

        function showControlsUi() {
            wrap.classList.add('is-controls-visible');
            syncControlsUi();
        }

        function hideControlsUi() {
            wrap.classList.remove('is-controls-visible');
            syncControlsUi();
        }

        function scheduleHideControlsUi() {
            clearTimeout(controlsHideTimer);
            if (!isMainPlaying()) return;
            controlsHideTimer = setTimeout(hideControlsUi, 2500);
        }

        vMain.addEventListener('play',  function () {
            if (!canPlayback()) {
                vMain.pause();
                return;
            }
            startSignsPlayback();
            setPlaying(true);
            hideControlsUi();
        });
        vMain.addEventListener('pause', function () {
            setPlaying(false);
            hideControlsUi();
            clearTimeout(controlsHideTimer);
            syncControlsUi();
        });
        vMain.addEventListener('ended', function () {
            setPlaying(false);
            hideControlsUi();
            clearTimeout(controlsHideTimer);
            syncControlsUi();
            if (vSigns) vSigns.pause();
        });

        /* Controles visibles al hover / toque (estilo YouTube) */
        wrap.addEventListener('mouseenter', function () {
            if (isMainPlaying()) showControlsUi();
        });
        wrap.addEventListener('mouseleave', function () {
            if (isMainPlaying()) hideControlsUi();
        });
        wrap.addEventListener('mousemove', function () {
            if (!isMainPlaying()) return;
            showControlsUi();
            scheduleHideControlsUi();
        });
        wrap.addEventListener('touchstart', function (e) {
            if (!isMainPlaying()) return;
            if (isUiTarget(e.target)) return;
            touchRevealOnly = false;
            if (!wrap.classList.contains('is-controls-visible')) {
                showControlsUi();
                scheduleHideControlsUi();
                touchRevealOnly = true;
            }
        }, { passive: true });

        wrap.addEventListener('touchend', function () {
            if (!touchRevealOnly) return;
            setTimeout(function () { touchRevealOnly = false; }, 350);
        }, { passive: true });

        syncControlsUi();

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

            updateSubtitlesDisplay();
        });

        /* ---------- Barra de progreso clicable ---------- */
        function seekTo(e) {
            var r     = progBg.getBoundingClientRect();
            var ratio = Math.min(1, Math.max(0, (e.clientX - r.left) / r.width));
            vMain.currentTime = ratio * vMain.duration;
            if (signsOn && vSigns) vSigns.currentTime = vMain.currentTime;
            updateSubtitlesDisplay();
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
                btnMute.setAttribute('aria-label', vMain.muted ? vspI18n('unmute', 'Unmute') : vspI18n('mute', 'Mute'));
            });
        }

        /* ---------- Subtítulos (español, VTT) ---------- */
        if (btnSubs && subsBox && subsText) {
            function applySubsState() {
                wrap.classList.toggle('is-subs-on', subsOn);
                if (subsLbl) subsLbl.textContent = subsOn ? vspI18n('subsOn', 'Disable subtitles') : vspI18n('subsOff', 'Enable subtitles');
                btnSubs.setAttribute('aria-pressed', String(subsOn));
                btnSubs.setAttribute('aria-label', subsOn ? vspI18n('subsOn', 'Disable subtitles') : vspI18n('subsOff', 'Enable subtitles'));
                updateSubtitlesDisplay();
            }

            bindControlClick(btnSubs, function () {
                subsOn = !subsOn;
                applySubsState();
            });

            applySubsState();

            function applyVttRaw(raw) {
                subsCues = parseVtt(raw);
                var ok = subsCues.length > 0;
                wrap.classList.toggle('is-subs-error', !ok);
                setSubsReady(ok);
                updateSubtitlesDisplay();
            }

            function loadSubtitles() {
                var cuesB64   = wrap.getAttribute('data-subtitulos-cues');
                var inlineB64 = wrap.getAttribute('data-subtitulos-data');
                var subsUrl   = wrap.getAttribute('data-subtitulos');

                if (cuesB64) {
                    try {
                        subsCues = JSON.parse(decodeBase64Utf8(cuesB64));
                        if (!Array.isArray(subsCues)) subsCues = [];
                    } catch (e) {
                        subsCues = [];
                    }
                    var ok = subsCues.length > 0;
                    wrap.classList.toggle('is-subs-error', !ok);
                    setSubsReady(ok);
                    updateSubtitlesDisplay();
                    return;
                }

                if (inlineB64) {
                    applyVttRaw(decodeBase64Utf8(inlineB64));
                    return;
                }

                if (!subsUrl) {
                    setSubsReady(false);
                    return;
                }

                fetchVttContent(subsUrl)
                    .then(applyVttRaw)
                    .catch(function () {
                        subsCues = [];
                        wrap.classList.add('is-subs-error');
                        setSubsReady(false);
                        updateSubtitlesDisplay();
                    });
            }

            loadSubtitles();
        } else if (loadState.subs !== 'skip') {
            setSubsReady(false);
        }

        /* ---------- Lengua de señas ---------- */
        if (btnSigns && vSigns && signsWrap) {
            function applySignsState() {
                var mainWasPlaying = !vMain.paused && !vMain.ended && vMain.readyState > 2;

                signsWrap.style.display = signsOn ? 'block' : 'none';
                signsWrap.setAttribute('aria-hidden', signsOn ? 'false' : 'true');
                if (signsLbl) signsLbl.textContent = signsOn ? vspI18n('signsOn', 'Disable sign language') : vspI18n('signsOff', 'Enable sign language');
                btnSigns.setAttribute('aria-pressed', String(signsOn));

                if (signsOn && loadState.signs === 'ready') {
                    vSigns.currentTime = vMain.currentTime;
                    if (mainWasPlaying) {
                        var signsPlayPromise = vSigns.play();
                        if (signsPlayPromise && typeof signsPlayPromise.catch === 'function') {
                            signsPlayPromise.catch(function () {});
                        }
                    }
                } else {
                    // No pausar señas al ocultar: sigue corriendo para evitar desincronización.
                }

                // Si el click del toggle interrumpió al principal por scripts externos,
                // restaura reproducción para mantener continuidad del contenido.
                if (mainWasPlaying && vMain.paused) {
                    var mainPlayPromise = vMain.play();
                    if (mainPlayPromise && typeof mainPlayPromise.catch === 'function') {
                        mainPlayPromise.catch(function () {});
                    }
                }
            }

            bindControlClick(btnSigns, function () {
                if (loadState.signs === 'error' || btnSigns.disabled) return;
                signsOn = !signsOn;
                applySignsState();
            });

            // Estado inicial: señas activadas por defecto.
            applySignsState();

            /* -- Arrastrar recuadro de señas (toda el área) -- */
            var dragging = false;
            var signsMoved = false;
            var dragOx = 0;
            var dragOy = 0;

            function anchorSignsPosition() {
                var wrapRect  = signsWrap.getBoundingClientRect();
                var stageRect = stage.getBoundingClientRect();
                signsWrap.style.left   = (wrapRect.left - stageRect.left) + 'px';
                signsWrap.style.top    = (wrapRect.top - stageRect.top) + 'px';
                signsWrap.style.right  = 'auto';
                signsWrap.style.bottom = 'auto';
            }

            function startDrag(clientX, clientY) {
                anchorSignsPosition();
                var wrapRect = signsWrap.getBoundingClientRect();
                dragOx = clientX - wrapRect.left;
                dragOy = clientY - wrapRect.top;
                dragging = true;
                signsMoved = false;
                signsWrap.classList.add('is-dragging');
            }

            function endDrag() {
                dragging = false;
                signsWrap.classList.remove('is-dragging');
                if (signsMoved) {
                    blockStageClick = true;
                    setTimeout(function () { blockStageClick = false; }, 250);
                }
            }

            function moveDrag(clientX, clientY) {
                if (!dragging) return;
                signsMoved = true;
                var stageRect = stage.getBoundingClientRect();
                var x = clientX - stageRect.left - dragOx;
                var y = clientY - stageRect.top - dragOy;
                var maxX = stageRect.width - signsWrap.offsetWidth;
                var maxY = stageRect.height - signsWrap.offsetHeight;
                signsWrap.style.left = Math.min(maxX, Math.max(0, x)) + 'px';
                signsWrap.style.top  = Math.min(maxY, Math.max(0, y)) + 'px';
            }

            function onSignsPointerDown(e) {
                if (!signsOn || signsWrap.getAttribute('aria-hidden') === 'true') return;
                if (e.button !== undefined && e.button !== 0) return;
                startDrag(e.clientX, e.clientY);
                e.preventDefault();
                e.stopPropagation();
            }

            signsWrap.addEventListener('mousedown', onSignsPointerDown);
            signsWrap.addEventListener('touchstart', function (e) {
                if (!signsOn || signsWrap.getAttribute('aria-hidden') === 'true') return;
                startDrag(e.touches[0].clientX, e.touches[0].clientY);
                e.preventDefault();
            }, { passive: false });

            document.addEventListener('mousemove', function (e) {
                moveDrag(e.clientX, e.clientY);
            });
            document.addEventListener('mouseup', endDrag);
            document.addEventListener('touchmove', function (e) {
                if (!dragging) return;
                moveDrag(e.touches[0].clientX, e.touches[0].clientY);
            }, { passive: true });
            document.addEventListener('touchend', endDrag);
            document.addEventListener('touchcancel', endDrag);
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
                btnFs.setAttribute('aria-label', isFs ? vspI18n('exitFullscreen', 'Exit fullscreen') : vspI18n('fullscreen', 'Fullscreen'));
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
