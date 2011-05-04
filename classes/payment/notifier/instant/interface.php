<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Interface for notifier implementation of managing instant payments
 * 
 * @author Alexey Geno <alexeygeno@gmail.com>
 * @package Payment
 */
interface Payment_Notifier_Instant_Interface {

	/**
	* Handle paying order
	* @param Payment_Instant
	*/
	public function pay_handled(Payment_Instant &$payment=NULL);

	/**
	* Handle refund order
	* @param Payment_Instant
	*/
	public function refund_handled(Payment_Instant &$payment=NULL);
}