<?php
/**
 * MailerService.php 2014-08-31 04:11
 * ----------------------------------------------
 * 
 *
 * @author      Stanislav Kiryukhin <korsar.zn@gmail.com>
 * @copyright   Copyright (c) 2014, CKGroup.ru
 *
 * @version 	0.0.1
 * ----------------------------------------------
 * All Rights Reserved.
 * ----------------------------------------------
 */
namespace sKSoft\Phalcon\Mailer;

use Phalcon\Mvc\User\Component;
use Phalcon\Mvc\View;
use Phalcon\Config;

/**
 * Class Mailer
 * @package sKSoft\Phalcon\Mailer
 */
class Mailer extends Component
{
	protected $config;

	/**
	 * @param array $config
	 */
	public function __construct(Array $config = null)
	{
		$this->configure($config);
		$this->registerSwiftMailer();
	}

	/**
	 * @return \Swift_Mailer
	 */
	public function getSwift()
	{
		return $this->getDI()->get('swift.mailer');
	}

	public function createMessageViaView($view, $params = [])
	{
		$message = $this->createMessage();
		$message->content($this->renderView($view, $params), 'text/html');


		return $message;
	}

	/**
	 * @return \sKSoft\Phalcon\Mailer\Message
	 */
	public function createMessage()
	{
		$eventsManager = $this->getEventsManager();

		if($eventsManager)
			$eventsManager->fire('mailer:beforeCreateMessage', $this);

		$message = $this->getDI()->get('\sKSoft\Phalcon\Mailer\Message', [$this]);

		if($eventsManager)
			$eventsManager->fire('mailer:afterCreateMessage', $this, [$message]);

		return $message;
	}


	protected function registerSwiftMailer()
	{
		$this->getDI()->set('swift.mailer', function()
		{
			$this->registerSwiftTransport();
			return new \Swift_Mailer($this->di['swift.transport']);

		}, true);
	}


	protected function registerSwiftTransport()
	{
		switch($this->getConfig('driver'))
		{
			case 'smtp':
				$this->registerTransportSmtp();
			break;

			case 'mail':
				$this->registerTransportMail();
			break;

			case 'sendmail':
				$this->registerTransportSendmail();
			break;
		}
	}

	protected function registerTransportSmtp()
	{
		$this->getDI()->set('swift.transport', function()
		{
			$config = $this->getConfig();

			$transport = \Swift_SmtpTransport::newInstance($config['host'], $config['port']);

			if($config['encryption'])
				$transport->setEncryption($config['encryption']);


			if($config['username'])
			{
				$transport->setUsername($config['username']);
				$transport->setPassword($config['password']);
			}

			return $transport;

		}, true);
	}

	protected function registerTransportSendmail()
	{
		$this->getDI()->set('swift.transport', function()
		{
			return \Swift_SendmailTransport::newInstance($this->getConfig('sendmail', '/usr/sbin/sendmail -bs'));

		}, true);

	}

	protected function registerTransportMail()
	{
		$this->getDI()->set('swift.transport', function()
		{
			return \Swift_MailTransport::newInstance();
		});
	}

	/**
	 * @param $config
	 */
	protected function configure(Array $config)
	{
		if($config === null)
			$this->config = (array)$this->getDI()->get('config')->mail->toArray();
		else
			$this->config = $config;
	}

	/**
	 * @param null $key
	 * @param null $default
	 *
	 * @return mixed
	 */
	protected function getConfig($key = null, $default = null)
	{
		if($key !== null)
		{
			if(isset($this->config[$key]))
				return $this->config[$key];
			else
				return $default;
		}
		else
			return $this->config;
	}

	/**
	 * @param $view
	 * @param $params
	 *
	 * @return string
	 */
	protected function renderView($view, $params)
	{
		ob_start();
		$this->getView()->partial($view, $params);

		$content = ob_get_contents();
		ob_end_clean();

		return $content;
	}

	/**
	 * @return View
	 */
	protected function getView()
	{
		return $this->getDI()->get('view');
	}
} 