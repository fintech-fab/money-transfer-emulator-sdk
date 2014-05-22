Money Transfer Emulator SDK
===============

SDK for Money Transfer Emulator (https://github.com/fintech-fab/money-transfer-emulator)

# Requirements

- php >=5.3.0
- php5-curl

# Installation (composer)

    {
        "require": {
            "fintech-fab/money-transfer-emulator-sdk": "dev-master",
        },
    }

# Simple usage

```PHP
use FintechFab\MoneyTransferEmulatorSdk\Gateway;

$config = array(
	'terminalId'    => 'your-terminal-id',
	'secretKey'     => 'your-terminal-secret-key',
	'gatewayUrl'    => 'url-to-gateway',
	'currency'      => 'RUB',
	'strongSSL'     => false,
);

// city list

$gatewayCity = Gateway::newInstance($config);
$cityList = $gatewayCity->getCityList();

// fee amount for target city

$gatewayFee = Gateway::newInstance($config);
$feeAmount = $gatewayFee->getFeeValue($cityList[0]->id, 10.00);

// Start with payment 'check' and 'pay'

$gatewayPay = Gateway::newInstance($config);

$params = array(
	'cityId'      => $cityList[0]->id,
	'toName'      => 'Happy Man',
	'orderAmount' => '10.00',
	'toNumber'    => '791032123123',
	'fromNumber'  => '3806865456467',
);

$resultCheck = $gatewayPay->check($params);

if($resultCheck){

	$resultPay = $gatewayPay->pay($params);

	if($resultPay){

		// get status

		$gatewayStatus = Gateway::newInstance($config);
		$gatewayStatus->status($gatewayPay->getResultCode(), $params['toNumber']);

		// do cancel

		$gatewayCancel = Gateway::newInstance($config);
		$gatewayCancel->cancel($gatewayPay->getResultCode(), $params['toNumber']);

	}

}
```
