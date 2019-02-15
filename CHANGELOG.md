# Changelog

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
