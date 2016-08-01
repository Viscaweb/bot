🤖 Bot
=====

Notes
--------------

- The bot will only merge PR that have labeled "merge-asap"
- The bot will only merge if all check status are green even if the repo settings do not enforce it


To improve
--------------

- Use combined Status (GET /repos/:owner/:repo/commits/:ref/status) instead of the complete list of statuses 


Install
--------------

- Setup Rabbit MQ
- ... ?

Setup
--------------

- Make sure the bot has access to the repository
- Add the webhook to your repository (pull_request, status)


Run
--------------

`bin/console rabbitmq:consumer bot_events`
