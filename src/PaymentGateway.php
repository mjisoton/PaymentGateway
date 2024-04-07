<?php
namespace AgenciaNet;

use \AgenciaNet\interfaces\GatewayInterface;
use \AgenciaNet\interfaces\GatewayAnswerInterface;

/**
 * 	PaymentGateway
 * 	Classe Factory, responsável por intermediar todas as operações a serem 
 *  executadas em gateways de pagamento. 
 */
class PaymentGateway {
    private GatewayInterface $gh;

    /**
     *  __construct 
     *  Recebe o nome do gateway a ser fabricado para receber operações. 
     */
    function __construct(string $gateway, ?array $credentials, string $environment = 'sandbox') {
        $gateway = 'AgenciaNet\\gateways\\' . $gateway;

        if(!class_exists($gateway)) {
            throw new \Exception('O gateway de pagamento '. $gateway .' solicitado não existe.');
        }

        //Fabrica uma instância do gateway de pagamentos escolhido
        $this->gh = new $gateway;

        //Caso haja credenciais, então aplica as mesmas 
        if($credentials) {
            $this->gh->setCredentials($credentials);
        }

        //Caso haja um indicador de ambiente
        if($environment) {
            $this->gh->setEnvironment($environment);
        }
    }

    /**
     * 	__call
     * 	Quaisquer chamadas á métodos neste objeto que não existirem explicitamente 
     * 	são automaticamente encaminhadas ao objeto de gateway criado sinteticamente
     */
    public function __call(string $method, array $args) : GatewayAnswerInterface {

        if(!method_exists($this->gh, $method)) {
            throw new \Exception('O método '. $method .' não existe.');
        }

        return call_user_func_array([$this->gh, $method], $args);
    }
    
}

?>