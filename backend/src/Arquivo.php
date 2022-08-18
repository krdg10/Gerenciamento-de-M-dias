<?php

namespace Src;

class Arquivo
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
                break;
            case 'POST':
                break;
            case 'PUT':
                break;
            case 'DELETE':
                break;
            default:
                break;
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

    private function notFoundResponse()
    {
        $response['status_code_header'] = 'HTTP/1.1 404 Not Found';
        $response['body'] = null;
        return $response;
    }
}
