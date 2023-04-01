<?php
session_start();
if($_SESSION['login'] == null){
    header("Location: ../login.php?");
    exit();
}

require_once __DIR__ . "/../bootstrap/bootstrap.php";

class IndexPage extends BasePage
{
    public function __construct()
    {
        $this->title = "Prohlížeč databáze firmy";
    }

    protected function pageBody()
    {
        return "";
    }

}

$page = new IndexPage();
$page->render();

?>