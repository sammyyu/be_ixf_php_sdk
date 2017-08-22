<?php
/**
 * BE IXF Client class
 *
 * mod_curl must be enabled
 *
 */
class BEIXFClient {
    public static $ENVIRONMENT_CONFIG = "sdk.environment";
    public static $CHARSET_CONFIG = "sdk.charset";
    public static $API_ENDPOINT_CONFIG = "api.endpoint";
    public static $ACCOUNT_ID_CONFIG = "sdk.account";
    public static $CONNECT_TIMEOUT_CONFIG = "sdk.connectTimeout";
    public static $SOCKET_TIMEOUT_CONFIG = "sdk.socketTimeout";
    public static $CRAWLER_CONNECT_TIMEOUT_CONFIG = "sdk.crawlerConnectTimeout";
    public static $CRAWLER_SOCKET_TIMEOUT_CONFIG = "sdk.crawlerSocketTimeout";
    public static $PROXY_HOST_CONFIG = "sdk.proxyHost";
    public static $PROXY_PORT_CONFIG = "sdk.proxyPort";
    public static $PROXY_PROTOCOL_CONFIG = "sdk.proxyProtocol";
    public static $PROXY_LOGIN_CONFIG = "sdk.proxyLogin";
    public static $PROXY_PASSWORD_CONFIG = "sdk.proxyPassword";
    public static $WHITELIST_PARAMETER_LIST_CONFIG = "whitelist.parameter.list";
    public static $FLAT_FILE_FOR_TEST_MODE_CONFIG = "flat.file";
    public static $PAGE_INDEPENDENT_FOR_TEST_MODE_CONFIG = "page.independent";
    public static $CRAWLER_USER_AGENTS_CONFIG = "crawler.useragents";

    public static $CANONICAL_HOST_CONFIG = "canonical.host";
    public static $CANONICAL_PAGE_CONFIG = "canonical.page";

    public static $ENVIRONMENT_PRODUCTION = "production";
    public static $ENVIRONMENT_STAGING = "staging";
    public static $ENVIRONMENT_TESTING = "testing";

    public static $DEFAULT_CHARSET = "UTF-8";
    public static $DEFAULT_API_ENDPOINT = "https://ixf2-api.brightedge.com";
    public static $DEFAULT_ACCOUNT_ID = "0";
    public static $DEFAULT_CONNECT_TIMEOUT = "2000";
    public static $DEFAULT_SOCKET_TIMEOUT = "2000";
    public static $DEFAULT_CRAWLER_CONNECT_TIMEOUT = "10000";
    public static $DEFAULT_CRAWLER_SOCKET_TIMEOUT = "10000";
    // means proxy is disabled
    public static $DEFAULT_PROXY_PORT = "0";
    public static $DEFAULT_PROXY_PROTOCOL = "http";
    // a list of query string parameters that are kept separated by |
    public static $DEFAULT_WHITELIST_PARAMETER_LIST = "";
    // a list of crawler user agents case insensitive regex, so separate by |
    public static $DEFAULT_CRAWLER_USER_AGENTS = "google|bingbot|msnbot|slurp|duckduckbot|baiduspider|yandexbot|sogou|exabot|facebot|ia_archiver";

    public static $INIT_BLOCKTYPE = 0;
    public static $CLOSE_BLOCKTYPE = 1;
    public static $OTHER_BLOCKTYPE = 2;

    public static $CLIENT_NAME = "php_sdk";
    public static $CLIENT_VERSION = "1.0.0";

    private static $API_VERSION = "1.0.0";

    private static $DEFAULT_PUBLISHING_ENGINE = "built-in";
    private static $DEFAULT_ENGINE_VERSION = "1.0.0";
    private static $DEFAULT_ENGINE_METASTRING = null;

    // time zone to emit all date in
    private static $NORMALIZED_TIMEZONE = "US/Pacific";

    private $connectTime = 0;

    private $_get_capsule_api_url = null;
    private $capsule = null;
    private $capsule_response = null;

    private $debugMode = false;

    /**
     * a list of errors that is retained and spewed out in the footer primarily for
     * debugging
     */
    protected $errorMessages = array();

