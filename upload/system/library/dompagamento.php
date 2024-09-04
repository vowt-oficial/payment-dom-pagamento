<?php
/**
 * @author Cleomar Campos
 * @contact cleomarocampos@gmail.com
*/

interface PostbackUrlInterface {
    /**
     * Sets the postback URL.
     *
     * @param string $postback_url The URL to be set.
     * @return self Returns the instance of the object.
     */
    public function setPostbackUrl(string $postback_url): self;

    /**
     * Gets the postback URL.
     *
     * @return string Returns the postback URL.
     */
    public function getPostbackUrl(): string;
}

/**
 * Interface SecretKeyInterface
 * 
 * This interface defines methods for setting and getting a secret key used for authentication.
 */
interface SecretKeyInterface {
    /**
    * Set the secret key for authentication.
    * 
    * @param string $secret_key The secret key to set.
    * @return self Returns an instance of the implementing class.
    */
    public function setSecretKey(string $secret_key): self;

    /**
     * Get the secret key used for authentication.
     * 
     * @return string|null The secret key, or null if not set.
    */
    public function getSecretKey(): ?string;
}

/**
 * Interface for setting the environment.
 */
interface EnvironmentInterface {
    public function setEnv(string $env): self;
    public function getEnv(): string;
}

/**
 * Interface for setting payment details.
 */
interface PaymentDetailsInterface {
    public function setCodExternal(int $value): self;
    public function getCodExternal(): int;
    public function setDiscount(int $value): self;
    public function getDiscount(): float;
    public function setTotal(float $value): self;
    public function getTotal(): float;
    public function setPaymentMethod(string $value): self;
    public function getPaymentMethod(): string;
    public function setCustomer(array $data): self;
    public function getCustomer(): array;
    public function setPaymentAddress(array $data): self;
    public function getPaymentAddress(): array;
    public function setShippingAddress(array $data): self;
    public function getShippingAddress(): array;
    public function addItem(array $data): self;
    public function getItems(): array;
}

/**
 * Interface for making HTTP requests.
 */
interface HttpRequestInterface {
    public function get(string $url = '', array $params = []): array;
    public function post(string $url = '', array $payload = []): array;
}

/**
 * Class DomPagamento
 * 
 * @package DomPagamento
 * @version 1.0
 * @since 2024-07-01
 * 
 * @license MIT
 * 
 * A class to interact with the DomPagamentos API.
 * 
 * Properties:
 * @property string $secret_key The secret key for authentication.
 * @property string $env The environment ('sandbox' or 'production').
 * @property string $auth_type The authentication type (default: 'Bearer').
 * @property string $url The base URL for API requests.
 * @property string $postback_url The URL for receiving postback notifications.
 * @property string $method The HTTP method for requests (default: 'GET').
 * @property array $headers The headers for API requests.
 * @property array $payload The payload for API requests.
 * @property array $params The parameters for API requests.
 * @property array $items The items to include in the transaction.
 * @property array $customer The customer information.
 * @property array $payment_address The payment address details.
 * @property array $shipping_address The shipping address details.
 * @property string $payment_method The payment method for the transaction.
 * @property float $total The total amount of the transaction.
 * @property float $discount The discount amount (if any) for the transaction.
 * @property int $cod_external The external code related to the transaction.
 * 
 * Example usage:
 * 
 * Initialize DomPagamento with sandbox environment.
 * $domPagamento = new DomPagamento('sandbox');
 * 
 * Set the secret key for authentication.
 * $domPagamento->setSecretKey('your_secret_key')
 * 
 * Set customer information.
 * $domPagamento->setCustomer([
 *     'name' => 'Cleomar Campos',
 *     'email' => 'cleomarcampos@example.com',
 *     'document' => '12345678909', // CPF or CNPJ
 *     'phone' => '11999999999'
 * ])
 * 
 * Set payment address details.
 * $domPagamento->setPaymentAddress([
 *     'street' => 'Rua Exemplo',
 *     'number' => '123',
 *     'neighborhood' => 'Bairro Exemplo',
 *     'city' => 'Cidade Exemplo',
 *     'state' => 'SP',
 *     'zipcode' => '12345678'
 * ])
 * 
 * Set shipping address details.
 * $domPagamento->setShippingAddress([
 *     'street' => 'Rua Exemplo',
 *     'number' => '123',
 *     'neighborhood' => 'Bairro Exemplo',
 *     'city' => 'Cidade Exemplo',
 *     'state' => 'SP',
 *     'zipcode' => '12345678'
 * ])
 * 
 * Add items to the transaction.
 * $domPagamento->addItem([
 *     'name' => 'Produto Exemplo 1',
 *     'quantity' => 1,
 *     'price' => 200.00 // price in reais (float)
 * ])
 * ->addItem([
 *     'name' => 'Produto Exemplo 2',
 *     'quantity' => 2,
 *     'price' => 300.00 // price in reais (float)
 * ])
 * 
 * Set the total amount of the transaction.
 * $domPagamento->setTotal(800.00); // total amount in reais (float)
 * 
 * Create the transaction.
 * $domPagamento->createTransaction([
 *     'installments' => 1,
 *     'description' => 'Compra de teste'
 * ]);
 * 
 * @see https://dom-pagamentos.readme.io/reference/criar-uma-transa%C3%A7%C3%A3o Documentation for creating a transaction.
 */
