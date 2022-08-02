<?php

namespace Src;

class Imovel
{
    private $db;
    private $requestMethod;
    private $imovelId;
    private $busca;
    private $url;

    public function __construct($db, $requestMethod, $imovelId, $busca, $url)
    {
        $this->db = $db;
        $this->requestMethod = $requestMethod;
        $this->imovelId = $imovelId;
        $this->busca = $busca;
        $this->url = $url;
    }

    public function processRequest()
    {
        switch ($this->requestMethod) {
            case 'GET':
                if ($this->imovelId) {
                    $response = $this->getImovel($this->imovelId);
                } else if ($this->busca) {
                    $response = $this->getImoveisByName($this->busca);
                } else {
                    $response = $this->getAllImovels();
                };
                break;
            case 'POST':
                $response = $this->createImovel();
                break;
            case 'PUT':
                if ($this->url == 'deletarImovel') {
                    $response = $this->deleteImovel($this->imovelId);
                } else {
                    $response = $this->updateImovel($this->imovelId);
                }
                break;
            case 'DELETE':
                break;
            default:
                $response = $this->notFoundResponse();
                break;
        }
        header($response['status_code_header']);
        if ($response['body']) {
            echo $response['body'];
        }
    }

    private function getAllImovels()
    {
        $query = "
      SELECT
          *
      FROM
          imoveis where ativo = 'A';
    ";

        try {
            $statement = $this->db->query($query);
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }

        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
        return $response;
    }

    private function getImoveisByName($busca)
    {
        $query = "
      SELECT
          *
      FROM
          imoveis where LOWER(nome) LIKE LOWER('%$busca%') and ativo = 'A';
    ";

        try {
            $statement = $this->db->query($query);
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }

        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
        return $response;
    }

    private function getImovel($id)
    { ///////// ver aqui pra verificar se tá ativo
        $result = $this->find($id);
        if (!$result) {
            return $this->notFoundResponse();
        }
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
        return $response;
    }

    private function createImovel()
    {
        $input = (array) json_decode(file_get_contents('php://input'), TRUE);
        if (!$this->validateImovel($input, 'POST')) {
            return $this->unprocessableEntityResponse();
        }

        $queryTag = "INSERT INTO tags (data_criacao) VALUES(:data_criacao);";

        try {
            $statement = $this->db->prepare($queryTag);
            $statement->execute(array(
                'data_criacao'  => $input['data_cadastro'],
            ));
            $lastInsert = $this->db->lastInsertId('tags');
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }

        $queryImovel = "
      INSERT INTO imoveis
          (nome, data_cadastro, descricao, preco, cep, rua, bairro, numero, cidade, estado, complemento, tags_id)
      VALUES
      (:nome, :data_cadastro, :descricao, :preco, :cep, :rua, :bairro, :numero, :cidade, :estado, :complemento, :tags_id);
      ";
        ////////// fazer pegar a data de cadastro automaticamente. current hora e tal. também colocar um formulario de edição
        try {
            $statement = $this->db->prepare($queryImovel);
            $statement->execute(array(
                'nome' => $input['nome'],
                'data_cadastro'  => $input['data_cadastro'],
                'descricao' => $input['descricao'],
                'preco' => $input['preco'],
                'cep' => $input['cep'],
                'rua' => $input['rua'],
                'bairro' => $input['bairro'],
                'numero' => $input['numero'],
                'cidade' => $input['cidade'],
                'estado' => $input['estado'],
                'complemento' => $input['complemento'],
                'tags_id' => $lastInsert,
            ));
            $statement->rowCount();
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }

        $response['status_code_header'] = 'HTTP/1.1 201 Created';
        $response['body'] = json_encode(array('message' => 'Imovel Created'));
        return $response;
    }

    ////////////////////////////////////////////////////////////////
    // Fazer Crud pra arquivos
    // fazer front
    // melhor tratamento de erros desse crud aqui
    ////////////////////////////////////////

    private function updateImovel($id)
    {
        $result = $this->find($id);
        if (!$result) {
            return $this->notFoundResponse();
        }
        $input = (array) json_decode(file_get_contents('php://input'), TRUE);
        if (!$this->validateImovel($input, 'PUT')) {
            return $this->unprocessableEntityResponse();
        }

        $statement = "
      UPDATE imoveis
      SET
        nome = :nome,
        descricao = :descricao,
        preco = :preco,
        cep = :cep,
        rua = :rua,
        bairro = :bairro,
        numero = :numero,
        cidade = :cidade,
        estado = :estado,
        complemento = :complemento,
        data_edicao = :data_edicao
      WHERE id = :id;
    ";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                'nome' => $input['nome'],
                'descricao' => $input['descricao'],
                'preco' => $input['preco'],
                'cep' => $input['cep'],
                'rua' => $input['rua'],
                'bairro' => $input['bairro'],
                'numero' => $input['numero'],
                'cidade' => $input['cidade'],
                'estado' => $input['estado'],
                'complemento' => $input['complemento'],
                'data_edicao' => $input['data_edicao'],
                'id' => $id
            ));
            $statement->rowCount();
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode(array('message' => 'Imovel Updated!'));
        return $response;
    }

    private function deleteImovel($id)
    {
        $result = $this->find($id);
        if (!$result) {
            return $this->notFoundResponse();
        }

        $query = "
      UPDATE imoveis
      set
      ativo = 'I'
      WHERE id = :id;
    ";

        try {
            $statement = $this->db->prepare($query);
            $statement->execute(array('id' => $id));
            $statement->rowCount();
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode(array('message' => 'Imovel Deleted!'));
        return $response;
    }

    public function find($id)
    {
        $query = "
      SELECT
          *
      FROM
          imoveis
      WHERE id = :id and ativo = 'A';
    ";

        try {
            $statement = $this->db->prepare($query);
            $statement->execute(array('id' => $id));
            $result = $statement->fetch(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }

    private function validateImovel($input, $tipo)
    {
        if ($tipo == 'POST') {
            if (
                !isset($input['nome']) || !isset($input['data_cadastro']) || !isset($input['descricao']) || !isset($input['preco'])  || !isset($input['cep'])
                || !isset($input['rua'])  || !isset($input['bairro'])  || !isset($input['cidade'])  || !isset($input['estado'])
            ) {
                return false;
            }
        } else if ($tipo == 'PUT') {
            if (
                !isset($input['nome']) || !isset($input['data_edicao']) || !isset($input['descricao']) || !isset($input['preco'])  || !isset($input['cep'])
                || !isset($input['rua'])  || !isset($input['bairro'])  || !isset($input['cidade'])  || !isset($input['estado'])
            ) {
                return false;
            }
        }



        return true;
    }

    private function unprocessableEntityResponse()
    {
        $response['status_code_header'] = 'HTTP/1.1 422 Unprocessable Entity';
        $response['body'] = json_encode([
            'error' => 'Invalid input'
        ]);
        return $response;
    }

    private function notFoundResponse()
    {
        $response['status_code_header'] = 'HTTP/1.1 404 Not Found';
        $response['body'] = null;
        return $response;
    }
}
