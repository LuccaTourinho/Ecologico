<?php

class MaterialResidualHeaderList extends TStandardList
{
    protected $form;
    protected $datagrid;
    protected $pageNavigation;
    
    public function __construct()
    {
        parent::__construct();
        
        parent::setDatabase('ecologico');
        parent::setActiveRecord('MaterialResidual');
        parent::setDefaultOrder('id_materialresidual', 'asc');
        
        parent::addFilterField('id_materialresidual', '=', 'id_materialresidual');
        parent::addFilterField('nm_materialresidual', 'like', 'nm_materialresidual');
        
        parent::setLimit(TSession::getValue(__CLASS__ . '_limit') ?? 10);
        
        // Cria o formulário
        $this->form = new BootstrapFormBuilder('form_search_MaterialResidual');
        $this->form->setFormTitle('Material Residuals');
        
        // Cria os campos do formulário
        $name = new TEntry('nm_materialresidual');
        
        // Adiciona os campos ao formulário
        $this->form->addFields([new TLabel(_t('Name'))], [$name]);
        
        // Mantém o formulário preenchido durante a navegação com os dados da sessão
        $this->form->setData(TSession::getValue('MaterialResidual_filter_data'));
        
        // Adiciona ações ao formulário de pesquisa
        $btn = $this->form->addAction(_t('Find'), new TAction([$this, 'onSearch']), 'fa:search');
        $btn->class = 'btn btn-sm btn-primary';
        
        // Cria o datagrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->setHeight(320);
        
        // Cria as colunas do datagrid
        $column_id = new TDataGridColumn('id_materialresidual', 'ID', 'center', '10%');
        $column_name = new TDataGridColumn('nm_materialresidual', 'Name', 'left');
        $column_residue = new TDataGridColumn('id_residuo', 'Residue', 'left');
        $column_unit = new TDataGridColumn('tp_unidademedida', 'Unit of Measure', 'left');
        $column_real_value = new TDataGridColumn('vl_real', 'Real Value', 'left');
        $column_ecological_value = new TDataGridColumn('vl_eco', 'Ecological Value', 'left');
        
        // Adiciona as colunas ao datagrid
        $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_name);
        $this->datagrid->addColumn($column_residue);
        $this->datagrid->addColumn($column_unit);
        $this->datagrid->addColumn($column_real_value);
        $this->datagrid->addColumn($column_ecological_value);
        
        // Cria o modelo do datagrid
        $this->datagrid->createModel();
        
        // Cria a navegação de página
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->enableCounters();
        $this->pageNavigation->setAction(new TAction([$this, 'onReload']));
        $this->pageNavigation->setWidth($this->datagrid->getWidth());
        
        // Cria um painel
        $panel = new TPanelGroup;
        $panel->add($this->datagrid)->style = 'overflow-x:auto';
        $panel->addFooter($this->pageNavigation);
        
        // Adiciona um botão de pesquisa ao cabeçalho
        $btnf = TButton::create('find', [$this, 'onSearch'], '', 'fa:search');
        $btnf->style = 'height: 37px; margin-right:4px;';
        
        $form_search = new TForm('form_search_name');
        $form_search->style = 'float:left;display:flex';
        $form_search->add($name, true);
        $form_search->add($btnf, true);
        
        $panel->addHeaderWidget($form_search);
        
        // Adiciona um link de ação ao cabeçalho
        $panel->addHeaderActionLink('', new TAction(['MaterialResidualForm', 'onEdit']), 'fa:plus');
        
        // Adiciona um link de ação ao cabeçalho
        $dropdown = new TDropDown(_t('Export'), 'fa:list');
        $dropdown->style = 'height:37px';
        $dropdown->setPullSide('right');
        $dropdown->setButtonClass('btn btn-default waves-effect dropdown-toggle');
        $dropdown->addAction(_t('Save as CSV'), new TAction([$this, 'onExportCSV']), 'fa:table fa-fw blue');
        $dropdown->addAction(_t('Save as PDF'), new TAction([$this, 'onExportPDF']), 'far:file-pdf fa-fw red');
        $dropdown->addAction(_t('Save as XML'), new TAction([$this, 'onExportXML']), 'fa:code fa-fw green');
        $panel->addHeaderWidget($dropdown);
        
        // Adiciona um link de ação ao cabeçalho
        $dropdown = new TDropDown(TSession::getValue(__CLASS__ . '_limit') ?? '10', '');
        $dropdown->style = 'height:37px';
        $dropdown->setPullSide('right');
        $dropdown->setButtonClass('btn btn-default waves-effect dropdown-toggle');
        $dropdown->addAction(10, new TAction([$this, 'onChangeLimit']), '10');
        $dropdown->addAction(20, new TAction([$this, 'onChangeLimit']), '20');
        $dropdown->addAction(50, new TAction([$this, 'onChangeLimit']), '50');
        $dropdown->addAction(100, new TAction([$this, 'onChangeLimit']), '100');
        $dropdown->addAction(1000, new TAction([$this, 'onChangeLimit']), '1000');
        $panel->addHeaderWidget($dropdown);
        
        // Adiciona o painel à página
        parent::add($panel);
    }
    
    public function onReload($param = NULL)
    {
        try
        {
            TTransaction::open($this->database);
            
            // Recarrega o datagrid
            $repository = new TRepository('MaterialResidual');
            $limit = $this->pageNavigation->getLimit();
            $criteria = new TCriteria;
            $criteria->setProperty('order', $this->order);
            $criteria->setProperty('limit', $limit);
            $criteria->setProperty('offset', $this->pageNavigation->getOffset());
            
            foreach ($this->filterFields as $filter)
            {
                if ($filter->value)
                {
                    $criteria->add($filter->key, $filter->operator, $filter->value);
                }
            }
            
            $objects = $repository->load($criteria, FALSE);
            $this->datagrid->clear();
            if ($objects)
            {
                foreach ($objects as $object)
                {
                    $this->datagrid->addItem($object);
                }
            }
            
            // Verifica se a contagem é necessária
            if (TSession::getValue(__CLASS__ . '_count') === NULL)
            {
                $count = $repository->count($criteria);
                TSession::setValue(__CLASS__ . '_count', $count);
            }
            
            TTransaction::close();
            $this->loaded = true;
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }
    
    public static function onChangeLimit($param)
    {
        TSession::setValue(__CLASS__ . '_limit', $param['limit']);
        AdiantiCoreApplication::loadPage(__CLASS__, 'onReload');
    }
}
