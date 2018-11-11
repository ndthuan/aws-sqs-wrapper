#!/usr/bin/env bash

set -ex

function cleanup() {
   docker-compose down
}

trap cleanup EXIT

docker-compose up -d

docker-compose exec -T composer composer install --ignore-platform-reqs --no-scripts

docker-compose exec -T netcat sh wait_for.sh localstack:4576 -t 60

docker-compose exec -T awscli aws --no-sign-request --endpoint-url http://localstack:4576 --region fake sqs create-queue --queue-name test-queue
docker-compose exec -T awscli aws --no-sign-request --endpoint-url http://localstack:4576 --region fake sqs purge-queue --queue-url http://localstack:4576/queue/test-queue

for phpver in php70 php71 php72; do
    docker-compose exec -T ${phpver} sh -c 'pecl list | grep -i xdebug > /dev/null || yes | pecl install xdebug; echo "zend_extension=xdebug.so" > /usr/local/etc/php/conf.d/xdebug.ini'
    docker-compose exec -T ${phpver} vendor/bin/phpcs --standard=PSR2 src/
    docker-compose exec -T ${phpver} vendor/bin/phpunit --coverage-html=tests/report/${phpver}-coverage
done
