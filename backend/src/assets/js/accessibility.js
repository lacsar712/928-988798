(function() {
    'use strict';

    const STORAGE_KEY = 'a11y_preferences';
    const API_URL = 'accessibility_api.php';

    const defaultPrefs = {
        font_size: 100,
        high_contrast: 0,
        eye_care: 0,
        tts_mode: 0,
        focus_highlight: 0
    };

    let currentPrefs = { ...defaultPrefs };
    let ttsSpeaking = null;
    let ttsUtterance = null;

    function apiCall(action, method = 'GET', data = null) {
        const url = API_URL + '?action=' + action;
        const options = { method: method };
        
        if (method === 'POST' && data) {
            options.headers = { 'Content-Type': 'application/x-www-form-urlencoded' };
            options.body = new URLSearchParams(data).toString();
        }
        
        return fetch(url, options).then(r => r.json());
    }

    function saveToLocalStorage(prefs) {
        try {
            localStorage.setItem(STORAGE_KEY, JSON.stringify(prefs));
        } catch (e) {
            console.warn('localStorage not available');
        }
    }

    function loadFromLocalStorage() {
        try {
            const data = localStorage.getItem(STORAGE_KEY);
            if (data) {
                return JSON.parse(data);
            }
        } catch (e) {
            console.warn('localStorage not available');
        }
        return null;
    }

    function saveToServer(prefs) {
        return apiCall('save_preferences', 'POST', prefs);
    }

    function loadFromServer() {
        return apiCall('get_preferences');
    }

    function resetOnServer() {
        return apiCall('reset_preferences');
    }

    function applyFontSize(size) {
        document.documentElement.style.fontSize = size + '%';
    }

    function applyHighContrast(enabled) {
        document.body.classList.toggle('a11y-high-contrast', enabled === 1);
    }

    function applyEyeCare(enabled) {
        document.body.classList.toggle('a11y-eye-care', enabled === 1);
    }

    function applyFocusHighlight(enabled) {
        document.body.classList.toggle('a11y-focus-highlight', enabled === 1);
    }

    function applyTTSMode(enabled) {
        document.body.classList.toggle('a11y-tts-active', enabled === 1);
    }

    function applyAllPrefs(prefs) {
        applyFontSize(prefs.font_size);
        applyHighContrast(prefs.high_contrast);
        applyEyeCare(prefs.eye_care);
        applyTTSMode(prefs.tts_mode);
        applyFocusHighlight(prefs.focus_highlight);
        updatePanelUI(prefs);
    }

    function speak(text, element) {
        if (!('speechSynthesis' in window)) {
            alert('您的浏览器不支持语音合成功能');
            return;
        }

        if (ttsSpeaking && ttsSpeaking === element) {
            stopSpeak();
            return;
        }

        stopSpeak();

        ttsUtterance = new SpeechSynthesisUtterance(text);
        ttsUtterance.lang = 'zh-CN';
        ttsUtterance.rate = 1;
        ttsUtterance.pitch = 1;
        
        ttsUtterance.onstart = function() {
            element.classList.add('a11y-tts-speaking');
            ttsSpeaking = element;
        };
        
        ttsUtterance.onend = function() {
            element.classList.remove('a11y-tts-speaking');
            ttsSpeaking = null;
            ttsUtterance = null;
        };
        
        ttsUtterance.onerror = function() {
            element.classList.remove('a11y-tts-speaking');
            ttsSpeaking = null;
            ttsUtterance = null;
        };

        speechSynthesis.speak(ttsUtterance);
    }

    function stopSpeak() {
        if ('speechSynthesis' in window) {
            speechSynthesis.cancel();
        }
        if (ttsSpeaking) {
            ttsSpeaking.classList.remove('a11y-tts-speaking');
        }
        ttsSpeaking = null;
        ttsUtterance = null;
    }

    function getTextFromElement(element) {
        let text = element.innerText || element.textContent || '';
        text = text.replace(/\s+/g, ' ').trim();
        return text;
    }

    function handleTTSClick(e) {
        if (currentPrefs.tts_mode !== 1) return;
        
        const target = e.target.closest('p, li, h1, h2, h3, h4, h5, h6');
        if (!target) return;
        
        if (target.closest('.a11y-panel, .a11y-float-btn, .modal, .swal2-container')) return;
        
        const text = getTextFromElement(target);
        if (text.length > 0) {
            speak(text, target);
        }
    }

    function createPanel() {
        const floatBtn = document.createElement('button');
        floatBtn.className = 'a11y-float-btn';
        floatBtn.innerHTML = '<i class="bi bi-universal-access-circle"></i>';
        floatBtn.setAttribute('aria-label', '无障碍辅助工具');
        floatBtn.title = '无障碍辅助工具';
        floatBtn.onclick = togglePanel;
        document.body.appendChild(floatBtn);

        const panel = document.createElement('div');
        panel.className = 'a11y-panel';
        panel.id = 'a11y-panel';
        panel.setAttribute('role', 'dialog');
        panel.setAttribute('aria-label', '无障碍设置面板');
        panel.innerHTML = `
            <div class="a11y-panel-header">
                <h5><i class="bi bi-universal-access-circle"></i>无障碍设置</h5>
                <button class="a11y-panel-close" onclick="togglePanel()" aria-label="关闭">&times;</button>
            </div>
            <div class="a11y-panel-body">
                <div class="a11y-section">
                    <div class="a11y-section-title">
                        <i class="bi bi-fonts"></i>字体大小
                    </div>
                    <div class="a11y-font-controls">
                        <button class="a11y-font-btn" id="a11y-font-dec" aria-label="缩小字体">A-</button>
                        <span class="a11y-font-value" id="a11y-font-value">100%</span>
                        <button class="a11y-font-btn" id="a11y-font-inc" aria-label="放大字体">A+</button>
                    </div>
                </div>

                <div class="a11y-section">
                    <div class="a11y-section-title">
                        <i class="bi bi-palette"></i>显示模式
                    </div>
                    <div class="a11y-toggle-list">
                        <div class="a11y-toggle-item">
                            <label class="a11y-toggle-label">
                                <i class="bi bi-contrast"></i>
                                <span>高对比度模式</span>
                            </label>
                            <label class="a11y-switch">
                                <input type="checkbox" id="a11y-high-contrast">
                                <span class="a11y-slider"></span>
                            </label>
                        </div>
                        <div class="a11y-toggle-item">
                            <label class="a11y-toggle-label">
                                <i class="bi bi-eye"></i>
                                <span>护眼模式</span>
                            </label>
                            <label class="a11y-switch">
                                <input type="checkbox" id="a11y-eye-care">
                                <span class="a11y-slider"></span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="a11y-section">
                    <div class="a11y-section-title">
                        <i class="bi bi-gear"></i>交互辅助
                    </div>
                    <div class="a11y-toggle-list">
                        <div class="a11y-toggle-item">
                            <label class="a11y-toggle-label">
                                <i class="bi bi-volume-up"></i>
                                <span>TTS 朗读模式</span>
                            </label>
                            <label class="a11y-switch">
                                <input type="checkbox" id="a11y-tts-mode">
                                <span class="a11y-slider"></span>
                            </label>
                        </div>
                        <div class="a11y-toggle-item">
                            <label class="a11y-toggle-label">
                                <i class="bi bi-arrow-through-heart"></i>
                                <span>键盘焦点高亮</span>
                            </label>
                            <label class="a11y-switch">
                                <input type="checkbox" id="a11y-focus-highlight">
                                <span class="a11y-slider"></span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="a11y-panel-footer">
                <button class="a11y-btn a11y-btn-secondary" id="a11y-reset-btn">重置默认</button>
                <button class="a11y-btn a11y-btn-primary" id="a11y-save-btn">保存设置</button>
            </div>
        `;
        document.body.appendChild(panel);

        bindPanelEvents();
    }

    function bindPanelEvents() {
        document.getElementById('a11y-font-dec').onclick = function() {
            if (currentPrefs.font_size > 80) {
                currentPrefs.font_size -= 10;
                updateFontUI();
                applyFontSize(currentPrefs.font_size);
            }
        };

        document.getElementById('a11y-font-inc').onclick = function() {
            if (currentPrefs.font_size < 150) {
                currentPrefs.font_size += 10;
                updateFontUI();
                applyFontSize(currentPrefs.font_size);
            }
        };

        document.getElementById('a11y-high-contrast').onchange = function() {
            currentPrefs.high_contrast = this.checked ? 1 : 0;
            if (currentPrefs.high_contrast === 1) {
                currentPrefs.eye_care = 0;
                document.getElementById('a11y-eye-care').checked = false;
            }
            applyHighContrast(currentPrefs.high_contrast);
            applyEyeCare(currentPrefs.eye_care);
        };

        document.getElementById('a11y-eye-care').onchange = function() {
            currentPrefs.eye_care = this.checked ? 1 : 0;
            if (currentPrefs.eye_care === 1) {
                currentPrefs.high_contrast = 0;
                document.getElementById('a11y-high-contrast').checked = false;
            }
            applyEyeCare(currentPrefs.eye_care);
            applyHighContrast(currentPrefs.high_contrast);
        };

        document.getElementById('a11y-tts-mode').onchange = function() {
            currentPrefs.tts_mode = this.checked ? 1 : 0;
            applyTTSMode(currentPrefs.tts_mode);
            if (currentPrefs.tts_mode !== 1) {
                stopSpeak();
            }
        };

        document.getElementById('a11y-focus-highlight').onchange = function() {
            currentPrefs.focus_highlight = this.checked ? 1 : 0;
            applyFocusHighlight(currentPrefs.focus_highlight);
        };

        document.getElementById('a11y-save-btn').onclick = function() {
            saveToLocalStorage(currentPrefs);
            saveToServer(currentPrefs).then(function(res) {
                if (res.code === 200) {
                    showToast('设置已保存');
                } else {
                    showToast('保存失败：' + res.message, 'error');
                }
            }).catch(function() {
                showToast('设置已保存到本地');
            });
        };

        document.getElementById('a11y-reset-btn').onclick = function() {
            if (confirm('确定要重置所有无障碍设置为默认值吗？')) {
                resetPrefs();
            }
        };

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                const panel = document.getElementById('a11y-panel');
                if (panel && panel.classList.contains('show')) {
                    hidePanel();
                }
            }
        });

        document.addEventListener('click', function(e) {
            const panel = document.getElementById('a11y-panel');
            const floatBtn = document.querySelector('.a11y-float-btn');
            if (panel && panel.classList.contains('show')) {
                if (!panel.contains(e.target) && !floatBtn.contains(e.target)) {
                    hidePanel();
                }
            }
        });
    }

    function updatePanelUI(prefs) {
        document.getElementById('a11y-font-value').textContent = prefs.font_size + '%';
        document.getElementById('a11y-font-dec').disabled = prefs.font_size <= 80;
        document.getElementById('a11y-font-inc').disabled = prefs.font_size >= 150;
        document.getElementById('a11y-high-contrast').checked = prefs.high_contrast === 1;
        document.getElementById('a11y-eye-care').checked = prefs.eye_care === 1;
        document.getElementById('a11y-tts-mode').checked = prefs.tts_mode === 1;
        document.getElementById('a11y-focus-highlight').checked = prefs.focus_highlight === 1;
    }

    function updateFontUI() {
        document.getElementById('a11y-font-value').textContent = currentPrefs.font_size + '%';
        document.getElementById('a11y-font-dec').disabled = currentPrefs.font_size <= 80;
        document.getElementById('a11y-font-inc').disabled = currentPrefs.font_size >= 150;
    }

    function togglePanel() {
        const panel = document.getElementById('a11y-panel');
        if (panel.classList.contains('show')) {
            hidePanel();
        } else {
            showPanel();
        }
    }

    function showPanel() {
        document.getElementById('a11y-panel').classList.add('show');
    }

    function hidePanel() {
        document.getElementById('a11y-panel').classList.remove('show');
    }

    function resetPrefs() {
        currentPrefs = { ...defaultPrefs };
        applyAllPrefs(currentPrefs);
        saveToLocalStorage(currentPrefs);
        resetOnServer().then(function(res) {
            if (res.code === 200) {
                showToast('已重置为默认设置');
            }
        }).catch(function() {
            showToast('已重置为默认设置');
        });
        stopSpeak();
    }

    function showToast(message, type = 'success') {
        if (typeof Swal !== 'undefined') {
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 2000,
                timerProgressBar: true
            });
            Toast.fire({
                icon: type,
                title: message
            });
        } else {
            alert(message);
        }
    }

    async function init() {
        createPanel();

        const localPrefs = loadFromLocalStorage();
        
        try {
            const res = await loadFromServer();
            if (res.code === 200 && res.data) {
                currentPrefs = { ...defaultPrefs, ...res.data };
            } else if (localPrefs) {
                currentPrefs = { ...defaultPrefs, ...localPrefs };
            }
        } catch (e) {
            if (localPrefs) {
                currentPrefs = { ...defaultPrefs, ...localPrefs };
            }
        }

        saveToLocalStorage(currentPrefs);
        applyAllPrefs(currentPrefs);

        document.addEventListener('click', handleTTSClick);

        window.addEventListener('beforeunload', function() {
            stopSpeak();
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
