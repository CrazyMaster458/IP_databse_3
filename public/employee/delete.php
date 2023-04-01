<?php
require_once __DIR__ . "/../../bootstrap/bootstrap.php";

class EmployeeDeletePage extends CRUDPage
{

    protected function prepare(): void
    {
        parent::prepare();

        $employeeId = filter_input(INPUT_POST, 'employeeId', FILTER_VALIDATE_INT);
        if (!$employeeId)
            throw new BadRequestException();

        //když poslal data
        $success2 = Key::deleteAll($employeeId);
        $success = Employee::deleteByID($employeeId);
        $this->redirect(self::ACTION_DELETE, $success2, "no");


        //přesměruj
        $this->redirect(self::ACTION_DELETE, $success);
    }

    protected function pageBody()
    {
        return "";
    }

}

$page = new EmployeeDeletePage();
$page->render();

?>
