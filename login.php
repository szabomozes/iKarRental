<?php
include("storage.php");

$car_storage = new CarStorage();
$rent_storage = new RentStorage();
$user_storage = new UserStorage();
session_start();

$_SESSION["loged_in"] = null;
$_SESSION["user_id"] = null;

//felhasználó keresése e-mail alapján
function findUserByEmail($users, $login_email)
{
    foreach ($users as $u) {
        if ($u["email"] == $login_email) {
            return $u;
        }
    }
    return null;
}

//jelszó ellenőrzése
function checkPassword($user, $login_password)
{
    if ($user && $user["password"] == $login_password) {
        $_SESSION["loged_in"] = true;
        $_SESSION["user_id"] = $user["id"];
        return "Sikeres belépés";
    } else {
        $_SESSION["loged_in"] = false;
        $_SESSION["user_id"] = null;
        return "Sikertelen belépés";
    }
}

//felhasználók lekérése
$users = $user_storage->findAll();

//validáció
$data = $_POST;
$errors = [];

//email
if (!isset($data["email"])) {
    $errors["email"] = "Nem adtál meg e-mail címet!";
} else if (trim($data["email"]) == "") {
    $errors["email"] = "Nem adtál meg e-mail címet!";
} else if (!filter_var($data["email"], FILTER_VALIDATE_EMAIL)) {
    $errors["email"] = "Nem megfelelő az e-mail cím formátum!";
} else if (!findUserByEmail($users, $data["email"])) {
    $errors["email"] = "Ehhez az e-mail címhez nem tartozik fiók!";
} else {
    $login_email = $data["email"];
    $user = findUserByEmail($users, $login_email);
}
if (isset($data["email"])) {
    $user = findUserByEmail($users, $data["email"]);
}
//password
if (!isset($data["password"])) {
    $errors["password"] = "Nem adtál meg jelszót!";
} else if (trim($data["password"]) == "") {
    $errors["password"] = "Nem adtál meg jelszót!";
} else if ((isset($data["email"])) && checkPassword($user, $data["password"]) != "Sikeres belépés") {
    $errors["password"] = "Hibás jelszó!";
} else {
    $login_password = $data["password"];
}

//hogy az oldal megnyitásakor ne legyenek azonnal hibák
if (count($data) == 0) {
    $errors = [];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/header.css" />
    <link rel="stylesheet" href="css/login.css" />
    <title>Bejelentkezés</title>
</head>

<body>
    <header>
        <div>
            <h3 id="logo-text" onclick="location.href='mainpage.php'">iKarRental</h3>
        </div>
        <div>
            <?php if ($_SESSION["loged_in"] == true): ?>
                <div class="profile-info">
                    <img id="profile-pic" src="pic/profilepic.png" onclick="location.href='profile.php'">
                    <?php
                    if ($_SESSION["user_id"]) {
                        $user = $user_storage->findById($_SESSION["user_id"]);
                        $user_name = $user["fullname"];
                    }
                    ?>
                    <?php if ($_SESSION["user_id"]): ?>
                        <span id="user-name" onclick="location.href='profile.php'"><strong><?= $user_name ?></strong></span>
                    <?php endif; ?>
                    <button id="logout-button" onclick="location.href='logout.php'">Kijelentkezés</button>
                </div>
            <?php else: ?>
                <button id="login-button" onclick="location.href='login.php'">Bejelentkezés</button>
                <button id="register-button" onclick="location.href='register.php'">Regisztráció</button>
            <?php endif; ?>
        </div>
    </header>
    <main>

        <?php if (!$_SESSION["loged_in"]): ?>
            <h1>Belépés</h1>
            <form action=" " method="post" novalidate>
                <label for="email">E-mail cím</label><br>
                <input type="email" id="email" name="email" placeholder="nagyadam@gmail.com">
                <span id="error"><?= $errors["email"] ?? "" ?></span><br> <br>

                <label for="password">Jelszó</label><br>
                <input type="password" id="password" name="password" placeholder="********">
                <?php if (!isset($errors["email"])): ?>
                    <span id="error"><?= $errors["password"] ?? "" ?></span><br> <br>
                <?php endif; ?>
                <button type="submit">Belépés</button>
            </form>
        <?php endif; ?>
    </main>
    <?php if (count($errors) == 0 && count($data) > 0): ?>
        <h2>A bejelentkezés SIKERES volt.</h2>
        <h3>Reméljük sikerül megtalálnod a megfelelő autót!</h3>
        <button onclick="location.href='mainpage.php'" id="gotomain">Főoldal</button>
    <?php endif; ?>
</body>

</html>