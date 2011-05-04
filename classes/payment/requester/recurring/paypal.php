<?php defined('SYSPATH') or die('No direct script access.');
/** 
 * Managing Paypal recurring payment profiles
 * 
 * @link https://cms.paypal.com/us/cgi-bin/?cmd=_render-content&content_ID=developer/e_howto_api_WPRecurringPayments
 * 
 * @author Alexey Geno <alexeygeno@gmail.com>
 * @package payment
 */
class Payment_Requester_Recurring_Paypal extends Payment_Requester_PayPal implements Payment_Requester_Recurring_Interface {

	/**
	 * Cancels active paypal recurring payment profile and provides note about the reason
	 * @link https://cms.paypal.com/us/cgi-bin/?cmd=_render-content&content_ID=developer/e_howto_api_Requester_r_ManageRecurringPaymentsProfileStatus
	 * @param Payment_recurring
	 * @throws Payment_Exception throws when one of required fields is empty
	 * @return Payment_recurring
	 */
	public function cancel(Payment_Recurring &$payment)
	{
		$empty_fields = $payment->empty_fields(array('profile_id'));
		if($empty_fields!==array())
		{
			throw new Payment_Exception(':fields_list fields are required for Paypal_Recurring::Cancel', array(':fields_list'=>implode(',',$empty_fields)));
		}
		$request = array(
				'PROFILEID' => $payment->profile_id,
				'ACTION'	=> 'Cancel'
				);
		if($payment->custom)
		{
			$request['CUSTOM'] = $payment->custom;
		}
		if($payment->note)
		{
			$request['NOTE'] = $payment->note;
		}
		$response = $this->_post('ManageRecurringPaymentsProfileStatus', $request,'recurring','cancel');
		$payment->date = strtotime($response['TIMESTAMP']);

		return $payment;
	}

	/**
	 * Setups new paypal recurring payment profile
	 * @link https://cms.paypal.com/us/cgi-bin/?cmd=_render-content&content_ID=developer/e_howto_api_nvp_r_CreateRecurringPayments
	 * @link https://cms.paypal.com/us/cgi-bin/?cmd=_render-content&content_ID=developer/e_howto_api_WPRecurringPayments#id08669F0705Z
	 * @param Payment_recurring
	 * @throws Payment_Exception throws when one of required fields is empty
	 * @return Payment_recurring
	 */
	public function  setup(Payment_Recurring &$payment)
	{
		$empty_fields = $payment->empty_fields(array('billing_fname','billing_lname','amount','card_number','card_exp_month','card_exp_year','frequency','frequency_num'));
		if($empty_fields!==array())
		{
			throw new Payment_Exception(':fields_list fields are required for Paypal_Recurring::Setup', array(':fields_list'=>implode(',',$empty_fields)));
		}
		//code of this function code have not tested
		$period_adapter = array('daily'=>'Day','weekly'=>'Week','monthly'=>'Month');
		$billing_period = $period_adapter[$payment->frequency];
		//required fields
		$request = array(
			'PROFILESTARTDATE'=> date('c'),//today
			'FIRSTNAME'=> $payment->billing_fname,
			'LASTNAME'=> $payment->billing_lname,

			'BILLINGPERIOD' => $billing_period,
			'BILLINGFREQUENCY' => $payment->frequency_num,
			'AMT' => $payment->amount,
			'ACCT' => $payment->card_number,
			'EXPDATE' => $payment->card_exp_month.$payment->card_exp_year,

		);
		//optional fields
		if($payment->note)
		{
			$request['DESC'] = $payment->note;
		}
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
		$response = $this->_post('CreateRecurringPaymentsProfile', $request,'recurring','setup');

		$payment->date = strtotime($response['TIMESTAMP']);
		$payment->profile_id = $response['PROFILEID'];

		return $payment;
	}
}
