<?php

class RecebimentoMaterial extends TRecord
{
    const TABLENAME = 'recebimento_material';
    const PRIMARYKEY = 'id_recebimentomaterial';
    const IDPOLICY = 'serial'; // ou 'max'
    
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        
        parent::addAttribute('id_pessoa');
        parent::addAttribute('id_material');
        parent::addAttribute('qt_material');
        parent::addAttribute('vl_real');
        parent::addAttribute('vl_eco');
    }
    
    // Métodos para obter e definir a pessoa e o material associados
    public function set_pessoa(Pessoa $pessoa)
    {
        $this->pessoa = $pessoa;
        $this->id_pessoa = $pessoa->id_pessoa;
    }

    public function get_pessoa()
    {
        if (empty($this->pessoa))
            $this->pessoa = new Pessoa($this->id_pessoa);
        
        return $this->pessoa;
    }

    public function set_material(MaterialResidual $material)
    {
        $this->material = $material;
        $this->id_material = $material->id_material;
    }

    public function get_material()
    {
        if (empty($this->material))
            $this->material = new MaterialResidual($this->id_material);
        
        return $this->material;
    }
}