    /**
     * Instaniate IXF Client using a parameter array
     *
     * @access public
     * @param array of configuration key, value pairs.  sdk.account is required
     * @return object
     */
    public function __construct($params = array()) {
        // config array, defaults are defined here.
        $this->config = array(
            self::$ENVIRONMENT_CONFIG => self::$ENVIRONMENT_PRODUCTION,
            self::$API_ENDPOINT_CONFIG => self::$DEFAULT_API_ENDPOINT,
            self::$CHARSET_CONFIG => self::$DEFAULT_CHARSET,
            self::$ACCOUNT_ID_CONFIG => self::$DEFAULT_ACCOUNT_ID,
            self::$CONNECT_TIMEOUT_CONFIG => self::$DEFAULT_CONNECT_TIMEOUT,
            self::$SOCKET_TIMEOUT_CONFIG => self::$DEFAULT_SOCKET_TIMEOUT,
            self::$CRAWLER_CONNECT_TIMEOUT_CONFIG => self::$DEFAULT_CRAWLER_CONNECT_TIMEOUT,
            self::$CRAWLER_SOCKET_TIMEOUT_CONFIG => self::$DEFAULT_CRAWLER_SOCKET_TIMEOUT,
            self::$WHITELIST_PARAMETER_LIST_CONFIG => self::$DEFAULT_WHITELIST_PARAMETER_LIST,
            self::$FLAT_FILE_FOR_TEST_MODE_CONFIG => "true",
            self::$PROXY_PORT_CONFIG => self::$DEFAULT_PROXY_PORT,
            self::$PROXY_PROTOCOL_CONFIG => self::$DEFAULT_PROXY_PROTOCOL,
            self::$CRAWLER_USER_AGENTS_CONFIG => self::$DEFAULT_CRAWLER_USER_AGENTS,
        );

        // read from properties file if it exists
        $ini_file_location = join("/", array(__DIR__, "ixf.properties"));
        if (file_exists($ini_file_location)) {
            $ini_file = fopen($ini_file_location, "r");
            while (!feof($ini_file)) {
                $line = fgets($ini_file);
                $line = trim($line);
                $parts = explode("=", $line, 2);
                if (count($parts) == 2) {
                    $key = trim($parts[0]);
                    $value = trim($parts[1]);
                    $this->config[$key] = $value;
                }
            }
            fclose($ini_file);
        }

        if (!extension_loaded("curl")) {
            echo "PHP curl extension is required";
            return;
        }

        if (isset($_GET["ixf-debug"])) {
            $param_value = $_GET["ixf-debug"];
            $this->debugMode = $param_value === 'true' ? true : false;
        }

        // Merge passed in params with defaults for config.
        $this->config = array_merge($this->config, $params);

        // make URL request
        // http://127.0.0.1:8000/api/ixf/1.0/get_capsule/f00000000000123/asdasdsd/
        $urlBase = $this->config[self::$API_ENDPOINT_CONFIG];
        if (substr($urlBase, -1) != '/') {
            $urlBase .= "/";
        }

        $this->_original_url = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $this->_normalized_url = $this->_original_url;
        // #1 one construct the canonical URL
        if (isset($this->config[self::$CANONICAL_PAGE_CONFIG])) {
            $this->_normalized_url = $this->config[self::$CANONICAL_PAGE_CONFIG];
        } else if (isset($this->config[self::$CANONICAL_HOST_CONFIG])) {
            $this->_normalized_url = IXFSDKUtils::overrideHostInURL($this->_normalized_url, $this->config[self::$CANONICAL_HOST_CONFIG]);
        }

        // #2 normalize the URL
        $whitelistParameters = explode("|", $this->config[self::$WHITELIST_PARAMETER_LIST_CONFIG]);
        $this->_normalized_url = IXFSDKUtils::normalizeURL($this->_normalized_url, $whitelistParameters);

        // #3 calculate the page hash
        $page_hash = IXFSDKUtils::getPageHash($this->_normalized_url);

        $this->_get_capsule_api_url = $urlBase . 'api/ixf/' . self::$API_VERSION . '/get_capsule/' . $this->config[self::$ACCOUNT_ID_CONFIG] .
            '/' . $page_hash;
        $startTime = round(microtime(true) * 1000);
        $ch = curl_init();

        $connect_timeout = $this->config[self::$CONNECT_TIMEOUT_CONFIG];
        $socket_timeout = $this->config[self::$SOCKET_TIMEOUT_CONFIG];
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        // raise timeout if it is crawler user agent
        if (IXFSDKUtils::userAgentMatchesRegex($user_agent, $this->config[self::$CRAWLER_USER_AGENTS_CONFIG])) {
            $connect_timeout = $this->config[self::$CRAWLER_CONNECT_TIMEOUT_CONFIG];
            $socket_timeout = $this->config[self::$CRAWLER_SOCKET_TIMEOUT_CONFIG];
        }

        // Set URL to download
        curl_setopt($ch, CURLOPT_URL, $this->_get_capsule_api_url);
        // Include header in result? (0 = yes, 1 = no)
        curl_setopt($ch, CURLOPT_HEADER, 0);
        // Should cURL return or print out the data? (true = return, false = print)
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // disable SSL certificate check
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        // connect timeout in milliseconds
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, $connect_timeout);
        // overall timeout in seconds
        curl_setopt($ch, CURLOPT_TIMEOUT_MS, $socket_timeout);
        // Enable decoding of the response
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');
        // Enable following of redirects
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        if (isset($this->config[self::$PROXY_HOST_CONFIG])) {
            curl_setopt($ch, CURLOPT_PROXY, $this->config[self::$PROXY_HOST_CONFIG]);
            curl_setopt($ch, CURLOPT_PROXYPORT, $this->config[self::$PROXY_PORT_CONFIG]);
            if (isset($this->config[self::$PROXY_LOGIN_CONFIG])) {
                curl_setopt($ch, CURLOPT_PROXYUSERPWD, $this->config[self::$PROXY_LOGIN_CONFIG] . ":" . $this->config[self::$PROXY_PASSWORD_CONFIG]);
            }
        }

