<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Interface for implementation base gateway class
 *
 * @author Alexey Geno <alexeygeno@gmail.com>
 * @package Payment
 */
interface Payment_Interface {

	/**
	 * Returns instance of gateway class impemented for given type and interface
	 * @param type 'requester' or 'notifier'
	 * @param interface 'instant' and 'recurring' supported for now
	 */
	public static function factory($type,$interface);
}
