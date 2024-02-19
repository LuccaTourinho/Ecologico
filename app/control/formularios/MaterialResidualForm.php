<?php

class MaterialResidualForm extends TStandardForm
{
    protected $form;
    
    public function __construct($param)
    {
        parent::__construct();
        
        parent::setTargetContainer('adianti_right_panel');
        
        // Cria o formulário
        $this->form = new BootstrapFormBuilder('form_MaterialResidualForm');
        $this->form->setFormTitle('Material Residual');
        $this->form->enableClientValidation();
        
        // Define o banco de dados
        parent::setDatabase('ecologico');
        
        // Define o active record
        parent::setActiveRecord('MaterialResidual');
        
        // Cria os campos do formulário
        $id = new TEntry('id_materialresidual');
        $name = new TEntry('nm_materialresidual');
        $residuo = new TDBCombo('id_residuo', 'ecologico', 'Residuo', 'id_residuo', 'tp_residuo');
        $unidademedida = new TEntry('tp_unidademedida');
        $vl_real = new TNumeric('vl_real', 2, ',', '.', true);
        $vl_eco = new TNumeric('vl_eco', 2, ',', '.', true);
        
        $id->setEditable(false);
      
        // Adiciona os campos ao formulário
        $this->form->addFields([new TLabel('ID')], [$id]);
        $this->form->addFields([new TLabel('Nome')], [$name]);
        $this->form->addFields([new TLabel('Resíduo')], [$residuo]);
        $this->form->addFields([new TLabel('Unidade de Medida')], [$unidademedida]);
        $this->form->addFields([new TLabel('Valor Real')], [$vl_real]);
        $this->form->addFields([new TLabel('Valor Ecológico')], [$vl_eco]);
        
        $id->setSize('10%');
        $name->setSize('20%');
        $residuo->setSize('20%');
        $unidademedida->setSize('20%');
        $vl_real->setSize('15%');
        $vl_eco->setSize('15%');
        
        // Validações
        $name->addValidation(_t('Name'), new TRequiredValidator);
        $residuo->addValidation(_t('Residue'), new TRequiredValidator);
        $unidademedida->addValidation(_t('Unit of Measure'), new TRequiredValidator);
        $vl_real->addValidation(_t('Real Value'), new TRequiredValidator);
        $vl_eco->addValidation(_t('Ecological Value'), new TRequiredValidator);

        // Adiciona ações ao formulário
        $btn = $this->form->addAction(_t('Save'), new TAction([$this, 'onSave']), 'far:save');
        $btn->class = 'btn btn-sm btn-primary';
        
        $this->form->addActionLink(_t('Clear'), new TAction([$this, 'onEdit']), 'fa:eraser red');
        $this->form->addHeaderActionLink(_t('Close'), new TAction([$this, 'onClose']), 'fa:times red');
        
        $container = new TVBox;
        $container->style = 'width: 100%';
        $container->add($this->form);
        parent::add($this->form);
    }
    
    public function onEdit($param)
    {
        try
        {
            if (isset($param['key']))
            {
                $key = $param['key'];
                
                TTransaction::open($this->database);
                $class = $this->activeRecord;
                $object = new $class($key);
                
                $this->form->setData($object);
                
                TTransaction::close();
                
                return $object;
            }
            else
            {
                $this->form->clear(true);
            }
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }
    
    public function onSave()
    {
        try
        {
            TTransaction::open($this->database);
            
            $data = $this->form->getData();
            
            $object = new MaterialResidual;
            $this->form->setData($data);
            $object->fromArray((array) $data); // load
            
            $this->form->validate();
            $object->store();
            $data->id = $object->id;
            
            TTransaction::close();
            $pos_action = new TAction(['MaterialResidualHeaderList', 'onReload']);
            new TMessage('info', AdiantiCoreTranslator::translate('Record saved'), $pos_action);
            
            return $object;
        }
        catch (Exception $e)
        {
            // get the form data
            $object = $this->form->getData($this->activeRecord);
            $this->form->setData($object);
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }
    
    public static function onClose($param)
    {
        TScript::create("Template.closeRightPanel()");
    }
}
