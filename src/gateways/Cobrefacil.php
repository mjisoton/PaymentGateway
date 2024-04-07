<?php
namespace AgenciaNet\gateways;
use AgenciaNet\interfaces\GatewayInterface;
use AgenciaNet\interfaces\GatewayAnswerInterface;
use AgenciaNet\gateways\CobreFacilAnswer;

/**
 * 	Cobrefacil
 * 	Classe do tipo GatewayInterface que implementa todos os métodos necessários para 
 *  estabelecer uma conexão com a API do gateway de pagamentos Cobrefácil, e executar 
 *  operações de gerenciamento de clientes, cobranças, notas fiscais, e outros aspectos.
 *  
 * 	https://developers.cobrefacil.com.br/
 */
class Cobrefacil implements GatewayInterface {

    //Credenciais 
    private ?string $app_id 		= null;
    private ?string $secret 		= null;
    private ?string $environment 	= 'sandbox';
    
    //Token para requisições 
    private ?string $token 			= null;
    private ?string $expiration 	= null;

    //Storage
    private ?string $storage		= null;

    //Endpoint para requisições 
    const ENDPOINT_PRODUCTION 		= 'https://api.cobrefacil.com.br/v1/';
    const ENDPOINT_SANDBOX 			= 'https://api.sandbox.cobrefacil.com.br/v1/';
    
    //Arquivo de banco de dados temporário
    const TMP_FILE = 'cobre_facil_db.json';

    /**
     *  Constructor
     *  Verifica se o diretório de credenciais foi criado, e obtém credenciais 
     *  se as mesmas existem
     */
    function __construct() {
        $tmp_dir = dirname(__DIR__) . self::DS . 'tmp';

        //Verifica se o diretório para arquivos temporários existe
        if(is_dir($tmp_dir) === false) {
            throw new \Exception('O diretório para arquivos temporários não existe, e é obrigatório.');
        }

        $this->storage = $tmp_dir . self::DS . self::TMP_FILE;

        //Carrega o token de autenticação, se houver 
        if($auth = $this->loadAuthTokenObject()):
            $this->token 		= $auth['token'];
            $this->expiration 	= $auth['expiration'];
        endif;
    }

    /**
     *  setCredentials
     *  Recebe da aplicação as credenciais para acesso á API do Cobrefácil. Com 
     *  estas credenciais, pode-se chamar /authenticate' para obtém um token com 
     * 	duração baixa, o qual deve ser enviado como Bearer em cada requisição.
     *  O array de credenciais deve ter dois índices: 'app_id', e 'secret'.
     */
    public function setCredentials(array $credentials) {

        //Verifica se as credenciais são válidas
        if(isset($credentials['app_id']) === false || isset($credentials['secret']) === false) {
            throw new \Exception('As credenciais do gateway \'Cobrefácil\' não são válidas. Verifique a estrutura enviada ao construtor da classe.');
        }

        $this->app_id	= $credentials['app_id'];
        $this->secret	= $credentials['secret'];
    }

    /**
     * 	setEnvironment
     * 	Indica á biblioteca qual o ambiente a performar operações. Pode ser 
     *  'sandbox' ou 'production'
     */
    public function setEnvironment(string $environment) {
        $this->environment = $environment;
    }

    /**
     * 	createClient
     * 	Executa a operação de cadastro de clientes
     */
    public function createClient(array $data) : GatewayAnswerInterface {
        $cliente = $this->execRequest('POST', 'customers', $data);
        
        return $cliente;
    }

    /**
     *  updateClient
     *  Executa a operação de edição de clientes previamente cadastrados
     */
    public function updateClient(string $code, array $data) : GatewayAnswerInterface {
        $cliente = $this->request('PUT', 'customers/' . $id, $cliente->getData());

        return $cliente;
    }

    /**
     *  deleteClient
     *  Executa a operação de exclusão de clientes
     */
    public function deleteClient(string $code) : GatewayAnswerInterface {

    }

    /**
     *  createCharge
     * 	Executa a criação de cobranças
     */
    public function createCharge(string $type) : GatewayAnswerInterface {

    }

