Set up
---

1. Download `db_generic` from https://github.com/rudiedirkx/db_generic
2. Create a writable dir `./db/`

Process
---

Run the following files with PHP on the commandline:

1. **@todo** `set-up-db.php` creates the relevant tables in your local SQLite db `db/drupaldeps.sqlite3`
2. `dl-projects.php` downloads ALL projects' metadata
3. `dl-releases.php` downloads the newest 7.x release for every known project
4. `dl-dependencies.php` downloads the module ZIP files and parses all `.info` files
   to find module dependencies
