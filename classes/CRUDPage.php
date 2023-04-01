<?php

session_start();

if($_SESSION['login'] == null){
    header("Location: ../login.php?");
    exit();
}


abstract class CRUDPage extends BasePage
{
    public const STATE_FORM_REQUESTED = 0;
    public const STATE_DATA_SENT = 1;

    public const ACTION_INSERT = "insert";
    public const ACTION_UPDATE = "update";
    public const ACTION_DELETE = "delete";

    protected function redirect(string $action, bool $success, string $version = "v1") : void
    {
        $data = [
            'action' => $action,
            'success' => $success ? 1 : 0
        ];
        if($version == "v1"){
            header('Location: list.php?'. http_build_query($data));
            exit;
        }
        else if($version == "v2"){
            header('Location: list.php?');
            exit;
        }
        else{
            http_build_query($data);
        }
    }
}