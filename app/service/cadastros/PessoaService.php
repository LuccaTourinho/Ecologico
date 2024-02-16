<?php

class PessoaService
{
    function validarData($dt_nascimento) 
    {
        // Obtém a data atual
        $data_atual = new DateTime('today');
        
        // Verifica se a data de nascimento é posterior à data atual
        if ($dt_nascimento > $data_atual->format('Y-m-d')) 
        {
            throw new Exception("Data de nascimento impossível");
        }
    }

    function validarCPF($cpf) {
        // Remove caracteres não numéricos do CPF
        $cpf = preg_replace('/[^0-9]/', '', $cpf);
    
        // Verifica se o CPF tem 11 dígitos
        if (strlen($cpf) !== 11) {
            throw new Exception("Formato inválido");
        }
    
        // Formata o CPF para o formato "111.111.111-11" se estiver no formato "11111111111"
        if (preg_match('/^(\d{3})(\d{3})(\d{3})(\d{2})$/', $cpf, $matches)) 
        {
            $cpf = $matches[1] . '.' . $matches[2] . '.' . $matches[3] . '-' . $matches[4];
        } 
        else
        {
            // Se não estiver no formato esperado, lança uma exceção
            throw new Exception("Formato inválido");
        }
    
        // Retorna o CPF formatado
        return $cpf;
    }

    function validarCep($cep) {
        // Remove caracteres não numéricos do CEP
        $cep = preg_replace('/[^0-9]/', '', $cep);
    
        // Verifica se o CEP tem 8 ou 9 dígitos
        if (strlen($cep) !== 8) 
        {
            throw new Exception("Formato inválido");
        }
    
        // Formata o CEP para o formato "11111-111" se estiver no formato "11111111"
        if (preg_match('/^(\d{5})(\d{3})$/', $cep, $matches)) 
        {
            $cep = $matches[1] . '-' . $matches[2];
        } 
        elseif (!preg_match('/^\d{5}-\d{3}$/', $cep)) 
        {
            // Se não estiver no formato esperado, lança uma exceção
            throw new Exception("Formato inválido");
        }
    
        // Retorna o CEP formatado
        return $cep;
    }
    
}