
# Food & Product Analyzer API

Проект на Laravel 10 для анализа продуктов, управления профилями, рецептами и историей сканов.  
Поддержка пользователей и администраторов с разграничением прав доступа.  

---

## 🔹 Основные возможности

- Регистрация и аутентификация пользователей (JWT / Sanctum)
- Управление профилями пользователей (CRUD)
- Управление рецептами (CRUD)
- Сканирование и анализ продуктов через Google Vision и Gemini AI
- История сканирования с разграничением доступа (только свои / все для admin)
- Фильтрация и пагинация
- Логи запросов и ошибок
- Роли и права доступа (permission middleware)
- Postman link : https://ajdanaamirtaj-1472534.postman.co/workspace/Fafaf-Pup's-Workspace~41021647-3e9e-4935-8359-542894b8cf39/collection/48116736-48adbbe2-9b23-42e2-a41c-08aa733e342f?action=share&source=copy-link&creator=48116736

---

## 🔹 Установка

```bash
git clone https://github.com/your-repo/food-analyzer.git
cd food-analyzer
composer install
cp .env.example .env
php artisan key:generate
````

Настрой `.env`:

```env
APP_NAME=FoodAnalyzer
APP_ENV=local
APP_KEY=base64:YOUR_KEY
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=food_analyzer
DB_USERNAME=root
DB_PASSWORD=

GEMINI_API_KEY=your_gemini_api_key
GEMINI_API_URL=https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent
```

Запуск миграций и сидов:

```bash
php artisan migrate
php artisan db:seed
php artisan serve
```

---

## 🔹 Аутентификация

### Регистрация

```
POST /api/auth/register
```

**Параметры:**

```json
{
  "name": "User Name",
  "email": "user@example.com",
  "password": "password",
  "password_confirmation": "password"
}
```

### Логин

```
POST /api/auth/login
```

**Параметры:**

```json
{
  "email": "user@example.com",
  "password": "password"
}
```

**Токен:** возвращается при логине, используется в заголовке:

```
Authorization: Bearer {token}
```

### Текущий пользователь

```
GET /api/auth/me
```

### Логаут

```
POST /api/auth/logout
```

---

## 🔹 Профили (Profile)

| Метод  | URL                     | Доступ | Описание                  |
| ------ | ----------------------- | ------ | ------------------------- |
| GET    | /api/profiles/me        | user   | Получить свой профиль     |
| PUT    | /api/profiles/me        | user   | Обновить свой профиль     |
| DELETE | /api/profiles/me        | user   | Удалить свой профиль      |
| POST   | /api/profiles           | admin  | Создать профиль           |
| GET    | /api/profiles/{profile} | admin  | Просмотреть любой профиль |
| PUT    | /api/profiles/{profile} | admin  | Обновить любой профиль    |
| DELETE | /api/profiles/{profile} | admin  | Удалить любой профиль     |

**Пример тела запроса для создания/обновления:**

```json
{
  "full_name": "John Doe",
  "age": 30,
  "avatar_path": "avatar.png",
  "gender": "male",
  "height": 180,
  "weight": 75,
  "goal": "lose weight"
}
```

**Ответ:**

```json
{
  "id": 1,
  "user_id": 5,
  "full_name": "John Doe",
  "age": 30,
  "avatar_path": "avatar.png",
  "gender": "male",
  "height": 180,
  "weight": 75,
  "goal": "lose weight",
  "created_at": "2025-12-17T12:00:00.000000Z",
  "updated_at": "2025-12-17T12:00:00.000000Z"
}
```

---

## 🔹 Рецепты (Recipe)

| Метод  | URL                   | Доступ       | Описание                                   |
| ------ | --------------------- | ------------ | ------------------------------------------ |
| GET    | /api/recipes          | user / admin | Список рецептов (user → свои, admin → все) |
| POST   | /api/recipes          | user / admin | Создать рецепт                             |
| GET    | /api/recipes/{recipe} | user / admin | Просмотр рецепта                           |
| PUT    | /api/recipes/{recipe} | user / admin | Обновить рецепт                            |
| DELETE | /api/recipes/{recipe} | user / admin | Удалить рецепт                             |

**Фильтр по имени (query string):**

```
GET /api/recipes?name=Суп
```

**Пример тела запроса для создания/обновления:**

```json
{
  "name": "Tomato Soup",
  "image": "soup.png",
  "products": ["Tomato", "Onion", "Garlic"],
  "instructions": "Boil tomatoes and mix with onion and garlic.",
  "calories": 150
}
```

**Ответ:**

```json
{
  "id": 1,
  "user_id": 5,
  "name": "Tomato Soup",
  "image": "soup.png",
  "products": ["Tomato", "Onion", "Garlic"],
  "instructions": "Boil tomatoes and mix with onion and garlic.",
  "calories": 150,
  "created_at": "2025-12-17T12:00:00.000000Z",
  "updated_at": "2025-12-17T12:00:00.000000Z"
}
```

---

## 🔹 Scan History (ScanHistory)

| Метод  | URL                        | Доступ       | Описание                                 |
| ------ | -------------------------- | ------------ | ---------------------------------------- |
| GET    | /api/scan-histories        | user / admin | Список сканов (user → свои, admin → все) |
| GET    | /api/scan-histories/{scan} | user / admin | Просмотр скана                           |
| DELETE | /api/scan-histories/{scan} | admin        | Удаление скана                           |

**Фильтр по дате:**

```
GET /api/scan-histories?date_from=2025-01-01&date_to=2025-12-31
```

**Ответ:**

```json
{
  "id": 1,
  "image": "scan1.png",
  "result": "Detected ingredients: Tomato, Onion",
  "created_at": "2025-12-17T12:00:00.000000Z"
}
```

---

## 🔹 Анализ продуктов

### Google Vision / Spoonacular

```
POST /api/analyze-products
```

**Тело запроса:**

```json
{
  "image": "base64_image_data"
}
```

### Gemini AI

* Анализ состава:

```
POST /api/gemini-analyze
POST /api/gemini-contents
```

**Параметры:**

```json
{
  "image": "base64_image_data",
  "halal_check": true
}
```

**Ответ:** JSON с информацией о составе и халяль/опасных ингредиентах.

---

## 🔹 Логи и ошибки

* Все запросы логируются через `Log::info()`
* Ошибки через `Log::error()`
* Формат: user_id, payload, entity_id, дата и время

---

## 🔹 Технологии

* Laravel 10
* PHP 8+
* MySQL / MariaDB
* Spoonacular API
* Gemini AI API
* Laravel Sanctum
* Laravel Policies + Permission middleware

---

## 🔹 Структура проекта

```
app/
├─ Http/
│  ├─ Controllers/Api/
│  ├─ Requests/ (ProfileRequest, RecipeRequest, ScanHistoryRequest)
│  ├─ Resources/ (ProfileResource, RecipeResource, ScanHistoryResource)
├─ Models/
├─ Repositories/ (ProfileRepository, RecipeRepository, ScanHistoryRepository)
routes/
├─ api.php
```

---

## 🔹 Развертывание

1. Настройка `.env` (ключи API, база данных)
2. `composer install`
3. `php artisan migrate --seed`
4. `php artisan serve`

---

## 🔹 Примечания

* Для admin доступны все CRUD-операции и пагинация.
* Для user доступно только управление своими ресурсами.
* Все API-ключи и чувствительные данные хранятся в `.env`.
* Frontend может быть минимальным — кнопки → API → результат в базе.
