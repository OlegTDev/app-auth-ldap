# Микросервис windows-аутентификации (с передачей данных из LDAP)

Сервис используется для сквозной аутентификации через веб-сервер IIS / Apache в среде Active Directory.

### Принцип работы: 
1. Сервис-инициатор (например, сайт на Laravel/Yii, не имеющий сквозной аутентификации) выполняет переадресацию браузера на данный микросервис с обязательным GET-параметром `return_url`.
2. Микросервис выполняет аутентификацию средствами веб-сервера, извлекает учетную запись пользователя из серверных параметров (`REMOTE_USER`), выполняет поиск расширенных данных о пользователе на сервере LDAP (ФИО, почта, отдел, группы).
3. Микросервис формирует защищенный `JWT-токен` с данными пользователя и выполняет обратную переадресацию на указанный `return_url`, передавая токен в GET-параметре `token`.

---


## Требования

* **PHP 8.3** и выше
* Расширение **`php_ldap`** (должно быть включено в `php.ini`)

На стороне сервиса-инициатора для возможности расшифровки полученного токена должна быть установлена библиотека `firebase/php-jwt`:
```bash
composer require firebase/php-jwt
```


### Пример реализации проверки на сервисе-инициаторе:
```php
// 1. Извлечение параметра из адресной строки
$redirectUrl = $response->getHeader('Location')[0];
$tokenString = parse_url($redirectUrl, PHP_URL_QUERY);
parse_str($tokenString, $result);
// получаем чистый токен из адресной строки
$jwtToken = $result['token'] ?? null;

if (!\$jwtToken) {
    throw new Exception('Токен авторизации отсутствует');
}

// 2. Расшифровка данных (секретный ключ должен совпадать с ключом из текущего сервиса)
$secretKey = env('JWT_SECRET');
$decodedData = Firebase\JWT\JWT::decode($jwtToken, new Firebase\JWT\Key($secretKey, 'HS256'));

// 3. Данные пользователя успешно получены
username = decodedData->sAMAccountName;       // Доменный логин (например, ivanov)
groups = decodedData->memberOf; // группы
// и т.п.

```


## Установка

1. Клонировать репозиторий:
```bash
git clone https://github.com/OlegTDev/app-auth-ldap.git app-auth-ldap
```

2. Перейти в папку с проектом и установить зависимости:
```bash
cd app-auth-ldap && composer install
```

3. **Важно для веб-сервера IIS:** Убедитесь, что для папки `logs/` в Windows выданы права на запись для группы "Пользователи". Без этого встроенный логгер Monolog будет вызывать ошибку сервера `500`.


## Настройка конфигурации

Создайте файл `.env` из примера:
```bash
cp .env.example .env
```

Настройте параметры в соответствии с вашей корпоративной сетью:
```ini
# Режим продакшена (true/false)
PROD=true

# Атрибут, в котором хранится учетная запись аутентифицированного пользователя на веб-сервере
DOMAIN_USER_ATTR_LOGIN=REMOTE_USER

# Адрес и порт текущего сервиса
APP_URL=https://company.local

# Ключ подписи JWT-токена (длина ключа должна быть минимум 32 символа!)
JWT_SECRET=64_symbols_random_crypto_secure_string_here_1234567890abcdef

# Настройки подключения к серверу LDAP / Active Directory
LDAP_CONNECTION_STRING=ldap://dc.company.local:389
LDAP_BIND_DN=service-account@company.local
LDAP_BIND_PASSWORD="your_service_account_password"
LDAP_BASE_DN="OU=Employees,DC=company,DC=local"
```
---

### Тестирование

Для запуска тестов выполните в консоли:
```bash
composer test
```

