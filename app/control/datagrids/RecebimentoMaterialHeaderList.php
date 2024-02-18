<?php

class RecebimentoMaterialHeaderList extends TStandardList
{
    protected $form;     // formulário de pesquisa
    protected $datagrid; // listagem
    protected $pageNavigation;
    
    public function __construct()
    {
        parent::__construct();
        
        // Define o banco de dados e o Active Record
        parent::setDatabase('ecologico');
        parent::setActiveRecord('RecebimentoMaterial');
        parent::setDefaultOrder('id_recebimentomaterial', 'asc');
        parent::addFilterField('id_recebimentomaterial', '=', 'id_recebimentomaterial');
        
        // Define a quantidade de registros por página
        parent::setLimit(TSession::getValue('RecebimentoMaterialHeaderList_limit') ?? 10);
        
        // Cria o formulário de pesquisa
        $this->form = new BootstrapFormBuilder('form_search_RecebimentoMaterial');
        $this->form->setFormTitle('Lista de Recebimento de Material');
        
        // Adiciona os campos de pesquisa ao formulário
        $id_recebimentomaterial = new TEntry('id_recebimentomaterial');
        $this->form->addFields([new TLabel('ID')], [$id_recebimentomaterial]);
        
        // Define os tamanhos dos campos
        $id_recebimentomaterial->setSize('70%');
        
        // Preenche o formulário com os dados da sessão
        $this->form->setData(TSession::getValue('RecebimentoMaterialHeaderList_filter_data'));
        
        // Adiciona ação de pesquisa ao formulário
        $this->form->addAction('Pesquisar', new TAction([$this, 'onSearch']), 'fa:search');
        
        // Cria a listagem
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->enablePopover('Detalhes', '<b>ID:</b> {id_recebimentomaterial} <br> <b>Pessoa:</b> {pessoa->nm_pessoa} <br> <b>Material Residual:</b> {material->nm_materialresidual} <br> <b>Quantidade do Material:</b> {qt_material} <br> <b>Valor Real:</b> {vl_real} <br> <b>Valor Ecológico:</b> {vl_eco}');
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->setHeight(320);
        
        // Cria as colunas da listagem
        $column_id = new TDataGridColumn('id_recebimentomaterial', 'ID', 'center', '10%');
        $column_pessoa = new TDataGridColumn('pessoa->nm_pessoa', 'Pessoa', 'left');
        $column_material = new TDataGridColumn('material->nm_materialresidual', 'Material Residual', 'left');
        $column_qt_material = new TDataGridColumn('qt_material', 'Quantidade do Material', 'left');
        $column_vl_real = new TDataGridColumn('vl_real', 'Valor Real', 'left');
        $column_vl_eco = new TDataGridColumn('vl_eco', 'Valor Ecológico', 'left');
        
        // Adiciona as colunas à listagem
        $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_pessoa);
        $this->datagrid->addColumn($column_material);
        $this->datagrid->addColumn($column_qt_material);
        $this->datagrid->addColumn($column_vl_real);
        $this->datagrid->addColumn($column_vl_eco);
        
        // Cria o modelo da listagem
        $this->datagrid->createModel();
        
        // Cria a navegação entre páginas
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->enableCounters();
        $this->pageNavigation->setAction(new TAction([$this, 'onReload']));
        $this->pageNavigation->setWidth($this->datagrid->getWidth());
        
        // Cria um painel e adiciona a listagem e a navegação ao painel
        $panel = new TPanelGroup;
        $panel->add($this->datagrid)->style = 'overflow-x:auto';
        $panel->addFooter($this->pageNavigation);
        
        // Adiciona o formulário de pesquisa ao painel como cabeçalho
        $panel->addHeaderWidget($this->form);
        
        // Adiciona o painel à página
        $container = new TVBox;
        $container->style = 'width: 100%';
        $container->add($panel);
        parent::add($container);
    }
    
    // Executado após a pesquisa
    public function onAfterSearch($datagrid, $options)
    {
        // Atualiza o botão de filtro com a quantidade de filtros aplicados
        if (TSession::getValue('RecebimentoMaterialHeaderList_filter_counter') > 0)
        {
            $this->filter_label->class = 'btn btn-primary';
            $this->filter_label->setLabel('Filtros (' . TSession::getValue('RecebimentoMaterialHeaderList_filter_counter') . ')');
        }
        else
        {
            $this->filter_label->class = 'btn btn-default';
            $this->filter_label->setLabel('Filtros');
        }
        
        // Preenche o campo de pesquisa com os dados da sessão
        if (!empty(TSession::getValue('RecebimentoMaterialHeaderList_filter_data')))
        {
            $obj = new stdClass;
            $obj->id_recebimentomaterial = TSession::getValue('RecebimentoMaterialHeaderList_filter_data')->id_recebimentomaterial;
            TForm::sendData('form_search_RecebimentoMaterial', $obj);
        }
    }
}
