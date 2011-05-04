<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Entry point of using Payment module functional 
 *
 * @author Alexey Geno <alexeygeno@gmail.com>
 * @package Payment
 */
class Payment_Instance {

	/**
	 * @var  array  instances
	 */
	protected static $_instances = array();

	/**
	 * Return notifier instance by given gateway and interface
	 * @param string gateway - only 'paypal' supported for now
	 * @param string interface - 'instant' and 'recurring' supported for now
	 * @return object
	 */
	public static function notifier($gateway,$interface)
	{
		$instance_name = 'notifier'.'_'.$gateway.'_'.$interface;
		if(isset(self::$_instances[$instance_name]))
		{
			return self::$_instances[$instance_name];
		}
		$gateway_reflection_instance = new ReflectionMethod('Payment_'.$gateway,'factory');
		return self::$_instances[$instance_name] = $gateway_reflection_instance->invokeArgs(NULL,array('Notifier',$interface));
	}

	/**
	 * Return requester instance by given gateway and interface
	 * @param string gateway - 'paypal' and 'linkpoint' supported for now
	 * @param string interface - 'instant' and 'recurring' supported for now
	 * @return object
	 */
	public static function requester($gateway,$interface)
	{
		$instance_name = 'requester'.'_'.$gateway.'_'.$interface;
		if(isset(self::$_instances[$instance_name]))
		{
			return self::$_instances[$instance_name];
		}
		$gateway_reflection_instance = new ReflectionMethod('Payment_'.$gateway,'factory');
		return self::$_instances[$instance_name] = $gateway_reflection_instance->invokeArgs(NULL,array('Requester',$interface));
	}

	/**
	 * Return buttons instance by given gateway
	 * @param string gateway - 'paypal' supported for now
	 * @return object
	 */
	public static function buttons($gateway)
	{
		$instance_name = 'buttons'.'_'.$gateway;
		if(isset(self::$_instances[$instance_name]))
		{
			return self::$_instances[$instance_name];
		}
		$gateway_reflection_instance = new ReflectionMethod('Payment_'.$gateway,'factory');
		return self::$_instances[$instance_name] = $gateway_reflection_instance->invokeArgs(NULL,array('Buttons',''));
	}
}