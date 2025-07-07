# News Aggregator Backend System

A comprehensive news aggregation backend system built with Laravel that fetches articles from multiple news sources and provides a RESTful API for frontend applications.

## Features

- **Multi-Source News Aggregation**: Fetches articles from NewsAPI.org, The Guardian, and The New York Times
- **RESTful API**: Complete API endpoints for article retrieval, filtering, and user preferences
- **User Preferences**: Support for personalized news feeds based on selected sources, categories, and authors
- **Advanced Filtering**: Search, category, author, source, and date range filtering
- **Scheduled Aggregation**: Automated news fetching with console commands
- **Database Storage**: Local storage of articles with deduplication
- **SOLID Principles**: Well-structured code following software development best practices

## Requirements

- PHP 8.2+
- Laravel 12.0+
- MySQL/PostgreSQL/SQLite
- Composer
- Docker (optional)

## Installation

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd news-aggregator
   ```

2. **Install dependencies**
   ```bash
   cd src
   composer install
   ```

3. **Environment setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Configure database**
   Update the `.env` file with your database credentials:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=news_aggregator
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```

5. **Configure API keys**
   Add your news API keys to the `.env` file:
   ```env
   NEWSAPI_API_KEY=your-newsapi-key-here
   GUARDIAN_API_KEY=your-guardian-key-here
   NYTIMES_API_KEY=your-nytimes-key-here
   ```

6. **Run migrations and seeders**
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

7. **Start the development server**
   ```bash
   php artisan serve
   ```

## API Documentation

### Public Endpoints

#### Articles

- `GET /api/articles` - Get articles with filtering
- `GET /api/articles/latest` - Get latest articles
- `GET /api/articles/categories` - Get available categories
- `GET /api/articles/authors` - Get available authors
- `GET /api/articles/sources` - Get available sources
- `GET /api/articles/{id}` - Get specific article

#### Aggregator

- `GET /api/aggregator/status` - Get system status
- `POST /api/aggregator/aggregate` - Trigger aggregation from all sources
- `POST /api/aggregator/aggregate/{sourceSlug}` - Trigger aggregation from specific source

### Protected Endpoints (Require Authentication)

#### User Preferences

- `GET /api/preferences` - Get user preferences
- `PUT /api/preferences` - Update user preferences
- `POST /api/preferences/sources/add` - Add source to preferences
- `DELETE /api/preferences/sources/remove` - Remove source from preferences
- `POST /api/preferences/categories/add` - Add category to preferences
- `DELETE /api/preferences/categories/remove` - Remove category from preferences
- `POST /api/preferences/authors/add` - Add author to preferences
- `DELETE /api/preferences/authors/remove` - Remove author from preferences

### Query Parameters

#### Article Filtering

- `search` - Search in title, description, and content
- `category` - Filter by category
- `author` - Filter by author
- `source_id` - Filter by news source ID
- `start_date` - Filter by start date (YYYY-MM-DD)
- `end_date` - Filter by end date (YYYY-MM-DD)
- `per_page` - Number of articles per page (1-100)
- `page` - Page number

### Example API Calls

```bash
# Get all articles
curl "http://localhost:8000/api/articles"

# Search for articles
curl "http://localhost:8000/api/articles?search=technology"

# Filter by category
curl "http://localhost:8000/api/articles?category=politics"

# Filter by date range
curl "http://localhost:8000/api/articles?start_date=2024-01-01&end_date=2024-01-31"

# Get latest articles
curl "http://localhost:8000/api/articles/latest"

# Trigger aggregation
curl -X POST "http://localhost:8000/api/aggregator/aggregate"

# Get system status
curl "http://localhost:8000/api/aggregator/status"
```

## Console Commands

### News Aggregation

```bash
# Aggregate from all sources
php artisan news:aggregate

# Aggregate from specific source
php artisan news:aggregate --source=newsapi
php artisan news:aggregate --source=guardian
php artisan news:aggregate --source=nytimes
```

### Scheduling

Add to your crontab for automatic aggregation:

```bash
# Run every hour
0 * * * * cd /path/to/your/project && php artisan news:aggregate

# Run every 30 minutes
*/30 * * * * cd /path/to/your/project && php artisan news:aggregate
```

## Database Schema

### Tables

1. **news_sources** - Configuration for news API sources
2. **articles** - Stored news articles
3. **user_preferences** - User preferences for filtering
4. **users** - User accounts (Laravel default)

### Key Relationships

- Articles belong to News Sources
- User Preferences belong to Users
- Articles can be filtered by User Preferences

## Architecture

### Models

- **NewsSource** - News source configuration
- **Article** - News article data
- **UserPreference** - User filtering preferences

### Controllers

- **ArticleController** - Article retrieval and filtering
- **UserPreferenceController** - User preference management
- **AggregatorController** - Aggregation control and monitoring

## Development

### Adding New News Sources

The news aggregation services have been removed from this application. To add new news sources, you would need to implement a new aggregation service.

### Testing

```bash
# Run tests
php artisan test

# Run specific test
php artisan test --filter=ArticleControllerTest
```

## Docker Setup

The project includes Docker configuration for easy development:

```bash
# Start containers
docker-compose up -d

# Run migrations
docker-compose exec app php artisan migrate

# Seed database
docker-compose exec app php artisan db:seed

# Note: News aggregation command has been removed
```

## API Keys

The news aggregation services have been removed, so API keys are no longer required.

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests
5. Submit a pull request

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT). 