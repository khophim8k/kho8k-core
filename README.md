# Server Requirements:
- Laravel Framework 8.
- PHP 7.3 or higher.
    + Configure `php.ini`:
    
    ```
    max_input_vars=100000
    ```
- MySQL 5.7 or higher.
# Add-on & Themes:
- Home: [OPhimCMS.CC](https://opcms.cc)
- Admin: [Demo.OPhimCMS.CC/admin](https://demo.ophimcms.cc/admin)
- Free Movies Data: [OPhim.Movie](https://kho8k.movie)

- Add-on:
    - [OPhim Crawler](https://github.com/khophim8k/kho8k-crawler)
- Theme: [MORE...](https://opcms.cc)

# Installation:
1. CD to project root and run: `composer require khophim8k/kho8k-core -W`
2. Configuration your database connection information in file `.env`
3. Then, run command: `php artisan kho8k:install`
4. Change app\Models\User:
```php
use Kho8k\Core\Models\User as OphimUser;

class User extends OphimUser {
    use HasApiTokens, HasFactory, Notifiable;
    // ...
}
```
5. Create new user by command: `php artisan kho8k:user`

6. Remove this route definition in routes/web.php
```php
Route::get('/', function () {
    return view('welcome');
});
```
7. Run `php artisan optimize:clear`

# Update:
1. CD to project root and run: `composer update khophim8k/kho8k-core -W`
2. Then, run command: `php artisan kho8k:install`
3. Run `php artisan optimize:clear`
4. Clear PHP Opcache in server (if enabled)

# Note
- Configure a production environment file `.env`
    + `APP_NAME=your_app_name`
    + `APP_ENV=production`
    + `APP_DEBUG=false`
    + `APP_URL=https://your-domain.com`
- Configure timezone `/config/app.php`
    + `'timezone' => 'Asia/Ho_Chi_Minh'`
    + `'locale' => 'vi'`

- Command CMS
    + `php artisan kho8k:menu:generate` : Generate menu
    + `php artisan kho8k:episode:change_domain` : Change episode domain play stream

# Command:
- Generate menu categories & regions: `php artisan kho8k:menu:generate`

# Reset view counter:
- Setup crontab, add this entry:
```
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```
