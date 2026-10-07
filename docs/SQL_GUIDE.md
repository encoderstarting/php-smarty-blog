# SQL и PDO: шпаргалка для проекта

Этот материал не заменяет понимание запросов. Лучше вручную набрать каждый пример, выполнить его в базе, изменить условия и убедиться, что вы можете объяснить каждую строку.

## 1. Что именно используется в проекте

Здесь есть два отдельных уровня:

- **SQL** описывает, какие данные нужно прочитать или изменить в MySQL;
- **PDO** передаёт SQL-запрос из PHP в MySQL и возвращает результат.

ORM и конструктор запросов не нужны. Запросы будут обычными строками SQL внутри классов-репозиториев.

Пример общего процесса:

```php
$sql = 'SELECT id, name FROM categories WHERE slug = :slug LIMIT 1';

$statement = $pdo->prepare($sql);
$statement->execute(['slug' => $slug]);

$category = $statement->fetch();
```

Здесь `:slug` — параметр. Значение не склеивается со строкой запроса, поэтому такой вариант защищает от SQL-инъекции.

## 2. Подключение через PDO

Рекомендуемые настройки подключения:

```php
$dsn = sprintf(
    'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
    $_ENV['DB_HOST'],
    $_ENV['DB_PORT'],
    $_ENV['DB_DATABASE'],
);

$pdo = new PDO($dsn, $_ENV['DB_USERNAME'], $_ENV['DB_PASSWORD'], [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
]);
```

Зачем нужны настройки:

- `ERRMODE_EXCEPTION` превращает ошибки базы в исключения;
- `FETCH_ASSOC` возвращает строки в виде ассоциативных массивов;
- `EMULATE_PREPARES => false` включает настоящие подготовленные запросы драйвера;
- `utf8mb4` позволяет корректно хранить полный Unicode.

## 3. Базовая форма SELECT

```sql
SELECT столбцы
FROM таблица
WHERE условие
ORDER BY столбец ASC | DESC
LIMIT количество OFFSET смещение;
```

Порядок частей запроса важен.

Получить последние опубликованные статьи:

```sql
SELECT id, title, slug, description, image_path, published_at
FROM posts
WHERE published_at <= NOW()
ORDER BY published_at DESC
LIMIT 3;
```

Полезно перечислять нужные столбцы явно, а не писать `SELECT *`. Так проще видеть контракт метода и не загружать лишние данные.

## 4. Получение одной записи

SQL:

```sql
SELECT id, name, slug, description
FROM categories
WHERE slug = :slug
LIMIT 1;
```

PHP:

```php
$statement = $pdo->prepare($sql);
$statement->execute(['slug' => $slug]);

$category = $statement->fetch();

if ($category === false) {
    // Категория не найдена: контроллер должен вернуть 404.
}
```

Для одной строки используется `fetch()`, для списка — `fetchAll()`.

## 5. INSERT, UPDATE и DELETE

Создание категории:

```sql
INSERT INTO categories (name, slug, description, created_at, updated_at)
VALUES (:name, :slug, :description, NOW(), NOW());
```

```php
$statement = $pdo->prepare($sql);
$statement->execute([
    'name' => $name,
    'slug' => $slug,
    'description' => $description,
]);

$categoryId = (int) $pdo->lastInsertId();
```

Изменение статьи:

```sql
UPDATE posts
SET title = :title,
    description = :description,
    updated_at = NOW()
WHERE id = :id;
```

Удаление связи статьи с категорией:

```sql
DELETE FROM category_post
WHERE post_id = :post_id
  AND category_id = :category_id;
```

У `UPDATE` и `DELETE` почти всегда должен быть `WHERE`. Без него изменятся или удалятся все строки таблицы.

## 6. Связь many-to-many

Одна статья может относиться к нескольким категориям. Поэтому между `posts` и `categories` используется связующая таблица `category_post`.

Привязать статью к категории:

