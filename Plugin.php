<?php

namespace Kanboard\Plugin\FreshdeskWebhook;

use Kanboard\Core\Plugin\Base;
use Kanboard\Core\Security\Role;
use Kanboard\Core\Translator;

class Plugin extends Base
{
    public function initialize()
    {
        $this->actionManager->getAction('\Kanboard\Action\CommentCreation')->addEvent(WebhookHandler::EVENT_ISSUE_COMMENT);
        $this->actionManager->getAction('\Kanboard\Action\TaskClose')->addEvent(WebhookHandler::EVENT_ISSUE_CLOSED);
        $this->actionManager->getAction('\Kanboard\Action\TaskCreation')->addEvent(WebhookHandler::EVENT_ISSUE_OPENED);
        $this->actionManager->getAction('\Kanboard\Action\TaskOpen')->addEvent(WebhookHandler::EVENT_ISSUE_REOPENED);

        $this->template->hook->attach('template:project:integrations', 'FreshdeskWebhook:project/integrations');
        $this->route->addRoute('/webhook/freshdesk/:project_id/:token', 'WebhookController', 'handler', 'FreshdeskWebhook');
        $this->applicationAccessMap->add('WebhookController', 'handler', Role::APP_PUBLIC);
    }

    public function onStartup()
    {
        Translator::load($this->languageModel->getCurrentLanguage(), __DIR__.'/Locale');

        $this->eventManager->register(WebhookHandler::EVENT_ISSUE_OPENED, t('Freshdesk issue opened'));
        $this->eventManager->register(WebhookHandler::EVENT_ISSUE_CLOSED, t('Freshdesk issue closed'));
        $this->eventManager->register(WebhookHandler::EVENT_ISSUE_REOPENED, t('Freshdesk issue reopened'));
        $this->eventManager->register(WebhookHandler::EVENT_ISSUE_COMMENT, t('Freshdesk issue comment created'));
    }

    public function getPluginName()
    {
        return 'Freshdesk Webhook';
    }

    public function getPluginDescription()
    {
        return t('Bind Freshdesk webhook events to Kanboard automatic actions');
    }

    public function getPluginAuthor()
    {
        return 'Aya Amr';
    }

    public function getPluginVersion()
    {
        return '1.0.0';
    }

    public function getPluginHomepage()
    {
        return 'https://github.com/kanboard/plugin-freshdesk-webhook';
    }

    public function getCompatibleVersion()
    {
        return '>=1.0.37';
    }
}
