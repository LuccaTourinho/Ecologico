<?php
use Adianti\Database\TTransaction;

class RecebimentoMaterialForm extends TStandardForm
{
    protected $form;
    private static $data_base = 'ecologico';
    private static $formName = 'form_RecebimentoMaterialForm';
    
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
        $material_id       = new TDBCombo('id_material', 'ecologico', 'MaterialResidual', 'id_materialresidual', 'nm_materialresidual');
        $material_id ->setChangeAction(new TAction([$this,'onValorizarProduto']));
        $qt_material       = new TEntry('qt_material');
        $qt_material ->setExitAction(new TAction([$this,'onValorizarProduto'])); 
        $vl_real           = new TNumeric('vl_real', 2, ',', '.', true);
        $vl_eco            = new TNumeric('vl_eco', 2, ',', '.', true);
        

        $id->setEditable(false);
        $vl_real->setEditable(false);
        $vl_eco->setEditable(false);


        
        // Adiciona os campos ao formulário
        $this->form->addFields([new TLabel('ID')], [$id]);
        $this->form->addFields([new TLabel('Pessoa')], [$pessoa_id]);
        $this->form->addFields([new TLabel('Material Residual')], [$material_id]);
        $this->form->addFields([new TLabel('Quantidade do Material')], [$qt_material]);
        $this->form->addFields([new TLabel('Valor Real')], [$vl_real]);
        $this->form->addFields([new TLabel('Valor Ecológico')], [$vl_eco]);

        // Define os tamanhos dos campos
        $id->setSize('10%');
        $pessoa_id->setSize('20%');
        $material_id->setSize('20%');
        $qt_material->setSize('20%');
        $vl_real->setSize('15%');
        $vl_eco->setSize('15%');

        $qt_material->addValidation('Quantidade', new TRequiredValidator);
        
        // Adiciona ações ao formulário
        $btn = $this->form->addAction(_t('Save'), new TAction([$this, 'onSave']), 'far:save');
        $btn->class = 'btn btn-sm btn-primary';

        $this->form->addActionLink(_t('Clear'), new TAction([$this, 'onEdit']), 'fa:eraser red');
        $this->form->addHeaderActionLink(_t('Close'), new TAction([$this, 'onClose']), 'fa:times red');
        
        // Adiciona o formulário à página
        $container = new TVBox;
        $container->style = 'width: 100%';
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
            $conn = TTransaction::open('ecologico');
            
            $data = $this->form->getData();
            $object = new RecebimentoMaterial;
            $object->fromArray((array) $data);
            $this->form->validate();
            $object->store();
            
            $conn->query('
            UPDATE Pessoa
            SET vl_saldoeco = (
                SELECT COALESCE(SUM(vl_eco), 0)
                FROM recebimento_material
                WHERE id_pessoa = '.$object->id_pessoa.'
            )
            WHERE id_pessoa = '.$object->id_pessoa
            
            );
            

            $data->id = $object->id;
            
            TTransaction::close();
            $pos_action = new TAction(['RecebimentoMaterialHeaderList', 'onReload']);
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

    public static function onValorizarProduto($param)
    {
        if(!empty($param['id_material']) && !empty($param['qt_material']))
        {
            TTransaction::open(self::$data_base);

    

            $material = new MaterialResidual($param['id_material']);
            $object = new stdClass();
            $object->vl_real = $material->vl_real * $param['qt_material'];
            $object->vl_eco  = $material->vl_eco * $param['qt_material'];
            TTransaction::close();

            TForm::sendData(self::$formName, $object);
        }
    }
}