        // make the request to the given URL and then store the response,
        // request info, and error number
        // so we can use them later
        $request = array(
            'response' => curl_exec($ch),
            'info' => curl_getinfo($ch),
            'error_number' => curl_errno($ch),
            'error_message' => curl_error($ch),
        );

        // Close the cURL resource, and free system resources
        curl_close($ch);

        // see if we got any errors with the connection
        if ($request['error_number'] != 0) {
            array_push($this->errorMessages,
                'API request error=' . $request['error_message'] . ", capsule_url=" . $this->_get_capsule_api_url);
        }

        // see if we got a status code of something other than 200
        if ($request['info']['http_code'] != 200) {
            array_push($this->errorMessages,
                'API request invalid HTTP status=' . $request['info']['http_code'] .
                ", capsule_url=" . $this->_get_capsule_api_url);
        } else {
            // successful request parse out capsule
            $this->_capsule_response = $request['response'];
            $this->capsule = deserializeCapsuleJson($this->_capsule_response);
        }

        // if we are here we got a response so let's return it
        $this->response_time = round($request['info']['total_time'] * 1000);
        $this->connectTime = round(microtime(true) * 1000) - $startTime;
    }

    protected function generateEndingTags($blockType, $node_type, $publishingEngine,
        $engineVersion, $metaString, $publishedTimeEpochMilliseconds, $elapsedTime) {
        $sb = "";
        if ($blockType == self::$CLOSE_BLOCKTYPE) {
            $sb .= "\n<ul id=\"be_sdkms_capsule\" style=\"display:none!important\">\n";
            if (count($this->errorMessages) > 0) {
                $sb .= "    <ul id=\"be_sdkms_capsule_errors\">\n";
                foreach ($this->errorMessages as $error_msg) {
                    $sb .= "        <li id=\"error_msg\">" . $error_msg . "</li>\n";
                }
                $sb .= "    </ul>\n";
            }
            if ($this->debugMode) {
                $sb .= "   <li id=\"be_sdkms_sdk_version\">" . self::$CLIENT_NAME . "_" . self::$CLIENT_VERSION . "</li>\n";
                $sb .= "   <li id=\"be_sdkms_original_url\">" . $this->_original_url . "</li>\n";
                $sb .= "   <li id=\"be_sdkms_normalized_url\">" . $this->_normalized_url . "</li>\n";
                $sb .= "   <li id=\"be_sdkms_configuration\">" . print_r($this->config, true) . "</li>\n";
                $sb .= "   <li id=\"be_sdkms_capsule_url\">" . $this->_get_capsule_api_url . "</li>\n";
                $sb .= "   <li id=\"be_sdkms_capsule_response\">\n// <!--\n" . $this->_capsule_response . "\n-->\n</li>\n";
            }

            $sb .= "</ul>\n";
        } else {
            // capsule information only applies to init block
            if ($blockType == self::$INIT_BLOCKTYPE) {
                $sb .= "\n<ul id=\"be_sdkms_capsule\" style=\"display:none!important\">\n";
                $sb .= "    <li id=\"be_sdkms_capsule_connect_timer\">" . $this->connectTime . " ms</li>\n";
                $sb .= "    <li id=\"be_sdkms_capsule_index_time\">" . $this->convertToNormalizedGoogleIndexTimeZone(round(microtime(true) * 1000), "i") .
                    "</li>\n";
                if ($this->capsule != null) {
                    $sb .= "    <li id=\"be_sdkms_capsule_pub\">" . $this->capsule->getPublishingEngine() . "_" .
                    $this->capsule->getVersion() . "</li>\n";
                    $sb .= "    <li id=\"be_sdkms_capsule_date_modified\">" . $this->convertToNormalizedGoogleIndexTimeZone($this->capsule->getDatePublished(), "p") .
                        "</li>\n";
                }

                $sb .= "</ul>\n";
            }
            // node information
            $publisherLine = $publishingEngine . "_" . $engineVersion . "; " . $node_type;
            if ($metaString != null) {
                $publisherLine .= "; " . $metaString;
            }
            $sb .= "<ul id=\"be_sdkms_node\" style=\"display:none!important\">\n";
            $sb .= "   <li id=\"be_sdkms_pub\">" . $publisherLine . "</li>\n";
            $sb .= "   <li id=\"be_sdkms_date_modified\">" . $this->convertToNormalizedTimeZone($publishedTimeEpochMilliseconds, "pn") . "</li>\n";
            $sb .= "   <li id=\"be_sdkms_timer\">" . $elapsedTime . " ms</li>\n";
            $sb .= "</ul>\n";
        }

        return $sb;
    }

    /**
     * Return date in this form: iy_2017; im_36; id_21; ih_11; imh_36; i_epoch:1503340561789
     * This function is not thread safe (PHP doesn't support this today)
     */
    public function convertToNormalizedGoogleIndexTimeZone($epochTimeInMillis, $prefix) {
        $sb = "";
        $current_timezone = date_default_timezone_get();
        try {
            date_default_timezone_set(self::$NORMALIZED_TIMEZONE);
            $sb .= strftime("${prefix}y_%Y; ${prefix}m_%M; ${prefix}d_%d; ${prefix}h_%H; ${prefix}mh_%M; ", $epochTimeInMillis / 1000);
            $sb .= "${prefix}_epoch:" . $epochTimeInMillis;
            return $sb;
        } finally {
            date_default_timezone_set($current_timezone);
        }
    }

    public function convertToNormalizedTimeZone($epochTimeInMillis, $prefix) {
        $sb = "";
        $current_timezone = date_default_timezone_get();
        try {
            date_default_timezone_set(self::$NORMALIZED_TIMEZONE);
            $sb .= strftime("${prefix}_tstr: %a %b %d %H:%M:%S PST %Y; ", $epochTimeInMillis / 1000);
            $sb .= "${prefix}_epoch:" . $epochTimeInMillis;
            return $sb;
        } finally {
            date_default_timezone_set($current_timezone);
        }
    }

    public function getInitString() {
        $sb = "";
        $startTime = round(microtime(true) * 1000);
        $publishingEngine = self::$DEFAULT_PUBLISHING_ENGINE;
        $engineVersion = self::$DEFAULT_ENGINE_VERSION;
        $metaString = self::$DEFAULT_ENGINE_METASTRING;
        $publishedTime = round(microtime(true) * 1000);

        if ($this->capsule) {
            $initStringNode = $this->capsule->getInitStringNode();
            if ($initStringNode) {
                $sb .= $initStringNode->getContent();
                $publishedTime = $initStringNode->getDatePublished();
                $publishingEngine = $initStringNode->getPublishingEngine();
                $engineVersion = $initStringNode->getPublishingEngine();
                $metaString = $initStringNode->getMetaString();
            } else {
                array_push($this->errorMessages,
                    'Capsule missing initstr node');
            }
        }
        $elapsedTime = round(microtime(true) * 1000) - $startTime;
        $sb .= $this->generateEndingTags(self::$INIT_BLOCKTYPE, "init_str", $publishingEngine, $engineVersion, $metaString, $publishedTime, $elapsedTime);
        return $sb;

    }

    public function getFeatureString($node_type, $target_div_id) {
        $sb = "";
        $startTime = round(microtime(true) * 1000);
        $publishingEngine = self::$DEFAULT_PUBLISHING_ENGINE;
        $engineVersion = self::$DEFAULT_ENGINE_VERSION;
        $metaString = self::$DEFAULT_ENGINE_METASTRING;
        $publishedTime = round(microtime(true) * 1000);

        if ($this->capsule) {
            $node = $this->capsule->getLinkBlockNode($target_div_id);
            if ($node) {
                $sb .= $node->getContent();
                $publishedTime = $node->getDatePublished();
                $publishingEngine = $node->getPublishingEngine();
                $engineVersion = $node->getPublishingEngine();
                $metaString = $node->getMetaString();
            } else {
                array_push($this->errorMessages,
                    'Capsule missing ' . $node_type . ' node, target_div_id ' . $target_div_id);
            }
        }

        $elapsedTime = round(microtime(true) * 1000) - $startTime;
        $sb .= $this->generateEndingTags(self::$OTHER_BLOCKTYPE, $node_type, $publishingEngine, $engineVersion, $metaString, $publishedTime, $elapsedTime);
        return $sb;
    }

    public function close() {
        if ($this->capsule) {
            $redirectNode = $this->capsule->getRedirectNode();
            if ($redirectNode != null) {
                header("Location: " . $redirectNode->getRedirectURL());
//                this . _servlet_response . setStatus(redirectNode . getRedirectType());
            }
        }

        $sb = "";
        $sb .= $this->generateEndingTags(self::$CLOSE_BLOCKTYPE, null, null, null, null, 0, 0);
        return $sb;
    }
}

