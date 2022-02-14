<?php
    if(!defined("BYPASS_AUYHENTICATE") || !BYPASS_AUYHENTICATE) {
        include "authenticate.php";
    }
?>

<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="utf-8">
    <title>JOS Group - pracovný čas</title>
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
<div class="container-fluid">
    <div class="row" style="background-color: #0132B9; color: #FFFFFF;">
        <div class="col-2">
            <img src="logo2.png" alt="logo" height="50">
        </div>
        <div class="col-7">
            <a href="index.php" class="headline" style="color: #FFFFFF; text-decoration: none;">
                <h1>JOS Group - pracovný čas</h1>
            </a>
        </div>
        <div class="col-3">
            <div class="row">
                <div class="col">
                    <a href="admin.php" class="noprint" style="color: #FFFFFF; float: right;  text-decoration: none;">Ďalšie akcie</a>
                </div>
                <div class="col noprint">
                    <form method="post" action="logout.php" style="float: right;">
                        <input type="submit" name="submit" value="Odhlásiť sa" style="background: transparent; color: #FFFFFF; border: none;">
                    </form>
                </div>
            </div>
        </div>
    </div>


