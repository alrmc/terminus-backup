<?php

require_once "vendor/autoload.php";

use MicrosoftAzure\Storage\Blob\BlobRestProxy;
use MicrosoftAzure\Storage\Blob\BlobSharedAccessSignatureHelper;
use MicrosoftAzure\Storage\Blob\Models\CreateBlockBlobOptions;
use MicrosoftAzure\Storage\Blob\Models\CreateContainerOptions;
use MicrosoftAzure\Storage\Blob\Models\ListBlobsOptions;
use MicrosoftAzure\Storage\Blob\Models\PublicAccessType;
use MicrosoftAzure\Storage\Blob\Models\DeleteBlobOptions;
use MicrosoftAzure\Storage\Blob\Models\CreateBlobOptions;
use MicrosoftAzure\Storage\Blob\Models\GetBlobOptions;
use MicrosoftAzure\Storage\Blob\Models\ContainerACL;
use MicrosoftAzure\Storage\Blob\Models\SetBlobPropertiesOptions;
use MicrosoftAzure\Storage\Blob\Models\ListPageBlobRangesOptions;
use MicrosoftAzure\Storage\Common\Exceptions\ServiceException;
use MicrosoftAzure\Storage\Common\Exceptions\InvalidArgumentTypeException;
use MicrosoftAzure\Storage\Common\Internal\Resources;
use MicrosoftAzure\Storage\Common\Internal\StorageServiceSettings;
use MicrosoftAzure\Storage\Common\Models\Range;
use MicrosoftAzure\Storage\Common\Models\Logging;
use MicrosoftAzure\Storage\Common\Models\Metrics;
use MicrosoftAzure\Storage\Common\Models\RetentionPolicy;
use MicrosoftAzure\Storage\Common\Models\ServiceProperties;

$connectionString = 'DefaultEndpointsProtocol=https;AccountName='.getenv("ENV_STORAGE_ACCT").';AccountKey='.getenv("ENV_AZURE_KEY");
$blobClient = BlobRestProxy::createBlobService($connectionString);

$myContainer = getenv("ENV_CONTAINER");

echo generateBlobDownloadLinkWithSAS();

function generateBlobDownloadLinkWithSAS()
{
    global $connectionString, $myContainer;
    $expiry = gmdate("Y-m-d\TH:i:s\Z", strtotime('+60 minutes'));

    $settings = StorageServiceSettings::createFromConnectionString($connectionString);
    $accountName = $settings->getName();
    $accountKey = $settings->getKey();

    $helper = new BlobSharedAccessSignatureHelper(
        $accountName,
        $accountKey
    );

    // Refer to following link for full candidate values to construct a service level SAS
    // https://docs.microsoft.com/en-us/rest/api/storageservices/constructing-a-service-sas
    $sas = $helper->generateBlobServiceSharedAccessSignatureToken(
        Resources::RESOURCE_TYPE_CONTAINER,
        "$myContainer",
        'rwdlac',                            // Read
	    $expiry,
        //'2030-01-01T08:30:00Z'//,       // A valid ISO 8601 format expiry time
        //'2016-01-01T08:30:00Z',       // A valid ISO 8601 format expiry time
        //'0.0.0.0-255.255.255.255'
        //'https,http'
    );

    $connectionStringWithSAS = Resources::BLOB_ENDPOINT_NAME .
        '='.
        'https://' .
        $accountName .
        '.' .
        Resources::BLOB_BASE_DNS_NAME .
        ';' .
        Resources::SAS_TOKEN_NAME .
        '=' .
        $sas;

    $blobClientWithSAS = BlobRestProxy::createBlobService(
        $connectionStringWithSAS
    );

    // We can download the blob with PHP Client Library
    // downloadBlobSample($blobClientWithSAS);

    // Or generate a temporary readonly download URL link
    $blobUrlWithSAS = sprintf(
        '%s%s?%s',
        (string)$blobClientWithSAS->getPsrPrimaryUri(),
        "$myContainer",
        $sas
    );

#    file_put_contents("outputBySAS.txt", fopen($blobUrlWithSAS, 'r'));

    return $blobUrlWithSAS;
}
?>
