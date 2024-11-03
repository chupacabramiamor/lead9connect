
Встановлення

`composer require chupacabramiamor/lead9connect`

Після встановлення, за бажаннями можна опубліквати деякі ресурси

`php artisan vendor:publish --provider="Chupacabramiamor\Lead9Connect\Lead9ConnectServiceProvider"`

Команди, внутрішньо мають вигляд конфігурації того що з мини має робити менеджер при їх використанні.
Додаткові можливості команди мають бути ідентифіковані за допомогою внутрішніх інтерфейсів та їх реалізацією.

### Реалізація команд

Приклад звичайної порожньої команди

```
namespace App\Integrations\Lead9Connect\Commands;

use Chupacabramiamor\Lead9Connect\AbstractCommand;

final class Register extends AbstractCommand
{
}
```

Приклад використання додаткової можливості, який одночасно показує роботу з точкою входу.
Ця точка є ключем в об'єкті відповіді, значення якого встановлюється як контент на виході виконання команди менеджером

```
use Chupacabramiamor\Lead9Connect\Contracts\UsePointer;
...
public function pointer(): string
{
    return 'data';
}
```

Можливості кешування. Кешування використовується з пакету Laravel, зі сховищем сконфігурованим за замовченням.
В прикладі наведено використання кешу з ключем gifts та строком життя в 28 днів

```
use Chupacabramiamor\Lead9Connect\Contracts\UseCache;
...
public function getCacheKey(): string
{
    return 'gifts';
}

public function getCacheTtl(): ?int
{
    return 28 * 24 * 3600;
}
```

Можливість перевизначення контенту відповіді

```
use Chupacabramiamor\Lead9Connect\Contracts\ReplaceResponseData;
...
public function replace($input): mixed
{
    $result = [];

    foreach ($input as $order) {
        $result[] = [ ... ];
    }

    return $result;
}
```

### Використання менеджеру для виконання команд

Першим аргументом є клас команди, який потрібно виконати, другим - параметри з якими команда відправляється у вигляді запиту. Третім аргументом методу `execute` встановлюються флаги, які відповідають за попередню обробку данних перед запитом або після отримання відповіді.
В прикладі представлений DROP_CACHE, який використовується для скидання кешу (якщо в команді він задіяний)

```
$user = $manager->execute(Profile::class, $data, Profile::DROP_CACHE);
```

Кастомізація тексту помилки. Для такої кастомізації можна обійтись статичним текстом або використувати відповідь із серверу у вигляді контенту ($contents)

```
public static function getErrorMessage($contents = null): ?string
{
    return 'Сталася внутрішня помилка. Почекайте будь ласочка та спробуйте ще раз пізніше';
}
```

```
public static function getErrorMessage($contents = null): ?string
{
    $result = [
        'msgtype1' => 'A',
        'msgtype2' => 'B',
    ];

    return $result[$contents->msgtype];
}
```
Також можна використати і параметри, які надсилатимуться менеджером в запиті

```
public static function getErrorMessage($contents = null): ?string
{
    return "Помилка при оброці номеру {$this->options['msisdn']}";
}
```