class DomPagamento implements EnvironmentInterface, PaymentDetailsInterface, HttpRequestInterface, PostbackUrlInterface {

	// https://dom-pagamentos.readme.io/reference/introdu%C3%A7%C3%A3o
    protected string $base_url;

    /**
     * @var string The secret key for authentication.
    */
    protected string $secret_key;

    /**
     * @var string The environment ('sandbox' or 'production').
    */
    protected string $env;

    /**
     * @var string The authentication type (default: 'Bearer').
    */
    protected string $auth_type = 'Bearer';

    /**
     * @var string The base URL for API requests.
    */
    protected string $url = '';

    /**
     * @var string The URL for receiving postback notifications.
    */
    protected string $postback_url = '';

    /**
     * @var string The HTTP method for requests (default: 'GET').
    */
    protected string $method = 'GET';

    /**
     * @var array The headers for API requests.
    */
    protected array $headers = [];

    /**
     * @var array The payload for API requests.
    */
    protected array $payload = [];

    /**
     * @var array The parameters for API requests.
    */
    protected array $params = [];

    /**
     * @var array The items to include in the transaction.
    */
    protected array $items = [];

    /**
     * @var array The customer information.
    */
    protected array $customer = [];

    /**
     * @var array The payment address details.
    */
    protected array $payment_address = [];

    /**
     * @var array The shipping address details.
    */
    protected array $shipping_address = [];

    /**
     * @var string The payment method for the transaction.
    */
    protected string $payment_method = '';

    /**
     * @var float The total amount of the transaction.
    */
    protected float $total = 0;

    /**
     * @var float The discount amount (if any) for the transaction.
    */
    protected float $discount = 0;

    /**
     * @var int The external code related to the transaction.
    */
    protected int $cod_external = 0;

    /**
     * Constructor to set the environment.
     * 
     * @param string $env The environment to set, either 'sandbox' or 'production'.
     */
    public function __construct(string $env = 'sandbox') {
        $this->setEnv($env);

        $this->base_url = $env === 'sandbox' 
            ? 'https://apiv3.dompagamentos.com.br/checkout/sandbox/' 
            : 'https://apiv3.dompagamentos.com.br/checkout/production/';
    }

	/**
	 * Sets the postback URL.
	 *
	 * @param string $postback_url The URL to be set.
	 * @return self Returns the instance of the object.
	 */
	public function setPostbackUrl(string $postback_url): self {
		$this->postback_url = $postback_url;
		return $this;
	}

	/**
	 * Gets the postback URL.
	 *
	 * @return string Returns the postback URL.
	 */
	public function getPostbackUrl(): string {
		return $this->postback_url;
	}

