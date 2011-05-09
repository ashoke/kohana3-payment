<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Base clase for managing data posted/responded to/from gateways
 *
 * @author Alexey Geno <alexeygeno@gmail.com>
 * @package Payment
 */
abstract class Payment {

	//Payment fields should be with '_field' postfix
	/**
	 * transaction id
	 * @var string
	 */

	protected $_txn_id_field;
	/**
	 * approval code
	 * @var string
	 */

	protected $_approval_code_field;
	/**
	 * card number
	 * @var string
	 */

	protected $_card_number_field;
	/**
	 * card expiration month in MM format
	 * @var string
	 */

	protected $_card_exp_month_field;
	/**
	 * card expiration year in YYYY format
	 * @var string
	 */

	protected $_card_exp_year_field;
	/**
	 * card cvv code 3 or 4 digit
	 * @var string
	 */

	protected $_card_cvv_field;
	/**
	 * custom field, usually used for app user_id
	 * @var string
	 */

	protected $_custom_field;
	/**
	 * amount in DD.DD format
	 * @var <type>
	 */

	protected $_amount_field;
	/**
	 * billing first name
	 * @var string
	 */

	protected $_billing_fname_field;
	/**
	 * billing last name
	 * @var string
	 */

	protected $_billing_lname_field;
	/**
	 * billing state in 2 chars format
	 * @link http://www.cfappsinc.ca/tools/view_provstate.php
	 * @var string
	 */

	protected $_billing_state_field;
	/**
	 * billing city
	 * @var string
	 */

	protected $_billing_city_field;
	/**
	 * billing address
	 * @var string
	 */

	protected $_billing_address_field;
	/**
	 * billing country in 2 chars format, f.e UK,US
	 * @var string
	 * @link http://www.iso.org/iso/english_country_names_and_code_elements
	 */

	protected $_billing_country_field;
	/**
	 * billing zip
	 * @var string
	 */

	protected $_billing_zip_field;
	/**
	 * billing zip code
	 * @var string
	 */

	protected $_billing_company_field;
	/**
	 * billing email
	 * @var string
	 */

	protected $_billing_email_field;

	/**
	 * date of gateway operation in timestamp format
	 * @var string
	 */
	protected $_date_field;

	/**
	 * returns instance of current class
	 */
	public static function factory()
	{
		//empty, shoud be implemented in child classes
	}

	/**
	 * magic method
	 * sets value for field
	 * to assign validation to any field add protected method
	 * _validate_[field_name] and it will be called automatically
	 * @param string field
	 * @param string value
	 */
	public function __set($field,$value)
	{
		if(array_key_exists($field,$this->as_array()))
		{
			$validate_method_name = '_validate_'.$field;
			if(method_exists($this, $validate_method_name))
			{
			$this->$validate_method_name($value);
			}
			$var_name = '_'.$field.'_field';
			$this->$var_name = $value;
		}
		else
		{
			throw new Kohana_Exception("Wrong Payment field: $field");
		}
	}

	/**
	 * magic method
	 * gets value of field
	 * @param string field
	 * @return string
	 */
	public function __get($field)
	{
		if(array_key_exists($field,$this->as_array()))
		{
			$var_name = '_'.$field.'_field';
			return $this->$var_name;
		}
		else
		{
			throw new Kohana_Exception("Wrong Payment field: $field");
		}
	}

	/**
	 * magic method
	 * string view of object
	 * @return sting
	 */
	public function  __toString()
	{
		return print_r($this->as_array(),TRUE);
	}

	/**
	 * sets values to fields from array
	 * @param array fields 
	 */
	public function change_values(array $fields)
	{
		foreach($fields AS $field=>$value)
		{
			$this->__set($field,$value);
		}
	}

	/**
	 * returns fields names and values as array
	 * @return array
	 */
	public function as_array()
	{
		$fields_full_list =  get_object_vars($this);
		$payment_fields = array();
		foreach($fields_full_list AS $field=>$value)
		{
			//if postfix is '_field' then it is payment fields but not internal ligic field
			if(substr($field,-6)==='_field')
			{
			//cut prefix '_' and postfix '_field'
			$key = substr($field,1,-6);
			//var_dump($key);
			$payment_fields[$key] = $value;
			}
		}

		return $payment_fields;
	}

	/**
	 * Returns array of empty fields from passed array or from all fields
	 * @param array fields, optional
	 * @return array
	 */
	public function empty_fields(array $fields=array())
	{
		if($fields===array())
		{
			$fields = $this->as_array();
		}
		$empty_fields = array();
		foreach($fields AS $field)
		{
			if(!$this->__get($field))
			{
			$empty_fields[] = $field;
			}
		}

		return $empty_fields;
	}

	/**
	 * validates card_exp_year field
	 * @param string $value
	 * @throws Payment_exception
	 * @return bool
	 */
	protected function _validate_card_exp_year($value)
	{
		if($value AND strlen($value)!==4)
		{
			throw new Payment_Exception('card_exp_year should be in YYYY format');
		}

		return TRUE;
	}

	/**
	 * validates card_exp_month field
	 * @param string value
	 * @throws Payment_exception
	 * @return bool
	 */
	protected function _validate_card_exp_month($value)
	{
		if($value AND strlen($value)!==2)
		{
			throw new Payment_Exception('card_exp_year should be in MM format');
		}

		return TRUE;
	}

	/**
	 * validates card_cvv field
	 * @param string value
	 * @throws Payment_exception
	 * @return bool
	 */
	protected function _validate_card_cvv($value)
	{
		if($value AND strlen($value)!==3 AND strlen($value)!==4)
		{
			throw new Payment_Exception('card_exp_cvv should be as 3 or 4 digit length');
		}

		return TRUE;
	}

	/**
	 * validates billing_state field
	 * @param string value
	 * @throws Payment_exception
	 * @return bool
	 */
	protected function _validate_billing_state($value)
	{
		if($value AND strlen($value)!==2)
		{
			throw new Payment_Exception('billing_state should be as 2 symbol length');
		}

		return TRUE;
	}
	
	/**
	 * validates billing_country field
	 * @param string value
	 * @throws Payment_exception
	 * @return bool
	 */
	protected function _validate_billing_country($value)
	{
		if($value AND strlen($value)!==2)
		{
			throw new Payment_Exception('billing_country should be as 2 symbol length');
		}

		return TRUE;
	}
}