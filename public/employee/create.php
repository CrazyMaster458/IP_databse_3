<?php
require_once __DIR__ . "/../../bootstrap/bootstrap.php";

class EmployeeCreatePage extends CRUDPage
{
    private ?Employee $employee;
    private $rooms;
    private $roomKeys;
    private $key;
    private ?array $errors = [];
    private int $state;

    protected function prepare(): void
    {
        parent::prepare();
        $this->findState();
        $this->title = "Založit novou místnost";

        //když chce formulář
        if ($this->state === self::STATE_FORM_REQUESTED)
        {
            //jdi dál
            $this->employee = new Employee();

            $this->key = Key::getAll();
            $this->rooms = Room::getAll();
            $this->roomKeys = Room::getAll();
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
            $isOk = $this->employee->validate($this->errors);
            if (!$isOk)
            {
                $this->state = self::STATE_FORM_REQUESTED;

                $this->key = Key::getAll();
                $this->rooms = Room::getAll();
                $this->roomKeys = Room::getAll();
            }
            else
            {
                $keyNum = $_POST['rooms'];

                //ulož je

                $options = [
                    'cost' => 12,
                ];

                $this->employee->password =  password_hash($this->employee->password, PASSWORD_BCRYPT, $options);
                $success = $this->employee->insert();

                //přesměruj
               $this->redirect(self::ACTION_INSERT, $success, "no");

                foreach ($keyNum as $no){
                    $this->key = Key::readPost($no, $this->employee->employee_id);
                    $success2 = $this->key->insert();
                    $this->redirect(self::ACTION_INSERT, $success2, "no");
                }

                $this->redirect("", true, "v2");
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

$page = new EmployeeCreatePage();
$page->render();

?>
