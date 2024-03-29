<?php

class EntradaForm extends TStandardForm
{
    protected $form;
    private static $data_base = 'ecologico';
    private static $formName = 'form_entrada';
    private $group_list;
    private $methods_list;
    

    public function __construct()
    {
        parent::__construct();

        parent::setTargetContainer('adianti_right_panel');

        // Cria um novo formulário
        $this->form = new BootstrapFormBuilder('form_entrada');
        $this->form->setFormTitle('Cadastro de Entrada de Produto');
        $this->form->enableClientValidation();

        // defines the database
        parent::setDatabase('ecologico');
        
        // defines the active record
        parent::setActiveRecord('Entrada');

        // Campos do formulário
        $id = new TEntry('id_entrada');
        $produto = new TDBCombo('id_produto', 'ecologico', 'Produto', 'id_produto', 'nm_produto');
        $produto->setChangeAction(new TAction([$this,'onValorizarProduto']));
        $quantidade = new TEntry('qt_produto');
        $quantidade ->setExitAction(new TAction([$this,'onValorizarProduto']));
        $valorReal = new TEntry('vl_real');
        $valorEco = new TEntry('vl_eco');

        // Configurações dos campos
        $id->setEditable(false);
        $valorReal->setEditable(false);
        $valorEco->setEditable(false);
        /*$quantidade->setNumericMask(0, ',', '.', true);
        $valorReal->setNumericMask(2, ',', '.', true);
        $valorEco->setNumericMask(2, ',', '.', true);*/

        // Adiciona os campos ao formulário
        $this->form->addFields( [new TLabel('ID')], [$id] );    
        $this->form->addFields( [new TLabel('Produto')], [$produto] );
        $this->form->addFields( [new TLabel('Quantidade')], [$quantidade] );
        $this->form->addFields( [new TLabel('Valor R$')], [$valorReal] );
        $this->form->addFields( [new TLabel('valorEco')], [$valorEco] );

        $id->setSize('10%');
        $produto->setSize('20%');
        $quantidade->setSize('20%');
        $valorReal->setSize('25%');
        $valorEco->setSize('25%');

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

    // Método chamado quando o botão salvar é acionado
    public function onSave()
    {
        try
        {
            $conn = TTransaction::open($this->database);
            
            $data = $this->form->getData();
            $object = new Entrada;
            #$this->form->setData($data);
            $object->fromArray( (array) $data); // load
            $this->form->validate();
            $object->store();
            $conn->query(' 
            UPDATE produto
            SET 
                qt_saldoquantidade = (
                    COALESCE((
                        SELECT COALESCE(SUM(qt_produto), 0)
                        FROM entrada
                        WHERE id_produto = '.$object->id_produto.'
                    ), 0) 
                    - COALESCE((
                        SELECT COALESCE(SUM(qt_produto), 0)
                        FROM saida
                        WHERE id_produto = '.$object->id_produto.'
                    ), 0)
                ),
                vl_saldoreal = (
                    COALESCE((
                        SELECT COALESCE(SUM(vl_real), 0)
                        FROM entrada
                        WHERE id_produto = '.$object->id_produto.'
                    ), 0) 
                    - COALESCE((
                        SELECT COALESCE(SUM(vl_real), 0)
                        FROM saida
                        WHERE id_produto = '.$object->id_produto.'
                    ), 0)
                ),
                vl_saldoeco = (
                    COALESCE((
                        SELECT COALESCE(SUM(vl_eco), 0)
                        FROM entrada
                        WHERE id_produto = '.$object->id_produto.'
                    ), 0) 
                    - COALESCE((
                        SELECT COALESCE(SUM(vl_eco), 0)
                        FROM saida
                        WHERE id_produto = '.$object->id_produto.'
                    ), 0)
                )
            WHERE id_produto = '.$object->id_produto
            );

            $data->id = $object->id;
            
            
            TTransaction::close();
            $pos_action = new TAction(['EntradaHeaderList', 'onReload']);
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

    public static function onClose($param)
    {
        TScript::create("Template.closeRightPanel()");
    }

    public static function onValorizarProduto($param)
    {
        if(!empty($param['id_produto']) && !empty($param['qt_produto']))
        {
            TTransaction::open(self::$data_base);
            $produto = new Produto($param['id_produto']);
            $object = new stdClass();
            $object->vl_real = $produto->vl_real * $param['qt_produto'];
            $object->vl_eco  = $produto->vl_eco * $param['qt_produto'];
            TTransaction::close();

            TForm::sendData(self::$formName, $object);
        }
    }
}
