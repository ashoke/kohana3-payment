<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Interface for notifier implementation of managing reccuring payments profiles
 *
 * @author Alexey Geno <alexeygeno@gmail.com>
 * @package Payment
 */
interface Payment_Notifier_Recurring_Interface {

	/**
	 * Handle reccuring paying order
	 * @param Payment_Reccuring
	 */
	public function pay_handled(Payment_Recurring &$payment=NULL);
	/**
	 * Handle reccuring profile setup
	 * @param Payment_Reccuring
	 */
	public function setup_handled(Payment_Recurring &$payment=NULL);
	/**
	 * * Handle reccuring profile cancel
	 * @param Payment_Reccuring
	 */
	public function cancel_handled(Payment_Recurring &$payment=NULL);
}
