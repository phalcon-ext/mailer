<?php
/**
 * Manager.php 2014-08-31 04:11
 * ----------------------------------------------
 *
 * @author      Stanislav Kiryukhin <korsar.zn@gmail.com>
 * @copyright   Copyright (c) 2014, CKGroup.ru
 *
 * ----------------------------------------------
 * All Rights Reserved.
 * ----------------------------------------------
 */
namespace Phalcon\Ext\Mailer;

use Phalcon\Config;
use Phalcon\Exception;
use Phalcon\Mvc\User\Component;
use Phalcon\Mvc\View;
use Phalcon\DiInterface;

/**
 * Class Manager
 */
class Manager extends Component
{
    /**
     * @var array
     */
    protected $config = [];

    /**
     * @var \Swift_Transport
     */
    protected $transport;

    /**
     * @var \Swift_Mailer
     */
    protected $mailer;

    /**
     * @var \Phalcon\Mvc\View\Simple
     */
    protected $view;

    /**
     * Create a new MailerManager component using $config for configuring
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->configure($config);
    }

    /**
     * Create a new Message instance.
     *
     * Events:
     * - mailer:beforeCreateMessage
     * - mailer:afterCreateMessage
     *
     * @return \Phalcon\Ext\Mailer\Message
     */
    public function createMessage()
    {
        $eventsManager = $this->getEventsManager();

        if ($eventsManager) {
            $eventsManager->fire('mailer:beforeCreateMessage', $this);
        }

        /** @var $message Message */
        $message = $this->getDI()->get('\Phalcon\Ext\Mailer\Message', [$this]);

        if (($from = $this->getConfig('from'))) {
            $message->from($from['email'], isset($from['name']) ? $from['name'] : null);
        }

        if ($eventsManager) {
            $eventsManager->fire('mailer:afterCreateMessage', $this, [$message]);
        }

        return $message;
    }

    /**
     * Create a new Message instance.
     * For the body of the message uses the result of render of view
     *
     * Events:
     * - mailer:beforeCreateMessage
     * - mailer:afterCreateMessage
     *
     * @param string $view
     * @param array $params optional
     * @param null|string $viewsDir optional
     *
     * @return \Phalcon\Ext\Mailer\Message
     *
     * @see \Phalcon\Ext\Mailer\Manager::createMessage()
     */
    public function createMessageFromView($view, $params = [], $viewsDir = null)
    {
        $message = $this->createMessage();
        $message->content($this->renderView($view, $params, $viewsDir), $message::CONTENT_TYPE_HTML);

        return $message;
    }

    /**
     * Return a {@link \Swift_Mailer} instance
     *
     * @return \Swift_Mailer
     */
    public function getSwift()
    {
        if (!$this->isInitSwiftMailer()) {
            $this->registerSwiftMailer();
        }

        return $this->mailer;
    }

    /**
     * Normalize IDN domains.
     *
     * @param $email
     *
     * @return string
     *
     * @see \Phalcon\Ext\Mailer\Manager::punycode()
     */
    public function normalizeEmail($email)
    {
        if (preg_match('#[^(\x20-\x7F)]+#', $email)) {

            list($user, $domain) = explode('@', $email);

            return $user . '@' . $this->punycode($domain);

        } else {
            return $email;
        }
    }

    /**
     * Configure MailerManager class
     *
     * @param array $config
     *
     * @see \Phalcon\Ext\Mailer\Manager::registerSwiftTransport()
     * @see \Phalcon\Ext\Mailer\Manager::registerSwiftMailer()
     */
    protected function configure(array $config)
    {
        $this->config = $config;
    }

    /**
     * Create a new Driver-mail of SwiftTransport instance.
     *
     * Supported driver-mail:
     * - smtp
     * - sendmail
     * - mail
     *
     */
    protected function registerSwiftTransport()
    {
        switch ($driver = $this->getConfig('driver')) {
            case 'smtp':
                $this->transport = $this->registerTransportSmtp();
                break;

            case 'mail':
                $this->transport = $this->registerTransportMail();
                break;

            case 'sendmail':
                $this->transport = $this->registerTransportSendmail();
                break;

            default:
                throw new \InvalidArgumentException(sprintf('Driver-mail "%s" is not supported', $driver));
        }
    }

