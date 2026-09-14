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

    const countdowns = [...document.querySelectorAll('[data-calendar-countdown][data-reset-at]')];
    if (countdowns.length) {
        const updateCountdowns = () => {
            countdowns.forEach((countdown) => {
                const resetAt = new Date(countdown.dataset.resetAt);
                const secondsLeft = Math.max(0, Math.floor((resetAt.getTime() - Date.now()) / 1000));
                const hours = String(Math.floor(secondsLeft / 3600)).padStart(2, '0');
                const minutes = String(Math.floor((secondsLeft % 3600) / 60)).padStart(2, '0');
                const seconds = String(secondsLeft % 60).padStart(2, '0');
                countdown.textContent = `${hours}:${minutes}:${seconds} left`;
            });
        };

        updateCountdowns();
        window.setInterval(updateCountdowns, 1000);
    }

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
        const topicButtons = [...browser.querySelectorAll('[data-topic-filter]')];
        const rows = [...browser.querySelectorAll('[data-problem-row]')];
        const count = browser.querySelector('[data-problem-count]');
        const empty = browser.querySelector('[data-no-problem-results]');
        const randomButton = browser.querySelector('[data-random-problem]');
        const randomTooltip = browser.querySelector('[data-random-tooltip]');
        const aboutModal = browser.querySelector('[data-practice-about-modal]');
        const aboutOpen = browser.querySelector('[data-practice-about-open]');
        let aboutTrigger = null;
        let randomRow = null;
        let activeTopic = topicButtons.find((button) => button.classList.contains('active'))?.dataset.topicFilter || '';
        const rowMatchesCurrentFilters = (row) => {
            const query = search?.value.trim().toLowerCase() || '';
            const selectedDifficulty = difficulty?.value || '';
            const matchesSearch = !query || row.dataset.title.includes(query);
            const matchesDifficulty = !selectedDifficulty || row.dataset.difficulty === selectedDifficulty;
            const matchesTopic = !activeTopic || row.dataset.topic.includes(activeTopic);

            return matchesSearch && matchesDifficulty && matchesTopic;
        };
        const setRandomState = () => {
            randomButton?.classList.toggle('is-active', Boolean(randomRow));
            randomButton?.setAttribute('aria-pressed', String(Boolean(randomRow)));
            if (randomTooltip) randomTooltip.textContent = randomRow ? 'Choose another problem' : 'Choose random problem';
        };
        const filterRows = () => {
            const matchingRows = rows.filter(rowMatchesCurrentFilters);

            if (randomRow && !matchingRows.includes(randomRow)) {
                randomRow = null;
            }

            let visible = 0;
            rows.forEach((row) => {
                const show = rowMatchesCurrentFilters(row) && (!randomRow || row === randomRow);
                row.hidden = !show;
                row.classList.toggle('is-random-selection', show && row === randomRow);
                if (show) visible += 1;
            });
            if (count) count.textContent = `${visible} problem${visible === 1 ? '' : 's'}`;
            if (empty) empty.hidden = visible !== 0;
            setRandomState();
        };
        search?.addEventListener('input', () => {
            randomRow = null;
            filterRows();
        });
        difficulty?.addEventListener('change', () => {
            randomRow = null;
            filterRows();
        });
        browser.querySelector('[data-clear-problem-search]')?.addEventListener('click', () => {
            if (search) search.value = '';
            if (difficulty) difficulty.value = '';
            randomRow = null;
            filterRows();
            search?.focus();
        });
        topicButtons.forEach((button) => {
            button.addEventListener('click', () => {
                activeTopic = button.dataset.topicFilter || '';
                randomRow = null;
                topicButtons.forEach((item) => item.classList.toggle('active', item.dataset.topicFilter === activeTopic));
                filterRows();
            });
        });
        randomButton?.addEventListener('click', () => {
            const matchingRows = rows.filter(rowMatchesCurrentFilters);
            const pool = matchingRows.length > 1 && randomRow
                ? matchingRows.filter((row) => row !== randomRow)
                : matchingRows;

            randomRow = pool[Math.floor(Math.random() * pool.length)] || null;
            filterRows();

            randomRow?.scrollIntoView({ behavior: 'smooth', block: 'center' });
        });
        const openAbout = () => {
            if (!aboutModal) return;
            aboutTrigger = document.activeElement;
            aboutModal.hidden = false;
            document.body.classList.add('has-modal-open');
            aboutModal.querySelector('[data-practice-about-close]')?.focus();
        };
        const closeAbout = () => {
            if (!aboutModal) return;
            aboutModal.hidden = true;
            document.body.classList.remove('has-modal-open');
            aboutTrigger?.focus?.();
            aboutTrigger = null;
        };

        aboutOpen?.addEventListener('click', openAbout);
        aboutModal?.querySelectorAll('[data-practice-about-close]').forEach((button) => {
            button.addEventListener('click', closeAbout);
        });
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && aboutModal && !aboutModal.hidden) {
                closeAbout();
            }
        });
        filterRows();
    }

    const communityModal = document.querySelector('[data-community-compose-modal]');
    const openCommunityModal = () => {
        if (!communityModal) return;
        communityModal.hidden = false;
        document.body.classList.add('has-modal-open');
        communityModal.querySelector('input[name="title"]')?.focus();
    };
    const closeCommunityModal = () => {
        if (!communityModal) return;
        communityModal.hidden = true;
        document.body.classList.remove('has-modal-open');
    };
    const resetCommunityImagePreview = () => {
        const imageInput = communityModal?.querySelector('[data-community-image-input]');
        const imageLabel = communityModal?.querySelector('[data-community-image-label]');
        const imagePreview = communityModal?.querySelector('[data-community-image-preview]');

        if (imageInput) imageInput.value = '';
        if (imageLabel) imageLabel.textContent = 'Add image';
        if (imagePreview) {
            imagePreview.removeAttribute('src');
            imagePreview.hidden = true;
        }
    };

    document.querySelectorAll('[data-community-compose-open]').forEach((button) => {
        button.addEventListener('click', openCommunityModal);
    });
    document.querySelectorAll('[data-community-compose-close]').forEach((button) => {
        button.addEventListener('click', () => {
            closeCommunityModal();
            resetCommunityImagePreview();
        });
    });
    communityModal?.querySelector('[data-community-image-input]')?.addEventListener('change', (event) => {
        const file = event.target.files?.[0];
        const imageLabel = communityModal.querySelector('[data-community-image-label]');
        const imagePreview = communityModal.querySelector('[data-community-image-preview]');

        if (!file) {
            resetCommunityImagePreview();
            return;
        }

        if (imageLabel) imageLabel.textContent = file.name;
        if (imagePreview) {
            imagePreview.src = URL.createObjectURL(file);
            imagePreview.hidden = false;
        }
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && communityModal && !communityModal.hidden) {
            closeCommunityModal();
        }
    });
    if (window.CODDY_OPEN_COMMUNITY_COMPOSER) {
        openCommunityModal();
    }

    const communityCsrf = document.querySelector('meta[name="csrf-token"]')?.content;
    const threadModals = [...document.querySelectorAll('[data-community-thread-modal]')];
    const shareModals = [...document.querySelectorAll('[data-community-share-modal]')];
    let communityThreadTrigger = null;
    let communityShareTrigger = null;

    const formatCompactNumber = (value) => {
        const absolute = Math.abs(Number(value) || 0);

        if (absolute >= 1000000) {
            return `${Number((absolute / 1000000).toFixed(1)).toString()}M`;
        }

        if (absolute >= 1000) {
            return `${Number((absolute / 1000).toFixed(1)).toString()}K`;
        }

        return String(absolute);
    };
    const formatScore = (score, style = 'plain') => {
        const numericScore = Number(score) || 0;

        if (style === 'compact') {
            return `${numericScore < 0 ? '-' : ''}${formatCompactNumber(numericScore)}`;
        }

        return String(numericScore);
    };
    const updateCommunityVote = (postId, score, userVote) => {
        document.querySelectorAll(`[data-community-vote-score="${postId}"]`).forEach((scoreElement) => {
            scoreElement.textContent = formatScore(score, scoreElement.dataset.scoreStyle);
        });

        document.querySelectorAll(`[data-community-vote-form][data-post-id="${postId}"] [data-community-vote-button]`).forEach((button) => {
            button.classList.toggle('active', Number(button.dataset.voteValue) === Number(userVote));
        });
    };

    const hasOpenCommunityThread = () => threadModals.some((modal) => !modal.hidden);
    const hasOpenCommunityShare = () => shareModals.some((modal) => !modal.hidden);
    const updateCommunityModalLock = () => {
        const hasOpenComposer = Boolean(communityModal && !communityModal.hidden);
        document.body.classList.toggle('has-modal-open', hasOpenComposer || hasOpenCommunityThread() || hasOpenCommunityShare());
    };
    const copyToClipboard = async (text) => {
        if (navigator.clipboard?.writeText) {
            try {
                await navigator.clipboard.writeText(text);
                return;
            } catch {
                // Fall back to the older copy command below.
            }
        }

        const textarea = document.createElement('textarea');
        textarea.value = text;
        textarea.setAttribute('readonly', '');
        textarea.style.position = 'fixed';
        textarea.style.opacity = '0';
        document.body.append(textarea);
        textarea.select();
        const copied = document.execCommand('copy');
        textarea.remove();

        if (!copied) {
            throw new Error('Copy failed');
        }
    };
    const closeOpenCommunityShare = () => {
        const openModal = shareModals.find((modal) => !modal.hidden);
        if (!openModal) return;

        openModal.hidden = true;
        const status = openModal.querySelector('[data-community-share-status]');
        if (status) status.textContent = '';
        updateCommunityModalLock();
        communityShareTrigger?.focus?.();
        communityShareTrigger = null;
    };
    const closeOpenCommunityThread = () => {
        const openModal = threadModals.find((modal) => !modal.hidden);
        if (!openModal) return;

        closeOpenCommunityShare();
        openModal.hidden = true;
        updateCommunityModalLock();
        communityThreadTrigger?.focus?.();
        communityThreadTrigger = null;
    };
    const openCommunityThread = (threadId, trigger = document.activeElement) => {
        const modal = threadModals.find((item) => item.dataset.threadId === String(threadId));
        if (!modal) return;

        closeCommunityModal();
        closeOpenCommunityShare();
        threadModals.forEach((item) => {
            item.hidden = item !== modal;
        });
        communityThreadTrigger = trigger;
        document.body.classList.add('has-modal-open');
        modal.querySelector('[data-community-thread-close]')?.focus();
    };
    const openCommunityShare = (shareId, trigger = document.activeElement) => {
        const modal = shareModals.find((item) => item.dataset.shareId === String(shareId));
        if (!modal) return;

        closeCommunityModal();
        shareModals.forEach((item) => {
            item.hidden = item !== modal;
        });
        communityShareTrigger = trigger;
        document.body.classList.add('has-modal-open');
        modal.querySelector('[data-community-share-message]')?.focus();
    };
    const runShareAction = async (button) => {
        const modal = button.closest('[data-community-share-modal]');
        const status = modal?.querySelector('[data-community-share-status]');
        const url = button.dataset.shareUrl || window.location.href;

        if (status) status.textContent = 'Copying link...';

        try {
            await copyToClipboard(url);
            if (status) status.textContent = 'Link copied.';
        } catch (error) {
            if (status) status.textContent = 'Could not copy. Try copying the browser link.';
        }
    };

    document.querySelectorAll('[data-community-thread-open]').forEach((button) => {
        button.addEventListener('click', () => {
            openCommunityThread(button.dataset.communityThreadOpen, button);
        });
    });
    document.querySelectorAll('[data-community-share-open]').forEach((button) => {
        button.addEventListener('click', () => {
            openCommunityShare(button.dataset.communityShareOpen, button);
        });
    });
    threadModals.forEach((modal) => {
        modal.querySelectorAll('[data-community-thread-close]').forEach((button) => {
            button.addEventListener('click', closeOpenCommunityThread);
        });
        modal.querySelectorAll('[data-community-thread-comment-focus]').forEach((button) => {
            button.addEventListener('click', () => {
                closeOpenCommunityShare();
                modal.querySelector('[data-community-thread-input]')?.focus();
            });
        });
    });
    shareModals.forEach((modal) => {
        modal.querySelectorAll('[data-community-share-close]').forEach((button) => {
            button.addEventListener('click', closeOpenCommunityShare);
        });
        modal.querySelectorAll('[data-community-share-copy]').forEach((button) => {
            button.addEventListener('click', () => runShareAction(button));
        });
    });
    document.querySelectorAll('[data-community-vote-form]').forEach((voteForm) => {
        voteForm.addEventListener('submit', async (event) => {
            event.preventDefault();

            const postId = voteForm.dataset.postId;
            const buttons = [...document.querySelectorAll(`[data-community-vote-form][data-post-id="${postId}"] button`)];
            buttons.forEach((button) => {
                button.disabled = true;
            });

            try {
                const response = await fetch(voteForm.action, {
                    method: voteForm.method || 'POST',
                    headers: {
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': communityCsrf || '',
                    },
                    body: new FormData(voteForm),
                });

                if (!response.ok) {
                    throw new Error('Vote failed');
                }

                const payload = await response.json();
                updateCommunityVote(payload.post_id, payload.score, payload.user_vote);
            } catch {
                HTMLFormElement.prototype.submit.call(voteForm);
                return;
            } finally {
                buttons.forEach((button) => {
                    button.disabled = false;
                });
            }
        });
    });
    document.addEventListener('keydown', (event) => {
        if (event.key !== 'Escape') return;

        if (hasOpenCommunityShare()) {
            closeOpenCommunityShare();
            return;
        }

        closeOpenCommunityThread();
    });
    if (window.CODDY_OPEN_COMMUNITY_THREAD) {
        openCommunityThread(window.CODDY_OPEN_COMMUNITY_THREAD);
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
    const completionToast = document.querySelector('[data-completion-toast]');
    const completionTitle = document.querySelector('[data-completion-title]');
    const completionClose = document.querySelector('[data-completion-close]');
    const dailyStreakModal = document.querySelector('[data-daily-streak-modal]');
    const dailyStreakCurrent = document.querySelector('[data-daily-streak-current]');
    const dailyStreakDayLabel = document.querySelector('[data-daily-streak-day-label]');
    const dailyStreakBest = document.querySelector('[data-daily-streak-best]');
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
    let timer = null;
    let checkController = null;
    let actionController = null;
    let completionTimer = null;
    let activeLanguage = languageSelect?.value || '';
    let starterCodes = {};

    try {
        starterCodes = JSON.parse(editor.dataset.starterCodes || '{}');
    } catch {
        starterCodes = {};
    }

    const syncSubmitFields = () => {
        if (submitLanguage && languageSelect) submitLanguage.value = languageSelect.value;
        if (submitCode && editor) submitCode.value = editor.value;
    };

    const showResultPanel = () => document.querySelector('[data-result-tab="result-output"]')?.click();

    const hideCompletionToast = () => {
        if (!completionToast) return;
        completionToast.hidden = true;
        clearTimeout(completionTimer);
    };

    const showCompletionToast = () => {
        if (!completionToast) return;

        if (completionTitle && form.dataset.exerciseTitle) {
            completionTitle.textContent = form.dataset.exerciseTitle;
        }

        completionToast.hidden = false;
        clearTimeout(completionTimer);
        completionTimer = setTimeout(hideCompletionToast, 6500);
    };

    const hideDailyStreakModal = () => {
        if (!dailyStreakModal) return;
        dailyStreakModal.hidden = true;
        document.body.classList.remove('has-modal-open');
    };

    const showDailyStreakModal = (completion = {}) => {
        if (!dailyStreakModal) return;

        const current = Number(completion.current_streak || 1);
        const best = Number(completion.longest_streak || current);

        if (dailyStreakCurrent) dailyStreakCurrent.textContent = String(current);
        if (dailyStreakDayLabel) dailyStreakDayLabel.textContent = current === 1 ? 'day' : 'days';
        if (dailyStreakBest) dailyStreakBest.textContent = `${best} ${best === 1 ? 'day' : 'days'}`;

        dailyStreakModal.hidden = false;
        document.body.classList.add('has-modal-open');
        dailyStreakModal.querySelector('[data-daily-streak-close]')?.focus();
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
            if (payload.verdict === 'ACCEPTED') {
                if (payload.completion?.type === 'daily_streak') {
                    hideCompletionToast();
                    showDailyStreakModal(payload.completion);
                } else {
                    showCompletionToast();
                }
            }
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
    languageSelect.addEventListener('change', () => {
        const previousStarter = starterCodes[activeLanguage] || '';
        const canReplaceEditor = !editor.value.trim() || editor.value === previousStarter;
        activeLanguage = languageSelect.value;

        if (canReplaceEditor && starterCodes[activeLanguage]) {
            editor.value = starterCodes[activeLanguage];
        }

        scheduleCheck();
    });
    runButton.addEventListener('click', runVisibleTests);
    form.addEventListener('submit', runVisibleTests);
    submitForm?.addEventListener('submit', submitSolution);
    completionClose?.addEventListener('click', hideCompletionToast);
    dailyStreakModal?.querySelectorAll('[data-daily-streak-close]').forEach((button) => {
        button.addEventListener('click', hideDailyStreakModal);
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && dailyStreakModal && !dailyStreakModal.hidden) {
            hideDailyStreakModal();
        }
    });
    syncSubmitFields();
    timer = setTimeout(runCheck, 500);

    function escapeHtml(value) {
        return String(value).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
    }
})();
