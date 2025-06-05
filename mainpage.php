<?php
include("storage.php");

$car_storage = new CarStorage();
$rent_storage = new RentStorage();
$user_storage = new UserStorage();
session_start();
if (isset($_SESSION["loged_in"]) && $_SESSION["user_id"] != "677ff9aef18c8") {
    $admin = false;
}

//összes autó
$cars = $car_storage->findAll();

//filter értékek
$filter_values = [
    "seats" => $_GET["seats"] ?? null,
    "start_date" => $_GET["start_date"] ?? null,
    "end_date" => $_GET["end_date"] ?? null,
    "transmission" => $_GET["transmission"] ?? null,
    "price_from" => $_GET["price_from"] ?? null,
    "price_to" => $_GET["price_to"] ?? null
];

//filter függvény
function filter_cars($car_storage, $filter_values, $rent_storage)
{
    $cars = $car_storage->findAll();
    $all_cars = $car_storage->findAll();
    $all_rent = $rent_storage->findAll();

    if ($filter_values["seats"] != null) {
        $cars = array_filter($cars, function ($car) use ($filter_values) {
            return $car["passengers"] >= $filter_values["seats"];
        });
    }

    if ($filter_values["transmission"] != null) {
        $cars = array_filter($cars, function ($car) use ($filter_values) {
            $transmission = $filter_values["transmission"];
            if ($transmission == "manual") {
                $transmission = "Manual";
            } else {
                $transmission = "Automatic";
            }
            return $car["transmission"] == $transmission;
        });
    }

    if ($filter_values["price_from"] != null) {
        $cars = array_filter($cars, function ($car) use ($filter_values) {
            return $car["daily_price_huf"] >= $filter_values["price_from"];
        });
    }

    if ($filter_values["price_to"] != null) {
        $cars = array_filter($cars, function ($car) use ($filter_values) {
            return $car["daily_price_huf"] <= $filter_values["price_to"];
        });
    }

    if ($filter_values["start_date"] != null && $filter_values["end_date"] != null) {
        return filter_cars_by_date($filter_values, $all_cars, $all_rent);
    }

    return $cars;
}

//időintervallum ellenőrzése
$all_cars = $car_storage->findAll();
$all_rent = $rent_storage->findAll();

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

$all_cars = filter_cars_by_date($filter_values, $all_cars, $all_rent);
$cars = filter_cars($car_storage, $filter_values, $rent_storage);

if (isset($_GET['all_cars'])) {
    $cars = $car_storage->findAll();
}

//kilistázott autók száma
$car_number = count($cars);

//bejelentkezett felhasználó (admin vagy nem)
$user_id = $_SESSION["user_id"] ?? null;
$admin = false;
if ($user_id != null) {
    $user = $user_storage->findById($user_id);
    if ($user["email"] == "admin@ikarrental.hu") {
        $admin = true;
    } else {
        $admin = false;
    }
}

//autó törlése
if (isset($_POST["delete_car_id"])) {
    $delet_car = $car_storage->findById($_POST["delete_car_id"]);
    //a törölt autóhoz tartozó foglalások törlése
    $id = $_POST["delete_car_id"];
    $rents = $rent_storage->findAll();
    foreach ($rents as $rent) {
        if ($rent["car_id"] == $id) {
            $rent_storage->delete($rent["id"]);
        }
    }

    $car_storage->delete($_POST["delete_car_id"]);
    header("Location: mainpage.php");
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/header.css" />
    <link rel="stylesheet" href="css/mainpage.css" />
    <title>Főoldal</title>

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

    <h1 id="page-title">Gyors és egyszerű <strong>autóbérlés</strong></h1>

    <form action="" method="get" novalidate id="filter-form">
        <div class="filter-box">
            <div class="filter-item">
                <input type="number" id="seats" name="seats"
                    placeholder="<?= ($filter_values["seats"] == null) ? 0 : $filter_values["seats"] ?>">
                <label for="seats">férőhely</label>
            </div>
            <div class="filter-item">
                <select id="transmission" name="transmission">
                    <option value="hiddens" disabled selected hidden>
                        <?= ($filter_values["transmission"] == null) ? "Váltó típusa" : (($filter_values["transmission"] == "manual") ? "manuális" : "automata") ?>
                    </option>
                    <option value="manual">manuális</option>
                    <option value="automatic">automata</option>
                </select>
            </div>
            <div class="filter-item">
                <input type="date" id="start_date" name="start_date"
                    placeholder="<?= ($filter_values["start_date"] == null) ? "2025.01.01" : $filter_values["start_date"] ?>"
                    required>
                <label for="start_date">-tól</label>
            </div>
            <div class="filter-item">
                <input type="date" id="end_date" name="end_date"
                    placeholder="<?= ($filter_values["end_date"] == null) ? "2025.01.05" : $filter_values["end_date"] ?>">
                <label for="end_date">-ig</label>
            </div>

            <div class="filter-item">
                <input type="number" id="price_from" name="price_from" min="0"
                    placeholder="<?= ($filter_values["price_from"] == null) ? 15000 : $filter_values["price_from"] ?>">
                -
                <input type="number" id="price_to" name="price_to" min="0"
                    placeholder="<?= ($filter_values["price_to"] == null) ? 23000 : $filter_values["price_to"] ?>"> Ft
            </div>
            <div class="filter-item">
                <button type="submit">Szűrés</button>
                <button type="submit" name="delete_filters" value="1">Szürők eltávolítása</button>
            </div>
        </div>

    </form>
    <h3 id="number-of-cars">Kilistázott autók száma: <?= $car_number ?></h3>

    <?php if ($admin): ?>
        <button id="add_new_car" onclick="location.href='newcar.php'">Új autó hozzáadása</button>
        <br>
    <?php endif; ?>
    <?php foreach ($cars as $car): ?>
        <a href="carpage.php?id=<?= $car["id"] ?>" class="car-card-link">
            <div class="car-card">
                <img id="car-image" src="<?= $car["image"] ?>">
                <?php $car_name = $car["brand"] . " " . $car["model"]; ?>
                <p class="car-title" onclick="location.href='carpage.php?id=<?= $car["id"] ?>'"><b><?= $car_name ?></b></p>
                <p><?= $car["passengers"] ?> férőhely -
                    <?= ($car["transmission"] == "Automatic") ? "automata" : "manuális" ?>
                </p>
                <p class="car-price"><?= $car["daily_price_huf"] ?> Ft /nap</p>
                <button class="reserve-button" id="<?= $car["id"] ?>"
                    onclick="location.href='carpage.php?id=<?= $car["id"] ?>'">
                    Foglalás
                </button>
        </a>
        <?php if ($admin): ?>
            <div class="admin-actions">
                <form action=" " method="post" novalidate>
                    <input type="hidden" name="delete_car_id" value="<?= $car["id"] ?>">
                    <button type="submit" id="delete-car">Törlés</button>
                </form>
                <button id="editing-car" onclick="location.href='editcar.php?edit_id=<?= $car["id"] ?>'">Szerkesztés</button>
            </div>
        <?php endif; ?>
        </div>
    <?php endforeach; ?>

</body>

</html>