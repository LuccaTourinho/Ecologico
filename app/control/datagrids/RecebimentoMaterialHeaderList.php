<?php

class RecebimentoMaterialHeaderList extends TStandardList
{
    protected $form;     // formulário de pesquisa
    protected $datagrid; // listagem
    protected $pageNavigation;
    protected $formgrid;
    protected $deleteButton;
    protected $transformCallback;
    
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
        $name = new TEntry('id_recebimentomaterial');
        $this->form->addFields([new TLabel(_t('Name'))], [$name]);
        $name->setSize('100%');
        
        // Preenche o formulário com os dados da sessão
        $this->form->setData(TSession::getValue('RecebimentoMaterialHeaderList_filter_data'));
        
        // Adiciona ação de pesquisa ao formulário
        $btn = $this->form->addAction('Pesquisar', new TAction([$this, 'onSearch']), 'fa:search');
        $btn->class = 'btn btn-sm btn-primary';
        // Cria a listagem
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
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

        // Cria as ações de coluna do datagrid
        $order_id = new TAction(array($this, 'onReload'));
        $order_id->setParameter('order', 'id_recebimentomaterial');
        $column_id->setAction($order_id);
        
        /*$order_name = new TAction(array($this, 'onReload'));
        $order_name->setParameter('order', 'id_recebimentomaterial');
        $column_name->setAction($order_name);*/
        
        
        // Cria ação de EDIÇÃO
        $action_edit = new TDataGridAction(array('RecebimentoMaterialForm', 'onEdit'), ['register_state' => 'false']);
        $action_edit->setButtonClass('btn btn-default');
        $action_edit->setLabel(_t('Edit'));
        $action_edit->setImage('far:edit blue ');
        $action_edit->setField('id_recebimentomaterial');
        $this->datagrid->addAction($action_edit);
        
        // Cria ação de EXCLUSÃO
        $action_del = new TDataGridAction(array($this, 'onDelete'));
        $action_del->setButtonClass('btn btn-default');
        $action_del->setLabel(_t('Delete'));
        $action_del->setImage('far:trash-alt red ');
        $action_del->setField('id_recebimentomaterial');
        $this->datagrid->addAction($action_del);
        
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

        // Criação do formulário de pesquisa
        $btnf = TButton::create('find', [$this, 'onSearch'], '', 'fa:search');
        $btnf->style= 'height: 37px; margin-right:4px;';

        $form_search = new TForm('form_search_name');
        $form_search->style = 'float:left;display:flex';
        $form_search->add($name, true);
        $form_search->add($btnf, true);
        
        // Adiciona o formulário de pesquisa ao painel como cabeçalho
        $panel->addHeaderWidget($form_search);

        // Adiciona link de ação de adição
        $panel->addHeaderActionLink('', new TAction(['RecebimentoMaterialForm', 'onEdit'], ['register_state' => 'false']), 'fa:plus');
        $this->filter_label = $panel->addHeaderActionLink('Filtros', new TAction([$this, 'onShowCurtainFilters']), 'fa:filter');
        
        // Ações do cabeçalho
        $dropdown = new TDropDown(_t('Export'), 'fa:list');
        $dropdown->style = 'height:37px';
        $dropdown->setPullSide('right');
        $dropdown->setButtonClass('btn btn-default waves-effect dropdown-toggle');
        $dropdown->addAction( _t('Save as CSV'), new TAction([$this, 'onExportCSV'], ['register_state' => 'false', 'static'=>'1']), 'fa:table fa-fw blue' );
        $dropdown->addAction( _t('Save as PDF'), new TAction([$this, 'onExportPDF'], ['register_state' => 'false', 'static'=>'1']), 'far:file-pdf fa-fw red' );
        $dropdown->addAction( _t('Save as XML'), new TAction([$this, 'onExportXML'], ['register_state' => 'false', 'static'=>'1']), 'fa:code fa-fw green' );
        $panel->addHeaderWidget( $dropdown );
        
        // Ações do cabeçalho
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

    public static function onChangeLimit($param)
        {
            TSession::setValue(__CLASS__ . '_limit', $param['limit'] );
            AdiantiCoreApplication::loadPage(__CLASS__, 'onReload');
        }
        
        /**
         *
         */
        public static function onShowCurtainFilters($param = null)
        {
            try
            {
                // create empty page for right panel
                $page = new TPage;
                $page->setTargetContainer('adianti_right_panel');
                $page->setProperty('override', 'true');
                $page->setPageName(__CLASS__);
                
                $btn_close = new TButton('closeCurtain');
                $btn_close->onClick = "Template.closeRightPanel();";
                $btn_close->setLabel("Fechar");
                $btn_close->setImage('fas:times');
                
                // instantiate self class, populate filters in construct 
                $embed = new self;
                $embed->form->addHeaderWidget($btn_close);
                
                // embed form inside curtain
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
