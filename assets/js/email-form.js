document.addEventListener('DOMContentLoaded', function() {
    const mailinoForm = document.querySelector('#mailino-email-form');
    const responseDiv = mailinoForm.querySelector('.response-email-subs');
    const loadingIndicator = mailinoForm.querySelector('.loading-email-subs');
    const emailInput = mailinoForm.querySelector('#email');

    emailInput.addEventListener('input', function() {
        emailInput.value = convertPersianNumbersMailino(emailInput.value);
    });

    mailinoForm.addEventListener('submit', function(e) {
        e.preventDefault();

        const email = emailInput.value;
        const allowedProviders = ['gmail.com', 'yahoo.com', 'outlook.com', 'hotmail.com'];
        const emailDomain = email.split('@').pop();

        if (!allowedProviders.includes(emailDomain)) {
            alert('Please use a valid email provider like Gmail, Yahoo, or Outlook.');
            return;
        }

        loadingIndicator.style.display = 'flex';
        responseDiv.style.display = 'none';
        const formData = new FormData(mailinoForm);
        formData.append('action', 'save_email_mailino');

        fetch(mailino_script_data.ajax_url, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(response => response.json())
            .then(data => {
                loadingIndicator.style.display = 'none';
                responseDiv.style.display = 'flex';

                if (data.success) {
                    responseDiv.innerHTML = data.data.message;
                    emailInput.value = "";
                    responseDiv.style.backgroundColor = '#d4edda';
                    responseDiv.style.color = '#155724';
                } else {
                    responseDiv.innerHTML = data.data.message || 'An unexpected error occurred.';
                    responseDiv.style.backgroundColor = '#ffe2e2';
                    responseDiv.style.color = '#750404';
                }

                setTimeout(() => {
                    responseDiv.style.display = 'none';
                }, 5000);
            })
            .catch(() => {
                loadingIndicator.style.display = 'none';
                responseDiv.innerHTML = 'An error occurred while processing your request.';
                responseDiv.style.display = 'flex';
                responseDiv.style.backgroundColor = '#750404';
                responseDiv.style.color = '#ffe2e2';

                setTimeout(() => {
                    responseDiv.style.display = 'none';
                }, 5000);
            });
    });

    function convertPersianNumbersMailino(input) {
        const persianToEnglishNumbers = {
            '۰': '0',
            '۱': '1',
            '۲': '2',
            '۳': '3',
            '۴': '4',
            '۵': '5',
            '۶': '6',
            '۷': '7',
            '۸': '8',
            '۹': '9'
        };

        return input.replace(/[۰-۹]/g, match => persianToEnglishNumbers[match]);
    }
});
