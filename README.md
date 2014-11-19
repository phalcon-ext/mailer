Phalcon\Ext\Mailer
==============

Mailer wrapper over SwiftMailer for Phalcon.

## Installing ##

Install composer in a common location or in your project:

    curl -s http://getcomposer.org/installer | php

Create the composer.json file as follows:

```
{
	"require": {
		"phalcon-ext/mailer": "~2.0"
	}
}
```

Run the composer installer:

```bash
php composer.phar install
```

Add in your the code

    require_once('vendor/autoload.php');

## Configure ##

**SMTP**

```php
$config = [
    'driver' 	 => 'smtp',
    'host'	 	 => 'smtp.gmail.com',
    'port'	 	 => 465,
    'encryption' => 'ssl',
    'username'   => 'example@gmail.com',
    'password'	 => 'your_password',
    'from'		 => [
    		'email' => 'example@gmail.com',
    		'name'	=> 'YOUR FROM NAME'
    	]
];
```

**Sendmail**

```php
$config = [
    'driver' 	 => 'sendmail',
	'sendmail' 	 => '/usr/sbin/sendmail -bs',
    'from'		 => [
    	'email' => 'example@gmail.com',
    	'name'	=> 'YOUR FROM NAME'
    ]
];
```

**PHP Mail**

```php
$config = [
    'driver' 	 => 'mail',
    'from'		 => [
    	'email' => 'example@gmail.com',
    	'name'	=> 'YOUR FROM NAME'
    ]
];
```

## Example ##

### createMessage() ###

```php
$mailer = new \Phalcon\Ext\Mailer\Manager($config);

$message = $mailer->createMessage()
		->to('example_to@gmail.com', 'OPTIONAL NAME')
		->subject('Hello world!')
		->content('Hello world!');

// Set the Cc addresses of this message.
$message->cc('example_cc@gmail.com');

// Set the Bcc addresses of this message.
$message->bcc('example_bcc@gmail.com');

// Send message
$message->send();
```

### createMessageFromView() ###

```php
/**
 * Global viewsDir for current instance Mailer\Manager.
 * 
 * This parameter is OPTIONAL, If it is not specified, 
 * use DI from view service (getViewsDir)
 */

$config['viewsDir'] = __DIR__ . '/views/email/';

$mailer = new \Phalcon\Ext\Mailer\Manager($config);

// view relative to the folder viewsDir (REQUIRED)
$viewPath = 'email/example_message';

// Set variables to views (OPTIONAL)
$params [ 
	'var1' => 'VAR VALUE 1',
	'var2' => 'VAR VALUE 2',
	...
	'varN' => 'VAR VALUE N',
];

/**
 * The local path to the folder viewsDir only this message. (OPTIONAL)
 * 
 * This parameter is OPTIONAL, If it is not specified, 
 * use global parameter "viewsDir" from configuration.
 */
$viewsDirLocal = __DIR__ . '/views/email/local/';


$message = $mailer->createMessageFromView($viewPath, $params, $viewsDirLocal)
		->to('example_to@gmail.com', 'OPTIONAL NAME')
		->subject('Hello world!');

// Set the Cc addresses of this message.
$message->cc('example_cc@gmail.com');

// Set the Bcc addresses of this message.
$message->bcc('example_bcc@gmail.com');

// Send message
$message->send();
```

## Events ##
- mailer:beforeCreateMessage
- mailer:afterCreateMessage
- mailer:beforeSend
- mailer:afterSend
- mailer:beforeAttachFile
- mailer:afterAttachFile
