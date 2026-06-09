<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ConnectHub - Register</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="shortcut icon" href="https://img.icons8.com/?size=100&id=7859&format=png&color=228BE6" type="image/x-icon">
    <style>
  @keyframes gradient-move {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
  }
  .animate-moving-gradient {
    background-size: 200% 200%;
    animation: gradient-move 12s ease infinite;
  }
</style>
</head>
<body class="min-h-screen w-full flex items-center justify-center p-4 bg-gradient-to-tr from-slate-50 via-blue-500 to-slate-300 animate-moving-gradient flex items-center justify-center min-h-screen">

    <div class="w-full max-w-md bg-white/90 backdrop-blur-md rounded-2xl shadow-2xl border border-white/20 p-8">

        <h2 class="text-3xl font-bold text-center text-gray-800 mb-6">
           <span class="bg-gradient-to-r from-slate-700 via-slate-900 to-black bg-clip-text text-transparent tracking-tight">
    Connect</span><span class="text-2xl font-extrabold bg-gradient-to-r from-blue-400 to-blue-700 bg-clip-text text-transparent">Hub</span>
        </h2>
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
                    value="{{ old('name') }}"
                    required
                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none"
                    placeholder="Enter your name"
                >
                @error('name')
                <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Email -->
            <div>
                <label class="block text-sm text-gray-600 mb-1">Email</label>
                <input 
                    type="email" 
                    name="email" 
                    value="{{ old('email') }}"
                    required
                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none"
                    placeholder="Enter your email"
                >
                @error('email')
                <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                @enderror
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
                @error('password')
                <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                @enderror
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
                style="text-shadow: 1px 1px rgba(0, 19, 44, 0.4);" class="w-full text-sm font-semibold bg-gradient-to-t from-slate-800 via-slate-900 to-black text-white py-3 rounded-xl transition duration-300 ease-in-out hover:brightness-125 active:scale-[0.99] shadow-lg shadow-black/20"
            >
                Register
            </button>
        </form>

        <p class="text-sm text-center text-gray-600 mt-6">
            Have an account?
            <a href="{{ route('login-user') }}" class="text-blue-500 hover:underline">
                Login
            </a>
        </p>

    </div>

</body>
</html>