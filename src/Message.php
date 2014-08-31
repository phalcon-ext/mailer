<?php
/**
 * Message.php 2014-08-31 04:50
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


/**
 * Class Message
 * @package sKSoft\Phalcon\Mailer
 */
class Message
{
	/**
	 * @var Mailer
	 */
	protected $mailer;

	/**
	 * @var \Swift_Message
	 */
	protected $message;

	/**
	 * @var array
	 */
	protected $failedRecipients = [];

	/**
	 * @param Mailer $mailer
	 */
	public function __construct(Mailer $mailer)
	{
		$this->mailer = $mailer;
	}

	/**
	 * @param $email
	 * @param null $name
	 *
	 * @return $this
	 */
	public function from($email, $name = null)
	{
		$this->getMessage()->addFrom($email, $name);

		return $this;
	}

	/**
	 * @param $email
	 * @param null $name
	 *
	 * @return $this
	 */
	public function replyTo($email, $name = null)
	{
		$this->getMessage()->addReplyTo($email, $name);

		return $this;
	}

	/**
	 * @param $email
	 * @param null $name
	 *
	 * @return $this
	 */
	public function to($email, $name = null)
	{
		$this->getMessage()->addTo($email, $name);

		return $this;
	}


	/**
	 * @param $email
	 * @param null $name
	 *
	 * @return $this
	 */
	public function cc($email, $name = null)
	{
		$this->getMessage()->addCc($email, $name);

		return $this;
	}

	/**
	 * @param $email
	 * @param null $name
	 *
	 * @return $this
	 */
	public function bcc($email, $name = null)
	{
		$this->getMessage()->addBcc($email, $name);

		return $this;
	}

	/**
	 * @param $email
	 * @param null $name
	 *
	 * @return $this
	 */
	public function sender($email, $name = null)
	{
		$this->getMessage()->setSender($email, $name);

		return $this;
	}

	/**
	 * @param $subject
	 *
	 * @return $this
	 */
	public function subject($subject)
	{
		$this->getMessage()->setSubject($subject);

		return $this;
	}

	/**
	 * @param $content
	 * @param null $contentType
	 * @param null $charset
	 *
	 * @return $this
	 */
	public function content($content, $contentType = null, $charset = null)
	{
		$this->getMessage()->setBody($content, $contentType, $charset);

		return $this;
	}

	/**
	 * @param $contentType
	 *
	 * @return $this
	 */
	public function contentType($contentType)
	{
		$this->getMessage()->setContentType($contentType);

		return $this;
	}

	/**
	 * @param $charset
	 *
	 * @return $this
	 */
	public function charset($charset)
	{
		$this->getMessage()->setCharset($charset);

		return $this;
	}

	/**
	 * @param $priority
	 *
	 * @return $this
	 */
	public function priority($priority)
	{
		$this->getMessage()->setPriority($priority);

		return $this;
	}

	/**
	 * @return bool
	 */
	public function send()
	{
		$eventManager = $this->mailer->getEventsManager();

		if($eventManager)
			$result = $eventManager->fire('mailer:beforeSend', $this);
		else
			$result = true;

		if($result)
		{
			$this->failedRecipients = [];
			$result = $this->getSwift()->send($this->getMessage(), $this->failedRecipients);

			if($eventManager)
				$eventManager->fire('mailer:afterSend', $this);

			return (bool)$result;
		}
		else
			return false;
	}


	/**
	 * @return \Swift_Mailer
	 */
	protected function getSwift()
	{
		return $this->mailer->getSwift();
	}

	/**
	 * @return \Swift_Message
	 */
	protected function getMessage()
	{
		if(!$this->message)
			$this->message = $this->getSwift()->createMessage();

		return $this->message;
	}
} 