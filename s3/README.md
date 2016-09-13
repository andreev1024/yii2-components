# Yii2 AWS S3

An Amazon S3Client wrapper as Yii2 component.

The component currently supports CloudFront (getting a Cloudfront simple and signed url).

## Configuration

Add component to `config/main.php`

    'components' => [
        // ...
        's3bucket' => [
            'class' => \andreev1024\s3::className(),
            'region' => 'your region',
            'credentials' => [ // Aws\Credentials\CredentialsInterface|array|callable
                'key' => 'your aws s3 key',
                'secret' => 'your aws s3 secret',
            ],
            'bucket' => 'your aws s3 bucket',
            'domainName' => 'http://example.cloudfront.net',
            'defaultAcl' => \andreev1024\s3::ACL_PUBLIC_READ,
            'debug' => true, // bool|array
            'cloudFrontConfig' => [                 
                  'region' => 'ap-northeast-1',
                  'version' => '2015-04-17',
                  'domainName' => 'http://abcdef.cloudfront.net',
                  'privateKey' => __DIR__ . '/private-key.pem',
                  'keyPairId' => 'qwerty123456789',
                  'policy' =>
                      '{
                     "Statement": [
                        {
                           "Resource":"http://abcdef.cloudfront.net/contents/*",
                           "Condition":{
                              "DateLessThan":{"AWS:EpochTime":' . strtotime("+1 year") . '}
                           }
                        }
                     ]
                  }',
              ],
            ],
        // ...
    ],

## Usage

### Uploading objects

    // creating an object
    $data = ['one', 'two', 'three'];
    Yii::$app->get('s3bucket')->put('path/to/s3object.ext', Json::encode($data));
    
    // uploading an object by streaming the contents of a stream
    $resource = fopen('/path/to/local/file.ext', 'r+');
    Yii::$app->get('s3bucket')->put('path/to/s3object.ext', $resource);

### Uploading files

    Yii::$app->get('s3bucket')->upload('path/to/s3object.ext', '/path/to/local/file.ext');

### Uploading large files using multipart uploads with custom options

    $concurrency = 5;
    $minPartSize = 536870912; // 512 MB
    
    Yii::$app->get('s3bucket')->multipartUpload(
        'path/to/s3object.ext',
        '/path/to/local/file.ext',
        $concurrency,
        $minPartSize
    );

### Reading objects

    /** @var \Aws\Result $result */
    $result = Yii::$app->get('s3bucket')->get('path/to/s3object.ext');
    $data = $result['Body'];


### Saving objects to a file

    Yii::$app->get('s3bucket')->get('path/to/s3object.ext', '/path/to/local/file.ext');


### Deleting objects

    Yii::$app->get('s3bucket')->delete('path/to/s3object.ext');


### Getting a plain URL
    
    $url = Yii::$app->get('s3bucket')->getUrl('path/to/s3object.ext');
    

### Creating a pre-signed URL
    php
    $url = Yii::$app->get('s3bucket')->getPresignedUrl('path/to/s3object.ext', '+10 minutes');
    

### Getting a CloudFront URL 
    php
    $url = Yii::$app->get('s3bucket')->getCloudFrontUrl('path/to/s3object.ext');
    

### Getting a CloudFront Signed URL 
    php
    $url = Yii::$app->get('s3bucket')->getCloudFrontSignedUrl('path/to/s3object.ext');
    

### Listing objects
    php
    $result = Yii::$app->get('s3bucket')->getList('path/');
    foreach ($result['Contents'] as $object) {
        echo $object['Key'] . PHP_EOL;
    }
    