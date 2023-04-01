<?php
require_once __DIR__ . "/../../bootstrap/bootstrap.php";

class EmployeeDetailPage extends BasePage
{
    private $rooms;
    private $employee;

    protected function prepare(): void
    {
        parent::prepare();
//        získat data z GET
        $employeeId = filter_input(INPUT_GET, 'employeeId', FILTER_VALIDATE_INT);
        if (!$employeeId)
            throw new BadRequestException();

        //najít místnost v databázi
        $this->employee = Employee::findByID($employeeId);
        if (!$this->employee)
            throw new NotFoundException();

        $stmt = PDOProvider::get()->prepare("SELECT `room_id`, r.`name` FROM `employee` e join `key` k on (e.employee_id = k.employee) JOIN `room` r on (k.room = r.room_id) WHERE e.employee_id = ".$employeeId. " ORDER BY `name`");
        $stmt->execute([]);
        $this->rooms = $stmt->fetchAll();

        $this->title = "Detail místnosti {$this->employee->name}";

    }

    protected function pageBody()
    {
        //prezentovat data
        return MustacheProvider::get()->render(
            'employeeDetail',
            ['employee' => $this->employee, 'rooms' => $this->rooms]
        );
    }

}

$page = new EmployeeDetailPage();
$page->render();

?>
