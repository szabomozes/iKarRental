<?php
include("storage.php");

$car_storage = new CarStorage();
$rent_storage = new RentStorage();
$user_storage = new UserStorage();
session_start();

$cars = $car_storage->findAll();
$id = $_GET['id'];


//üzemanyag és váltó típusok átalakítása
function fuel_type($car)
{
    $fuel_type = " ";
    if ($car["fuel_type"] == "Petrol") {
        $fuel_type = "Benzin";
    } else if ($car["fuel_type"] == "Diesel") {
        $fuel_type = "Dízel";
    } else if ($car["fuel_type"] == "Electric") {
        $fuel_type = "Elektromos";
    }
    return $fuel_type;
}
function transmission_type($car)
{
    $transmission = " ";
    if ($car["transmission"] == "Manual") {
        $transmission = "Manuális";
    } else if ($car["transmission"] == "Automatic") {
        $transmission = "Automata";
    }
    return $transmission;
}

//autó adatai
$car = $car_storage->findById($id);
$car_name = $car["brand"] . " " . $car["model"];
$image = $car["image"];
$fuel = fuel_type($car);
$transmission = transmission_type($car);
$year = $car["year"];
$seats = $car["passengers"];
$price = $car["daily_price_huf"];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/header.css" />
    <link rel="stylesheet" href="css/carpage.css" />
    <title><?= ($_GET["id"] == null) ? "Autó foglalás" : $car_name . " lefoglalása" ?></title>
</head>

<body>
    <header>
        <div>
            <h3 id="logo-text" onclick="location.href='mainpage.php'">iKarRental</h3>
        </div>
        <div>
            <?php if (isset($_SESSION["loged_in"]) && $_SESSION["loged_in"] == true): ?>
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

    <div class="car-more-info">
        <div class="car-image">
            <img src="<?= $image ?>" alt="Car Image">
        </div>
        <div class="car-details">
            <h2><?= $car_name ?></h2>
            <p><strong>Üzemanyag:</strong> <?= $fuel ?></p>
            <p><strong>Váltó:</strong> <?= $transmission ?></p>
            <p><strong>Gyártási év:</strong> <?= $year ?></p>
            <p><strong>Férőhelyek száma:</strong> <?= $seats ?></p>
            <p class="price"><strong><?= $price ?> Ft/nap</strong></p>
        </div>
    </div>

    </div>

    <h3 id="choose-date" style="text-align: center;">Dátum kiválasztása</h3>
    <form action="notification.php" method="post" novalidate>
        <div class="filter-box">
            <div class="filter-item">
                <input type="date" id="start_date" name="start_date" placeholder="2025.01.01" required>
                <label for="start_date">Kezdő dátum</label>
            </div>
            <div class="filter-item">
                <input type="date" id="end_date" name="end_date" placeholder="2025.01.05">
                <label for="end_date">Vég dátum</label>
            </div>
            <div class="filter-item">
                <input type="text" id="car_id" name="car_id" value="<?= $_GET["id"] ?>" hidden>
            </div>
            <div class="filter-item">
                <button type="submit">Lefoglalom</button>
            </div>
        </div>
</body>

</html>