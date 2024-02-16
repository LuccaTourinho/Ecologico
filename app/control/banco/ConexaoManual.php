<?php

class ConexaoManual extends TPage
{
    public function __construct()
    {
        parent::__construct();

        try
        {
            $conn = TTransaction::open('ecologico');

            $pessoa = new Pessoa('1');

            var_dump($pessoa);
            

            TTransaction::close();
        }
        catch(Exception $e)
        {
            new TMessage('error', $e->getMessage());
        }
    }
}