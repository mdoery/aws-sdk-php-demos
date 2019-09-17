<?php
require './aws-autoloader.php';

use Aws\Translate\TranslateClient;
use Aws\Exception\AwsException;

$client = new Aws\Translate\TranslateClient([
	'profile' => 'TranslateUser',
	'region' => 'us-west-2',
	'version' => 'latest'
]);

// Translate from English (en) to Spanish (es).
$currentLanguage = 'en';
$targetLanguage= 'es';
$textToTranslate = "Call me Ishmael. Some years ago—never mind how long precisely—having little or no money in my purse, and nothing particular to interest me on shore, I thought I would sail about a little and see the watery part of the world.";

echo "Calling translateText function on '".$textToTranslate."'\n";

try {
	$result = $client->translateText([
		'SourceLanguageCode' => $currentLanguage,
		'TargetLanguageCode' => $targetLanguage,
		'Text' => $textToTranslate,
	]);
	echo $result['TranslatedText']."\n";
} catch(AwsException $e) {
	// output error message if fails
	echo "Failed: ".$e->getMessage()."\n";
}