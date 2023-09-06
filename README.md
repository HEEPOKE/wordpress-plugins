# wordpress-plugins

- WP Gitlab Trigger

## Install Wordpress

```bash
docker compose up -d
```

## Install Dependencies

```bash
composer install && && composer dump-autoload
```

## Test

```bash
./vendor/bin/phpunit --coverage-text
```
