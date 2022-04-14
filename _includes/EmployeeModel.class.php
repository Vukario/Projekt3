<?php

class EmployeeModel
{
    public ?int $employee_id;
    public string $name = "";
    public string $surname = "";
    public string $job="";
    public ?int $wage;
    public string $room="";
    public string $login="";
    public string $password="";


    private array $validationErrors = [];

    public function getValidationErrors(): array
    {
        return $this->validationErrors;
    }

    public function __construct()
    {
    }
    public static function  getKeyes($employeeId) : PDOStatement{

        $stmt = DB::getConnection()->prepare('SELECT `key`.room AS key_room, `key`.key_id as key_id, `key`.employee AS key_employee, employee.employee_id AS employee_id, room.name AS room_name, room.room_id AS room_id FROM employee,`key`,room WHERE employee_id=:employeeId AND `key`.employee = employee_id AND `key`.room = room_id');
        $stmt->execute(['employeeId' => $employeeId]);
        return $stmt;
    }
    public static function  getDetail($employeeId) : PDOStatement{
        $stmt = DB::getConnection()->prepare('SELECT employee.employee_id, employee.wage,employee.name AS name, employee.surname, employee.room, employee.job, room.phone, room.room_id, room.name AS room_name
FROM employee,room
WHERE employee_id=:employeeId AND room_id = employee.room');
        $stmt->execute(['employeeId' => $employeeId]);
        return $stmt;
    }
    public static function  getRoomBy() : PDOStatement{
        $stmt = DB::getConnection()->prepare('SELECT * FROM room ');
        $stmt->execute();
        return $stmt;
    }
    public static function getAllKeys():PDOStatement{
        $stmt = DB::getConnection()->prepare('SELECT * FROM `key` ');
        $stmt->execute();
        return $stmt;
    }
    public function update() : bool
    {

        $sql = "UPDATE employee SET name=:name, surname=:surname, job=:job,wage=:wage,room=:room, login=:login,password=:password WHERE employee_id=:employee_id";

        $stmt = DB::getConnection()->prepare($sql);
        $stmt->bindParam(':employee_id', $this->employee_id);
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':surname', $this->surname);
        $stmt->bindParam(':job', $this->job);
        $stmt->bindParam(':wage', $this->wage);
        $stmt->bindParam(':room', $this->room);
        $stmt->bindParam(':login', $this->login);
        $passHashed= hash("sha256",$this->password);
        $stmt->bindParam(':password', $passHashed);

        $sql2 = "DELETE FROM `key` WHERE employee=:employee_id";
        $stmt2 = DB::getConnection()->prepare($sql2);
        $stmt2->bindParam(':employee_id', $this->employee_id);
        $stmt2->execute();
        foreach ($_POST["key"] as $key){
            $this->insertKeyUpdate($key);
        }

        return $stmt->execute();
    }
    public function updatePassword($employee_id) : bool
    {
        $sql = "UPDATE employee SET password=:password WHERE employee_id=:employee_id";

        $stmt = DB::getConnection()->prepare($sql);
        $stmt->bindParam(':employee_id', $employee_id);
        $passHashed= hash("sha256",$this->password);
        $stmt->bindParam(':password', $passHashed);

        return $stmt->execute();
    }
    public function insert() : bool {

        $sql = "INSERT INTO employee (name, surname, job,wage,room,login,password) VALUES (:name, :surname, :job,:wage,:room,:login,:password)";



        $stmt = DB::getConnection()->prepare($sql);
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':surname', $this->surname);
        $stmt->bindParam(':job', $this->job);
        $stmt->bindParam(':wage', $this->wage);
        $stmt->bindParam(':room', $this->room);
        $stmt->bindParam(':login', $this->login);
        $passHashed= hash("sha256",$this->password);
        $stmt->bindParam(':password', $passHashed);

