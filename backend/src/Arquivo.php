<?php

namespace Src;

class Arquivo
{

    private $db;
    private $requestMethod;
    private $arquivoId;
    private $busca;
    private $url;

    public function __construct($db, $requestMethod, $imovelId, $busca, $url)
    {
        $this->db = $db;
        $this->requestMethod = $requestMethod;
        $this->arquivoId = $imovelId;
        $this->busca = $busca;
        $this->url = $url;
    }

    public function processRequest()
    {
        switch ($this->requestMethod) {
            case 'GET':
                $response = $this->createArquivo();
                break;
            case 'POST':
                $response = $this->createArquivo();
                break;
            case 'PUT':
                break;
            case 'DELETE':
                break;
            default:
                break;
        }
        header($response['status_code_header']);
        if ($response['body']) {
            echo $response['body'];
        }
    }

    private function getAllArquivos()
    {
        $query = "SELECT * FROM arquivos where ativo = 'A';";

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


        $fileTmpPath = $_FILES['uploadedFile']['tmp_name'];
        $fileName = $_FILES['uploadedFile']['name'];
        //$fileSize = $_FILES['uploadedFile']['size']; fazer validação de tamanho
        //$fileType = $_FILES['uploadedFile']['type'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));

        $newFileName = md5(time() . $fileName) . '.' . $fileExtension;

        $uploadFileDir = '../public/';
        $dest_path = $uploadFileDir . $newFileName;

        $allowedfileExtensions = array('jpg', 'gif', 'png', 'zip', 'txt', 'xls', 'doc', 'pdf');

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
                    'caminho' => end(explode("../", $dest_path)),
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

    private function notFoundResponse()
    {
        $response['status_code_header'] = 'HTTP/1.1 404 Not Found';
        $response['body'] = null;
        return $response;
    }
}
