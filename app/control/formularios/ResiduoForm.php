<?php
class ResiduoForm extends TStandardForm
{
    protected $form;

    public function __construct($param)
    {
        parent::__construct();

        parent::setTargetContainer('adianti_right_panel');

        $this->form = new BootstrapFormBuilder('form_ResiduoForm');
        $this->form->setFormTitle('Resíduo');
        $this->form->enableClientValidation();

        parent::setDatabase('ecologico');
        parent::setActiveRecord('Residuo');

        $id   = new TEntry('id_residuo');
        $type = new TEntry('tp_residuo');

        $id->setEditable(false);

        $this->form->addFields([new TLabel('ID')], [$id]);
        $this->form->addFields([new TLabel('Tipo')], [$type]);

        $id->setSize('10%');
        $type->setSize('90%');

        $type->addValidation(_t('Type'), new TRequiredValidator);

        $btn = $this->form->addAction(_t('Save'), new TAction([$this, 'onSave']), 'far:save');
        $btn->class = 'btn btn-sm btn-primary';

        $this->form->addActionLink(_t('Clear'), new TAction([$this, 'onEdit']), 'fa:eraser red');
        $this->form->addHeaderActionLink(_t('Close'), new TAction([$this, 'onClose']), 'fa:times red');

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
            TTransaction::open($this->database);

            $data = $this->form->getData();

            $object = new Residuo;
            $this->form->setData($data);
            $object->fromArray((array) $data);

            $this->form->validate();
            $object->store();
            $data->id = $object->id;

            TTransaction::close();
            $pos_action = new TAction(['ResiduoHeaderList', 'onReload']);
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
