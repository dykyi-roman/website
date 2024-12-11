document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('loginForm');
    const emailInput = document.getElementById('loginEmail');
    const passwordInput = document.getElementById('loginPassword');
    const errorFeedback = loginForm.querySelector('.invalid-feedback');

    loginForm.addEventListener('submit', function(e) {
        e.preventDefault();

        const email = emailInput.value;
        const password = passwordInput.value;

        fetch('/login', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                email: email,
                password: password
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Redirect to dashboard or specified route
                window.location.href = data.redirect;
            } else {
                // Show error message
                errorFeedback.textContent = data.message || 'Login failed';
                emailInput.classList.add('is-invalid');
                passwordInput.classList.add('is-invalid');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            errorFeedback.textContent = 'An unexpected error occurred';
            emailInput.classList.add('is-invalid');
            passwordInput.classList.add('is-invalid');
        });
    });
});
