<?php
require "../start.php";

use Src\Imovel;

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
// endpoints starting with `/post` or `/posts` for GET shows all posts
// everything else results in a 404 Not Found
if ($uri[1] !== 'imovel') {
    if ($uri[1] !== 'imoveis') {
        header("HTTP/1.1 404 Not Found");
        exit();
    }
}

// endpoints starting with `/posts` for POST/PUT/DELETE results in a 404 Not Found
if ($uri[1] == 'imoveis' and isset($uri[2])) {
    header("HTTP/1.1 404 Not Found");
    exit();
}

// the post id is, of course, optional and must be a number
$postId = null;
if (isset($uri[2])) {
    $postId = (int) $uri[2];

}

$requestMethod = $_SERVER["REQUEST_METHOD"];

// pass the request method and post ID to the Post and process the HTTP request:
$controller = new Imovel($dbConnection, $requestMethod, $postId);
$controller->processRequest();