    /**
     * Create a new SmtpTransport instance.
     *
     * @return \Swift_SmtpTransport
     *
     * @see \Swift_SmtpTransport
     */
    protected function registerTransportSmtp()
    {
        $config = $this->getConfig();

        /** @var $transport \Swift_SmtpTransport: */
        $transport = $this->getDI()->get('\Swift_SmtpTransport')
            ->setHost($config['host'])
            ->setPort($config['port']);

        if (isset($config['encryption'])) {

            $transport->setEncryption($config['encryption']);
        }

        if (isset($config['username'])) {
            $transport->setUsername($this->normalizeEmail($config['username']));
            $transport->setPassword($config['password']);
        }

        return $transport;
    }

    /**
     * Get option config or the entire array of config, if the parameter $key is not specified.
     *
     * @param null $key
     * @param null $default
     *
     * @return string|array
     */
    protected function getConfig($key = null, $default = null)
    {
        if ($key !== null) {
            if (isset($this->config[$key])) {
                return $this->config[$key];
            } else {
                return $default;
            }

        } else {
            return $this->config;
        }
    }

    /**
     * Convert UTF-8 encoded domain name to ASCII
     *
     * @param $str
     *
     * @return string
     */
    protected function punycode($str)
    {
        if (function_exists('idn_to_ascii')) {
            return idn_to_ascii($str);
        } else {
            return $str;
        }
    }

    /**
     * Create a new MailTransport instance.
     *
     * @return \Swift_MailTransport
     *
     * @see \Swift_MailTransport
     */
    protected function registerTransportMail()
    {
        return $this->getDI()->get('\Swift_MailTransport');
    }

    /**
     * Create a new SendmailTransport instance.
     *
     * @return \Swift_SendmailTransport
     *
     * @see \Swift_SendmailTransport
     */
    protected function registerTransportSendmail()
    {
        /** @var $transport \Swift_SendmailTransport */
        $transport = $this->getDI()->get('\Swift_SendmailTransport')
            ->setCommand($this->getConfig('sendmail', '/usr/sbin/sendmail -bs'));

        return $transport;
    }

    /**
     * Register SwiftMailer
     *
     * @see \Swift_Mailer
     */
    protected function registerSwiftMailer()
    {
        $this->registerSwiftTransport();
        $this->mailer = $this->getDI()->get('\Swift_Mailer', [$this->transport]);
    }

    /**
     * Renders a view
     *
     * @param $viewPath
     * @param $params
     * @param null $viewsDir
     *
     * @return string
     */
    protected function renderView($viewPath, $params, $viewsDir = null)
    {
        $view = $this->getView();

        if ($viewsDir !== null) {
            $viewsDirOld = $view->getViewsDir();
            $view->setViewsDir($viewsDir);

            $content = $view->render($viewPath, $params);
            $view->setViewsDir($viewsDirOld);

            return $content;
        } else {
            return $view->render($viewPath, $params);
        }
    }

    /**
     * Return a {@link \Phalcon\Mvc\View\Simple} instance
     *
     * @return \Phalcon\Mvc\View\Simple
     */
    protected function getView()
    {
        if ($this->view) {
            return $this->view;
        } else {

            /** @var $viewApp \Phalcon\Mvc\View */
            $viewApp = $this->getDI()->get('view');

            if (!($viewsDir = $this->getConfig('viewsDir'))) {
                $viewsDir = $viewApp->getViewsDir();
            }

            /** @var $view \Phalcon\Mvc\View\Simple */
            $view = $this->getDI()->get('\Phalcon\Mvc\View\Simple');
            $view->setViewsDir($viewsDir);

            if ($engines = $viewApp->getRegisteredEngines()) {
                $view->registerEngines($engines);
            }

            return $this->view = $view;
        }
    }

    /**
     * Check init SwiftMailer
     *
     * @return bool
     */
    protected function isInitSwiftMailer()
    {
        return $this->mailer && $this->transport;
    }

    /**
     * Returns the internal dependency injector
     *
     * @return \Phalcon\DiInterface
     */
    public function getDI()
    {
        if (!($di = parent::getDI()) && !($di instanceof DiInterface)) {
            throw new \RuntimeException('A dependency injection object is required to access internal services');
        }

        return $di;
    }
}
