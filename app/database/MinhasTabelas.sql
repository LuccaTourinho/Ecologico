CREATE TABLE Pessoa (
    id_pessoa SERIAL PRIMARY KEY, -- Identificador único da pessoa
    nm_pessoa VARCHAR(100) UNIQUE NOT NULL, -- Nome da pessoa (único e não nulo)
    dt_nascimento DATE NOT NULL, -- Data de nascimento da pessoa (não nulo)
    nu_cpf VARCHAR(14) UNIQUE NOT NULL, -- CPF da pessoa (único e não nulo)
    nu_rg VARCHAR(20) UNIQUE NOT NULL, -- RG da pessoa (único e não nulo)
    nu_cep VARCHAR(9) NOT NULL -- CEP da pessoa (não nulo)
);
