## how-to use the Amazon AWS SDK for PHP 3.x for Comprehend ##

The idea is to test out the ``Comprehend`` API of the SDK.

Instructions for getting started with the SDK for PHP are here:

https://docs.aws.amazon.com/sdk-for-php/v3/developer-guide/getting-started_installation.html

First, download the .zip file as described in the above link.

Extract the zip file to the root of your project. That adds a lot of files and folders to the project root, for example "Aws" folder is added.

### DetectSentiment API ###

This is the DetectSentiment API:

https://docs.aws.amazon.com/aws-sdk-php/v3/api/api-comprehend-2017-11-27.html#detectsentiment

Set up the text as "I'm very happy with Amazon's AWS Comprehend SDK! Good work!"

We need to set up a "client", which can be used to generate results.

https://docs.aws.amazon.com/aws-sdk-php/v3/api/class-Aws.Comprehend.ComprehendClient.html

See trial1.php for sample code in setting up your client.

### Credentials ###

The client will not do anything without credentials. You cannot be an anonymous user. I am already an Amazon AWS user, so I will use that sign-in to create my credentials.

https://docs.aws.amazon.com/sdk-for-php/v3/developer-guide/guide_credentials_profiles.html

The way to create a new "user" with limited access to sign in is described here:
https://console.aws.amazon.com/iam/home#/

Sign in. Then click your user name, go to My Security Credentials, and follow the steps (add user, add group, add user to group etc).

https://docs.aws.amazon.com/IAM/latest/UserGuide/id_credentials_access-keys.html?icmpid=docs_iam_console#Using_CreateAccessKey

I created an IAM user with a test name, ATestComprehendUser. https://console.aws.amazon.com/iam/home#/users$new?step=details
This user only has "programmatic" access.

I create a group, it has ComprehendFullAccess.

Group is called ATestComprehendFullAccess

Now in the root of my project, run:

```
aws configure
```

It asks you for the access key id and secret access key. These are found in your console, above. This creates ~/.aws/credentials/ and now you are good to go.

### Now run the code ###

Run ``php test_comprehend.php`` and see this output:

```
Your feedback string is "I'm very happy with Amazon's AWS Comprehend SDK! Good work!"
The feedback was POSITIVE.
```

It just works, nice!