    /**
     * Set the secret key for authentication.
     * 
     * @param string $secret_key The secret key to set.
     * @return self Returns an instance of the implementing class.
     */
    public function setSecretKey(string $secret_key): self {
        $this->secret_key = $secret_key;
        return $this;
    }

	/**
	 * Get the secret key used for authentication.
	 * 
	 * @return string|null The secret key, or null if not set.
	 */
	public function getSecretKey(): ?string {
		return $this->secret_key;
	}

    // EnvironmentInterface methods

    /**
     * Set the environment.
     * 
     * @param string $env The environment to set.
     * @return self
     */
    public function setEnv(string $env): self {
        $this->env = $env;
        return $this;
    }

    /**
     * Get the environment.
     * 
     * @return string
     */
    public function getEnv(): string {
        return $this->env;
    }

    // PaymentDetailsInterface methods

    /**
     * Set the external code.
     * 
     * @param int $value The external code value.
     * @return self
     */
    public function setCodExternal(int $value): self {
        $this->cod_external = $value;
        return $this;
    }
    
    /**
     * Get the external code.
     * 
     * @return int
     */
    public function getCodExternal(): int {
        return $this->cod_external;
    }

    /**
     * Set the discount amount.
     * 
     * @param int $value The discount amount.
     * @return self
     */
    public function setDiscount(int $value): self {
        $this->discount = $value;
        return $this;
    }
    
    /**
     * Get the discount amount.
     * 
     * @return int
     */
    public function getDiscount(): float {
        return (float)$this->discount;
    }

    /**
     * Set the total amount.
     * 
     * @param int $value The total amount.
     * @return self
     */
    public function setTotal(float $value): self {
        $this->total = $value;
        return $this;
    }
    
    /**
     * Get the total amount.
     * 
     * @return int
     */
    public function getTotal(): float {
        return (float)$this->total;
    }

    /**
     * Set the payment method.
     * 
     * @param string $value The payment method.
     * @return self
     */
    public function setPaymentMethod(string $value): self {
        $this->payment_method = $value;
        return $this;
    }
    
    /**
     * Get the payment method.
     * 
     * @return string
     */
    public function getPaymentMethod(): string {
        return $this->payment_method;
    }

    /**
     * Set the customer data.
     * 
     * @param array $data The customer data.
     * @return self
     */
    public function setCustomer(array $data): self {
        $this->customer = $data;
        return $this;
    }
    
    /**
     * Get the customer data.
     * 
     * @return array
     */
    public function getCustomer(): array {
        return $this->customer;
    }

    /**
     * Set the payment address.
     * 
     * @param array $data The payment address.
     * @return self
     */
    public function setPaymentAddress(array $data): self {
        $this->payment_address = $data;
        return $this;
    }

    /**
     * Get the payment address.
     * 
     * @return array
     */
    public function getPaymentAddress(): array {
        return $this->payment_address;
    }

    /**
     * Set the shipping address.
     * 
     * @param array $data The shipping address.
     * @return self
     */
    public function setShippingAddress(array $data): self {
        $this->shipping_address = $data;
        return $this;
    }

    /**
     * Get the shipping address.
     * 
     * @return array
     */
    public function getShippingAddress(): array {
        return $this->shipping_address;
    }

    /**
     * Add an item to the transaction.
     * 
     * @param array $data The item data.
     * @return self
     */
    public function addItem(array $data): self {
        $this->items[] = $data;
        return $this;
    }

    /**
     * Get the items in the transaction.
     * 
     * @return array
     */
    public function getItems(): array {
        return $this->items;
    }

    // HttpRequestInterface methods

    /**
     * Make a GET request.
     * 
     * @param string $url The URL to send the request to.
     * @param array $params The query parameters to include in the request.
     * @return array The response from the API.
     */
    public function get(string $url = '', array $params = []): array {
        $this->method = 'GET';
        if ($url) $this->url = $url;
        if ($params) $this->params = $params;
        return $this->request();
    }

