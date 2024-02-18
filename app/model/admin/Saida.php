<?php

class Saida extends TRecord
{
    const TABLENAME = 'saida';
    const PRIMARYKEY = 'id_saida';
    const IDPOLICY = 'max'; // {max, serial}
    
    private $produto;
    
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        
        parent::addAttribute('id_produto');
        parent::addAttribute('qt_produto');
        parent::addAttribute('vl_real');
        parent::addAttribute('vl_eco');
    }
    
    public function set_produto(Produto $produto)
    {
        $this->produto = $produto;
        $this->id_produto = $produto->id_produto;
    }

    public function get_produto()
    {
        if (empty($this->produto))
            $this->produto = new Produto($this->id_produto);

        return $this->produto;
    }
}

?>
