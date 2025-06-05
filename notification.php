<?php
include("storage.php");

$car_storage = new CarStorage();
$rent_storage = new RentStorage();
$user_storage = new UserStorage();
session_start();
if (!isset($_SESSION["loged_in"])) {
    $_SESSION["loged_in"] = false;
}

//hiányzó adatok
$post = $_POST;
$errors = [];
if (!isset($post["start_date"])) {
    $errors[] = "hiányzó kezdő dátum";
}
if (trim($post["start_date"]) == "") {
    $errors[] = "hiányzó kezdő dátum";
}

if (!isset($post["end_date"])) {
    $errors[] = "hiányzó befejező dátum";
}
if (trim($post["end_date"]) == "") {
    $errors[] = "hiányzó befejező dátum";
}
if (empty($errors)) {
    $start_date = new DateTime($post["start_date"]);
    $end_date = new DateTime($post["end_date"]);
    if ($start_date > $end_date) {
        $errors[] = "A kezdő dátum nem lehet későbbi, mint a befejező dátum";
    }
}

//időintervallum ellenőrzése
$all_cars = $car_storage->findAll();
$all_rent = $rent_storage->findAll();
$filter_values["start_date"] = $post["start_date"];
$filter_values["end_date"] = $post["end_date"];
function filter_cars_by_date($filter_values, $all_cars, $all_rent)
{
    if (!empty($filter_values["start_date"]) && !empty($filter_values["end_date"])) {
        $from_date = new DateTime($filter_values["start_date"]);
        $to_date = new DateTime($filter_values["end_date"]);
    } else {
        $from_date = null;
        $to_date = null;
    }

    foreach ($all_rent as $rent) {
        $rent_start_date = new DateTime($rent["from"]);
        $rent_end_date = new DateTime($rent["to"]);

        if ($from_date <= $rent_end_date && $to_date >= $rent_start_date) {
            $car_id = $rent["car_id"];
            $all_cars = array_filter($all_cars, function ($car) use ($car_id) {
                return $car["id"] != $car_id;
            });
        }
    }
    return $all_cars;
}

//benne van-e a kiválasztott autó a szabad időpontok között
$rentable = false;
$rentable_cars = filter_cars_by_date($filter_values, $all_cars, $all_rent);
foreach ($rentable_cars as $car) {
    if ($car["id"] == $post["car_id"]) {
        $rentable = true;
    }
}

//új foglalás 
if ($rentable == true && (count($errors) == 0)) {
    $new_rent["from"] = $post["start_date"];
    $new_rent["to"] = $post["end_date"];
    $new_rent["user_id"] = $_SESSION["user_id"];
    $new_rent["car_id"] = $post["car_id"];

    $start_date = new DateTime($post["start_date"]);
    $end_date = new DateTime($post["end_date"]);
    $interval = $start_date->diff($end_date);
    $sum_of_days = $interval->days;
    $rent_storage->add($new_rent);
}

//autó adatai
if ($rentable == true && isset($sum_of_days)) {
    $price = $car["daily_price_huf"];
    $sum = $price * ($sum_of_days + 1);
}
$car_name = $car["brand"] . " " . $car["model"];
$from = str_replace('-', '.', $post["start_date"]);
$to = str_replace('-', '.', $post["end_date"]);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/header.css" />
    <link rel="stylesheet" href="css/notification.css" />
    <title>Foglalás állapota</title>
</head>

<body>
    <header>
        <div>
            <h3 id="logo-text" onclick="location.href='mainpage.php'">iKarRental</h3>
        </div>
        <div>
            <?php if ((isset($_SESSION["loged_in"])) && $_SESSION["loged_in"] == true): ?>
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

    <?php if (!(isset($_SESSION["loged_in"])) || ($_SESSION["loged_in"] == false)): ?>
        <img src="pic/unsucces.png">
        <h1>Sikertelen foglalás!</h1>
        <p>A(z) <strong><?= $car_name ?></strong> autót sajnos nem sikerült lefoglalni, mivel nincs bejelentkezve!
            <br> Kérem jelentkezzen be, majd próbálkozzon újra a foglalással!
        </p>
        <button onclick="location.href='login.php'">Bejelentkezés</button>
    <?php endif; ?>
    <?php if ($rentable && $_SESSION["loged_in"] == true && (count($errors) == 0)): ?>
        <img src="pic/succes.png">
        <h1>Sikeres foglalás!</h1>
        <p>A(z) <strong><?= $car_name ?></strong> sikeresen le lett foglalva a <strong><?= $from ?>–<?= $to ?></strong>
            intervallumra.
        <p>Összesen <strong><?= $sum_of_days + 1 ?></strong> napra történt foglalás, így a végösszeg
            <strong><?= $sum ?>Ft</strong>
        </p>
        <br> A foglalásod állapotát a profiloldaladon tudod megtekinteni.
        </p>
        <button onclick="location.href='profile.php'">Profilom</button>
    <?php endif; ?>
    <?php if (!$rentable): ?>
        <img src="pic/unsucces.png">
        <h1>Sikertelen foglalás!</h1>
        <p>A(z) <strong><?= $car_name ?></strong> sajnos nem foglalható a <strong><?= $from ?>–<?= $to ?></strong>
            intervallumra.
            <br> Próbáld újrafoglalni egy másik időintervallumra, vagy keress egy másik autót.
        </p>
        <button id="back" onclick="location.href='carpage.php?id=<?= $post["car_id"] ?>'">Vissza a jármű oldalára</button>
    <?php endif; ?>
    <?php if ((count($errors) > 0) && $rentable && $_SESSION["loged_in"] == true): ?>
        <img src="pic/unsucces.png">
        <h1>Sikertelen foglalás!</h1>
        <?php if (count($errors) > 0): ?>
            <p>A(z) <strong><?= $car_name ?></strong> sajnos nem foglalható a nem megfelő dátum miatt.
                <?php if (count($errors) == 1): ?>
                    <br> Kérem vedd figyelembe az alábbi hibaüzenetet!
                    <br>Hibaüzenet:
                <?php else: ?>
                    <br> Kérem vedd figyelembe az alábbi hibaüzeneteket!
                    <br>Hibaüzenetek:
                <?php endif; ?>
            <ul></ul>
            <?php foreach ($errors as $error): ?>
                <li><?= $error ?></li>
            <?php endforeach; ?>
            </ul>
            </p>
            <button id="back" onclick="location.href='carpage.php?id=<?= $post["car_id"] ?>'">Vissza a jármű oldalára</button>
        <?php else: ?>
        <?php endif; ?>
    <?php endif; ?>
</body>

</html>