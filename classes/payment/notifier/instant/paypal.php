<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Paypal Instant payment notifier implementation, strongly recomended to read doc by link to understand how it works
 *
 * @link https://cms.paypal.com/cms_content/US/en_US/files/developer/IPNGuide.pdf
 *
 * @author Alexey Geno <alexeygeno@gmail.com>
 * @package Payment
 */
class Payment_Notifier_Instant_Paypal extends Payment_Notifier_Paypal implements Payment_Notifier_Instant_Interface {

	/**
	 * If paying order handled
	 * @var bool
	 */
	protected $_pay;
	/**
	 * If refund order handled
	 * @var bool
	 */
	protected $_refund;

	/**
	 * Instant payment for fill in after handling
	 * @var Payment_Instant
	 */
	protected $_instant_payment;

	/**
	 * Logs handled data and checks if operation success or failed
	 * @throws Payment_Exception throws if operation failed
	 */
	protected function after_confirm()
	{
		$custom = (isset($this->_post_data['custom'])?$this->_post_data['custom']:FALSE);
		if($this->_pay)
		{
			$success = (isset($this->_post_data['payment_status']) AND $this->_post_data['payment_status']==='Completed');
			$txn_id = (isset($this->_post_data['txn_id'])?$this->_post_data['txn_id']:FALSE);
			$date = (($this->_post_data['payment_date'])?strtotime($this->_post_data['payment_date']):FALSE);
			$action = 'pay';
		}
		elseif($this->_refund)
		{
			//refund not implemented yet
			$action = 'refund';
		}

		$payment_log = new Model_Payment_Log('notifier','paypal');
		$payment_log->create('instant',$action,$success, $this->_post_data, array(), $txn_id, $date, $custom);
		if(!$success)
		{
			throw new Payment_Exception("Paypal ipn have not appied: payment_status is not 'Completed' but is ':status'. POST: :post ",
				array(':status'=>$this->_post_data['payment_status'],':post'=>print_r($this->_post_data,TRUE)));
		}
	}

	/**
	 * If current notifier handled
	 * @return bool
	 */
	public function handled()
	{
		$this->_pay = (isset($this->_post_data['txn_type']) AND $this->_post_data['txn_type'] == 'web_accept' AND
			isset($this->_post_data['payment_type']) AND $this->_post_data['payment_type'] == 'instant');

		//refund has not ipmplemented
		$this->_refund = FALSE;

		$handled = ($this->_pay OR $this->_refund);

		if($handled)
		{
			//fields common to all actions
			$this->_instant_payment = Payment_Instant::factory();

			if(isset($this->_post_data['first_name']))
			{
				$this->_instant_payment->billing_fname = $this->_post_data['first_name'];
			}
			if(isset($this->_post_data['last_name']))
			{
				$this->_instant_payment->billing_lname = $this->_post_data['last_name'];
			}
			if(isset($this->_post_data['payer_business_name']))
			{
				$this->_instant_payment->billing_company = $this->_post_data['payer_business_name'];
			}
			if(isset($this->_post_data['address_city']))
			{
				$this->_instant_payment->billing_city = $this->_post_data['address_city'];
			}
			if(isset($this->_post_data['address_state']))
			{
				$this->_instant_payment->billing_state = $this->_post_data['address_state'];
			}
			if(isset($this->_post_data['address_country_code']))
			{
				$this->_instant_payment->billing_country = $this->_post_data['address_country_code'];
			}
			if(isset($this->_post_data['address_zip']))
			{
				$this->_instant_payment->billing_zip = $this->_post_data['address_zip'];
			}
			if(isset($this->_post_data['payer_email']))
			{
				$this->_instant_payment->billing_email = $this->_post_data['payer_email'];
			}
			if(isset($this->_post_data['address_street']))
			{
				$this->_instant_payment->billing_address = $this->_post_data['address_street'];
			}
			if(isset($this->_post_data['custom']))
			{
				$this->_instant_payment->custom = $this->_post_data['custom'];
			}
			if(isset($this->_post_data['item_number']))
			{
				$this->_instant_payment->item_id = $this->_post_data['item_number'];
			}
		}
		return $handled;
	}

	/**
	 * If paying order is handled then fills in Payment_Instant
	 * and returns TRUE else returns FALSE
	 * @param Payment_Instant
	 */
	public function  pay_handled(Payment_Instant &$payment=NULL)
	{
		if(!$this->_pay)
		{
			return FALSE;
		}
		//adapt fields
		if(isset($this->_post_data['payment_gross']))
		{
			$this->_instant_payment->amount = $this->_post_data['payment_gross'];
		}
		if(isset($this->_post_data['txn_id']))
		{
			$this->_instant_payment->txn_id = $this->_post_data['txn_id'];
		}
		if(isset($this->_post_data['verify_sign']))
		{
			$this->_instant_payment->approval_code = $this->_post_data['verify_sign'];
		}
		if(isset($this->_post_data['payment_date']))
		{
			$this->_instant_payment->date = strtotime($this->_post_data['payment_date']);
		}
		$payment = $this->_instant_payment;
		return TRUE;
	}

	/**
	 * If refund order is handled then fills in Payment_Instant
	 * and returns TRUE else returns FALSE
	 * @param Payment_Instant
	 */
	public function refund_handled(Payment_Instant &$payment=NULL)
	{
		//not implemented
		return FALSE;
	}
}