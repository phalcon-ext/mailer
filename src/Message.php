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
	 * Attach a file to the message.
	 *
	 * @param  string  $file
	 * @param  array   $options
	 *
	 * @return Message
	 */
	public function attachment($file, Array $options = [])
	{
		$attachment = $this->createAttachmentViaPath($file);
		return $this->prepareAttachment($attachment, $options);
	}

	/**
	 * Attach in-memory data as an attachment.
	 *
	 * @param  string  $data
	 * @param  string  $name
	 * @param  array   $options
	 *
	 * @return Message
	 */
	public function attachData($data, $name, Array $options = [])
	{
		$attachment = $this->createAttachmentViaData($data, $name);
		return $this->prepareAttachment($attachment, $options);
	}

	/**
	 * Embed a file in the message and get the CID.
	 *
	 * @param  string  $file
	 *
	 * @return string
	 */
	public function embed($file)
	{
		$embed = $this->createEmbedViaPath($file);
		return $this->getMessage()->embed($embed);
	}

	/**
	 * Embed in-memory data in the message and get the CID.
	 *
	 * @param  string  $data
	 * @param  string  $name
	 * @param  string  $contentType
	 *
	 * @return string
	 */
	public function embedData($data, $name, $contentType = null)
	{
		$embed = $this->createEmbedViaData($data, $name, $contentType);
		return $this->getMessage()->embed($embed);
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
	 * @return \Swift_Message
	 */
	public function getMessage()
	{
		if(!$this->message)
			$this->message = $this->getSwift()->createMessage();

		return $this->message;
	}

	/**
	 * @return \Swift_Mailer
	 */
	protected function getSwift()
	{
		return $this->mailer->getSwift();
	}

	/**
	 * Prepare and attach the given attachment.
	 *
	 * @param  \Swift_Attachment  $attachment
	 * @param  array  $options
	 *
	 * @return Message
	 */
	protected function prepareAttachment(\Swift_Attachment $attachment, Array $options = [])
	{
		if(isset($options['mime']))
			$attachment->setContentType($options['mime']);

		if(isset($options['as']))
			$attachment->setFilename($options['as']);

		$eventManager = $this->mailer->getEventsManager();

		if($eventManager)
			$result = $eventManager->fire('mailer:beforeAttachFile', $this, [$attachment]);
		else
			$result = true;

		if($result)
		{
			$this->getMessage()->attach($attachment);

			if($eventManager)
				$eventManager->fire('mailer:afterAttachFile', $this, [$attachment]);
		}

		return $this;
	}

	/**
	 * Create a Swift Attachment instance.
	 *
	 * @param  string  $file
	 *
	 * @return \Swift_Attachment
	 */
	protected function createAttachmentViaPath($file)
	{
		return \Swift_Attachment::fromPath($file);
	}

	/**
	 * Create a Swift Attachment instance from data.
	 *
	 * @param  string  $data
	 * @param  string  $name
	 *
	 * @return \Swift_Attachment
	 */
	protected function createAttachmentViaData($data, $name)
	{
		return \Swift_Attachment::newInstance($data, $name);
	}


	/**
	 * @param $file
	 *
	 * @return \Swift_Image
	 */
	protected function createEmbedViaPath($file)
	{
		return \Swift_Image::fromPath($file);
	}

	/**
	 * @param $data
	 * @param $name
	 * @param null $contentType
	 *
	 * @return \Swift_Image
	 */
	protected function createEmbedViaData($data, $name, $contentType = null)
	{
		return \Swift_Image::newInstance($data, $name, $contentType);
	}
} 