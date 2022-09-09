<?php

namespace Src;

class Arquivo
{

    private $db;
    private $requestMethod;
    private $arquivoId;
    private $busca;
    private $url;

    public function __construct($db, $requestMethod, $arquivoId, $busca, $url)
    {
        $this->db = $db;
        $this->requestMethod = $requestMethod;
        $this->arquivoId = $arquivoId;
        $this->busca = $busca;
        $this->url = $url;
    }

    public function processRequest()
    {
        switch ($this->requestMethod) {
            case 'GET':
                if ($this->arquivoId) {
                    $response = $this->getArquivo($this->arquivoId);
                } else if ($this->busca) {
                    if ($this->url == 'buscaNome') {
                        $response = $this->getArquivosByName($this->busca);
                    } else {
                        $response = $this->getArquivosByImovel($this->busca);
                    }
                } else {
                    if ($this->url == 'buscarTodosValidos') {
                        $response = $this->getAllArquivos('A');
                    } else if ($this->url == 'buscarTodosInvalidos') {
                        $response = $this->getAllArquivos('I');
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

    private function getNumeroArquivosAtivosOuInativos($status)
    {
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


    private function getArquivosByName($busca)
    {
        $keywords = $busca[0];
        $status = $busca[1];
        $query = "SELECT * FROM arquivos where LOWER(nome) LIKE LOWER('%$keywords%') and ativo = '$status';";

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

    private function getArquivosByImovel($busca)
    {
        $keywords = $busca[0];
        $status = $busca[1];
        $query = "SELECT * FROM arquivos where imovel_id = $keywords and ativo = '$status';";

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

    private function getArquivo($id)
    {
        $result = $this->find($id);
        if (!$result) {
            return $this->notFoundResponse();
        }
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
        return $response;
    }

    public function find($id)
    {
        $query = "
      SELECT
          *
      FROM
          arquivos
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

    private function createArquivo()
    {
        if (!$_POST || !$_FILES) {
            return $this->notFoundResponse();
        }
        if ($_POST['imovel_id'] == '') {
            $_POST['imovel_id'] = null;
        }


        $fileTmpPath = $_FILES['uploadedFile']['tmp_name'];
        $fileName = $_FILES['uploadedFile']['name'];
        //$fileSize = $_FILES['uploadedFile']['size']; fazer validação de tamanho
        //$fileType = $_FILES['uploadedFile']['type'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));

        $newFileName = md5(time() . $fileName) . '.' . $fileExtension;

        $uploadFileDir = 'files/';
        $dest_path = $uploadFileDir . $newFileName;

        $allowedfileExtensions = array('jpg', 'jpeg', 'png', 'xlsx', 'xls', 'doc', 'docx', 'pdf');

        if (in_array($fileExtension, $allowedfileExtensions)) {
            if (!move_uploaded_file($fileTmpPath, $dest_path)) {
                return $this->notFoundResponse();
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
            return $response;
        }
        // https://code.tutsplus.com/tutorials/how-to-upload-a-file-in-php-with-example--cms-31763
        $response['status_code_header'] = 'HTTP/1.1 201 Created';
        $response['body'] = json_encode(array('message' => 'Arquivo Created'));
        return $response;
    }

    private function deletarOuReativarArquivo($tipo, $id)
    {
        if ($tipo == 'I') {
            $result = $this->find($id);
            if (!$result) {
                return $this->notFoundResponse();
            }
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
        $result = $this->find($id);
        if (!$result) {
            return $this->notFoundResponse();
        }
        $input = (array) json_decode(file_get_contents('php://input'), TRUE);
        // botar um validate

        $statement = "UPDATE arquivos SET nome = :nome, imovel_id = :imovel, data_edicao = :data_edicao WHERE id = :id;";

        if ($input['imovel'] == '') {
            $input['imovel'] = null;
        }

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

    private function notFoundResponse()
    {
        $response['status_code_header'] = 'HTTP/1.1 404 Not Found';
        $response['body'] = null;
        return $response;
    }
}
