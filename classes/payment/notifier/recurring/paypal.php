<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Paypal reccuring payment notifier implementation, strongly recomended to read doc by link to understand how it works
 *
 * @link https://cms.paypal.com/cms_content/US/en_US/files/developer/IPNGuide.pdf
 *
 * @author Alexey Geno <alexeygeno@gmail.com>
 * @package Payment
 */
class Payment_Notifier_Recurring_Paypal extends Payment_Notifier_Paypal implements Payment_Notifier_Recurring_Interface {

	/**
	 * If reccuring payment order handled
	 * @var bool
	 */
	protected $_pay;
	/**
	 * If reccuring profile cancel handled
	 * @var bool
	 */
	protected $_cancel;

	/**
	 * If reccuring profile setup handled
	 * @var bool
	 */
	protected $_setup;

	/**
	 * Reccuring payment for fill in after handling
	 * @var Payment_Instant
	 */
	protected $_recurring_payment;

	/**
	 * If current notifier handled
	 * @return bool
	 */
	protected function handled()
	{
		$this->_pay = (isset($this->_post_data['txn_type']) AND $this->_post_data['txn_type'] == 'subscr_payment' AND
			isset($this->_post_data['payment_type']) AND $this->_post_data['payment_type'] == 'instant');

		$this->_setup = (isset($this->_post_data['txn_type']) AND $this->_post_data['txn_type'] == 'subscr_signup');
		$this->_cancel = (isset($this->_post_data['txn_type']) AND $this->_post_data['txn_type'] == 'subscr_cancel');

		$handled = ($this->_pay OR $this->_setup OR $this->_cancel);
		//fields common to all actions
		if($handled)
		{
			$this->_recurring_payment = Payment_Recurring::factory();

			if(isset($this->_post_data['first_name']))
			{
				$this->_recurring_payment->billing_fname = $this->_post_data['first_name'];
			}
			if(isset($this->_post_data['last_name']))
			{
				$this->_recurring_payment->billing_lname = $this->_post_data['last_name'];
			}
			if(isset($this->_post_data['payer_business_name']))
			{
				$this->_recurring_payment->billing_company = $this->_post_data['payer_business_name'];
			}
			if(isset($this->_post_data['address_city']))
			{
				$this->_recurring_payment->billing_city = $this->_post_data['address_city'];
			}
			if(isset($this->_post_data['address_state']))
			{
				$this->_recurring_payment->billing_state = $this->_post_data['address_state'];
			}
			if(isset($this->_post_data['address_country_code']))
			{
				$this->_recurring_payment->billing_country = $this->_post_data['address_country_code'];
			}
			if(isset($this->_post_data['address_zip']))
			{
				$this->_recurring_payment->billing_zip = $this->_post_data['address_zip'];
			}
			if(isset($this->_post_data['payer_email']))
			{
				$this->_recurring_payment->billing_email = $this->_post_data['payer_email'];
			}
			if(isset($this->_post_data['address_street']))
			{
				$this->_recurring_payment->billing_address = $this->_post_data['address_street'];
			}
			if(isset($this->_post_data['custom']))
			{
				$this->_recurring_payment->custom = $this->_post_data['custom'];
			}
			if(isset($this->_post_data['item_number']))
			{
				$this->_recurring_payment->item_id = $this->_post_data['item_number'];
			}
			if(isset($this->_post_data['subscr_id']))
			{
				$this->_recurring_payment->profile_id = $this->_post_data['subscr_id'];
			}
			if(isset($this->_post_data['recur_times']))
			{
				$this->_recurring_payment->installments = $this->_post_data['recur_times'];
			}
			if(isset($this->_post_data['period3']))
			{
				////native format: 4 D, 3 W, 2 M, 1 Y
				$frequency = substr($this->_post_data['period3'],-1);
				$frequency_num = substr($this->_post_data['period3'],0,1);
				$frequency_adapter = array('D'=>'daily', 'W'=>'weekly', 'M'=>'monthly','Y'=>'yearly');
				$this->_recurring_payment->frequency = $frequency_adapter[$frequency];
				$this->_recurring_payment->frequency_num = $frequency_num;
			}
		}
		return $handled;
	}

