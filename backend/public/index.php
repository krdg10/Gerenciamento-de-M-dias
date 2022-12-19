<?php
require "../start.php";

use Src\Imovel;
use Src\Arquivo;
use Src\User;
// dar uma melhorada nesse código. nos outros do back tbm e revisar os do front.

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
$tags = null;

if ($uri[1] == 'imovel') {
    if ($uri[2] == 'novo' || $uri[2] == 'buscarTodosValidos' || $uri[2] == 'buscarTodosInvalidos' || $uri[2] == 'buscarTodosValidosEInvalidos' || $uri[2] == 'numeroDeAtivos' || $uri[2] == 'numeroDeInativos') {
    } else if ($uri[2] == 'alterarTag' || $uri[2] == 'editar' || $uri[2] == 'desassociarTodosDocumentos' || $uri[2] == 'deletarTodosDocumentosAssociados' || $uri[2] == 'deletarImovel' ||  $uri[2] == 'deletarImovelPermanente' || $uri[2] == 'reativarImovel') {
        if (!isset($uri[3])) {
            header("HTTP/1.1 404 Not Found");
            echo 'Pagina nao encontrada';
            exit();
        }
        $postId = (int) $uri[3];
    } else if ($uri[2] == 'busca') {
        if (!isset($uri[3]) || !isset($uri[4]) || !isset($uri[5]) || !isset($uri[6])) {
            header("HTTP/1.1 404 Not Found");
            echo 'Pagina nao encontrada';
            exit();
        }
        $busca = [$uri[3], $uri[4]];
        $offset = (int) $uri[5];
        $limit = (int) $uri[6];
    } else if ($uri[2] == 'imoveisPaginadosAtivos' || $uri[2] == 'imoveisPaginadosInativos') {
        if (!isset($uri[3]) || !isset($uri[4])) {
            header("HTTP/1.1 404 Not Found");
            echo 'Pagina nao encontrada';
            exit();
        }
        $offset = (int) $uri[3];
        $limit = (int) $uri[4];
    } else if ($uri[2] == 'imoveisPaginadosComFiltro') {
        if (!isset($uri[3]) || !isset($uri[4]) || !isset($uri[5]) || !isset($uri[6]) || !isset($uri[7])) {
            header("HTTP/1.1 404 Not Found");
            echo 'Pagina nao encontrada';
            exit();
        }
        $offset = (int) $uri[3];
        $limit = (int) $uri[4];
        $tags = (object) ['urgente' => (int) $uri[5], 'favorito' => (int) $uri[6], 'importante' => (int) $uri[7]];
    } else if ($uri[2] == 'buscarTodosValidosComFiltro') {
        if (!isset($uri[3]) || !isset($uri[4]) || !isset($uri[5]) || !isset($uri[6]) || !isset($uri[7]) || !isset($uri[8]) || !isset($uri[9])) {
            header("HTTP/1.1 404 Not Found");
            echo 'Pagina nao encontrada';
            exit();
        }
        $busca = [$uri[3], $uri[4]];
        $offset = (int) $uri[5];
        $limit = (int) $uri[6];
        $tags = (object) ['urgente' => (int) $uri[7], 'favorito' => (int) $uri[8], 'importante' => (int) $uri[9]];
    } else {
        header("HTTP/1.1 404 Not Found");
        echo 'Pagina nao encontrada';
        exit();
    }
    $controller = new Imovel($dbConnection, $requestMethod, $postId, $busca, $uri[2], $offset, $limit, $tags);
    $controller->processRequest();
} else if ($uri[1] == 'arquivo') {
    if ($uri[2] == 'novoArquivo') {
        if (!isset($_FILES['uploadedFile']) || $_FILES['uploadedFile']['error'] !== UPLOAD_ERR_OK) {
            $message = 'There is some error in the file upload. Please check the following error. ';
            $message .= 'Error:' . $_FILES['uploadedFile']['error'];
            header("HTTP/1.1 404 Not Found");
            echo $message;
            exit();
        }
    } else if ($uri[2] == 'deletarArquivo' || $uri[2] == 'reativarArquivo' || $uri[2] == 'deletarArquivoPermanente' || $uri[2] == 'editar') {
        if (!isset($uri[3])) {
            header("HTTP/1.1 404 Not Found");
            echo 'Pagina nao encontrada';
            exit();
        }
        $postId = (int) $uri[3];
    } else if ($uri[2] == 'buscarTodosValidos' || $uri[2] == 'buscarTodosInvalidos' || $uri[2] == 'numeroDeAtivos' || $uri[2] == 'numeroDeInativos' || $uri[2] == 'numeroDeSemImovel') {
    } else if ($uri[2] == 'arquivosPaginadosAtivos' || $uri[2] == 'arquivosPaginadosInativos' || $uri[2] == 'ativosSemImovel') {
        if (!isset($uri[3]) || !isset($uri[4])) {
            header("HTTP/1.1 404 Not Found");
            echo 'Pagina nao encontrada';
            exit();
        }
        $offset = (int) $uri[3];
        $limit = (int) $uri[4];
    } else if ($uri[2] == 'buscaNome' || $uri[2] == 'buscaImovel') {
        if (!isset($uri[3]) || !isset($uri[4]) || !isset($uri[5]) || !isset($uri[6])) {
            header("HTTP/1.1 404 Not Found");
            echo 'Pagina nao encontrada';
            exit();
        }
        $busca = [$uri[3], $uri[4]];
        $offset = (int) $uri[5];
        $limit = (int) $uri[6];
    } else {
        header("HTTP/1.1 404 Not Found");
        echo 'Pagina nao encontrada';
        exit();
    }
    $controller = new Arquivo($dbConnection, $requestMethod, $postId, $busca, $uri[2], $offset, $limit);
    $controller->processRequest();
} else if ($uri[1] == 'user') {
    $email = null;
    $password = null;
    if ($uri[2] == 'login') {
        $input = (array) json_decode(file_get_contents('php://input'), TRUE);
        if (!isset($input['password']) || !isset($input['email'])) {
            $message = 'Without password or email';
            header("HTTP/1.1 404 Not Found");
            echo $message;
            exit();
        }
        $email =  $_POST['email'];
        $password = $_POST['password'];
    } else if ($uri[2] == 'editPassword') {
        $input = (array) json_decode(file_get_contents('php://input'), TRUE);
        if (!isset($input['password']) || !isset($input['email']) || !isset($input['newPassword'])) {
            $message = 'Without password or email';
            header("HTTP/1.1 404 Not Found");
            echo $message;
            exit();
        }
    } else if ($uri[2] == 'newUser') {
        if (!isset($_POST['password']) || !isset($_POST['email']) || !isset($_POST['type'])) {
            $message = 'Withou password or email or type';
            header("HTTP/1.1 404 Not Found");
            echo $message;
            exit();
        }
        $email =  $_POST['email'];
        $password = $_POST['password'];
    } else if ($uri[2] == 'deleteUser') {
        $input = (array) json_decode(file_get_contents('php://input'), TRUE);
        if (!isset($input['passwordAdm']) || !isset($input['emailAdm']) || !isset($input['emailUser'])) {
            $message = 'Withou password or email or type';
            header("HTTP/1.1 404 Not Found");
            echo $message;
            exit();
        }
    }


    $controller = new User($dbConnection, $requestMethod, $uri[2], $email, $password);
    $controller->processRequest();
} else {
    header("HTTP/1.1 404 Not Found");
    echo 'Pagina nao encontrada';
    exit();
}
