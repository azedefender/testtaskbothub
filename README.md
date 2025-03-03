## Telegram Bot на PHP

Этот проект представляет собой простого Telegram-бота, который взаимодействует с пользователями, позволяет им зарегистрироваться и управлять их балансом. Бот использует базу данных MySQL для хранения информации о пользователях и их балансах.

### Содержание

- [Требования](#требования)
- [Установка](#установка)
- [Конфигурация](#конфигурация)
- [Использование](#использование)
- [SQL-скрипт](#sql-скрипт)
- [Лицензия](#лицензия)

### Требования

- PHP 7.2 или выше
- Composer
- База данных MySQL
- Расширение PDO для работы с MySQL
- Библиотека `telegram-bot/api` для работы с Telegram API

### Установка

1. Склонируйте репозиторий:

   ```bash
   git clone https://github.com/yourusername/telegram-bot.git
   cd telegram-bot
   ```

2. Установите зависимости с помощью Composer:

   ```bash
   composer install
   ```

### Конфигурация

1. Откройте файл `bot.php` и замените `BOT_API_TOKEN` на ваш токен API, полученный от [BotFather](https://core.telegram.org/bots#botfather).

   ```php
   $botToken = 'BOT_API_TOKEN';
   ```

2. Настройте подключение к базе данных. Убедитесь, что данные для подключения (имя базы данных, имя пользователя и пароль) соответствуют вашим настройкам:

   ```php
   $db = new PDO('mysql:host=localhost;dbname=testdb', 'root', 'defender123!');
   ```

### Использование

1. Запустите скрипт `bot.php`:

   ```bash
   php bot.php
   ```

2. Найдите вашего бота в Telegram и начните с ним взаимодействовать, отправив команду `/start`.

3. Бот проверит, зарегистрирован ли вы в базе данных. Если нет, он добавит вас и установит баланс в 0.00. Если вы уже зарегистрированы, бот отправит вам ваш текущий баланс.

4. Вы можете отправлять числовые сообщения боту, чтобы увеличить или уменьшить ваш баланс. Бот будет проверять, достаточно ли у вас средств для выполнения операции.

### SQL-скрипт

Для создания таблицы `users` в вашей базе данных выполните следующий SQL-запрос:

```sql
CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    chat_id BIGINT UNIQUE NOT NULL,
    balance DECIMAL(10, 2) NOT NULL DEFAULT 0.00
);


