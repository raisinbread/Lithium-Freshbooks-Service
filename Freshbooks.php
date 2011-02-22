<?php

namespace app\extensions\service;

use lithium\data\Connections;

/**
* An HTTP Service extension for accessing the Freshbooks API
*
* @package default
* @author John Anderson
*/
class Freshbooks extends \lithium\net\http\Service {

	/**
	* Connection details for this service.
	*
	* @var string
	*/
protected $_connection = array();

/**
* Constructor. Expects a connection configured like the following:
* 
* Connections::add('freshbooks', array(
* 	'host' => 'yourusername.freshbooks.com',
* 	'token' => 'tokengoeshere',
* );
*
* @param array $config 
* @author John Anderson
*/
public function __construct(array $config = array()) {

	$this->_connection = Connections::get('freshbooks', array('config' => true));
	$defaults = array(
		'scheme'     => 'https',
		'host'       => trim($this->_connection['host']),
		'socket'     => 'Curl',
		'username'   => trim($this->_connection['token']),
		'password'   => 'X',
		'encoding'   => 'UTF-8'
		);
	parent::__construct($config + $defaults);
}

public function connection($options) {
	$conn = parent::connection($options);
	$conn->set(CURLOPT_USERPWD, $this->_connection['token'] . ":X");
	return $conn;
}

public function send($method, $path = null, $data = array(), array $options = array()) {
	$defaults = array(
		'headers' => array(
			'User-Agent'    => 'Lava Surfboard'
		)
	);
	$xml = new \SimpleXmlElement('<request method="' . $path . '"></request>');
	foreach($data as $key => $val) {
		if($val != null) {
			$xml->$key = $val;
		}
	}
	$xmlString = $xml->asXML();
	$data = str_replace("<?xml version=\"1.0\"?>\n", '<!--?xml version="1.0" encoding="utf-8"?-->', $xmlString);
	$path = '/api/2.1/xml-in';
	
	$response = parent::send($method, $path, $data, $options + $defaults);
	
	return new \SimpleXmlElement($response);
}

/**
* Returns a list of invoice summaries. Results are ordered by descending invoice_id.
*
* @param array $options 
* @return void
* @author John Anderson
* @see http://developers.freshbooks.com/api/view/invoices/#invoice.list
*/
public function invoiceList($options) {
	$defaults = array(
		'client_id' => null,
		'recurring_id' => null,
		'status' => null, //disputed, draft, sent, viewed, paid, auto-paid, retry, failed, unpaid
		'date_from' => null,
		'date_to' => null,
		'updated_from' => null,
		'updated_to' => null,
		'page' => 1,
		'per_page' => 100,
		'folder' => null,
		);
		
	return $this->post('invoice.list', $options + $defaults);
}

/**
* Returns a list of client summaries in order of descending client_id.
*
* @return void
* @author John Anderson
* @see http://developers.freshbooks.com/api/view/clients/#client.list
*/
public function clientList($options) {
	$defaults = array(
		'email' => null,
		'username' => null,
		'updated_from' => null,
		'updated_to' => null,
		'page' => 1,
		'per_page' => 100,
		'folder' => null,
		'notes' => null,
		);

	return $this->post('client.list', $options + $defaults);
}

}