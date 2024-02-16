<?php

class Residuo extends TRecord
{
    const TABLENAME = 'residuo';
    const PRIMARYKEY = 'id_residuo';
    const IDPOLICY = 'max'; // {max, serial}
    
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('tp_residuo');
    }
}
