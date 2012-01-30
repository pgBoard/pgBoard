# a lightweight php+postgresql messageboard

Requirements:
Apache /w mod_rewrite enabled
PHP 5x
PostgreSQL 8x
Sphinx 0.9x

Caveats:
Currently the board software does not support being installed in a folder, ie:
http://www.mywebsite.com/board/

Installation:
1. Create database in PostgreSQL.
2. Run the SQL creation scripts in /doc/ (skip 0-Migrate)
3. Rename /config.default.php to config.php and modify as needed.
4. Rename /lang/en.default.php to en.php and modify as needed.
5. Rename /lang/en_header.default.php to en_header.php and modify as needed.
6. Rename /lang/en_footer.default.php to en_footer.php and modify as needed.
7. Rename /class/Plugin.default.php to Plugin.php and modify as needed.

More details to follow.
