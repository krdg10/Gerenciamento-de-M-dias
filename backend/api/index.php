<?php
require "../start.php";

use Src\Imovel;
use Src\Arquivo;

/*header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");*/

if (isset($_SERVER['HTTP_ORIGIN'])) {
    // Decide if the origin in $_SERVER['HTTP_ORIGIN'] is one
    // you want to allow, and if so:
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 86400');    // cache for 1 day
}

// Access-Control headers are received during OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {

    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
        // may also be using PUT, PATCH, HEAD etc
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");

    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
        header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");

    exit(0);
}

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode('/', $uri);

$requestMethod = $_SERVER["REQUEST_METHOD"];
$postId = null;
$busca = null;

if ($uri[1] == 'imovel') {


    if ($uri[2] == 'novo') {
    } else if ($uri[2] == 'editar') {
        if (!isset($uri[3])) {
            header("HTTP/1.1 404 Not Found");
            exit();
        }
        $postId = (int) $uri[3];
    } else if ($uri[2] == 'deletarImovel') {
        if (!isset($uri[3])) {
            header("HTTP/1.1 404 Not Found");
            exit();
        }
        $postId = (int) $uri[3];
    } else  if ($uri[2] == 'buscarTodos') {
    } else if ($uri[2] == 'busca') {
        if (!isset($uri[3])) {
            header("HTTP/1.1 404 Not Found");
            exit();
        }
        $busca = $uri[3];
    } else if ($uri[2] == 'alterarTag') {
        if (!isset($uri[3])) {
            header("HTTP/1.1 404 Not Found");
            exit();
        }
        $postId = (int) $uri[3];
    } else {
        header("HTTP/1.1 404 Not Found");
        exit();
    }
    $controller = new Imovel($dbConnection, $requestMethod, $postId, $busca, $uri[2]);
    $controller->processRequest();
} else if ($uri[1] == 'arquivo') {
    if ($uri[2] == 'novoArquivo') {
        if (!isset($_FILES['uploadedFile']) || $_FILES['uploadedFile']['error'] !== UPLOAD_ERR_OK) {
            $message = 'There is some error in the file upload. Please check the following error.<br>';
            $message .= 'Error:' . $_FILES['uploadedFile']['error'];
            echo $message;
            exit();
        }
    } else if ($uri[2] == 'editar') {
        if (!isset($uri[3])) {
            header("HTTP/1.1 404 Not Found");
            exit();
        }
        $postId = (int) $uri[3];
    } else if ($uri[2] == 'deletarArquivo') {
        if (!isset($uri[3])) {
            header("HTTP/1.1 404 Not Found");
            exit();
        }
        $postId = (int) $uri[3];
    } else if ($uri[2] == 'buscarTodos') {
    } else if ($uri[2] == 'busca') {
        if (!isset($uri[3])) {
            header("HTTP/1.1 404 Not Found");
            exit();
        }
        $busca = $uri[3];
    }
    $controller = new Arquivo($dbConnection, $requestMethod, $postId, $busca, $uri[2]);
    $controller->processRequest();
} else {
    header("HTTP/1.1 404 Not Found");
    exit();
}
