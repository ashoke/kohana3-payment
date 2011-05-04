<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Builds html code for paypal operations-buttons
 * 
 * @link  https://cms.paypal.com/us/cgi-bin/?cmd=_render-content&content_ID=developer/howto_html_wp_standard_overview *
 * 
 * @author Alexey Geno <alexeygeno@gmail.com>
 * @package Payment
 */
class Payment_Buttons_Paypal extends Payment_PayPal {

	/**
	 * Gets html code for bye now button
	 * @link https://cms.paypal.com/us/cgi-bin/?cmd=_render-content&content_ID=developer/e_howto_html_Appx_websitestandard_htmlvariables#id08A6HF080O3
	 * @param array $params HTML variables array(see link for possible values)
	 * @return string
	 */
	public function buynow($params = array())
	{
		$params += array('cmd'=>'_s-xclick');
		$data = array(
			'url' => $this->webscr_url(),
			'params' => $params
		);
		return View::factory('payment/buttons/paypal/buynow',$data)->render();
	}

	/**
	 * Gets html code for subscribe button
	 * @link https://cms.paypal.com/us/cgi-bin/?cmd=_render-content&content_ID=developer/e_howto_html_Appx_websitestandard_htmlvariables#id08A6HI00JQU
	 * @param array $params HTML variables array(see link for possible values)
	 * @return string
	 */
	public function subscribe($params = array())
	{
		$params += array('cmd'=>'_s-xclick');
		$data = array(
					'url' => $this->webscr_url(),
					'params' => $params
		);
		return View::factory('payment/buttons/paypal/subscribe',$data)->render();
	}

	/**
	 * Gets html code for unsubscribe button
	 * @return string
	 */
	public function unsubscribe()
	{
		$data = array(
					'url' => $this->webscr_url(),
					'merchant_id' => $this->_merchant_id
		);
		return View::factory('payment/buttons/paypal/unsubscribe',$data)->render();
	}

}