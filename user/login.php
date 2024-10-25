<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/db_connect.php';
require_once '../classes/User.php';

$db = getDBConnection();
$user = new User($db);

// Inisialisasi variabel
$email = $password = "";
$emailErr = $passwordErr = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validasi email
    if (empty(trim($_POST["email"]))) {
        $emailErr = "Email is required";
    } else {
        $email = sanitizeInput(trim($_POST["email"]));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $emailErr = "Invalid email format";
        }
    }

    // Validasi password
    if (empty(trim($_POST["password"]))) {
        $passwordErr = "Password is required";
    } else {
        $password = trim($_POST["password"]);
    }

    // Authenticate user after validation
    if (empty($emailErr) && empty($passwordErr)) {
        if ($user->authenticate($email, $password)) {
            // Set session for user
            $_SESSION['user_id'] = $user->id; // Change to user_id for better clarity
            $_SESSION['role'] = $user->is_admin ? 'admin' : 'user'; // Store role in session

            // Redirect based on role
            if ($user->is_admin) {
                header("Location: ../admin/dashboard_admin.php");
            } else {
                header("Location: dashboard_user.php");
            }
            exit();
        } else {
            $error = "Invalid email or password";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>  
    <link rel="icon" href="../logo/logoUAS.png" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto mt-10 max-w-md">
        <h1 class="text-3xl font-bold mb-5 text-center text-gray-800">Login</h1>

        <?php if (isset($error)) : ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <strong class="font-bold">Error!</strong>
                <span class="block sm:inline"><?php echo $error; ?></span>
            </div>
        <?php endif; ?>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="bg-white shadow-md rounded-lg px-8 pt-6 pb-8 mb-4 border border-gray-300">
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="email">Email</label>
                <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline <?php echo (!empty($emailErr)) ? 'border-red-500' : ''; ?>" id="email" type="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                <p class="text-red-500 text-xs italic"><?php echo $emailErr; ?></p>
            </div>
            <div class="mb-4 relative">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="password">Password</label>
                <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline <?php echo (!empty($passwordErr)) ? 'border-red-500' : ''; ?>" id="password" type="password" name="password" required>
                <span class="absolute inset-y-0 right-0 top-4 pr-3 flex items-center text-gray-700">
                    <button type="button" onmousedown="showPassword()" onmouseup="hidePassword()" onmouseleave="hidePassword()" class="focus:outline-none">
                    <img src="https://img.icons8.com/ios-filled/16/000000/visible.png" id="passwordIcon" alt="Show Password" class="w-5 h-5"/>
                    </button>
                </span>
                <p class="text-red-500 text-xs italic"><?php echo $passwordErr; ?></p>  
            </div>

            <div class="flex items-center justify-between">
                <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" type="submit">Login</button>
            </div>
        </form> 
        <div class="text-center mt-4">
         <p class="text-gray-600">Belum punya akun? <a href="../user/Register.php" class="text-blue-500 hover:underline">Register</a>
        <br><a href="../includes/lupa_pass.php" class="text-blue-500 hover:underline">Lupa Password?</a>
         </p>
    </div>

    </div>

    <script>
        function showPassword() {
            document.getElementById('password').type = 'text';
        }

        function hidePassword() {
            document.getElementById('password').type = 'password';
        }
    </script>
</body>
</html>
