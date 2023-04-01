<?php

class Employee
{
    public const DB_TABLE = "employee";
    public const DB_TABLE2 = "room";

    public ?int $employee_id;
    public ?string $name;
    public ?string $surname;
    public ?string $job;
    public ?int $wage;
    public ?int $room;
    public ?string $login;
    public ?string $password;
    public ?int $admin;
    public ?string $roomName;
    public ?int $phone;
    public ?Key $rooms;

    /**
     * @param int|null $employee_id
     * @param string|null $name
     * @param string|null $surname
     * @param string|null $job
     * @param int|null $wage
     * @param int|null $room
     * @param string|null $login
     * @param string|null $password
     * @param bool|null $admin
     */
    public function __construct(?int $employee_id = null, ?string $name = null, ?string $surname = null, ?string $job = null, ?int $wage = null, ?int $room = null, ?string $login = null, ?string $password = null, ?bool $admin = null)
    {
        $this->employee_id = $employee_id;
        $this->name = $name;
        $this->surname = $surname;
        $this->job = $job;
        $this->wage = $wage;
        $this->room = $room;
        $this->login = $login;
        $this->password = $password;
        $this->admin = $admin;

    }

    public static function findByID(int $id) : ?self
    {

        $pdo = PDOProvider::get();
        $stmt = $pdo->prepare("SELECT `employee_id`, e.`name`, r.`name` as `roomName` , `surname`, `job`, `wage`, `room`, `room_id`, `phone`, `login`, `admin`, `password` FROM employee e join room r on (e.`room` = r.`room_id`) WHERE `employee_id` = ".$id);
        $stmt->execute([]);

        if ($stmt->rowCount() < 1)
            return null;

        $employee = new self();
        $employee->hydrate($stmt->fetch());

        return $employee;
    }

    public static function findUser(string $login, string $password) : ?self{
        $st = PDOProvider::get()->prepare("SELECT * FROM employee WHERE login='$login'");
        $st->execute();

        while ($userData = $st->fetch()){
            $user = new Employee();
            $user->hydrate($userData);

            if(password_verify($password, $user->password)){
                var_dump("success");
                return $user;
            }

        }
        return null;
    }

    /**
     * @return Employee[]
     */
    public static function getAll($sorting = []) : array
    {
        $sortSQL = "";
        if (count($sorting))
        {
            $SQLchunks = [];
            foreach ($sorting as $field => $direction)
                $SQLchunks[] = "`{$field}` {$direction}";

            $sortSQL = " ORDER BY " . implode(', ', $SQLchunks);
        }

        $pdo = PDOProvider::get();
//        $stmt = $pdo->prepare("SELECT * FROM `".self::DB_TABLE."`" . $sortSQL);
        $stmt = $pdo->prepare("SELECT employee_id, e.name, r.name as roomName , surname, job, wage, room, room_id, phone, login, admin FROM employee e join room r on (e.room = r.room_id) ". $sortSQL);
        $stmt->execute([]);

        $employees = [];
        while ($employeeData = $stmt->fetch())
        {
            $employee = new Employee();
            $employee->hydrate($employeeData);
            $employees[] = $employee;
        }

        return $employees;
    }

    private function hydrate(array|object $data)
    {
        $fields = ['employee_id', 'name', 'surname', "roomName", "job", "wage", "room", "room_id", "phone", "login", "admin", "password"];
        if (is_array($data))
        {
            foreach ($fields as $field)
            {
                if (array_key_exists($field, $data))
                    $this->{$field} = $data[$field];
            }
        }
        else
        {
            foreach ($fields as $field)
            {
                if (property_exists($data, $field))
                    $this->{$field} = $data->{$field};
            }
        }
    }

