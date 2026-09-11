/**
 * AI EXTREME 2026 - Form Validation
 */

const Validation = {
    validateTeamName(value) {
        value = value.trim();
        if (value.length < 3) return 'Team name must be at least 3 characters.';
        if (value.length > 100) return 'Team name must not exceed 100 characters.';
        if (/<[^>]+>/.test(value)) return 'Team name contains invalid characters.';
        return null;
    },

    validateEmail(value) {
        value = value.trim();
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!re.test(value)) return 'Please enter a valid email address.';
        return null;
    },

    validateMobile(value) {
        value = value.trim().replace(/^\+91/, '').replace(/[\s\-]/g, '');
        if (!/^[6-9]\d{9}$/.test(value)) return 'Please enter a valid 10-digit mobile number.';
        return null;
    },

    validatePRN(value) {
        value = value.trim();
        if (!value) return 'PRN is required.';
        if (value.length > 50) return 'PRN must not exceed 50 characters.';
        return null;
    },

    validateFullName(value) {
        value = value.trim();
        if (value.length < 2) return 'Full name must be at least 2 characters.';
        if (value.length > 100) return 'Full name must not exceed 100 characters.';
        return null;
    },

    validateGender(value) {
        const allowed = ['Male', 'Female', 'Other', 'Prefer not to say'];
        if (!allowed.includes(value)) return 'Please select a gender.';
        return null;
    },

    validateFile(file) {
        if (!file) return 'Payment screenshot is required.';
        const allowed = ['image/jpeg', 'image/jpg', 'image/png'];
        if (!allowed.includes(file.type)) return 'Only JPG, JPEG, and PNG images are allowed.';
        if (file.size > 5 * 1024 * 1024) return 'File must not exceed 5 MB.';
        return null;
    },

    showError(input, message) {
        input.classList.add('error');
        let errorEl = input.parentElement.querySelector('.form-error');
        if (!errorEl) {
            errorEl = document.createElement('div');
            errorEl.className = 'form-error';
            input.parentElement.appendChild(errorEl);
        }
        errorEl.textContent = message;
    },

    clearError(input) {
        input.classList.remove('error');
        const errorEl = input.parentElement.querySelector('.form-error');
        if (errorEl) errorEl.remove();
    },

    validateField(input, validator) {
        const error = validator(input.value);
        if (error) {
            this.showError(input, error);
            return false;
        }
        this.clearError(input);
        return true;
    }
};
