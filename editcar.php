<?php
include("storage.php");

$car_storage = new CarStorage();
$rent_storage = new RentStorage();
$user_storage = new UserStorage();
session_start();

$car_id_get = $_GET["edit_id"];

$data = $_GET;
$cars = $car_storage->findAll();
$car = $car_storage->findById($car_id_get);

//validáció
$errors = [];
$edited_car = [];
//brand
if (!isset($data["brand"])) {
    $errors["brand"] = "Hiányzik az autó márka!";
} else if (trim($data["brand"]) == "") {
    $errors["brand"] = "Hiányzik az autó márka!";
} else if (strlen($data["brand"]) < 3) {
    $errors["brand"] = "Az autó márka minimum 3 karakter hosszú kell, hogy legyen!";
} else {
    $edited_car["brand"] = $data["brand"];
}

//model
if (!isset($data["model"])) {
    $errors["model"] = "Hiányzik az autó modelje!";
} else if (trim($data["model"]) == "") {
    $errors["model"] = "Hiányzik az autó modelje!";
} else if (strlen($data["model"]) < 1) {
    $errors["model"] = "Az autó modeljének minimum 1 karakter hosszú kell, hogy legyen!";
} else {
    $edited_car["model"] = $data["model"];
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
    $edited_car["year"] = $data["year"];
}

//transmission
if (!isset($data["transmission"])) {
    $errors["transmission"] = "Hiányzik az autó váltójának a típusa!";
} else if (trim($data["transmission"]) == "") {
    $errors["transmission"] = "Hiányzik az autó váltójának a típusa!";
} else if ($data["transmission"] != "Manual" && $data["transmission"] != "Automatic") {
    $errors["transmission"] = "Nem megfelelő váltótípus!";
} else {
    $edited_car["transmission"] = $data["transmission"];
}

//fuel_type
if (!isset($data["fuel_type"])) {
    $errors["fuel_type"] = "Hiányzik az autó üzemanyag típusa!";
} else if (trim($data["fuel_type"]) == "") {
    $errors["fuel_type"] = "Hiányzik az autó üzemanyag típusa!";
} else if ($data["fuel_type"] != "Petrol" && $data["fuel_type"] != "Diesel" && $data["fuel_type"] != "Electric") {
    $errors["fuel_type"] = "Nem megfelelő üzemanyag típus!";
} else {
    $edited_car["fuel_type"] = $data["fuel_type"];
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
    $edited_car["passengers"] = $data["passengers"];
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
    $edited_car["daily_price_huf"] = $data["daily_price_huf"];
}

//image
if (!isset($data["image"])) {
    $errors["image"] = "Hiányzik az autó képének a URL-je!";
} else if (trim($data["image"]) == "") {
    $errors["image"] = "Hiányzik az autó képének a URL-je!";
} else {
    $edited_car["image"] = $data["image"];
}
$edited_car["id"] = $data["edit_id"];

