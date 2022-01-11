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
}