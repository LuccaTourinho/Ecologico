<?php

class PessoaForm extends TStandardForm
{
    protected $form;
    private $group_list;
    private $methods_list;
    
    /**
     * Class constructor
     * Creates the page and the registration form
     */
    function __construct($param)
    {
        parent::__construct();
        
        parent::setTargetContainer('adianti_right_panel');
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_PessoaForm');
        $this->form->setFormTitle('Pessoa');
        $this->form->enableClientValidation();
        
        // defines the database
        parent::setDatabase('ecologico');
        
        // defines the active record
        parent::setActiveRecord('Pessoa');
        
        // create the form fields
        $id                = new TEntry('id_pessoa');
        $name              = new TEntry('nm_pessoa');
        $data              = new TDate('dt_nascimento');
        $cpf               = new TEntry('nu_cpf');
        $rg                = new TEntry('nu_rg');
        $cep               = new TEntry('nu_cep');

        
        $id->setEditable(false);
      
        // add the fields
        $this->form->addFields( [new TLabel('ID')], [$id] );
      
        $this->form->addFields( [new TLabel('Nome')], [$name] );
        $this->form->addFields( [new TLabel('Data de Nascimento')], [$data] );
        $this->form->addFields( [new TLabel('CPF')], [$cpf] );
        $this->form->addFields( [new TLabel('RG')], [$rg] );
        $this->form->addFields( [new TLabel('Endereço(CEP)')], [$cep] );
        #$this->form->addFields( [new TFormSeparator('teate')] );
        
        $id->setSize('10%');
        $name->setSize('20%');
        $data->setSize('20%');
        $cpf->setSize('20%');
        $rg->setSize('15%');
        $cep->setSize('15%');
        // validations
        $name->addValidation(_t('Name'), new TRequiredValidator);
      

        // add form actions
        $btn = $this->form->addAction(_t('Save'), new TAction(array($this, 'onSave')), 'far:save');
        $btn->class = 'btn btn-sm btn-primary';
        
        $this->form->addActionLink(_t('Clear'), new TAction(array($this, 'onEdit')), 'fa:eraser red');
        //$this->form->addActionLink(_t('Back'),new TAction(array('SystemProgramList','onReload')),'far:arrow-alt-circle-left blue');
        
        $this->form->addHeaderActionLink(_t('Close'), new TAction([$this, 'onClose']), 'fa:times red');
        
        $container = new TVBox;
        $container->style = 'width: 100%';
        // $container->add(new TXMLBreadCrumb('menu.xml','SystemProgramList'));
        $container->add($this->form);
        
        // add the container to the page
        parent::add($container);
    }
    
   
    public function onEdit($param)
    {
        try
        {
            if (isset($param['key']))
            {
                $key=$param['key'];
                
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
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }
    
    /**
     * method onSave()
     * Executed whenever the user clicks at the save button
     */
    public function onSave()
    {
        try
        {
            TTransaction::open($this->database);
            
            $data = $this->form->getData();
            
            $object = new Pessoa;
            $this->form->setData($data);
            $object->fromArray( (array) $data); // load

            $this->form->validate();
            $object->store();
            $data->id = $object->id;
            
            
            TTransaction::close();
            $pos_action = new TAction(['PessoaHeaderList', 'onReload']);
            new TMessage('info', AdiantiCoreTranslator::translate('Record saved'), $pos_action);
            
            return $object;
        }
        catch (Exception $e) // in case of exception
        {
            // get the form data
            $object = $this->form->getData($this->activeRecord);
            $this->form->setData($object);
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }
    
    /**
     * on close
     */
    public static function onClose($param)
    {
        TScript::create("Template.closeRightPanel()");
    }
}
