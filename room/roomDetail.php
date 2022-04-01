<?php
session_start();
require_once "../_includes/bootstrap.inc.php";

final class Page extends BaseDBPage{
    public function __construct()
    {
        parent::__construct();
        $roomId = (int) ($_GET['room_id'] ?? 0);
        $roomNo = RoomModel::getDetail($roomId)->fetch();
        $this->title = "Místnost č.".$roomNo->no;
    }

    protected function body(): string
    {
        $roomId = (int) ($_GET['room_id'] ?? 0);
        $stmt = RoomModel::getDetail($roomId)->fetch();
        if ($stmt==null){
            $ePage = new ErrorPage();
            $ePage->render();
            return $ePage;
        }else{

            return $this->m->render(
                "roomDetail",
                ["rooms" =>RoomModel::getDetail($roomId),"employees"=>RoomModel::getEmployees($roomId),"keyes"=>RoomModel::getKeyes($roomId),"avg"=>RoomModel::getAVG($roomId)]
            );
        }


    }
}

if ($_SESSION["loged"]){
    (new Page())->render();
}else{
    header('Location: login.php');
    exit;
}






//echo $roomId;
//echo $success;
