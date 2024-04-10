<?php
namespace PaymentGateway\interfaces;

/**
 * 	interface GatewayInterface
 * 	Define os métodos a serem implementados nas classes de gateways de pagamento, 
 * 	de forma que elas possam ser compatíveis e chamadas de forma idêntica pelo 
 * 	GatewayFactory.
 */
interface GatewayInterface {
    const DS = DIRECTORY_SEPARATOR;

    //Autenticação e versionamento
    public function setCredentials(array $credentials);
    public function setEnvironment(string $environment);
}

?>