<?php
include "header.php";
?>
<div style="padding-left: 1em">
    <div class="row mt-1" style="padding-top: 1.25em;">
        <div class="col">
            <a class="btn btn-primary" href="index.php">Index</a>
        </div>
    </div>
    <div class="row mt-1" style="padding-top: 1em;">
        <div class="col">
            <ul>
                <?php if($_SESSION["user_role"]==1){?><li><a href="add_worker.php">Pridať používateľa</a></li><?php }?>
                <?php if($_SESSION["user_role"]==1){?><li><a href="add_project.php">Pridať projekt</a></li><?php }?>
                <li><a href="projects.php">Prehľad projektov</a></li>
                <li><a href="overview.php">Týždenný prehľad</a></li>
            </ul>
        </div>
    </div>
</div>
<?php
include "footer.php";