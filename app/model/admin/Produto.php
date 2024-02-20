<?php

class Produto extends TRecord
{
    const TABLENAME = 'produto';
    const PRIMARYKEY = 'id_produto';
    const IDPOLICY = 'max'; // {max, serial}
    
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('nm_produto');
        parent::addAttribute('dt_produto');
        parent::addAttribute('vl_real');
        parent::addAttribute('vl_eco');
        parent::addAttribute('qt_saldoquantidade');
        parent::addAttribute('vl_saldoreal');
        parent::addAttribute('vl_saldoeco');
    }
}
