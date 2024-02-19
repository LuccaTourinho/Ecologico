<?php

class SaidaForm extends TStandardForm
{
    protected $form;
    private $group_list;
    private $methods_list;
    
    public function __construct($param)
    {
        parent::__construct();
        
        parent::setTargetContainer('adianti_right_panel');
        
        $this->form = new BootstrapFormBuilder('form_SaidaForm');
        $this->form->setFormTitle('Saída de Produto');
        $this->form->enableClientValidation();
        
        parent::setDatabase('ecologico');
        parent::setActiveRecord('Saida');
        
        $id     = new TEntry('id_saida');
        $produto = new TDBCombo('id_produto', 'ecologico', 'Produto', 'id_produto', 'nm_produto');
        $qt_produto = new TEntry('qt_produto');
        $vl_real = new TEntry('vl_real');
        $vl_eco = new TEntry('vl_eco');

        $id->setEditable(false);
      
        $this->form->addFields([new TLabel('ID')], [$id]);
        $this->form->addFields([new TLabel('Produto')], [$produto]);
        $this->form->addFields([new TLabel('Quantidade')], [$qt_produto]);
        $this->form->addFields([new TLabel('Valor Real')], [$vl_real]);
        $this->form->addFields([new TLabel('Valor Ecológico')], [$vl_eco]);
        
        $id->setSize('10%');
        $produto->setSize('20%');
        $qt_produto->setSize('20%');
        $vl_real->setSize('25%');
        $vl_eco->setSize('25%');

        /*$produto->addValidation(_t('Product'), new TRequiredValidator);
        $qt_produto->addValidation(_t('Quantity'), new TRequiredValidator);
        $vl_real->addValidation(_t('Real Value'), new TRequiredValidator);
        $vl_eco->addValidation(_t('Ecological Value'), new TRequiredValidator);*/

        $btn = $this->form->addAction(_t('Save'), new TAction([$this, 'onSave']), 'far:save');
        $btn->class = 'btn btn-sm btn-primary';
        
        $this->form->addActionLink(_t('Clear'), new TAction([$this, 'onEdit']), 'fa:eraser red');
        $this->form->addActionLink(_t('Close'), new TAction([$this, 'onClose']), 'fa:times red');

        $container = new TVBox;
        $container->style = 'width: 100%';
        #$container->add(new TXMLBreadCrumb('menu.xml', 'SaidaHeaderList'));
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
            
            $object = new Saida;
            $this->form->setData($data);
            $object->fromArray((array) $data); // load

            $this->form->validate();
            $object->store();
            $data->id = $object->id;
            
            TTransaction::close();
            
            $pos_action = new TAction(['SaidaHeaderList', 'onReload']);
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


