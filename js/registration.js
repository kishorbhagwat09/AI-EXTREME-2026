/**
 * AI EXTREME 2026 - Registration Form
 */

document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('registration-form');
    if (!form) return;

    let currentStep = 1;
    const totalSteps = 4;
    let currentFee = 200;

    initSteps();
    initGenderCounts();
    initFileUpload();
    fetchCurrentFee();

    function initSteps() {
        document.getElementById('btn-next')?.addEventListener('click', () => {
            if (validateStep(currentStep)) {
                if (currentStep < totalSteps) {
                    currentStep++;
                    showStep(currentStep);
                }
            }
        });

        document.getElementById('btn-back')?.addEventListener('click', () => {
            if (currentStep > 1) {
                currentStep--;
                showStep(currentStep);
            }
        });

        form.addEventListener('submit', (e) => {
            if (!validateStep(4)) {
                e.preventDefault();
                return;
            }
            const submitBtn = document.getElementById('btn-submit');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.textContent = 'SUBMITTING...';
            }
        });

        showStep(1);
    }

    function showStep(step) {
        document.querySelectorAll('.form-step').forEach(el => el.classList.remove('active'));
        document.getElementById(`step-${step}`)?.classList.add('active');

        document.querySelectorAll('.progress-step').forEach((el, i) => {
            el.classList.remove('active', 'completed');
            if (i + 1 < step) el.classList.add('completed');
            if (i + 1 === step) el.classList.add('active');
        });

        document.getElementById('btn-back').style.display = step === 1 ? 'none' : 'inline-flex';
        document.getElementById('btn-next').style.display = step === totalSteps ? 'none' : 'inline-flex';
        document.getElementById('btn-submit').style.display = step === totalSteps ? 'inline-flex' : 'none';

        if (step === 4) updateConfirmationSummary();

        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function validateStep(step) {
        let valid = true;

        if (step === 1) {
            const teamName = document.getElementById('team_name');
            valid = Validation.validateField(teamName, Validation.validateTeamName);
        }

        if (step === 2) {
            const fields = [
                { id: 'leader_name', fn: Validation.validateFullName },
                { id: 'leader_prn', fn: Validation.validatePRN },
                { id: 'leader_email', fn: Validation.validateEmail },
                { id: 'leader_mobile', fn: Validation.validateMobile },
                { id: 'leader_gender', fn: Validation.validateGender },
                { id: 'member2_name', fn: Validation.validateFullName },
                { id: 'member2_prn', fn: Validation.validatePRN },
                { id: 'member2_email', fn: Validation.validateEmail },
                { id: 'member2_mobile', fn: Validation.validateMobile },
                { id: 'member2_gender', fn: Validation.validateGender },
                { id: 'member3_name', fn: Validation.validateFullName },
                { id: 'member3_prn', fn: Validation.validatePRN },
                { id: 'member3_email', fn: Validation.validateEmail },
                { id: 'member3_mobile', fn: Validation.validateMobile },
                { id: 'member3_gender', fn: Validation.validateGender },
            ];

            fields.forEach(({ id, fn }) => {
                const input = document.getElementById(id);
                if (input && !Validation.validateField(input, fn)) valid = false;
            });

            // Duplicate PRN check
            const prns = ['leader_prn', 'member2_prn', 'member3_prn'].map(id => document.getElementById(id).value.trim());
            if (new Set(prns).size !== prns.length) {
                alert('Duplicate PRN detected within the team.');
                valid = false;
            }

            // Duplicate email check
            const emails = ['leader_email', 'member2_email', 'member3_email'].map(id => document.getElementById(id).value.trim().toLowerCase());
            if (new Set(emails).size !== emails.length) {
                alert('Duplicate email detected within the team.');
                valid = false;
            }
        }

        if (step === 3) {
            const fileInput = document.getElementById('payment_screenshot');
            const file = fileInput?.files[0];
            const error = Validation.validateFile(file);
            if (error) {
                alert(error);
                valid = false;
            }
        }

        if (step === 4) {
            const confirm = document.getElementById('confirm');
            if (!confirm?.checked) {
                alert('Please confirm that all information is correct.');
                valid = false;
            }
        }

        return valid;
    }

    function initGenderCounts() {
        const genderSelects = document.querySelectorAll('[id$="_gender"]');
        genderSelects.forEach(select => {
            select.addEventListener('change', updateTeamSummary);
        });
        updateTeamSummary();
    }

    function updateTeamSummary() {
        const genders = ['leader_gender', 'member2_gender', 'member3_gender'].map(id => {
            return document.getElementById(id)?.value || '';
        });

        let boys = 0, girls = 0, other = 0;
        genders.forEach(g => {
            if (g === 'Male') boys++;
            else if (g === 'Female') girls++;
            else if (g) other++;
        });

        const leaderName = document.getElementById('leader_name')?.value || '-';

        document.getElementById('summary-total').textContent = '3';
        document.getElementById('summary-leader').textContent = leaderName || '-';
        document.getElementById('summary-boys').textContent = boys;
        document.getElementById('summary-girls').textContent = girls;
        document.getElementById('summary-other').textContent = other;
    }

    function initFileUpload() {
        const dropzone = document.getElementById('file-upload');
        const fileInput = document.getElementById('payment_screenshot');
        const preview = document.getElementById('file-preview');
        if (!dropzone || !fileInput) return;

        dropzone.addEventListener('click', () => fileInput.click());

        dropzone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropzone.classList.add('dragover');
        });

        dropzone.addEventListener('dragleave', () => dropzone.classList.remove('dragover'));

        dropzone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropzone.classList.remove('dragover');
            if (e.dataTransfer.files.length) {
                fileInput.files = e.dataTransfer.files;
                showPreview(e.dataTransfer.files[0]);
            }
        });

        fileInput.addEventListener('change', () => {
            if (fileInput.files[0]) showPreview(fileInput.files[0]);
        });

        function showPreview(file) {
            const error = Validation.validateFile(file);
            if (error) {
                alert(error);
                fileInput.value = '';
                preview.innerHTML = '';
                return;
            }
            const reader = new FileReader();
            reader.onload = (e) => {
                preview.innerHTML = `<img src="${e.target.result}" alt="Payment screenshot preview">`;
            };
            reader.readAsDataURL(file);
        }
    }

    function fetchCurrentFee() {
        fetch('php/registration/calculate-fee.php')
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    currentFee = data.fee;
                    updateFeeDisplay(data);
                }
            })
            .catch(() => {});
    }

    function updateFeeDisplay(data) {
        const feeEls = document.querySelectorAll('.current-fee');
        feeEls.forEach(el => el.textContent = data.fee_display);

        const earlyBird = document.getElementById('fee-early-bird');
        const regular = document.getElementById('fee-regular');
        if (earlyBird && regular) {
            earlyBird.classList.toggle('active', data.early_bird);
            regular.classList.toggle('active', !data.early_bird);
        }
    }

    function updateConfirmationSummary() {
        updateTeamSummary();

        document.getElementById('confirm-team-name').textContent = document.getElementById('team_name')?.value || '-';
        document.getElementById('confirm-leader').textContent = document.getElementById('leader_name')?.value || '-';
        document.getElementById('confirm-member2').textContent = document.getElementById('member2_name')?.value || '-';
        document.getElementById('confirm-member3').textContent = document.getElementById('member3_name')?.value || '-';
        document.getElementById('confirm-fee').textContent = '₹' + currentFee;
        document.getElementById('confirm-screenshot').textContent = document.getElementById('payment_screenshot')?.files[0] ? 'Uploaded' : 'Not uploaded';
    }

    // Update summary on name changes
    ['leader_name', 'member2_name', 'member3_name'].forEach(id => {
        document.getElementById(id)?.addEventListener('input', updateTeamSummary);
    });
});
