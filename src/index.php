<?php
require 'be_ixf_client.php';
use BrightEdge\BEIXFClient;

$be_ixf_config = array(
    BEIXFClient::$ENVIRONMENT_CONFIG => BEIXFClient::$ENVIRONMENT_PRODUCTION,
    // Comment/uncomment the following CAPSULE_MODE_CONFIG options to for testing/dubugging
    BEIXFClient::$CAPSULE_MODE_CONFIG => BEIXFClient::$LOCAL_GLOBAL_CAPSULE_MODE, //use this to test connection to be_ixf_client.php
    // BEIXFClient::$CAPSULE_MODE_CONFIG => BEIXFClient::$REMOTE_PROD_GLOBAL_CAPSULE_MODE, //use this to test connection to api
    // BEIXFClient::$CAPSULE_MODE_CONFIG => BEIXFClient::$REMOTE_PROD_CAPSULE_MODE, // use this for for live mode
);

$be_ixf = new BEIXFClient($be_ixf_config);
?>

<html>
<head>
<?php echo $be_ixf->getHeadOpen() ?>
<title><?php echo $be_ixf->getHeadString("flexblock_1") ?></title>
<meta name="description" content="<?php echo $be_ixf->getHeadString("flexblock_2") ?>" />
</head>

<body>
    <?php echo $be_ixf->getBodyOpen() ?>

    <div style="background-color:#CCC;padding:10px">Header</div>

    <div style="background-color:#CCC;padding:10px">

        Body Content

        <ul>

            <li><?php echo $be_ixf->getBodyString("flexblock_3") ?></li>

            <li><?php echo $be_ixf->getBodyString("flexblock_4") ?></li>

            <li><?php echo $be_ixf->getCleanString("bodystr", "flexblock_5") ?></li>

        </ul>
    </div>

    <div style="background-color:#CCC;padding:10px">
        Footer
        <?php echo $be_ixf->close() ?>

    </div>

</body>
</html>
