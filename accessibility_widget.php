<!-- ═══════════════════════════════════════
         ACCESSIBILITY TOOLS WIDGET (shared include)
    ═══════════════════════════════════════ -->
    <style>
        /* ══════════════════════════════════════
           ACCESSIBILITY TOOLS WIDGET
        ══════════════════════════════════════ */

        /* FAB trigger button */
        #acc-fab {
            position: fixed;
            bottom: calc(env(safe-area-inset-bottom, 0px) + 28px);
            right: max(28px, env(safe-area-inset-right, 0px) + 28px);
            z-index: 99999; /* stays below the open panel (100000) so it never peeks out from behind it */
            width: 58px; height: 58px; border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: #fff; border: none; cursor: pointer;
            box-shadow: 0 6px 20px rgba(0,74,173,0.4);
            display: flex; align-items: center; justify-content: center;
            font-size: 22px; transition: var(--transition);
        }
        #acc-fab:hover { transform: scale(1.1); box-shadow: 0 10px 28px rgba(0,74,173,0.5); }
        #acc-fab .acc-fab-tooltip {
            position: absolute; right: 70px; white-space: nowrap;
            background: #001f4d; color: #fff; font-size: 12px; font-weight: 500;
            padding: 5px 12px; border-radius: 20px; pointer-events: none;
            opacity: 0; transition: opacity 0.2s;
        }
        #acc-fab:hover .acc-fab-tooltip { opacity: 1; }

        /* Panel
           Sized relative to the viewport at all times (not just under one
           width breakpoint) so it never overflows on short/landscape/small
           viewports, regardless of how wide they happen to be. */
        #acc-panel {
            position: fixed;
            bottom: calc(env(safe-area-inset-bottom, 0px) + 96px);
            right: max(16px, env(safe-area-inset-right, 0px) + 16px);
            left: auto;
            z-index: 100000;
            width: min(340px, calc(100vw - 32px));
            max-height: min(520px, calc(100dvh - 128px), calc(100vh - 128px));
            background: #fff;
            border-radius: 20px; box-shadow: 0 20px 60px rgba(0,74,173,0.18), 0 4px 16px rgba(0,0,0,0.08);
            border: 1px solid #e8eef8;
            transform: translateY(20px) scale(0.96); opacity: 0; pointer-events: none;
            transition: transform 0.3s cubic-bezier(0.34,1.56,0.64,1), opacity 0.25s ease;
            overflow: hidden;
            display: flex; flex-direction: column;
        }
        #acc-panel.acc-open { transform: translateY(0) scale(1); opacity: 1; pointer-events: all; }

        /* Only when the panel genuinely can't fit even the collapsed
           minimum height (very short viewports, e.g. landscape phones)
           do we drop the bottom offset and go almost edge-to-edge. */
        @media (max-height: 480px) {
            #acc-panel {
                bottom: calc(env(safe-area-inset-bottom, 0px) + 8px);
                top: 8px;
                max-height: none;
                height: calc(100dvh - 16px);
            }
        }

        .acc-panel-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: #fff; padding: 18px 20px 14px;
            display: flex; align-items: center; justify-content: space-between;
            flex-shrink: 0;
        }
        .acc-panel-header h3 { font-size: 15px; font-weight: 700; display: flex; align-items: center; gap: 9px; }
        .acc-panel-header p { font-size: 11.5px; opacity: .8; margin-top: 2px; }
        .acc-close-btn {
            background: rgba(255,255,255,0.18); border: none; color: #fff;
            width: 30px; height: 30px; border-radius: 50%; cursor: pointer;
            font-size: 13px; display: flex; align-items: center; justify-content: center;
            transition: background 0.2s; flex-shrink: 0;
        }
        .acc-close-btn:hover { background: rgba(255,255,255,0.35); }

        /* Category tabs */
        .acc-tabs { display: flex; border-bottom: 1px solid #eef0f8; flex-shrink: 0; }
        .acc-tab {
            flex: 1; padding: 11px 8px; border: none; background: none; cursor: pointer;
            font-family: 'Poppins', sans-serif; font-size: 11.5px; font-weight: 600;
            color: var(--text-light); transition: var(--transition); border-bottom: 2px solid transparent;
        }
        .acc-tab.active { color: var(--primary-color); border-bottom-color: var(--primary-color); background: #f5f8ff; }
        .acc-tab:hover:not(.active) { background: #f9fafb; color: var(--text-dark); }

        /* Tool cards inside panel — fills remaining space in the flex
           column and scrolls internally; min-height:0 is required for a
           flex child to actually shrink/scroll instead of overflowing. */
        .acc-body {
            padding: 16px; display: flex; flex-direction: column; gap: 10px;
            flex: 1 1 auto; min-height: 0; overflow-y: auto;
            -webkit-overflow-scrolling: touch; overscroll-behavior: contain;
        }
        .acc-tool-card {
            background: #f8faff; border: 1px solid #e4eaf8; border-radius: 14px;
            padding: 14px 16px; display: flex; align-items: flex-start; gap: 13px;
            transition: var(--transition);
        }
        .acc-tool-card:hover { border-color: var(--primary-color); background: #eef3ff; }
        .acc-tool-icon {
            width: 42px; height: 42px; border-radius: 12px; flex-shrink: 0;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: #fff; display: flex; align-items: center; justify-content: center;
            font-size: 17px;
        }
        .acc-tool-info { flex: 1; }
        .acc-tool-info h4 { font-size: 13px; font-weight: 700; color: var(--text-dark); margin-bottom: 3px; }
        .acc-tool-info p  { font-size: 11.5px; color: var(--text-light); line-height: 1.5; }
        .acc-voicenav-note { padding-left: 55px; }
        .acc-toggle {
            position: relative; display: inline-block; width: 42px; height: 23px; flex-shrink: 0; margin-top: 2px;
        }
        .acc-toggle input { opacity: 0; width: 0; height: 0; }
        .acc-slider {
            position: absolute; inset: 0; background: #cdd5e0; border-radius: 23px; cursor: pointer;
            transition: background 0.25s;
        }
        .acc-slider::before {
            content: ''; position: absolute; width: 17px; height: 17px; border-radius: 50%;
            background: #fff; left: 3px; top: 3px; transition: transform 0.25s;
            box-shadow: 0 1px 4px rgba(0,0,0,0.18);
        }
        .acc-toggle input:checked + .acc-slider { background: var(--primary-color); }
        .acc-toggle input:checked + .acc-slider::before { transform: translateX(19px); }

        /* High-contrast & TTS body states */
        body.acc-high-contrast { filter: contrast(1.6) brightness(0.95); }
        body.acc-tts-active .acc-tts-reading { outline: 2px dashed var(--accent-color); outline-offset: 3px; background: rgba(255,215,0,0.1); }
        .acc-tts-cursor { cursor: crosshair !important; }
        .acc-speech-indicator {
            position: fixed; bottom: 28px; left: 28px; z-index: 100000;
            background: #004aad; color: #fff; border-radius: 50px; padding: 10px 18px;
            font-size: 13px; font-weight: 600; display: flex; align-items: center; gap: 9px;
            box-shadow: 0 6px 20px rgba(0,74,173,0.4); display: none;
        }
        .acc-speech-indicator.visible { display: flex; }
        .acc-mic-pulse { width: 10px; height: 10px; border-radius: 50%; background: #4ade80; animation: livePulse 1.2s infinite; }

        /* Voice Navigation listening indicator */
        .acc-voice-indicator {
            position: fixed; bottom: 28px; left: 28px; z-index: 100000;
            background: #001f4d; color: #fff; border-radius: 50px; padding: 10px 18px;
            font-size: 13px; font-weight: 600; display: none; align-items: center; gap: 9px;
            box-shadow: 0 6px 20px rgba(0,74,173,0.4);
        }
        .acc-voice-indicator.visible { display: flex; }
        .acc-voice-indicator .acc-mic-pulse { background: #ffd700; }

        /* Below this width the panel already goes near-full-width via the
           width:min(340px, calc(100vw - 32px)) rule above; these are just
           density/typography tweaks for small screens. */
        @media (max-width: 480px) {
            #acc-fab {
                width: 50px; height: 50px; bottom: 16px; right: 16px; font-size: 19px;
            }
            #acc-fab .acc-fab-tooltip { display: none; } /* hover tooltip is meaningless on touch */

            #acc-panel { right: 12px; }

            .acc-panel-header { padding: 14px 16px 12px; }
            .acc-panel-header h3 { font-size: 14px; }
            .acc-panel-header p  { font-size: 11px; }

            .acc-tab { padding: 10px 4px; font-size: 11px; }

            .acc-body { padding: 12px; gap: 8px; }

            .acc-tool-card { padding: 12px; gap: 10px; }
            .acc-tool-icon { width: 36px; height: 36px; font-size: 15px; }

            .acc-voicenav-note { padding-left: 0; margin-top: 2px; }
        }

        /* Speech/voice status pills: keep them clear of the FAB and off the
           edges on any narrow viewport, not just <=480px. */
        @media (max-width: 600px) {
            .acc-speech-indicator,
            .acc-voice-indicator {
                left: 12px; right: 12px; bottom: calc(env(safe-area-inset-bottom, 0px) + 78px);
                width: auto; max-width: none;
                font-size: 12px; padding: 9px 14px;
                justify-content: center;
            }
        }
    </style>

    <!-- ═══════════════════════════════════════
         ACCESSIBILITY TOOLS WIDGET
    ═══════════════════════════════════════ -->

    <!-- Speech-to-text listening indicator -->
    <div class="acc-speech-indicator" id="accSpeechIndicator">
        <span class="acc-mic-pulse"></span>
        Listening…
    </div>

    <!-- Voice Navigation listening indicator -->
    <div class="acc-voice-indicator" id="accVoiceIndicator">
        <span class="acc-mic-pulse"></span>
        <span id="accVoiceIndicatorText">Voice Navigation On — say a command</span>
    </div>

    <!-- FAB button -->
    <button id="acc-fab" aria-label="Open Accessibility Tools" aria-expanded="false" aria-controls="acc-panel">
        <i class="fa-solid fa-universal-access"></i>
        <span class="acc-fab-tooltip">Accessibility Tools</span>
    </button>

    <!-- Accessibility panel -->
    <div id="acc-panel" role="dialog" aria-modal="true" aria-label="Accessibility Tools">
        <div class="acc-panel-header">
            <div>
                <h3><i class="fa-solid fa-universal-access"></i> Accessibility Tools</h3>
                <p>Assistive features for all users</p>
            </div>
            <button class="acc-close-btn" id="accPanelClose" aria-label="Close panel"><i class="fa-solid fa-xmark"></i></button>
        </div>

        <!-- Category tabs -->
        <div class="acc-tabs" role="tablist">
            <button class="acc-tab active" role="tab" aria-selected="true"  data-tab="deaf">
                <i class="fa-solid fa-ear-deaf"></i> Deaf / HoH
            </button>
            <button class="acc-tab"        role="tab" aria-selected="false" data-tab="blind">
                <i class="fa-solid fa-eye-slash"></i> Blind / VI
            </button>
        </div>

        <!-- ── Deaf / Hard-of-Hearing tab ── -->
        <div class="acc-body" id="tab-deaf">

            <!-- 1. Speech-to-Text -->
            <div class="acc-tool-card">
                <div class="acc-tool-icon"><i class="fa-solid fa-microphone"></i></div>
                <div class="acc-tool-info">
                    <h4>Speech-to-Text</h4>
                    <p>Converts your spoken words into typed text in any input field.</p>
                </div>
                <label class="acc-toggle" aria-label="Toggle Speech-to-Text">
                    <input type="checkbox" id="toggleSTT">
                    <span class="acc-slider"></span>
                </label>
            </div>

        </div><!-- /tab-deaf -->

        <!-- ── Blind / Visually-Impaired tab ── -->
        <div class="acc-body" id="tab-blind" style="display:none;">

            <!-- 1. Voice Navigation -->
            <div class="acc-tool-card" style="flex-direction:column; gap:10px;">
                <div style="display:flex; align-items:flex-start; gap:13px; width:100%;">
                    <div class="acc-tool-icon"><i class="fa-solid fa-wave-square"></i></div>
                    <div class="acc-tool-info">
                        <h4>Voice Navigation</h4>
                        <p>Navigate the site hands-free using spoken commands.</p>
                    </div>
                    <label class="acc-toggle" aria-label="Toggle Voice Navigation" style="margin-top:2px;">
                        <input type="checkbox" id="toggleVoiceNav">
                        <span class="acc-slider"></span>
                    </label>
                </div>
                <p class="acc-voicenav-note" style="font-size:11px; color:var(--text-light); line-height:1.6;">
                    Try: <em>"Go to home / about / services / schedule / contact / track application"</em>,
                    <em>"scroll up / down / top / bottom"</em>, <em>"read page"</em>, <em>"stop"</em>, or <em>"help"</em>.
                </p>
            </div>

            <!-- 2. Text-to-Speech -->
            <div class="acc-tool-card">
                <div class="acc-tool-icon"><i class="fa-solid fa-volume-high"></i></div>
                <div class="acc-tool-info">
                    <h4>Text-to-Speech</h4>
                    <p>Click any text on the page and have it read aloud instantly.</p>
                </div>
                <label class="acc-toggle" aria-label="Toggle Text-to-Speech">
                    <input type="checkbox" id="toggleTTS">
                    <span class="acc-slider"></span>
                </label>
            </div>

            <!-- Bonus: High Contrast -->
            <div class="acc-tool-card">
                <div class="acc-tool-icon"><i class="fa-solid fa-circle-half-stroke"></i></div>
                <div class="acc-tool-info">
                    <h4>High Contrast Mode</h4>
                    <p>Boosts contrast for better visibility in low-light environments.</p>
                </div>
                <label class="acc-toggle" aria-label="Toggle High Contrast">
                    <input type="checkbox" id="toggleContrast">
                    <span class="acc-slider"></span>
                </label>
            </div>

            <!-- Bonus: Font Size -->
            <div class="acc-tool-card" style="flex-direction:column; gap:10px;">
                <div style="display:flex; align-items:center; gap:13px;">
                    <div class="acc-tool-icon"><i class="fa-solid fa-text-height"></i></div>
                    <div class="acc-tool-info">
                        <h4>Text Size</h4>
                        <p>Adjust the page font size for easier reading.</p>
                    </div>
                </div>
                <div style="display:flex; align-items:center; gap:12px; padding-left:4px;">
                    <button class="acc-size-btn" id="accFontDec" aria-label="Decrease font size" style="width:34px;height:34px;border-radius:50%;border:2px solid #d0d8ea;background:#f8faff;font-size:16px;cursor:pointer;font-weight:700;color:var(--primary-color);transition:var(--transition);">−</button>
                    <span id="accFontLabel" style="font-size:13px;font-weight:600;color:var(--text-dark);min-width:40px;text-align:center;">100%</span>
                    <button class="acc-size-btn" id="accFontInc" aria-label="Increase font size" style="width:34px;height:34px;border-radius:50%;border:2px solid #d0d8ea;background:#f8faff;font-size:16px;cursor:pointer;font-weight:700;color:var(--primary-color);transition:var(--transition);">+</button>
                    <button id="accFontReset" style="font-size:11px;background:none;border:none;color:var(--text-light);cursor:pointer;text-decoration:underline;padding:0;">Reset</button>
                </div>
            </div>

        </div><!-- /tab-blind -->

    </div><!-- /#acc-panel -->

    <script>
    // ═══════════════════════════════════════
    //  ACCESSIBILITY TOOLS LOGIC
    // ═══════════════════════════════════════
    (function() {
        const fab        = document.getElementById('acc-fab');
        const panel      = document.getElementById('acc-panel');
        const closeBtn   = document.getElementById('accPanelClose');
        const tabs       = document.querySelectorAll('.acc-tab');
        const tabDeaf    = document.getElementById('tab-deaf');
        const tabBlind   = document.getElementById('tab-blind');

        // ── Panel open/close ──
        // On small screens the panel sits over a fixed FAB position; while
        // it's open we lock background scrolling so a short page's footer
        // can never shift/creep up behind it (mobile browsers resize the
        // viewport as their address bar shows/hides, which otherwise can
        // expose content beneath fixed-position elements).
        const MOBILE_LOCK_QUERY = '(max-width: 600px)';
        function setPanelOpen(open) {
            panel.classList.toggle('acc-open', open);
            fab.setAttribute('aria-expanded', open);
            if (window.matchMedia(MOBILE_LOCK_QUERY).matches) {
                document.documentElement.style.overflow = open ? 'hidden' : '';
                document.body.style.overflow = open ? 'hidden' : '';
            }
        }
        fab.addEventListener('click', () => {
            setPanelOpen(!panel.classList.contains('acc-open'));
        });
        closeBtn.addEventListener('click', () => setPanelOpen(false));
        document.addEventListener('keydown', e => {
            if (e.key === 'Escape' && panel.classList.contains('acc-open')) {
                setPanelOpen(false);
            }
        });

        // ── Tabs ──
        tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                tabs.forEach(t => { t.classList.remove('active'); t.setAttribute('aria-selected','false'); });
                tab.classList.add('active'); tab.setAttribute('aria-selected','true');
                const which = tab.dataset.tab;
                tabDeaf.style.display  = which === 'deaf'  ? 'flex' : 'none';
                tabBlind.style.display = which === 'blind' ? 'flex' : 'none';
            });
        });

        // ────────────────────────────────
        //  1. SPEECH-TO-TEXT
        // ────────────────────────────────
        const sttToggle   = document.getElementById('toggleSTT');
        const speechInd   = document.getElementById('accSpeechIndicator');
        let   recognition = null;
        let   sttActive   = false;
        let   listeningField = null;

        if ('webkitSpeechRecognition' in window || 'SpeechRecognition' in window) {
            const SR = window.SpeechRecognition || window.webkitSpeechRecognition;
            recognition = new SR();
            recognition.continuous = false;
            recognition.interimResults = true;
            recognition.lang = 'en-PH';

            recognition.onresult = e => {
                let transcript = Array.from(e.results).map(r => r[0].transcript).join('');
                if (listeningField) listeningField.value = transcript;
            };
            recognition.onend = () => {
                speechInd.classList.remove('visible');
                sttActive = false;
            };
        }

        sttToggle.addEventListener('change', () => {
            if (!recognition) { alert('Speech Recognition is not supported in this browser. Please use Chrome.'); sttToggle.checked = false; return; }
            if (sttToggle.checked) {
                // Attach click listener to all text inputs/textareas
                document.querySelectorAll('input[type="text"], input[type="email"], textarea').forEach(el => {
                    el.addEventListener('focus', startListeningFor);
                });
            } else {
                document.querySelectorAll('input[type="text"], input[type="email"], textarea').forEach(el => {
                    el.removeEventListener('focus', startListeningFor);
                });
                if (recognition) recognition.abort();
                speechInd.classList.remove('visible');
            }
        });

        // ────────────────────────────────
        //  2. VOICE NAVIGATION (Blind / VI)
        // ────────────────────────────────
        const voiceNavToggle  = document.getElementById('toggleVoiceNav');
        const voiceInd        = document.getElementById('accVoiceIndicator');
        const voiceIndText    = document.getElementById('accVoiceIndicatorText');
        let   voiceRecognition = null;
        let   voiceNavActive   = false;
        let   voiceNavShouldRun = false; // tracks intent so onend can auto-restart

        // Map of spoken phrases -> destination pages
        const voiceNavRoutes = [
            { page: 'index.php',            phrases: ['home', 'homepage', 'main page', 'go home'] },
            { page: 'about.php',            phrases: ['about', 'about us', 'about page'] },
            { page: 'service.php',          phrases: ['service', 'services', 'services page'] },
            { page: 'schedule.php',         phrases: ['schedule', 'book appointment', 'appointment', 'booking'] },
            { page: 'contact.php',          phrases: ['contact', 'contact us', 'contact page'] },
            { page: 'track_application.php', phrases: ['track application', 'track my application', 'application status', 'track'] }
        ];

        function voiceSpeak(text) {
            if (!('speechSynthesis' in window)) return;
            window.speechSynthesis.cancel();
            const utt = new SpeechSynthesisUtterance(text);
            utt.lang = 'en-PH'; utt.rate = 0.95; utt.pitch = 1;
            window.speechSynthesis.speak(utt);
        }

        function readPageAloud() {
            const main = document.querySelector('main') || document.body;
            const bits = Array.from(main.querySelectorAll('h1, h2, h3, p'))
                .map(el => el.innerText.trim())
                .filter(Boolean)
                .slice(0, 12); // keep it reasonable
            const text = bits.join('. ') || document.title;
            voiceSpeak(text);
        }

        function handleVoiceCommand(transcript) {
            const said = transcript.toLowerCase().trim();
            voiceIndText.textContent = 'Heard: "' + transcript.trim() + '"';

            // Navigation commands
            for (const route of voiceNavRoutes) {
                if (route.phrases.some(p => said.includes(p))) {
                    voiceSpeak('Going to ' + route.phrases[0]);
                    setTimeout(() => { window.location.href = route.page; }, 900);
                    return;
                }
            }

            // Scroll commands
            if (said.includes('scroll down'))        { window.scrollBy({ top: 400, behavior: 'smooth' }); voiceSpeak('Scrolling down'); return; }
            if (said.includes('scroll up'))           { window.scrollBy({ top: -400, behavior: 'smooth' }); voiceSpeak('Scrolling up'); return; }
            if (said.includes('scroll to top') || said.includes('top of page'))    { window.scrollTo({ top: 0, behavior: 'smooth' }); voiceSpeak('Going to top'); return; }
            if (said.includes('scroll to bottom') || said.includes('bottom of page')) { window.scrollTo({ top: document.body.scrollHeight, behavior: 'smooth' }); voiceSpeak('Going to bottom'); return; }

            // Read page
            if (said.includes('read page') || said.includes('read this page')) { readPageAloud(); return; }

            // Stop speaking
            if (said.includes('stop')) { window.speechSynthesis && window.speechSynthesis.cancel(); return; }

            // Help
            if (said.includes('help') || said.includes('what can i say')) {
                voiceSpeak('You can say: go to home, about, services, schedule, contact, or track application. You can also say scroll up, scroll down, read page, or stop.');
                return;
            }

            // Unrecognized
            voiceSpeak("Sorry, I didn't understand that command. Say help to hear the list of commands.");
        }

        if ('webkitSpeechRecognition' in window || 'SpeechRecognition' in window) {
            const SR2 = window.SpeechRecognition || window.webkitSpeechRecognition;
            voiceRecognition = new SR2();
            voiceRecognition.continuous     = true;
            voiceRecognition.interimResults = false;
            voiceRecognition.lang           = 'en-PH';

            voiceRecognition.onresult = e => {
                const last = e.results[e.results.length - 1];
                if (last && last[0]) handleVoiceCommand(last[0].transcript);
            };
            voiceRecognition.onerror = () => { /* swallow — will auto-restart via onend if still enabled */ };
            voiceRecognition.onend = () => {
                voiceNavActive = false;
                if (voiceNavShouldRun) {
                    try { voiceRecognition.start(); voiceNavActive = true; } catch (err) { /* already starting */ }
                } else {
                    voiceInd.classList.remove('visible');
                }
            };
        }

        voiceNavToggle.addEventListener('change', () => {
            if (!voiceRecognition) {
                alert('Voice Navigation is not supported in this browser. Please use Chrome.');
                voiceNavToggle.checked = false;
                return;
            }
            if (voiceNavToggle.checked) {
                voiceNavShouldRun = true;
                voiceIndText.textContent = 'Voice Navigation On — say a command';
                voiceInd.classList.add('visible');
                try { voiceRecognition.start(); voiceNavActive = true; } catch (err) { /* already running */ }
                voiceSpeak('Voice navigation on. Say help to hear the list of commands.');
            } else {
                voiceNavShouldRun = false;
                voiceInd.classList.remove('visible');
                if (voiceRecognition) voiceRecognition.abort();
                window.speechSynthesis && window.speechSynthesis.cancel();
            }
        });

        function startListeningFor(e) {
            if (!sttToggle.checked) return;
            listeningField = e.target;
            try { recognition.start(); speechInd.classList.add('visible'); sttActive = true; }
            catch(err) { /* already running */ }
        }

        // ────────────────────────────────
        //  3. TEXT-TO-SPEECH
        // ────────────────────────────────
        const ttsToggle = document.getElementById('toggleTTS');

        ttsToggle.addEventListener('change', () => {
            if (ttsToggle.checked) {
                document.body.classList.add('acc-tts-active', 'acc-tts-cursor');
                document.addEventListener('click', ttsClickHandler, true);
            } else {
                document.body.classList.remove('acc-tts-active', 'acc-tts-cursor');
                document.removeEventListener('click', ttsClickHandler, true);
                window.speechSynthesis && window.speechSynthesis.cancel();
            }
        });

        function ttsClickHandler(e) {
            if (e.target.closest('#acc-panel') || e.target.closest('#acc-fab')) return;
            const el = e.target.closest('p, h1, h2, h3, h4, li, label, td, th, button, a, span');
            if (!el) return;
            e.preventDefault(); e.stopPropagation();
            const text = el.innerText.trim();
            if (!text) return;
            window.speechSynthesis.cancel();
            const utt = new SpeechSynthesisUtterance(text);
            utt.lang = 'en-PH'; utt.rate = 0.95; utt.pitch = 1;
            el.classList.add('acc-tts-reading');
            utt.onend = () => el.classList.remove('acc-tts-reading');
            window.speechSynthesis.speak(utt);
        }

        // ────────────────────────────────
        //  5. HIGH CONTRAST (bonus)
        // ────────────────────────────────
        const contrastToggle = document.getElementById('toggleContrast');
        contrastToggle.addEventListener('change', () => {
            document.body.classList.toggle('acc-high-contrast', contrastToggle.checked);
        });

        // ────────────────────────────────
        //  6. FONT SIZE (bonus)
        // ────────────────────────────────
        let fontScale = 100;
        const fontLabel = document.getElementById('accFontLabel');

        function applyFontScale(val) {
            fontScale = Math.min(Math.max(val, 80), 160);
            document.documentElement.style.fontSize = fontScale + '%';
            fontLabel.textContent = fontScale + '%';
        }

        document.getElementById('accFontInc').addEventListener('click',   () => applyFontScale(fontScale + 10));
        document.getElementById('accFontDec').addEventListener('click',   () => applyFontScale(fontScale - 10));
        document.getElementById('accFontReset').addEventListener('click', () => applyFontScale(100));

    })();
    </script>