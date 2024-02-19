<?php
class ResiduoHeaderList extends TStandardList
{
    protected $form;
    protected $datagrid;
    protected $pageNavigation;
    protected $formgrid;
    protected $deleteButton;
    protected $transformCallback;

    public function __construct()
    {
        parent::__construct();
        
        parent::setDatabase('Ecologico');
        parent::setActiveRecord('Residuo');
        parent::setDefaultOrder('id_residuo', 'asc');
        parent::addFilterField('id_residuo', '=', 'id_residuo');
        
        parent::setLimit(TSession::getValue(__CLASS__ . '_limit') ?? 10);
        
        $this->form = new BootstrapFormBuilder('form_search_Residuo');
        $this->form->setFormTitle('Resíduos');
        
        $name = new TEntry('tp_residuo');
        
        $this->form->addFields([new TLabel(_t('Type'))], [$name]);
        $name->setSize('100%');
        
        $this->form->setData(TSession::getValue('Residuo_filter_data'));
        
        $btn = $this->form->addAction(_t('Find'), new TAction([$this, 'onSearch']), 'fa:search');
        $btn->class = 'btn btn-sm btn-primary';
        
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->setHeight(320);
        
        $column_id = new TDataGridColumn('id_residuo', 'Id', 'center', 50);
        $column_controller = new TDataGridColumn('tp_residuo', 'Tipo', 'left');
    

        $column_name = new TDataGridColumn('nu_cpf','CPF', 'left');
        $column_menu = new TDataGridColumn('nu_cep', 'Endereço(CEP)', 'left');


        $column_name->enableAutoHide(500);
        $column_menu->enableAutoHide(500);
            
        $column_menu->setTransformer( function($value, $object, $row) {
            $menuparser = new TMenuParser('menu.xml');
            $paths = $menuparser->getPath($value);
                
            if ($paths)
            {
                return implode(' &raquo; ', $paths);
            }
        });
    
            // add the columns to the DataGrid
            $this->datagrid->addColumn($column_id);
            $this->datagrid->addColumn($column_controller);
            $this->datagrid->addColumn($column_name);
            $this->datagrid->addColumn($column_menu);
    
    
            // creates the datagrid column actions
            $order_id = new TAction(array($this, 'onReload'));
            $order_id->setParameter('order', 'id_residuo');
            $column_id->setAction($order_id);
            
            $order_name = new TAction(array($this, 'onReload'));
            $order_name->setParameter('order', 'tp_residuo');
            $column_name->setAction($order_name);
            
            
            // create EDIT action
            $action_edit = new TDataGridAction(array('ResiduoForm', 'onEdit'), ['register_state' => 'false']);
            $action_edit->setButtonClass('btn btn-default');
            $action_edit->setLabel(_t('Edit'));
            $action_edit->setImage('far:edit blue ');
            $action_edit->setField('id_residuo');
            $this->datagrid->addAction($action_edit);
            
            // create DELETE action
            $action_del = new TDataGridAction(array($this, 'onDelete'));
            $action_del->setButtonClass('btn btn-default');
            $action_del->setLabel(_t('Delete'));
            $action_del->setImage('far:trash-alt red ');
            $action_del->setField('id_residuo');
            $this->datagrid->addAction($action_del);
            
          
            // create the datagrid model
            $this->datagrid->createModel();
            
            // create the page navigation
            $this->pageNavigation = new TPageNavigation;
            $this->pageNavigation->enableCounters();
            $this->pageNavigation->setAction(new TAction(array($this, 'onReload')));
            $this->pageNavigation->setWidth($this->datagrid->getWidth());
            
            $panel = new TPanelGroup;
            $panel->add($this->datagrid)->style='overflow-x:auto';
            $panel->addFooter($this->pageNavigation);
            
            $btnf = TButton::create('find', [$this, 'onSearch'], '', 'fa:search');
            $btnf->style= 'height: 37px; margin-right:4px;';
            
            $form_search = new TForm('form_search_name');
            $form_search->style = 'float:left;display:flex';
            $form_search->add($name, true);
            $form_search->add($btnf, true);
            
            $panel->addHeaderWidget($form_search);
            
            $panel->addHeaderActionLink('', new TAction(['ResiduoForm', 'onEdit'], ['register_state' => 'false']), 'fa:plus');
            $this->filter_label = $panel->addHeaderActionLink('Filtros', new TAction([$this, 'onShowCurtainFilters']), 'fa:filter');
            
            // header actions
            $dropdown = new TDropDown(_t('Export'), 'fa:list');
            $dropdown->style = 'height:37px';
            $dropdown->setPullSide('right');
            $dropdown->setButtonClass('btn btn-default waves-effect dropdown-toggle');
            $dropdown->addAction( _t('Save as CSV'), new TAction([$this, 'onExportCSV'], ['register_state' => 'false', 'static'=>'1']), 'fa:table fa-fw blue' );
            $dropdown->addAction( _t('Save as PDF'), new TAction([$this, 'onExportPDF'], ['register_state' => 'false', 'static'=>'1']), 'far:file-pdf fa-fw red' );
            $dropdown->addAction( _t('Save as XML'), new TAction([$this, 'onExportXML'], ['register_state' => 'false', 'static'=>'1']), 'fa:code fa-fw green' );
            $panel->addHeaderWidget( $dropdown );
            
            // header actions
            $dropdown = new TDropDown( TSession::getValue(__CLASS__ . '_limit') ?? '10', '');
            $dropdown->style = 'height:37px';
            $dropdown->setPullSide('right');
            $dropdown->setButtonClass('btn btn-default waves-effect dropdown-toggle');
            $dropdown->addAction( 10,   new TAction([$this, 'onChangeLimit'], ['register_state' => 'false', 'static'=>'1', 'limit' => '10']) );
            $dropdown->addAction( 20,   new TAction([$this, 'onChangeLimit'], ['register_state' => 'false', 'static'=>'1', 'limit' => '20']) );
            $dropdown->addAction( 50,   new TAction([$this, 'onChangeLimit'], ['register_state' => 'false', 'static'=>'1', 'limit' => '50']) );
            $dropdown->addAction( 100,  new TAction([$this, 'onChangeLimit'], ['register_state' => 'false', 'static'=>'1', 'limit' => '100']) );
            $dropdown->addAction( 1000, new TAction([$this, 'onChangeLimit'], ['register_state' => 'false', 'static'=>'1', 'limit' => '1000']) );
            $panel->addHeaderWidget( $dropdown );
            
            if (TSession::getValue(get_class($this).'_filter_counter') > 0)
            {
                $this->filter_label->class = 'btn btn-primary';
                $this->filter_label->setLabel('Filtros ('. TSession::getValue(get_class($this).'_filter_counter').')');
            }
            
            // vertical box container
            $container = new TVBox;
            $container->style = 'width: 100%';
            $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
            //$container->add($this->form);
            $container->add($panel);
            
            parent::add($container);

    }

