<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Paypal gateway integration
 *
 * @link  https://cms.paypal.com/us/cgi-bin/?cmd=_render-content&content_ID=developer/library_documentation
 * 
 * @author Alexey Geno <alexeygeno@gmail.com>
 * @package Payment
 */
abstract class Payment_PayPal implements Payment_Interface {

	// API username
	protected $_username;

	// API password
	protected $_password;

	// API signature
	protected $_signature;

	// Merchant email and id
	protected $_merchant_email;
	protected $_merchant_id;

	// Environment type
	protected $_environment = 'live';

	//base paypal host
	protected $_base_host;

	/**
	 * Creates a new PayPal instance for the given username, password,
	 * and signature for the given environment.
	 *
	 * @param   string  API username
	 * @param   string  API password
	 * @param   string  API signature
	 * @param   string  Merchant email
	 * @param   string  Merchant Id
	 * @param   string  environment (one of: live, sandbox, sandbox-beta)
	 */
	public function __construct($username, $password, $signature,$merchant_email,$merchant_id, $environment = 'live')
	{
		// Set the API username and password
		$this->_username = $username;
		$this->_password = $password;

		// Set the API signature
		$this->_signature = $signature;

		//Set the merchant email and id
		$this->_merchant_email = $merchant_email;
		$this->_merchant_id = $merchant_id;

		// Set the environment
		$this->_environment = $environment;

		if($this->_environment==='live')
		{
		$this->_base_host = 'paypal.com';
		}
		else
		{
		$this->_base_host = $this->_environment.'.paypal.com';
		}
	}

	/**
	 * Returns new instance of Paypal class impemented for given type and interface
	 *
	 * @param type 'requester' or 'notifier'
	 * @param interface 'instant' and 'recurring' supported for now
	 * @return  object
	 */
	public static function factory($type,$interface)
	{
		$class = 'Payment_'.$type.($interface?'_'.$interface:'').'_Paypal';

		// Load default configuration
		$config = Kohana::config('payment')->paypal;

		// Create a new PayPal instance with the default configuration
		return new $class($config['username'], $config['password'], $config['signature'], $config['merchant_email'], $config['merchant_id'], $config['environment']);
	}
}