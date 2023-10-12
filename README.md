How to send email:
1. php bin/console messenger:stats - Show the message count for one or more transports
2. php bin/console messenger:consume -vv - Consume messages ('-vv' to see logs about consumed messages)

Test Stripe events:
1. Login to the Stripe account: stripe login
2. stripe listen --forward-to localhost:8000/api/payment/stripe

How to use crontab:
1. To see the list of all cron jobs of the current user: crontab -l
2. To make a written cron job: crontab crontab/crontab
3. To test commands with a crontab on local machine use the full path of the "bin/console" command (/home/illia/Projects/doctor-appointment/bin/console): php /home/illia/Projects/doctor-appointment/bin/console app:send-email-appoinment-reminder