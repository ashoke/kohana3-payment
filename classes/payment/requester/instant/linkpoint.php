<?php defined('SYSPATH') or die('No direct script access.');
/** 
 * Managing linkpoint instant payments
 * 
 * @link https://www.firstdata.com/downloads/marketing-merchant/fd_globalgatewayapi_usermanual.pdf
 * 
 * @author Alexey Geno <alexeygeno@gmail.com>
 * @package payment
 */
class Payment_Requester_Instant_Linkpoint extends Payment_Requester_Linkpoint implements Payment_Requester_Instant_Interface{

	/**
	* Does paying order
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

		$request['billing']['name'] = $payment->billing_fname.' '.$payment->billing_lname;
		$request['creditcard']['cardnumber'] = $payment->card_number;
		$request['payment']['chargetotal'] = $payment->amount;

		//linpoint needs card month in 2 digit in any case
		$request['creditcard']['cardexpmonth'] =
			((strlen($payment->card_exp_month)==1)?'0'.$payment->card_exp_month:$payment->card_exp_month);

		//linkpoint needs card year last 2 digit from year
		$request['creditcard']['cardexpyear'] =
			((strlen($payment->card_exp_year)==4)?substr($payment->card_exp_year,-2):$payment->card_exp_year);

		if($payment->card_cvv)
		{
			$request['creditcard']['cvmvalue'] = $payment->card_cvv;
		}

		if($payment->billing_company)
		{
			$request['billing']['company'] = $payment->billing_company;
		}
		if($payment->billing_city)
		{
			$request['billing']['city'] = $payment->billing_city;
		}
		if($payment->billing_state)
		{
			$request['billing']['state'] = $payment->billing_state;
		}

		if($payment->billing_country)
		{
			$request['billing']['country'] = $payment->billing_country;
		}
		if($payment->billing_zip)
		{
			$request['billing']['zip'] = $payment->billing_zip;
		}
		if($payment->billing_email)
		{
			$request['billing']['email'] = $payment->billing_email;
		}
		if($payment->billing_address)
		{
			$request['billing']['address1'] = $payment->billing_address;
		}
		if($payment->custom)
		{
			$request['billing']['userid'] = $payment->custom;
		}
		if($payment->item_id)
		{
			$request['transactiondetails']['ponumber'] = $payment->item_id;
		}

		$response = $this->_post('SALE', $request,'instant','pay');
		$payment->txn_id = $response['r_ordernum'];
		$payment->approval_code = $response['r_code'];
		$payment->date = strtotime($response['r_time']);

		return $payment;
	}

	/**
	* Does refund order
	* @param Payment_Instant
	* @return Payment_Instant
	*/
	public function refund(Payment_Instant &$payment)
	{
		//not implemented
		return $payment;
	}
}
