# Changelog

## [Unreleased]

## [1.5.11] - 2023-06-30

### Update

- Added Support for Drupal 10 / php8.2

## [1.5.10] - 2022-12-30

### Update

- Allow configuration of debug mode using $ALLOW_DEBUG_MODE_CONFIG
- example usecase: $be_ixf_config[BEIXFClient::$ALLOW_DEBUG_MODE_CONFIG] = false;

## [1.5.9] - 2022-09-29

### Update

- Use default value (2) of CURLOPT_SSL_VERIFYHOST to check the SSL
## [1.5.8] - 2022-07-07

### Update

- Update default direct api endpoint and rectify deprecation warning related to Implicit conversion from float to int
## [1.5.7] - 2022-06-22

### Update

- Added support for php8.x for date format

## [1.5.6] - 2022-04-27

### Fixed

- Remove output comment from SDK GetBodyString call()

## [1.5.5] - 2022-02-10

### Fixed

- Drop parameters containing SQL queries

## [1.5.4] - 2021-05-27

### Update

- Update default api endpoint

## [1.5.3] - 2020-12-14

### Fixed

- HTTP_HOST warning message
- Remove proxy password

## [1.5.2] - 2020-06-15
### Added
- Support api.brightedge.com endpoint

## [1.5.1] - 2020-04-29
### Added
- Add ixf-endpoint to URL parameters
- Add validateGetBoolValue function to validate the parameters in URL

## [1.5.0] - 2020-04-09
### Added
- Expose $ALLOW_DEBUG_MODE as a SDK configuration to customers
- Remove ixf-endpoint from URL parameters

## [1.4.29] - 2020-01-27
### Fixed
- Fix orphan ul tag

## [1.4.28] - 2019-11-15
### Added
- Add displayCapsuleUrl

## [1.4.27] - 2019-10-21
### Updated
- Diagnostic info update

## [1.4.26] - 2019-07-19
### Fixed
- Removed partial Encryption and added capsule url in Meta

## [1.4.25] - 2019-06-17
### Fixed
- htmlentities orginal and normalized diagnostic urls

## [1.4.24] - 2019-06-11
### Fixed
- urlencode diagnostic urls
- api endpoints change regex

## [1.4.23] - 2019-05-30
### Fixed
- PHP error on null capsule response

## [1.4.22] - 2019-05-20
### Added
- IXF enpoints to be protected
- connection timeout to be defaulted to 1000ms if value lesser than 1000ms set in config

## [1.4.21] - 2019-04-15
### Added
- Diagnostic type and diagnostic string configurations
- getHeadOpen method - Update with more meta tags, having more diagnostic information
- AES encryption added to be:diag meta tag
- be_ixf comment tag prefixed each time a block is written to the page
- Google-indexable diagnostic string (sdk_is) is added to the close method
- Error messages that were previously in the Close string will now be included as 'messages' in be:diag meta tag
- All diagnostic output from node-specific strings are removed
- When debug mode is enabled in the url, getBodyOpen will show diagnostic information along with capsule response
- getCleanString method -  will return the string from the capsule with no extra meta info or comment tag

## [Unreleased]
## [1.4.20] - 2019-02-20
### Added
- Support to have exclude rules at page_group level maitaining backward compatibility

## [1.4.19] - 2019-01-24
### Fixed
- Fixed canonical protocol/ canonical host key not found bug

## [1.4.18] - 2019-01-08
### Added
- Update ixfd endpoint
- Update default timeouts connect/ socket Timeout to 1000ms and crawlerSocketTimeout to 1000ms
- add url parameter ixf-endpoint for testing

## [1.4.17] - 2018-10-18
### Added
- Determine page group using normalized url
- Override nodes with page_group_nodes if page group for the url is not null
- tagFormat optional parameter to hide default comments with bodystr
- hasFeatureString method - change definition to return false in case of empty string

## [1.4.16] - 2018-08-29
### Added
- PAGE_ALIAS_URL config key : Page_alias property overrides original url
- Expose CANONICAL_HOST/ CANONICAL_PROTOCOL properties, remove setCanonicalHost function
- Modify logic to apply protocol override and canonical host override after applying page_alias / canonical url

## [1.4.15] - 2018-07-16
### Added
- PAGE_HIDE_ORIGINALURL config key
- Fixed redirects not working bug

## [1.4.14] - 2018-06-29
### Added
- Cleaned up more warnings

## [1.4.13] - 2018-06-27
### Added
- Public accessor to get capsule URL

## [1.4.12] - 2018-06-19
### Added
- Allow missing user agent

## [1.4.11] - 2018-04-27
### Fixed
- normalizing of URL with same key should sort the value

## [1.4.10] - 2018-04-19
### Added
- Use CData around capsule payload
- New forcedirectapi.parameter.list parameter to force direct api without caching

## [1.4.9] - 2018-02-28
### Added
- Use X-Forwarded-Proto as additional determination of https

## [1.4.8] - 2018-02-27
### Fixed
- Added CANONICAL_PROTOCOL_CONFIG

## [1.4.7] - 2018-02-23
### Fixed
- Address issue when $_SERVER[HTTP] is missing
## [1.4.6] - 2018-02-08
### Added
- apply config rules to original url fix
- add brightedge user agent

## [1.4.5] - 2018-02-02
### Added
- ixf-disable-redirect parameter turns off redirect

## [1.4.4] - 2018-02-01
### Fixed
- regex escaping
### Added
- case insensitive rule flag

## [1.4.3] - 2018-01-30
### Changed
- properly indicate invalid JSON

## [1.4.2] - 2018-01-24
### Added
- support for redirect_rules
