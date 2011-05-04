<?php defined('SYSPATH') or die('No direct script access.');
/** 
 * Managing paypal instant payments
 * 
 * @link https://cms.paypal.com/us/cgi-bin/?cmd=_render-content&content_ID=developer/e_howto_api_nvp_r_DoDirectPayment
 * 
 * @author Alexey Geno <alexeygeno@gmail.com>
 * @package payment
 */
class Payment_Requester_Instant_Paypal extends Payment_Requester_PayPal implements Payment_Requester_Instant_Interface {

	/**
	* Does Paying order
	* @link https://cms.paypal.com/us/cgi-bin/?cmd=_render-content&content_ID=developer/e_howto_api_Requester_r_DoDirectPayment
	* @param Payment_Instant
	* @return Payment_Instant
	*/
	public function pay(Payment_Instant &$payment)
	{
		$empty_fields = $payment->empty_fields(array('billing_fname','billing_lname','amount','card_number','card_exp_month','card_exp_year'));
		if($empty_fields!==array())
		{
			throw new Payment_Exception(':fields_list fields are required for Paypal_Instant::Pay', array(':fields_list'=>implode(',',$empty_fields)));
		}

		//required fields
		$request = array(
			'FIRSTNAME'=> $payment->billing_fname,
			'LASTNAME'=> $payment->billing_lname,

			'AMT' => $payment->amount,
			'ACCT' => $payment->card_number,
			'EXPDATE' => $payment->card_exp_month.$payment->card_exp_year,

		);

		if($payment->card_cvv)
		{
			$request['CVV2'] = $payment->card_cvv;
		}
		if($payment->billing_email)
		{
			$request['EMAIL'] = $payment->billing_email;
		}
		if($payment->billing_address)
		{
			$request['STREET'] = $payment->billing_address;
		}
		if($payment->billing_city)
		{
			$request['CITY'] = $payment->billing_city;
		}
		if($payment->billing_state)
		{
			$request['STATE'] = $payment->billing_state;
		}
		if($payment->billing_country)
		{
			$request['COUNTRYCODE'] = $payment->billing_country;
		}
		if($payment->billing_zip)
		{
			$request['ZIP'] = $payment->billing_zip;
		}
		if($payment->custom)
		{
			$request['CUSTOM'] = $payment->custom;
		}
		if($payment->item_id)
		{
			$request['L_NUMBER0'] = $payment->item_id;
		}
		$response = $this->_post('DoDirectPayment', $request,'instant','pay');

		$payment->date = strtotime($response['TIMESTAMP']);
		$payment->txn_id = $response['TRANSACTIONID'];

		return $payment;
	}

	/**
	* Does refund order
	* @param Payment_Instant
	* @return Payment_Instant
	*/
	public function  refund(Payment_Instant &$payment)
	{
		//Not implemented
		return $payment;
	}
	
}