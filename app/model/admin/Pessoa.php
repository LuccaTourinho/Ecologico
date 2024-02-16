<?php

class Pessoa extends TRecord
{
    const TABLENAME = 'pessoa';
    const PRIMARYKEY= 'id_pessoa';
    const IDPOLICY =  'max'; // {max, serial}
    
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('nm_pessoa');
        parent::addAttribute('dt_nascimento');
        parent::addAttribute('nu_cpf');
        parent::addAttribute('nu_rg');
        parent::addAttribute('nu_cep');
    }
}