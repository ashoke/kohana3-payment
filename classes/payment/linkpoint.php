<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Linkpoint gateway integration 
 *
 * @link https://www.firstdata.com/downloads/marketing-merchant/fd_globalgatewayapi_usermanual.pdf 
 * @author	 Alexey Geno
 * @package	Payment
 */
abstract class Payment_Linkpoint implements Payment_Interface {

	/**
	 * @var string storenumber
	 */
	protected $_storenumber;
	
	/**
	 * @var Cert file path
	 */	
	protected $_keyfile;

	/**
	 * @var  Environment type
	 */
	protected $_environment = 'live';

	/**
	 * Creates a new Linkpoint instance for the given storenumber, keyfile, emviroment	 
	 *
	 * @param   string  storenumber
	 * @param   string  path to cert file
	 * @param   string  environment (one of: live, sandbox)
	 */
	public function __construct($storenumber, $keyfile,$environment = 'live')
	{
		// Set the API username and password
		$this->_storenumber = $storenumber;
		$this->_keyfile = $keyfile;
		// Set the environment
		$this->_environment = $environment;
	}

		/**
	 * Returns new instance of Linkpoint class impemented for given type and interface
	 *
	 * @param type 'requester' or 'notifier'
	 * @param interface 'instant' and 'recurring' supported for now
	 * @return  object
	 */
	public static function factory($type,$interface)
	{
		$config = Kohana::config('payment')->linkpoint;
		$class = 'Payment_'.$type.($interface?'_'.$interface:'').'_Linkpoint';

		// Create a new Linkpoint instance with the default configuration
		return new $class($config['storenumber'], $config['keyfile'], $config['environment']);
	}

}