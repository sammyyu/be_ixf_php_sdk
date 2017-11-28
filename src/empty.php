<?php
require 'be_ixf_client.php';
$be_ixf_config = array(
);
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

<?php
if (!$be_ixf->hasBodyString("non_existent_block")) {
    echo "my_html";
} else {
    echo $be_ixf->getBodyString("non_existent_block");
}
?>
</div>
<p> Direct Empty response
<?php echo $be_ixf->getBodyString("non_existent_block"); ?>
</p>

<?php echo $be_ixf->close() ?>
</body>
</html>
