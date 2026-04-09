<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
</head>
<body style="font-family: Arial; background: #f9f9f9; padding: 20px;">
    
    <div style="background: white; padding: 20px; border-radius: 10px;">
        <h2>Welcome, {{ $user->name }} 👋</h2>
        
        <p>
            We're happy to have you on our platform.
        </p>

        <p>
            Your email: {{ $user->email }}
        </p>

        <p style="margin-top: 20px;">
            🚀 Let’s get started!
        </p>
    </div>

</body>
</html>