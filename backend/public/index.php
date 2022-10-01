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
$offset = null;
$limit = null;

if ($uri[1] == 'imovel') {
    if ($uri[2] == 'novo') {
    } else if ($uri[2] == 'editar' || $uri[2] == 'desassociarTodosDocumentos' || $uri[2] == 'deletarTodosDocumentosAssociados') {
        if (!isset($uri[3])) {
            header("HTTP/1.1 404 Not Found");
            exit();
        }
        $postId = (int) $uri[3];
    } else if ($uri[2] == 'deletarImovel' ||  $uri[2] == 'deletarImovelPermanente' || $uri[2] == 'reativarImovel') {
        if (!isset($uri[3])) {
            header("HTTP/1.1 404 Not Found");
            exit();
        }
        $postId = (int) $uri[3];
    } else  if ($uri[2] == 'buscarTodosValidos' || $uri[2] == 'buscarTodosInvalidos' || $uri[2] == 'buscarTodosValidosEInvalidos' || $uri[2] == 'numeroDeAtivos' || $uri[2] == 'numeroDeInativos') {
    } else if ($uri[2] == 'busca') {
        if (!isset($uri[3]) || !isset($uri[4]) || !isset($uri[5]) || !isset($uri[6])) {
            header("HTTP/1.1 404 Not Found");
            exit();
        }
        $busca = [$uri[3], $uri[4]];
        $offset = (int) $uri[5];
        $limit = (int) $uri[6];
    } else if ($uri[2] == 'alterarTag') {
        if (!isset($uri[3])) {
            header("HTTP/1.1 404 Not Found");
            exit();
        }
        $postId = (int) $uri[3];
    } else if ($uri[2] == 'imoveisPaginadosAtivos' || $uri[2] == 'imoveisPaginadosInativos') {
        if (!isset($uri[3]) || !isset($uri[4])) {
            header("HTTP/1.1 404 Not Found");
            exit();
        }
        $offset = (int) $uri[3];
        $limit = (int) $uri[4];
    } else {
        header("HTTP/1.1 404 Not Found");
        exit();
    }
    $controller = new Imovel($dbConnection, $requestMethod, $postId, $busca, $uri[2], $offset, $limit);
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
    } else if ($uri[2] == 'deletarArquivo' || $uri[2] == 'reativarArquivo' || $uri[2] == 'deletarArquivoPermanente') {
        if (!isset($uri[3])) {
            header("HTTP/1.1 404 Not Found");
            exit();
        }
        $postId = (int) $uri[3];
    } else if ($uri[2] == 'buscarTodosValidos' || $uri[2] == 'buscarTodosInvalidos' || $uri[2] == 'numeroDeAtivos' || $uri[2] == 'numeroDeInativos' || $uri[2] == 'numeroSemImovel' || $uri[2] == 'ativosSemImovel') {
    } else if ($uri[2] == 'buscaNome' || $uri[2] == 'buscaImovel') {
        if (!isset($uri[3]) || !isset($uri[4])) {
            header("HTTP/1.1 404 Not Found");
            exit();
        }
        $busca = [$uri[3], $uri[4]];
    }
    $controller = new Arquivo($dbConnection, $requestMethod, $postId, $busca, $uri[2]);
    $controller->processRequest();
} else if ($uri[1] == 'public') {

    var_dump($_SERVER['DOCUMENT_ROOT']);
    exit();
} else {
    header("HTTP/1.1 404 Not Found");
    exit();
}
