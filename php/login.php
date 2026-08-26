<?php
// Capstone Project - One More Game
// Omar Elzahri
// Login Page

session_start();

// Simple test account for the sprint review.
// This can be replaced with the team database login later.
$testUsername = 'Jesse';
$testPassword = 'cool';
$error = '';

// If the user submits the form, check the login information.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Please enter your username and password.';
    } elseif ($username === $testUsername && $password === $testPassword) {
        session_regenerate_id(true);
        $_SESSION['is_logged_in'] = true;
        $_SESSION['username'] = $username;

        // Send the user to the home page after a successful login.
        header('Location: index.php');
        exit;
    } else {
        $error = 'Username or password is incorrect.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>One More Game | Login</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            font-family: Arial, Helvetica, sans-serif;
            color: #ffffff;
            background: #101010;
        }

        .login-page {
            width: 100%;
            max-width: 920px;
            min-height: 560px;
            display: grid;
            grid-template-columns: 1.1fr 0.9fr;
            background: #171717;
            border: 1px solid #2a2a2a;
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.45);
        }

        .intro {
            padding: 48px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            background: linear-gradient(145deg, #3a1900, #111111 70%);
        }

        .brand {
            font-size: 20px;
            font-weight: 700;
            letter-spacing: 1px;
        }

        .brand span {
            color: #ff7a00;
        }

        .intro h1 {
            margin: 0;
            font-size: 58px;
            line-height: 1;
            text-transform: uppercase;
        }

        .intro h1 span {
            display: block;
            color: #ff7a00;
        }

        .intro p {
            max-width: 430px;
            margin: 18px 0 0;
            color: #c4c4c4;
            line-height: 1.6;
        }

        .project-note {
            color: #8f8f8f;
            font-size: 13px;
        }

        .form-side {
            padding: 48px;
            display: flex;
            align-items: center;
            background: #0c0c0c;
        }

        .login-box {
            width: 100%;
        }

        .login-box h2 {
            margin: 0 0 8px;
            font-size: 34px;
        }

        .login-box > p {
            margin: 0 0 28px;
            color: #999999;
        }

        .error {
            margin-bottom: 18px;
            padding: 12px 14px;
            border: 1px solid #693737;
            border-radius: 8px;
            background: #2a1717;
            color: #ffb2b2;
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 18px;
        }

        label {
            display: block;
            margin-bottom: 7px;
            font-size: 13px;
            font-weight: 700;
        }

        input {
            width: 100%;
            padding: 14px;
            border: 1px solid #3a3a3a;
            border-radius: 8px;
            outline: none;
            color: #ffffff;
            background: #181818;
            font-size: 15px;
        }

        input:focus {
            border-color: #ff7a00;
        }

        button {
            width: 100%;
            margin-top: 6px;
            padding: 14px;
            border: 0;
            border-radius: 8px;
            cursor: pointer;
            color: #ffffff;
            background: #f06d00;
            font-size: 15px;
            font-weight: 700;
        }

        button:hover {
            background: #ff7a00;
        }

        .demo {
            margin-top: 20px;
            padding-top: 18px;
            border-top: 1px solid #2a2a2a;
            color: #777777;
            font-size: 12px;
            line-height: 1.6;
        }

        @media (max-width: 760px) {
            .login-page {
                grid-template-columns: 1fr;
            }

            .intro,
            .form-side {
                padding: 32px;
            }

            .intro {
                min-height: 340px;
            }

            .intro h1 {
                font-size: 44px;
            }
        }
    </style>
</head>
<body>

    <main class="login-page">
        <section class="intro">
            <div class="brand">ONE MORE <span>GAME</span> 🏀</div>

            <div>
                <h1>
                    Find Your Run.
                    <span>Own The Court.</span>
                </h1>
                <p>
                    Find pickup basketball games, connect with local players,
                    and get back on the court.
                </p>
            </div>

            <div class="project-note">Capstone Project | Login Page | Omar Elzahri</div>
        </section>

        <section class="form-side">
            <div class="login-box">
                <h2>Game Time.</h2>
                <p>Log in to find your next game.</p>

                <?php if ($error !== ''): ?>
                    <div class="error">
                        <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                    </div>
                <?php endif; ?>

                <form method="post" action="">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input
                            type="text"
                            name="username"
                            id="username"
                            value="<?= htmlspecialchars($_POST['username'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                            autocomplete="username"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <input
                            type="password"
                            name="password"
                            id="password"
                            autocomplete="current-password"
                            required
                        >
                    </div>

                    <button type="submit">Enter The Court</button>
                </form>

                <div class="demo">
                    Sprint review test account:<br>
                    Username: Jesse<br>
                    Password: cool
                </div>
            </div>
        </section>
    </main>

</body>
</html>
