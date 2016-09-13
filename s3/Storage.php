<?php

namespace andreev1024\s3;

use Aws\CloudFront\CloudFrontClient;
use Aws\S3\MultipartUploader;
use Aws\S3\S3Client;
use yii\base\Component;
use yii\base\InvalidConfigException;

/**
 * AWS S3 wrapper class.
 */
class Storage extends Component
{
    const ACL_PRIVATE = 'private';
    const ACL_PUBLIC_READ = 'public-read';
    const ACL_PUBLIC_READ_WRITE = 'public-read-write';
    const ACL_AUTHENTICATED_READ = 'authenticated-read';
    const ACL_BUCKET_OWNER_READ = 'bucket-owner-read';
    const ALC_BUCKET_OWNER_FULL_CONTROL = 'bucket-owner-full-control';

    /**
     * @var \Aws\Credentials\CredentialsInterface|array|callable
     */
    public $credentials;

    /**
     * @var string  Region to connect to. See
     * http://docs.aws.amazon.com/general/latest/gr/rande.html
     * for a list of available regions.
     */
    public $region;

    /**
     * @var string
     */
    public $bucket;

    /**
     * @var string
     */
    public $defaultAcl;

    /**
     * @var bool|array
     */
    public $debug;

    /**
     * @var array
     */
    public $options = [];

    /**
     * @var string The version of the webservice to utilize (e.g., 2006-03-01).
     */
    public $version;

    /**
     * @var array
     */
    public $cloudFrontConfig;

    /**
     * @var S3Client
     */
    private $client;

    /**
     * @var CloudFrontClient
     */
    private $cloudFrontClient;

    /**
     * @author Frostealth <frostealth@gmail.com>
     * @author Andreev <andreev1024@gmail.com>
     * @throws InvalidConfigException
     */
    public function init()
    {
        if (empty($this->credentials)) {
            throw new InvalidConfigException('S3 credentials isn\'t set.');
        }

        if (empty($this->region)) {
            throw new InvalidConfigException('Region isn\'t set.');
        }

        if (empty($this->bucket)) {
            throw new InvalidConfigException('You must set bucket name.');
        }

        $args = $this->prepareArgs($this->options, [
            'version' => $this->version,
            'region' => $this->region,
            'credentials' => $this->credentials,
            'debug' => $this->debug,
        ]);

        $this->client = new S3Client($args);

        if ($this->cloudFrontConfig) {
            $this->cloudFrontClient = new CloudFrontClient([
                'region' => $this->cloudFrontConfig['region'],
                'version' => $this->cloudFrontConfig['version'],
            ]);
        }
    }

    /**
     * @return S3Client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @return CloudFrontClient
     */
    public function getcloudFrontClient()
    {
        return $this->cloudFrontClient;
    }

    /**
     * @param string $filename
     * @param mixed $data
     * @param string $acl
     * @param array $options
     *
     * @return \Aws\ResultInterface
     */
    public function put($filename, $data, $acl = null, array $options = [])
    {
        $args = $this->prepareArgs($options, [
            'Bucket' => $this->bucket,
            'Key' => $filename,
            'Body' => $data,
            'ACL' => !empty($acl) ? $acl : $this->defaultAcl,
        ]);

        return $this->execute('PutObject', $args);
    }

    /**
     * @param string $filename
     * @param string $saveAs
     *
     * @return \Aws\ResultInterface
     */
    public function get($filename, $saveAs = null)
    {
        $args = $this->prepareArgs([
            'Bucket' => $this->bucket,
            'Key' => $filename,
            'SaveAs' => $saveAs,
        ]);

        return $this->execute('GetObject', $args);
    }

    /**
     * @param string $filename
     * @param array $options
     *
     * @return bool
     */
    public function exist($filename, array $options = [])
    {
        return $this->getClient()->doesObjectExist($this->bucket, $filename, $options);
    }

    /**
     * @param string $filename
     *
     * @return \Aws\ResultInterface
     */
    public function delete($filename)
    {
        return $this->execute('DeleteObject', [
            'Bucket' => $this->bucket,
            'Key' => $filename,
        ]);
    }

