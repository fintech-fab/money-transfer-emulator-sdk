<?php
namespace FintechFab\MoneyTransferEmulatorSdk;

use Exception;

class Gateway
{

	const C_ERROR_HTTP = 'http';
	const C_ERROR_GATEWAY = 'gateway';
	const C_ERROR_PROCESSING = 'processing';
	const C_ERROR_PARAMS = 'parameters';


	private $error = array();
	private $response;
	private $rawResponse;


	private $configParams = array(
		'terminalId' => '',
		'secretKey'  => '',
		'gatewayUrl' => '',
		'currency'   => '',
		'strongSSL'  => true,
	);

	private static $customParams = array(
		'city'   => array(),
		'fee'    => array(
			'cityId',
			'orderAmount',
		),
		'check'  => array(
			'fromNumber',
			'toNumber',
			'cityId',
			'orderAmount',
			'toName',
			'fromEmail',
		),
		'pay'    => array(
			'fromNumber',
			'toNumber',
			'cityId',
			'orderAmount',
			'toName',
			'fromEmail',
		),
		'cancel' => array(
			'orderCode',
			'toNumber',
		),
		'status' => array(
			'orderCode',
			'toNumber',
		),
	);

	private static $convertRequestParams = array(
		'cityId'      => 'city',
		'terminalId'  => 'term',
		'fromEmail'   => 'email',
		'currency'    => 'cur',
		'orderAmount' => 'amount',
		'toName'      => 'name',
		'toNumber'    => 'to',
		'fromNumber'  => 'from',
		'orderCode'   => 'code',
		'sign'        => 'sign',
	);


	/**
	 * Get City list from gateway
	 *
	 * @return boolean
	 */
	public function city()
	{
		$requestParams = $this->initRequestParams('city');
		$this->request('city', $requestParams);

		return empty($this->error);
	}

	/**
	 * Get simple city list
	 *
	 * @return boolean
	 */
	public function getCityList()
	{

		$result = $this->city();
		if (!$result) {
			return null;
		}

		return $this->getResultCityList();

	}

	public function getResultCityList()
	{
		return !empty($this->response->list)
			? $this->response->list
			: array();
	}

	/**
	 * Get fee result by city and amount
	 *
	 * @param integer $cityId
	 * @param float   $orderAmount
	 *
	 * @return boolean
	 */
	public function fee($cityId, $orderAmount)
	{
		$params = compact('cityId', 'orderAmount');
		$requestParams = $this->initRequestParams('fee', $params);
		$this->request('fee', $requestParams);

		return empty($this->error);
	}

	/**
	 * Get fee value by city and amount
	 *
	 * @param integer $cityId
	 * @param float   $orderAmount
	 *
	 * @return boolean
	 */
	public function getFeeValue($cityId, $orderAmount)
	{

		$result = $this->fee($cityId, $orderAmount);
		if (!$result) {
			dd($this->getResultRaw());

			return null;
		}

		return $this->getResultFeeValue();

	}

	public function getResultFeeValue()
	{
		return !empty($this->response->value)
			? $this->response->value
			: array();
	}

	/**
	 * Check payment
	 *
	 * @param array $params
	 *
	 * @return boolean
	 */
	public function check($params)
	{
		$requestParams = $this->initRequestParams('check', $params);
		$this->request('check', $requestParams);

		return empty($this->error);
	}

	/**
	 * Pay payment
	 *
	 * @param array $params
	 *
	 * @return boolean
	 */
	public function pay($params)
	{
		$requestParams = $this->initRequestParams('pay', $params);
		$this->request('pay', $requestParams);

		return empty($this->error);
	}

	/**
	 * Get payment status
	 *
	 * @param string $orderCode
	 * @param string $toNumber
	 *
	 * @return boolean
	 */
	public function status($orderCode, $toNumber)
	{
		$params = compact('orderCode', 'toNumber');
		$requestParams = $this->initRequestParams('status', $params);
		$this->request('status', $requestParams);

		return empty($this->error);
	}

	/**
	 * Cancel for payment in status 'processed'
	 *
	 * @param string $orderCode
	 * @param string $toNumber
	 *
	 * @return boolean
	 */
	public function cancel($orderCode, $toNumber)
	{
		$params = compact('orderCode', 'toNumber');
		$requestParams = $this->initRequestParams('cancel', $params);
		$this->request('cancel', $requestParams);

		return empty($this->error);
	}

	/**
	 * @return string|null
	 */
	public function getError()
	{
		return (!empty($this->error['message']))
			? $this->error['message']
			: null;
	}

	/**
	 * @return string|null
	 */
	public function getErrorType()
	{
		return (!empty($this->error['type']))
			? $this->error['type']
			: null;
	}

	/**
	 * @return string|null
	 */
	public function getResultTerminalId()
	{
		return (!empty($this->response->term))
			? $this->response->term
			: null;
	}

	/**
	 * @return string|null
	 */
	public function getResultAmount()
	{
		return (!empty($this->response->amount))
			? $this->response->amount
			: null;
	}

	/**
	 * @return string|null
	 */
	public function getResultCode()
	{
		return (!empty($this->response->code))
			? $this->response->code
			: null;
	}

