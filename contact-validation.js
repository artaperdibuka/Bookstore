document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('contactForm');
    const nameInput = document.getElementById('name');
    const emailInput = document.getElementById('email');
    const numberInput = document.getElementById('number');
    const messageInput = document.getElementById('message');

    const nameError = document.getElementById('nameError');
    const emailError = document.getElementById('emailError');
    const numberError = document.getElementById('numberError');
    const messageError = document.getElementById('messageError');

    function validateName() {
        if (nameInput.value.trim() === '') {
            nameError.textContent = 'Name is required';
            return false;
        }
        if (nameInput.value.trim().length < 2) {
            nameError.textContent = 'Name must be at least 2 characters';
            return false;
        }
        nameError.textContent = '';
        return true;
    }

    function validateEmail() {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (emailInput.value.trim() === '') {
            emailError.textContent = 'Email is required';
            return false;
        }
        if (!emailRegex.test(emailInput.value.trim())) {
            emailError.textContent = 'Invalid email format';
            return false;
        }
        emailError.textContent = '';
        return true;
    }

    function validateNumber() {
        const phoneRegex = /^[0-9]{9,}$/;
        if (numberInput.value.trim() === '') {
            numberError.textContent = 'Phone number is required';
            return false;
        }
        if (!phoneRegex.test(numberInput.value.trim())) {
            numberError.textContent = 'Invalid phone number';
            return false;
        }
        numberError.textContent = '';
        return true;
    }

    function validateMessage() {
        if (messageInput.value.trim() === '') {
            messageError.textContent = 'Message is required';
            return false;
        }
        if (messageInput.value.trim().length < 10) {
            messageError.textContent = 'Message must be at least 10 characters';
            return false;
        }
        messageError.textContent = '';
        return true;
    }

    // Real-time validation
    nameInput.addEventListener('input', validateName);
    emailInput.addEventListener('input', validateEmail);
    numberInput.addEventListener('input', validateNumber);
    messageInput.addEventListener('input', validateMessage);

    form.addEventListener('submit', function(e) {
        e.preventDefault();
    
        const isNameValid = validateName();
        const isEmailValid = validateEmail();
        const isNumberValid = validateNumber();
        const isMessageValid = validateMessage();
    
        if (isNameValid && isEmailValid && isNumberValid && isMessageValid) {
            // Përdorni window.Swal
            window.Swal.fire({
                icon: 'success',
                title: 'Message Sent!',
                text: 'Your message has been successfully sent.',
                confirmButtonText: 'OK'
            }).then(() => {
                // Zbraz fushat pas klikimit OK
                nameInput.value = '';
                emailInput.value = '';
                numberInput.value = '';
                messageInput.value = '';
            });
        }
    });
});