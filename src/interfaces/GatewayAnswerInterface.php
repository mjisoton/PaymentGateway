<?php
namespace AgenciaNet\interfaces;

/**
 *  interface GatewayAnswerInterface
 *  Define os métodos a srem implementados na classe de retorno de requisições 
 *  enviadas para os gateways de pagamento.
 */
interface GatewayAnswerInterface {

	//Identificação de estado
	public function isSuccess() : bool;

	//Obtenção de dados
	public function getMessage() : ?string;
	public function getId() : string|int;
	public function getFullBody() : ?array;
	public function getData(string $index) : ?string;
	public function shouldRetry() : bool;
}

?>