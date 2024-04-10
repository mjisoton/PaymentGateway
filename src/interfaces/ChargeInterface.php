<?php
namespace PaymentGateway\interfaces;

/**
 * 	interface ChargeInterface
 * 	Define os métodos a serem implementados nas classes de entidades de cobranças
 */
interface ChargeInterface {
    const DS = DIRECTORY_SEPARATOR;

    public function create(array $data) : GatewayAnswerInterface;
}

?>