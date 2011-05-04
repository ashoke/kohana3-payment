<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Base paypal requester class
 *
 * @link https://cms.paypal.com/us/cgi-bin/?cmd=_render-content&content_ID=developer/e_howto_api_NVPAPIBasics
 * @link https://cms.paypal.com/us/cgi-bin/?cmd=_render-content&content_ID=developer/e_howto_api_nvp_NVPAPIOverview
 * @link https://cms.paypal.com/us/cgi-bin/?cmd=_render-content&content_ID=developer/howto_api_reference
 
 * @author Alexey Geno <alexeygeno@gmail.com>
 * @package Payment
 */
abstract class Payment_Requester_PayPal extends Payment_PayPal {

	/**
	 * Returns the Requester API URL for the current environment
	 * @return string
	 */
	protected function _api_url()
	{
		if ($this->_environment === 'live')
		{
			// Live environment does not use a sub-domain
			$env = '';
		}
		else
		{
			// Use the environment sub-domain
			$env = $this->_environment.'.';
		}

		return 'https://api-3t.'.$env.'paypal.com/nvp';
	}

	/**
	 * Makes a POST request to PayPal NVP for the given method and parameters
	 * @link  https://cms.paypal.com/us/cgi-bin/?cmd=_render-content&content_ID=developer/e_howto_api_Requester_RequesterAPIOverview
	 * @param string API method to call
	 * @param array API POST parameters
	 * @param string interface base name that will be log
	 * @param string action name that will be log
	 * @throws Kohana_Exception
	 * @return array API response
	 */
	protected function _post($method, array $params, $interface, $action)
	{
		// Create POST data
		$post = array(
			'METHOD'	=> $method,
			'VERSION'   => 51.0,
			'USER'		=> $this->_username,
			'PWD'		=> $this->_password,
			'SIGNATURE' => $this->_signature,
		) + $params;

		// Create a new curl instance
		$curl = curl_init();
		//var_dump(http_build_query($post, NULL, '&'));
		// Set curl options
		curl_setopt_array($curl, array(
			CURLOPT_URL			   => $this->_api_url(),
			CURLOPT_POST		   => TRUE,
			CURLOPT_POSTFIELDS	   => http_build_query($post, NULL, '&'),
			CURLOPT_SSL_VERIFYPEER => FALSE,
			CURLOPT_SSL_VERIFYHOST => FALSE,
			CURLOPT_RETURNTRANSFER => TRUE,
		));

		/*
		echo '<br\>Request data:<br\>';
		var_dump($post);
		*/
		if (($response_str = curl_exec($curl)) === FALSE)
		{
			// Get the error code and message
			$code  = curl_errno($curl);
			$error = curl_error($curl);

			// Close curl
			curl_close($curl);

			throw new Kohana_Exception('PayPal API request for :method failed: :error (:code)',
				array(':method' => $method, ':error' => $error, ':code' => $code));
		}

		// Close curl
		curl_close($curl);

		// Parse the response
		parse_str($response_str, $response);

		/*
		echo '<br\>Response data:<br\>';
		var_dump($response);
		*/
		$this->_after_request($post,$response,$interface,$action);

		return $response;
	}

	/**
	 * Calls directly after request done. Does Logging and checks success of API operation
	 * @param array API request
	 * @param array API response
	 * @param string  interface base name that should be log
	 * @param string  action name that should be log
	 * @throws Payment_Exception throws when API operation has failed
	 */
	protected function _after_request(array $request,array $response,$interface,$action)
	{
		$success =  (isset($response['ACK']) AND strpos($response['ACK'], 'Success')!==FALSE);
		$txn_id = ((isset($response['TRANSACTIONID']) AND $response['TRANSACTIONID'])?$response['TRANSACTIONID']:FALSE);
		$custom = (isset($request['CUSTOM'])?$request['CUSTOM']:FALSE);
		$date = (isset($response['TIMESTAMP'])?strtotime($response['TIMESTAMP']):FALSE);
		$payment_log = new Model_Payment_Log('requester','paypal');
		$payment_log->create($interface,$action,$success, $response, $request, $txn_id, $date, $custom);

		if (!$success)
		{
			throw new Kohana_Exception('PayPal API request for :method failed: :error (:code)',
				array(':method' => $request['METHOD'], ':error' => $response['L_LONGMESSAGE0'], ':code' => $response['L_ERRORCODE0']));
		}
	}
}