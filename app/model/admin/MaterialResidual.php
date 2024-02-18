<?php

class MaterialResidual extends TRecord
{
    const TABLENAME = 'material_residual';
    const PRIMARYKEY = 'id_materialresidual';
    const IDPOLICY = 'max'; // {max, serial}

    private $residuo;

    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('nm_materialresidual');
        parent::addAttribute('id_residuo');
        parent::addAttribute('tp_unidademedida');
        parent::addAttribute('vl_real');
        parent::addAttribute('vl_eco');
    }

    public function set_residuo(Residuo $object)
    {
        $this->residuo = $object;
        $this->id_residuo = $object->id_residuo;
    }

    public function get_residuo()
    {
        if (empty($this->residuo))
            $this->residuo = new Residuo($this->id_residuo);

        return $this->residuo;
    }
}