function deserializeCapsuleJson($capsule_json) {
    $capsule_array = json_decode($capsule_json);
    $capsule = new Capsule();
//    print_r($capsule_array);

    $capsule->setVersion($capsule_array->capsule_version);
    $capsule->setAccountId($capsule_array->account_id);
    $capsule->setDateCreated((float) $capsule_array->date_created);
    $capsule->setDatePublished((float) $capsule_array->date_published);
    $capsule->setPublishingEngine($capsule_array->publishing_engine);

    $node_list = array();
    foreach ($capsule_array->nodes as $node_obj) {
        $node = new Node();
        $node->setType($node_obj->type);
        $node->setPublishingEngine($node_obj->publishing_engine);
        $node->setEngineVersion($node_obj->engine_version);
        if (isset($node_obj->meta_string)) {
            $node->setMetaString($node_obj->meta_string);
        }
        $node->setDateCreated((float) $node_obj->date_created);
        $node->setDatePublished((float) $node_obj->date_published);

        // no content for redirect type only for initstr and linkblock
        if (isset($node_obj->content)) {
            $node->setContent($node_obj->content);
        }

        if (isset($node_obj->content)) {
            $node->setContent($node_obj->content);
        }

        if (isset($node_obj->targeted_id)) {
            $node->setTargetedId($node_obj->targeted_id);
        }

        if (isset($node_obj->redirect_type)) {
            $node->setRedirectType($node_obj->redirect_type);
        }

        if (isset($node_obj->redirect_url)) {
            $node->setRedirectURL($node_obj->redirect_url);
        }

        array_push($node_list, $node);
    }
    $capsule->setCapsuleNodeList($node_list);
    return $capsule;
}

