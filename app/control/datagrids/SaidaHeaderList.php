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
        
        $id = new TEntry('id_saida');
        $produto = new TEntry('id_produto');
        $qt_produto = new TEntry('qt_produto');
        $vl_real = new TEntry('vl_real');
        $vl_eco = new TEntry('vl_eco');
    
        $this->form->addFields([new TLabel('ID')], [$id]);
        $this->form->addFields([new TLabel('Produto')], [$produto]);
        $this->form->addFields([new TLabel('Quantidade')], [$qt_produto]);
        $this->form->addFields([new TLabel('Valor Real')], [$vl_real]);
        $this->form->addFields([new TLabel('Valor Ecológico')], [$vl_eco]);
        
        $id->setSize('30%');
        $produto->setSize('30%');
        $qt_produto->setSize('20%');
        $vl_real->setSize('20%');
        $vl_eco->setSize('20%');
        
        $this->form->setData(TSession::getValue('Saida_filter_data'));
        
        $this->form->addAction('Search', new TAction([$this, 'onSearch']), 'fa:search');
        $this->form->addAction('Clear', new TAction([$this, 'clearFilters']), 'fa:eraser red');
        
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->datatable = 'true';
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->setHeight(320);
        
        $column_id = new TDataGridColumn('id_saida', 'ID', 'center', '10%');
        $column_produto = new TDataGridColumn('produto->nm_produto', 'Produto', 'left');
        $column_qt_produto = new TDataGridColumn('qt_produto', 'Quantidade', 'left');
        $column_vl_real = new TDataGridColumn('vl_real', 'Valor Real', 'left');
        $column_vl_eco = new TDataGridColumn('vl_eco', 'Valor Ecológico', 'left');
        
        $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_produto);
        $this->datagrid->addColumn($column_qt_produto);
        $this->datagrid->addColumn($column_vl_real);
        $this->datagrid->addColumn($column_vl_eco);
        
        $action_edit = new TDataGridAction(['SaidaForm', 'onEdit']);
        $action_edit->setButtonClass('btn btn-default');
        $action_edit->setLabel('Edit');
        $action_edit->setImage('far:edit blue');
        $action_edit->setField('id_saida');
        
        $action_del = new TDataGridAction(['SaidaForm', 'onDelete']);
        $action_del->setButtonClass('btn btn-default');
        $action_del->setLabel('Delete');
        $action_del->setImage('far:trash-alt red');
        $action_del->setField('id_saida');
        
        $this->datagrid->addAction($action_edit);
        $this->datagrid->addAction($action_del);
        
        $this->datagrid->createModel();
        
        $panel = new TPanelGroup;
        $panel->add($this->datagrid)->style='overflow-x:auto';
        
        $container = new TVBox;
        $container->style = 'width: 100%';
        $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        $container->add($panel);
        
        parent::add($container);
    }
}

?>