```sql
INSERT INTO category_post (post_id, category_id)
VALUES (:post_id, :category_id);
```

Получить категории статьи:

```sql
SELECT c.id, c.name, c.slug
FROM categories AS c
INNER JOIN category_post AS cp ON cp.category_id = c.id
WHERE cp.post_id = :post_id
ORDER BY c.name ASC;
```

Логика `JOIN` читается так:

1. Берём категории `c`.
2. Присоединяем строки связующей таблицы `cp`.
3. Оставляем связи только выбранной статьи.

## 7. Статьи выбранной категории

```sql
SELECT p.id,
       p.title,
       p.slug,
       p.description,
       p.image_path,
       p.views_count,
       p.published_at
FROM posts AS p
INNER JOIN category_post AS cp ON cp.post_id = p.id
WHERE cp.category_id = :category_id
  AND p.published_at <= NOW()
ORDER BY p.published_at DESC
LIMIT :limit OFFSET :offset;
```

Числовые параметры пагинации лучше привязать явно:

```php
$statement = $pdo->prepare($sql);
$statement->bindValue(':category_id', $categoryId, PDO::PARAM_INT);
$statement->bindValue(':limit', $perPage, PDO::PARAM_INT);
$statement->bindValue(':offset', $offset, PDO::PARAM_INT);
$statement->execute();

$posts = $statement->fetchAll();
```

Смещение вычисляется в PHP:

```php
$page = max(1, $page);
$offset = ($page - 1) * $perPage;
```

Например, при шести статьях на странице для третьей страницы смещение равно `(3 - 1) * 6 = 12`.

## 8. Подсчёт строк для пагинации

```sql
SELECT COUNT(*)
FROM posts AS p
INNER JOIN category_post AS cp ON cp.post_id = p.id
WHERE cp.category_id = :category_id
  AND p.published_at <= NOW();
```

```php
$statement = $pdo->prepare($sql);
$statement->execute(['category_id' => $categoryId]);

$totalPosts = (int) $statement->fetchColumn();
$totalPages = max(1, (int) ceil($totalPosts / $perPage));
```

`COUNT(*)` возвращает одну величину, поэтому здесь удобен `fetchColumn()`.

## 9. Безопасная сортировка

Пользователь выбирает `date` или `views`. Название столбца нельзя передать как обычный параметр PDO, поэтому нужен белый список:

```php
$allowedSorts = [
    'date' => 'p.published_at',
    'views' => 'p.views_count',
];

$sort = $_GET['sort'] ?? 'date';
$orderColumn = $allowedSorts[$sort] ?? $allowedSorts['date'];

$sql = "SELECT ... ORDER BY {$orderColumn} DESC, p.id DESC";
```

В SQL подставляется только значение, заранее написанное разработчиком. Нельзя помещать `$_GET['sort']` прямо в строку запроса.

Вторичная сортировка по `id` делает порядок стабильным, если у нескольких статей одинаковая дата или одинаковое число просмотров.

## 10. Увеличение просмотров

Не нужно сначала читать текущее значение в PHP. MySQL может атомарно увеличить счётчик:

```sql
UPDATE posts
SET views_count = views_count + 1
WHERE id = :id;
```

После этого статью можно получить отдельным `SELECT`. Заранее решите и опишите, какое число показывается пользователю: до увеличения или после него.

## 11. Похожие статьи

Простой вариант — найти другие статьи с любой общей категорией:

```sql
SELECT p.id,
       p.title,
       p.slug,
       p.description,
       p.image_path,
       p.published_at,
       COUNT(DISTINCT cp.category_id) AS common_categories
FROM posts AS p
INNER JOIN category_post AS cp ON cp.post_id = p.id
WHERE cp.category_id IN (
    SELECT current_cp.category_id
    FROM category_post AS current_cp
    WHERE current_cp.post_id = :current_post_id
)
  AND p.id <> :excluded_post_id
  AND p.published_at <= NOW()
GROUP BY p.id,
         p.title,
         p.slug,
         p.description,
         p.image_path,
         p.published_at
ORDER BY common_categories DESC, p.published_at DESC
LIMIT 3;
```

