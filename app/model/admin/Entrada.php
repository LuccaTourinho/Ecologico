<?php

class Entrada extends TRecord
{
    const TABLENAME = 'entrada';
    const PRIMARYKEY = 'id_entrada';
    const IDPOLICY = 'max'; // {max, serial}

    private $produto;

    public function __construct($id = null, $callObjectLoad = true)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('id_produto');
        parent::addAttribute('qt_produto');
        parent::addAttribute('vl_real');
        parent::addAttribute('vl_eco');
    }

    public function set_produto(Produto $object)
    {
        $this->produto = $object;
        $this->id_produto = $object->id_produto;
    }

    public function get_produto()
    {
        if (empty($this->produto))
            $this->produto = new Produto($this->id_produto);

        return $this->produto;
    }

    public function set_id_produto($id)
    {
        $this->id_produto = $id;
        $this->produto = new Produto($id);
    }
}
