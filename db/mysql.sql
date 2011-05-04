CREATE TABLE `payment_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` enum('notifier','requester') NOT NULL COMMENT '''notifier'' - gateway sends data to  app(to defined controller) for notify about some actions, that has been palece at gateway. \n''requester'' - data sends from app to gateway about some action as request and gateway return response as result of that request',
  `gateway` enum('paypal','linkpoint') NOT NULL COMMENT 'One of supported Gateways',
  `interface` varchar(500) NOT NULL COMMENT 'used interface base name',
  `action` varchar(500) NOT NULL COMMENT 'used action of interface',
  `success` tinyint(1) NOT NULL COMMENT 'If operation has success or not',
  `txn_id` varchar(500) DEFAULT NULL COMMENT 'gateway request order id',
  `date` int(11) DEFAULT NULL COMMENT 'gateway request date in timestamp format',
  `custom` varchar(500) DEFAULT NULL COMMENT 'Gateway custom field, usually it is id of app-user',
  `sent_data` text COMMENT 'serialized string of sent to gateway data. For ipn type should be empty',
  `recieved_data` text NOT NULL COMMENT 'serialized string of recieved from gateway data',
  `created_date` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