    /**
     *  createInvoice
     * 	Executa a criação de notas fiscais de serviços
     */
    public function createInvoice() : GatewayAnswerInterface {

    }

    /**
     *  deleteInvoice
     * 	Executa o cancelamento de notas fiscais de serviços
     */
    public function deleteInvoice(string $code) : GatewayAnswerInterface {

    }

    /*
     *	establishAuthentication()
     *	Obtém um token para requisições. Se o token já existente está 
     *	dentro do prazo de validade, reaproveita-o. Caso contrário, faz-se 
     *	necessário buscar um novo.
     */
    private function establishAuthentication() : bool {

        //Verifica se o token atual é válido (com uma folga de uns 10 segundos)
        if(isset($this->token) && ($this->expiration - 10) > time()):
            return true;
        endif;

        //Gera um token 
        $auth = $this->execRequest('POST', 'authenticate', array(
            'app_id'	=> $this->app_id,
            'secret'	=> $this->secret
        ));

        //Armazena no objeto para futuro uso...
        if($auth->isSuccess() === true):
            
            //Armazena no objeto...
            $this->token 			= $auth->getData('token');
            $this->expiration 		= time() + $auth->getData('expiration');
            
            //Armazena no banco de dados 
            $this->saveAuth($this->token, $this->expiration);

            return true;
        endif;
        
        //Em caso de problemas, retorna o objeto inteiro de retorno
        return false;
    }

