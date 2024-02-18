CREATE TABLE Pessoa (
    id_pessoa SERIAL PRIMARY KEY, -- Identificador único da pessoa
    nm_pessoa VARCHAR(100) UNIQUE NOT NULL, -- Nome da pessoa (único e não nulo)
    dt_nascimento DATE NOT NULL, -- Data de nascimento da pessoa (não nulo)
    nu_cpf VARCHAR(14) UNIQUE NOT NULL, -- CPF da pessoa (único e não nulo)
    nu_rg VARCHAR(20) UNIQUE NOT NULL, -- RG da pessoa (único e não nulo)
    nu_cep VARCHAR(9) NOT NULL -- CEP da pessoa (não nulo)
);

CREATE TABLE Residuo (
    id_residuo SERIAL PRIMARY KEY, -- Identificador único do resíduo
    tp_residuo VARCHAR(100) UNIQUE NOT NULL -- Tipo de resíduo (único e não nulo)
);

CREATE TABLE produto (
    id_produto SERIAL PRIMARY KEY, -- Identificador único do produto
    nm_produto VARCHAR(100) NOT NULL, -- Nome do produto (não nulo)
    dt_produto DATE NOT NULL, -- Data do produto (não nulo)
    vl_real DECIMAL(10, 2) NOT NULL, -- Valor real do produto (não nulo)
    vl_eco DECIMAL(10, 2) NOT NULL -- Valor ecológico do produto (não nulo)
);

CREATE TABLE material_residual (
    id_materialresidual SERIAL PRIMARY KEY, -- Identificador único do material residual
    nm_materialresidual VARCHAR(100) NOT NULL, -- Nome do material residual (não nulo)
    id_residuo INTEGER NOT NULL, -- Chave estrangeira para o tipo de resíduo
    tp_unidademedida VARCHAR(50) NOT NULL, -- Tipo de unidade de medida (não nulo)
    vl_real DECIMAL(10, 2) NOT NULL, -- Valor real do material residual (não nulo)
    vl_eco DECIMAL(10, 2) NOT NULL, -- Valor ecológico do material residual (não nulo)
    FOREIGN KEY (id_residuo) REFERENCES Residuo(id_residuo) -- Chave estrangeira referenciando a tabela Residuo
);

CREATE TABLE recebimento_material (
    id_recebimentomaterial SERIAL PRIMARY KEY, -- Identificador único do recebimento de material
    id_pessoa INTEGER NOT NULL, -- Chave estrangeira para a pessoa que recebeu o material
    id_material INTEGER NOT NULL, -- Chave estrangeira para o tipo de material recebido
    qt_material INTEGER NOT NULL, -- Quantidade do material recebido
    vl_real DECIMAL(10, 2) NOT NULL, -- Valor real do recebimento
    vl_eco DECIMAL(10, 2) NOT NULL, -- Valor ecológico do recebimento
    FOREIGN KEY (id_pessoa) REFERENCES Pessoa(id_pessoa), -- Chave estrangeira referenciando a tabela Pessoa
    FOREIGN KEY (id_material) REFERENCES material_residual(id_materialresidual) -- Chave estrangeira referenciando a tabela Material Residual
);

CREATE TABLE entrada(
    id_entrada SERIAL PRIMARY KEY, -- Identificador único da entrada
    id_produto INTEGER NOT NULL, -- Chave estrangeira para o produto
    qt_produto INTEGER NOT NULL, -- Quantidade do produto
    vl_real DECIMAL(10, 2) NOT NULL, -- Valor real em R$
    vl_eco DECIMAL(10, 2) NOT NULL, -- Valor ecológico
    FOREIGN KEY (id_produto) REFERENCES produto(id_produto) -- Chave estrangeira referenciando a tabela Produto
);

CREATE TABLE saida (
    id_saida SERIAL PRIMARY KEY, -- Identificador único da saída
    id_produto INTEGER NOT NULL, -- Chave estrangeira para o produto
    qt_produto INTEGER NOT NULL, -- Quantidade do produto
    vl_real DECIMAL(10, 2) NOT NULL, -- Valor real em R$
    vl_eco DECIMAL(10, 2) NOT NULL, -- Valor ecológico
    FOREIGN KEY (id_produto) REFERENCES produto(id_produto) -- Chave estrangeira referenciando a tabela Produto
);