    public function onAfterSearch($datagrid, $options)
    {
        if (TSession::getValue(get_class($this).'_filter_counter') > 0)
        {
            $this->filter_label->class = 'btn btn-primary';
            $this->filter_label->setLabel('Filters ('. TSession::getValue(get_class($this).'_filter_counter').')');
        }
        else
        {
            $this->filter_label->class = 'btn btn-default';
            $this->filter_label->setLabel('Filtros');
        }
        
        if (!empty(TSession::getValue(get_class($this).'_filter_data')))
        {
            $obj = new stdClass;
            $obj->type = TSession::getValue(get_class($this).'_filter_data')->type;
            TForm::sendData('form_search_type', $obj);
        }
    }

    public static function onChangeLimit($param)
    {
        TSession::setValue(__CLASS__ . '_limit', $param['limit']);
        AdiantiCoreApplication::loadPage(__CLASS__, 'onReload');
    }

    public static function onShowCurtainFilters($param = null)
    {
        try
        {
            $page = new TPage;
            $page->setTargetContainer('adianti_right_panel');
            $page->setProperty('override', 'true');
            $page->setPageName(__CLASS__);
            
            $btn_close = new TButton('closeCurtain');
            $btn_close->onClick = "Template.closeRightPanel();";
            $btn_close->setLabel("Fechar");
            $btn_close->setImage('fas:times');
            
            $embed = new self;
            $embed->form->addHeaderWidget($btn_close);
            
            $page->add($embed->form);
            $page->setIsWrapped(true);
            $page->show();
        }
        catch (Exception $e) 
        {
            new TMessage('error', $e->getMessage());    
        }
    }
}