if (count($errors) == 0) {
    $id_this = $data["edit_id"];
    unset($data["edit_id"]);
    $car_storage->update($car_id_get, $edited_car);
    header("Location: editcar.php?edit_id=" . $edited_car["id"] . "&successful=1");
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/header.css" />
    <link rel="stylesheet" href="css/editcar.css" />
    <title>Autó módosítása</title>
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

    <?php if (isset($_GET["successful"])): ?>
        <h1>Sikeres módosítás!</h1>
    <?php endif; ?>
    <h1>Autó adatainak szerkesztése</h1>
    <form action="" method="get" novalidate>
        <label for="brand">Márka:</label>
        <input type="text" id="brand" name="brand" value="<?= $car["brand"] ?>">
        <?php if (isset($_GET["edit_id"]) && isset($_GET["id"])): ?>
            <span class="error"><?= $errors["brand"] ?? "" ?></span>
        <?php endif; ?>
        <br><br>
        <label for="model">Modell:</label>
        <input type="text" id="model" name="model" value="<?= $car["model"] ?>">
        <?php if (isset($_GET["edit_id"]) && isset($_GET["id"])): ?>
            <span id="error"><?= $errors["model"] ?? "" ?></span>
        <?php endif; ?>
        <br><br>
        <label for="year">Gyártási év:</label>
        <input type="number" id="year" name="year" value="<?= $car["year"] ?>">
        <?php if (isset($_GET["edit_id"]) && isset($_GET["id"])): ?>
            <span id="error"><?= $errors["year"] ?? "" ?></span>
        <?php endif; ?>
        <br><br>
        <label for="transmission">Váltó:</label>
        <select id="transmission" name="transmission">
            <option value="" disabled>Válasszd ki a váltótípust</option>
            <option value="Manual" <?= $car["transmission"] == "Manual" ? "selected" : "" ?>>Manuális</option>
            <option value="Automatic" <?= $car["transmission"] == "Automatic" ? "selected" : "" ?>>Automata</option>
        </select>
        <?php if (isset($_GET["edit_id"]) && isset($_GET["id"])): ?>
            <span id="error"><?= $errors["transmission"] ?? "" ?></span>
        <?php endif; ?>
        <br><br>
        <label for="fuel_type">Üzemanyag:</label>
        <select id="fuel_type" name="fuel_type">
            <option value="" disabled>Válasszd ki az üzemanyagtípust</option>
            <option value="Petrol" <?= $car["fuel_type"] == "Petrol" ? "selected" : "" ?>>Benzin</option>
            <option value="Diesel" <?= $car["fuel_type"] == "Diesel" ? "selected" : "" ?>>Dízel</option>
            <option value="Electric" <?= $car["fuel_type"] == "Electric" ? "selected" : "" ?>>Elektromos</option>
        </select>
        <?php if (isset($_GET["edit_id"]) && isset($_GET["id"])): ?>
            <span id="error"><?= $errors["fuel_type"] ?? "" ?></span>
        <?php endif; ?>
        <br><br>
        <label for="passengers">Férőhelyek száma:</label>
        <input type="number" id="passengers" name="passengers" value="<?= $car["passengers"] ?>">
        <?php if (isset($_GET["edit_id"]) && isset($_GET["id"])): ?>
            <span id="error"><?= $errors["passengers"] ?? "" ?></span>
        <?php endif; ?>
        <br><br>
        <label for="daily_price_huf">Napi ár (Ft):</label>
        <input type="number" id="daily_price_huf" name="daily_price_huf" value="<?= $car["daily_price_huf"] ?>">
        <?php if (isset($_GET["edit_id"]) && isset($_GET["id"])): ?>
            <span id="error"><?= $errors["daily_price_huf"] ?? "" ?></span>
        <?php endif; ?>
        <br><br>
        <img src="<?= $car["image"] ?>" id="actual_pic"><br>
        <label for="image">Kép URL:</label>
        <input type="url" id="image" name="image" value="<?= $car["image"] ?>">
        <input type="text" name="edit_id" value="<?= $_GET["edit_id"] ?>" hidden>
        <input type="text" name="id" value="<?= $_GET["edit_id"] ?>" hidden>
        <?php if (isset($_GET["edit_id"]) && isset($_GET["id"])): ?>
            <span id="error"><?= $errors["image"] ?? "" ?></span>
        <?php endif; ?>
        <br><br>
        <button type="submit">Módosítás</button>
    </form>

    <h1>Autó adatinak előnézete:</h1>
    <div class="car-card_new">
        <img src="<?= $car["image"] ?>"><br>
        <?php $car_name = $car["brand"] . " " . $car["model"]; ?>
        <b><?= $car_name ?></b><br>
        <strong>Márka:</strong> <?= $car["brand"] ?><br>
        <strong>Modell:</strong> <?= $car["model"] ?><br>
        <strong>Gyártási év:</strong> <?= $car["year"] ?><br>
        <strong>Váltó:</strong> <?= ($car["transmission"] == "Automatic") ? "automata" : "manuális" ?><br>
        <strong>Üzemanyag:</strong> <?= $car["fuel_type"] ?><br>
        <strong>Férőhelyek száma:</strong> <?= $car["passengers"] ?><br>
        <strong>Napi ár:</strong> <?= $car["daily_price_huf"] ?> Ft/nap<br>
        <button class="main_page_button" onclick="location.href='carpage.php?id=<?= $edited_car["id"] ?>'">
            Az autó oldalára
        </button>
    </div>
</body>

</html>