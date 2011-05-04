<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Interface for requester implementation of managing instant payments
 * 
 * @author Alexey Geno
 * @package Payment
 */
interface Payment_Requester_Instant_Interface {

	/**
	 * Does paying order
	 * @param Payment_Instant
	 */
	public function pay(Payment_Instant &$payment);

	/**
	 * Does refund order
	 * @param Payment_Instant
	 */
	public function refund(Payment_Instant &$payment);
}