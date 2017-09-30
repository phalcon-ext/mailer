# CHANGELOG

## v2.1.0 (2017-09-30)
- Fix [#7](https://github.com/phalcon-ext/mailer/issues/7)
- Add support `auth_mode` [#6](https://github.com/phalcon-ext/mailer/issues/6)
- Allow composers use Swiftmailer v6 [#8](https://github.com/phalcon-ext/mailer/pull/8)
- Change license LGPL-3.0 to MIT

## v2.0.3 (2015-12-18)
- Added method `contentAlternative()` - optionally an alternative text body 
- fix [#5](https://github.com/phalcon-ext/mailer/issues/5)

## v2.0.2 (2015-03-03)
- fix [#2](https://github.com/phalcon-ext/mailer/issues/2)

## v2.0.1 (2014-11-05)
- Changes in the private logic...
  - Added protected method `isInitSwiftMailer()`
  - Added lazy init `SwiftMailer`
  - Added check for the required of dependency injection object
  
## v2.0.0 (2014-10-16)
- rename root namespace `\Phalcon\Mailer` to `\Phalcon\Ext\Mailer`
- remove deprecated method `getMessage()` in class `\Phalcon\Ext\Mailer\Message`

## v1.1.0 (2014-10-16)
- added method `getSwiftMessage()` in class `\Phalcon\Mailer\Message` analogue for `getMessage()`
- method `getMessage()` in class `\Phalcon\Mailer\Message` is deprecated (will removed since 2.0.0)

## v1.0.2 (2014-09-17)
- fixed bug when you create a message via View (incorrect namespace)

## v1.0.1 (2014-09-15)
- fixed bug in the attachment file.
- In `\Phalcon\Mailer\Message`, creation SwiftMailer of instances moved to the DI

## v1.0.0 (2014-09-14)
- Release of component :) 