Запрос сначала выбирает категории текущей статьи, затем находит статьи из этих категорий. Статьи с большим числом общих категорий идут первыми.

При настоящих подготовленных запросах MySQL одно именованное значение не следует использовать дважды. Поэтому здесь используются параметры `:current_post_id` и `:excluded_post_id`, которым передаётся одинаковый идентификатор.

## 12. Транзакции

Транзакция нужна, когда несколько изменений должны выполниться как единое целое. Например: создать статью и добавить все связи с категориями.

```php
$pdo->beginTransaction();

try {
    // INSERT статьи.
    // INSERT связей с категориями.

    $pdo->commit();
} catch (Throwable $exception) {
    $pdo->rollBack();
    throw $exception;
}
```

Если создание одной из связей завершится ошибкой, `rollBack()` отменит и создание самой статьи.

Миграции с DDL-командами (`CREATE TABLE`, `ALTER TABLE`) требуют отдельной осторожности: MySQL может автоматически зафиксировать транзакцию при выполнении таких команд.

## 13. Частые ошибки

### Склеивание SQL с пользовательскими данными

Плохо:

```php
$sql = "SELECT * FROM posts WHERE slug = '" . $_GET['slug'] . "'";
```

Хорошо:

```php
$statement = $pdo->prepare(
    'SELECT id, title FROM posts WHERE slug = :slug LIMIT 1'
);
$statement->execute(['slug' => $slug]);
```

### SQL внутри Smarty

Шаблон должен только отображать уже подготовленные данные. Запросы помещаются в репозитории.

### Неоднозначные имена столбцов

При `JOIN` у разных таблиц могут быть одинаковые столбцы `id`. Используйте псевдонимы: `p.id`, `c.id`, `cp.post_id`.

### Пагинация без ORDER BY

Без стабильной сортировки записи между страницами могут появляться в неожиданном порядке.

### N+1-запросы

Не следует внутри цикла по статьям выполнять отдельный запрос для каждой категории. Сначала подумайте, можно ли получить данные одним `JOIN` или вторым запросом сразу для всех идентификаторов.

## 14. Как тренироваться перед реализацией

Выполните самостоятельно по порядку:

1. Создайте одну категорию через `INSERT`.
2. Получите её по `slug`.
3. Создайте две статьи.
4. Свяжите обе статьи с категорией.
5. Получите статьи категории через `JOIN`.
6. Отсортируйте их сначала по дате, затем по просмотрам.
7. Добавьте `LIMIT` и `OFFSET`.
8. Посчитайте количество статей через `COUNT(*)`.
9. Увеличьте просмотры через `UPDATE`.
10. Объясните вслух каждую часть каждого запроса.

После этих упражнений можно переходить к репозиториям PHP.

## 15. Официальная документация

- [PDO::prepare — подготовленные запросы](https://www.php.net/manual/ru/pdo.prepare.php)
- [PDOStatement::execute — выполнение запроса](https://www.php.net/manual/ru/pdostatement.execute.php)
- [PDOStatement::fetch — получение одной строки](https://www.php.net/manual/ru/pdostatement.fetch.php)
- [PDOStatement::fetchAll — получение списка](https://www.php.net/manual/ru/pdostatement.fetchall.php)
- [Транзакции PDO](https://www.php.net/manual/ru/pdo.transactions.php)
- [MySQL: SELECT](https://dev.mysql.com/doc/refman/8.4/en/select.html)
- [MySQL: JOIN](https://dev.mysql.com/doc/refman/8.4/en/join.html)
- [MySQL: EXPLAIN](https://dev.mysql.com/doc/refman/8.4/en/using-explain.html)
