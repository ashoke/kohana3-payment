#Payment

Payment module for Kohana 3. Paypal and Linkpoint supported for now.

##Basic terms

Gateway - payment system like paypal, linpoint, authorize.net etc.

Mechanism types

'notifier' - gateway sends data to  app(to defined controller) for notify about some actions that has been placed at gateway. 

'requester' - data sends from app to gateway about some action as request and gateway return response as result of that request.

##Install

php extensions required: [curl] (http://php.net/manual/en/book.curl.php)

kohana official modules required: database

1.applly script for your DBMS - only db/mysql.sql available for now

2.configure merchant's options and enviroment at config/payment.php

Basic usages you can see at test controller - classes/controller/payment/test

##Usage

	/**
	 * Test controller for payment module.
	 *
	 * @author Alexey Geno <alexeygeno@gmail.com>
	 * @package Payment 
	 */
	class Controller_Payment_Test extends Controller {

	/**
	 * Notifier action, only paypal is supported for now
	 * @link https://cms.paypal.com/us/cgi-bin/?cmd=_render-content&content_ID=developer/e_howto_admin_IPNIntro
	 * @param string gateway
	 */
	public function action_notifier($gateway)
	{
		$gateway = 'paypal'; //paypal is only supported for now
		try
		{
			if(Payment_Instance::notifier($gateway, 'recurring')->pay_handled($payment))
			{
				Kohana::$log->add(Kohana_Log::DEBUG,'Recuuring payment handled :payment',array(':payment'=>$payment));
				//here you can apply $payment to your reccuring payment business logic
			}
			elseif(Payment_Instance::notifier($gateway, 'recurring')->setup_handled($payment))
			{
				Kohana::$log->add(Kohana_Log::DEBUG,'Recuuring subscr setup handled :payment',array(':payment'=>$payment_recurring));
				//here you can apply $payment to your reccuring profile creation business logic
			}
			elseif(Payment_Instance::notifier($gateway, 'recurring')->cancel_handled($payment))
			{
				Kohana::$log->add(Kohana_Log::DEBUG,'Recuuring subscr cancel handled :payment',array(':payment'=>$payment_recurring));
				//here you can apply $payment to your reccuring profile cancelation business logic
			}
			elseif(Payment_Instance::notifier($gateway, 'instant')->pay_handled($payment))
			{
				Kohana::$log->add(Kohana_Log::DEBUG,'Instant payment handled :payment',array(':payment'=>$payment));
				//here you can apply $payment to your instant payment business logic
			}
		}
		catch(Payment_Exception $payment_exception)
		{
			//gateway did not apply operation
			Kohana::$log->add(Kohana_Log::ERROR,'Notifier operation failed: ":message" gateway: :gateway :payment',
				array(':payment'=>$payment,':gateway'=>$gateway,':message'=>$payment_exception->getMessage()));
			//here you can apply failed operation business locgic
		}
	}

	/**
	 * Instant pay action
	 * @param string gateway
	 */
	public function action_requester_instant_pay($gateway)
	{
		try
		{
			$payment = Payment_Instant::factory();
			$payment->card_number = '4000001234567899';
			$payment->card_exp_year = '2012';
			$payment->card_exp_month = '11';
			$payment->amount = '62.00';
			$payment->card_cvv = '234';
			$payment->custom = 'user_id';
			$payment->item_id = 'item_id';

			$payment->billing_fname = 'Fname';
			$payment->billing_lname = 'Lname';
			$payment->billing_state = 'NY';
			$payment->billing_city = 'New York';
			$payment->billing_address = 'Address';
			$payment->billing_country = 'US';
			$payment->billing_zip = '12345';
			$payment->billing_company = 'Company';
			$payment->billing_email = 'email@email.email';

			Payment_Instance::Requester($gateway,'instant')->pay($payment);
			//here you can apply $payment to your instant payment business logic
			echo "<pre>$payment</pre>";
		}
		catch(Payment_Exception $payment_exception)
		{
			//gateway did not apply operation
			Kohana::$log->add(Kohana_Log::ERROR,'Requester instant pay operation failed: ":message" gateway: :gateway :payment',
				array(':payment'=>$payment,':gateway'=>$gateway,':message'=>$payment_exception->getMessage()));
			//here you can apply failed operation business logic
		}
	}

	/**
	 * Reccuring payment setup action
	 * @param string gateway
	 */
	public function action_requester_recurring_setup($gateway)
	{
		try
		{
			$payment = Payment_Recurring::factory();
			$payment->card_number = '4000001234567899';
			$payment->card_exp_year = '2014';
			$payment->card_exp_month = '10';
			$payment->amount = '11.00';
			$payment->card_cvv = '2345';
			$payment->custom = 'user_id';
			$payment->item_id = 'item_id';

			$payment->billing_fname = 'Fname';
			$payment->billing_lname = 'Lname';
			$payment->billing_state = 'NY';
			$payment->billing_city = 'New York';
			$payment->billing_address = 'Address';
			$payment->billing_country = 'US';
			$payment->billing_zip = '12345';
			$payment->billing_company = 'Company';
			$payment->billing_email = 'email@email.email';

			$payment->frequency = 'daily';
			$payment->frequency_num = '30';
			$payment->installments = '2';
			$payment->note = 'Some Note';

			Payment_Instance::Requester($gateway,'recurring')->setup($payment);
			echo "<pre>$payment</pre>";
			//here you can apply $payment to your recurring payment business logic
		}
		catch(Payment_Exception $payment_exception)
		{
			//gateway did not apply operation
			Kohana::$log->add(Kohana_Log::ERROR,'Requester recurring setup operation failed: ":message" gateway: :gateway :payment',
				array(':payment'=>$payment,':gateway'=>$gateway,':message'=>$payment_exception->getMessage()));
			//here you can apply failed operation business logic
		}
	}
	/**
	 * Reccuring payment cancel action
	 * @param string gateway
	 * @param string reccuring profile id
	 */
	public function action_requester_recurring_cancel($gateway,$profile_id)
	{
		try
		{
			$payment = Payment_Recurring::factory();
			$payment->card_number = '4000001234567899';
			$payment->profile_id = $profile_id;
			$payment->custom = 'recurr_custom';
			$payment->note = 'Some Note';

			Payment_Instance::Requester($gateway,'Recurring')->cancel($payment);
			echo "<pre>$payment</pre>";
			//here you can apply $payment to your recurring payment business logic
		}
		catch(Payment_Exception $payment_exception)
		{
			//gateway did not apply operation
			Kohana::$log->add(Kohana_Log::ERROR,'Requester recurring cancel operation failed: ":message" gateway: :gateway :payment',
				array(':payment'=>$payment,':gateway'=>$gateway,':message'=>$payment_exception->getMessage()));
			//here you can apply failed operation business logic
		}
	}

	/**
	 * Drawing operations-buttons action
	 * @param string gateway
	 */
	public function action_buttons($gateway)
	{
		$gateway = 'paypal'; //paypal is only supported for now
		echo Payment_Instance::buttons($gateway)->buynow(array('hosted_button_id'=>'6D36KDD6P9ULW','custom'=>'account_id'));
		echo Payment_Instance::buttons($gateway)->subscribe(array('hosted_button_id'=>'JJAF2QT55ELBU','custom'=>'account_id'));
		echo Payment_Instance::buttons($gateway)->unsubscribe();
	}
	}


 