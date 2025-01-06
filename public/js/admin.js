document.addEventListener('DOMContentLoaded', function() {
    // Handle delete confirmations
    const deleteButtons = document.querySelectorAll('[data-confirm]');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            if (!confirm(this.dataset.confirm || 'Are you sure you want to delete this item?')) {
                e.preventDefault();
            }
        });
    });

    // Auto-hide messages after 5 seconds
    const messages = document.querySelectorAll('.success-message, .error-message');
    messages.forEach(message => {
        setTimeout(() => {
            message.style.opacity = '0';
            setTimeout(() => message.remove(), 300);
        }, 5000);
    });

    // Image preview for file inputs
    const imageInputs = document.querySelectorAll('input[type="file"][accept*="image"]');
    imageInputs.forEach(input => {
        input.addEventListener('change', function() {
            const preview = this.nextElementSibling;
            if (preview && preview.classList.contains('image-preview')) {
                if (this.files && this.files[0]) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        preview.src = e.target.result;
                        preview.style.display = 'block';
                    };
                    reader.readAsDataURL(this.files[0]);
                }
            }
        });
    });

    // Form validation
    const forms = document.querySelectorAll('form[data-validate]');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;

            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.classList.add('error');
                    
                    // Create or update error message
                    let errorMsg = field.nextElementSibling;
                    if (!errorMsg || !errorMsg.classList.contains('field-error')) {
                        errorMsg = document.createElement('div');
                        errorMsg.classList.add('field-error');
                        field.parentNode.insertBefore(errorMsg, field.nextSibling);
                    }
                    errorMsg.textContent = `${field.getAttribute('data-label') || 'This field'} is required`;
                } else {
                    field.classList.remove('error');
                    const errorMsg = field.nextElementSibling;
                    if (errorMsg && errorMsg.classList.contains('field-error')) {
                        errorMsg.remove();
                    }
                }
            });

            if (!isValid) {
                e.preventDefault();
            }
        });
    });
});
