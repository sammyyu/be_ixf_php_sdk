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

    public function testOverrideHostInURL() {
        $this->assertEquals(
            "http://cnn.com/topnews",
            IXFSDKUtils::overrideHostInURL("http://www.abc.com/topnews", "cnn.com")
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

    public function testUserAgentMatchesRegex() {
        $userAgentRegex1 = "google|bingbot|msnbot|slurp|duckduckbot|baiduspider|yandexbot|sogou|exabot|facebot|ia_archiver";
        $userAgentRegex2 = "chrome|google|bingbot|msnbot|slurp|duckduckbot|baiduspider|yandexbot|sogou|exabot|facebot|ia_archiver";
        $this->assertFalse(
            IXFSDKUtils::userAgentMatchesRegex("Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/59.0.3071.115 Safari/537.36",
                $userAgentRegex1));
        $this->assertTrue(
            IXFSDKUtils::userAgentMatchesRegex("Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/59.0.3071.115 Safari/537.36",
                $userAgentRegex2));

        // Google Crawlers: https://support.google.com/webmasters/answer/1061943?hl=en
        $this->assertTrue(
            IXFSDKUtils::userAgentMatchesRegex("Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)",
                $userAgentRegex1));
        $this->assertTrue(
            IXFSDKUtils::userAgentMatchesRegex("Googlebot/2.1 (+http://www.google.com/bot.html)",
                $userAgentRegex1));
        $this->assertTrue(
            IXFSDKUtils::userAgentMatchesRegex("Mozilla/5.0 (Linux; Android 6.0.1; Nexus 5X Build/MMB29P) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.96 Mobile Safari/537.36 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)",
                $userAgentRegex1));
        $this->assertTrue(
            IXFSDKUtils::userAgentMatchesRegex("Mediapartners-Google",
                $userAgentRegex1));

        // Bing Crawlers: https://www.bing.com/webmaster/help/which-crawlers-does-bing-use-8c184ec0
        $this->assertTrue(
            IXFSDKUtils::userAgentMatchesRegex("Mozilla/5.0 (compatible; bingbot/2.0; +http://www.bing.com/bingbot.htm)",
                $userAgentRegex1));
        $this->assertTrue(
            IXFSDKUtils::userAgentMatchesRegex("msnbot/2.0b (+http://search.msn.com/msnbot.htm)",
                $userAgentRegex1));
    }

}
?>
