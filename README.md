Freshdesk Webhook
=================

Bind Freshdesk webhook events to Kanboard automatic actions.

Author
------

- Aya Amr
- License MIT

Requirements
------------

- Kanboard >= 1.0.37
- Freshdesk webhooks configured for a project

Installation
------------

You have the choice between 3 methods:

1. Download the zip file and decompress everything under the directory `plugins/FreshdeskWebhook`
2. Clone this repository into the folder `plugins/FreshdeskWebhook`

Note: Plugin folder is case-sensitive.

Documentation
-------------

### List of supported events

- Freshdesk issue opened
- Freshdesk issue closed
- Freshdesk issue reopened
- Freshdesk issue comment created

### List of supported actions

- Create a task from an external provider
- Create a comment from an external provider
- Close a task
- Open a task

### Configuration

1. On Kanboard, go to the project settings and choose the section **Integrations**
2. Copy the Freshdesk webhook URL
3. On Freshdesk, go to the project settings and go to the section **Webhooks**
4. Choose a title for your webhook and paste the Kanboard URL

### Examples

You have to create some automatic actions in your project to make it work:

#### Close a Kanboard task when a commit pushed to Freshdesk

- Choose the event: **Freshdesk commit received**
- Choose action: **Close the task**

When one or more commits are sent to Freshdesk, Kanboard will receive the information, each commit message with a task number included will be closed.

Example:

- Commit message: "Fix bug #1234"
- That will close the Kanboard task #1234

#### Add comment when a commit received

- Choose the event: **Freshdesk commit received**
- Choose action: **Create a comment from an external provider**

The comment will contain the commit message and the URL to the commit.
