<?php
require './aws-autoloader.php';
//require './aws_sdk_php/Aws/AwsClient.php'; 

use Aws\Credentials\CredentialProvider;
use Aws\Comprehend\ComprehendClient;
$provider = CredentialProvider::env();
$client = new ComprehendClient([
    'profile' => 'default',
    'region' => 'us-west-2',
	'version' => '2017-11-27',
	'credentials' => $provider
]);
$options = [
	'LanguageCode' => 'en',
	'Text' => "I'm very happy with Amazon's AWS Comprehend SDK! Good work!",
];
$result = $client->detectSentiment($options);
// If debugging:
// echo print_r($result, true);
$sentiment = $result['Sentiment'];
echo 'Your feedback string is "'.$options['Text'].'"'.PHP_EOL;
echo 'The feedback was '.$sentiment.'.'.PHP_EOL;
?>