    /*
     *	execRequest()
     *	Executa as requisições HTTP para comunicação com o sistema do provedor
     *	de serviço 'Cobre Fácil'. Estas requisições exigem um token de autenticação, 
     *	e podem executar quaisquer operações. 
     */
    private function execRequest(string $method, string $endpoint, array $payload = array()) : GatewayAnswerInterface {

        /*
         *	Antes de qualquer coisa, verifica se o objeto possui um token de 
         *	autenticação válido. Se sim, segue o baile. Caso contrário, é necessário 
         *	gerar um novo token. 
         *	Ainda, se a chamada a 'execRequest()' possui o endpoint 'authenticate', deixa 
         *	passar, pois trata-se da chamada para gerar o token.
         */
        if($this->isAuthRequest($endpoint) === false && $this->establishAuthentication() === false) {
            return new CobreFacilAnswer(array(
                'success'		=> false, 
                'message'		=> 'Falha de autenticação ao executar requisição', 
                'shouldRetry'	=> false,  
                'errors'		=> array(
                    'Credenciais inválidas ou inexistentes.'
                )
            ));
        }

        //Array de configurações
        $curl_cfg = array(
            CURLOPT_POST 				=> 0,
            CURLOPT_HEADER 				=> 0,
            CURLOPT_URL 				=> ($this->environment == 'production' ? self::ENDPOINT_PRODUCTION : self::ENDPOINT_SANDBOX) . $endpoint,
            CURLOPT_FRESH_CONNECT		=> 1,
            CURLOPT_RETURNTRANSFER 		=> 1,
            CURLOPT_TIMEOUT 			=> 45,
            CURLOPT_SSL_VERIFYHOST 		=> 0,
            CURLOPT_SSL_VERIFYPEER 		=> 0,

            CURLOPT_HTTPHEADER 			=> array(

                //User-Agent da API
                'User-Agent: Agencianet.Sistema', 
                
                //Versão 
                'Version: v1', 
                
                //Tipo de corpo de requisição 
                'Content-Type: application/json', 
                
                //Formato aceito
                'Accept: application/json'
            )
        );
        
        //Caso o objeto já possua um token de autenticação válido 
        if($this->token):
            $curl_cfg[CURLOPT_HTTPHEADER] = array_merge($curl_cfg[CURLOPT_HTTPHEADER], array(
                'Authorization: Bearer ' . $this->token
            ));
        endif;

        //Configura verbos diferenciados e corpos de requisição correspondentes
        switch($method):
        
            //Post
            case 'POST':
                $curl_cfg[CURLOPT_POST] 	= true;
                $curl_cfg[CURLOPT_POSTFIELDS] = json_encode($payload);
            break;
            
            //Put
            case 'PUT':
                $curl_cfg[CURLOPT_CUSTOMREQUEST] 	= 'PUT';
                $curl_cfg[CURLOPT_POSTFIELDS] = json_encode($payload);
            break;
            
            //Delete
            case 'DELETE':
                $curl_cfg[CURLOPT_CUSTOMREQUEST] 	= 'DELETE';
                
                /*
                 *	A inclusão de payload para requisições DELETE não é considerada 
                 *	proibida de acordo com a RFC. Ainda, o Cobre Fácil fez uma implementação 
                 *	para nós que permite cancelar NFS-e ao cancelar cobranças, e isso exige 
                 *	o envio de payload em requisições DELETE.
                 */
                if($payload):
                    $curl_cfg[CURLOPT_POSTFIELDS] = json_encode($payload);
                endif;
            break;
        endswitch;
        
        //Cria o objeto...
        $curl_req = curl_init(); 
        curl_setopt_array($curl_req, $curl_cfg); 
        
        //Executa a requisição...
        $return_req = curl_exec($curl_req);

        //Array que receberá o retorno, por padrão fica com uma mensagem de erro
        $result = array(
            'success'		=> false, 
            'message'		=> 'Falha técnica ao executar requisição', 
            'shouldRetry'	=> true,  
            'errors'		=> array(
                'Erro Desconhecido'
            )
        );

        //Em caso de erros...
        if($err = curl_error($curl_req)):

            $result = array_merge($retorno, array(
                'errors'		=> array(
                    $err
                )
            ));
        else:

            //Obtém o código HTTP
            $httpcode = curl_getinfo($curl_req, CURLINFO_HTTP_CODE);

            //Decodifica o retorno
            $return_req = json_decode($return_req, true);

            //Finaliza o objeto...
            curl_close($curl_req); 
        
            //Caso tenha ocorrido um erro na decodificação, dá um erro...
            if ($return_req === null && json_last_error() !== JSON_ERROR_NONE):
            
                $result = array_merge($result, array(
                    'shouldRetry'	=> false, 
                    'errors'		=> array(
                        'Falha ao decodificar o corpo de retorno da requisição.'
                    )
                ));
                
            else:
                
                //Pega o retorno...
                $result = $return_req;

            endif;
        endif;

        //Retorna sempre um objeto 'GatewayAnswerInterface'
        return new CobreFacilAnswer($result);
    }

    
    /*
     *	loadAuthTokenObject
     *	Carrega o token para o objeto, considerando que o mesmo se encontra 
     *	no arquivo de banco de dados temporário
     */
    private function loadAuthTokenObject() : array {
        
        //Se o arquivo temporário não existe, cria-o 
        if(file_exists($this->storage) === false):
            touch($this->storage);
        endif;
        
        //Carrega as variáveis do array JSON 
        if(($token = json_decode(file_get_contents($this->storage), true)) !== false):
            
            //Se houve algum problema na decodificação 
            if(json_last_error() !== JSON_ERROR_NONE):
                return array();
            endif;
            
            return $token;
        endif;
        
        //Ao fim, em caso de não ter conteúdo algum no arquivo, retorna falso
        return array();
    }
    
    /*
     *	saveAuthTokenObject
     *	Salva o token para o arquivo de cache, possibilitando uso futuro 
     *	enquanto o mesmo estiver válido
     */
    private function saveAuthTokenObject(string $token, string $expiration) : bool {
        
        //Objeto temporário de tokens 
        $auth = json_encode(array(
            'token'			=> $token,
            'expiration'	=> $expiration
        ));
        
        //Armazena 
        return file_put_contents($this->storage, $auth) ? true : false;
    }
    
    /*
     *	 isAuthRequest
     *	 Verifica se a requisição HTTP é de autenticação
     */
    private function isAuthRequest(string $endpoint) : bool {
        return strstr($endpoint, 'authenticate') !== false;
    }
}

?>