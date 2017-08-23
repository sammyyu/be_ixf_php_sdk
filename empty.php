<?php
require 'be_ixf_client.php';
$be_ixf_config = array(
);
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

<?php
if (!$client->hasFeatureString("bodystr", "non_existent_block")) {
    echo "my_html";
} else {
    echo $client->getFeatureString("bodystr", "non_existent_block");
}
?>
</div>
<p> Direct Empty response
<?php echo $client->getFeatureString("bodystr", "non_existent_block"); ?>
</p>

<?php echo $client->close() ?>
</body>
</html>