    /**
     * Make a POST request.
     * 
     * @param string $url The URL to send the request to.
     * @param array $payload The payload to include in the request.
     * @return array The response from the API.
     */
    public function post(string $url = '', array $payload = []): array {
        $this->method = 'POST';
        if ($url) $this->url = $url;
        if ($payload) $this->payload = $payload;
        return $this->request();
    }

    /**
     * Creates a transaction.
     *
     * @param array $data The transaction data.
     * @return array The response from the API.
	 * @throws \InvalidArgumentException If data validation fails.
	 * @throws \Exception If there is a cURL error or unexpected HTTP status code.
	 * @see https://dom-pagamentos.readme.io/reference/criar-uma-transa%C3%A7%C3%A3o
    */
    public function createTransaction(array $data): array {
		$this->validateTransactionData($data);
		
        $payload = [
            'customer' => $this->getCustomer(),
            'cod_external' => $this->getCodExternal(),
            'items' => $this->getItems(),
            'payment' => [
                'total' => $this->getTotal(),
                'discount' => $this->getDiscount(),
                'payment_method' => $this->getPaymentMethod(),
			],
        ];

		$payload['postbackUrl'] = $this->getPostbackUrl();

		$payload['payment'] = array_merge($payload['payment'], $data);

        return $this->post('transactions', $payload);
    }
		
	/**
	 * Validate essential transaction data.
	 *
	 * @param array $data The transaction data to be validated.
	 * @throws \InvalidArgumentException If the data is invalid.
	 */
	protected function validateTransactionData(array $data): void {
		if (empty($this->getCustomer())) {
			throw new \InvalidArgumentException('Customer data is required.');
		}

		if (empty($this->getCodExternal())) {
			throw new \InvalidArgumentException('External code is required.');
		}

		if (empty($this->getItems())) {
			throw new \InvalidArgumentException('Items data is required.');
		}

		if ($this->getTotal() <= 0) {
			throw new \InvalidArgumentException('Total amount must be greater than zero.');
		}

		if (empty($this->getPaymentMethod())) {
			throw new \InvalidArgumentException('Payment method is required.');
		}

		if (empty($this->getPostbackUrl())) {
			throw new \InvalidArgumentException('Postback URL is required.');
		}

		// Validate additional payment data from the $data parameter if needed
		// Example: if (empty($data['someKey'])) { throw new \InvalidArgumentException('Some key is required.'); }
	}

    /**
     * Executes the HTTP request.
     * 
     * @return array The response from the API.
     * @throws \Exception If there is a cURL error.
     */
    protected function request(): array {
        $ch = curl_init();

        $params = !empty($this->params) ? (strpos($this->url, '?') === false ? '?' : '&') . http_build_query($this->params) : '';

        curl_setopt($ch, CURLOPT_URL, $this->base_url . $this->url . $params);

        if (strtoupper($this->method) === 'POST') {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($this->payload));
        }

        $headers = [
            'Content-Type: application/json',
            'Accept: application/json'
        ];

        if (!empty($this->secret_key)) {
            $headers[] = 'Authorization: ' . $this->auth_type . ' ' . $this->secret_key;
        }

        $headers = array_merge($headers, $this->headers);

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($this->method));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		
		if (curl_errno($ch)) {
			// Log the error
			error_log('cURL error: ' . curl_error($ch));
			throw new \Exception(curl_error($ch));
		}

		if ($http_code < 200 || $http_code >= 300) {
			// Log the HTTP error
			error_log('HTTP error: Unexpected HTTP status code ' . $http_code);
			throw new \Exception('Unexpected HTTP status code ' . $http_code);
		}

        $result = json_decode($response, true);

		if (json_last_error() !== JSON_ERROR_NONE) {
			// Log JSON decode error
			error_log('JSON decode error: ' . json_last_error_msg());
			throw new \Exception('Failed to decode JSON response');
		}

        curl_close($ch);

        return (array)$result;
    }
}