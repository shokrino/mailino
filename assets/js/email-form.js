document.addEventListener('DOMContentLoaded', function() {
    const mailinoForm = document.querySelector('#mailino-email-form');
    const responseDiv = document.getElementById('mailino-response');
    const loadingIndicator = mailinoForm.querySelector('.loading-email-subs');

    mailinoForm.addEventListener('submit', function(e) {
        e.preventDefault(); // Prevent the default form submission

        // Get the email input value
        const emailInput = document.getElementById('email');
        const email = emailInput.value;
        const allowedProviders = ['gmail.com', 'yahoo.com', 'outlook.com', 'hotmail.com'];
        const emailDomain = email.split('@').pop();

        // Check for valid email providers
        if (!allowedProviders.includes(emailDomain)) {
            alert('Please use a valid email provider like Gmail, Yahoo, or Outlook.');
            return; // Exit the function if the email is not valid
        }

        loadingIndicator.style.display = 'block'; // Show loading spinner

        const formData = new FormData(mailinoForm);
        formData.append('action', 'save_email_mailino'); // Set the action

        // Perform the AJAX request
        fetch(mailino_script_data.ajax_url, {
            method: 'POST', // Ensure it's POST
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest' // Set custom header
            }
        })
        .then(response => {
            loadingIndicator.style.display = 'none'; // Hide loading spinner
            return response.json(); // Parse JSON response
        })
        .then(data => {
            if (data.success) {
                responseDiv.textContent = data.message; // Display success message
                responseDiv.style.color = 'green'; // Set text color to green
            } else {
                responseDiv.textContent = data.error; // Display error message
                responseDiv.style.color = 'red'; // Set text color to red
            }
        })
        .catch(error => {
            loadingIndicator.style.display = 'none'; // Hide loading spinner on error
            responseDiv.textContent = 'An error occurred.'; // General error message
        });
    });
});

