<?php

require_once __DIR__ . "/../bootstrap/bootstrap.php";

class LoginPage extends BasePage
{
    private $users;
    private ?array $errors = [];
    private $name;

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
        if (isset($_POST['name']) && isset($_POST['password'])) {
            function validate($data){
                $data = trim($data);
                $data = stripslashes($data);
                $data = htmlspecialchars($data);
                return $data;
            }

            $this->name = validate($_POST['name']);
            $pass = validate($_POST['password']);

            if(empty($this->name)){
                $this->errors['name'] = 'Jméno nesmí být prázdné';
            }
            if(empty($pass)){
                $this->errors['password'] = 'Password musí být vyplněno';
            }
            else if(strlen($pass) < 6){
                $this->errors['password'] = 'Password musí být delší než 6 znaků';
            }
            else{
                $this->users = Employee::findUser($this->name, $pass);

                if ($this->users) {
                    session_start();
                    $this->errors = [];
                    $_SESSION['login'] = $this->users->login;
                    $_SESSION['name'] = $this->users->name;
                    $_SESSION['surname'] = $this->users->surname;
                    $_SESSION['password'] = $this->users->password;
                    $_SESSION['admin'] = $this->users->admin;
                    $_SESSION['employee_id'] = $this->users->employee_id;
                    header("Location: index.php");
                    exit;
                }else {
                    echo 'Invalid password.';
                }
            }
        }
    }


    protected function pageBody()
    {
        return MustacheProvider::get()->render(
            'login',
          [
              'errors' => $this->errors,
              'name' =>$this->name
          ]
        );
    }
}

$page = new LoginPage();
$page->render();

?>