class Node {
    protected $type;
    protected $dateCreated;
    protected $datePublished;
    protected $publishingEngine;
    protected $engineVersion;
    protected $metaString;
    protected $content;
    // only applies to linkblock type
    protected $targeted_id;
    // only applies to redirect type
    private $redirectType;
    private $redirectURL;

    public static $INITSTR_NODE_TYPE = "initstr";
    public static $REDIRECT_NODE_TYPE = "redirect";
    public static $LINKBLOCK_NODE_TYPE = "linkblock";

    public function __construct() {
    }

    public function getType() {
        return $this->type;
    }

    public function setType($type) {
        $this->type = $type;
    }

    public function getTargetedId() {
        return $this->targeted_id;
    }

    public function setTargetedId($targeted_id) {
        $this->targeted_id = $targeted_id;
    }

    public function getDateCreated() {
        return $this->dateCreated;
    }

    public function setDateCreated($dateCreated) {
        $this->dateCreated = $dateCreated;
    }
    public function getDatePublished() {
        return $this->datePublished;
    }
    public function setDatePublished($datePublished) {
        $this->datePublished = $datePublished;
    }
    public function getPublishingEngine() {
        $this->publishingEngine;
    }
    public function setPublishingEngine($publishingEngine) {
        $this->publishingEngine = $publishingEngine;
    }
    public function getEngineVersion() {
        return $this->engineVersion;
    }
    public function setEngineVersion($engineVersion) {
        $this->engineVersion = $engineVersion;
    }
    public function getMetaString() {
        return $this->metaString;
    }
    public function setMetaString($metaString) {
        $this->metaString = $metaString;
    }
    public function getContent() {
        return $this->content;
    }
    public function setContent($content) {
        $this->content = $content;
    }
    public function getRedirectType() {
        return $this->redirectType;
    }
    public function setRedirectType($redirectType) {
        $this->redirectType = $redirectType;
    }
    public function getRedirectURL() {
        return $this->redirectURL;
    }
    public function setRedirectURL($redirectURL) {
        $this->redirectURL = $redirectURL;
    }
}

