<p align="center"><a href="https://news-aggregator.com" target="_blank"><img src="https://news-aggregator.com/logo.svg" width="400" alt="News Aggregator Logo"></a></p>

<p align="center">
<a href="https://github.com/news-aggregator/actions"><img src="https://github.com/news-aggregator/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/news-aggregator/framework"><img src="https://img.shields.io/packagist/dt/news-aggregator/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/news-aggregator/framework"><img src="https://img.shields.io/packagist/v/news-aggregator/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/news-aggregator/framework"><img src="https://img.shields.io/packagist/l/news-aggregator/framework" alt="License"></a>
</p>

## About News Aggregator

News Aggregator is a web application designed to collect and display news articles from various sources in one place. It provides an intuitive interface for users to stay updated on the latest news across multiple categories. Key features include:

- Simple, fast article aggregation engine.
- Powerful filtering and categorization options.
- Support for multiple news APIs and RSS feeds.
- Real-time updates and notifications for breaking news.
- User-friendly interface for browsing and searching articles.

News Aggregator is accessible, powerful, and provides tools required for a seamless news reading experience.

## Database Usage

News Aggregator uses multiple databases to optimize performance and scalability:

- **MongoDB**: All news articles are stored in MongoDB. This NoSQL database is chosen for its flexibility in handling unstructured and semi-structured data, which is ideal for aggregating articles from diverse sources with varying formats. MongoDB's schema-less nature allows for easy adaptation to changes in article structure and supports efficient querying for large volumes of news data.
- **Relational Database (e.g., MySQL)**: Used for News sources and user table.

**Why MongoDB for Articles?**

Articles from different news sources often have inconsistent fields and metadata. MongoDB allows the application to store each article as a document, accommodating variations without requiring schema migrations. This flexibility, combined with high performance for read-heavy workloads, makes MongoDB a natural fit for storing and retrieving news articles efficiently.

## Console Commands

### Fetching News Articles

The application provides console commands to fetch articles from configured news sources. These commands use service implementations to interact with external APIs and store articles in the database.

#### Basic Usage

```bash
# Fetch articles from all active news sources
php artisan news:fetch

# Fetch articles from a specific source
php artisan news:fetch --source=newsapi

# Dry run (show what would be fetched without storing)
php artisan news:fetch --dry-run

# Verbose output (show detailed information)
php artisan news:fetch --verbose

# Combine options
php artisan news:fetch --source=guardian --dry-run --verbose


### Scheduled Execution

The `news:fetch` command is scheduled to run automatically at specific intervals to ensure timely aggregation of news articles:

- **NewsAPI**: Runs every 5 minutes
- **The Guardian**: Runs every 10 minutes
- **NYTimes**: Runs every 15 minutes

This scheduling is configured in the application's [bootstrap/app.php](src/bootstrap/app.php) file using Laravel's task scheduling feature. Logs for each execution are appended to `storage/logs/news-fetch.log`.

#### Example Scheduling Configuration

```php
$schedule->command('news:fetch --source=newsapi')
    ->everyFiveMinutes()
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/news-fetch.log'));

$schedule->command('news:fetch --source=guardian')
    ->everyTenMinutes()
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/news-fetch.log'));

$schedule->command('news:fetch --source=nytimes')
    ->everyFifteenMinutes()
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/news-fetch.log'));