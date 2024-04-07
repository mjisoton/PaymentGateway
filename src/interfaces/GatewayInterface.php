<?php
namespace AgenciaNet\interfaces;

/**
 * 	interface GatewayInterface
 * 	Define os métodos a serem implementados nas classes de gateways de pagamento, 
 * 	de forma que elas possam ser compatíveis e chamadas de forma idêntica pelo 
 * 	GatewayFactory.
 */
interface GatewayInterface {
    const DS = DIRECTORY_SEPARATOR;

    //Autenticação
    public function setCredentials(array $credentials);
    public function setEnvironment(string $environment);

    //Operações com clientes
    public function createClient(array $data) : GatewayAnswerInterface;
    public function updateClient(string $code, array $data) : GatewayAnswerInterface;
    public function deleteClient(string $code) : GatewayAnswerInterface;

    //Operações com cobranças
    public function createCharge(string $type) : GatewayAnswerInterface;

    //Operações com notas fiscais
    public function createInvoice() : GatewayAnswerInterface;
    public function deleteInvoice(string $code) : GatewayAnswerInterface;
}

?>