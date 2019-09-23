<?php

// mostly copied from https://docs.aws.amazon.com/AmazonS3/latest/dev/UploadObjSingleOpPHP.html
// But that demo does not show how you use your AWS credentials.
// Also, it is not really clear what is meant by your "key name".
require './aws-autoloader.php';

use Aws\S3\S3Client;;
use Aws\S3\Exception\S3Exception;

// The name of my bucket. To run this demo, you will have to create your own
// S3 bucket and name it.
$bucket = 'full-stack-oasis-test';

// Notice I'm using a profile for uploading this data to an S3 bucket.
// The profile is found in my ~/.aws/credentials file, where it has an
// aws_access_key_id and aws_secret_access_key.
// You will have to create your own, and use your own 'profile' here.
// Another common mistake is using the wrong region or version.
$s3 = new S3Client([
	'profile' => 'FullStackOasisS3User',
    'version' => 'latest',
	'region'  => 'us-east-1'
]);

try {
	// Open the file
	$filename = "cute-kitten.jpg";
	$file = fopen($filename, "rb");
	// read the file as a string
	$contents = fread($file, filesize($filename));
	// close the file.
	fclose($file);
	
	// Upload our data. Notice that I'm using a file name with special
	// characters. The "Key" is basically a file name, as demonstrated
	// here.
    $result = $s3->putObject([
        'Bucket' => $bucket,
        'Key'    => 'cüte-kitten-(2).jpg',
        'Body'   => $contents,
        'ACL'    => 'public-read'
    ]);
	
    // Print the URL for this object.
	echo 'URL is : ' . $result['ObjectURL'] . PHP_EOL;
	// Output is "URL is : https://full-stack-oasis-textract.s3.amazonaws.com/c%C3%BCte-kitten-%282%29.jpg"
} catch (S3Exception $e) {
    echo $e->getMessage() . PHP_EOL;
}