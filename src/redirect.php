<?php
require 'be_ixf_client.php';
use BrightEdge\BEIXFClient;

$be_ixf_config = array(
    BEIXFClient::$API_ENDPOINT_CONFIG => 'https://ixfd1-api.bc0a.com',
);
$be_ixf = new BEIXFClient($be_ixf_config);
?>

<html>
<head>
<?php echo $be_ixf->getHeadOpen() ?>
</head>
<body><?php echo $be_ixf->getBodyOpen() ?>
<h2>IXF Redirection Page (You shouldn't be seeing this)</h2>
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

<?php echo $be_ixf->close(); ?>
</body>
</html>
