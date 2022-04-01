<?php
session_start();
require_once "../_includes/bootstrap.inc.php";

final class Page extends BaseDBPage{
    public function __construct()
    {
        parent::__construct();
        $employeeId = (int) ($_GET['employee_id'] ?? 0);
        $stmt = EmployeeModel::getDetail($employeeId)->fetch();
        $this->title = "Místnost č.".$stmt->name."\n".$stmt->surnamr;
    }

    protected function body(): string
    {
        $employeeId = (int) ($_GET['employee_id'] ?? 0);
        return $this->m->render(
            "employeeDetail",
            ["employees"=>EmployeeModel::getDetail($employeeId),"keyes"=>EmployeeModel::getKeyes($employeeId)]
        );
    }
}

if ($_SESSION["loged"]){
    (new Page())->render();
}else{
    header('Location: login.php');
    exit;
}


