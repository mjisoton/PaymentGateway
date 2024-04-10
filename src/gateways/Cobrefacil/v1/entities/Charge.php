<?php
namespace PaymentGateway\gateways\Cobrefacil\v1\entities;

use PaymentGateway\interfaces\GatewayInterface;
use PaymentGateway\interfaces\ChargeInterface;
use PaymentGateway\interfaces\GatewayAnswerInterface;
use PaymentGateway\gateways\Cobrefacil\v1\Answer;

/**
 * 	Charge
 * 	Classe para operações com cobranças, permitindo cadastro, edição, exclusão e busca
 *  
 * 	https://developers.cobrefacil.com.br/
 */
class Charge implements ChargeInterface {
    private GatewayInterface $gateway;

    function __construct(GatewayInterface $gateway) {
        $this->gateway = $gateway;
    }

    /**
     * 	create
     * 	Executa a operação de cadastro de clientes
     */
    public function create(array $data) : GatewayAnswerInterface {

        //Sanitiza os dados
        $data = self::getSanitizedData($data);

        //Executa a operação
        return $this->gateway->execRequest('POST', 'customers', $data);
    }

    /**
     *  getSanitizedData
     *  Cria um array que representa uma entidade de cobranças, a ser enviada para a API 
     *  do Cobrefácil
     */
    public static function getSanitizedData(array $data) : array {
        $result = array();

        //Sanitiza as strings
        $data = self::beautifyStrings($data);

        return $result;
    }

    /**
     *  beautifyStrings
     *  Percorre uma estrutura de dados, e ao encontrar strings, aplica uma série de 
     *  funções para padronizar o estilo. Especificamente o padrão de maiúsculas e minúsculas
     */
    private static function beautifyStrings(mixed $data) : mixed {
        $exceptions = array(
            
        );

        //Limpa whitespaces, coloca em caixa baixa e primeiras maiúsculas
		if(!is_array($data)) {
            return ucwords(mb_strtolower(trim($data)));
        }

        foreach($data as $k => $v) {
            if(in_array($k, $exceptions) === false) {
                $data[$k] = self::beautifyStrings($v);
            }
        }

        return $data;
    }

	
	/*
	 *	 stripNonNumericChars
	 *	 Remove caracteres não numéricos de uma string
	 */
	private static function stripNonNumericChars(?string $str) : ?string {
		if(is_null($str)):
			return null;
		endif;
		
		return preg_replace('/[^0-9]/', '', $str);
	}
}
?>