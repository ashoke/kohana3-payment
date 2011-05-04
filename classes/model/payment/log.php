<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Payment log model
 * 
 * @author Alexey Geno <alexeygeno@gmail.com>
 * @package Payment
 */
class Model_Payment_Log
{
	/**
	 * Payment gateway
	 * @var string
	 */
	protected $_gateway;
	
	/**
	 * Type - 'requester' or 'notifier'
	 * @var string
	 */
	protected $_type;

	/**
	 * Creates new instance for type an gateway
	 * @param string type - 'requester' or 'notifier'
	 * @param string one of supported gateway
	 */
	public function  __construct($type,$gateway)
	{
		if(!isset(Kohana::config('payment')->$gateway))
		{
			throw new Kohana_Exception('Wrong gateway: :gateway', array(':gateway' => $gateway));
		}
		elseif(!($type=='notifier' OR $type=='requester'))
		{
			throw new Kohana_Exception('Wrong type: :type', array(':type' => $type));
		}
		$this->_gateway = $gateway;
		$this->_type = $type;
	}
	
	/**
	 * Creates new log record
	 * @param string interface base name
	 * @param string action
	 * @param bool success, if operation success or failed
	 * @param array recieved data from gateway
	 * @param array sent data to gateway, optional
	 * @param string transaction id, optional
	 * @param string date from gateway in timestamp format
	 * @param string custom field from gateway, usually used as app user_id
	 * @return Model_Payment_Log
	 */
	public function create($interface, $action, $success, array $recieved_data,array $sent_data=array(),$txn_id=FALSE,$date=FALSE,$custom=FALSE)
	{
		if($this->exists($interface,$action,$txn_id))
		{
			throw new Payment_Exception('Duplicate transaction: gateway: :gateway type: :type interface: :interface action: :action  txn_id: :txn_id '.
							'SENT DATA: :sent_data, RECIEVED_DATA: :recieved_data',
					array(
						':gateway'=>$this->_gateway,
						':type'=>$this->_type,
						':interface'=>$interface,
						':action'=>$action,
						':txn_id'=>$txn_id,
						':sent_data'=>print_r($sent_data,TRUE),
						':recieved_data'=>print_r($recieved_data,TRUE)
					)
				);
		}
		$data['type'] = $this->_type;
		$data['gateway'] = $this->_gateway;
		$data['interface'] = $interface;
		$data['action'] = $action;
		if($sent_data!==array())
		{
			$data['sent_data'] = serialize($sent_data);
		}
		if($date)
		{
			$data['date'] = $date;
		}
		if($txn_id)
		{
			$data['txn_id'] = $txn_id;
		}
		if($custom)
		{
			$data['custom'] = $custom;
		}
		$data['recieved_data'] = serialize($recieved_data);
		$data['created_date'] = DB::expr('NOW()');
		$data['success'] = (bool)$success;
		DB::insert('payment_logs',array_keys($data))->values(array_values($data))->execute();
		return $this;
	}

	/**
	 * If transaction is duplicate
	 * @param string interface base name
	 * @param string action
	 * @param string transaction id
	 * @return bool
	 */
	public function exists($interface,$action,$txn_id)
	{
		$res =  DB::select('id')->from('payment_logs')
				->where('txn_id','=',$txn_id)
				->where('type','=',$this->_type)
				->where('gateway','=',$this->_gateway)
				->where('interface','=',$interface)
				->where('action','=',$action)
				->where('success','=',TRUE)
			->execute()
			->as_array();
		return (count($res)!==0);
	}
}
