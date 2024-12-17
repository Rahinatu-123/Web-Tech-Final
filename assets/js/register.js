document.getElementById("registration-form").addEventListener("submit", function (e) {
    e.preventDefault(); // Prevent form submission
  
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    const passwordRegex = /^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$/;
  
    const email = document.getElementById("email").value;
    const password = document.getElementById("password").value;
    const confirmPassword = document.getElementById("confirm-password").value;
  
    // Validate email
    if (!emailRegex.test(email)) {
      alert("Please enter a valid email address.");
      return;
    }
  
    // Validate password
    if (!passwordRegex.test(password)) {
      alert("Password must be at least 8 characters long and contain both letters and numbers.");
      return;
    }
  
    // Check password confirmation
    if (password !== confirmPassword) {
      alert("Passwords do not match.");
      return;
    }
  
    alert("Registration successful!");
  });
  