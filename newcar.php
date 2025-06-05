<?php
include("storage.php");

$car_storage = new CarStorage();
$rent_storage = new RentStorage();
$user_storage = new UserStorage();
session_start();

$success = isset($_GET["success"]) ? true : false;

if ($success) {
    $data = [];
    $errors = [];
}

$data = $_GET;
$cars = $car_storage->findAll();

//validáció
$errors = [];
$new_car = [];
//brand
if (!isset($data["brand"])) {
    $errors["brand"] = "Hiányzik az autó márka!";
} else if (trim($data["brand"]) == "") {
    $errors["brand"] = "Hiányzik az autó márka!";
} else if (strlen($data["brand"]) < 3) {
    $errors["brand"] = "Az autó márka minimum 3 karakter hosszú kell, hogy legyen!";
} else {
    $new_car["brand"] = $data["brand"];
}

//model
if (!isset($data["model"])) {
    $errors["model"] = "Hiányzik az autó modelje!";
} else if (trim($data["model"]) == "") {
    $errors["model"] = "Hiányzik az autó modelje!";
} else if (strlen($data["model"]) < 1) {
    $errors["model"] = "Az autó modeljének minimum 1 karakter hosszú kell, hogy legyen!";
} else {
    $new_car["model"] = $data["model"];
}

//year
if (!isset($data["year"])) {
    $errors["year"] = "Hiányzik az autó gyártási éve!";
} else if (trim($data["year"]) == "") {
    $errors["year"] = "Hiányzik az autó gyártási éve!";
} else if (!is_numeric($data["year"])) {
    $errors["year"] = "Az évnek számnak kell lennie!";
} else if ($data["year"] < 1800 || $data["year"] > 2100) {
    $errors["year"] = "Nem megfelelő gyártási év!";
} else {
    $new_car["year"] = $data["year"];
}

//transmission
if (!isset($data["transmission"])) {
    $errors["transmission"] = "Hiányzik az autó váltójának a típusa!";
} else if (trim($data["transmission"]) == "") {
    $errors["transmission"] = "Hiányzik az autó váltójának a típusa!";
} else if ($data["transmission"] != "Manual" && $data["transmission"] != "Automatic") {
    $errors["transmission"] = "Nem megfelelő váltótípus!";
} else {
    $new_car["transmission"] = $data["transmission"];
}

//fuel_type
if (!isset($data["fuel_type"])) {
    $errors["fuel_type"] = "Hiányzik az autó üzemanyag típusa!";
} else if (trim($data["fuel_type"]) == "") {
    $errors["fuel_type"] = "Hiányzik az autó üzemanyag típusa!";
} else if ($data["fuel_type"] != "Petrol" && $data["fuel_type"] != "Diesel" && $data["fuel_type"] != "Electric") {
    $errors["fuel_type"] = "Nem megfelelő üzemanyag típus!";
} else {
    $new_car["fuel_type"] = $data["fuel_type"];
}

//passengers
if (!isset($data["passengers"])) {
    $errors["passengers"] = "Hiányzik az autó férőhelyeinek a száma!";
} else if (trim($data["passengers"]) == "") {
    $errors["passengers"] = "Hiányzik az autó férőhelyeinek a száma!";
} else if (!is_numeric($data["passengers"])) {
    $errors["passengers"] = "A férőhelyek számának egész számnak kell lennie!";
} else if ($data["passengers"] < 1 || $data["passengers"] > 20) {
    $errors["passengers"] = "Nem megfelelő férőhely szám!";
} else {
    $new_car["passengers"] = $data["passengers"];
}

//daily_price_huf
if (!isset($data["daily_price_huf"])) {
    $errors["daily_price_huf"] = "Hiányzik az autó napi ára!";
} else if (trim($data["daily_price_huf"]) == "") {
    $errors["daily_price_huf"] = "Hiányzik az autó napi ára!";
} else if (!is_numeric($data["daily_price_huf"])) {
    $errors["daily_price_huf"] = "Az árnak számnak kell lennie!";
} else if ($data["daily_price_huf"] < 1000 || $data["daily_price_huf"] > 1000000) {
    $errors["daily_price_huf"] = "Nem megfelelő napi ár!";
} else {
    $new_car["daily_price_huf"] = $data["daily_price_huf"];
}

