if [ -f '.env.local' ]
then
    composer install
    npm i
    npm run build
else
    echo 'You need to create .env.local file in the root directory. Run: cp .env.local.example .env.local'
fi
