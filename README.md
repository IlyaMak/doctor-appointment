How to send email:
1. php bin/console messenger:stats - Show the message count for one or more transports
2. php bin/console messenger:consume -vv - Consume messages ('-vv' to see logs about consumed messages)



Problems:
1. Fix sign-in wrong password message style.
2. Add "Already have an account? <a>Sign in</a>" to the end of the register form.
3. Add "New to this website? <a>Join now</a>" to the sign in page.
4. Add "Forgot password?" to the sign in page.
5. Forbid sign-in if not verified.
6. Add .env var IS_EMAIL_VERIFICATION_REQUIRED=true/false:
    - if true
        - Forbid sign-in if not verified.
        - everything stays the same.
    - if false
        - authenticate a user right after the registration page
        - Allow sign-in if not verified.
