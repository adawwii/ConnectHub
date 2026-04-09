<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="shortcut icon" href="https://cdn.iconscout.com/icon/premium/png-256-thumb/online-registration-icon-svg-download-png-2133475.png" type="image/x-icon">
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">

    <div class="bg-white shadow-xl rounded-2xl w-full max-w-md p-8">

        <h2 class="text-2xl font-bold text-center text-gray-800 mb-6">
            Create Account
        </h2>

        <form method="POST" action="{{ route('create-user') }}" class="space-y-5">
            @csrf

            <!-- Name -->
            <div>
                <label class="block text-sm text-gray-600 mb-1">Name</label>
                <input 
                    type="text" 
                    name="name" 
                    required
                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none"
                    placeholder="Enter your name"
                >
            </div>

            <!-- Email -->
            <div>
                <label class="block text-sm text-gray-600 mb-1">Email</label>
                <input 
                    type="email" 
                    name="email" 
                    required
                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none"
                    placeholder="Enter your email"
                >
            </div>

            <!-- Password -->
            <div>
                <label class="block text-sm text-gray-600 mb-1">Password</label>
                <input 
                    type="password" 
                    name="password" 
                    required
                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none"
                    placeholder="Enter password"
                >
            </div>

            <!-- Confirm Password -->
            <div>
                <label class="block text-sm text-gray-600 mb-1">Confirm Password</label>
                <input 
                    type="password" 
                    name="password_confirmation" 
                    required
                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none"
                    placeholder="Confirm password"
                >
            </div>

            <!-- Submit -->
            <button 
                type="submit"
                class="w-full bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 transition duration-200"
            >
                Register
            </button>
        </form>

    </div>

</body>
</html>