	/**
	 * Logs handled data and checks if operation success or failed
	 * @throws Payment_Exception throws if operation failed
	 */
	protected function  after_confirm()
	{
		//common
		$custom = (isset($this->_post_data['custom'])?$this->_post_data['custom']:FALSE);
		//for recurring payment
		if($this->_pay)
		{
			$success = (isset($this->_post_data['payment_status']) AND $this->_post_data['payment_status']==='Completed');
			$txn_id = (isset($this->_post_data['txn_id'])?$this->_post_data['txn_id']:FALSE);
			$date = (($this->_post_data['payment_date'])?strtotime($this->_post_data['payment_date']):FALSE);
			$action = 'pay';
		}
		elseif($this->_cancel OR $this->_setup)
		{
			//for setup and cancel
			$date = strtotime($this->_post_data['subscr_date']);
			$success = TRUE;//if data recieved - then success is true
			$txn_id = FALSE;
			$action = ($this->_setup?'setup':'cancel');
		}
		$log = new Model_Payment_Log('notifier','paypal');
		$log->create('recurring',$action,$success, $this->_post_data, array(), $txn_id, $date, $custom);
		if(!$success)
		{
			throw new Payment_Exception("Paypal ipn have not appied: payment_status is not 'Completed' but is ':status'. POST: :post ",
					array(':status'=>$this->_post_data['payment_status'],':post'=>print_r($this->_post_data,TRUE)));
		}
	}

	/**
	 * If recurring payment order is handled then fills in Payment_Reccuring
	 * and returns TRUE else returns FALSE
	 * @param Payment_Recurring
	 * @return bool
	 */
	public function  pay_handled(Payment_Recurring &$payment=NULL)
	{		
		if(!$this->_pay)
		{
			return FALSE;
		}
		//adapt fields
		if(isset($this->_post_data['payment_gross']))
		{
			$this->_recurring_payment->amount = $this->_post_data['payment_gross'];
		}
		
		if(isset($this->_post_data['txn_id']))
		{
			$this->_recurring_payment->txn_id = $this->_post_data['txn_id'];
		}
		if(isset($this->_post_data['verify_sign']))
		{
			$this->_recurring_payment->approval_code = $this->_post_data['verify_sign'];
		}
		if(isset($this->_post_data['payment_date']))
		{
			$this->_recurring_payment->date = strtotime($this->_post_data['payment_date']);
		}
		$payment = $this->_recurring_payment;
		return TRUE;
	}

	/**
	 * If recurring profile setup is handled then fills in Payment_Reccuring
	 * and returns TRUE else returns FALSE
	 * @param Payment_Recurring
	 * @return bool
	 */
	public function  setup_handled(Payment_Recurring &$payment=NULL)
	{
		if(!$this->_setup)
		{
			return FALSE;
		}
		if(isset($this->_post_data['subscr_date']))
		{
			$this->_recurring_payment->date = strtotime($this->_post_data['subscr_date']);
		}
		if(isset($this->_post_data['mc_amount3']))
		{
			$this->_recurring_payment->amount = $this->_post_data['mc_amount3'];
		}
		$payment = $this->_recurring_payment;
		return TRUE;
	}

	/**
	 * If recurring profile cancel is handled then fills in Payment_Reccuring
	 * and returns TRUE else returns FALSE
	 * @param Payment_Recurring
	 * @return bool
	 */
	public function  cancel_handled(Payment_Recurring &$payment=NULL)
	{
	   if(!$this->_cancel)
	   {
		return FALSE;
	   }
	   if(isset($this->_post_data['subscr_date']))
	   {
		$this->_recurring_payment->date = strtotime($this->_post_data['subscr_date']);
	   }
	   if(isset($this->_post_data['mc_amount3']))
	   {
		$this->_recurring_payment->amount = $this->_post_data['mc_amount3'];
	   }
	   $payment = $this->_recurring_payment;
	   return TRUE;
	}
}
