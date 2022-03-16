<?php
    if(!defined("BYPASS_AUYHENTICATE") || !BYPASS_AUYHENTICATE) {
        include "authenticate.php";
    }
    $menu_active = "";
    if(isset($active)){
        $menu_active = $active;
    }
?>

<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="utf-8">
    <title>JOS Group - pracovný čas</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="logo2.png" />
    <link rel="stylesheet" href="export_tables.css">
    <link rel="stylesheet" href="styles.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p"
            crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
</head>
<body>
<script src="scripts.js" type="application/javascript"></script>
<nav class="navbar navbar-expand-xl navbar-dark" style="background-color: #0132B9">
    <div class="container-fluid">
        <a class="navbar-brand" href="index.php">
            <img src="logo2.png" alt="logo" height="50">
            JOS Group - pracovný čas
        </a>
        <button class="navbar-toggler noprint" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse noprint" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link <?php if($menu_active == "month") echo "active"?>" href="<?php if($_SESSION["user_id"]==1)echo "admin_";?>month_view.php">Mesačný prehľad</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php if($menu_active == "week") echo "active"?>" href="overview.php">Týždenný prehľad</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php if($menu_active == "projects") echo "active"?>" href="projects.php">Prehľad projektov</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?php if($menu_active == "settings") echo "active"?>" href="#" id="navbarDropdownMenuLink" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Nastavenia
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
                        <li><a class="dropdown-item" href="password_change.php?id=<?= $_SESSION["user_id"] ?>">Zmeniť heslo</a></li>
                        <li><a class="dropdown-item" href="default_change.php?id=<?= $_SESSION["user_id"] ?>">Zmeniť default hodnoty</a></li>
                        <?php if($_SESSION["user_role"]==1){?><li><a class="dropdown-item" href="add_worker.php">Pridať používateľa</a></li><?php }?>
                        <?php if($_SESSION["user_role"]==1){?><li><a class="dropdown-item" href="add_project.php">Pridať projekt</a></li><?php }?>
                    </ul>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="https://webmail.webhouse.sk/" target="_blank">Mail</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="logout.php">Odhlásiť</a>
                </li>
            </ul>
        </div>
    </div>
</nav>
<div class="container-fluid">


