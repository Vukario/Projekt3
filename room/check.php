<?php
require_once "../_includes/bootstrap.inc.php";
session_start();
$user = strtolower($_POST['name']);
$_SESSION["jmeno"] = $_POST['name'];
$stmt = DB::getConnection()->prepare("SELECT * FROM `employee` WHERE `login`=:user");
$stmt->bindParam(':user', $user);
$stmt->execute();
$stmt = $stmt->fetch();


if ($user === $stmt->login && hash("sha256",$_POST['password']) === $stmt->password) {
    $_SESSION["loged"] = "Ano";

    if ($stmt->admin==1){
        $_SESSION["admin"]="ano";
    }
} else {

    header('Location: login.php');
    exit;

}

if(isset($_SESSION["loged"])){
    $_SESSION["name"] = $stmt->name;
    $_SESSION["surname"] = $stmt->surname;
    $_SESSION["id"] = $stmt->employee_id;
    header('Location: room.php');
    exit;
    echo '<a href="logout.php">Odhlásit se</a>';
}

?>