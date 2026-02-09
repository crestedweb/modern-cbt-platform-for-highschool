<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Nigerian CBT System</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-green-500 to-green-700 min-h-screen flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-2xl p-8 w-full max-w-md">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-green-700">🎓 CBT System</h1>
            <p class="text-gray-600 mt-2">Cambridge International School Examination Portal</p>
        </div>

        <?php if($errors->any()): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?php echo e($errors->first()); ?>

            </div>
        <?php endif; ?>

        <form action="<?php echo e(route('login.post')); ?>" method="POST" class="space-y-6">
            <?php echo csrf_field(); ?>
            
            <div>
                <label for="identifier" class="block text-sm font-medium text-gray-700 mb-2">
                    Email or Registration Number
                </label>
                <input 
                    type="text" 
                    id="identifier" 
                    name="identifier" 
                    value="<?php echo e(old('identifier')); ?>"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                    placeholder="Enter email or registration number"
                    required
                    autofocus
                >
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                    Password
                </label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                    placeholder="Enter your password"
                    required
                >
            </div>

            <button 
                type="submit" 
                class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-3 rounded-lg transition duration-200"
            >
                Login
            </button>
        </form>

        <div class="mt-6 text-center text-sm text-gray-600">
            <p class="font-semibold mb-2">Demo Credentials:</p>
            <div class="space-y-1 text-xs">
                <p><strong>Admin:</strong> admin@school.com / password</p>
                <p><strong>Teacher:</strong> okafor@school.com / password</p>
                <p><strong>Student:</strong> STD2024001 / password</p>
            </div>
        </div>
    </div>
</body>
</html>
<?php /**PATH C:\laragon\www\cbtplus\modern-cbt-platform-for-highschool\resources\views/auth/login.blade.php ENDPATH**/ ?>