<?php

namespace Src;

include 'DataValidator/DataValidator.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Throwable;

class Imovel
{
    private $db;
    private $requestMethod;
    private $imovelId;
    private $busca;
    private $url;
    private $offset;
    private $limit;
    private $tags;

    public function __construct($db, $requestMethod, $imovelId, $busca, $url, $offset, $limit, $tags)
    {
        $this->db = $db;
        $this->requestMethod = $requestMethod;
        $this->imovelId = $imovelId;
        $this->busca = $busca;
        $this->url = $url;
        $this->offset = $offset;
        $this->limit = $limit;
        $this->tags = $tags;
    }

    public function processRequest()
    {
        switch ($this->requestMethod) {
            case 'GET':
                if ($this->imovelId) {
                    $response = $this->getImovel($this->imovelId);
                } else if ($this->busca) {
                    if ($this->url == 'buscarTodosValidosComFiltro') {
                        $response = $this->getImoveisByName($this->busca, $this->offset, $this->limit, $this->tags);
                    } else {
                        $response = $this->getImoveisByName($this->busca, $this->offset, $this->limit, null);
                    }
                } else {
                    if ($this->url == 'buscarTodosValidos') {
                        $response = $this->getAllImovels('A');
                    } else if ($this->url == 'imoveisPaginadosAtivos') {
                        $response = $this->getAllImovelsInPages('A', $this->offset, $this->limit, null);
                    } else if ($this->url == 'imoveisPaginadosInativos') {
                        $response = $this->getAllImovelsInPages('I', $this->offset, $this->limit, null);
                    } else if ($this->url == 'imoveisPaginadosComFiltro') {
                        $response = $this->getAllImovelsInPages('A', $this->offset, $this->limit, $this->tags);
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
                $response = $this->notFoundResponse('Error');
                break;
        }
        header($response['status_code_header']);
        if ($response['body']) {
            echo $response['body'];
        }
    }

    private function getAllImovels($status)
    {
        $result = $this->verifyTokenAndEmail(false);
        if ($result != 'verdadeiro') {
            return $result;
        }

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

    private function getAllImovelsInPages($status, $offset, $limit, $tags)
    {
        $result = $this->verifyTokenAndEmail(false);
        if ($result != 'verdadeiro') {
            return $result;
        }

        $query = "SELECT imovel.*, tag.favorito, tag.importante, tag.urgente FROM imoveis imovel, tags tag where imovel.ativo = '$status' and imovel.tags_id = tag.id ORDER BY imovel.id LIMIT $limit OFFSET $offset;";

        $queryTotal = "SELECT COUNT(*) totalImoveis FROM imoveis where ativo = '$status';";

        if (isset($tags)) {
            $partialQuery = '';

            $favorito = $tags->favorito;
            $importante = $tags->importante;
            $urgente = $tags->urgente;

            if ($favorito == 1) {
                $partialQuery .= ' and tag.favorito = 1';
            }
            if ($importante == 1) {
                $partialQuery .= ' and tag.importante = 1';
            }
            if ($urgente == 1) {
                $partialQuery .= ' and tag.urgente = 1';
            }

            $query = "SELECT imovel.*, tag.favorito, tag.importante, tag.urgente FROM imoveis imovel, tags tag where imovel.ativo = '$status' and imovel.tags_id = tag.id $partialQuery ORDER BY imovel.id LIMIT $limit OFFSET $offset;";

            $queryTotal = "SELECT COUNT(*) totalImoveis  FROM imoveis imovel, tags tag where imovel.ativo = '$status' and imovel.tags_id = tag.id $partialQuery;";
        }


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
        $result = $this->verifyTokenAndEmail(false);
        if ($result != 'verdadeiro') {
            return $result;
        }

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


    private function getImoveisByName($busca, $offset, $limit, $tags)
    {
        $result = $this->verifyTokenAndEmail(false);
        if ($result != 'verdadeiro') {
            return $result;
        }

        $keywords = $busca[0];
        $status = $busca[1];

        $query = "SELECT imovel.*, tag.favorito, tag.importante, tag.urgente FROM imoveis imovel, tags tag where LOWER(imovel.nome) LIKE LOWER('%$keywords%') and imovel.ativo = '$status' and imovel.tags_id = tag.id LIMIT $limit OFFSET $offset;";

        $queryTotal = "SELECT COUNT(*) totalImoveis FROM imoveis where ativo = '$status' and  LOWER(nome) LIKE LOWER('%$keywords%');";

        if (isset($tags)) {
            $partialQuery = '';

            $favorito = $tags->favorito;
            $importante = $tags->importante;
            $urgente = $tags->urgente;

            if ($favorito == 1) {
                $partialQuery .= ' and tag.favorito = 1';
            }
            if ($importante == 1) {
                $partialQuery .= ' and tag.importante = 1';
            }
            if ($urgente == 1) {
                $partialQuery .= ' and tag.urgente = 1';
            }

            $query = "SELECT imovel.*, tag.favorito, tag.importante, tag.urgente FROM imoveis imovel, tags tag where LOWER(imovel.nome) LIKE LOWER('%$keywords%') and imovel.ativo = '$status' and imovel.tags_id = tag.id $partialQuery LIMIT $limit OFFSET $offset;";

            $queryTotal = "SELECT COUNT(*) totalImoveis FROM imoveis imovel, tags tag where imovel.ativo = '$status' and  LOWER(imovel.nome) LIKE LOWER('%$keywords%') and imovel.tags_id = tag.id  $partialQuery;";
        }

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
    {
        $result = $this->verifyTokenAndEmail(false);
        if ($result != 'verdadeiro') {
            return $result;
        }

        $result = $this->find($id, 'A', 'imoveis');
        if (!$result) {
            return $this->notFoundResponse('Imovel Inexistente');
        }
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
        return $response;
    }


    private function decodeToken($token)
    {
        try {
            $decoded = JWT::decode($token, new Key($_SERVER['KEY'], 'HS256'));
        } catch (Throwable $e) {
            if ($e->getMessage() === 'Expired token') {
                $response['status_code_header'] = 'HTTP/1.1 401 Unathourized';
                $response['body'] = json_encode(array('message' => 'Expired Token'));
                return $response;
            } else {
                $response['status_code_header'] = 'HTTP/1.1 401 Unathourized';
                $response['body'] = json_encode(array('message' => $e->getMessage()));
                return $response;
            }
        }
        return $decoded;
    }

    public function validateToken()
    {
        $headers = apache_request_headers();
        if (!$headers["Authorization"]) {
            $response['status_code_header'] = 'HTTP/1.1 401 Unathourized';
            $response['body'] = json_encode(array('message' => 'Sem autenticação'));
            return $response;
        }
        $authorization = $headers["Authorization"];
        $token = str_replace('Bearer ', '', $authorization);
        $decodedToken = $this->decodeToken($token);
        return $decodedToken;
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

    private function verifyTokenAndEmail($requiresAdm)
    {
        $valideToken = $this->validateToken();
        if (!is_object($valideToken)) {
            return $valideToken;
        }

        $result = $this->findEmail($valideToken->email);
        if (!$result) {
            $response['status_code_header'] = 'HTTP/1.1 401 Unathourized';
            $response['body'] = json_encode(array('message' => 'Sem autenticação'));
            return $response;
        }
        if ($requiresAdm) {
            if ($result['type'] != "adm" && $result['type'] != "master") {
                $response['status_code_header'] = 'HTTP/1.1 401 Unathourized';
                $response['body'] = json_encode(array('message' => 'Sem autenticação'));
                return $response;
            }
        }
        return 'verdadeiro';
    }

    private function createImovel()
    {
        $result = $this->verifyTokenAndEmail(true);
        if ($result != 'verdadeiro') {
            return $result;
        }

        $input = (array) json_decode(file_get_contents('php://input'), TRUE);
        $validation = $this->validateImovel($input, 'POST');
        if (is_array($validation)) {
            return $this->returnValidationErrors($validation);
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

        $queryImovel = "INSERT INTO imoveis (nome, data_cadastro, descricao, preco, cep, rua, bairro, numero, cidade, estado, complemento, tags_id) VALUES (:nome, :data_cadastro, :descricao, :preco, :cep, :rua, :bairro, :numero, :cidade, :estado, :complemento, :tags_id);";
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

    private function updateImovel($id)
    {
        $result = $this->verifyTokenAndEmail(true);
        if ($result != 'verdadeiro') {
            return $result;
        }

        $result = $this->find($id, 'A', 'imoveis');
        if (!$result) {
            return $this->notFoundResponse('Imovel Inexistente');
        }
        $input = (array) json_decode(file_get_contents('php://input'), TRUE);
        $validation = $this->validateImovel($input, 'PUT');
        if (is_array($validation)) {
            return $this->returnValidationErrors($validation);
        }

        $statement = "UPDATE imoveis SET nome = :nome, descricao = :descricao, preco = :preco, cep = :cep, rua = :rua, bairro = :bairro, numero = :numero, cidade = :cidade, estado = :estado, complemento = :complemento, data_edicao = :data_edicao WHERE id = :id;";

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
        $result = $this->verifyTokenAndEmail(true);
        if ($result != 'verdadeiro') {
            return $result;
        }

        $result = $this->find($id, 'A', 'tags');
        if (!$result) {
            return $this->notFoundResponse('Tag Inexistente');
        }

        $input = (array) json_decode(file_get_contents('php://input'), TRUE);

        if (!isset($input['type'])) {
            if ($input['type'] != 'urgente' && $input['type'] != 'importante' && $input['type'] != 'favorito') {
                return $this->notFoundResponse('Sem data necessária para update das tags');
            }
        }

        if ($input['type'] == 'urgente') {
            $statement = "UPDATE tags SET urgente = :value, data_edicao = :hora WHERE id = :id;";
        } else if ($input['type'] == 'importante') {
            $statement = "UPDATE tags SET importante = :value, data_edicao = :hora WHERE id = :id;";
        } else {
            $statement = "UPDATE tags SET favorito = :value, data_edicao = :hora WHERE id = :id;";
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
        $result = $this->verifyTokenAndEmail(true);
        if ($result != 'verdadeiro') {
            return $result;
        }

        $tipoBusca = 'A';
        if ($tipo == 'A') {
            $tipoBusca = 'I';
        }

        $result = $this->find($id, $tipoBusca, 'imoveis');
        if (!$result) {
            return $this->notFoundResponse('Imovel Inexistente');
        }

        if ($tipo == 'I') {
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

    public function find($id, $tipo, $table)
    {
        $query = "SELECT * FROM $table WHERE id = :id and ativo = '$tipo';";

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
        $typeOfDate = 'data_cadastro';
        if ($tipo == 'PUT') {
            $typeOfDate = 'data_edicao';
        }

        $validate = new Data_Validator();
        $validate->set('nome', $input['nome'])->is_required()->max_length('50')
            ->set('preco', $input['preco'])->is_required()->min_value('1')->is_num()
            ->set('cep', $input['cep'])->is_required()->is_zipCode()
            ->set('rua', $input['rua'])->is_required()->max_length('50')
            ->set('bairro', $input['bairro'])->is_required()->max_length('50')
            ->set('numero', $input['numero'])->max_length('50')
            ->set('cidade', $input['cidade'])->is_required()->max_length('50')
            ->set('estado', $input['estado'])->is_required()->exact_length('2')
            ->set('complemento', $input['complemento'])->max_length('50')
            ->set($typeOfDate, $input[$typeOfDate])->is_required()->is_date();


        if ($validate->validate()) {
            return  true;
        } else {
            return $validate->get_errors();
        }
    }

    private function deletePermanente($id)
    {
        $result = $this->verifyTokenAndEmail(true);
        if ($result != 'verdadeiro') {
            return $result;
        }

        $result = $this->find($id, 'I', 'imoveis');
        if (!$result) {
            return $this->notFoundResponse('Imovel Inexistente');
        }

        $id_tag = $result["tags_id"];
        $input = (array) json_decode(file_get_contents('php://input'), TRUE);

        if ($input["tipoDelete"] == "desassociaDocumentos") {
            $this->desassociaDocumentos($id, false);
        } else {
            $this->deletaDocumentos($id, 'permanente', false);
        }

        $query = "DELETE FROM imoveis WHERE id = $id;";
        $queryTags = "DELETE FROM tags WHERE id = $id_tag;";


        try {
            $statement = $this->db->query($query);
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            $statement = $this->db->query($queryTags);
            $resultTags =  $statement->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }

        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode('Imóvel deletado permanentemente com sucesso');
        return $response;
    }

    private function desassociaDocumentos($id, $urlTrueOrFalse)
    {
        $result = $this->verifyTokenAndEmail(true);
        if ($result != 'verdadeiro') {
            return $result;
        }

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
        $result = $this->verifyTokenAndEmail(true);
        if ($result != 'verdadeiro') {
            return $result;
        }

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

    private function returnValidationErrors($input)
    {
        $response['status_code_header'] = 'HTTP/1.1 422 Unprocessable Entity';
        $response['body'] = json_encode([
            $input
        ]);

        return $response;
    }

    private function notFoundResponse($message)
    {
        $response['status_code_header'] = 'HTTP/1.1 404 Not Found';
        $response['body'] = json_encode([
            $message
        ]);
        return $response;
    }
}
