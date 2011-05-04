<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Reccuring payment data managing
 *
 * @author Alexey Geno <alexeygeno@gmail.com>
 * @package Payment
 */
class Payment_Recurring extends Payment {

	//Payment fields should be with '_field' postfix

	/**
	 * id of purchased item
	 * @var string
	 */
	protected $_item_id_field;

	/**
	 * recurring profile id
	 * @var string
	 */
	protected $_profile_id_field;

	/**
	 * recurring frequency
	 * @var string
	 */
	protected $_frequency_field; //daily, weekly, monthly, yearly

	/**
	 * number of frequency 1..99
	 * @var string
	 */
	protected $_frequency_num_field; //num for frequency. F.e every 3 days or 2 monthes

	/**
	 * recurring installments - how many times recurring will occur
	 * @var string
	 */
	protected $_installments_field; //how many times

	/**
	 * note - some comments
	 * @var string
	 */
	protected $_note_field; //comments

	//internal logic fields

	/**
	 * available frequencies
	 * @var array
	 */
	protected $_frequencies = array('daily','weekly','monthly','yearly');

	/**
	 * Returns instance
	 * @return Payment_Recurring
	 */
	public static function  factory()
	{
		$class_name = get_class();
		return new $class_name();
	}

	/**
	 * validates frequency field
	 * @param string value
	 * @return bool
	 */
	protected function _validate_frequency($value)
	{
		if($value AND !in_array($value, $this->_frequencies))
		{
			throw new Payment_Exception('frequency should be one from list: :frequencies_list',
						array(':frequencies_list'=>"'".implode("','",$this->_frequencies)."'"));
		}
		return TRUE;
	}
}