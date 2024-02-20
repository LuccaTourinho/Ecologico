<?php
use Adianti\Widget\Dialog\TMessage;

class SaidaForm extends TStandardForm
{
    protected $form;
    private static $data_base = 'ecologico';
    private static $formName = 'form_SaidaForm';
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
        $produto->setChangeAction(new TAction([$this,'onValorizarProduto']));
        $qt_produto = new TEntry('qt_produto');
        $qt_produto->setExitAction(new TAction([$this,'onValorizarProduto']));
        $vl_real = new TEntry('vl_real');
        $vl_eco = new TEntry('vl_eco');

        $id->setEditable(false);
        $vl_real->setEditable(false);
        $vl_eco->setEditable(false);
      
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
            $conn = TTransaction::open($this->database);
            
            $data = $this->form->getData();
            
            $object = new Saida;
            $this->form->setData($data);
            $object->fromArray((array) $data); // load

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

    public static function onValorizarProduto($param)
    {
        if(!empty($param['id_produto']) && !empty($param['qt_produto']))
        {
            $conn = TTransaction::open(self::$data_base);
            $produto = new Produto($param['id_produto']);
            $object = new stdClass();
            $obvl_real = $produto->vl_real * $param['qt_produto'];
            $obvl_eco  = $produto->vl_eco * $param['qt_produto'];

            $queryQT = '
                SELECT 
                    (
                        COALESCE((SELECT SUM(qt_produto) FROM entrada WHERE id_produto = '.$param['id_produto'].'), 0) 
                        - COALESCE((SELECT SUM(qt_produto) FROM saida WHERE id_produto = '.$param['id_produto'].'), 0)
                    ) AS qt_produto
            ';

            $queryECO = '
                SELECT 
                    (
                        COALESCE((SELECT SUM(vl_eco) FROM entrada WHERE id_produto = '.$param['id_produto'].'), 0) 
                        - COALESCE((SELECT SUM(vl_eco) FROM saida WHERE id_produto = '.$param['id_produto'].'), 0)
                    ) AS vl_eco
            ';

            $queryReal = '
                SELECT 
                    (
                        COALESCE((SELECT SUM(vl_real) FROM entrada WHERE id_produto = '.$param['id_produto'].'), 0) 
                        - COALESCE((SELECT SUM(vl_real) FROM saida WHERE id_produto = '.$param['id_produto'].'), 0)
                    ) AS vl_real
            ';

            $statementQT = $conn->query($queryQT);
            $statementECO = $conn->query($queryECO);
            $statementReal = $conn->query($queryReal);

            $resultQT = $statementQT->fetch(PDO::FETCH_ASSOC);
            $resultECO = $statementECO->fetch(PDO::FETCH_ASSOC);
            $resultReal= $statementReal->fetch(PDO::FETCH_ASSOC);
            
            $qt_produto = $resultQT['qt_produto'];
            $vl_eco = $resultECO['vl_eco'];
            $vl_real = $resultReal['vl_real'];

            TTransaction::close();

            

            if($param['qt_produto']<=$qt_produto && $obvl_real<=$vl_real && $obvl_eco<=$vl_eco)
            {
                $object->vl_eco = $obvl_eco;
                $object->vl_real = $obvl_real;
                TForm::sendData(self::$formName, $object);
            }
            else
            {
                $object->qt_produto = null;
                $object->vl_eco = null;
                $object->vl_real = null;
                TForm::sendData(self::$formName, $object);
                if($param['qt_produto']<=$qt_produto)
                {
                    new TMessage('info', 'Ultrapassou o saldo de quantidade.');
                }
                elseif($obvl_real<=$vl_real)
                {
                    new TMessage('info', 'Ultrapassou o saldo do valor real.');
                }
                else
                {
                    new TMessage('info', 'Ultrapassou o saldo do valor eco.');
                }
                
            }
        }
    }
}


