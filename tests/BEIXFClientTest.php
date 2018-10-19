<?php
namespace BrightEdge\Tests;

use PHPUnit\Framework\TestCase;

use BrightEdge\IXFSDKUtils;
use BrightEdge\Rule;
use BrightEdge\RuleEngine;
use function BrightEdge\buildCapsuleWrapper;

/**
C:\wamp64\bin\php\php7.0.23\php.exe c:\php-56\phpunit.phar --bootstrap be_ixf_client.php tests\BEIXFClientTest.php
 */

/**
 * @covers BEIXFClient
 */


final class BEIXFClientTest extends TestCase {

    public static $capsuleJson = '{
  "account_id": "f00000000000123",
  "key": "http://mycompany.com/test/index.php?a=foo&b=bar",
  "date_created": 1501608650554,
  "date_published": 1501608670554,
  "publishing_engine": "capsulemaker",
  "engine_version": "1.0.0.0",
  "capsule_version": "1.0.0",
  "nodes": [
    {
      "type": "headstr",
      "date_created": 1501608650554,
      "date_published": 1501608670554,
      "publishing_engine": "pixel",
      "engine_version": "1.0.0.0",
      "meta_string": "consolidated_12",
      "content": "General _head_open",
      "feature_group": "_head_open"
    },
    {
      "type": "bodystr",
      "date_created": 1501608650554,
      "date_published": 1501608670554,
      "publishing_engine": "linkmaker",
      "engine_version": "1.0.0.0",
      "content": "General body open",
      "feature_group": "_body_open"
    }
  ],
  "page_group_nodes": {
    "page_group1": [
      {
        "type": "bodystr",
        "feature_group": "_body_open",
        "date_created": 1504117307000,
        "date_published": 1504117349000,
        "publishing_engine": "SDK_internal",
        "engine_version": "1.0.0.1",
        "content": "Page group _body_open"
      },
      {
        "type": "bodystr",
        "feature_group": "body_1",
        "date_created": 1504117307000,
        "date_published": 1504117349000,
        "publishing_engine": "SDK_internal",
        "engine_version": "1.0.0.1",
        "content": "Page group body_1"
      }
    ]
  },
  "config": {
    "redirect_rules": [
        {
            "name": "force secure",
            "type": "regex",
            "source_regex": "^(HTTP:\\\/\\\/)(.*)",
            "replacement_regex": "https://$2",
            "user_agent_regex": "bingbot",
            "flag": 2
        },
        {
            "name": "replace_space_in_path",
            "type": "regex_path",
            "source_regex" : "%20",
            "replacement_regex": "-",
            "flag": 0
        },
        {
            "name": "upper_case_parameter",
            "type": "case_parameter",
            "case": 1,
            "flag": 1
        },
        {
            "name": "upper_case_path",
            "type": "case_path",
            "case": 1,
            "flag": 0
        }
    ],
    "page_groups": [
      {
        "name": "page_group2",
        "include_rules": [
          "\\\/blog.*",
          "\\\/content.*",
          "\\\/localhosv.*"
        ],
        "priority": 1
      },
      {
        "name": "page_group1",
        "include_rules": [
          "\\\/googletest.*"
        ],
        "priority": 2
      },
      {
        "name": "page_group3",
        "include_rules": [
          "http:\\\/\\\/www.brightege.com\\\/.*"
        ],
        "priority": 3
      }
    ]
  }
}';

    public function testIsBitEnabled() {
        $this->assertFalse(IXFSDKUtils::isBitEnabled(0, 0));
        $this->assertTrue(IXFSDKUtils::isBitEnabled(1, 0));
        $this->assertTrue(IXFSDKUtils::isBitEnabled(3, 0));
        $this->assertFalse(IXFSDKUtils::isBitEnabled(2, 0));

        $this->assertFalse(IXFSDKUtils::isBitEnabled(0, 1));
        $this->assertFalse(IXFSDKUtils::isBitEnabled(1, 1));
        $this->assertTrue(IXFSDKUtils::isBitEnabled(2, 1));
        $this->assertTrue(IXFSDKUtils::isBitEnabled(3, 1));
        $this->assertFalse(IXFSDKUtils::isBitEnabled(4, 1));
        $this->assertTrue(IXFSDKUtils::isBitEnabled(6, 1));
    }

    public function testGetBooleanValue() {
        $this->assertFalse(IXFSDKUtils::getBooleanValue("F"));
        $this->assertFalse(IXFSDKUtils::getBooleanValue("false"));
        $this->assertFalse(IXFSDKUtils::getBooleanValue("o"));
        $this->assertFalse(IXFSDKUtils::getBooleanValue(""));
        $this->assertFalse(IXFSDKUtils::getBooleanValue(NULL));
        $this->assertTrue(IXFSDKUtils::getBooleanValue("true"));
        $this->assertTrue(IXFSDKUtils::getBooleanValue("True"));
        $this->assertTrue(IXFSDKUtils::getBooleanValue("on"));
        $this->assertTrue(IXFSDKUtils::getBooleanValue("T"));
        $this->assertTrue(IXFSDKUtils::getBooleanValue("t"));
        $this->assertTrue(IXFSDKUtils::getBooleanValue("1"));
    }

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

    public function testoverrideHostOrProtocolInURL() {
        $this->assertEquals(
            "http://cnn.com/topnews",
            IXFSDKUtils::overrideHostOrProtocolInURL("http://www.abc.com/topnews", "cnn.com", null)
        );
        $this->assertEquals(
            "http://cnn.com/topnews",
            IXFSDKUtils::overrideHostOrProtocolInURL("http://www.abc.com/topnews", "cnn.com:80", null)
        );
        $this->assertEquals(
            "http://cnn.com:81/topnews",
            IXFSDKUtils::overrideHostOrProtocolInURL("http://www.abc.com/topnews", "cnn.com:81", null)
        );
        $this->assertEquals(
            "http://cnn.com:81/topnews",
            IXFSDKUtils::overrideHostOrProtocolInURL("https://www.abcnews.com/topnews", "cnn.com:81", "http")
        );
        $this->assertEquals(
            "https://cnn.com/topnews",
            IXFSDKUtils::overrideHostOrProtocolInURL("http://cnn.com/topnews", null, "https")
        );
    }

    public function testParametersInURL() {
        $whitelistParameters = array();

        // empty list empty parameter
        $this->assertFalse(
            IXFSDKUtils::parametersInURL("http://www.brightedge.com/test/index.jsp", $whitelistParameters)
        );

        // empty list
        $this->assertFalse(
            IXFSDKUtils::parametersInURL("http://www.brightedge.com/test/index.jsp?k1=v1&k2=v2", $whitelistParameters)
        );

        $whitelistParameters = array();
        array_push($whitelistParameters, "k1");
        $this->assertTrue(
            IXFSDKUtils::parametersInURL("http://www.brightedge.com/test/index.jsp?k1=v1&k2=v2", $whitelistParameters)
        );

        $whitelistParameters = array();
        array_push($whitelistParameters, "k3");
        $this->assertFalse(
            IXFSDKUtils::parametersInURL("http://www.brightedge.com/test/index.jsp?k1=v1&k2=v2", $whitelistParameters)
        );

    }

    public function testNormalizeURL() {
        $whitelistParameters = array();
        // make sure we remove all query string by default
        $this->assertEquals("http://www.brightedge.com/test/index.jsp",
            IXFSDKUtils::normalizeURL("http://www.brightedge.com/test/index.jsp?k1=v1&k2=v2", $whitelistParameters));
        // make sure we remove extraneous port info
        $this->assertEquals("http://www.brightedge.com/test/index.jsp",
            IXFSDKUtils::normalizeURL("http://www.brightedge.com:80/test/index.jsp?k1=v1&k2=v2", $whitelistParameters));
        $this->assertEquals("http://www.brightedge.com:81/test/index.jsp",
            IXFSDKUtils::normalizeURL("http://www.brightedge.com:81/test/index.jsp?k1=v1&k2=v2", $whitelistParameters));
        $this->assertEquals("https://www.brightedge.com/test/index.jsp",
            IXFSDKUtils::normalizeURL("https://www.brightedge.com:443/test/index.jsp?k1=v1&k2=v2", $whitelistParameters));
        $this->assertEquals("https://www.brightedge.com:444/test/index.jsp",
            IXFSDKUtils::normalizeURL("https://www.brightedge.com:444/test/index.jsp?k1=v1&k2=v2", $whitelistParameters));
        // make sure whitelist parameter works
        $whitelistParameters = array();
        array_push($whitelistParameters, "k1");
        $this->assertEquals("http://www.brightedge.com/test/index.jsp?k1=v1",
            IXFSDKUtils::normalizeURL("http://www.brightedge.com/test/index.jsp?k1=v1&k2=v2", $whitelistParameters));
        $whitelistParameters = array();
        array_push($whitelistParameters, "k1");
        array_push($whitelistParameters, "k2");
        $this->assertEquals("http://www.brightedge.com/test/index.jsp?k1=v1&k2=v2",
            IXFSDKUtils::normalizeURL("http://www.brightedge.com/test/index.jsp?k1=v1&k2=v2", $whitelistParameters));
        $whitelistParameters = array();
        array_push($whitelistParameters, "k2");
        $this->assertEquals("http://www.brightedge.com/test/index.jsp?k2=v2",
            IXFSDKUtils::normalizeURL("http://www.brightedge.com/test/index.jsp?k1=v1&k2=v2", $whitelistParameters));
        // single key multiple value
        $whitelistParameters = array();
        array_push($whitelistParameters, "k1");
        $this->assertEquals("http://www.brightedge.com/test/index.jsp?k1=v1&k1=v2",
            IXFSDKUtils::normalizeURL("http://www.brightedge.com/test/index.jsp?k1=v1&k1=v2", $whitelistParameters));
        // make sure we keep the encoding value
        $whitelistParameters = array();
        array_push($whitelistParameters, "k1");
        $this->assertEquals("http://www.brightedge.com/test/index.jsp?k1=%25abcdef%3D",
            IXFSDKUtils::normalizeURL("http://www.brightedge.com/test/index.jsp?k1=%25abcdef%3D&k2=v2",
                $whitelistParameters));
        // check sorting in key
        $whitelistParameters = array();
        array_push($whitelistParameters, "ka");
        array_push($whitelistParameters, "kb");
        array_push($whitelistParameters, "kc");
        $this->assertEquals("http://www.brightedge.com/test/index.jsp?ka=v2&kb=v1&kc=v3",
            IXFSDKUtils::normalizeURL("http://www.brightedge.com/test/index.jsp?kb=v1&kc=v3&ka=v2", $whitelistParameters));
        // check sorting in key with single key multiple values
        // seems like comparator keeps position
        $whitelistParameters = array();
        array_push($whitelistParameters, "ka");
        array_push($whitelistParameters, "kb");
        array_push($whitelistParameters, "kc");
        $this->assertEquals("http://www.brightedge.com/test/index.jsp?ka=v2.0&ka=v2.1&kb=v1&kc=v3",
            IXFSDKUtils::normalizeURL("http://www.brightedge.com/test/index.jsp?kb=v1&ka=v2.0&kc=v3&ka=v2.1",
                $whitelistParameters));
        $this->assertEquals("http://www.brightedge.com/test/index.jsp?ka=v2.0&ka=v2.1&kb=v1&kc=v3",
            IXFSDKUtils::normalizeURL("http://www.brightedge.com/test/index.jsp?kb=v1&ka=v2.1&kc=v3&ka=v2.0",
                $whitelistParameters));
        $whitelistParameters = array();
        array_push($whitelistParameters, "foo");
        array_push($whitelistParameters, "goo");
        $this->assertEquals("http://www.brightedge.com?foo=&goo=car",
            IXFSDKUtils::normalizeURL("http://www.brightedge.com?goo=car&foo=&", $whitelistParameters));
        $this->assertEquals("http://www.brightedge.com?foo=car&goo=",
            IXFSDKUtils::normalizeURL("http://www.brightedge.com?goo=&foo=car", $whitelistParameters));
        $this->assertEquals("http://www.brightedge.com?goo=&goo=1",
            IXFSDKUtils::normalizeURL("http://www.brightedge.com?goo=1&goo=", $whitelistParameters));
        $this->assertEquals("http://www.brightedge.com?goo&goo=1",
            IXFSDKUtils::normalizeURL("http://www.brightedge.com?goo=1&goo", $whitelistParameters));
    }

    public function testUserAgentMatchesRegex() {
        $userAgentRegex1 = "google|bingbot|msnbot|slurp|duckduckbot|baiduspider|yandexbot|sogou|exabot|facebot|ia_archiver";
        $userAgentRegex2 = "chrome|google|bingbot|msnbot|slurp|duckduckbot|baiduspider|yandexbot|sogou|exabot|facebot|ia_archiver";

        // assert null always returns false
        $this->assertFalse(
            IXFSDKUtils::userAgentMatchesRegex(NULL, $userAgentRegex1));

        // check empty string
        $this->assertFalse(
            IXFSDKUtils::userAgentMatchesRegex("", $userAgentRegex1));


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

    public function testconvertToNormalizedTimeZone() {
        // daylight savings 3/12/2017-11/5/2017
        // not supported by PHP
        $epochTimeMillis = 1504199514000;
        $this->assertEquals("p_tstr:Thu Aug 31 10:11:54 PST 2017; p_epoch:1504199514000",
            IXFSDKUtils::convertToNormalizedTimeZone($epochTimeMillis, "p"));

        // daylight savings 3/12/2017-11/5/2017
        // not supported by PHP
        $epochTimeMillis = 1490980314000;
        $this->assertEquals("p_tstr:Fri Mar 31 10:11:54 PST 2017; p_epoch:1490980314000",
            IXFSDKUtils::convertToNormalizedTimeZone($epochTimeMillis, "p"));

        // standard
        $epochTimeMillis = 1488388314000;
        $this->assertEquals("p_tstr:Wed Mar 01 09:11:54 PST 2017; p_epoch:1488388314000",
            IXFSDKUtils::convertToNormalizedTimeZone($epochTimeMillis, "p"));

        // test single digit month, day, hour, and minute
        $epochTimeMillis = 1488388194000;
        $this->assertEquals("p_tstr:Wed Mar 01 09:09:54 PST 2017; p_epoch:1488388194000",
            IXFSDKUtils::convertToNormalizedTimeZone($epochTimeMillis, "p"));
    }

    public function testConvertToNormalizedGoogleIndexTimeZone() {
        // daylight savings 3/12/2017-11/5/2017
        $epochTimeMillis = 1504199514000;
        $this->assertEquals("py_2017; pm_08; pd_31; ph_10; pmh_11; p_epoch:1504199514000",
            IXFSDKUtils::convertToNormalizedGoogleIndexTimeZone($epochTimeMillis, "p"));

        // daylight savings 3/12/2017-11/5/2017
        // not supported by PHP
        $epochTimeMillis = 1490980314000;
        $this->assertEquals("py_2017; pm_03; pd_31; ph_10; pmh_11; p_epoch:1490980314000",
            IXFSDKUtils::convertToNormalizedGoogleIndexTimeZone($epochTimeMillis, "p"));

        // standard
        $epochTimeMillis = 1488388314000;
        $this->assertEquals("py_2017; pm_03; pd_01; ph_09; pmh_11; p_epoch:1488388314000",
            IXFSDKUtils::convertToNormalizedGoogleIndexTimeZone($epochTimeMillis, "p"));

        // test single digit month, day, hour, and minute
        $epochTimeMillis = 1488388194000;
        $this->assertEquals("py_2017; pm_03; pd_01; ph_09; pmh_09; p_epoch:1488388194000", IXFSDKUtils::convertToNormalizedGoogleIndexTimeZone($epochTimeMillis, "p"));
    }

    public function testForSecure() {
        $this->assertEquals("https://www.google.com",
            Rule::evaluateRule("^(http:\/\/)(.*)", "https://$2", "http://www.google.com", false)[0]);
    }

    public function testRemoveWWW() {
        $this->assertEquals("https://google.com",
            Rule::evaluateRule("^(https?)\:\/\/www.(.*)", "$1://$2", "https://www.google.com", false)[0]);
        $this->assertEquals("http://google.com",
            Rule::evaluateRule("^(https?)\:\/\/www.(.*)", "$1://$2", "http://www.google.com", false)[0]);
    }

    public function testForceWWW() {
        $this->assertEquals("https://www.google.com",
            Rule::evaluateRule("(https?):\/\/((?!www).*)", "$1://www.$2", "https://google.com", false)[0]);
        $this->assertEquals("http://www.google.com",
            Rule::evaluateRule("(https?):\/\/((?!www).*)", "$1://www.$2", "http://google.com", false)[0]);
    }

    public function testReplaceSpace() {
        $this->assertEquals("https://google-test.com",
            Rule::evaluateRule("%20", "-", "https://google%20test.com", false)[0]);
    }

    public function testCustomRule() {
        $this->assertEquals("https://www.hotelsbook.com/4star-hotels/london/royal-garden-hotel/",
            Rule::evaluateRule("(.*)(\/RATES\/.*)+", "$1/",
                "https://www.hotelsbook.com/4star-hotels/london/royal-garden-hotel/rates/d42a9f334a9b6b3cec5f93035a9288b", true)[0]);
    }

    public function testChangeCaseUpper() {
        $this->assertEquals("HTTP", Rule::changeCase(1, "http")[0]);
    }

    public function testChangeCaseLower() {
        $this->assertEquals("http", Rule::changeCase(0, "HTTP")[0]);
    }

    public function testevaluateRulesPath() {
        $normalizedURL = "http://googletest/local a/";
        $re = new RuleEngine();
        $rulesArray = '[{"name":"force secure","type":"regex","source_regex":"^(http:\\\/\\\/)(.*)","replacement_regex":"https://$2","user_agent_regex":"bingbot","flag":0},
        {"name":"replace_space_in_path","type":"regex_path","source_regex":"[[:space:]]+","replacement_regex":"-","flag":0},
        {"name":"upper_case_parameter","type":"case_parameter","case":1,"flag":1},
        {"name":"lower_case_path","type":"case_path","case":0}]';
        $re->setRulesArray(json_decode($rulesArray));
        $output = $re->evaluateRules($normalizedURL, "bingbot");
        $this->assertEquals("https://googletest/local-a/", $output);
    }

    public function testevaluateRulesParameter() {
        $normalizedURL = "HTTP://googletest/local%20%20%20a/?local=000";
        $re = new RuleEngine();
        $rulesArray = '[{"name":"force secure","type":"regex","source_regex":"^(http:\\\/\\\/)(.*)","replacement_regex":"https://$2","flag": 2},
        {"name":"replace_space_in_path","type":"regex_path","source_regex":"[%20]+","replacement_regex":"-","flag":0},
        {"name":"upper_case_parameter","type":"case_parameter","case":1,"flag":1},
        {"name":"lower_case_path","type":"case_path","case":0}]';
        $re->setRulesArray(json_decode($rulesArray));
        $output = $re->evaluateRules($normalizedURL, "bingbot");
        $this->assertEquals("https://googletest/local-a/?LOCAL=000", $output);
    }

    public function testBuildURL() {
        $url1 = "http://my.domain.com/my_page/str/?arg1=this&amp;arg2=that";
        $urlParts = parse_url($url1);
        $urlParts['path'] = "/my/";
        $output1 = RuleEngine::build_url($urlParts);
        $this->assertEquals($output1, "http://my.domain.com/my/?arg1=this&amp;arg2=that");

        $url2 = "http://my.domain.com/my%20page/str/?key=%20%27&key2=%C3%A1";
        $urlParts = parse_url($url2);
        $urlParts['path'] = "/my%20new%20path/";
        $output2 = RuleEngine::build_url($urlParts);
        $this->assertEquals($output2, "http://my.domain.com/my%20new%20path/?key=%20%27&key2=%C3%A1");

        $url3 = "http://my.domain.com/my%20page/?key=%20%27";
        $urlParts = parse_url($url3);
        $urlParts['query'] = "key=%20%27&key2=%C3%A1";
        $output3 = RuleEngine::build_url($urlParts);
        $this->assertEquals($output3, "http://my.domain.com/my%20page/?key=%20%27&key2=%C3%A1");
    }

    public function testbuildCapsuleWrapper() {
        $obj = json_decode(self::$capsuleJson);
        unset($obj->config->page_groups);
        unset($obj->page_group_nodes);
        $jsonObject = json_encode($obj);
        $capsule = buildCapsuleWrapper($jsonObject, "http://googletest/local%20a/?local=1", "bingbot");
        $node = $capsule->getRedirectNode();
        $redirect_url = $node->getRedirectURL();
        $this->assertEquals("https://googletest/local-a/?LOCAL=1", $redirect_url);

        // test invalid JSON
        $jsonObject = '{z';
        $capsule = buildCapsuleWrapper($jsonObject, "http://googletest/local%20a/?local=1", "bingbot");
        $this->assertEquals($capsule, NULL);
    }

    public function testbuildCapsuleWrapperEmptyConfig() {
        $obj = json_decode(self::$capsuleJson);
        $obj->config = array();
        unset($obj->page_group_nodes);
        $jsonObject = json_encode($obj);
        $capsule = buildCapsuleWrapper($jsonObject, "http://googletest/local%20a/?local=1", "bingbot");
        $this->assertEquals($capsule->getRedirectNode(), NULL);
    }

    public function testbuildCapsuleWrapperPageGroups() {
        $obj = json_decode(self::$capsuleJson);
        $obj->config->redirect_rules = array_slice($obj->config->redirect_rules,0,1);
        $jsonObject = json_encode($obj);
        $capsule = buildCapsuleWrapper($jsonObject, "https://googletest/local%20a/?local=1", "bingbot");
        $body_open_str = $capsule->getNode('bodystr', '_body_open')->getContent();
        $head_open_str = $capsule->getNode('headstr', '_head_open')->getContent();
        $this->assertEquals($body_open_str, 'Page group _body_open');
        $this->assertEquals($head_open_str, 'General _head_open');
        $redirect_node = $capsule->getRedirectNode();
        $this->assertEquals($redirect_node, NULL);
    }

    public function testbuildCapsuleWrapperRedirectWithPageGroups() {
        $jsonObject = json_encode(json_decode(self::$capsuleJson));
        $capsule = buildCapsuleWrapper($jsonObject, "https://googletest/local%20a/?local=1", "bingbot");
        $node = $capsule->getRedirectNode();
        $redirect_url = $node->getRedirectURL();
        $this->assertEquals("https://googletest/local-a/?LOCAL=1", $redirect_url);
    }
}
?>