    public function insert() : bool
    {
        $query = "INSERT INTO `employee` (`name`, `surname`, `job`, `wage`, `room`, `login`, `password`, admin) VALUES (:name, :surname, :job, :wage, :room,:login, :password, :admin)";
        $stmt = PDOProvider::get()->prepare($query);
        $result = $stmt->execute(['name'=>$this->name, 'surname'=>$this->surname, 'job'=>$this->job, 'wage'=>$this->wage, 'room'=>$this->room, 'login'=>$this->login, 'password'=>$this->password, 'admin'=>$this->admin]);

        if (!$result)
            return false;

        $this->employee_id = PDOProvider::get()->lastInsertId();
        return true;
    }

    public function update() : bool
    {

        if (!isset($this->employee_id) || !$this->employee_id)
            throw new Exception("Cannot update model without ID");
        $query = "UPDATE `employee` SET `name` = :name, `surname` = :surname, `job` = :job, `wage` = :wage, `room` = :room, `login` = :login, `password` = :password, `admin` = :admin WHERE `employee_id` = :employee_id";
        $stmt = PDOProvider::get()->prepare($query);
        return $stmt->execute(['employee_id'=>$this->employee_id, 'name'=>$this->name, 'surname'=>$this->surname, 'job'=>$this->job, 'wage'=>$this->wage, 'room'=>$this->room, 'login'=>$this->login, 'password'=>$this->password, 'admin'=>$this->admin]);
    }

    public function delete() : bool
    {
        return self::deleteByID($this->employee_id);
    }

    public static function deleteByID(int $employeeId) : bool
    {

        $query = "DELETE FROM `".self::DB_TABLE."` WHERE `employee_id` = ".$employeeId;
        $stmt = PDOProvider::get()->prepare($query);
        return $stmt->execute([]);
    }

    public function validate(&$errors = []) : bool
    {
        if (!isset($this->name) || (!$this->name))
            $errors['name'] = 'Jméno nesmí být prázdné';

        if (!isset($this->surname) || (!$this->surname))
            $errors['surname'] = 'Příjmení nesmí být prázdné';

        if (!isset($this->room) || (!$this->room))
            $errors['room'] = 'Room nesmí být prázdné';

        if (!isset($this->job) || (!$this->job))
            $errors['job'] = 'Job musí být vyplněno';

        if (!isset($this->wage))
            $errors['wage'] = 'Wage musí být vyplněno';

        if (!isset($this->login) || (!$this->login))
            $errors['login'] = 'Login musí být vyplněno';

        if (!isset($this->password) || (!$this->password))
            $errors['password'] = 'Password musí být vyplněno';
        else if(strlen($this->password) < 6){
            $errors['password'] = 'Password musí být delší než 6 znaků';
        }

        if (!isset($this->admin))
            $errors['admin'] = 'Admin musí být vyplněno';
        else if($this->admin > 1 || $this->admin < 0){
            $errors['admin'] = 'Admin musí být 0 a nebo 1';
        }

        return count($errors) === 0;
    }

    public static function readPost() : self
    {
        $employee = new Employee();

        $employee->employee_id = filter_input(INPUT_POST, 'employee_id', FILTER_VALIDATE_INT);

        $employee->name = filter_input(INPUT_POST, 'name');
        if ($employee->name)
            $employee->name = trim($employee->name);

        $employee->surname = filter_input(INPUT_POST, 'surname');
        if ($employee->surname)
            $employee->surname = trim($employee->surname);

        $employee->job = filter_input(INPUT_POST, 'job');
        if ($employee->job)
            $employee->job = trim($employee->job);

        $employee->wage = filter_input(INPUT_POST, 'wage', FILTER_VALIDATE_INT);
        if ($employee->wage)
            $employee->wage = trim($employee->wage);

        $employee->room = filter_input(INPUT_POST,'room');
        if ($employee->room){
            $employee->room = trim($employee->room);
        }

        $employee->login = filter_input(INPUT_POST, 'login');
        if ($employee->login)
            $employee->login = trim($employee->login);

        $employee->password = filter_input(INPUT_POST, 'password');
        if ($employee->password){
            $employee->password = trim($employee->password);
        }

        $employee->admin = filter_input(INPUT_POST, 'admin', FILTER_VALIDATE_INT);
        if ($employee->admin)
            $employee->admin = trim($employee->admin);

        return $employee;
    }
}