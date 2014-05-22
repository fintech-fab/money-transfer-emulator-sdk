<?php

namespace FintechFab\MoneyTransferEmulatorSdk\Test;


use FintechFab\MoneyTransferEmulatorSdk\Gateway;


/**
 *
 * if your wont run this tests,
 * set your configuration into config property
 * and comment 'markTestSkipped' into setUp
 *
 * Class GatewayTest
 *
 * @package FintechFab\MoneyTransferEmulatorSdk\Test
 */
class GatewayTest extends \PHPUnit_Framework_TestCase
{

	/**
	 * @var Gateway
	 */
	private $gateway = null;
	private $config = array(
		'terminalId' => '1',
		'secretKey'  => '561ae1c70633b26831d9f75f9c90a0d9',
		'gatewayUrl' => 'http://fintech-fab.dev/mt/emulator/demo/gateway',
		'currency'   => 'RUB',
		'strongSSL'  => false,
	);

	public function setUp()
	{
		parent::setUp();
		$this->markTestSkipped();
	}


	public function testCity()
	{
		$this->makeGateway();
		$cityList = $this->gateway->getCityList();
		$this->assertNotEmpty($cityList);
		$this->assertNotEmpty($cityList[0]->id);
		$this->assertNotEmpty($cityList[0]->name);
		$this->assertNotEmpty($cityList[0]->country);

	}

	public function testFee()
	{
		$this->makeGateway();
		$value = $this->gateway->getFeeValue(1, 10.00);
		$this->assertNotEmpty($value);

	}


	public function testFail()
	{
		$this->makeGateway();
		$params = array(
			'cityId'      => 1,
			'toName'      => 'Happy Man',
			'orderAmount' => '0.00',
			'toNumber'    => '791032123123',
			'fromNumber'  => '3806865456467',
		);
		$result = $this->gateway->check($params);
		$this->assertFalse($result, $this->gateway->getError());
		$this->assertEquals('error', $this->gateway->getResultType());
		$this->assertContains('amount', $this->gateway->getResultMessage());

	}

	public function testCorrect()
	{
		// check

		$this->makeGateway();
		$params = array(
			'cityId'      => 1,
			'toName'      => 'Happy Man',
			'orderAmount' => sprintf("%01.2f", mt_rand(100, 99999) / 100),
			'toNumber'    => '791032123123',
			'fromNumber'  => '3806865456467',
		);
		$result = $this->gateway->check($params);
		$this->assertTrue($result, $this->gateway->getError());
		$this->assertEquals('enabled', $this->gateway->getResultStatus());

		// pay

		$result = $this->gateway->pay($params);
		$this->assertTrue($result, $this->gateway->getError());
		$this->assertEquals('processed', $this->gateway->getResultStatus());

		// status

		$result = $this->gateway->status($this->gateway->getResultCode(), $params['toNumber']);
		$this->assertTrue($result, $this->gateway->getError());
		$this->assertEquals('processed', $this->gateway->getResultStatus());

		// cancel

		$result = $this->gateway->cancel($this->gateway->getResultCode(), $params['toNumber']);
		$this->assertTrue($result, $this->gateway->getError());
		$this->assertEquals('processed', $this->gateway->getResultStatus());

		// status

		$result = $this->gateway->status($this->gateway->getResultCode(), $params['toNumber']);
		$this->assertTrue($result, $this->gateway->getError());
		$this->assertEquals('canceled', $this->gateway->getResultStatus());

	}

	private function makeGateway()
	{
		$this->gateway = Gateway::newInstance($this->config);
	}


}


