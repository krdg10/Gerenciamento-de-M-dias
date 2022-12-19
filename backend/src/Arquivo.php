<?php

namespace Src;

include 'DataValidator/DataValidator.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Throwable;


class Arquivo
{

    private $db;
    private $requestMethod;
    private $arquivoId;
    private $busca;
    private $url;
    private $offset;
    private $limit;

    public function __construct($db, $requestMethod, $arquivoId, $busca, $url, $offset, $limit)
    {
        $this->db = $db;
        $this->requestMethod = $requestMethod;
        $this->arquivoId = $arquivoId;
        $this->busca = $busca;
        $this->url = $url;
        $this->offset = $offset;
        $this->limit = $limit;
    }

    public function processRequest()
    {
        switch ($this->requestMethod) {
            case 'GET':
                if ($this->arquivoId) {
                    $response = $this->getArquivo($this->arquivoId);
                } else if ($this->busca) {
                    if ($this->url == 'buscaNome') {
                        $response = $this->getArquivosByName($this->busca, $this->offset, $this->limit);
                    } else {
                        $response = $this->getArquivosByImovel($this->busca, $this->offset, $this->limit);
                    }
                } else {
                    if ($this->url == 'buscarTodosValidos') {
                        $response = $this->getAllArquivos('A');
                    } else if ($this->url == 'buscarTodosInvalidos') {
                        $response = $this->getAllArquivos('I');
                    } else if ($this->url == 'ativosSemImovel') {
                        $response = $this->getAllArquivosInPages('semImovel', $this->offset, $this->limit);
                    } else if ($this->url == 'arquivosPaginadosAtivos') {
                        $response = $this->getAllArquivosInPages('A', $this->offset, $this->limit);
                    } else if ($this->url == 'arquivosPaginadosInativos') {
                        $response = $this->getAllArquivosInPages('I', $this->offset, $this->limit);
                    } else if ($this->url == 'numeroDeAtivos') {
                        $response = $this->getNumeroArquivosAtivosOuInativos('A');
                    } else if ($this->url == 'numeroDeInativos') {
                        $response = $this->getNumeroArquivosAtivosOuInativos('I');
                    } else {
                        $response = $this->getNumeroArquivosSemImovel();
                    }
                }
                break;
            case 'POST':
                $response = $this->createArquivo();
                break;
            case 'PUT':
                if ($this->url == 'deletarArquivo') {
                    $response = $this->deletarOuReativarArquivo('I', $this->arquivoId);
                } else if ($this->url == 'reativarArquivo') {
                    $response = $this->deletarOuReativarArquivo('A', $this->arquivoId);
                } else {
                    $response = $this->updateArquivo($this->arquivoId);
                }
                break;
            case 'DELETE':
                $response = $this->deletePermanente($this->arquivoId);
                break;
            default:
                break;
        }
        header($response['status_code_header']);
        if ($response['body']) {
            echo $response['body'];
        }
    }

