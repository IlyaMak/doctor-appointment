if [ -f '.env.local' ]
then
    docker compose down
    docker compose build
    docker compose up -d
    docker compose exec php composer install
    docker compose exec php npm i
    docker compose exec php npm run build
else
    echo 'You need to create .env.local file in the root directory. Run: cp .env.local.example .env.local'
fi
