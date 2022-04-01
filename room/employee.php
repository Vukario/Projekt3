<?php
session_start();
require_once "../_includes/bootstrap.inc.php";

final class Page extends BaseDBPage{
    public function __construct()
    {
        parent::__construct();
        $this->title = "Employee listing";
    }

    protected function body(): string
    {
        if (isset($_SESSION["jmeno"])){
            $name =  $_SESSION["name"];
            $surname =  $_SESSION["surname"];
        }
        return $this->m->render(
            "employeeList",
            ["employees" => EmployeeModel::getList(), "employeeDetailName" => "employeeDetail.php","logedName"=>$name,"logedSurname"=>$surname]
        );
    }
}
if ($_SESSION["loged"]){
    (new Page())->render();
}else{
    header('Location: login.php');
    exit;
}