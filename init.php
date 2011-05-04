<?php defined('SYSPATH') or die('No direct script access.');

//set up payment controller
Route::set('payment', 'payment/<controller>/<action>(/<gateway>)(/<id>)')
	->defaults(array(
		'directory'  =>'payment',
		'controller' => 'test',
		'action'	 => 'buttons',
	));