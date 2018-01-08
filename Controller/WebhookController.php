<?php

namespace Kanboard\Plugin\FreshdeskWebhook\Controller;

use Kanboard\Controller\BaseController;
use Kanboard\Plugin\FreshdeskWebhook\WebhookHandler;

/**
 * Webhook Controller
 *
 * @package  controller
 * @author   Frederic Guillot
 */
class WebhookController extends BaseController
{
    /**
     * Handle Freshdesk webhooks
     *
     * @access public
     */
    public function handler()
    {
        $this->checkWebhookToken();
        $freshdeskWebhook = new WebhookHandler($this->container);
        $freshdeskWebhook->setProjectId($this->request->getIntegerParam('project_id'));
        $result = $freshdeskWebhook->parsePayload($this->request->getJson());
        return $result;
    }
}
