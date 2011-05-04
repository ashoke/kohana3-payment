<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Base linkpoint requester class
 * 
 * @link https://www.firstdata.com/downloads/marketing-merchant/fd_globalgatewayapi_usermanual.pdf
 *  
 * @author Alexey Geno <alexeygeno@gmail.com>
 * @package Payment
 */
abstract class Payment_Requester_Linkpoint extends Payment_Linkpoint {

	/**
	 * Makes a POST request to Linkpoint API for the given order type and parameters
	 * @param string Order Type(see API docs)
	 * @param array another params from inside of 'order' section
	 * @param string interface base name that will be log
	 * @param string action name that will be log
	 * @throws Kohana_Exception
	 * @return array API response
	 */
	protected function _post($order_type, array $params,$interface,$action)
	{
		if ($this->_environment === 'live')
		{
			// Live environment
			$port = '1129';
			$host = 'secure.linkpt.net';
		}
		else
		{
			// Use the environment sub-domain
			$port = '1129';
			$host = 'staging.linkpt.net';
		}		

		
		//unset data what we load from config
		unset($params['orderoptions']);
		unset($params['merchantinfo']);
		// Create POST data
		$post = array('order'=>
				array(
					'orderoptions'=>array('ordertype'=>$order_type,
					),
					'merchantinfo'=>array('keyfile'=>$this->_keyfile,
							  'host'	  =>$host,
							  'port'	  =>$port,
							  'configfile'=>$this->_storenumber
					)
				)+ $params
			);

		//build xml from array
		$xml = $this->_simple_xml_encode($post);

		// Create a new curl instance
		$curl = curl_init();
		

		// Set curl options
		curl_setopt ($curl, CURLOPT_URL,"https://".$host.":".$port."/LSGSXML");
		curl_setopt ($curl, CURLOPT_POST, 1);
		curl_setopt ($curl, CURLOPT_POSTFIELDS, $xml);
		curl_setopt ($curl, CURLOPT_SSLCERT, $this->_keyfile);
		curl_setopt ($curl, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt ($curl, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt ($curl, CURLOPT_RETURNTRANSFER, 1);

		if (($response = curl_exec($curl)) === FALSE)
		{
			// Get the error code and message
			$code  = curl_errno($curl);
			$error = curl_error($curl);

			// Close curl
			curl_close($curl);

			throw new Kohana_Exception('Linkpoint API request for :order_type failed: :error code: :code',
				array(':order_type' => $order_type, ':error' => $error, ':code' => $code));
		}

		/*
		echo('<br>sended array:<br>');
		var_dump($post);

		echo('<br>sended xml:<br>');
		var_dump($xml);*/

		// Close curl
		curl_close($curl);

		$array_response = $this->_simple_xml_decode($response);

		/*
		echo('<br>responsed array:<br>');
		var_dump($array_response);
		*/
		
		$this->_after_request($post, $array_response,$interface,$action);

		return $array_response;
	}

	/**
	 * Calls directly after request done. Does Logging and checks success of API operation
	 * @param array API request
	 * @param array API response
	 * @param string  interface base name that should be log
	 * @param string  action name that should be log
	 * @throws Payment_Exception throws when API operation has failed
	 */
	protected function _after_request($request, $response,$interface,$action)
	{
		$success = (isset($response['r_approved']) AND $response['r_approved']==='APPROVED');
		$txn_id = ((isset($response['r_ordernum']) AND $response['r_ordernum'])?$response['r_ordernum']:FALSE);
		$txn_date = ((isset($response['r_time']) AND $response['r_time'])?strtotime($response['r_time']):FALSE);
		$custom = ((isset($request['order']['billing'])
				AND isset($request['order']['billing']['userid']))
			?
				$request['order']['billing']['userid']
			:
				FALSE
			   );

		$payment_log = new Model_Payment_Log('requester','linkpoint');
		$payment_log->create($interface,$action,$success, $response, $request, $txn_id, $txn_date, $custom);

		if (!$success)
		{
			throw new Payment_Exception('Linpoint API request for :order_type failed: :response data (:data)',
				array(':order_type' => $request['order']['orderoptions']['ordertype'], ':data'=>print_r($response,TRUE)));
		}
	}

	/**
	 * Decodes simple xml string to array
	 * @param string XML string
	 * @return array decoded array
	 */
	private function _simple_xml_decode($xml)
	{
		preg_match_all ('/<(.*?)>(.*?)\</', $xml, $out, PREG_SET_ORDER);
		$n = 0;
		$array = array();
		while (isset($out[$n]))
		{
			$array[$out[$n][1]] = strip_tags($out[$n][0]);
			$n++;
		}
		return $array;
	}

	/**
	 * Encode array to simple xml string
	 * @param array Array
	 * @return string encoded xml string
	 */
	private function _simple_xml_encode(array $array)
	{
		$xml = '';
		foreach($array as $element => $value) {
			if (is_array($value)) {
				$xml .= "<$element>".$this->_simple_xml_encode($value)."</$element>";
				}
			else {
			   $xml .= "<$element>$value</$element>";
			   }
		}
		return $xml;
	}
}