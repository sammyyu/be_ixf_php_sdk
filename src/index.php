<?php
require 'be_ixf_client.php';

$useTesting = false;
$useGlobal = false;
$useTestingFlatFile = false;

$be_ixf_config = array(
    BEIXFClient::$ACCOUNT_ID_CONFIG => 'f00000000000123',
    BEIXFClient::$ENVIRONMENT_CONFIG => BEIXFClient::$ENVIRONMENT_PRODUCTION,
);

if (!$useTesting) {
    if (!$useGlobal) {
        $be_ixf_config = array_merge($be_ixf_config, array(
            BEIXFClient::$CAPSULE_MODE_CONFIG => BEIXFClient::$REMOTE_PROD_CAPSULE_MODE,
        ));
    } else {
        $be_ixf_config = array_merge($be_ixf_config, array(
            BEIXFClient::$CAPSULE_MODE_CONFIG => BEIXFClient::$REMOTE_PROD_GLOBAL_CAPSULE_MODE,
        ));
    }
} else {
    if (!$useGlobal) {
        if (!$useTestingFlatFile) {
            $be_ixf_config = array_merge($be_ixf_config, array(
                BEIXFClient::$CAPSULE_MODE_CONFIG => BEIXFClient::$LOCAL_CAPSULE_MODE,
            ));
        } else {
            $be_ixf_config = array_merge($be_ixf_config, array(
                BEIXFClient::$CAPSULE_MODE_CONFIG => BEIXFClient::$LOCAL_FLAT_FILE_CAPSULE_MODE,
            ));
        }
    } else {
        if (!$useTestingFlatFile) {
            $be_ixf_config = array_merge($be_ixf_config, array(
                BEIXFClient::$CAPSULE_MODE_CONFIG => BEIXFClient::$LOCAL_GLOBAL_CAPSULE_MODE,
            ));
        } else {
            $be_ixf_config = array_merge($be_ixf_config, array(
                BEIXFClient::$CAPSULE_MODE_CONFIG => BEIXFClient::$LOCAL_GLOBAL_FLAT_FILE_CAPSULE_MODE,
            ));
        }
    }
}

$be_ixf_config = array_merge($be_ixf_config, array(
    BEIXFClient::$PROXY_HOST_CONFIG => 'test1.bredg.com',
    BEIXFClient::$PROXY_PORT_CONFIG => '3333',
    BEIXFClient::$PROXY_LOGIN_CONFIG => 'syu',
    BEIXFClient::$PROXY_PASSWORD_CONFIG => 'myproxy',
));

// Set new location from where resources are loaded
// $be_ixf_config = array_merge($be_ixf_config, array(
//     BEIXFClient::$CONTENT_BASE_PATH_CONFIG => 'c:/brightedge/',
// ));
$client = new BEIXFClient($be_ixf_config);
?>

<html>
<head>
<?php echo $client->getInitString() ?>
</head>
<body>
<h2>Hello World!</h2>
<?php
// set time zone if it is not set
if (get_cfg_var("date.timezone") == "0" || get_cfg_var("date.timezone") == "UTC") {
    date_default_timezone_set("US/Pacific");
}
?>
<p>Current time is <?php echo date("Y-m-d h:i:sa") ?></p>
<div id="be_sdkms_linkblock">
<?php echo $client->getFeatureString("bodystr", "be_sdkms_flexblock_1") ?>
</div>

<?php echo $client->close() ?>
</body>
</html>