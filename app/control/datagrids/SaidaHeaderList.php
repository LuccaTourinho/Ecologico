<?php

class SaidaHeaderList extends TStandardList
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
        
        parent::setDatabase('ecologico');
        parent::setActiveRecord('Saida');
        parent::setDefaultOrder('id_saida', 'desc');
        parent::addFilterField('id_saida', '=', 'id_saida');
        
        parent::setLimit(TSession::getValue(__CLASS__ . '_limit') ?? 10);
        
        $this->form = new BootstrapFormBuilder('form_search_Saida');
        $this->form->setFormTitle('Saídas de Produtos');
        
        #$id = new TEntry('id_saida');
        #$produto = new TEntry('id_produto');
        $qt_produto = new TEntry('qt_produto');
        #$vl_real = new TEntry('vl_real');
        #$vl_eco = new TEntry('vl_eco');
    
        #$this->form->addFields([new TLabel('ID')], [$id]);
        #$this->form->addFields([new TLabel('Produto')], [$produto]);
        $this->form->addFields([new TLabel('Quantidade')], [$qt_produto]);
        #$this->form->addFields([new TLabel('Valor Real')], [$vl_real]);
        #$this->form->addFields([new TLabel('Valor Ecológico')], [$vl_eco]);
        
        #$id->setSize('30%');
        #$produto->setSize('30%');
        #$qt_produto->setSize('100%');
        #$vl_real->setSize('20%');
        #$vl_eco->setSize('20%');
        
        $this->form->setData(TSession::getValue('Saida_filter_data'));
        
        $btn = $this->form->addAction('Search', new TAction([$this, 'onSearch']), 'fa:search');
        #$this->form->addAction('Clear', new TAction([$this, 'clearFilters']), 'fa:eraser red');
        $btn->class = 'btn btn-sm btn-primary';


        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->setHeight(320);
        
        $column_id = new TDataGridColumn('id_saida', 'ID', 'center', 50);
        $column_produto = new TDataGridColumn('produto->nm_produto', 'Produto', 'left');
        $column_qt_produto = new TDataGridColumn('qt_produto', 'Quantidade', 'left');
        $column_vl_real = new TDataGridColumn('vl_real', 'Valor Real', 'left');
        $column_vl_eco = new TDataGridColumn('vl_eco', 'Valor Ecológico', 'left');
        
        $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_produto);
        $this->datagrid->addColumn($column_qt_produto);
        $this->datagrid->addColumn($column_vl_real);
        $this->datagrid->addColumn($column_vl_eco);

        // cria as ações de coluna do datagrid
        $order_id = new TAction([$this, 'onReload']);
        $order_id->setParameter('order', 'id_saida');
        $column_id->setAction($order_id);
        
        $order_quantidade = new TAction([$this, 'onReload']);
        $order_quantidade->setParameter('order', 'qt_produto');
        $column_produto->setAction($order_quantidade);
        
        $action_edit = new TDataGridAction(['SaidaForm', 'onEdit']);
        $action_edit->setButtonClass('btn btn-default');
        $action_edit->setLabel('Edit');
        $action_edit->setImage('far:edit blue');
        $action_edit->setField('id_saida');
        $this->datagrid->addAction($action_edit);
        
        $action_del = new TDataGridAction([$this , 'onDelete']);
        $action_del->setButtonClass('btn btn-default');
        $action_del->setLabel('Delete');
        $action_del->setImage('far:trash-alt red');
        $action_del->setField('id_saida');
        $this->datagrid->addAction($action_del);
        
        $this->datagrid->createModel();

        // cria a navegação de página
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->enableCounters();
        $this->pageNavigation->setAction(new TAction([$this, 'onReload']));
        $this->pageNavigation->setWidth($this->datagrid->getWidth());
        
        $panel = new TPanelGroup;
        $panel->add($this->datagrid)->style='overflow-x:auto';
        $panel->addFooter($this->pageNavigation);

        $btnf = TButton::create('find', [$this, 'onSearch'], '', 'fa:search');
        $btnf->style = 'height: 37px; margin-right:4px;';

        $form_search = new TForm('form_search_description');
        $form_search->style = 'float:left;display:flex';
        $form_search->add($qt_produto, true);
        $form_search->add($btnf, true);

        $panel->addHeaderWidget($form_search);

        $panel->addHeaderActionLink('', new TAction(['SaidaForm', 'onEdit'], ['register_state' => 'false']), 'fa:plus');
        $this->filter_label = $panel->addHeaderActionLink('Filtros', new TAction([$this, 'onShowCurtainFilters']), 'fa:filter');
        
        // ações do cabeçalho
        $dropdown = new TDropDown(_t('Export'), 'fa:list');
        $dropdown->style = 'height:37px';
        $dropdown->setPullSide('right');
        $dropdown->setButtonClass('btn btn-default waves-effect dropdown-toggle');
        $dropdown->addAction(_t('Save as CSV'), new TAction([$this, 'onExportCSV'], ['register_state' => 'false', 'static' => '1']), 'fa:table fa-fw blue');
        $dropdown->addAction(_t('Save as PDF'), new TAction([$this, 'onExportPDF'], ['register_state' => 'false', 'static' => '1']), 'far:file-pdf fa-fw red');
        $dropdown->addAction(_t('Save as XML'), new TAction([$this, 'onExportXML'], ['register_state' => 'false', 'static' => '1']), 'fa:code fa-fw green');
        $panel->addHeaderWidget($dropdown);
        
        // ações do cabeçalho
        $dropdown = new TDropDown(TSession::getValue(__CLASS__ . '_limit') ?? '10', '');
        $dropdown->style = 'height:37px';
        $dropdown->setPullSide('right');
        $dropdown->setButtonClass('btn btn-default waves-effect dropdown-toggle');
        $dropdown->addAction(10, new TAction([$this, 'onChangeLimit'], ['register_state' => 'false', 'static' => '1', 'limit' => '10']));
        $dropdown->addAction(20, new TAction([$this, 'onChangeLimit'], ['register_state' => 'false', 'static' => '1', 'limit' => '20']));
        $dropdown->addAction(50, new TAction([$this, 'onChangeLimit'], ['register_state' => 'false', 'static' => '1', 'limit' => '50']));
        $dropdown->addAction(100, new TAction([$this, 'onChangeLimit'], ['register_state' => 'false', 'static' => '1', 'limit' => '100']));
        $dropdown->addAction(1000, new TAction([$this, 'onChangeLimit'], ['register_state' => 'false', 'static' => '1', 'limit' => '1000']));
        $panel->addHeaderWidget($dropdown);
        
        if (TSession::getValue(get_class($this) . '_filter_counter') > 0) {
            $this->filter_label->class = 'btn btn-primary';
            $this->filter_label->setLabel('Filtros (' . TSession::getValue(get_class($this) . '_filter_counter') . ')');
        }
        
        $container = new TVBox;
        $container->style = 'width: 100%';
        $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        #$container->add($this->form);
        $container->add($panel);
        
        parent::add($container);
    }

    /**
     *
     */
    public function onAfterSearch($datagrid, $options)
    {
        if (TSession::getValue(get_class($this) . '_filter_counter') > 0) {
            $this->filter_label->class = 'btn btn-primary';
            $this->filter_label->setLabel('Filtros (' . TSession::getValue(get_class($this) . '_filter_counter') . ')');
        } else {
            $this->filter_label->class = 'btn btn-default';
            $this->filter_label->setLabel('Filtros');
        }
        
        if (!empty(TSession::getValue(get_class($this) . '_filter_data'))) {
            $obj = new stdClass;
            $obj->description = TSession::getValue(get_class($this) . '_filter_data')->description;
            TForm::sendData('form_search_description', $obj);
        }
    }
    
    /**
     *
     */
    public static function onChangeLimit($param)
    {
        TSession::setValue(__CLASS__ . '_limit', $param['limit']);
        AdiantiCoreApplication::loadPage(__CLASS__, 'onReload');
    }
    
    /**
     *
     */
    public static function onShowCurtainFilters($param = null)
    {
        try {
            // cria página vazia para o painel direito
            $page = new TPage;
            $page->setTargetContainer('adianti_right_panel');
            $page->setProperty('override', 'true');
            $page->setPageName(__CLASS__);
            
            $btn_close = new TButton('closeCurtain');
            $btn_close->onClick = "Template.closeRightPanel();";
            $btn_close->setLabel("Fechar");
            $btn_close->setImage('fas:times');
            
            // instanciar a própria classe, preencher filtros no construtor
            $embed = new self;
            $embed->form->addHeaderWidget($btn_close);
            
            // incorporar formulário dentro da cortina
            $page->add($embed->form);
            $page->setIsWrapped(true);
            $page->show();
        } catch (Exception $e) {
            new TMessage('error', $e->getMessage());    
        }
    }
}


