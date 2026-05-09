// Form Validation JavaScript

class FormValidator {
    constructor() {
        this.forms = document.querySelectorAll('form[data-validate]');
        this.initializeForms();
    }

    initializeForms() {
        this.forms.forEach(form => {
            form.addEventListener('submit', (e) => this.handleSubmit(e, form));
            
            const inputs = form.querySelectorAll('input, textarea, select');
            inputs.forEach(input => {
                input.addEventListener('blur', () => this.validateField(input));
                input.addEventListener('input', () => {
                    if (input.classList.contains('error')) {
                        this.validateField(input);
                    }
                });
            });
        });
    }

    handleSubmit(e, form) {
        e.preventDefault();
        const inputs = form.querySelectorAll('input, textarea, select');
        let isValid = true;

        inputs.forEach(input => {
            if (!this.validateField(input)) {
                isValid = false;
            }
        });

        if (isValid) {
            this.submitForm(form);
        }
    }

    validateField(field) {
        const type = field.getAttribute('data-validate') || field.type;
        let isValid = true;
        let errorMessage = '';

        // Required
        if (field.hasAttribute('required') && !field.value.trim()) {
            isValid = false;
            errorMessage = `${this.getFieldLabel(field)} is required`;
        }

        // Email
        if (isValid && type === 'email' && field.value) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(field.value)) {
                isValid = false;
                errorMessage = 'Please enter a valid email address';
            }
        }

        // Phone
        if (isValid && type === 'phone' && field.value) {
            const phoneRegex = /^(\+63|0)\d{9,10}$/;
            if (!phoneRegex.test(field.value.replace(/\s|-/g, ''))) {
                isValid = false;
                errorMessage = 'Please enter a valid phone number';
            }
        }

        // Password
        if (isValid && type === 'password' && field.value) {
            const password = field.value;
            if (password.length < 8) {
                isValid = false;
                errorMessage = 'Password must be at least 8 characters';
            } else if (!/[A-Z]/.test(password)) {
                isValid = false;
                errorMessage = 'Password must contain an uppercase letter';
            } else if (!/[a-z]/.test(password)) {
                isValid = false;
                errorMessage = 'Password must contain a lowercase letter';
            } else if (!/[0-9]/.test(password)) {
                isValid = false;
                errorMessage = 'Password must contain a number';
            }
        }

        // Confirm Password
        if (isValid && type === 'password-confirm' && field.value) {
            const passwordField = field.form.querySelector('input[type="password"]:not([data-validate="password-confirm"])');
            if (passwordField && field.value !== passwordField.value) {
                isValid = false;
                errorMessage = 'Passwords do not match';
            }
        }

        // Min Length
        if (isValid && field.hasAttribute('minlength') && field.value) {
            const minLength = parseInt(field.getAttribute('minlength'));
            if (field.value.length < minLength) {
                isValid = false;
                errorMessage = `Must be at least ${minLength} characters`;
            }
        }

        // Max Length
        if (isValid && field.hasAttribute('maxlength') && field.value) {
            const maxLength = parseInt(field.getAttribute('maxlength'));
            if (field.value.length > maxLength) {
                isValid = false;
                errorMessage = `Must not exceed ${maxLength} characters`;
            }
        }

        // Custom Pattern
        if (isValid && field.hasAttribute('pattern') && field.value) {
            const pattern = new RegExp(field.getAttribute('pattern'));
            if (!pattern.test(field.value)) {
                isValid = false;
                errorMessage = field.getAttribute('data-error') || 'Invalid format';
            }
        }

        this.setFieldState(field, isValid, errorMessage);
        return isValid;
    }

    setFieldState(field, isValid, errorMessage) {
        const group = field.closest('.form-group');
        
        if (!isValid) {
            field.classList.add('border-red-500', 'bg-red-50');
            field.classList.remove('border-slate-200', 'bg-slate-50');
            
            let errorEl = group.querySelector('.form-error');
            if (!errorEl) {
                errorEl = document.createElement('p');
                errorEl.className = 'form-error text-red-600 text-xs font-semibold mt-2';
                group.appendChild(errorEl);
            }
            errorEl.textContent = errorMessage;
        } else {
            field.classList.remove('border-red-500', 'bg-red-50');
            field.classList.add('border-slate-200', 'bg-slate-50');
            
            const errorEl = group.querySelector('.form-error');
            if (errorEl) errorEl.remove();
        }
    }

    getFieldLabel(field) {
        const label = field.closest('.form-group')?.querySelector('label');
        return label ? label.textContent.replace(/\s*\*\s*$/, '') : field.name || 'This field';
    }

    submitForm(form) {
        // Show loading state
        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fa-solid fa-circle-notch animate-spin mr-2"></i>Processing...';
        }

        // Simulate form submission (replace with actual AJAX call)
        setTimeout(() => {
            showNotification('Form submitted successfully!', 'success');
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = submitBtn.getAttribute('data-original-text') || 'Submit';
            }
        }, 1500);
    }
}

// Password Strength Indicator
class PasswordStrength {
    constructor() {
        this.passwordInputs = document.querySelectorAll('input[type="password"][data-strength]');
        this.init();
    }

    init() {
        this.passwordInputs.forEach(input => {
            input.addEventListener('input', (e) => this.checkStrength(e.target));
        });
    }

    checkStrength(input) {
        const password = input.value;
        let strength = 0;

        if (!password) {
            this.updateStrengthDisplay(input, '');
            return;
        }

        // Length
        if (password.length >= 8) strength += 25;
        if (password.length >= 12) strength += 25;

        // Uppercase
        if (/[A-Z]/.test(password)) strength += 25;

        // Numbers
        if (/[0-9]/.test(password)) strength += 12;

        // Special characters
        if (/[!@#$%^&*(),.?":{}|<>]/.test(password)) strength += 13;

        this.updateStrengthDisplay(input, strength);
    }

    updateStrengthDisplay(input, strength) {
        const container = input.closest('.form-group');
        let meter = container.querySelector('.password-strength');

        if (!meter) {
            meter = document.createElement('div');
            meter.className = 'password-strength';
            input.after(meter);
        }

        if (!strength) {
            meter.classList.remove('active', 'weak', 'fair', 'good', 'strong');
            return;
        }

        meter.classList.add('active');

        if (strength <= 25) {
            meter.className = 'password-strength active weak';
        } else if (strength <= 50) {
            meter.className = 'password-strength active fair';
        } else if (strength <= 75) {
            meter.className = 'password-strength active good';
        } else {
            meter.className = 'password-strength active strong';
        }
    }
}

// Initialize validators on page load
document.addEventListener('DOMContentLoaded', () => {
    new FormValidator();
    new PasswordStrength();

    // Toggle password visibility
    const passwordToggles = document.querySelectorAll('[data-password-toggle]');
    passwordToggles.forEach(toggle => {
        toggle.addEventListener('click', function() {
            const input = this.closest('.form-group').querySelector('input[type="password"], input[type="text"]');
            if (input) {
                const isPassword = input.type === 'password';
                input.type = isPassword ? 'text' : 'password';
                this.innerHTML = isPassword 
                    ? '<i class="fa-solid fa-eye-slash"></i>' 
                    : '<i class="fa-solid fa-eye"></i>';
            }
        });
    });
});

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { FormValidator, PasswordStrength };
}