class Capsule {
    protected $accountId;
    protected $publishingEngine;
    protected $dateCreated;
    protected $datePublished;
    protected $version;
    protected $capsuleNodeList;

    public function __construct() {
        $this->capsuleNodeList = null;
    }

    public function getInitStringNode() {
        if ($this->capsuleNodeList == null) {
            return null;
        }
        foreach ($this->capsuleNodeList as $node) {
            if ($node->getType() == Node::$INITSTR_NODE_TYPE) {
                return $node;
            }
        }
        return null;
    }

    public function getRedirectNode() {
        if ($this->capsuleNodeList == null) {
            return null;
        }
        foreach ($this->capsuleNodeList as $node) {
            if ($node->getType() == Node::$REDIRECT_NODE_TYPE) {
                return $node;
            }
        }
        return null;
    }

    public function getLinkBlockNode($targetedId) {
        if ($this->capsuleNodeList == null) {
            return null;
        }
        foreach ($this->capsuleNodeList as $node) {
            if ($node->getType() == Node::$LINKBLOCK_NODE_TYPE && $node->getTargetedId() == $targetedId) {
                return $node;
            }
        }
        return null;
    }

    public function getCapsuleNodeList() {
        return $this->capsuleNodeList;
    }

    public function setCapsuleNodeList($capsuleNodeList) {
        $this->capsuleNodeList = $capsuleNodeList;
    }

    public function getVersion() {
        return $this->version;
    }

    public function setVersion($version) {
        $this->version = $version;
    }

    public function getAccountId() {
        return $this->accountId;
    }

    public function getPublishingEngine() {
        return $this->publishingEngine;
    }

    public function setPublishingEngine($publishingEngine) {
        $this->publishingEngine = $publishingEngine;
    }

    public function setAccountId($accountId) {
        $this->accountId = $accountId;
    }

    public function getDateCreated() {
        return $this->dateCreated;
    }

    public function setDateCreated($dateCreated) {
        $this->dateCreated = $dateCreated;
    }

    public function getDatePublished() {
        return $this->datePublished;
    }

    public function setDatePublished($datePublished) {
        $this->datePublished = $datePublished;
    }
}

class IXFSDKUtils {
    public static function getSignedNumber($number) {
        $bitLength = 32;
        $mask = pow(2, $bitLength) - 1;
        $testMask = 1 << ($bitLength - 1);
        if (($number & $testMask) != 0) {
            return $number | ~$mask;
        } else {
            return $number & $mask;
        }
    }

