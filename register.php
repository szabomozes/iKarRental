<?php
include("storage.php");

$car_storage = new CarStorage();
$rent_storage = new RentStorage();
$user_storage = new UserStorage();
session_start();

$data = $_POST;

//validáció
$errors = [];

//foglalt e-mail cím
function existsEmail($user_storage, $data)
{
    $users = $user_storage->findAll();
    foreach ($users as $u) {
        if ($u["email"] === $data["email"]) {
            return true;
        }
    }
    return false;
}

//fullname
if (!isset($data["fullname"])) {
    $errors["fullname"] = "Kérem adja meg a teljes nevét!";
} else if (trim($data["fullname"]) === "") {
    $errors["fullname"] = "Kérem adja meg a teljes nevét!";
} else if (strlen($data["fullname"]) < 5) {
    $errors["fullname"] = "A névnek legalább 5 karakter hosszúnak kell lennie!";
} else if (count(explode(' ', trim($data["fullname"]))) < 2) {
    $errors["fullname"] = "Kérem a vezetéknevet és a keresztnevet is adja meg!";
} else {
    $user["fullname"] = $data["fullname"];
}

//email
if (!isset($data["email"])) {
    $errors["email"] = "Nem adtál meg e-mail címet!";
} else if (trim($data["email"]) === "") {
    $errors["email"] = "Nem adtál meg e-mail címet!";
} else if (!filter_var($data["email"], FILTER_VALIDATE_EMAIL)) {
    $errors["email"] = "Nem megfelelő e-mail cím formátum!";
} else if (isset($data["email"]) && existsEmail($user_storage, $data)) {
    $errors["email"] = "Ezzel az e-mail címmel korábban már regisztráltak!";
} else {
    $user["email"] = $data["email"];
}

//password
if (!isset($data["password"])) {
    $errors["password"] = "Nem adtál meg jelszót!";
} else if (trim($data["password"]) === "") {
    $errors["password"] = "Nem adtál meg jelszót!";
} else if (strlen($data["password"]) < 5) {
    $errors["password"] = "A jelszónak legalább 5 karakter hosszúnak kell lennie!";
} else {
    $user["password"] = $data["password"];
}

//confirm_password
if (!isset($data["confirm_password"])) {
    $errors["confirm_password"] = "Nem adtad meg a jelszót mégegyszer!";
} else if (trim($data["confirm_password"]) === "") {
    $errors["confirm_password"] = "Nem adtad meg a jelszót mégegyszer!";
} else if ($data["password"] !== $data["confirm_password"]) {
    $errors["confirm_password"] = "A két jelszó nem egyezik!";
}

if (count($errors) === 0) {
    $user_storage->add($user);
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
    <link rel="stylesheet" href="css/register.css" />

    <title>Regisztráció</title>
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

    <h1>Regisztráció</h1>
    <main>
        <form action=" " method="post" novalidate>
            <label for="fullname">Teljes név</label><br>
            <input type="text" id="fullname" name="fullname" placeholder="Nagy Ádám">
            <span id="error"><?= $errors["fullname"] ?? "" ?></span><br> <br>
            <label for="email">E-mail cím</label><br>
            <input type="email" id="email" name="email" placeholder="nagyadam@gmail.com">
            <span id="error"><?= $errors["email"] ?? "" ?></span><br> <br>
            <label for="password">Jelszó</label><br>
            <input type="password" id="password" name="password" placeholder="********">
            <span id="error"><?= $errors["password"] ?? "" ?></span><br> <br>
            <label for="confirm_password">Jelszó mégyszer</label><br>
            <input type="password" id="confirm_password" name="confirm_password" placeholder="********">
            <span id="error"><?= $errors["confirm_password"] ?? "" ?></span><br> <br>
            <button type="submit">Regisztráció</button>
        </form>
    </main>
    <?php if (count($errors) == 0 && count($data) > 0): ?>
        <h2>A regisztráció SIKERES volt.</h2>
        <h3>Kérem jelentkezzen be!</h3>
    <?php endif; ?>
</body>

</html>