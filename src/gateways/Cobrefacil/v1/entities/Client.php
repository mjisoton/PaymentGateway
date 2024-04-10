<?php
namespace PaymentGateway\gateways\Cobrefacil\v1\entities;

use PaymentGateway\interfaces\GatewayInterface;
use PaymentGateway\interfaces\ClientInterface;
use PaymentGateway\interfaces\GatewayAnswerInterface;
use PaymentGateway\gateways\Cobrefacil\v1\Answer;

/**
 * 	Client
 * 	Classe para operações com clientes, permitindo cadastro, edição, exclusão e busca
 *  
 * 	https://developers.cobrefacil.com.br/
 */
class Client implements ClientInterface {
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
     *  update
     *  Executa a operação de edição de clientes previamente cadastrados
     */
    public function update(string $code, array $data) : GatewayAnswerInterface {

        //Sanitiza os dados
        $data = self::getSanitizedData($data);

        //Executa a operação
        return $this->gateway->execRequest('PUT', 'customers/' . $code, $data);
    }

    /**
     *  delete
     *  Executa a operação de exclusão de clientes
     */
    public function delete(string $code) : GatewayAnswerInterface {
        die('teste');
        $cliente = $this->gateway->execRequest('DELETE', 'customers/' . $code);

        return $cliente;
    }

    /**
     *  get
     *  Executa a operação de busca de detalhes de clientes
     */
    public function get(string $code) : GatewayAnswerInterface {
        return $this->gateway->execRequest('GET', 'customers/' . $code);
    }

    /**
     *  SanitizeClient
     *  Cria um array que representa uma entidade de cliente, a ser enviada para a API 
     *  do Cobrefácil para cadastro e edição 
     */
    public static function getSanitizedData(array $data) : array {
        $result = array();

        //Sanitiza as strings
        $data = self::beautifyStrings($data);

        //Tipo de entidade
        if($data['pessoa-tipo'] === 'F') {
            $result = array_merge($result, array(
                'person_type'   => 1,
                'taxpayer_id'   => self::stripNonNumericChars($data['documento']),
                'personal_name' => $data['nome']
            ));
        } else {
            $result = array_merge($result, array(
                'person_type'   => 2,
                'ein'           => self::stripNonNumericChars($data['documento']),
                'company_name'  => $data['nome']
            ));
        }

        //Telefones
        if(!empty($data['telefones'])) {
            $result = array_merge($result, array(
                'telephone' => self::stripNonNumericChars($data['telefones'][0])
            ));

            if(isset($data['telefones'][1])) {
                $result = array_merge($result, array(
                    'cellular' => self::stripNonNumericChars($data['telefones'][1])
                ));
            }
        }

        //E-mails
        if(!empty($data['emails'])) {
            $result = array_merge($result, array(
                'email' => $data['emails'][0]
            ));

            if(isset($data['emails'][1])) {
                $result = array_merge($result, array(
                    'email_cc' => $data['emails'][1]
                ));
            }
        }

        //Endereço
        if($data['endereco']) {
            $e = $data['endereco'];

            $address = array(
                'description'   => $e['descricao'],
                'zipcode'       => self::stripNonNumericChars($e['cep']),
                'street'        => $e['logradouro'],
                'number'        => $e['numero'],
                'complement'    => $e['complemento'],
                'neighborhood'  => $e['bairro'],
                'city'          => $e['cidade'],
                'state'         => $e['estado']['uf']
            );

            //Se o endereço possui ID... 
            if(isset($e['id']) && !empty($e['id'])) {
                $address = array_merge($address, array(
                    'id'    => $e['id']
                ));
            }

            $result = array_merge($result, array(
                'address' => $address
            ));
        }

        return $result;
    }

    /**
     *  beautifyStrings
     *  Percorre uma estrutura de dados, e ao encontrar strings, aplica uma série de 
     *  funções para padronizar o estilo. Especificamente o padrão de maiúsculas e minúsculas
     */
    private static function beautifyStrings(mixed $data) : mixed {
        $exceptions = array(
            'uf', 'emails', 'emails', 'pessoa-tipo', 'id'
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