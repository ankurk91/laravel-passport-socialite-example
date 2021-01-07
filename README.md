# Laravel Passport and Socialite example

### What?

This is an example repo to demonstrate; how to
use [laravel-passport-social-grant](https://github.com/coderello/laravel-passport-social-grant) package
with [socialite](https://github.com/laravel/socialite) to authenticate your mobile app.

### Prerequisites

* php v7.4.3, [see](https://laravel.com/docs/8.x/installation) Laravel specific requirements
* Apache v2.4.33 with ```mod_rewrite```
* MySQL v8.0.19 || 5.7
* [Composer](https://getcomposer.org) v2.x

### Setup
* Make sure your web server has proper write permissions on `./storage` and `./bootstrap/cache` folders
* Create a config file (copy from ```.env.example```), and update environment variables
```
cp .env.example .env
```
* Install dependencies
```
composer install

php artisan key:generate
```
* Migrate and Seed database
```
php artisan migrate
php artisan db:seed
```
* Create the symbolic link for local file uploads
```
php artisan storage:link
```
* Run this command for to make Laravel Passport work
```
php artisan passport:keys
```
* Point your web server to **public** folder of this project
* Create an OAuth app on GitHub, [link](https://github.com/settings/applications/new)
* Put generated client_id and client_secret in `.env` file
* Import the postman collection from `./postman` folder to see example login request

### Hints
* You can find passport's client_id and client_secret in [seeding](./database/seeders/AuthClientsTableSeeder.php)
* You can find the `SocialUserResolverInterface` implementation [here](./app/Resolvers/SocialUserResolver.php)

### License

Same as Laravel, [MIT license](https://opensource.org/licenses/MIT)