//image
if (!isset($data["image"])) {
    $errors["image"] = "Hiányzik az autó képének a URL-je!";
} else if (trim($data["image"]) == "") {
    $errors["image"] = "Hiányzik az autó képének a URL-je!";
} else {
    $new_car["image"] = $data["image"];
}

if (count($errors) == 0) {
    $car_storage->add($new_car);
    header("Location: newcar.php?success=1");
    exit();
}

if (count($errors) == 0) {
    $car_name = $data["brand"] . " " . $data["model"];
    $car_image = $data["image"];
    $car_passengers = $data["passengers"];
    $car_transmission = $data["transmission"];
    $car_daily_price_huf = $data["daily_price_huf"];
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
    <link rel="stylesheet" href="css/newcar.css" />
    <title>Új autó hozzáadása</title>
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
    <?php if ($success): ?>
        <?php
        if (isset($data["success"])) {
            $errors = [];
        }
        ?>
        <h1>Sikeresen hozzáadtad az autót!</h1>
        <button class="main_page_button" onclick="location.href='mainpage.php'">
            Főoldal
        </button>
    <?php endif; ?>
    <h1>Új autó hozzáadása</h1>
    <form action="" method="get" novalidate>
        <label for="brand">Márka:</label>
        <input type="text" id="brand" name="brand" placeholder="Honda" value="<?= $data["brand"] ?? "" ?>">
        <span id="error"><?= $errors["brand"] ?? "" ?></span><br> <br>

        <label for="model">Modell:</label>
        <input type="text" id="model" name="model" placeholder="Civic" value="<?= $data["model"] ?? "" ?>">
        <span id="error"><?= $errors["model"] ?? "" ?></span><br> <br>


        <label for="year">Gyártási év:</label>
        <input type="number" id="year" name="year" placeholder="2019" value="<?= $data["year"] ?? "" ?>">
        <span id="error"><?= $errors["year"] ?? "" ?></span><br> <br>

        <label for="transmission">Váltó:</label>
        <select id="transmission" name="transmission">
            <option value="" disabled <?= !isset($data["transmission"]) ? "selected" : "" ?>>Válasszd ki a váltótípust
            </option>
            <option value="Manual" <?= (isset($data["transmission"]) && $data["transmission"] == "Manual") ? "selected" : "" ?>>Manuális</option>
            <option value="Automatic" <?= (isset($data["transmission"]) && $data["transmission"] == "Automatic") ? "selected" : "" ?>>Automata</option>
        </select>
        <span id="error"><?= $errors["transmission"] ?? "" ?></span><br> <br>

        <label for="fuel_type">Üzemanyag:</label>
        <select id="fuel_type" name="fuel_type">
            <option value="" disabled <?= !isset($data["fuel_type"]) ? "selected" : "" ?>>Válasszd ki az üzemanyagtípust
            </option>
            <option value="Petrol" <?= (isset($data["fuel_type"]) && $data["fuel_type"] == "Petrol") ? "selected" : "" ?>>
                Benzin</option>
            <option value="Diesel" <?= (isset($data["fuel_type"]) && $data["fuel_type"] == "Diesel") ? "selected" : "" ?>>
                Dízel</option>
            <option value="Electric" <?= (isset($data["fuel_type"]) && $data["fuel_type"] == "Electric") ? "selected" : "" ?>>Elektromos</option>
        </select>
        <span id="error"><?= $errors["fuel_type"] ?? "" ?></span><br> <br>

        <label for="passengers">Férőhelyek száma:</label>
        <input type="number" id="passengers" name="passengers" placeholder="5" value="<?= $data["passengers"] ?? "" ?>">
        <span id="error"><?= $errors["passengers"] ?? "" ?></span><br> <br>

        <label for="daily_price_huf">Napi ár (Ft):</label>
        <input type="number" id="daily_price_huf" name="daily_price_huf" placeholder="16250"
            value="<?= $data["daily_price_huf"] ?? "" ?>">
        <span id="error"><?= $errors["daily_price_huf"] ?? "" ?></span><br> <br>
        <label for="image">Kép URL:</label>
        <input type="url" id="image" name="image" placeholder="https://example.com/image.jpg"
            value="<?= $data["image"] ?? "" ?>">
        <span id="error"><?= $errors["image"] ?? "" ?></span><br> <br>
        <button type="submit">Hozzáadás</button>
    </form>
</body>

</html>