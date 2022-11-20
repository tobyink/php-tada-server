# php-tada-server

A small PHP script that allows you to GET/PUT/POST text blobs.

It is intended for use with [tada](https://github.com/tobyink/rust-tada),
though in theory can be used for other purposes.

## Installation

Create a database and create the tables defined in `sql/schema.sql`.
Edit `index.php` to point to the correct database. If you're using SQLite,
then make sure the user Apache is running under has permissions to write
to the SQLite database file.

Optionally set up an `.htaccess` file to ensure that `https://SERVER/PATH/foo`
is internally rewritten to `https://SERVER/PATH/index.php/foo`. An example:

```text
RewriteEngine on
RewriteBase /
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_FILENAME} !-l
RewriteRule (.*) /PATH/index.php/$1 [L]
```

Insert rows into the `user` table for as many users as you like:

```sql
INSERT INTO user (username, token) VALUES ('alice', 'ALICE-TOKEN-XYZ');
```

## Basic Usage

### Creating a text file

The following HTTP `PUT` request should create a file:

```text
PUT /PATH/index.php/some/file.txt
Authorization: Bearer ALICE-TOKEN-XYZ
Content-Type: text/plain
Host: SERVER

Here is the file content.
```

### Updating a text file

The following HTTP `PUT` request should update the file with new content:

```text
PUT /PATH/index.php/some/file.txt
Authorization: Bearer ALICE-TOKEN-XYZ
Content-Type: text/plain
Host: SERVER

Here is the new file content.
```

`POST` will also work.

### Retrieving a text file

The following HTTP `GET` request should retrieve the file:

```text
GET /PATH/index.php/some/file.txt
Authorization: Bearer ALICE-TOKEN-XYZ
Host: SERVER
```

## Advanced Usage

Updating a file supports the `If-Match` and `If-Unmodified-Since` headers,
which may help avoid race conditions.

By default, all users can read and write files that they own, and the initial
creator of a file will be the user to own it. The `permission` table may be
used to provide read or write access to other users.

There is currently no UI for creating new users, editing permissions,
listing files, etc.

## Issues and Workarounds

### The `Authorization` Header

Apache sometimes hides this from environment variables for security reasons.
Try using `X-Tada-Authorization` in that case.

## Licence

This project is triple licensed under the [Apache License, version 2.0](http://www.apache.org/licenses/LICENSE-2.0), the [MIT License](http://opensource.org/licenses/MIT), and the [GNU General Public License, version 2.0](https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html).

### Contribution

Unless you explicitly state otherwise, any contribution intentionally submitted for inclusion into this project by you, shall be triple licensed as Apache-2.0/MIT/GPL-2.0, without any additional terms or conditions.
