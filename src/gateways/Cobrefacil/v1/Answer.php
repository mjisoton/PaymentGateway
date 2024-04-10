<?php
namespace PaymentGateway\gateways\Cobrefacil\v1;
use PaymentGateway\interfaces\GatewayAnswerInterface;

/**
 * 	Answer
 * 	Classe do tipo GatewayAnswerInterface que permite executar operações 
 * 	de forma padronizada em toda e qualquer resposta recebida pela aplicação 
 * 	oriunda do gateway de pagamentos Cobrefácil
 *  
 * 	https://developers.cobrefacil.com.br/
 */
class Answer implements GatewayAnswerInterface {
    private bool $success       = false;
    private bool $retry         = false;
    private ?string $message    = null;
    private array $errors       = array();
    private array $body         = array();
    private $id                 = null;

    /**
     * 	__construct
     * 	Recebe a resposta da API já decodificada em um array
     */
    function __construct(?array $response) {
        $this->body = $response;

        //Identifica o status
        if(isset($response['success']) === true) {
            $this->success = (bool) $response['success'];
        }

        //Identifica o status
        if(isset($response['data']['id']) === true) {
            $this->id = $response['data']['id'];
        }

        //Identifica mensagens de retorno
        if(isset($response['message']) === true) {
            $this->message = $response['message'];
        }

        //Caso haja errors, organiza-os em um array
        if(isset($response['errors']) === true && empty($response['errors']) === false) {
            $this->errors = $response['errors'];
        }

        //Caso haja errors, organiza-os em um array
        if(isset($response['shouldRetry']) === true && $response['shouldRetry'] == true) {
            $this->retry = true;
        }
    }

    //Verifica se a operação executada foi um sucesso ou falha
    public function isSuccess() : bool {
        return $this->success;
    }

    //Obtém a mensagem de resposta da API
    public function getMessage() : ?string {
        return $this->message;
    }

    //Caso a resposta se refira á uma entidade, obtém o identificador único
    public function getId() : string|int {
        return $this->id;
    }

    //Para fins de debugging, obtém o corpo inteiro
    public function getFullBody() : array {
        return $this->body;
    }

    //Retorna índices específicos do retorno
    public function getData(string $index, ?string $parentNode = 'data') : ?string {
        $aux = $this->body;
        $ind = explode('.', ($parentNode ? $parentNode . '.' : '') . $index);

        foreach($ind as $k => $i) {
            if(isset($aux[$i])) {
                $aux = $aux[$i];
            } else {
                return null;
            }
        }

        return $aux;
    }

    //Retorna erros da requisição, se houver
    public function getErrors() : array {
        return $this->errors;
    }

    //Especifica para a aplicação se uma nova tentativa é algo necessário
    public function shouldRetry() : bool {
        return $this->retry;
    }
}

?>