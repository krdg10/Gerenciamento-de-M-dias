<?php

namespace Src;

include 'DataValidator/DataValidator.php';
// > Criar tela de Login no front.
// > Fazer proteção... tudo, ver como fica.
// E ai a partir disso ir adicionando token nas funções enquanto trabalho front e back. 
// Por exemplo, carregar imoveis. E ai mexo no front e na função do back
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Throwable;

// fazer update senha do user, fazer dados do put aparecer.
// fazer delete de user
// funcionou delete... só ver se tá bloqueando os casos certos

// criar token na hora de logar --> feito
// validar token antes das requisições que precisam
// verificar tipo na hora do token

// front.
// é isso... hoje podia ter avançado mais, 2h e tal, mas surtei. 

// ai fazer um valide token pras outras classes/funções, de arquivo e imovel tal, tem que receber token e ver se é valido
// https://github.com/aleduca/jwt-php/blob/master/backend/public/auth.php só isso aqui aparentemnte?? segundo o video é isso mesmo
//vamos testando
// testar se mostra se o token é valido, enfim, testar no geral pra entender como é.


// e ai integrar com front... fazer tudo no front na vdd, onde guardar o token, o que fazer com ele, etc
class User
{
    private $db;
    private $requestMethod;
    private $url;
    private $email;
    private $password;


    public function __construct($db, $requestMethod, $url, $email, $password)
    {
        $this->db = $db;
        $this->requestMethod = $requestMethod;
        $this->url = $url;
        $this->email = $email;
        $this->password = $password;
    }

    public function processRequest()
    {
        switch ($this->requestMethod) {
            case 'GET':
                break;
            case 'POST':
                if ($this->url == 'login') {
                    $response = $this->login();
                } else if ($this->url == 'newUser') {
                    $response = $this->createUser($this->email, $this->password);
                } else {
                    //
                }
                break;
            case 'PUT':
                if ($this->url == 'editPassword') {
                    $response = $this->editPassword();
                } else {
                    //
                }
                break;
            case 'DELETE':
                if ($this->url == 'deleteUser') {
                    $response = $this->deleteUser();
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



    private function createUser($email, $password)
    {
        $validation = $this->validateUser($_POST);

        if (is_array($validation)) {
            return $this->returnValidationErrors($validation);
        }


        $result = $this->findEmail($email);
        if ($result) {
            return $this->notFoundResponse('Email já utilizado');
        }

        if (!isset($_POST['data_criacao'])) {
            $data = date("Y-m-d h:i:s");
        } else {
            $data = $_POST['data_criacao'];
        };

        $type = $_POST['type'];

        $query = "INSERT INTO users (password, email, data_criacao, type) VALUES(:password, :email, :data, :type);";

        $passwordHashed = password_hash($password, PASSWORD_DEFAULT);

        try {
            $statement = $this->db->prepare($query);
            $statement->execute(array(
                'password' => $passwordHashed,
                'email'  => $email,
                'data' => $data,
                'type' => $type
            ));
            $statement->rowCount();
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
        $response['status_code_header'] = 'HTTP/1.1 201 Created';
        $response['body'] = json_encode(array('message' => 'User Created'));
        return $response;
        // talvez no final fazer um login
    }

    private function editPassword()
    {
        $token = $this->validateToken();

        $input = (array) json_decode(file_get_contents('php://input'), TRUE);
        // colocar os dados desse input nas coisas
        $validation = $this->validateUser($input);

        if (is_array($validation)) {
            return $this->returnValidationErrors($validation);
        }


        $result = $this->findEmail($input['email']);
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

        $query = "UPDATE users set password = :password, data_edicao = :data where email = :email;";
        $passwordHashed = password_hash($input['newPassword'], PASSWORD_DEFAULT);

        try {
            $statement = $this->db->prepare($query);
            $statement->execute(array(
                'password' => $passwordHashed,
                'email'  => $input['email'],
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


    private function deleteUser()
    {
        $token = $this->validateToken();


        $input = (array) json_decode(file_get_contents('php://input'), TRUE);

        $result = $this->findEmail($input['emailAdm']);
        if (!$result) {
            return $this->notFoundResponse('Email inválido');
        }
        if ($result["type"] != 'adm') {
            return $this->notFoundResponse('User without permission');
        }

        if (!password_verify($input['passwordAdm'], $result["password"])) {
            return $this->notFoundResponse('Senha Inválida');
        }

        $result = $this->findEmail($input['emailUser']);
        if (!$result) {
            return $this->notFoundResponse('Email a ser excluído inválido');
        }

        if ($result["type"] == 'adm') {
            return $this->notFoundResponse('User without permission');
        }


        $email = $input['emailUser'];


        $query = "DELETE FROM users WHERE email = '$email';";


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

        // criar endereço na api e testar no postman. metodo post, mandar dados como data e nao por endereço ou algo assim
        // ver se senha vem no result e comparar. se for diferente... senha invalida.
        // se der certo... ai partir token. seguir passos do video e do git.

        // https://github.com/aleduca/jwt-php/blob/master/backend/public/login.php
        // https://www.youtube.com/watch?v=P4bAs4Mz6m4

        // dei uma planejada... seguindo  esses passos tá ok. 
        // foda que empenho de 20 minutos... muito pouco. na proxima focar pelo menos 2h plmdds, ta paia já.
        // mas melhor que nada e hoje nao foi tao a toa. planejar era uma passo que eu tava enrolando. pelo menos ja planejei
        // agora por mao na massa seguindo essa ordem ai
        // foda que na proxima tem limpeza ainda... vamos ver
        // 15 dias que terminei a parte normal e to enrolando pro login...  dia 26 por ai. 15 dias. ja era pra ter terminado...
        // tem isso, tem flutter e tem começar o negocio com Maria. to realmente com tarefas e planos e projetos e to enrolando.
        // fazer isso... ai flutter genshin e tal, fazer rodar no meu celular e atualizar os dados. dps dar uma atualizar pra nao perder manha 
        // editar algo pra n fazer manha, seja no genshin ou no jokenpo, dps de fazer funcionar no meu cel e att dados do genshin 
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
