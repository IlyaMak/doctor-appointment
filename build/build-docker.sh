if [ -f '.env' ]
then
    docker compose down
    docker compose build
    docker compose up -d
    docker compose exec php composer install
    docker compose exec php npm i
    docker compose exec php npm run build
else
    echo 'You need to create .env file in the root directory. Run: cp .env.example .env'
fi
