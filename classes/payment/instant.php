<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Instant payment data managing
 *
 * @author Alexey Geno <alexeygeno@gmail.com>
 * @package Payment
 */
class Payment_Instant extends Payment {

	/**
	 * id of purchased item
	 * @var string
	 */
	protected $_item_id_field;

	/**
	 * return instance
	 * @return Payment_Instant
	 */
	public static function  factory()
	{
		$class_name = get_class();

		return new $class_name();
	}
}