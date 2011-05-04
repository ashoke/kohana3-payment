<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Interface for requester implementation of managing reccuring payments profiles
 * 
 * @author Alexey Geno <alexeygeno@gmail.com>
 * @package Payment
 */
interface Payment_Requester_Recurring_Interface {

	/**
	* Setups new reccuring payment profile
	* @param Payment_Recurring
	*/
	public function setup(Payment_Recurring &$payment);

	/**
	* Cancels active reccuring payment profile
	* @param Payment_Recurring
	*/
	public function cancel(Payment_Recurring &$payment);
}

