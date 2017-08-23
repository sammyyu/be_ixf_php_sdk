<?php
require 'be_ixf_client.php';

$useGlobal = false;

$be_ixf_config = array(
    BEIXFClient::$ACCOUNT_ID_CONFIG => 'f00000000000123',
    BEIXFClient::$ENVIRONMENT_CONFIG => BEIXFClient::$ENVIRONMENT_PRODUCTION,
);

if ($useGlobal) {
    $be_ixf_config = array_merge($be_ixf_config, array(
        BEIXFClient::$PAGE_INDEPENDENT_MODE_CONFIG => 'true',
    ));

}

$be_ixf_config = array_merge($be_ixf_config, array(
    BEIXFClient::$PROXY_HOST_CONFIG => 'test1.bredg.com',
    BEIXFClient::$PROXY_PORT_CONFIG => '3333',
    BEIXFClient::$PROXY_LOGIN_CONFIG => 'syu',
    BEIXFClient::$PROXY_PASSWORD_CONFIG => 'myproxy',
));
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
<?php echo $client->getFeatureString("bodystr", "be_sdkms_linkblock") ?>
</div>

<?php echo $client->close() ?>
</body>
</html>
