<?php
/*
Copyright 2019 Marya Doery

MIT License https://opensource.org/licenses/MIT

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/

/*
 * To run this project, make sure that the AWS PHP SDK has been unzipped in the current directory.
 * 
 * Caution: this is not production quality code. There are no tests, and there is no error handling.
 */
require './aws-autoloader.php';

use Aws\Credentials\CredentialProvider;
use Aws\Textract\TextractClient;

// If you use CredentialProvider, it will use credentials in your .aws/credentials file.
/*
$provider = CredentialProvider::env();
$client = new TextractClient([
	'profile' => 'TextractUser',
    'region' => 'us-west-2',
	'version' => '2018-06-27',
	'credentials' => $provider
]);
*/
$client = new TextractClient([
    'region' => 'us-west-2',
	'version' => '2018-06-27',
	'credentials' => [
        'key'    => 'AKIAI44QH8DHBEXAMPLE',
        'secret' => 'je7MtGbClwBF/2Zp9Utk/h3yCo8nvbEXAMPLEKEY'
	]
]);

// The file in this project.
$filename = "aws_cli_text_document.jpg";
$file = fopen($filename, "rb");
$contents = fread($file, filesize($filename));
fclose($file);
$options = [
    'Document' => [
		'Bytes' => $contents
    ],
    'FeatureTypes' => ['FORMS'], // REQUIRED
];
$promise = $client->analyzeDocumentAsync($options);
$promise->then(
    // $onFulfilled
    function ($value) {
		echo 'The promise was fulfilled.';
		processResult($value);
    },
    // $onRejected
    function ($reason) {
        echo 'The promise was rejected.';
    }
);

// If debugging:
// echo print_r($result, true);
function processResult($result) {
	$blocks = $result['Blocks'];
	// Loop through all the blocks:
	foreach ($blocks as $key => $value) {
		if (isset($value['BlockType']) && $value['BlockType']) {
			$blockType = $value['BlockType'];
			if (isset($value['Text']) && $value['Text']) {
				$text = $value['Text'];
				if ($blockType == 'WORD') {
					echo "Word: ". print_r($text, true) . "\n";
				} else if ($blockType == 'LINE') {
					echo "Line: ". print_r($text, true) . "\n";
				}
			}
		}
	}
}
?>
