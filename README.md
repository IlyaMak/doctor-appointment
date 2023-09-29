How to send email:
1. php bin/console messenger:stats - Show the message count for one or more transports
2. php bin/console messenger:consume -vv - Consume messages ('-vv' to see logs about consumed messages)



Problems:
4. Add "Forgot password?" to the sign in page.
5. Forbid sign-in if not verified.
6. Add .env var IS_EMAIL_VERIFICATION_REQUIRED=true/false:
    - if true
        - Forbid sign-in if not verified.
        - everything stays the same.
    - if false
        - authenticate a user right after the registration page
        - Allow sign-in if not verified.
