# Documentação do DomPagamento API

Este é um pacote PHP para interagir com a API do DomPagamento.

## Requisitos

- PHP 7.4 ou superior

## Instalação

Você pode instalar este pacote via Composer. Execute o seguinte comando no terminal:

```<?php

<?php
use DomPagamento\DomPagamento;

// Inclua o autoloader do Composer
require 'vendor/autoload.php';

// Inicialize o DomPagamento com ambiente de sandbox
$domPagamento = new DomPagamento('sandbox');

// Configure a chave secreta
$domPagamento->setSecretKey('your_secret_key');

// Altere o ambiente para produção
$domPagamento->setEnv('production');

// Configure a URL de postback
$domPagamento->setPostbackUrl('https://your-domain.com/postback');

// Configure os detalhes do pagamento
$domPagamento->setCodExternal(12345);
$domPagamento->setTotal(100.00);
$domPagamento->setDiscount(10.00);
$domPagamento->setPaymentMethod('credit_card');

// Configure os dados do cliente
$domPagamento->setCustomer([
    'name' => 'Cleomar Campos',
    'email' => 'cleomarcampos@example.com',
    'document' => '12345678909',
    'phone' => '11999999999'
]);

// Configure o endereço de pagamento
$domPagamento->setPaymentAddress([
    'street' => 'Rua Exemplo',
    'number' => '123',
    'neighborhood' => 'Bairro Exemplo',
    'city' => 'Cidade Exemplo',
    'state' => 'SP',
    'zipcode' => '12345678'
]);

// Configure o endereço de envio
$domPagamento->setShippingAddress([
    'street' => 'Rua Exemplo',
    'number' => '123',
    'neighborhood' => 'Bairro Exemplo',
    'city' => 'Cidade Exemplo',
    'state' => 'SP',
    'zipcode' => '12345678'
]);

// Adicione itens à transação
$domPagamento->addItem([
    'name' => 'Produto Exemplo 1',
    'quantity' => 1,
    'price' => 50.00
])->addItem([
    'name' => 'Produto Exemplo 2',
    'quantity' => 2,
    'price' => 25.00
]);

// Crie a transação
$response = $domPagamento->createTransaction([
    'installments' => 1,
    'description' => 'Compra de teste'
]);

// Exiba a resposta da API
var_dump($response);


3. **Salvar o Arquivo**: Salve o arquivo após inserir o conteúdo acima.

Este exemplo fornece uma estrutura básica para documentar o uso do pacote PHP para interagir com a API do DomPagamento. Você pode ajustar e adicionar mais detalhes conforme necessário para atender às necessidades de documentação do seu projeto.
