How to send email:
1. php bin/console messenger:stats - Show the message count for one or more transports
2. php bin/console messenger:consume -vv - Consume messages ('-vv' to see logs about consumed messages)

Test Stripe events:
1. Login to the Stripe account: stripe login
2. stripe listen --forward-to localhost:8000/api/payment/stripe
