document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('.account-settings');
    const imageInput = document.createElement('input');
    imageInput.type = 'file';
    imageInput.accept = 'image/*';
    imageInput.style.display = 'none';
    form.appendChild(imageInput);

    // Handle image upload
    const profileImage = document.querySelector('.item-image');
    const imageOverlay = document.querySelector('.image-overlay');
    
    imageOverlay.addEventListener('click', () => {
        imageInput.click();
    });

    imageInput.addEventListener('change', function(e) {
        if (this.files && this.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                profileImage.src = e.target.result;
            };
            reader.readAsDataURL(this.files[0]);
        }
    });

    // Make fields editable
    const editableFields = ['name', 'email', 'phone'];
    editableFields.forEach(field => {
        const container = document.querySelector(`label[for="${field}"] strong`);
        if (container) {
            const value = container.textContent;
            const input = document.createElement('input');
            input.type = field === 'email' ? 'email' : 'text';
            input.value = value;
            input.className = 'form-control editable-input';
            input.name = field;
            container.parentNode.replaceChild(input, container);
        }
    });

    // Handle form submission
    const saveButton = document.getElementById('save-account-settings');
    saveButton.addEventListener('click', async function(e) {
        e.preventDefault();

        const formData = new FormData();
        if (imageInput.files[0]) {
            formData.append('photo', imageInput.files[0]);
        }

        editableFields.forEach(field => {
            const input = form.querySelector(`input[name="${field}"]`);
            if (input) {
                formData.append(field, input.value);
            }
        });

        try {
            const response = await fetch('/api/v1/users', {
                method: 'PUT',
                body: formData
            });

            if (response.ok) {
                const result = await response.json();
                // Show success message
                alert('Settings saved successfully!');
            } else {
                throw new Error('Failed to save settings');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Failed to save settings. Please try again.');
        }
    });
});
