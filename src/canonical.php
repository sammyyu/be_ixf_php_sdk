<?php
require 'be_ixf_client.php';
$be_ixf_config = array();
// $be_ixf_config[BEIXFClient::$CANONICAL_PAGE_CONFIG] = 'http://www.test.com/ixf-sdk/index.jsp';
$be_ixf_config[BEIXFClient::$CANONICAL_HOST_CONFIG] = 'www.test.com';
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

<?php echo $be_ixf->getBodyString("bodystr", "be_sdkms_flexblock_1"); ?>
</div>

<?php echo $be_ixf->close() ?>
</body>
</html>
