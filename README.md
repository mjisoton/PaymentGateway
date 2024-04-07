# PaymentGateway
Esta biblioteca permite que uma aplicação possa interagir com diferentes gateways de pagamento através de uma mesma interface. Com isso, não há a necessidade de reescrever a aplicação em situações onde sua empresa precisa alterar o gateway de pagamentos. 
A classe *PaymentGateway* age como um *Factory* e instancia o objeto desejado, passando através de *__call()* os métodos chamados externamente. 

## Como extender
Todos os gateways desenvolvidos devem ficar em *src/gateways*, e devem obrigatoriamente implementar *GatewayInterface*. Ainda, todo gateway deve obrigatoriamente retornar em seus métodos objetos que implementam *GatewayAnswerInterface*. 

## Como usar 
Ao usar composer, incluir o autoloader e chamar o objeto.
Claro, não se esquecer de *composer install* nas dependências. 

```php
<?php
require './PaymentGateway/vendor/autoload.php';
use AgenciaNet\PaymentGateway;

$gateway = new PaymentGateway('Cobrefacil', [
	'app_id'	=> '1234567890',
	'secret'	=> 'secret'
], 'sandbox');
?>
```

E para chamar métodos... 

```php
$cliente = $gateway->createClient([
	'nome'	=> 'João da Silva'
]);

$sucesso 	= $cliente->isSuccess();
$id 		= $cliente->getId();
$mensagem	= $cliente->getMessage();
$erros		= $cliente->getErrors();
```

E por aí vai... 
Estou implementando no tempo livre. Se algum momento isso estiver funcionando, irei atualizar.