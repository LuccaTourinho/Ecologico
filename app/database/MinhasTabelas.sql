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
