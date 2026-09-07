import './bootstrap';

(() => {
    const registerForm = document.querySelector('[data-register-form]');

    if (!registerForm) {
        return;
    }

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
})();