    private function getAllArquivos($status)
    {
        $result = $this->verifyTokenAndEmail(false);
        if ($result != 'verdadeiro') {
            return $result;
        }

        $query = "SELECT * FROM arquivos where ativo = '$status';";

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

    private function getAllArquivosInPages($status, $offset, $limit)
    {
        $result = $this->verifyTokenAndEmail(false);
        if ($result != 'verdadeiro') {
            return $result;
        }

        if ($status == 'semImovel') {
            $query = "SELECT * FROM arquivos where ativo = 'A' AND imovel_id IS NULL ORDER BY id LIMIT $limit OFFSET $offset;";
            $queryTotal = "SELECT COUNT(*) totalImoveis FROM arquivos where ativo = 'A' AND imovel_id IS NULL;";
        } else {
            $query = "SELECT * FROM arquivos where ativo = '$status' ORDER BY id LIMIT $limit OFFSET $offset;";
            $queryTotal = "SELECT COUNT(*) totalImoveis FROM arquivos where ativo = '$status';";
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


    private function getNumeroArquivosAtivosOuInativos($status)
    {
        $result = $this->verifyTokenAndEmail(false);
        if ($result != 'verdadeiro') {
            return $result;
        }

        $query = "SELECT count(*) numero FROM arquivos where ativo = '$status';";

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

    private function getNumeroArquivosSemImovel()
    {
        $result = $this->verifyTokenAndEmail(false);
        if ($result != 'verdadeiro') {
            return $result;
        }

        $query = "SELECT count(*) numero FROM arquivos where ativo = 'A' and imovel_id IS NULL;";

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


    private function getArquivosByName($busca, $offset, $limit)
    {
        $result = $this->verifyTokenAndEmail(false);
        if ($result != 'verdadeiro') {
            return $result;
        }

        $keywords = $busca[0];
        $status = $busca[1];
        $query = "SELECT * FROM arquivos where LOWER(nome) LIKE LOWER('%$keywords%') and ativo = '$status' ORDER BY id LIMIT $limit OFFSET $offset;";
        $queryTotal = "SELECT COUNT(*) totalImoveis FROM arquivos where LOWER(nome) LIKE LOWER('%$keywords%') and ativo = '$status';";

        try {
            $statement = $this->db->query($query);
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            $statement = $this->db->query($queryTotal);
            $total =  $statement->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
        $object = (object) ['totalImoveis' => $total[0], 'resultado' => $result];

        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($object);
        return $response;
    }

    private function getArquivosByImovel($busca,  $offset, $limit)
    {
        $result = $this->verifyTokenAndEmail(false);
        if ($result != 'verdadeiro') {
            return $result;
        }

        $keywords = $busca[0];
        $status = $busca[1];
        $query = "SELECT * FROM arquivos where imovel_id = $keywords and ativo = '$status' ORDER BY id LIMIT $limit OFFSET $offset;";
        $queryTotal = "SELECT COUNT(*) totalImoveis FROM arquivos where imovel_id = $keywords and ativo = '$status';";


        try {
            $statement = $this->db->query($query);
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            $statement = $this->db->query($queryTotal);
            $total =  $statement->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
        $object = (object) ['totalImoveis' => $total[0], 'resultado' => $result];

        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($object);
        return $response;
    }

    private function getArquivo($id)
    {
        $result = $this->verifyTokenAndEmail(false);
        if ($result != 'verdadeiro') {
            return $result;
        }

        $result = $this->find($id, 'A');
        if (!$result) {
            return $this->notFoundResponse('Arquivo Inexistente');
        }
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
        return $response;
    }

    public function find($id, $status)
    {
        $query = "SELECT * FROM arquivos WHERE id = :id and ativo = '$status';";

        try {
            $statement = $this->db->prepare($query);
            $statement->execute(array('id' => $id));
            $result = $statement->fetch(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
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
            if ($result['type'] != "adm") {
                $response['status_code_header'] = 'HTTP/1.1 401 Unathourized';
                $response['body'] = json_encode(array('message' => 'Sem autenticação'));
                return $response;
            }
        }
        return 'verdadeiro';
    }

    private function createArquivo()
    {
        $result = $this->verifyTokenAndEmail(true);
        if ($result != 'verdadeiro') {
            return $result;
        }

        if (!$_POST || !$_FILES) {
            return $this->notFoundResponse('Erro no upload de arquivo');
        }

        $validation = $this->validateImovel($_POST, 'POST');

        if (is_array($validation)) {
            return $this->returnValidationErrors($validation);
        }

        if ($_POST['imovel_id'] == '') {
            $_POST['imovel_id'] = null;
        }

        $fileTmpPath = $_FILES['uploadedFile']['tmp_name'];
        $fileName = $_FILES['uploadedFile']['name'];
        $fileSize = $_FILES['uploadedFile']['size'];
        if ($fileSize > 5000000) {
            $response = 'Tamanho do arquivo maior que 5MB.';
            return $this->notFoundResponse($response);
        }
        //$fileType = $_FILES['uploadedFile']['type'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));

        $newFileName = md5(time() . $fileName) . '.' . $fileExtension;

        $uploadFileDir = 'files/';
        $dest_path = $uploadFileDir . $newFileName;

        $allowedfileExtensions = array('jpg', 'jpeg', 'png', 'xlsx', 'xls', 'doc', 'docx', 'pdf');

        if (in_array($fileExtension, $allowedfileExtensions)) {
            if (!move_uploaded_file($fileTmpPath, $dest_path)) {
                return $this->notFoundResponse('Erro ao salvar arquivo');
            }

            $query = "INSERT INTO arquivos (nome, data_upload, caminho, nome_salvo, imovel_id, nome_original ) VALUES(:nome, :data_upload, :caminho, :nome_salvo, :imovel_id, :nome_original );";

            try {
                $statement = $this->db->prepare($query);
                $statement->execute(array(
                    'nome'  => $_POST['nome'],
                    'data_upload'  => $_POST['data_upload'],
                    'caminho' => $dest_path,
                    'nome_salvo'  => $newFileName,
                    'imovel_id'  => $_POST['imovel_id'],
                    'nome_original'  => $fileName
                ));
            } catch (\PDOException $e) {
                exit($e->getMessage());
            }
        } else {
            $response = 'Upload failed. Allowed file types: ' . implode(',', $allowedfileExtensions);
            return $this->notFoundResponse($response);
        }
        $response['status_code_header'] = 'HTTP/1.1 201 Created';
        $response['body'] = json_encode(array('message' => 'Arquivo Created'));
        return $response;
    }

    private function deletarOuReativarArquivo($tipo, $id)
    {
        $result = $this->verifyTokenAndEmail(true);
        if ($result != 'verdadeiro') {
            return $result;
        }

        $tipoBusca = 'A';
        if ($tipo == 'A') {
            $tipoBusca = 'I';
        }
        $result = $this->find($id, $tipoBusca);
        if (!$result) {
            return $this->notFoundResponse('Arquivo Inexistente');
        }


        $query = "UPDATE arquivos set ativo = '$tipo' WHERE id = :id;";

        try {
            $statement = $this->db->prepare($query);
            $statement->execute(array('id' => $id));
            $statement->rowCount();
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode(array('message' => 'Arquivo Deleted!'));
        return $response;
    }

    private function updateArquivo($id)
    {
        $result = $this->verifyTokenAndEmail(true);
        if ($result != 'verdadeiro') {
            return $result;
        }

        $result = $this->find($id, 'A');
        if (!$result) {
            return $this->notFoundResponse('Arquivo inexistente');
        }
        $input = (array) json_decode(file_get_contents('php://input'), TRUE);

        if ($input['imovel'] == '') {
            $input['imovel'] = null;
        }


        $validation = $this->validateImovel($input, 'PUT');
        if (is_array($validation)) {
            return $this->returnValidationErrors($validation);
        }

        $statement = "UPDATE arquivos SET nome = :nome, imovel_id = :imovel, data_edicao = :data_edicao WHERE id = :id;";


        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                'nome' => $input['nome'],
                'imovel' => $input['imovel'],
                'data_edicao' => $input['data_edicao'],
                'id' => $id
            ));
            $statement->rowCount();
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode(array('message' => 'Arquivo Updated!'));
        return $response;
    }

    private function deletePermanente($id)
    {
        $result = $this->verifyTokenAndEmail(true);
        if ($result != 'verdadeiro') {
            return $result;
        }

        $query = "DELETE FROM arquivos WHERE id = $id";

        try {
            $statement = $this->db->query($query);
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }

        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode('Arquivo deletado permanentemente com sucesso');
        return $response;
    }


    private function validateImovel($input, $tipo)
    {
        $typeOfDate = 'data_upload';
        if ($tipo == 'PUT') {
            $typeOfDate = 'data_edicao';
        }

        $validate = new Data_Validator();
        $validate->set('nome', $input['nome'])->is_required()->max_length('50')
            ->set($typeOfDate, $input[$typeOfDate])->is_required()->is_date();

        if (isset($input['imovel_id']) && $input['imovel_id'] != '') {
            $validate->set('imovel_id', $input['imovel_id'])->min_value('0')->is_num();
        } else if (isset($input['imovel']) && $input['imovel'] != '') {
            $validate->set('imovel_id', $input['imovel'])->min_value('0')->is_num();
        }

        if ($validate->validate()) {
            return  true;
        } else {
            return $validate->get_errors();
        }
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
