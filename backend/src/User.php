<?php

namespace Src;

include 'DataValidator/DataValidator.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Throwable;

class User
{
    private $db;
    private $requestMethod;
    private $url;
    private $offset;
    private $limit;


    public function __construct($db, $requestMethod, $url, $offset, $limit)
    {
        $this->db = $db;
        $this->requestMethod = $requestMethod;
        $this->url = $url;
        $this->offset = $offset;
        $this->limit = $limit;
    }

    public function processRequest()
    {
        switch ($this->requestMethod) {
            case 'GET':
                if ($this->url == 'listUsers') {
                    $response = $this->getUsers($this->offset, $this->limit);
                } else if ($this->url == 'verifyToken') {
                    $response = $this->verifyToken();
                }
                break;
            case 'POST':
                if ($this->url == 'login') {
                    $response = $this->login();
                } else {
                    $response = $this->createUser();
                }
                break;
            case 'PUT':
                if ($this->url == 'editPassword') {
                    $response = $this->editPassword();
                } else {
                    $response = $this->alterRole();
                }
                break;
            case 'DELETE':
                if ($this->url == 'deleteMyUser') {
                    $response = $this->deleteMyUser();
                } else {
                    $response = $this->deleteAccountByMaster();
                }
                break;
            default:
                $response = $this->notFoundResponse('Error');
                break;
        }
        header($response['status_code_header']);
        if ($response['body']) {
            echo $response['body'];
        }
    }

