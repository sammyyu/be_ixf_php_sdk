<?php
require 'be_ixf_client.php';
$be_ixf_config = array(
    BEIXFClient::$ACCOUNT_ID_CONFIG => 'f00000000000123',
    BEIXFClient::$ENVIRONMENT_CONFIG => BEIXFClient::$ENVIRONMENT_PRODUCTION,
);

$be_ixf_config = array_merge(array(
    BEIXFClient::$PROXY_HOST_CONFIG => 'test1.bredg.com',
    BEIXFClient::$PROXY_PORT_CONFIG => '3333',
    BEIXFClient::$PROXY_LOGIN_CONFIG => 'syu',
    BEIXFClient::$PROXY_PASSWORD_CONFIG => 'myproxy',
));
$be_ixf = new BEIXFClient($be_ixf_config);
?>

<html>
<head>
<?php echo $be_ixf->getHeadOpen() ?>
</head>
<body><?php echo $be_ixf->getBodyOpen() ?>
<h2>Hello World!</h2>
<?php
// set time zone if it is not set
if (get_cfg_var("date.timezone") == "0" || get_cfg_var("date.timezone") == "UTC") {
    date_default_timezone_set("US/Pacific");
}
?>
<p>Current time is <?php echo date("Y-m-d h:i:sa") ?></p>
<div id="be_sdkms_linkblock">
<?php echo $be_ixf->getBodyString("be_sdkms_flexblock_1") ?>
</div>

<?php echo $be_ixf->close() ?>
</body>
</html>
<?php
header('Location: ./index.php');
?>
