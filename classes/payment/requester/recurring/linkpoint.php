<?php defined('SYSPATH') or die('No direct script access.');
/** 
 * Managing linkpoint recurring payment profiles
 *
 * @link https://www.firstdata.com/downloads/marketing-merchant/fd_globalgatewayapi_usermanual.pdf
 *  
 * @author Alexey Geno <alexeygeno@gmail.com>
 * @package payment
 */
class Payment_Requester_Recurring_Linkpoint extends Payment_Requester_Linkpoint implements Payment_Requester_Recurring_Interface {

	/**
	 * Cancels active linkpoint recurring payment and provides note about the reason
	 * @param Payment_recurring
	 * @throws Payment_Exception throws when one of required fields is empty
	 * @return Payment_recurring
	 */
	public function cancel(Payment_Recurring &$payment)
	{
		$empty_fields = $payment->empty_fields(array('profile_id','card_number'));
		if($empty_fields!==array())
		{
			throw new Payment_Exception(':fields_list fields are required for Linkpoint_recurring::Cancel',
							array(':fields_list'=>implode(',',$empty_fields)));
		}

		$request['periodic']['action'] = 'Cancel';
		$request['periodic']['threshold'] = '5';//max
		$request['transactiondetails']['oid'] = $payment->profile_id;
		$request['creditcard']['cardnumber'] = $payment->card_number;
		if($payment->note)
		{
			$request['periodic']['comments'] = $payment->note;
		}
		if($payment->custom)
		{
			$request['billing']['userid'] = $payment->custom;
		}
		//does request
		$response = $this->_post('SALE', $request,'recurring','cancel');
		//fills Payment_Reccuring by response
		$payment->date = strtotime($response['r_time']);

		return $payment;
	}

	/**
	 * Setups new linpoint recurring payment	 
	 * @param Payment_recurring
	 * @throws Payment_Exception throws when one of required fields is empty
	 * @return Payment_recurring
	 */
	public function setup(Payment_Recurring &$payment)
	{
		$empty_fields = $payment->empty_fields(array('billing_fname','billing_lname',
								 'amount','card_number','card_exp_month',
								 'card_exp_year','frequency','frequency_num'));
		if($empty_fields!==array())
		{
			throw new Payment_Exception(':fields_list fields are required for Linkpoint_Recurring::Setup',
				array(':fields_list'=>implode(',',$empty_fields)));
		}
		//payment fields
		//adapt required fields
		$request['creditcard']['cardnumber'] = $payment->card_number;
			//linpoint needs card month in 2 digit in any case
		$request['creditcard']['cardexpmonth'] =
			((strlen($payment->card_exp_month)==1)?'0'.$payment->card_exp_month:$payment->card_exp_month);

		//linkpoint needs card year last 2 digit from year
		$request['creditcard']['cardexpyear'] =
			((strlen($payment->card_exp_year)==4)?substr($payment->card_exp_year,-2):$payment->card_exp_year);
		$request['billing']['name'] = $payment->billing_fname.' '.$payment->billing_lname;
		$request['payment']['chargetotal'] = $payment->amount;

		//convert frequency and frequency_num in linkpoint format: m3 - charging once every 3 months.
		//See https://www.firstdata.com/downloads/marketing-merchant/fd_globalgatewayapi_usermanual.pdf - page 33

		$request['periodic']['periodicity'] = substr($payment->frequency,0,1).$payment->frequency_num;
		if($payment->installments)
		{
			$request['periodic']['installments'] = $payment->installments;
		}
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

		//recurring fields
		$request['periodic']['startdate'] = 'immediate';
		$request['periodic']['action'] = 'SUBMIT';
		$request['periodic']['threshold'] = '5';//max

		//does request
		$response = $this->_post('SALE', $request,'recurring','setup');
		//fills Payment_Reccuring by response
		$payment->txn_id = $response['r_ordernum'];
		$payment->approval_code = $response['r_code'];
		$payment->date = strtotime($response['r_time']);
		$payment->profile_id = $response['r_ordernum'];

		return $payment;
	}
}