<?php
// Логин: user_68243c137df317.97692946  Пароль: 6df092e81725e440
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = 'sql304.infinityfree.com';
$dbname = 'if0_38574572_user';
$username = 'if0_38574572';
$password = 'JuliaDb2025';

$mysqli = new mysqli($host, $username, $password, $dbname);
if ($mysqli->connect_error) {
    die("Ошибка подключения: " . $mysqli->connect_error);
}

$formData = json_decode($_COOKIE['form_data'] ?? '{}', true);
$errors = json_decode($_COOKIE['form_errors'] ?? '{}', true);

$userData = [];
if (isset($_SESSION['user_id'])) {
    $stmt = $mysqli->prepare("SELECT name, phone, email, birthdate FROM users WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $userData = $result->fetch_assoc();
}
?>


<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <title>Форма регистрации</title>
</head>
<body>
    <?php if (isset($_SESSION['show_credentials'])):
        unset($_SESSION['show_credentials']);?>
	<div class="credentials-box">
            <h2>Данные для входа</h2>
            <p>Логин: <?= htmlspecialchars($_SESSION['temp_login']) ?></p>
            <p>Пароль: <?= htmlspecialchars($_SESSION['temp_password']) ?></p>
	    <p class="warning">* Сохраните пароль! </p>
            <p><a href="index.php" class="login-button">На главную</a></p>
        </div>
    <?php
        unset($_SESSION['registration_data']);
        exit();
    ?>
    <?php endif; ?>

    <?php if (!isset($_SESSION['user_id']) && !isset($_GET['register'])): ?>
        <form method="POST" action="submit.php?action=login">
            <h2>Вход</h2>
            <input type="text" name="login" placeholder="Логин" required>
            <input type="password" name="password" placeholder="Пароль" required>
            <button type="submit">Войти</button>
            <a href="index.php?register=1">Регистрация</a>
        </form>
    <?php endif; ?>

    <?php if (isset($_SESSION['login_error'])): ?>
        <div class="error-message">
            <?= htmlspecialchars($_SESSION['login_error']) ?>
            <?php unset($_SESSION['login_error']); ?>
        </div>
    <?php endif; ?>

    <?php if (!isset($_SESSION['user_id']) && isset($_GET['register'])): ?>
        <form method="POST" action="submit.php?action=register">
            <h2>Регистрация</h2>

            <div class="form-group <?= isset($errors['name']) ? 'error' : '' ?>">
                <label>ФИО:
                    <input type="text" name="name"
                        value="<?= htmlspecialchars($formData['name'] ?? '') ?>"
                        required>
                </label>
                <?php if (isset($errors['name'])): ?>
                    <div class="error-message"><?= htmlspecialchars($errors['name']) ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group <?= isset($errors['phone']) ? 'error' : '' ?>">
                <label>Телефон:
                    <input type="tel" name="phone"
                        value="<?= htmlspecialchars($formData['phone'] ?? '') ?>"
                        pattern="\+7\d{10}"
                        title="Формат: +7XXXXXXXXXX"
                        required>
                </label>
                <?php if (isset($errors['phone'])): ?>
                   <div class="error-message"><?= htmlspecialchars($errors['phone']) ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group <?= isset($errors['email']) ? 'error' : '' ?>">
                <label>Email:
                    <input type="email" name="email"
                        value="<?= htmlspecialchars($formData['email'] ?? '') ?>"
                        required>
                </label>
                <?php if (isset($errors['email'])): ?>
                    <div class="error-message"><?= htmlspecialchars($errors['email']) ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group <?= isset($errors['birthdate']) ? 'error' : '' ?>">
                <label>Дата рождения:
                    <input type="date" name="birthdate"
                        value="<?= htmlspecialchars($formData['birthdate'] ?? '') ?>"
                        max="<?= date('Y-m-d', strtotime('-10 years')) ?>"
                        required>
                </label>
                <?php if (isset($errors['birthdate'])): ?>
                    <div class="error-message"><?= htmlspecialchars($errors['birthdate']) ?></div>
                <?php endif; ?>
            </div>

            <button type="submit">Зарегистрироваться</button>
	    <a href="submit.php?action=clear_form">Очистить форму</a>
        </form>
    <?php endif; ?>

    <?php if (isset($_SESSION['user_id'])): ?>

        <?php if (isset($_SESSION['update_success'])): ?>
            <div class="success"><?= $_SESSION['update_success'] ?></div>
            <?php unset($_SESSION['update_success']); ?>
        <?php endif; ?>

        <form method="POST" action="submit.php?action=update">
        <h2>Редактирование данных</h2>

        <div class="<?= isset($_SESSION['update_errors']['name']) ? 'error' : '' ?>">
            <label>ФИО:
                <input
                    type="text"
                    name="name"
                    value="<?= htmlspecialchars($userData['name'] ?? '') ?>"
                    required
                >
            </label>
        </div>

        <div class="<?= isset($_SESSION['update_errors']['phone']) ? 'error' : '' ?>">
            <label>Телефон:
                <input
                    type="tel"
                    name="phone"
                    value="<?= htmlspecialchars($userData['phone'] ?? '') ?>"
                    pattern="\+7\d{10}"
                    required
                >
            </label>
        </div>

        <div class="<?= isset($_SESSION['update_errors']['email']) ? 'error' : '' ?>">
            <label>Email:
                <input
                    type="email"
                    name="email"
                    value="<?= htmlspecialchars($userData['email'] ?? '') ?>"
                    required
                >
            </label>
        </div>

        <div class="<?= isset($_SESSION['update_errors']['birthdate']) ? 'error' : '' ?>">
            <label>Дата рождения:
                <input
                    type="date"
                    name="birthdate"
                    value="<?= htmlspecialchars($userData['birthdate'] ?? '') ?>"
                    max="<?= date('Y-m-d', strtotime('-10 years')) ?>"
                    required
                >
            </label>
        </div>


        <button type="submit">Сохранить</button>
        <a href="submit.php?action=logout">Выйти</a>
    </form>

        <?php
        unset($_SESSION['update_errors'], $_SESSION['form_data']); ?>
    <?php endif; ?>

</body>
</html>
