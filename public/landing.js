(() => {
    const initializeLab = () => {
        const lab = document.querySelector('[data-landing-lab]');
        if (!lab || lab.classList.contains('is-enhanced')) return;

        const languageButtons = [...lab.querySelectorAll('[data-lab-language]')];
        const codePanels = [...lab.querySelectorAll('[data-lab-code]')];
        const presets = [...lab.querySelectorAll('[data-lab-preset]')];
        const inputA = [...lab.querySelectorAll('[data-lab-a]')];
        const inputB = [...lab.querySelectorAll('[data-lab-b]')];
        const output = lab.querySelector('[data-lab-output]');
        const status = lab.querySelector('[data-lab-status]');
        const runButton = lab.querySelector('[data-lab-run]');
        const initialPreset = presets.find((preset) => preset.getAttribute('aria-pressed') === 'true') || presets[0];

        if (!initialPreset || !output || !status || !runButton) return;

        const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
        let selectedA;
        let selectedB;

        const clearAnimation = () => {
            delete lab.dataset.labAnimate;
        };

        const resetOutput = () => {
            clearAnimation();
            lab.dataset.labState = 'ready';
            output.textContent = '\u2014';
            status.textContent = 'Choose an example, then run the function.';
        };

        const selectPreset = (preset) => {
            const a = Number(preset.dataset.a);
            const b = Number(preset.dataset.b);
            if (!Number.isFinite(a) || !Number.isFinite(b)) return;

            selectedA = a;
            selectedB = b;
            presets.forEach((button) => button.setAttribute('aria-pressed', String(button === preset)));
            inputA.forEach((readout) => { readout.textContent = String(a); });
            inputB.forEach((readout) => { readout.textContent = String(b); });
            resetOutput();
        };

        const selectLanguage = (button) => {
            const language = button.dataset.labLanguage;
            if (!['python', 'javascript'].includes(language)) return;

            languageButtons.forEach((item) => item.setAttribute('aria-pressed', String(item === button)));
            codePanels.forEach((panel) => { panel.hidden = panel.dataset.labCode !== language; });
            resetOutput();
        };

        presets.forEach((preset) => {
            preset.addEventListener('click', () => selectPreset(preset));
        });

        languageButtons.forEach((button) => {
            button.addEventListener('click', () => selectLanguage(button));
        });

        runButton.addEventListener('click', (event) => {
            clearAnimation();
            if (!Number.isFinite(selectedA) || !Number.isFinite(selectedB)) return;

            const result = selectedA + selectedB;
            output.textContent = String(result);
            status.textContent = `Function returned ${result}. Try another example.`;
            lab.dataset.labState = 'complete';

            if (event.detail > 0 && !reducedMotion.matches) {
                lab.dataset.labAnimate = 'true';
            }
        });

        reducedMotion.addEventListener('change', clearAnimation);

        const initialLanguage = languageButtons.find((button) => button.getAttribute('aria-pressed') === 'true') || languageButtons[0];
        if (initialLanguage) selectLanguage(initialLanguage);
        selectPreset(initialPreset);

        status.setAttribute('aria-live', 'polite');
        status.setAttribute('aria-atomic', 'true');
        const runLabel = runButton.querySelector('[data-lab-run-label]');
        if (runLabel) runLabel.textContent = 'Run function';

        lab.classList.add('is-enhanced');
        runButton.hidden = false;
    };

    const initializeDemoCopy = () => {
        document.querySelectorAll('[data-demo-email][data-demo-password]').forEach((button) => {
            button.addEventListener('click', async () => {
                const credentials = `Email: ${button.dataset.demoEmail}\nPassword: ${button.dataset.demoPassword}`;
                const status = button.parentElement?.querySelector('.demo-copy-status');

                try {
                    await navigator.clipboard.writeText(credentials);
                    button.dataset.copied = 'true';
                    button.querySelector('span').textContent = 'Copied';
                    if (status) status.textContent = 'Demo credentials copied.';
                } catch {
                    if (status) status.textContent = 'Copy unavailable. Select the email and password above.';
                }

                window.setTimeout(() => {
                    delete button.dataset.copied;
                    button.querySelector('span').textContent = 'Copy credentials';
                }, 1800);
            });
        });
    };

    const initializeScrollReveals = () => {
        const revealItems = [...document.querySelectorAll('[data-reveal]')];
        if (!revealItems.length || !('IntersectionObserver' in window)) return;

        document.body.classList.add('has-scroll-reveal');

        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;

                entry.target.setAttribute('data-reveal-visible', '');
                observer.unobserve(entry.target);
            });
        }, {
            rootMargin: '0px 0px -80px 0px',
            threshold: 0.12,
        });

        revealItems.forEach((item) => observer.observe(item));
    };

    const initializeCodeStory = () => {
        const story = document.querySelector('[data-code-story]');
        if (!story) return;

        const steps = [...story.querySelectorAll('[data-story-step]')];
        const panels = [...story.querySelectorAll('[data-story-panel]')];
        const label = story.querySelector('[data-story-label]');
        const staticMode = window.matchMedia('(prefers-reduced-motion: reduce), (max-width: 760px), (max-height: 700px)');
        const validStates = new Set(['values', 'decision', 'function']);
        const scrollKeys = new Set(['ArrowDown', 'ArrowUp', 'PageDown', 'PageUp', 'Home', 'End', ' ']);
        let observer;
        let instantTimer;
        let updateFrame;

        if (!steps.length || panels.length !== 3 || !label) return;

        const activate = (state) => {
            if (!validStates.has(state) || story.dataset.storyState === state) return;

            story.dataset.storyState = state;
            panels.forEach((panel) => panel.setAttribute('aria-hidden', String(panel.dataset.storyPanel !== state)));
            steps.forEach((step) => {
                if (step.dataset.storyStep === state) step.setAttribute('aria-current', 'step');
                else step.removeAttribute('aria-current');
            });

            const activeStep = steps.find((step) => step.dataset.storyStep === state);
            label.textContent = activeStep?.dataset.storyLabel || state;
        };

        const stopObserver = () => {
            if (!observer) return;
            observer.disconnect();
            observer = undefined;
        };

        const updateFromScroll = () => {
            updateFrame = undefined;
            if (staticMode.matches) return;

            const storyRect = story.getBoundingClientRect();
            if (storyRect.bottom <= 0) {
                activate('function');
                return;
            }
            if (storyRect.top >= window.innerHeight) {
                activate('values');
                return;
            }

            const viewportFocus = window.innerHeight * 0.5;
            const closest = steps
                .map((step) => ({ step, rect: step.getBoundingClientRect() }))
                .sort((a, b) => Math.abs((a.rect.top + a.rect.bottom) / 2 - viewportFocus) - Math.abs((b.rect.top + b.rect.bottom) / 2 - viewportFocus))[0];

            if (closest) activate(closest.step.dataset.storyStep);
        };

        const scheduleScrollUpdate = () => {
            if (updateFrame) return;
            updateFrame = window.requestAnimationFrame(updateFromScroll);
        };

        const startObserver = () => {
            stopObserver();

            if (staticMode.matches || !('IntersectionObserver' in window)) {
                activate('function');
                return;
            }

            activate('values');
            observer = new IntersectionObserver((entries) => {
                if (!entries.some((entry) => entry.isIntersecting)) return;
                scheduleScrollUpdate();
            }, {
                rootMargin: '-32% 0px -38% 0px',
                threshold: [0, 0.25, 0.5, 0.75, 1],
            });

            steps.forEach((step) => observer.observe(step));
            updateFromScroll();
        };

        document.addEventListener('keydown', (event) => {
            if (!scrollKeys.has(event.key)) return;

            story.dataset.storyInstant = 'true';
            window.clearTimeout(instantTimer);
            instantTimer = window.setTimeout(() => {
                delete story.dataset.storyInstant;
            }, 400);
        });

        window.addEventListener('scroll', scheduleScrollUpdate, { passive: true });
        staticMode.addEventListener('change', startObserver);
        startObserver();
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            initializeLab();
            initializeDemoCopy();
            initializeCodeStory();
            initializeScrollReveals();
        }, { once: true });
    } else {
        initializeLab();
        initializeDemoCopy();
        initializeCodeStory();
        initializeScrollReveals();
    }
})();
