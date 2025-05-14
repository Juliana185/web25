<?php
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

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'login':
        $login = $_POST['login'] ?? '';
        $password = $_POST['password'] ?? '';

        $stmt = $mysqli->prepare("SELECT id, password_hash FROM users WHERE login = ?");
        $stmt->bind_param("s", $login);
        $stmt->execute();
        $result = $stmt->get_result();

        if (($row = $result->fetch_assoc()) && password_verify($password, $row['password_hash'])) {
            $_SESSION['user_id'] = $row['id'];
            header('Location: index.php');
            exit();
        } else {
            $_SESSION['login_error'] = 'Неверный логин или пароль';
            header('Location: index.php');
            exit();
        }
        break;

    case 'register':
        $formData = $_POST;
        $errors = [];

        $name = trim($formData['name'] ?? '');
        $phone = trim($formData['phone'] ?? '');
        $email = trim($formData['email'] ?? '');
        $birthdate = $formData['birthdate'] ?? '';

        if (empty($name) || strlen($name) > 150) $errors['name'] = 'Неверное имя';
        if (!preg_match('/^\+7\d{10}$/', $phone)) $errors['phone'] = 'Формат: +7XXXXXXXXXX';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Некорректный email';
        if (strtotime($birthdate) > strtotime('-10 years')) $errors['birthdate'] = 'Минимальный возраст: 10 лет';

        setcookie('form_data', json_encode($formData), time() + 31536000, '/');
        setcookie('form_errors', json_encode($errors), time() + 3600, '/');

        if (empty($errors)) {
            $login = uniqid('user_', true);
            $rawPassword = bin2hex(random_bytes(8));
            $passwordHash = password_hash($rawPassword, PASSWORD_DEFAULT);

            $stmt = $mysqli->prepare("INSERT INTO users (name, phone, email, birthdate, login, password_hash) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssss", $name, $phone, $email, $birthdate, $login, $passwordHash);
            $stmt->execute();

            $_SESSION['show_credentials'] = true;
            $_SESSION['temp_login'] = $login;
            $_SESSION['temp_password'] = $rawPassword;

            setcookie('form_errors', '', time() - 3600, '/');
            header('Location: index.php');
            exit();
        } else {
            header('Location: index.php?register=1');
            exit();
        }
        break;

    case 'logout':
        session_destroy();
        header('Location: index.php');
        break;

    case 'clear_form':
        setcookie('form_data', '', time() - 3600, '/');
        setcookie('form_errors', '', time() - 3600, '/');
        header('Location: index.php?register=1');
        break;

    default:
        header('Location: index.php');
}
?>
