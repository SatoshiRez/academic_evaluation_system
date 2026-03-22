<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SSIPS Academic Portal | Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        /* CSS Reset & Styling */
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }
        body { height: 100vh; width: 100%; overflow: hidden; }
        .container { display: flex; height: 100%; width: 100%; }
        
        /* Left Side: Image */
        .image-section { 
            flex: 1; 
            background-image: url('ssips.jpg'); /* Ensure this image is in the folder */
            background-size: cover; 
            background-position: center; 
            position: relative; 
        }
        .image-overlay { position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.2); }

        /* Right Side: Login */
        .login-section { 
            flex: 1; 
            background-color: #fdfbf7; 
            background-image: linear-gradient(135deg, #fdfbf7 0%, #fffaf0 100%); 
            display: flex; flex-direction: column; justify-content: center; align-items: center; padding: 40px; 
        }
        
        .content-wrapper { width: 100%; max-width: 400px; animation: fadeIn 0.5s ease; }
        
        h1 { font-size: 2.5rem; color: #1a1a1a; font-weight: 700; margin-bottom: 10px; line-height: 1.2; }
        p.subtitle { color: #4a4a4a; font-size: 1rem; margin-bottom: 30px; }

        /* Role Buttons */
        .roles-grid { display: flex; flex-direction: column; gap: 15px; }
        .role-btn { 
            display: flex; align-items: center; justify-content: space-between; 
            background: white; border: 1px solid #eaeaea; padding: 20px; border-radius: 12px; 
            cursor: pointer; transition: all 0.2s ease; font-size: 1.1rem; font-weight: 600; color: #1a1a1a; 
        }
        .role-btn:hover { 
            transform: translateY(-3px); border-color: #1a1a1a; background-color: #1a1a1a; color: white; 
        }

        /* Form Styling */
        .login-form-container { display: none; }
        .input-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-size: 0.9rem; font-weight: 600; color: #333; }
        input { 
            width: 100%; padding: 14px; border-radius: 8px; border: 1px solid #ddd; 
            background-color: white; font-size: 1rem; outline: none; transition: border-color 0.2s; 
        }
        input:focus { border-color: #1a1a1a; }
        
        .login-btn { 
            width: 100%; padding: 16px; background-color: #1a1a1a; color: white; 
            border: none; border-radius: 8px; font-size: 1rem; font-weight: 600; cursor: pointer; margin-top: 10px; 
        }
        .login-btn:hover { background-color: #333; }
        
        .back-link { cursor: pointer; margin-bottom: 20px; display: inline-block; color: #666; }
        .back-link:hover { text-decoration: underline; color: black; }

        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        
        @media (max-width: 900px) { .container { flex-direction: column; } .image-section { display: none; } .login-section { flex: 1; padding: 20px; } }
    </style>
</head>
<body>

    <div class="container">
        <div class="image-section"><div class="image-overlay"></div></div>

        <div class="login-section">
            <div class="content-wrapper">
                
                <!-- 1. Role Selection -->
                <div id="roleSelection">
                    <h1>SSIPS Academic Portal</h1>
                    <p class="subtitle">Welcome back. Please select your role to continue.</p>
                    <div class="roles-grid">
                        <div class="role-btn" onclick="selectRole('Principal')"><span>Principal</span><span>→</span></div>
                        <div class="role-btn" onclick="selectRole('Teacher')"><span>Teacher</span><span>→</span></div>
                        <div class="role-btn" onclick="selectRole('Student')"><span>Student</span><span>→</span></div>
                    </div>
                </div>

                <!-- 2. Login Form -->
                <div id="loginForm" class="login-form-container">
                    <div class="back-link" onclick="goBack()">← Back to role selection</div>
                    <h1 id="welcomeText">Login</h1>
                    <p class="subtitle" id="roleSubtitle">Please enter your credentials.</p>

                    <form action="authenticate.php" method="POST">
                        <!-- Hidden Input for Role -->
                        <input type="hidden" name="role" id="hiddenRole">

                        <div class="input-group">
                            <label>Email Address</label>
                            <input type="email" name="email" placeholder="e.g. Name@xyz.com" required>
                        </div>
                        
                        <div class="input-group">
                            <label>Password</label>
                            <input type="password" name="password" placeholder="Enter your password" required>
                        </div>

                        <button class="login-btn" type="submit">Login</button>
                    </form>
                </div>

            </div>
        </div>
    </div>

    <script>
        function selectRole(role) {
            document.getElementById("roleSelection").style.display = "none";
            document.getElementById("loginForm").style.display = "block";
            document.getElementById("welcomeText").innerText = role + " Login";
            document.getElementById("roleSubtitle").innerText = "Enter your " + role + " credentials.";
            
            // Set the hidden input value so PHP knows which role was selected
            document.getElementById("hiddenRole").value = role;
        }

        function goBack() {
            document.getElementById("loginForm").style.display = "none";
            document.getElementById("roleSelection").style.display = "block";
        }
    </script>

</body>
</html>