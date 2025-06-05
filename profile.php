<?php
include("storage.php");

$car_storage = new CarStorage();
$rent_storage = new RentStorage();
$user_storage = new UserStorage();
session_start();

$user = $user_storage->findById($_SESSION["user_id"]);

$cars = $car_storage->findAll();
$my_rents = $rent_storage->findMany(function ($rent) use ($user) {
    return $rent["user_id"] == $user["id"];
});

//a foglalásokhoz tartozó autók
foreach ($my_rents as $rent) {
    foreach ($cars as $car) {
        if ($rent["car_id"] == $car["id"]) {
            $my_rented_cars[] = $car;
        }
    }
}

//admin kezelése
$user_id = $_SESSION["user_id"] ?? null;
$user = $user_storage->findById($user_id);
$admin = false;
if ($user["email"] == "admin@ikarrental.hu") {
    $admin = true;
} else {
    $admin = false;
}

//admin összes foglalás
$all_rents = $rent_storage->findAll();

//foglalás törlése admin felületen

if (isset($_POST["delete_rent_id"])) {
    $rent_id = $_POST["delete_rent_id"];
    $rent_storage->delete($rent_id);
    header("Location: profile.php");
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/header.css" />
    <link rel="stylesheet" href="css/profile.css" />
    <title>Profil</title>
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
    <div class="profile-container">
        <h1 id="title">Profilom</h1>
        <h2 class="username">Bejelentkezve, mint <?= $user["fullname"] ?></h2>
        <h3 id="my-rents">Foglalások</h3>
    </div>
    <?php if (!$admin && $my_rents): ?>
        <?php foreach ($my_rented_cars as $car): ?>
            <div class="car-card">
                <img src="<?= $car["image"] ?>" alt="<?= $car_name ?>">
                <?php $car_name = $car["brand"] . " " . $car["model"]; ?>
                <p class="car-title" onclick="location.href='carpage.php?id=<?= $car["id"] ?>'"><b><?= $car_name ?></b></p>
                <p><?= $car["passengers"] ?> férőhely - <?= ($car["transmission"] == "Automatic") ? "automata" : "manuális" ?>
                </p>
                <p class="car-price"><?= $car["daily_price_huf"] ?> Ft /nap</p>

                <?php foreach ($my_rents as $rent): ?>
                    <?php if ($rent["car_id"] == $car["id"]): ?>
                        <p class="rent-date"><?= $rent["from"] ?> - <?= $rent["to"] ?></p>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
    <?php if ($admin): ?>
        <?php foreach ($all_rents as $rent): ?>
            <div class="admin-all-rents">
                <?php
                $car = $car_storage->findById($rent["car_id"]);
                $car_image = $car["image"];
                $car_name = $car["brand"] . " " . $car["model"];
                ?>
                <img src="<?= $car_image ?>" alt="<?= $car_name ?>">

                <p class="car-title" onclick="location.href='carpage.php?id=<?= $car["id"] ?>'"><b><?= $car_name ?></b></p>
                <p><?= $car["passengers"] ?> férőhely - <?= ($car["transmission"] == "Automatic") ? "automata" : "manuális" ?>
                </p>
                <p class="car-price"><?= $car["daily_price_huf"] ?> Ft /nap</p>
                <p class="rent-date"><?= $rent["from"] ?> - <?= $rent["to"] ?></p>
                <form action=" " method="post" novalidate>
                    <input type="hidden" name="delete_rent_id" value="<?= $rent["id"] ?>">
                    <button type="submit" name="delete" value="delete" id="delete">Törlés</button>
                </form>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</body>

</html>