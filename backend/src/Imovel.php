<?php

namespace Src;

class Imovel
{
    private $db;
    private $requestMethod;
    private $imovelId;
    private $busca;
    private $url;
    private $offset;
    private $limit;

    public function __construct($db, $requestMethod, $imovelId, $busca, $url, $offset, $limit)
    {
        $this->db = $db;
        $this->requestMethod = $requestMethod;
        $this->imovelId = $imovelId;
        $this->busca = $busca;
        $this->url = $url;
        $this->offset = $offset;
        $this->limit = $limit;
    }

    public function processRequest()
    {
        switch ($this->requestMethod) {
            case 'GET':
                if ($this->imovelId) {
                    $response = $this->getImovel($this->imovelId);
                } else if ($this->busca) {
                    $response = $this->getImoveisByName($this->busca, $this->offset, $this->limit);
                } else {
                    if ($this->url == 'buscarTodosValidos') {
                        $response = $this->getAllImovels('A');
                    } else if ($this->url == 'imoveisPaginadosAtivos') {
                        $response = $this->getAllImovelsInPages('A', $this->offset, $this->limit);
                    } else if ($this->url == 'imoveisPaginadosInativos') {
                        $response = $this->getAllImovelsInPages('I', $this->offset, $this->limit);
                    } else if ($this->url == 'buscarTodosInvalidos') {
                        $response = $this->getAllImovels('I');
                    } else if ($this->url == 'buscarTodosValidosEInvalidos') {
                        $response = $this->getAllImovels(null);
                    } else if ($this->url == 'numeroDeAtivos') {
                        $response = $this->getNumeroImoveisAtivosOuInativos('A');
                    } else { //numeros de inativos
                        $response = $this->getNumeroImoveisAtivosOuInativos('I');
                    }
                };
                break;
            case 'POST':
                $response = $this->createImovel();
                break;
            case 'PUT':
                if ($this->url == 'deletarImovel') {
                    $response = $this->deletarOuReativarImovel('I', $this->imovelId);
                } else if ($this->url == 'reativarImovel') {
                    $response = $this->deletarOuReativarImovel('A', $this->imovelId);
                } else if ($this->url == 'alterarTag') {
                    $response = $this->updateTag($this->imovelId);
                } else if ($this->url == 'desassociarTodosDocumentos') {
                    $response = $this->desassociaDocumentos($this->imovelId, true);
                } else if ($this->url == 'deletarTodosDocumentosAssociados') {
                    $response = $this->deletaDocumentos($this->imovelId, 'inativar', true);
                } else {
                    $response = $this->updateImovel($this->imovelId);
                }
                break;
            case 'DELETE':
                $response = $this->deletePermanente($this->imovelId);
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

    private function getAllImovels($status)
    {
        $query = "SELECT imovel.*, tag.favorito, tag.importante, tag.urgente FROM imoveis imovel, tags tag where imovel.ativo = '$status' and imovel.tags_id = tag.id;";

        if (!isset($status)) {
            $query = "SELECT imovel.*, tag.favorito, tag.importante, tag.urgente FROM imoveis imovel, tags tag where imovel.tags_id = tag.id;";
        }

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

    private function getAllImovelsInPages($status, $offset, $limit)
    {
        $query = "SELECT imovel.*, tag.favorito, tag.importante, tag.urgente FROM imoveis imovel, tags tag where imovel.ativo = '$status' and imovel.tags_id = tag.id ORDER BY imovel.id LIMIT $limit OFFSET $offset;";

        if (!isset($status)) {
            $query = "SELECT imovel.*, tag.favorito, tag.importante, tag.urgente FROM imoveis imovel, tags tag where imovel.tags_id = tag.id ORDER BY imovel.nome LIMIT $limit OFFSET $offset;";
        }

        $queryTotal = "SELECT COUNT(*) totalImoveis FROM imoveis where ativo = '$status';";

        try {
            $statement = $this->db->query($query);
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            $statement = $this->db->query($queryTotal);
            $total = $statement->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
        $object = (object) ['totalImoveis' => $total[0], 'resultado' => $result];

        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($object);
        return $response;
    }

    private function getNumeroImoveisAtivosOuInativos($status)
    {
        $query = "SELECT count(*) numero FROM imoveis where ativo = '$status';";

        try {
            $statement = $this->db->query($query);
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }

        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result[0]);
        return $response;
    }


    private function getImoveisByName($busca, $offset, $limit)
    {

        $keywords = $busca[0];
        $status = $busca[1];

        $query = "
      SELECT
      imovel.*, tag.favorito, tag.importante, tag.urgente
      FROM
      imoveis imovel, tags tag where LOWER(imovel.nome) LIKE LOWER('%$keywords%') and imovel.ativo = '$status' and imovel.tags_id = tag.id LIMIT $limit OFFSET $offset;
      ";

        $queryTotal = "SELECT COUNT(*) totalImoveis FROM imoveis where ativo = '$status' and  LOWER(nome) LIKE LOWER('%$keywords%');";



        try {
            $statement = $this->db->query($query);
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            $statement = $this->db->query($queryTotal);
            $total = $statement->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
        $object = (object) ['totalImoveis' => $total[0], 'resultado' => $result];

        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($object);
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

    private function updateTag($id)
    {
        $result = $this->find($id);
        if (!$result) {
            return $this->notFoundResponse();
        }
        $input = (array) json_decode(file_get_contents('php://input'), TRUE);
        // validar o input
        if ($input['type'] == 'urgente') {
            $statement = "
            UPDATE tags
            SET
            urgente = :value,
            data_edicao = :hora
            WHERE id = :id;
          ";
        } else if ($input['type'] == 'importante') {
            $statement = "
            UPDATE tags
            SET
            importante = :value,
            data_edicao = :hora
            WHERE id = :id;
          ";
        } else {
            $statement = "
            UPDATE tags
            SET
            favorito = :value,
            data_edicao = :hora
            WHERE id = :id;
          ";
        }
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                'value' => $input['value'],
                'hora' => $input['hora'],
                'id' => $id
            ));
            $statement->rowCount();
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode(array('message' => 'Tag Updated!'));
        return $response;
    }

    private function deletarOuReativarImovel($tipo, $id)
    {
        if ($tipo == 'I') {
            $result = $this->find($id);
            if (!$result) {
                return $this->notFoundResponse();
            }

            $input = (array) json_decode(file_get_contents('php://input'), TRUE);

            if ($input["tipoDelete"] == "desassociaDocumentos") {
                $this->desassociaDocumentos($id, false);
            } else {
                $this->deletaDocumentos($id, 'inativar', false);
            }
        }

        $query = "UPDATE imoveis set ativo = '$tipo' WHERE id = :id;";

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

    private function deletePermanente($id)
    {
        $input = (array) json_decode(file_get_contents('php://input'), TRUE);

        if ($input["tipoDelete"] == "desassociaDocumentos") {
            $this->desassociaDocumentos($id, false);
        } else {
            $this->deletaDocumentos($id, 'permanente', false);
        }

        $query = "DELETE FROM imoveis WHERE id = $id;";

        try {
            $statement = $this->db->query($query);
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }

        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode('Imóvel deletado permanentemente com sucesso');
        return $response;
    }

    private function desassociaDocumentos($id, $urlTrueOrFalse)
    {
        $query = "UPDATE arquivos set imovel_id = null WHERE imovel_id = $id;";


        try {
            $statement = $this->db->query($query);
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            if (!$urlTrueOrFalse) {
                return $result;
            }
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode('Documentos do imóvel desassociados com sucesso');
        return $response;
    }

    private function deletaDocumentos($id, $tipo, $urlTrueOrFalse)
    {
        if ($tipo == 'permanente') {
            $query = "DELETE FROM arquivos WHERE imovel_id = $id;";
        } else {
            $query = "UPDATE arquivos SET ativo = 'I' WHERE imovel_id = $id;";
        }

        try {
            $statement = $this->db->query($query);
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            if (!$urlTrueOrFalse) {
                return $result;
            }
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode('Documentos do imóvel apagados com sucesso');
        return $response;
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