    private function getUsers($offset, $limit)
    {

        $token = $this->validateToken();

        $result = $this->findEmail($token->email);
        if ($result["type"] != 'master') {
            return $this->notFoundResponse('Not found');
        }


        $query = "SELECT email, type, data_edicao, data_criacao FROM users where type <> 'master' ORDER BY email LIMIT $limit OFFSET $offset;";
        $queryTotal = "SELECT COUNT(*) totalUsers FROM users  where type <> 'master';";

        try {
            $statement = $this->db->query($query);
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            $statement = $this->db->query($queryTotal);
            $total =  $statement->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
        $object = (object) ['totalUsers' => $total[0], 'resultado' => $result];

        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($object);

        return $response;
    }

    private function createUser()
    {
        $input = (array) json_decode(file_get_contents('php://input'), TRUE);

        $validation = $this->validateUser($input);

        if (is_array($validation)) {
            return $this->returnValidationErrors($validation);
        }


        $result = $this->findEmail($input['email']);
        if ($result) {
            return $this->notFoundResponse('Email já utilizado');
        }

        if (!isset($_POST['data_criacao'])) {
            $data = date("Y-m-d h:i:s");
        } else {
            $data = $_POST['data_criacao'];
        };


        $query = "INSERT INTO users (password, email, data_criacao, type) VALUES(:password, :email, :data, :type);";

        $passwordHashed = password_hash($input['password'], PASSWORD_DEFAULT);

        try {
            $statement = $this->db->prepare($query);
            $statement->execute(array(
                'password' => $passwordHashed,
                'email'  => $input['email'],
                'data' => $data,
                'type' => 'notadm'
            ));
            $statement->rowCount();
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }

        $token = $this->createToken($input['email']);
        $response['status_code_header'] = 'HTTP/1.1 201 Created';
        $response['body'] = json_encode(array(
            'message' => 'User created',
            'token' => $token,
            'type' => 'notadm'
        ));
        return $response;
    }

    private function alterRole()
    {
        $token = $this->validateToken();

        $result = $this->findEmail($token->email);
        if ($result["type"] != 'master') {
            return $this->notFoundResponse('Not found');
        }

        $input = (array) json_decode(file_get_contents('php://input'), TRUE);

        if ($input['email'] == $token->email) {
            return $this->notFoundResponse('Impossível realizar solicitação');
        }
        $newRole = 'notadm';

        if ($input['type'] == 'notadm') {
            $newRole = 'adm';
        }

        if (!isset($input['data_edicao'])) {
            $data = date("Y-m-d h:i:s");
        } else {
            $data = $input['data_edicao'];
        };

        $query = "UPDATE users set type = :type, data_edicao = :data where email = :email;";

        try {
            $statement = $this->db->prepare($query);
            $statement->execute(array(
                'type' => $newRole,
                'email'  => $input['email'],
                'data' => $data
            ));
            $statement->rowCount();
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
        $response['status_code_header'] = 'HTTP/1.1 201 Created';
        $response['body'] = json_encode(array('message' => 'Type Updated', 'newType' => $newRole));
        return $response;
    }


    private function deleteAccountByMaster()
    {
        $token = $this->validateToken();

        $result = $this->findEmail($token->email);
        if ($result["type"] != 'master') {
            return $this->notFoundResponse('Not found');
        }

        $input = (array) json_decode(file_get_contents('php://input'), TRUE);

        $query = "DELETE FROM users where email = :email;";

        try {
            $statement = $this->db->prepare($query);
            $statement->execute(array(
                'email'  => $input['email'],
            ));
            $statement->rowCount();
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
        $response['status_code_header'] = 'HTTP/1.1 201 Created';
        $response['body'] = json_encode(array('message' => 'User Deleted'));
        return $response;
    }


    private function editPassword()
    {
        $token = $this->validateToken();

        $input = (array) json_decode(file_get_contents('php://input'), TRUE);

        $result = $this->findEmail($token->email);
        if (!$result) {
            return $this->notFoundResponse('Email inexistente');
        }

        if (!isset($input['data_edicao'])) {
            $data = date("Y-m-d h:i:s");
        } else {
            $data = $input['data_edicao'];
        };

        if (!password_verify($input['password'], $result["password"])) {
            return $this->notFoundResponse('Senha Inválida');
        }

        if (strlen($input['newPassword']) > 50 || strlen($input['newPassword']) < 9) {
            return $this->notFoundResponse('Nova Senha Fora das Normas');
        }

        $query = "UPDATE users set password = :password, data_edicao = :data where email = :email;";
        $passwordHashed = password_hash($input['newPassword'], PASSWORD_DEFAULT);

        try {
            $statement = $this->db->prepare($query);
            $statement->execute(array(
                'password' => $passwordHashed,
                'email'  => $result['email'],
                'data' => $data
            ));
            $statement->rowCount();
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
        $response['status_code_header'] = 'HTTP/1.1 201 Created';
        $response['body'] = json_encode(array('message' => 'Password Updated'));
        return $response;
    }


    private function deleteMyUser()
    {
        $token = $this->validateToken();

        $result = $this->findEmail($token->email);
        if (!$result) {
            return $this->notFoundResponse('Email inválido');
        }

        if ($result["type"] == 'master') {
            return $this->notFoundResponse('User without permission');
        }

        $query = "DELETE FROM users WHERE email = '$token->email';";

        try {
            $statement = $this->db->query($query);
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }

        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode('Usuário deletado permanentemente com sucesso');
        return $response;
    }

    private function login()
    {
        $input = (array) json_decode(file_get_contents('php://input'), TRUE);

        $validation = $this->validateUser($input);

        if (is_array($validation)) {
            return $this->returnValidationErrors($validation);
        }

        $result = $this->findEmail($input['email']);
        if (!$result) {
            return $this->notFoundResponse('Email inválido');
        }

        if (!password_verify($input['password'], $result["password"])) {
            return $this->notFoundResponse('Senha Inválida');
        }

        $token = $this->createToken($input['email']);
        $response['status_code_header'] = 'HTTP/1.1 201 Created';
        $response['body'] = json_encode(array(
            'message' => 'User Logged In',
            'token' => $token,
            'type' => $result['type']
        ));

        return $response;
    }

    private function findEmail($email)
    {
        $query = "SELECT * FROM users WHERE email = '$email'";

        try {
            $statement = $this->db->prepare($query);
            $statement->execute();
            $result = $statement->fetch(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }

    private function createToken($email)
    {
        $payload = [
            'exp' => time() + 1000000,
            'iat' => time(),
            'email' => $email,
        ];

        $encode = JWT::encode($payload, $_ENV['KEY'], 'HS256');
        return $encode;
    }

    private function verifyToken()
    {
        $token = $this->validateToken();

        $result = $this->findEmail($token->email);
        if (!$result) {
            return $this->notFoundResponse('Email inexistente');
        }

        $response['status_code_header'] = 'HTTP/1.1 201 Created';
        $response['body'] = json_encode(array(
            'message' => 'Valid Token', 'type' => $result["type"]
        ));

        return $response;
    }

    private function decodeToken($token)
    {
        try {
            $decoded = JWT::decode($token, new Key($_SERVER['KEY'], 'HS256'));
        } catch (Throwable $e) {
            if ($e->getMessage() === 'Expired token') {
                http_response_code(401);
                die('Please, log in on the application.');
            } else {
                http_response_code(401);
                die($e->getMessage());
            }
        }
        return $decoded;
    }

    public function validateToken()
    {
        if (!isset($_SERVER['HTTP_AUTHORIZATION'])) {
            http_response_code(401);
            die('Without authorization.');
        }
        $authorization = $_SERVER['HTTP_AUTHORIZATION'];
        $token = str_replace('Bearer ', '', $authorization);
        $decodedToken = $this->decodeToken($token);
        return $decodedToken;
    }

    private function validateUser($input)
    {
        $validate = new Data_Validator();
        $validate->set('email', $input['email'])->is_required()->max_length('50')->is_email()
            ->set('password', $input['password'])->is_required()->max_length('50');

        if ($validate->validate()) {
            return  true;
        } else {
            return $validate->get_errors();
        }
    }

    private function notFoundResponse($message)
    {
        $response['status_code_header'] = 'HTTP/1.1 404 Not Found';
        $response['body'] = json_encode([
            $message
        ]);
        return $response;
    }

    private function returnValidationErrors($input)
    {
        $response['status_code_header'] = 'HTTP/1.1 422 Unprocessable Entity';
        $response['body'] = json_encode([
            $input
        ]);

        return $response;
    }
}