         $returnos = $stmt->execute();
         foreach ($_POST["key"] as $key){
            $this->insertKey($key);
        }
        return $returnos;
    }
    public function insertKeyUpdate($key){
        $sql = "INSERT INTO `key` (employee,room) VALUES (:employee, :room)";


        $stmt = DB::getConnection()->prepare($sql);
        $stmt->bindParam(':employee', $this->employee_id);
        $stmt->bindParam(':room', $key);
        $stmt->execute();
    }
    public function insertKey($key){
        $sql = "INSERT INTO `key` (employee,room) VALUES (:employee, :room)";
        $sql2 = "SELECT MAX(employee_id) as max FROM employee";
        $stmt2 = DB::getConnection()->prepare($sql2);
        $stmt2->execute();
        $stmt2 = $stmt2->fetch();

        $stmt = DB::getConnection()->prepare($sql);
        $stmt->bindParam(':employee', $stmt2->max);
        $stmt->bindParam(':room', $key);
        $stmt->execute();
    }

    public static function getAll($orderBy = "name", $orderDir = "ASC") : PDOStatement
    {

        $stmt = DB::getConnection()->prepare("SELECT * FROM `employee` ORDER BY `{$orderBy}` {$orderDir}");
        $stmt->execute();
        return $stmt;

    }
    public static function getList($orderBy = "name", $orderDir = "ASC") : PDOStatement
    {

        $stmt = DB::getConnection()->prepare("SELECT employee.employee_id, employee.wage,employee.name AS name, employee.surname, employee.room, employee.job, room.phone, room.room_id, room.name AS room_name
FROM employee,room WHERE room_id = employee.room ORDER BY `{$orderBy}` {$orderDir}");
        $stmt->execute();
        return $stmt;

    }


    public static function deleteById(int $employee_id) : bool
    {
        $sql = "DELETE FROM employee WHERE employee_id=:employee_id";

        $stmt = DB::getConnection()->prepare($sql);
        $stmt->bindParam(':employee_id', $employee_id);

        return $stmt->execute();
    }
    public static function getPasswordFromPost() : self {
        $employee = new EmployeeModel();


        $employee->password = filter_input(INPUT_POST, "password");

        return $employee;
    }
    public static function getFromPost() : self
    {

        $employee = new EmployeeModel();

        $employee->employee_id = filter_input(INPUT_POST, "employee_id", FILTER_VALIDATE_INT);

        $employee->name = filter_input(INPUT_POST, "name");
        $employee->surname = filter_input(INPUT_POST, "surname");
        $employee->job = filter_input(INPUT_POST, "job");
        if (ctype_digit(filter_input(INPUT_POST, "wage"))) {
            if ((int)filter_input(INPUT_POST, "wage") > 0) {
                $employee->wage = filter_input(INPUT_POST, "wage");
            }

        } else {
            $employee->wage = null;
        }
      //

        $employee->room = filter_input(INPUT_POST, "room");
        $employee->login = filter_input(INPUT_POST, "login");
        $employee->password = filter_input(INPUT_POST, "password");

        return $employee;
    }
    public function validate() : bool {


        $isOk = true;
        $errors = [];

        if (!$this->name){
            $isOk = false;
            $errors["name"] = "Employee name cannot be empty";
        }

        if (!$this->surname){
            $isOk = false;
            $errors["surname"] = "Employee surname cannot be empty";
        }
        if ($this->job === ""){
            $isOk = false;
            $errors["job"] = "Employee job cannot be empty";
        }
        if ($this->login === ""){
            $isOk = false;
            $errors["login"] = "Employee login cannot be empty";
        }
        if (!$this->room){
            $isOk = false;
            $errors["room"] = "Employee room cannot be empty";
        }
        if ($this->wage==null){
            $isOk = false;
            $errors["wage"] = "Employee wage cannot be empty";
        }
        if ($this->password === ""){
            $isOk = false;
            $errors["password"] = "Employee password cannot be empty";
        }

        $this->validationErrors = $errors;
        return $isOk;
    }
    public function validatePassword() : bool {
        $isOk = true;
        $errors = [];

        if ($this->password === ""){
            $isOk = false;
            $errors["password"] = "Employee password cannot be empty";
        }

        $this->validationErrors = $errors;
        return $isOk;
    }

    public static function getById($employeeId) : ?self
    {
        $stmt = DB::getConnection()->prepare("SELECT * FROM `employee` WHERE `employee_id`=:employee_id");

        $stmt->bindParam(':employee_id', $employeeId);
        $stmt->execute();



        $record = $stmt->fetch();

        if (!$record)
            return null;


        $model = new self();
        $model->employee_id = $record->employee_id;
        $model->name = $record->name;
        $model->surname = $record->surname;
        $model->job = $record->job;
        $model->room = $record->room;
        $model->wage= $record->wage;
        $model->login=$record->login;
        $model->password=$record->password;
        return $model;
    }
}