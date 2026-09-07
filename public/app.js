(() => {
    const registerForm = document.querySelector('[data-register-form]');

    if (registerForm) {
        const passwordInput = registerForm.querySelector('[data-password-input]');
        const confirmationInput = registerForm.querySelector('[data-password-confirmation]');
        const strengthBar = registerForm.querySelector('[data-password-strength-bar]');
        const strengthLabel = registerForm.querySelector('[data-password-strength-label]');
        const passwordMessage = registerForm.querySelector('[data-password-message]');
        const confirmationMessage = registerForm.querySelector('[data-password-confirmation-message]');
        const submitButton = registerForm.querySelector('[data-register-submit]');

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
})();

(() => {
    const form = document.querySelector('[data-auto-check]');

    if (!form) {
        return;
    }

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
        if (submitLanguage && languageSelect) {
            submitLanguage.value = languageSelect.value;
        }

        if (submitCode && editor) {
            submitCode.value = editor.value;
        }
    };

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

        results.innerHTML = `
            <div class="verdict ${verdictClass}">${escapeHtml(verdict)}</div>
            ${items || '<p>No visible test details returned.</p>'}
        `;
    };

    const runCheck = async () => {
        syncSubmitFields();

        if (!editor.value.trim()) {
            status.textContent = 'Auto-check waits for code.';
            results.innerHTML = '<p>Write a non-empty answer to run visible tests.</p>';
            return;
        }

        if (checkController) {
            checkController.abort();
        }

        checkController = new AbortController();
        status.textContent = 'Checking visible tests...';

        const data = new FormData();
        data.append('language', languageSelect.value);
        data.append('source_code', editor.value);

        try {
            const response = await fetch(form.dataset.checkUrl, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                },
                body: data,
                signal: checkController.signal,
            });

            if (!response.ok) {
                status.textContent = 'Auto-check needs a valid answer.';
                return;
            }

            const payload = await response.json();
            status.textContent = `Auto-check: ${payload.verdict}`;
            renderTests(payload);
        } catch (error) {
            if (error.name !== 'AbortError') {
                status.textContent = 'Auto-check could not run.';
            }
        }
    };

    const runVisibleTests = async (event) => {
        event.preventDefault();
        clearTimeout(timer);
        syncSubmitFields();

        if (!editor.value.trim()) {
            status.textContent = 'Run needs code.';
            results.innerHTML = '<p>Write a non-empty answer before running tests.</p>';
            return;
        }

        if (checkController) {
            checkController.abort();
        }

        if (actionController) {
            actionController.abort();
        }

        actionController = new AbortController();
        status.textContent = 'Running visible tests...';
        runButton.disabled = true;
        runButton.textContent = 'Running';

        const data = new FormData(form);
        data.set('language', languageSelect.value);
        data.set('source_code', editor.value);

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                },
                body: data,
                signal: actionController.signal,
            });

            if (!response.ok) {
                status.textContent = 'Run failed. Check the page errors or try again.';
                return;
            }

            const payload = await response.json();
            status.textContent = `Run: ${payload.verdict}`;
            renderTests(payload);
        } catch (error) {
            if (error.name !== 'AbortError') {
                status.textContent = 'Run could not complete.';
            }
        } finally {
            runButton.disabled = false;
            runButton.textContent = 'Run';
        }
    };

    const submitSolution = async (event) => {
        event.preventDefault();
        clearTimeout(timer);
        syncSubmitFields();

        if (!editor.value.trim()) {
            status.textContent = 'Submit needs code.';
            results.innerHTML = '<p>Write a non-empty answer before submitting.</p>';
            return;
        }

        if (checkController) {
            checkController.abort();
        }

        if (actionController) {
            actionController.abort();
        }

        if (idempotencyKey) {
            idempotencyKey.value = window.crypto?.randomUUID?.() || `${Date.now()}-${Math.random()}`;
        }

        actionController = new AbortController();
        status.textContent = 'Submitting...';
        submitButton.disabled = true;
        submitButton.textContent = 'Submitting';

        const data = new FormData(submitForm);
        data.set('language', languageSelect.value);
        data.set('source_code', editor.value);

        try {
            const response = await fetch(submitForm.action, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                },
                body: data,
                signal: actionController.signal,
            });

            if (!response.ok) {
                status.textContent = 'Submit failed. Check your code and try again.';
                return;
            }

            const payload = await response.json();
            status.textContent = `Submit: ${payload.verdict}`;
            renderTests(payload);
        } catch (error) {
            if (error.name !== 'AbortError') {
                status.textContent = 'Submit could not complete.';
            }
        } finally {
            submitButton.disabled = false;
            submitButton.textContent = 'Submit';
        }
    };

    const scheduleCheck = () => {
        syncSubmitFields();
        status.textContent = 'Editing...';
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
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }
})();
