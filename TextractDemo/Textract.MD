## How to use the Amazon AWS SDK for Textract with PHP 7.2 ##

The <a href="https://docs.aws.amazon.com/textract/latest/dg/what-is.html">Amazon AWS Textract</a> API lets you do OCR (optical character recognition) on digital files. It's actually pretty easy to use, although there's some prep work.

This post has instructions for using the Textract API using their **PHP SDK**. I'm using PHP version 7.2 on an Ubuntu 16.2 operating system. This demo works as of September 2019.

## Step 1: Create the project ##

Create a folder for your project, for example ``mkdir ~/TextractDemo ; cd ~/TextractDemo``. 

<a href="https://docs.aws.amazon.com/sdk-for-php/v3/developer-guide/getting-started_installation.html">Instructions for getting started with the SDK for PHP are here</a>. First, download the .zip file as described on that page. Then, extract the zip file to the root of your project. That adds a lot of files and folders to the project root, for example "Aws" folder is added.

This is what you should see when listing the contents of this directory:

```
~/TextractDemo$ ls -lairt
total 676
  396747 -rw-r--r--   1 fullstackdev fullstackdev  10129 Sep 12 14:11 README.md
  531373 drwxr-xr-x   3 fullstackdev fullstackdev   4096 Sep 12 14:11 Psr
  396739 -rw-r--r--   1 fullstackdev fullstackdev   2881 Sep 12 14:11 NOTICE.md
  399132 -rw-r--r--   1 fullstackdev fullstackdev   9202 Sep 12 14:11 LICENSE.md
  926072 drwxr-xr-x   2 fullstackdev fullstackdev   4096 Sep 12 14:11 JmesPath
  396755 drwxr-xr-x   7 fullstackdev fullstackdev   4096 Sep 12 14:11 GuzzleHttp
  399129 -rw-r--r--   1 fullstackdev fullstackdev 478403 Sep 12 14:11 CHANGELOG.md
  396748 -rw-r--r--   1 fullstackdev fullstackdev 132879 Sep 12 14:11 aws-autoloader.php
  531270 drwxr-xr-x 203 fullstackdev fullstackdev  12288 Sep 12 14:11 Aws
  396729 drwxr-xr-x   6 fullstackdev fullstackdev   4096 Sep 15 09:48 .
13500418 drwxr-xr-x  46 fullstackdev fullstackdev  20480 Sep 15 09:49 ..

```

## Create an IAM User ##

In order to use the Textract API, you need an <a href="https://aws.amazon.com/premiumsupport/knowledge-center/create-and-activate-aws-account/">Amazon AWS account</a>.

Assuming you've got an AWS account, next, you need to <a href="https://docs.aws.amazon.com/textract/latest/dg/setting-up.html">create an IAM (Identity and Access Management) user</a>. If you are signed in to your AWS console, just search for "Identity and Access Management", and it takes you to the right place to create an IAM user. There's an area called "Create individual IAM users". Go there, click the "Manage Users" button, click the "Add User" button, choose a name like TextractUser, and give it programmatic access only. Once you've created the name, go to the next step, where you can add the user to a specific group. Create a group which has the AmazonTextractFullAccess policy name. Name it something like TextractFullAccessGroup, and save that. Add the user you just created to this group. The next step lets you add tags to the user, but you can leave that blank.

In the Review (last) step, you are given the user's access key ID and secret key (which is hidden - you will have to unhide it to copy it). Save these in a secure place! As the documentation says, "This is the last time these credentials will be available to download. However, you can create new credentials at any time." (So if you lose them somehow, you can always generate a new set.)

The credentials that you just created may be saved in the file ~/.aws/credentials in Linux.

If this file already exists, you can add to it. <a href="https://docs.aws.amazon.com/cli/latest/userguide/cli-configure-profiles.html">Here's the documentation for adding lines to an AWS credentials file.</a> On that page, it gives you an example credentials file with this content:

```
[default]
aws_access_key_id=AKIAIOSFODNN7EXAMPLE
aws_secret_access_key=wJalrXUtnFEMI/K7MDENG/bPxRfiCYEXAMPLEKEY

[user1]
aws_access_key_id=AKIAI44QH8DHBEXAMPLE
aws_secret_access_key=je7MtGbClwBF/2Zp9Utk/h3yCo8nvbEXAMPLEKEY
```

Instead of user1, add the line ``[TextractUser]`` (or whatever user name you used in the "creating user" step above). Copy and paste your access key id and secret key as shown.

The credentials file is normally created when installing the AWS CLI. So if you do not already have a credentials file, <a href="https://docs.aws.amazon.com/cli/latest/userguide/cli-chap-install.html">install the CLI</a> first. Then you can add users to the file.

Now we're ready to use Textract. Let's try to detect text in a sample "document" - the image file shown below. If you are following along, you can right click and save this image, or you can try it on one of your own image files (i.e. one that contains text!).

## Call Textract using the SDK ##

You *can* have Textract analyze images that are in an S3 bucket. However, for demo purposes, that is overkill! It is simpler and quicker to read in an image file as bytes, and send that to Textract for analysis. That's what we will do.

The source code only needs to do three things. First, it needs to create a Textract client. Second, it needs to read in the image file as bytes. Third, the client needs to call the Textract API. Here's the demo code:

```
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
$result = $client->analyzeDocument($options);
// If debugging:
// echo print_r($result, true);
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
?>
```

You'll need to edit this source code to use your own AWS credentials. Once you do that, you should be able to run the code and view the output, as shown here:

```
php textract_demo.php 
Line: The AWS CLI is updated frequently with support for new services and commands.
Word: The
Word: AWS
Word: CLI
...
```

That's it! Feel free to email me with any questions. If you found this interesting, hit the subscribe button above. Got comments? Send me an email at fullstackdev@fullstackoasis.com. I post new content just about every week.

References:

[1] <a href="https://stackoverflow.com/questions/49806405/cannot-read-credentials-from-aws-credentials-php-script-call-aws-sdk">Stackoverflow question about AWS Credentials</a>