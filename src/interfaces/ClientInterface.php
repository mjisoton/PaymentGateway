<?php
namespace PaymentGateway\interfaces;

/**
 * 	interface ClientInterface
 * 	Define os métodos a serem implementados nas classes de entidades de clientes
 */
interface ClientInterface {
    const DS = DIRECTORY_SEPARATOR;

    public function create(array $data) : GatewayAnswerInterface;
    public function update(string $code, array $data) : GatewayAnswerInterface;
    public function delete(string $code) : GatewayAnswerInterface;
    public function get(string $code) : GatewayAnswerInterface;
}

?>