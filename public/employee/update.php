<?php
require_once __DIR__ . "/../../bootstrap/bootstrap.php";

class EmployeeUpdatePage extends CRUDPage
{
    private ?Employee $employee;
    private $rooms;
    private $roomKeys;
    private $keys;
    private $key;
    private ?array $errors = [];
    private int $state;

    protected function prepare(): void
    {
        parent::prepare();
        $this->findState();
        $this->title = "Upravit zaměstnance";

        //když chce formulář
        if ($this->state === self::STATE_FORM_REQUESTED)
        {
            $employeeId = filter_input(INPUT_GET, 'employeeId', FILTER_VALIDATE_INT);
            if (!$employeeId)
                throw new BadRequestException();


            //jdi dál
            $this->employee = Employee::findByID($employeeId);
            if (!$this->employee)
                throw new NotFoundException();

            $this->key = Key::getAll();
            $this->rooms = Room::getAll();
            $this->roomKeys = Room::getAll();

            foreach ($this->rooms as $room){
                if($room->room_id == $this->employee->room){
                    $room->selected = "selected";
                }
                else{
                    $room->selected = "";
                }
            }
            foreach ($this->roomKeys as $roomKey){
                foreach ($this->key as $keyN){
                    if($roomKey->room_id == $keyN->room && $keyN->employee == $this->employee->employee_id){
                        $roomKey->selected = "selected";
                    }
                }
                if(!$roomKey->selected){
                    $roomKey->selected = "";
                }
            }
        }

        //když poslal data
        elseif($this->state === self::STATE_DATA_SENT) {
            if($_SESSION['login'] == null || $_SESSION['admin'] != 1){
                header("Location: ../login.php?");
                exit();
            }

            //načti je
            $this->employee = Employee::readPost();

            //zkontroluj je, jinak formulář
            $this->errors = [];
            //ulož je

            //přesměruj
            $isOk = $this->employee->validate($this->errors);
            if (!$isOk)
            {
                $this->state = self::STATE_FORM_REQUESTED;
            }
            else
            {
                //ulož je
                $keyNum = $_POST['rooms'];

                $success2 = $this->key = Key::deleteAll($_POST['employee_id']);
                $this->redirect(self::ACTION_INSERT, $success2, "no");


                foreach ($keyNum as $no){

                    $this->key = Key::readPost($no);
                    $success2 = $this->key->insert();
                  $this->redirect(self::ACTION_INSERT, $success2, "no");
                }

                $options = [
                    'cost' => 12,
                ];

                $this->employee->password =  password_hash($this->employee->password, PASSWORD_BCRYPT, $options);
                $success = $this->employee->update();

                //přesměruj
               $this->redirect(self::ACTION_UPDATE, $success);

            }
        }
    }

    protected function pageBody()
    {
        return MustacheProvider::get()->render(
            'employeeForm',
            [
                'employee' => $this->employee,
                'errors' => $this->errors,
                'rooms' => $this->rooms,
                'roomKeys' => $this->roomKeys
            ]
        );
    }

    private function findState() : void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST')
            $this->state = self::STATE_DATA_SENT;
        else
            $this->state = self::STATE_FORM_REQUESTED;
    }

}

$page = new EmployeeUpdatePage();
$page->render();

?>
