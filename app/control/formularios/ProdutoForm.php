<?php

class ProdutoForm extends TStandardForm
{
    protected $form;
    
    function __construct($param)
    {
        parent::__construct();
        
        parent::setTargetContainer('adianti_right_panel');
        
        $this->form = new BootstrapFormBuilder('form_ProdutoForm');
        $this->form->setFormTitle('Produto');
        $this->form->enableClientValidation();
        
        parent::setDatabase('ecologico');
        parent::setActiveRecord('Produto');
        
        $id    = new TEntry('id_produto');
        $name  = new TEntry('nm_produto');
        $date  = new TDate('dt_produto');
        $value = new TEntry('vl_real');
        $eco   = new TEntry('vl_eco');

        $id->setEditable(false);
      
        $this->form->addFields([new TLabel('ID')], [$id]);
        $this->form->addFields([new TLabel('Nome')], [$name]);
        $this->form->addFields([new TLabel('Data')], [$date]);
        $this->form->addFields([new TLabel('Valor Real')], [$value]);
        $this->form->addFields([new TLabel('Valor Ecológico')], [$eco]);
        
        $id->setSize('10%');
        $name->setSize('20%');
        $date->setSize('20%');
        $value->setSize('20%');
        $eco->setSize('20%');

        $name->addValidation(_t('Name'), new TRequiredValidator);

        $btn = $this->form->addAction(_t('Save'), new TAction([$this, 'onSave']), 'far:save');
        $btn->class = 'btn btn-sm btn-primary';
        
        $this->form->addActionLink(_t('Clear'), new TAction([$this, 'onEdit']), 'fa:eraser red');
        $this->form->addActionLink(_t('Close'), new TAction([$this, 'onClose']), 'fa:times red');

        $container = new TVBox;
        $container->style = 'width: 100%';
        $container->add(new TXMLBreadCrumb('menu.xml', 'ProdutoHeaderList'));
        $container->add($this->form);
        
        parent::add($container);
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
            
            $object = new Produto;
            $this->form->setData($data);
            $object->fromArray((array) $data); // load

            $this->form->validate();
            $object->store();
            $data->id = $object->id;
            
            TTransaction::close();
            
            $pos_action = new TAction(['ProdutoHeaderList', 'onReload']);
            new TMessage('info', AdiantiCoreTranslator::translate('Record saved'), $pos_action);
            
            return $object;
        }
        catch (Exception $e)
        {
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
