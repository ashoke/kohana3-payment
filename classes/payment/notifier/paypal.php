<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Notifier implementation for paypal, strongly recomended to read doc by link to understand how it works
 *
 * @link https://cms.paypal.com/cms_content/US/en_US/files/developer/IPNGuide.pdf
 *
 * @author Alexey Geno <alexeygeno@gmail.com>
 * @package Payment
 */
abstract class Payment_Notifier_PayPal extends Payment_PayPal {

	/**
	 * Data recieved from $_POST
	 * @var array
	 */
	protected $_post_data;
	
	/**
	 * If remote host has been checked
	 * @var bool
	 */
	private static $_remote_host_checked;

	/**
	 * Takes data from post and checks if remote server is valid
	 * @param string merchant username
	 * @param string merchant password
	 * @param string merchant signature
	 * @param string merchant email
	 * @param string merchant id
	 * @param string environment (one of: live, sandbox, sandbox-beta)
	 */
	public function __construct($username, $password, $signature,$merchant_email,$merchant_id, $environment = 'live')
	{
		$this->_post_data = $_POST;
		parent::__construct($username, $password, $signature, $merchant_email, $merchant_id,$environment);
		//Security feature
		if(!isset(Payment_Notifier_PayPal::$_remote_host_checked))
		{
		$ipn_prefix = ($this->_environment==='live')?'notify':'ipn';
		$paypal_ipn_host = "$ipn_prefix.{$this->_base_host}";
		$remote_host = gethostbyaddr($_SERVER['REMOTE_ADDR']);
		//if remote server is not ipn remote then show 404 and log data about that server
		if($paypal_ipn_host!==$remote_host)
		{
			Kohana::$log->add(Kohana_Log::INFO, 'SECURETY: NOT PAYPAL Notifier HOST(:remote_host) TRIED TO REQUEST Notifier CONTROLLER. POST: :post',
				array(':remote_host'=>$remote_host,':post'=>print_r($this->_post_data,TRUE)));
			throw new Kohana_Request_Exception('Page Not found');
		}

		if($this->_post_data['receiver_email'] !== $this->_merchant_email)
		{
			Kohana::$log->add(Kohana_Log::INFO, "Receiver_email(:receiver_email) is not equal to merchant email(:merchant_email). POST: :post",
				array(':receiver_email'=>$this->_post_data['receiver_email'],
					':merchant_email'=>$this->_merchant_email,
					':post'=>print_r($this->_post_data,TRUE)
				)
				);
			throw new Kohana_Request_Exception('Page Not found');
		}
		Payment_Notifier_PayPal::$_remote_host_checked = TRUE;
		}
		if($this->handled())
		{
		$this->confirm();
		$this->after_confirm();
		}
	}

	/**
	 * If Notifier handled, should be reloaded in child class
	 * @return bool
	 */
	protected function handled()
	{
		return FALSE;
	}
	
	/**
	 * Sends back the same post to paypal for confirm payment
	 * @throws Payment_Exception
	 */
	private function confirm()
	{
		$req = 'cmd=_notify-validate';
		foreach ($this->_post_data as $key => $value) {
			$value = urlencode(stripslashes($value));
			$req .= "&$key=$value";
		}

		// post back to PayPal system to validate
		$url= "https://www.{$this->_base_host}/cgi-bin/webscr";

		$curl_result = $curl_err = '';
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/x-www-form-urlencoded",
							   "Content-Length: " . strlen($req)));
		curl_setopt($ch, CURLOPT_HEADER , 0);
		curl_setopt($ch, CURLOPT_VERBOSE, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);

		if (($curl_result = curl_exec($ch)) === FALSE)
		{
			// Get the error code and message
			$code  = curl_errno($ch);
			$error = curl_error($ch);

			// Close curl
			curl_close($ch);

			throw new Kohana_Exception('PayPal IPN request for failed: :error (:code)',
				array(':error' => $error, ':code' => $code));
		}
		
		if (strpos($curl_result, "VERIFIED")===FALSE)
		{
			throw new Payment_Exception('Paypal ipn response is not "VERIFIED" but is: ":curl_res". POST: :post',
				array(':post'=>print_r($this->_post_data,TRUE),':curl_res'=>$curl_result));
			
		}
	}
	
	/**
	 * Calls directly after success confirm
	 */
	protected function after_confirm()
	{
		//can be reloaded at child class
	}
}