    /**
     * Convert url to a hash number, this func is to match JS version for IX link block
     */
    public static function getPageHash($url) {
        $hash = 0;

        $strlen = strlen($url);

        for ($i = 0; $i < $strlen; $i++) {
            $char = substr($url, $i, 1);
            $characterOrd = ord($char);
            $temp1 = self::getSignedNumber($hash << 5);
            $temp2 = self::getSignedNumber($temp1 - $hash);
            $hash = self::getSignedNumber($temp2 + $characterOrd);
            $hash = self::getSignedNumber($hash & $hash);
//            echo "Round 1 char=" . $characterOrd . ", temp1=" . $temp1 . ", temp2=" . $temp2 . ", hash=" . $hash . "\n";
        }

        // if hash is a negative number, remove '-' and append '0' in front
        if ($hash < 0) {
            return "0" . -$hash;
        } else {
            return $hash;
        }
    }

    private static function proper_parse_str($str) {
        # result array
        $arr = array();

        # split on outer delimiter
        $pairs = explode('&', $str);

        # loop through each pair
        foreach ($pairs as $i) {
            # split into name and value
            list($name, $value) = explode('=', $i, 2);

            # if name already exists
            if (isset($arr[$name])) {
                # stick multiple values into an array
                if (is_array($arr[$name])) {
                    $arr[$name][] = $value;
                } else {
                    $arr[$name] = array($arr[$name], $value);
                }
            }
            # otherwise, simply stick it in a scalar
            else {
                $arr[$name] = $value;
            }
        }

        # return result array
        return $arr;
    }

    public static function overrideHostInURL($url, $canonicalHost) {
        $url_parts = parse_url($url);
        $url_parts['host'] = $canonicalHost;
        $url = (isset($url_parts['scheme']) ? "{$url_parts['scheme']}:" : '') .
            ((isset($url_parts['user']) || isset($url_parts['host'])) ? '//' : '') .
            (isset($url_parts['user']) ? "{$url_parts['user']}" : '') .
            (isset($url_parts['pass']) ? ":{$url_parts['pass']}" : '') .
            (isset($url_parts['user']) ? '@' : '') .
            (isset($url_parts['host']) ? "{$url_parts['host']}" : '') .
            (isset($url_parts['port']) ? ":{$url_parts['port']}" : '') .
            (isset($url_parts['path']) ? "{$url_parts['path']}" : '') .
            (isset($url_parts['query']) ? "?{$url_parts['query']}" : '') .
            (isset($url_parts['fragment']) ? "#{$url_parts['fragment']}" : '');
        return $url;

    }

    public static function normalizeURL($url, $whitelistParameters) {
        $url_parts = parse_url($url);
        $normalized_url = $url_parts['scheme'] . '://' . $url_parts['host'];
        if (isset($url_parts['port'])) {
            if (!(($url_parts['scheme'] == 'http' && $url_parts['port'] == 80) ||
                ($url_parts['scheme'] == 'https' && $url_parts['port'] == 443))) {
                $normalized_url .= ':' . $url_parts['port'];
            }
        }
//        print_r($url_parts);
        $normalized_url .= $url_parts['path'];
        if ($whitelistParameters != null && count($whitelistParameters) > 0 && isset($url_parts['query'])) {
            $query_string_keep = array();
            $qs_array = self::proper_parse_str($url_parts['query']);
            foreach ($qs_array as $key => $value) {
//                echo "Checking $key found=" . in_array($key, $whitelistParameters) . "\n";
                if (in_array($key, $whitelistParameters)) {
                    $query_string_keep[$key] = $value;
                }
            }
//            print_r($query_string_keep);
            if (count($query_string_keep) > 0) {
                $normalized_url .= "?";
                $first = true;
                foreach ($query_string_keep as $key => $value) {
                    if (is_array($value)) {
                        foreach ($value as $value_scalar) {
                            if (!$first) {
                                $normalized_url .= "&";
                            }
                            $normalized_url .= $key . "=" . $value_scalar;
                            if ($first) {
                                $first = false;
                            }
                        }
                    } else {
                        if (!$first) {
                            $normalized_url .= "&";
                        }
                        $normalized_url .= $key . "=" . $value;
                    }
                    if ($first) {
                        $first = false;
                    }
                }
            }
        }
        return $normalized_url;
    }

    public static function userAgentMatchesRegex($user_agent, $user_agent_regex) {
        if (preg_match("/" . $user_agent_regex . "/i", $user_agent)) {
            return true;
        }
        return false;
    }

}
