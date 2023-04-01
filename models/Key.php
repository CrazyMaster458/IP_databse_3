<?php

class Key
{
    public ?int $key_id;
    public ?int $employee;
    public ?int $room;
    public ?string $employeeName;
    public ?string $roomName;

    /**
     * @param int|null $key_id
     * @param int|null $employee
     * @param int|null $room
     * @param string|null $employeeName
     * @param string|null $roomName
     */
    public function __construct(?int $key_id = null, ?int $employee = null, ?int $room = null, ?string $employeeName = null, ?string $roomName = null)
    {
        $this->key_id = $key_id;
        $this->employee = $employee;
        $this->room = $room;
        $this->employeeName = $employeeName;
        $this->roomName = $roomName;
    }

    public static function findByID(int $id, string $type) : ?array
    {
        if($type == "room") {
            $stmt = PDOProvider::get()->prepare("SELECT `key_id`, k.`employee`, k.`room`, e.`name` as `employeeName`, e.`surname` , r.`name` as `roomName` FROM `employee` e join `key` k on (e.employee_id = k.employee) JOIN `room` r on (k.room = r.room_id) WHERE k.`room` = ".$id." ORDER BY e.`name`");

        }
        else if($type == "employee") {
            $stmt = PDOProvider::get()->prepare("SELECT `key_id`, k.`employee`, k.`room`, e.`name` as `employeeName`, e.`surname` , r.`name` as `roomName` FROM `employee` e join `key` k on (e.employee_id = k.employee) JOIN `room` r on (k.room = r.room_id) WHERE k.`employee` = ".$id." ORDER BY r.`name`");
        }
        else {
            exit();
        }

        $stmt->execute([]);

        if ($stmt->rowCount() < 1)
            return null;

        $keys = [];
        while ($keyData = $stmt->fetch())
        {
            $key = new Key();
            $key->hydrate($keyData);
            $keys[] = $key;
        }

        return $keys;
    }

    /**
     * @return Key[]
     */
    public static function getAll() : array
    {
//        $sortSQL = "";
//        if (count($sorting))
//        {
//            $SQLchunks = [];
//            foreach ($sorting as $field => $direction)
//                $SQLchunks[] = "`{$field}` {$direction}";
//
//            $sortSQL = " ORDER BY " . implode(', ', $SQLchunks);
//        }

        $stmt = PDOProvider::get()->prepare("SELECT `key_id`, k.`employee`, k.`room`, e.`name` as `employeeName`, e.`surname` , r.`name` as `roomName` FROM `employee` e join `key` k on (e.employee_id = k.employee) JOIN `room` r on (k.room = r.room_id) ORDER BY r.`name`");
        $stmt->execute([]);

        $keys = [];
        while ($keyData = $stmt->fetch())
        {
            $key = new Key();
            $key->hydrate($keyData);
            $keys[] = $key;
        }

        return $keys;
    }

    private function hydrate(array|object $data)
    {
        $fields = ['key_id', 'employee', 'room', 'surname', 'employeeName', 'employeeSurname', 'roomName'];
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
        $query = "INSERT INTO `key` (`employee`, `room`) VALUES (:employee, :room)";
        $stmt = PDOProvider::get()->prepare($query);
        $result = $stmt->execute(['employee'=>$this->employee, 'room'=>$this->room]);

        if (!$result)
            return false;

        $this->key_id = PDOProvider::get()->lastInsertId();
        return true;
    }

    public static function deleteByID(int $id) : bool
    {
        $query = "DELETE FROM `key` WHERE `key_id` = ".$id;
        $stmt = PDOProvider::get()->prepare($query);
        return $stmt->execute([]);
    }
    public static function deleteAll(int $id) : bool
    {
        $query = "DELETE FROM `key` WHERE `employee` = ".$id;
        $stmt = PDOProvider::get()->prepare($query);
        return $stmt->execute([]);
    }

    public static function readPost(string $room2, int $emplo2 = 0) : self
    {
        $key = new Key();

        if($emplo2 != 0){
            $key->employee = $emplo2;
        }
        else{
            $key->employee = filter_input(INPUT_POST, 'employee_id');
            if ($key->employee)
                $key->employee = trim($key->employee);
        }

        $key->room = $room2;
        if ($key->room)
            $key->room = trim($key->room);

        return $key;
    }
}