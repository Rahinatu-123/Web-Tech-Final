document.getElementById('login-btn').addEventListener('click', function () {
    const email = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value.trim();

    // Email validation regex
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    // Password validation regex (at least 8 characters, including 1 letter and 1 number)
    const passwordRegex = /^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$/;

    let errors = [];

    if (!emailRegex.test(email)) {
        errors.push('Please enter a valid email address.');
    }

    if (!passwordRegex.test(password)) {
        errors.push('Password must be at least 8 characters long and include at least one letter and one number.');
    }

    if (errors.length > 0) {
        alert(errors.join('\n'));
    } else {
        alert('Login successful!');
        // Add your login logic here
    }
});
