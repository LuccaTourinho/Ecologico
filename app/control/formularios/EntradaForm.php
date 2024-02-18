<?php

class EntradaForm extends TPage
{
    private $form;

    public function __construct()
    {
        parent::__construct();

        // Cria um novo formulário
        $this->form = new BootstrapFormBuilder('form_entrada');
        $this->form->setFormTitle('Cadastro de Entrada de Produto');

        // Campos do formulário
        $id = new TEntry('id_entrada');
        $produto = new TDBCombo('id_produto', 'ecologico', 'Produto', 'id_produto', 'nm_produto');
        $quantidade = new TEntry('qt_produto');
        $valorReal = new TEntry('vl_real');
        $valorEco = new TEntry('vl_eco');

        // Configurações dos campos
        $id->setEditable(false);
        $quantidade->setNumericMask(0, ',', '.', true);
        $valorReal->setNumericMask(2, ',', '.', true);
        $valorEco->setNumericMask(2, ',', '.', true);

        // Adiciona os campos ao formulário
        $this->form->addFields(
            [new TLabel('ID')], [$id],
            [new TLabel('Produto')], [$produto],
            [new TLabel('Quantidade')], [$quantidade],
            [new TLabel('Valor Real')], [$valorReal],
            [new TLabel('Valor Ecológico')], [$valorEco]
        );

        // Botão de salvar
        $btn_save = new TButton('save');
        $btn_save->setAction(new TAction([$this, 'onSave']), 'Salvar');
        $btn_save->setProperty('class', 'btn btn-sm btn-primary');

        // Adiciona o botão de salvar ao formulário
        $this->form->addAction($btn_save);

        // Layout do formulário
        $vbox = new TVBox;
        $vbox->style = 'width: 100%';
        $vbox->add($this->form);

        // Adiciona o layout à página
        parent::add($vbox);
    }

    // Método chamado quando o botão salvar é acionado
    public function onSave($param)
    {
        try {
            // Implementar aqui a lógica de salvamento no banco de dados

            // Exemplo de mensagem de sucesso
            new TMessage('info', 'Dados salvos com sucesso!');
        } catch (Exception $e) {
            // Exemplo de mensagem de erro
            new TMessage('error', 'Erro ao salvar os dados: ' . $e->getMessage());
        }
    }
}
