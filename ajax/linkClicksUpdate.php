<?php
include("../config.php");

if(isset($_POST["id"])){
    $query = $con->prepare("UPDATE sites SET clicks = clicks + 1 WHERE id=:id");
    $query->bindParam(":id", $_POST["id"]);

    $query->execute(); 
} 
else{
    echo "no linkID is passed to page";
}
?>