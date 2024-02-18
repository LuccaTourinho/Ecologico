<?php

class RecebimentoMaterialForm extends TStandardForm
{
    protected $form;
    
    public function __construct($param)
    {
        parent::__construct();
        
        parent::setTargetContainer('adianti_right_panel');
        
        // Cria o formulário
        $this->form = new BootstrapFormBuilder('form_RecebimentoMaterialForm');
        $this->form->setFormTitle('Cadastro de Recebimento de Material');
        $this->form->enableClientValidation();
        
        // Define o banco de dados e o Active Record
        parent::setDatabase('ecologico');
        parent::setActiveRecord('RecebimentoMaterial');
        
        // Cria os campos do formulário
        $id                = new TEntry('id_recebimentomaterial');
        $pessoa_id         = new TDBCombo('id_pessoa', 'ecologico', 'Pessoa', 'id_pessoa', 'nm_pessoa');
        $material_id       = new TDBCombo('id_material', 'ecologico', 'MaterialResidual', 'id_material', 'nm_materialresidual');
        $qt_material       = new TEntry('qt_material');
        $vl_real           = new TNumeric('vl_real', 2, ',', '.', true);
        $vl_eco            = new TNumeric('vl_eco', 2, ',', '.', true);
        
        // Define os tamanhos dos campos
        $id->setSize('10%');
        $pessoa_id->setSize('70%');
        $material_id->setSize('70%');
        $qt_material->setSize('70%');
        $vl_real->setSize('70%');
        $vl_eco->setSize('70%');
        
        // Adiciona os campos ao formulário
        $this->form->addFields([new TLabel('ID')], [$id]);
        $this->form->addFields([new TLabel('Pessoa')], [$pessoa_id]);
        $this->form->addFields([new TLabel('Material Residual')], [$material_id]);
        $this->form->addFields([new TLabel('Quantidade do Material')], [$qt_material]);
        $this->form->addFields([new TLabel('Valor Real')], [$vl_real]);
        $this->form->addFields([new TLabel('Valor Ecológico')], [$vl_eco]);
        
        // Adiciona ações ao formulário
        $this->form->addAction(_t('Save'), new TAction([$this, 'onSave']), 'far:save');
        $this->form->addActionLink(_t('Clear'), new TAction([$this, 'onEdit']), 'fa:eraser red');
        $this->form->addActionLink(_t('Close'), new TAction([$this, 'onClose']), 'fa:times red');
        
        // Adiciona o formulário à página
        $container = new TVBox;
        $container->style = 'width: 100%';
        $container->add($this->form);
        parent::add($container);
    }
    
    public function onSave()
    {
        try
        {
            TTransaction::open('ecologico');
            
            $data = $this->form->getData();
            $object = new RecebimentoMaterial;
            $object->fromArray((array) $data);
            $this->form->validate();
            $object->store();
            $data->id = $object->id;
            
            TTransaction::close();
            $pos_action = new TAction(['RecebimentoMaterialList', 'onReload']);
            new TMessage('info', AdiantiCoreTranslator::translate('Record saved'), $pos_action);
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }
    
    public static function onClose($param)
    {
        TScript::create("Template.closeRightPanel()");
    }
}
