# Docker Setup for pgBoard

## Quick Start

### 1. Copy Production Config Files

First, create or copy the configuration files required to run the board:

This will copy:
- `config.php`
- `class/Plugin.php`
- `lang/en.php`
- `lang/en_header.php`
- `lang/en_footer.php`

### 2. Update Database Configuration

If necessary, edit `config.php` and update the database connection string:

```php
define("DB", "dbname=board user=board password=board host=db");
define("SPHINX_HOST", "sphinx");
define("SPHINX_PORT", 9312);
define("DIR", "/var/www/html/");
```

### 3. Start Docker Containers

```bash
docker-compose up -d
```

This will start:
- PostgreSQL database on port 5432
- Sphinx search on port 3312
- Web server (nginx + PHP-FPM) on port 8080

### 4. Initialize Database

On first run, the SQL scripts in `/doc` will automatically run to initialize the database schema.

### 5. Access the Application

Open your browser to: http://localhost:8080

## Common Commands

```bash
# View logs
docker-compose logs -f web

# Restart services
docker-compose restart

# Stop all services
docker-compose down

# Stop and remove volumes (WARNING: This deletes the database!)
docker-compose down -v

# Rebuild containers after Dockerfile changes
docker-compose up -d --build
```

## Database Access

To connect to PostgreSQL directly:

```bash
docker exec -it pgboard_db psql -U board -d board
```

## File Persistence

Your local files are mounted into the container, so any changes you make locally or inside the container will be reflected immediately.

## Troubleshooting

### Permission Issues

If you encounter permission errors:

```bash
docker exec -it pgboard_web chown -R www-data:www-data /var/www/html
```

### Database Connection Issues

Make sure the database is ready:

```bash
docker-compose logs db
```

### Nginx/PHP Issues

Check the web server logs:

```bash
docker-compose logs web
```
