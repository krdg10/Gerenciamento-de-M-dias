<?php

namespace Src;


class Imovel
{
    private $db;


    public function __construct($db)
    {
        $this->db = $db;
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
                $response = $this->notFoundResponse('Error');
                break;
        }
        header($response['status_code_header']);
        if ($response['body']) {
            echo $response['body'];
        }
    }



    private function createUser()
    {
        // do something
    }

    private function updateImovel($id)
    {
        // do something
    }


    private function deleteUser($tipo, $id)
    {
        // do something
    }

    private function login()
    {
        // do something
    }

    private function createToken()
    {
        // do something
    }
}
