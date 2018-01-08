<?php

namespace Kanboard\Plugin\FreshdeskWebhook;

use Kanboard\Core\Base;
use Kanboard\Event\GenericEvent;

/**
 * Freshdesk Webhook
 *
 * @author   Frederic Guillot
 */
class WebhookHandler extends Base
{
    /**
     * Events
     *
     * @var string
     */
    const EVENT_ISSUE_OPENED           = 'freshdesk.webhook.issue.opened';
    const EVENT_ISSUE_CLOSED           = 'freshdesk.webhook.issue.closed';
    const EVENT_ISSUE_REOPENED         = 'freshdesk.webhook.issue.reopened';
    const EVENT_ISSUE_COMMENT          = 'freshdesk.webhook.issue.commented';

    /**
     * Project id
     *
     * @access private
     * @var integer
     */
    private $project_id = 0;

    /**
     * Set the project id
     *
     * @access public
     * @param  integer   $project_id   Project id
     */
    public function setProjectId($project_id)
    {
        $this->project_id = $project_id;
    }

    /**
     * Parse incoming events
     *
     * @access public
     * @param  array   $payload   Freshdesk event
     * @return boolean
     */
    public function parsePayload($payload)
    {
        $eventType = $payload['freshdesk_webhook']['triggered_event'];
        $eventType = str_replace(array( '{', '}' ), '', $eventType);
        $eventTypeArray = explode(':', $eventType);
        if(sizeof($eventTypeArray) > 2) {
            $eventType = $eventTypeArray[0];
        }
        switch ($eventType) {
            case 'reply_sent:sent':
                return $this->handleCommentCreated($payload);
            case 'ticket_action:created':
                return $this->handleIssueOpened($payload);
            case 'status':
                return $this->handleIssueUpdated($payload);
        }

        return false;
    }

    /**
     * Parse comment issue events
     *
     * @access public
     * @param  array   $payload
     * @return boolean
     */
    public function handleCommentCreated(array $payload)
    {
        $task = $this->taskFinderModel->getByReference($this->project_id, $payload['freshdesk_webhook']['ticket_id']);


        if(empty($task)) {
            //create task
            $taskId = $this->taskCreationModel->create(array(
                'project_id' => $this->project_id,
                'title' => $payload['freshdesk_webhook']['ticket_subject'],
                'reference' => $payload['freshdesk_webhook']['ticket_id'],
                'description' => $payload['freshdesk_webhook']['ticket_description'],
            ));
            $task = $this->taskFinderModel->getByReference($this->project_id, $payload['freshdesk_webhook']['ticket_id']); //make sure issue not created before
        }

        if (! empty($task)) {

            $event = array(
                'project_id' => $this->project_id,
                'reference' => $payload['freshdesk_webhook']['ticket_id'],
                'comment' => $payload['freshdesk_webhook']['ticket_latest_public_comment'],
                'user_id' => 0,
                'task_id' => $task['id'],
            );

            $this->dispatcher->dispatch(
                self::EVENT_ISSUE_COMMENT,
                new GenericEvent($event)
            );

            return true;
        }

        return false;
    }

    /**
     * Handle new issues
     *
     * @access public
     * @param  array    $payload
     * @return boolean
     */
    public function handleIssueOpened($payload)
    {
        $task = $this->taskFinderModel->getByReference($this->project_id, $payload['freshdesk_webhook']['ticket_id']); //make sure issue not created before
        if (!empty($task)) {
            return false;
        }
        $event = array(
            'project_id' => $this->project_id,
            'reference' => $payload['freshdesk_webhook']['ticket_id'],
            'title' => $payload['freshdesk_webhook']['ticket_subject'],
            'description' => $payload['freshdesk_webhook']['ticket_description'],
        );
        $this->dispatcher->dispatch(
            self::EVENT_ISSUE_OPENED,
            new GenericEvent($event)
        );

        return true;
    }

    /**
     * Handle issue updates
     *
     * @access public
     * @param  array    $payload
     * @return boolean
     */
    public function handleIssueUpdated(array $payload)
    {
        $task = $this->taskFinderModel->getByReference($this->project_id, $payload['freshdesk_webhook']['ticket_id']);

        if(empty($task)) {
            //create task
            $taskId = $this->taskCreationModel->create(array(
                'project_id' => $this->project_id,
                'title' => $payload['freshdesk_webhook']['ticket_subject'],
                'reference' => $payload['freshdesk_webhook']['ticket_id'],
                'description' => $payload['freshdesk_webhook']['ticket_description'],
            ));
            $task = $this->taskFinderModel->getByReference($this->project_id, $payload['freshdesk_webhook']['ticket_id']); //make sure issue not created before
        }
        if (empty($task)) {
            return false;
        }

        return $this->handleStatusChange($task, $payload);
    }

    /**
     * Handle issue status change
     *
     * @access public
     * @param  array    $task
     * @param  array    $payload
     * @return boolean
     */
    public function handleStatusChange(array $task, array $payload)
    {
        $event = array(
            'project_id' => $this->project_id,
            'task_id' => $task['id'],
            'reference' => $payload['freshdesk_webhook']['ticket_id'],
        );

        switch ($payload['freshdesk_webhook']['ticket_status']) {
            case 'Resolved':
            case 'Closed':
                $this->dispatcher->dispatch(self::EVENT_ISSUE_CLOSED, new GenericEvent($event));
                return true;
            default:
                $this->dispatcher->dispatch(self::EVENT_ISSUE_REOPENED, new GenericEvent($event));
                return true;
        }

        return false;
    }
}
