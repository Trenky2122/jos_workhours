<?php
if(isset($_GET["err"])){
    if($_GET["err"]==1){
        ?>
        <div class="row">
            <div class="alert alert-danger" role="alert">
                Chyba pri spracovaní requestu.
            </div>
        </div>
    <?php
    }
    if($_GET["err"]==2){
        ?>
        <div class="row">
            <div class="alert alert-danger" role="alert">
                Nesprávne heslo používateľa.
            </div>
        </div>
        <?php
    }
    if($_GET["err"]==3){
        ?>
        <div class="row">
            <div class="alert alert-danger" role="alert">
                Nepodarilo sa uložiť do databázy.
            </div>
        </div>
        <?php
    }
    if($_GET["err"]==4){
        ?>
        <div class="row">
            <div class="alert alert-danger" role="alert">
                Nové heslá sa nerovnajú.
            </div>
        </div>
        <?php
    }
}

if(isset($_GET["succ"])){
    if($_GET["succ"]==1){
        ?>
        <div class="row">
            <div class="alert alert-success" role="alert">
                Úspešne uložené.
            </div>
        </div>
        <?php
    }
    if($_GET["succ"]==2){
        ?>
        <div class="row">
            <div class="alert alert-success" role="alert">
                Používateľ vytvorený s heslom 'aleluja'.
            </div>
        </div>
        <?php
    }
    if($_GET["succ"]==3){
        ?>
        <div class="row">
            <div class="alert alert-success" role="alert">
                Úspešne prihlásený.
            </div>
        </div>
        <?php
    }
    if($_GET["succ"]==4){
        ?>
        <div class="row">
            <div class="alert alert-success" role="alert">
                Úspešne odhlásený.
            </div>
        </div>
        <?php
    }
    if($_GET["succ"]==5){
        ?>
        <div class="row">
            <div class="alert alert-success" role="alert">
                Mesiac úspešne uzavretý.
            </div>
        </div>
        <?php
    }
}