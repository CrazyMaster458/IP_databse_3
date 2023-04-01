<?php
require_once __DIR__ . "/../bootstrap/bootstrap.php";

class ChangeLogPage extends CRUDPage
{
    private $users;
    private $name;
    private ?array $errors = [];

    public function __construct()
    {
        $this->title = "Prohlížeč databáze firmy";
    }

    protected function pageHeader(): string
    {
        return "";
    }

    protected function prepare(): void
    {
        //když chce formulář
        if (isset($_POST['name']) && isset($_POST['password']) && isset($_POST['oldPass'])) {
            function validate($data)
            {
                $data = trim($data);
                $data = stripslashes($data);
                $data = htmlspecialchars($data);
                return $data;
            }

            $this->name = validate($_POST['name']);
            $oldPass = validate($_POST['oldPass']);
            $pass = validate($_POST['password']);



            if (empty($this->name)) {
                $this->errors['name'] = 'Jméno nesmí být prázdné';
            }
            if (empty($pass)) {
                $this->errors['password'] = 'Password musí být vyplněno';
            }
            if(empty($oldPass)){
                $this->errors['password'] = 'Password musí být vyplněno';
            }
            elseif(strlen($pass) < 6){
                $this->errors['password'] = 'Password musí být delší než 6 znaků';

            }
            elseif (strlen($oldPass) < 6){
                $this->errors['oldPass'] = 'Password musí být delší než 6 znaků';

            }
            else {
                $options = [
                    'cost' => 12,
                ];

                $this->users = Employee::findUser($_SESSION['login'], $oldPass);

                $this->users->login = $this->name;
                $this->users->password = password_hash($pass, PASSWORD_BCRYPT, $options);

                $success = $this->users->update();
                $this->redirect(self::ACTION_UPDATE, $success, "no");

                if ($this->users) {
                    $this->errors = [];
                    $_SESSION['login'] = $this->users->login;
                    $_SESSION['name'] = $this->users->name;
                    $_SESSION['surname'] = $this->users->surname;
                    $_SESSION['password'] = $this->users->password;
                    $_SESSION['admin'] = $this->users->admin;
                    $_SESSION['employee_id'] = $this->users->employee_id;
                    header("Location: index.php");
                    exit;
                } else {
                    echo 'Invalid password.';
                }
            }
        }
    }


    protected function pageBody()
    {
        return MustacheProvider::get()->render(
            'changeLog',
            [
                'errors' => $this->errors,
                'name' =>$this->name
            ]
        );
    }
}

$page = new ChangeLogPage();
$page->render();

