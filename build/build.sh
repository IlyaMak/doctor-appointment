if [ -f '.env' ]
then
    composer install
    npm i
    npm run build
else
    echo 'You need to create .env file in the root directory. Run: cp .env.example .env'
fi