	/**
	 * @return string|null
	 */
	public function getResultMessage()
	{
		return (!empty($this->response->message))
			? $this->response->message
			: null;
	}

	/**
	 * @return string|null
	 */
	public function getResultStatus()
	{
		return (!empty($this->response->status))
			? $this->response->status
			: null;
	}

	/**
	 * @return string|null
	 */
	public function getResultType()
	{
		return (!empty($this->response->type))
			? $this->response->type
			: null;
	}

	/**
	 * @return string|null
	 */
	public function getResultRaw()
	{
		return (!empty($this->rawResponse))
			? $this->rawResponse
			: null;
	}


	/**
	 * @param array $config
	 *
	 * @throws Exception
	 * @return Gateway
	 */
	public static function newInstance($config)
	{

		if (!function_exists('curl_init')) {
			throw new Exception('Curl required');
		}

		$gateway = new self();
		$gateway->setConfig($config);

		return $gateway;

	}

	/**
	 * Set config parameters
	 *
	 * @param $config
	 */
	private function setConfig($config)
	{
		foreach ($config as $key => $value) {
			$this->setConfigParam($key, $value);
		}

	}

	/**
	 * @param string $name
	 * @param string $value
	 *
	 * @throws GatewayException
	 */
	public function setConfigParam($name, $value)
	{

		if (!isset($this->configParams[$name])) {
			throw new GatewayException('User undefined config param [' . $name . ']');
		}

		$this->configParams[$name] = $value;

	}


	/**
	 * Generate request signature
	 *
	 * @param string $type
	 * @param array  $params
	 *
	 * @return string
	 */
	private function sign($type, &$params)
	{

		ksort($params);
		$str4sign = implode('|', $params);
		$sign = md5($str4sign . $type . $this->configParams['secretKey']);

		return $sign;

	}

	/**
	 * Generate params for http query
	 *
	 * @param string $type
	 * @param array  $params
	 *
	 * @return array
	 * @throws GatewayException
	 */
	private function initRequestParams($type, $params = array())
	{

		$list = self::$customParams;
		if (!isset($list[$type])) {
			throw new GatewayException('Undefined request type');
		}

		$list = $list[$type];
		$requestParams = array();

		foreach ($list as $key) {
			if (!empty($params[$key])) {
				$requestParams[$key] = trim($params[$key]);
			}
		}

		foreach ($this->configParams as $key => $value) {
			if (
				in_array($type, array('cancel', 'status', 'city')) &&
				$key == 'currency'
			) {
				continue;
			}
			$requestParams[$key] = $value;
		}

		if (!empty($requestParams['orderAmount'])) {
			$requestParams['orderAmount'] = sprintf("%01.2f", $requestParams['orderAmount']);
		}

		$requestParams = $this->convert($requestParams);

		$requestParams['time'] = time();
		$requestParams['sign'] = $this->sign($type, $requestParams);

		return $requestParams;

	}

	/**
	 * Reverse gateway and human names
	 *
	 * @param array $requestParams
	 *
	 * @return array
	 */
	private function convert($requestParams)
	{

		$convertedParams = array();
		$convertList = self::$convertRequestParams;
		if (isset($convertList['term'])) {
			$convertList = array_flip($convertList);
		}

		foreach ($requestParams as $key => $value) {
			if (isset($convertList[$key])) {
				$convertedParams[$convertList[$key]] = $value;
			}
		}

		return $convertedParams;

	}


	/**
	 * Executing http request
	 *
	 * @param string $type
	 * @param array  $requestParams
	 */
	private function request($type, $requestParams)
	{
		$this->cleanup();

		$curl = new Curl();
		$curl->setCheckCertificates($this->configParams['strongSSL']);
		$curl->post($this->configParams['gatewayUrl'], array(
			'type'  => $type,
			'input' => $requestParams,
		));

		$this->parseErrors($curl);

		if (!$this->error || $this->error['type'] == self::C_ERROR_PROCESSING) {
			$this->response = json_decode($curl->result);
		}

	}

	/**
	 * Parse error type
	 *
	 * @param Curl $curl
	 */
	private function parseErrors(Curl $curl)
	{

		$this->rawResponse = $curl->result;

		if ($curl->error) {
			$this->error = array(
				'type'    => self::C_ERROR_HTTP,
				'message' => 'Curl error: ' . $curl->error,
			);

			return;
		}

		if ($curl->code != '200') {
			$this->error = array(
				'type'    => self::C_ERROR_HTTP,
				'message' => 'Response code: ' . $curl->code,
			);

			return;
		}

		if (empty($curl->result)) {
			$this->error = array(
				'type'    => self::C_ERROR_GATEWAY,
				'message' => 'Empty Response',
			);

			return;
		}

		$response = @json_decode($curl->result);
		if (!$response || empty($response->type)) {
			$this->error = array(
				'type'    => self::C_ERROR_GATEWAY,
				'message' => 'Response is not json or Unrecognized response format',
			);

			return;
		}

		if ($response->type == 'error') {
			$this->error = array(
				'type'    => self::C_ERROR_PROCESSING,
				'message' => $response->message,
			);

			return;
		}

	}

	/**
	 * Clear request/response data
	 */
	private function cleanup()
	{
		$this->response = '';
		$this->rawResponse = '';
		$this->error = array();
	}

}