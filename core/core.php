<?php

error_reporting(E_ERROR | E_PARSE);
require "classDatabase.php";
require "classFile.php";
require "classBuilder.php";
require "../vendor/autoload.php";

$db = new Database();
$file = new File();
$builder = new Builder();

function connect()
{
    global $db;
    $host       = $_POST["host"];
    $username   = $_POST["username"];
    $password   = $_POST["password"];
    $database   = $_POST["database"];

    $db->setHost($host);
    $db->setUsename($username);
    $db->setPassword($password);
    $db->setDatabase($database);
    $db->connection();
}

if (isset($_POST["action"])) {
    $action = $_POST["action"];

    if ($action == 'getConnection') {
        $file->setPath($_POST["path"]);

        if (!is_dir($file->getPath()) || !file_exists($file->getPath() . "/.env")) {
            $data = [
                "success" => false,
                "message" => "directory or file .env in '" . $file->getPath() . "' not found"
            ];
        } else {
            $dotenv = Dotenv\Dotenv::createImmutable($file->getPath());
            $dotenv->load();

            $data = [
                "host"      => $_SERVER["database.default.hostname"],
                "username"  => $_SERVER["database.default.username"],
                "password"  => $_SERVER["database.default.password"],
                "database"  => $_SERVER["database.default.database"]
            ];
        }

        echo json_encode($data);
    }

    if ($action == 'checkConnection') {
        connect();
        $data = [];

        if ($db->checkConnection()) {
            $data["success"] = false;
            $data["message"] = 'The connection error: ' . mysqli_connect_error();
        } else {
            $data["success"] = true;
            $data["message"] = 'The connection success';
        }

        echo json_encode($data);
    }

    if ($action == 'checkPermission') {

        $file->setPath($_POST["path"]);
        $folder_array = [
            "app/Controllers",
            "app/Models",
            "app/Views"
        ];
        $data = [];

        if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
            @exec("find " . $file->getPath() . "/app/Controllers -type d -exec chmod 0777 {} +");
            @exec("find " . $file->getPath() . "/app/Models -type d -exec chmod 0777 {} +");
            @exec("find " . $file->getPath() . "/app/Views -type d -exec chmod 0777 {} +");
        }

        foreach ($folder_array as $folder) {
            if ($file->isPermissionWritable($folder)) {
                $data["success"] = true;
                $data["message"] = "All directory it is writable";
            } else {
                $data["success"] = false;
                $data["message"] = "directory \"$folder\" it is not writable";
                break;
            }
        }

        echo json_encode($data);
    }

    if ($action == 'getTable') {
        connect();

        foreach ($db->getTable() as $table) {
            echo "<option value='$table[0]'>$table[0]</option>";
        }
    }

    if ($action == 'getDescriptionTable') {
        connect();
        $table = $_POST["table"];
        $data = "";

        foreach ($db->getDescTable($table) as $key => $value) {
            $data .= "<tr>";
            $data .= "  <td><div class='d-flex justify-content-center'><input type='checkbox' class='mx-auto form-check-input position-static checksingle' value='$value[Field]' /></div></td>";
            $data .= "  <td>$value[Field]</td>";
            $data .= "  <td>$value[Type]</td>";
            $data .= "  <td>$value[Null]</td>";
            $data .= "  <td>$value[Key]</td>";
            $data .= "  <td>$value[Default]</td>";
            $data .= "  <td>$value[Extra]</td>";
            $data .= "</tr>";
        }

        echo $data;
    }

    if ($action == 'getPrimaryKey') {
        connect();
        $table = $_POST["table"];
        foreach ($db->getPrimaryKey($table) as $primary) {
            echo $primary["Column_name"];
        }
    }

    if ($action == 'build') {

        $array_data = ["nmfile", "select", "primarykey", "title", "table"];
        $data_model = [];
        $data_controller = [];
        $data_view = [];

        foreach ($array_data as $data) {
            $data_model[$data] = $_POST["data"][$data];
            $data_controller[$data] = $_POST["data"][$data];
            $data_view[$data] = $_POST["data"][$data];
        }

        $path               = $_POST["data"]["path"];
        $namefileController = ucfirst(preg_replace('/[^A-Za-z0-9\-]/', '', strtolower($_POST["data"]["nmfile"])));
        $namefileModel      = ucfirst(preg_replace('/[^A-Za-z0-9\-]/', '', strtolower($_POST["data"]["nmfile"]))) . "Model";
        $namefileView       = preg_replace('/[^A-Za-z0-9\-]/', '', strtolower($_POST["data"]["nmfile"]));

        $model      = $builder->Model($data_model);
        $controller = $builder->Controller($data_controller);
        $view       = $builder->View($data_view);

        $data = [];

        if (
            $file->createFile("$path/app/Controllers/$namefileController.php", $controller) &&
            $file->createFile("$path/app/Models/$namefileModel.php", $model) &&
            $file->createFile("$path/app/Views/$namefileView.php", $view)
        ) {
            $data["success"] = true;
            $data["message"] = "Success build it";
        } else {
            $data["success"] = false;
            $data["message"] = "Failed build it";
        }

        echo json_encode($data);
    }
}
