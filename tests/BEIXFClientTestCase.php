<?php
use PHPUnit\Framework\TestCase;

/**
c:\wamp64\bin\php\php7.0.10\php.exe c:\php56\phpunit.phar --bootstrap be_ixf_client.php tests\BEIXFClientTestCase.php
 */

/**
 * @covers BEIXFClient
 */
final class BEIXFClientTestCase extends TestCase {
    public function testGetSignedNumber() {
        $this->assertEquals(
            5,
            IXFSDKUtils::getSignedNumber(5)
        );
        $this->assertEquals(
            -5,
            IXFSDKUtils::getSignedNumber(-5)
        );
        $this->assertEquals(
            313923,
            IXFSDKUtils::getSignedNumber(313923)
        );
        $this->assertEquals(
            -313923,
            IXFSDKUtils::getSignedNumber(-313923)
        );
        $this->assertEquals(
            341720826,
            IXFSDKUtils::getSignedNumber(2139235434234)
        );

    }

    public function testGetPageHash() {
        $this->assertEquals(
            "02026868259",
            IXFSDKUtils::getPageHash("/test/index.jsp")
        );
    }

    public function testNormalizeURL() {
        $whitelistParameters = array();

        // make sure we remove all query string by default
        $this->assertEquals(
            "http://www.brightedge.com/test/index.jsp",
            IXFSDKUtils::normalizeURL("http://www.brightedge.com/test/index.jsp?k1=v1&k2=v2", $whitelistParameters)
        );

        // make sure we remove extraneous port info
        $this->assertEquals(
            "http://www.brightedge.com/test/index.jsp",
            IXFSDKUtils::normalizeURL("http://www.brightedge.com:80/test/index.jsp?k1=v1&k2=v2", $whitelistParameters)
        );
        $this->assertEquals(
            "http://www.brightedge.com:81/test/index.jsp",
            IXFSDKUtils::normalizeURL("http://www.brightedge.com:81/test/index.jsp?k1=v1&k2=v2", $whitelistParameters)
        );
        $this->assertEquals(
            "http://www.brightedge.com:81/test/index.jsp",
            IXFSDKUtils::normalizeURL("http://www.brightedge.com:81/test/index.jsp?k1=v1&k2=v2", $whitelistParameters)
        );
        $this->assertEquals(
            "https://www.brightedge.com/test/index.jsp",
            IXFSDKUtils::normalizeURL("https://www.brightedge.com:443/test/index.jsp?k1=v1&k2=v2", $whitelistParameters)
        );
        $this->assertEquals(
            "https://www.brightedge.com:444/test/index.jsp",
            IXFSDKUtils::normalizeURL("https://www.brightedge.com:444/test/index.jsp?k1=v1&k2=v2", $whitelistParameters)
        );

        // make sure whitelist parameter works
        $whitelistParameters = array();
        array_push($whitelistParameters, "k1");
        $this->assertEquals(
            "http://www.brightedge.com/test/index.jsp?k1=v1",
            IXFSDKUtils::normalizeURL("http://www.brightedge.com/test/index.jsp?k1=v1&k2=v2", $whitelistParameters)
        );

        $whitelistParameters = array();
        array_push($whitelistParameters, "k1");
        array_push($whitelistParameters, "k2");
        $this->assertEquals(
            "http://www.brightedge.com/test/index.jsp?k1=v1&k2=v2",
            IXFSDKUtils::normalizeURL("http://www.brightedge.com/test/index.jsp?k1=v1&k2=v2", $whitelistParameters)
        );

        $whitelistParameters = array();
        array_push($whitelistParameters, "k2");
        $this->assertEquals(
            "http://www.brightedge.com/test/index.jsp?k2=v2",
            IXFSDKUtils::normalizeURL("http://www.brightedge.com/test/index.jsp?k1=v1&k2=v2", $whitelistParameters)
        );

        // single key multiple value
        $whitelistParameters = array();
        array_push($whitelistParameters, "k1");
        $this->assertEquals(
            "http://www.brightedge.com/test/index.jsp?k1=v1&k1=v2",
            IXFSDKUtils::normalizeURL("http://www.brightedge.com/test/index.jsp?k1=v1&k1=v2", $whitelistParameters)
        );

    }

}
?>
