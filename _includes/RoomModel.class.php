<?php

class RoomModel
{
    public ?int $room_id;
    public string $name = "";
    public string $no = "";
    public ?string $phone = null;

    private array $validationErrors = [];

    public function getValidationErrors(): array
    {
        return $this->validationErrors;
    }

    public function __construct()
    {
    }

    public function insert() : bool {

        $sql = "INSERT INTO room (name, no, phone) VALUES (:name, :no, :phone)";

        $stmt = DB::getConnection()->prepare($sql);
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':no', $this->no);
        $stmt->bindParam(':phone', $this->phone);

        return $stmt->execute();
    }

    public function update() : bool
    {
        $sql = "UPDATE room SET name=:name, no=:no, phone=:phone WHERE room_id=:room_id";

        $stmt = DB::getConnection()->prepare($sql);
        $stmt->bindParam(':room_id', $this->room_id);
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':no', $this->no);
        $stmt->bindParam(':phone', $this->phone);

        return $stmt->execute();
    }

    public static function getById($roomId) : ?self
    {
        $stmt = DB::getConnection()->prepare("SELECT * FROM `room` WHERE `room_id`=:room_id");
        $stmt->bindParam(':room_id', $roomId);
        $stmt->execute();

        $record = $stmt->fetch();

        if (!$record)
            return null;

        $model = new self();
        $model->room_id = $record->room_id;
        $model->name = $record->name;
        $model->no = $record->no;
        $model->phone = $record->phone;
        return $model;
    }

    public static function getAll($orderBy = "name", $orderDir = "ASC") : PDOStatement
    {

        $stmt = DB::getConnection()->prepare("SELECT * FROM `room` ORDER BY `{$orderBy}` {$orderDir}");
        $stmt->execute();
        return $stmt;

    }
    public static function getAVG(int $room_Id):PDOStatement{
        $stmt = DB::getConnection()->prepare('SELECT AVG(employee.wage) as avgwage, employee.name AS employee_name,employee.room,room.room_id FROM room,employee WHERE room_id = employee.room AND room_id=:roomId');
        $stmt->execute(['roomId'=>$room_Id]);
        return $stmt;
    }
    public static function getDetail(int $room_Id):PDOStatement
    {

       $stmt = DB::getConnection()->prepare('SELECT room_id, no, name, phone FROM room WHERE room_id=:roomId');
       $stmt->execute(['roomId' => $room_Id]);

       return $stmt;
    }

    public static function getKeyes(int $room_Id):PDOStatement{
        $stmt = DB::getConnection()->prepare('SELECT e.employee_id, e.name AS ename, e.surname FROM `key` k INNER JOIN employee e ON e.employee_id=k.employee WHERE k.room =:roomId');
        $stmt->execute(['roomId' => $room_Id]);

        return $stmt;
    }
    public static function getEmployees(int $room_Id):PDOStatement
    {

        $stmt = DB::getConnection()->prepare('SELECT e.wage, e.name AS ename, e.surname, e.employee_id FROM employee e INNER JOIN room r ON e.room=r.room_id WHERE r.room_id=:roomId');
        $stmt->execute(['roomId' => $room_Id]);

        return $stmt;
    }

    public static function deleteById(int $room_id) : bool
    {

        $stmt1 = DB::getConnection()->prepare('SELECT employee.employee_id, employee.wage,employee.name AS name, employee.surname, employee.room, employee.job, room.phone, room.room_id, room.name AS room_name
FROM employee,room
WHERE room_id=:roomId AND employee.room = room_id');
        $stmt1->execute(['roomId' => $room_id]);
        if (!$stmt1->fetch()){

            $sql = "DELETE FROM room WHERE room_id=:room_id";


            $stmt = DB::getConnection()->prepare($sql);

            $stmt->bindParam(':room_id', $room_id);

            return $stmt->execute();
        }else{
            return  false;
        }





    }

    public function delete() : bool
    {
        return self::deleteById($this->room_id);
    }

    public static function getFromPost() : self {
        $room = new RoomModel();

        $room->room_id = filter_input(INPUT_POST, "room_id", FILTER_VALIDATE_INT);
        $room->name = filter_input(INPUT_POST, "name");
        $room->no = filter_input(INPUT_POST, "no");
        $room->phone = filter_input(INPUT_POST, "phone");

        return $room;
    }

    public function validate() : bool {
        $isOk = true;
        $errors = [];

        if (!$this->name){
            $isOk = false;
            $errors["name"] = "Room name cannot be empty";
        }

        if (!$this->no){
            $isOk = false;
            $errors["no"] = "Room number cannot be empty";
        }
        if ($this->phone === ""){
            $this->phone = null;
        }

        $this->validationErrors = $errors;
        return $isOk;
    }
}