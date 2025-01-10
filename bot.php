require_once 'vendor/autoload.php';

use TelegramBot\Api\BotApi;
use TelegramBot\Api\Types\Update;

$botToken = 'BOT_API_TOKEN';
$bot = new BotApi($botToken);

// Настройка подключения к базе данных
try {
    $db = new PDO('mysql:host=localhost;dbname=testdb', 'root', 'defender123!');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Ошибка подключения к базе данных: " . $e->getMessage());
}

$bot->command('start', function ($message) use ($bot, $db) {
    $chatId = $message->getChat()->getId();
    
    try {
        // Проверяем, есть ли пользователь в базе данных
        $stmt = $db->prepare("SELECT * FROM users WHERE chat_id = ?");
        $stmt->execute([$chatId]);
        $user = $stmt->fetch();

        // Если пользователь новый, добавляем его в базу
        if (!$user) {
            $stmt = $db->prepare("INSERT INTO users (chat_id) VALUES (?)");
            $stmt->execute([$chatId]);
            $bot->sendMessage($chatId, "Добро пожаловать! Ваш счёт: \$0.00");
        } else {
            $bot->sendMessage($chatId, "Вы уже зарегистрированы. Ваш счёт: " . $user['balance']);
        }
    } catch (PDOException $e) {
        $bot->sendMessage($chatId, "Произошла ошибка при обращении к базе данных: " . $e->getMessage());
    }
});

// Обработка текстовых сообщений
$bot->on(function (Update $update) use ($bot, $db) {
    $message = $update->getMessage();
    $chatId = $message->getChat()->getId();
    $text = $message->getText();

    // Проверяем, является ли сообщение числом
    if (is_numeric(str_replace(',', '.', $text))) {
        $amount = floatval(str_replace(',', '.', $text));

        try {
            // Начинаем транзакцию
            $db->beginTransaction();

            // Получаем текущий баланс пользователя
            $stmt = $db->prepare("SELECT balance FROM users WHERE chat_id = ?");
            $stmt->execute([$chatId]);
            $user = $stmt->fetch();

            if ($user) {
                $newBalance = $user['balance'] + $amount;

                if ($newBalance < 0) {
                    $bot->sendMessage($chatId, "Ошибка: недостаточно средств на счёте.");
                } else {
                    // Обновляем баланс пользователя
                    $stmt = $db->prepare("UPDATE users SET balance = ? WHERE chat_id = ?");
                    $stmt->execute([$newBalance, $chatId]);
                    $db->commit(); // Подтверждаем транзакцию
                    $bot->sendMessage($chatId, "Ваш новый баланс: $newBalance");
                }
            } else {
                $bot->sendMessage($chatId, "Пользователь не найден в базе данных.");
            }
        } catch (PDOException $e) {
            // Откатываем транзакцию в случае ошибки
            $db->rollBack();
            $bot->sendMessage($chatId, "Произошла ошибка при обращении к базе данных: " . $e->getMessage());
        }
    } else {
        $bot->sendMessage($chatId, "Пожалуйста, введите число.");
    }
}, function () {
    return true;
});

$bot->run();
