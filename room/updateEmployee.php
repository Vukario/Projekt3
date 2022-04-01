<?php

require_once "../_includes/bootstrap.inc.php";
session_start();
final class Page extends BaseDBPage{

    const STATE_FORM_REQUESTED = 1;
    const STATE_DATA_SENT = 2;
    const STATE_REPORT_RESULT = 3;

    const RESULT_SUCCESS = 1;
    const RESULT_FAIL = 2;

    private EmployeeModel $employee;
    private int $state;
    private int $result;


    public function __construct()
    {
        parent::__construct();
        $this->title = "Employee update";
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->getState();

        if ($this->state === self::STATE_REPORT_RESULT) {
            if ($this->result === self::RESULT_SUCCESS) {
                $this->title = "Employee update";
            } else {
                $this->title = "Employee update failed";
            }
            return;
        }
        if (isset ($_SESSION["admin"])){
            if ($this->state === self::STATE_DATA_SENT) {
                $this->employee = EmployeeModel::getFromPost();
                if ($this->employee->validate()) {
                    //uložím
                    if ($this->employee->update()) {
                        $this->redirect(self::RESULT_SUCCESS);
                    } else {
                        $this->redirect(self::RESULT_FAIL);
                    }
                } else {
                    $this->state = self::STATE_FORM_REQUESTED;
                    $this->title = "Employee update: Invalid data";
                }
            } else {
                $this->title = "Update room";
                $employeeId = filter_input(INPUT_GET, "employee_id", FILTER_VALIDATE_INT);
                if ($employeeId){
                    $this->employee = EmployeeModel::getById($employeeId);
                    if (!$this->employee)
                        throw  new RequestException(404);
                } else {
                    throw  new RequestException(400);
                }
            }

        }else{
            if ($this->state === self::STATE_DATA_SENT) {
                $this->employee = EmployeeModel::getPasswordFromPost();
                $employeeId = filter_input(INPUT_GET, "employee_id", FILTER_VALIDATE_INT);
                if ($this->employee->validatePassword()) {
                    //uložím
                    if ($this->employee->updatePassword($employeeId)) {
                        $this->redirect(self::RESULT_SUCCESS);
                    } else {
                        $this->redirect(self::RESULT_FAIL);
                    }
                } else {
                    $this->state = self::STATE_FORM_REQUESTED;
                    $this->title = "Employee update: Invalid data";
                }
            } else {
                $this->title = "Update room";
                $employeeId = filter_input(INPUT_GET, "employee_id", FILTER_VALIDATE_INT);
                if ($employeeId){
                    $this->employee = EmployeeModel::getById($employeeId);
                    if (!$this->employee)
                        throw  new RequestException(404);
                } else {
                    throw  new RequestException(400);
                }
            }

        }

    }


    protected function body(): string {
        $employeeId = (int) ($_GET['employee_id'] ?? 0);
        $result = (int) ($_GET['result'] ?? 0);
        if (isset ($_SESSION["admin"]) or $employeeId ===$_SESSION["id"] or $result === 1){
        if ($this->state === self::STATE_FORM_REQUESTED) {
            if (isset ($_SESSION["admin"])){
                return $this->m->render("employeeForm", [
                    "employee"=>$this->employee,
                    "errors"=>$this->employee->getValidationErrors(),
                    "room"=>EmployeeModel::getRoomBy(),
                    "admin"=>true,
                    "update"=>true
                ]);
            }else{
                $istheone = true;
                return $this->m->render("employeeForm", [
                    "employee"=>$this->employee,
                    "errors"=>$this->employee->getValidationErrors(),
                    "update"=>true
                ]);
            }

        } elseif ($this->state === self::STATE_REPORT_RESULT) {
            if ($this->result === self::RESULT_SUCCESS) {
                return $this->m->render("reportSuccess", ["data"=>"Room update successfully"]);
            } else {
                return $this->m->render("reportFail", ["data"=>"Room update failed. Please contact adiministrator or try again later."]);
            }

        }
        }else {
            return $this->m->render("reportFail", ["data"=>"Room update failed you don't have permition. Please contact adiministrator."]);
        }
    }

    private function getState() : void {
        //je už hotovo?
        $result = filter_input(INPUT_GET, "result", FILTER_VALIDATE_INT);
        if ($result === self::RESULT_SUCCESS) {
            $this->state = self::STATE_REPORT_RESULT;
            $this->result = self::RESULT_SUCCESS;
            return;
        } elseif ($result === self::RESULT_FAIL) {
            $this->state = self::STATE_REPORT_RESULT;
            $this->result = self::RESULT_FAIL;
            return;
        }

        //byl odeslán formulář
        $action = filter_input(INPUT_POST, "action");
        if ($action === "update") {
            $this->state = self::STATE_DATA_SENT;
            return;
        }

        $this->state = self::STATE_FORM_REQUESTED;
    }

    private function redirect(int $result) : void {
        //odkaz sám na sebe, bez query string atd.
        $location = strtok($_SERVER['REQUEST_URI'], '?');

        header("Location: {$location}?result={$result}");
        exit;
    }
}

if ($_SESSION["loged"]){
    (new Page())->render();
}else{
    header('Location: login.php');
    exit;
}