    /**
     * Return Url for S3 object.
     *
     * @param string $filename
     *
     * @return string
     */
    public function getUrl($filename)
    {
        return $this->getClient()->getObjectUrl($this->bucket, $filename);
    }

    /**
     * Return CloudFront url.
     *
     * @author Andreev <andreev1024@gmail.com>
     * @param string $filename
     *
     * @return string
     */
    public function getCloudFrontUrl($filename)
    {
        return $this->cloudFrontConfig['domainName'] . '/' . trim($filename, '/');
    }

    /**
     * Return CloudFront signed url.
     *
     * @author Andreev <andreev1024@gmail.com>
     * @param $filename
     *
     * @return string
     * @throws InvalidConfigException
     */
    public function getCloudFrontSignedUrl($filename)
    {
        $url = $this->getCloudFrontUrl($filename);
        $arguments = [
            'url',
            'policy',
            'privateKey',
            'keyPairId'
        ];

        foreach ($arguments as $oneArgument) {
            if (!$oneArgument) {
                throw new InvalidConfigException('CloudFront (S3) config invalid.');
            }
        }

        if (!file_exists($this->cloudFrontConfig['privateKey'])) {
            throw new InvalidConfigException('CloudFront privateKey don\'t exist.');
        }

        $config = [
            'url' => $url,
            'policy' => preg_replace('/\s+/', '', $this->cloudFrontConfig['policy']),
            'key_pair_id' => $this->cloudFrontConfig['keyPairId'],
            'private_key' => $this->cloudFrontConfig['privateKey'],
        ];

        $url = $this->getCloudFrontClient()->getSignedUrl($config);

        return $url;
    }

    /**
     * @param string $filename
     * @param string|int|\DateTime $expires
     *
     * @return string
     */
    public function getPresignedUrl($filename, $expires)
    {
        $command = $this->getClient()->getCommand('GetObject', ['Bucket' => $this->bucket, 'Key' => $filename]);
        $request = $this->getClient()->createPresignedRequest($command, $expires);

        return (string) $request->getUri();
    }

    /**
     * @param string $prefix
     * @param array $options
     *
     * @return \Aws\ResultInterface
     */
    public function getList($prefix = null, array $options = [])
    {
        $args = $this->prepareArgs($options, [
            'Bucket' => $this->bucket,
            'Prefix' => $prefix,
        ]);

        return $this->execute('ListObjects', $args);
    }

    /**
     * @param string $filename
     * @param mixed $source
     * @param string $acl
     * @param array $options
     *
     * @return \Aws\ResultInterface
     */
    public function upload($filename, $source, $acl = null, array $options = [])
    {
        return $this->getClient()->upload(
            $this->bucket,
            $filename,
            $source,
            !empty($acl) ? $acl : $this->defaultAcl,
            $options
        );
    }

    /**
     * @param string $filename
     * @param mixed $source
     * @param int $concurrency
     * @param int $partSize
     * @param string $acl
     * @param array $options
     *
     * @return \Aws\ResultInterface
     */
    public function multipartUpload(
        $filename,
        $source,
        $concurrency = null,
        $partSize = null,
        $acl = null,
        array $options = []
    ) {
        $args = $this->prepareArgs($options, [
            'bucket' => $this->bucket,
            'acl' => !empty($acl) ? $acl : $this->defaultAcl,
            'key' => $filename,
            'concurrency' => $concurrency,
            'part-size' => $partSize,
        ]);

        $uploader = new MultipartUploader($this->getClient(), $source, $args);

        return $uploader->upload();
    }

    /**
     * @param string $name
     * @param array $args
     *
     * @return \Aws\ResultInterface
     */
    protected function execute($name, array $args)
    {
        $command = $this->getClient()->getCommand($name, $args);

        return $this->getClient()->execute($command);
    }

    /**
     * @param array $a
     *
     * @return array
     */
    protected function prepareArgs(array $a)
    {
        $result = [];
        $args = func_get_args();

        foreach ($args as $item) {
            $item = array_filter($item);
            $result = array_replace($result, $item);
        }

        return $result;
    }
}