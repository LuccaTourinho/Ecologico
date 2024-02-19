
    <?php
   
    class PessoaHeaderList extends TStandardList
    {
        protected $form;     // registration form
        protected $datagrid; // listing
        protected $pageNavigation;
        protected $formgrid;
        protected $deleteButton;
        protected $transformCallback;
      
        public function __construct()
        {
            parent::__construct();
            
            parent::setDatabase('Ecologico');            // define o banco de dados
            parent::setActiveRecord('Pessoa');           // define o registro ativo
            parent::setDefaultOrder('id_pessoa', 'asc'); // define a ordem padrão
            parent::addFilterField('id_pessoa', '=', 'id_pessoa'); // campo de filtro, operador, campo de formulário
        
           
            
            parent::setLimit(TSession::getValue(__CLASS__ . '_limit') ?? 10);
            
            $this->form = new BootstrapFormBuilder('form_search_Pessoa');
            $this->form->setFormTitle('Pessoas');
            
            $name = new TEntry('nm_pessoa');
            $this->form->addFields( [new TLabel(_t('Name'))], [$name] );
            $name->setSize('100%');
            
            $this->form->setData( TSession::getValue('Pessoa_filter_data') );
            
            $btn = $this->form->addAction(_t('Find'), new TAction(array($this, 'onSearch')), 'fa:search');
            $btn->class = 'btn btn-sm btn-primary';
            
            
            $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
            $this->datagrid->style = 'width: 100%';
            $this->datagrid->setHeight(320);
            
            
            $column_id = new TDataGridColumn('id_pessoa', 'Id', 'center', 50);
            $column_controller = new TDataGridColumn('nm_pessoa', 'Nome', 'left');
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
    
            // Adiciona as colunas ao datagrid
            $this->datagrid->addColumn($column_id);
            $this->datagrid->addColumn($column_controller);
            $this->datagrid->addColumn($column_name);
            $this->datagrid->addColumn($column_menu);
    
    
            // Cria as ações de coluna do datagrid
            $order_id = new TAction(array($this, 'onReload'));
            $order_id->setParameter('order', 'id_pessoa');
            $column_id->setAction($order_id);
            
            $order_name = new TAction(array($this, 'onReload'));
            $order_name->setParameter('order', 'nm_pessoa');
            $column_name->setAction($order_name);
            
            
            // Cria ação de EDIÇÃO
            $action_edit = new TDataGridAction(array('PessoaForm', 'onEdit'), ['register_state' => 'false']);
            $action_edit->setButtonClass('btn btn-default');
            $action_edit->setLabel(_t('Edit'));
            $action_edit->setImage('far:edit blue ');
            $action_edit->setField('id_pessoa');
            $this->datagrid->addAction($action_edit);
            
            // Cria ação de EXCLUSÃO
            $action_del = new TDataGridAction(array($this, 'onDelete'));
            $action_del->setButtonClass('btn btn-default');
            $action_del->setLabel(_t('Delete'));
            $action_del->setImage('far:trash-alt red ');
            $action_del->setField('id_pessoa');
            $this->datagrid->addAction($action_del);
            
          
            // Cria o modelo do datagrid
            $this->datagrid->createModel();
            
            // Cria a navegação de páginas
            $this->pageNavigation = new TPageNavigation;
            $this->pageNavigation->enableCounters();
            $this->pageNavigation->setAction(new TAction(array($this, 'onReload')));
            $this->pageNavigation->setWidth($this->datagrid->getWidth());
            
            // Cria o painel
            $panel = new TPanelGroup;
            $panel->add($this->datagrid)->style='overflow-x:auto';
            $panel->addFooter($this->pageNavigation);
            
            // Criação do formulário de pesquisa
            $btnf = TButton::create('find', [$this, 'onSearch'], '', 'fa:search');
            $btnf->style= 'height: 37px; margin-right:4px;';
            
            $form_search = new TForm('form_search_name');
            $form_search->style = 'float:left;display:flex';
            $form_search->add($name, true);
            $form_search->add($btnf, true);
            
            $panel->addHeaderWidget($form_search);
            
            // Adiciona link de ação de adição
            $panel->addHeaderActionLink('', new TAction(['PessoaForm', 'onEdit'], ['register_state' => 'false']), 'fa:plus');
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
            
            // vertical box container
            $container = new TVBox;
            $container->style = 'width: 100%';
            $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
            //$container->add($this->form);
            $container->add($panel);
            
            parent::add($container);
        }
        
        /**
         *
         */
        public function onAfterSearch($datagrid, $options)
        {
            if (TSession::getValue(get_class($this).'_filter_counter') > 0)
            {
                $this->filter_label->class = 'btn btn-primary';
                $this->filter_label->setLabel('Filtros ('. TSession::getValue(get_class($this).'_filter_counter').')');
            }
            else
            {
                $this->filter_label->class = 'btn btn-default';
                $this->filter_label->setLabel('Filtros');
            }
            
            if (!empty(TSession::getValue(get_class($this).'_filter_data')))
            {
                $obj = new stdClass;
                $obj->name = TSession::getValue(get_class($this).'_filter_data')->name;
                TForm::sendData('form_search_name', $obj);
            }
        }
        
        /**
         *
         */
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
    
    


