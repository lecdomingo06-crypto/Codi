(() => {
    const root = document.documentElement;
    const body = document.body;

    const storedTheme = window.localStorage.getItem('coddy-theme');
    if (storedTheme === 'light' || storedTheme === 'dark') {
        body.dataset.theme = storedTheme;
    }

    document.querySelector('[data-theme-toggle]')?.addEventListener('click', () => {
        const nextTheme = body.dataset.theme === 'light' ? 'dark' : 'light';
        body.dataset.theme = nextTheme;
        window.localStorage.setItem('coddy-theme', nextTheme);
        document.querySelector('[data-theme-toggle]')?.setAttribute('aria-label', `Switch to ${nextTheme === 'light' ? 'dark' : 'light'} theme`);
    });

    const navToggle = document.querySelector('[data-nav-toggle]');
    const primaryNav = document.querySelector('#primary-nav');
    navToggle?.addEventListener('click', () => {
        const isOpen = primaryNav?.classList.toggle('is-open');
        navToggle.setAttribute('aria-expanded', String(Boolean(isOpen)));
        navToggle.setAttribute('aria-label', isOpen ? 'Close navigation' : 'Open navigation');
    });

    document.querySelectorAll('[data-password-toggle]').forEach((toggle) => {
        toggle.addEventListener('click', () => {
            const input = toggle.parentElement?.querySelector('input');
            if (!input) return;
            const isHidden = input.type === 'password';
            input.type = isHidden ? 'text' : 'password';
            toggle.textContent = isHidden ? 'Hide' : 'Show';
            toggle.setAttribute('aria-label', `${isHidden ? 'Hide' : 'Show'} password`);
        });
    });

    const registerForm = document.querySelector('[data-register-form]');
    if (registerForm) {
        const passwordInput = registerForm.querySelector('[data-password-input]');
        const confirmationInput = registerForm.querySelector('[data-password-confirmation]');
        const strengthBar = registerForm.querySelector('[data-password-strength-bar]');
        const strengthLabel = registerForm.querySelector('[data-password-strength-label]');
        const passwordMessage = registerForm.querySelector('[data-password-message]');
        const confirmationMessage = registerForm.querySelector('[data-password-confirmation-message]');
        const submitButton = registerForm.querySelector('[data-register-submit], button[type="submit"]');

        const scorePassword = (password) => {
            if (!password) {
                return { level: '', label: '', width: '0%', message: '' };
            }

            const variety = [
                /[a-z]/.test(password),
                /[A-Z]/.test(password),
                /\d/.test(password),
                /[^A-Za-z0-9]/.test(password),
            ].filter(Boolean).length;
            const lengthScore = password.length >= 12 ? 2 : password.length >= 8 ? 1 : 0;
            const score = lengthScore + variety;

            if (password.length >= 12 && variety >= 4) {
                return { level: 'strong', label: 'Strong', width: '100%', message: '' };
            }

            if (password.length >= 8 && score >= 4) {
                return { level: 'medium', label: 'Medium', width: '66%', message: '' };
            }

            return {
                level: 'weak',
                label: 'Weak',
                width: '33%',
                message: 'Use at least 8 characters with a mix of uppercase, lowercase, numbers, and symbols.',
            };
        };

        const updateRegisterValidation = () => {
            const strength = scorePassword(passwordInput.value);
            const passwordsMatch = !confirmationInput.value || passwordInput.value === confirmationInput.value;

            strengthBar.classList.remove('is-weak', 'is-medium', 'is-strong');
            strengthBar.style.width = strength.width;
            strengthLabel.textContent = strength.label;

            if (strength.level) {
                strengthBar.classList.add(`is-${strength.level}`);
            }

            passwordMessage.textContent = strength.level === 'weak' ? strength.message : '';
            confirmationMessage.textContent = passwordsMatch ? '' : 'Password and confirmation password must match.';
            submitButton.disabled = strength.level === 'weak';
        };

        registerForm.addEventListener('submit', (event) => {
            updateRegisterValidation();

            const strength = scorePassword(passwordInput.value);

            if (!passwordInput.value || !confirmationInput.value) {
                event.preventDefault();
                passwordMessage.textContent = !passwordInput.value ? 'Password is required.' : passwordMessage.textContent;
                confirmationMessage.textContent = !confirmationInput.value ? 'Please confirm your password.' : confirmationMessage.textContent;
                return;
            }

            if (strength.level === 'weak') {
                event.preventDefault();
                passwordMessage.textContent = strength.message;
                return;
            }

            if (passwordInput.value !== confirmationInput.value) {
                event.preventDefault();
                confirmationMessage.textContent = 'Password and confirmation password must match.';
            }
        });

        passwordInput.addEventListener('input', updateRegisterValidation);
        confirmationInput.addEventListener('input', updateRegisterValidation);
        updateRegisterValidation();
    }

    const activateTabs = (selector, panelSelector) => {
        document.querySelectorAll(selector).forEach((tab) => {
            tab.addEventListener('click', () => {
                const target = document.getElementById(tab.dataset[panelSelector]);
                if (!target) return;
                document.querySelectorAll(selector).forEach((item) => {
                    item.classList.toggle('active', item === tab);
                    item.setAttribute('aria-selected', String(item === tab));
                });
                const panelClass = panelSelector === 'workspaceTab' ? '.workspace-tab-panel' : '.result-list';
                document.querySelectorAll(panelClass).forEach((panel) => {
                    panel.hidden = panel !== target;
                });
            });
        });
    };

    activateTabs('[data-workspace-tab]', 'workspaceTab');
    activateTabs('[data-result-tab]', 'resultTab');

    const browser = document.querySelector('[data-problem-browser]');
    if (browser) {
        const search = browser.querySelector('[data-problem-search]');
        const difficulty = browser.querySelector('select[name="difficulty"]');
        const rows = [...browser.querySelectorAll('[data-problem-row]')];
        const count = browser.querySelector('[data-problem-count]');
        const empty = browser.querySelector('[data-no-problem-results]');
        const filterRows = () => {
            const query = search?.value.trim().toLowerCase() || '';
            const selectedDifficulty = difficulty?.value || '';
            let visible = 0;
            rows.forEach((row) => {
                const matchesSearch = !query || row.dataset.title.includes(query);
                const matchesDifficulty = !selectedDifficulty || row.dataset.difficulty === selectedDifficulty;
                const show = matchesSearch && matchesDifficulty;
                row.hidden = !show;
                if (show) visible += 1;
            });
            if (count) count.textContent = `${visible} problem${visible === 1 ? '' : 's'}`;
            if (empty) empty.hidden = visible !== 0;
        };
        search?.addEventListener('input', filterRows);
        difficulty?.addEventListener('change', filterRows);
        browser.querySelector('[data-clear-problem-search]')?.addEventListener('click', () => {
            if (search) search.value = '';
            if (difficulty) difficulty.value = '';
            filterRows();
            search?.focus();
        });
        filterRows();
    }

    const form = document.querySelector('[data-auto-check]');
    if (!form) return;

    const editor = form.querySelector('[data-code-editor]');
    const languageSelect = document.querySelector('[data-editor-language]');
    const status = document.querySelector('[data-auto-check-status]');
    const results = document.querySelector('[data-auto-check-results]');
    const submitForm = document.querySelector('[data-submit-from-editor]');
    const submitLanguage = document.querySelector('[data-submit-language]');
    const submitCode = document.querySelector('[data-submit-code]');
    const idempotencyKey = submitForm?.querySelector('input[name="idempotency_key"]');
    const runButton = document.querySelector('[data-run-button]');
    const submitButton = submitForm?.querySelector('button[type="submit"]');
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
    let timer = null;
    let checkController = null;
    let actionController = null;

    const syncSubmitFields = () => {
        if (submitLanguage && languageSelect) submitLanguage.value = languageSelect.value;
        if (submitCode && editor) submitCode.value = editor.value;
    };

    const showResultPanel = () => document.querySelector('[data-result-tab="result-output"]')?.click();

    const renderTests = (payload) => {
        const verdict = payload.verdict || payload.status || 'UNKNOWN';
        const returnedTests = payload.tests || payload.test_results || [];
        const verdictClass = verdict === 'ACCEPTED' ? 'accepted' : 'failed';
        const items = returnedTests.map((test) => `
            <div class="result-row ${test.verdict === 'ACCEPTED' ? 'accepted' : 'failed'}">
                <strong>${escapeHtml(test.test_name)}</strong>
                <span>${escapeHtml(test.verdict)}</span>
                <small>${escapeHtml(test.message || '')}</small>
            </div>
        `).join('');
        results.innerHTML = `<div class="verdict ${verdictClass}">${escapeHtml(verdict.replaceAll('_', ' '))}</div>${items || '<p>No visible test details returned.</p>'}`;
        showResultPanel();
    };

    const runCheck = async () => {
        syncSubmitFields();
        if (!editor.value.trim()) {
            status.textContent = 'Auto-check waits for code.';
            return;
        }
        if (checkController) checkController.abort();
        checkController = new AbortController();
        status.textContent = 'Checking visible tests…';
        const data = new FormData();
        data.append('language', languageSelect.value);
        data.append('source_code', editor.value);
        try {
            const response = await fetch(form.dataset.checkUrl, { method: 'POST', headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrf }, body: data, signal: checkController.signal });
            if (!response.ok) {
                status.textContent = 'Auto-check needs a valid answer.';
                return;
            }
            const payload = await response.json();
            status.textContent = `Auto-check: ${payload.verdict}`;
            renderTests(payload);
        } catch (error) {
            if (error.name !== 'AbortError') status.textContent = 'Auto-check could not run.';
        }
    };

    const runVisibleTests = async (event) => {
        event.preventDefault();
        clearTimeout(timer);
        syncSubmitFields();
        if (!editor.value.trim()) {
            status.textContent = 'Run needs code.';
            results.innerHTML = '<div class="result-placeholder"><span>▷</span><p>Write a non-empty answer before running tests.</p></div>';
            showResultPanel();
            return;
        }
        if (checkController) checkController.abort();
        if (actionController) actionController.abort();
        actionController = new AbortController();
        status.textContent = 'Running visible tests…';
        runButton.disabled = true;
        runButton.textContent = 'Running…';
        const data = new FormData(form);
        data.set('language', languageSelect.value);
        data.set('source_code', editor.value);
        try {
            const response = await fetch(form.action, { method: 'POST', headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrf }, body: data, signal: actionController.signal });
            if (!response.ok) {
                status.textContent = 'Run failed. Check your code and try again.';
                return;
            }
            const payload = await response.json();
            status.textContent = `Run: ${payload.verdict}`;
            renderTests(payload);
        } catch (error) {
            if (error.name !== 'AbortError') status.textContent = 'Run could not complete.';
        } finally {
            runButton.disabled = false;
            runButton.textContent = 'Run code';
        }
    };

    const submitSolution = async (event) => {
        event.preventDefault();
        clearTimeout(timer);
        syncSubmitFields();
        if (!editor.value.trim()) {
            status.textContent = 'Submit needs code.';
            results.innerHTML = '<div class="result-placeholder"><span>▷</span><p>Write a non-empty answer before submitting.</p></div>';
            showResultPanel();
            return;
        }
        if (checkController) checkController.abort();
        if (actionController) actionController.abort();
        if (idempotencyKey) idempotencyKey.value = window.crypto?.randomUUID?.() || `${Date.now()}-${Math.random()}`;
        actionController = new AbortController();
        status.textContent = 'Submitting solution…';
        submitButton.disabled = true;
        submitButton.textContent = 'Submitting…';
        const data = new FormData(submitForm);
        data.set('language', languageSelect.value);
        data.set('source_code', editor.value);
        try {
            const response = await fetch(submitForm.action, { method: 'POST', headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrf }, body: data, signal: actionController.signal });
            if (!response.ok) {
                status.textContent = 'Submit failed. Check your code and try again.';
                return;
            }
            const payload = await response.json();
            status.textContent = `Submit: ${payload.verdict}`;
            renderTests(payload);
        } catch (error) {
            if (error.name !== 'AbortError') status.textContent = 'Submit could not complete.';
        } finally {
            submitButton.disabled = false;
            submitButton.textContent = 'Submit solution';
        }
    };

    const scheduleCheck = () => {
        syncSubmitFields();
        status.textContent = 'Editing…';
        clearTimeout(timer);
        timer = setTimeout(runCheck, 850);
    };

    editor.addEventListener('input', scheduleCheck);
    languageSelect.addEventListener('change', scheduleCheck);
    runButton.addEventListener('click', runVisibleTests);
    form.addEventListener('submit', runVisibleTests);
    submitForm?.addEventListener('submit', submitSolution);
    syncSubmitFields();
    timer = setTimeout(runCheck, 500);

    function escapeHtml(value) {
        return String(value).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
    }
})();
