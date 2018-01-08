<h3><i class="fa fa-ticket fa-fw"></i>&nbsp;<?= t('Freshdesk webhooks') ?></h3>
<div class="panel">
    <input type="text" class="auto-select" readonly="readonly" value="<?= $this->url->href('WebhookController', 'handler', array('plugin' => 'FreshdeskWebhook', 'token' => $webhook_token, 'project_id' => $project['id']), false, '', true) ?>"/><br/>
    <p class="form-help"><a href="#!" target="_blank"><?= t('Help on Freshdesk webhooks') ?></a></p>
</div>
