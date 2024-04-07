### Building project

## Features

- Php 8.2.0
- Mariadb 10.4

## Requirements

- [Git](https://git-scm.com/downloads)
- [Docker](https://store.docker.com/editions/community/docker-ce-desktop-mac) >= 25.0.3

## Installation

### Clone source docker

```bash
git clone https://github.com/AuNguyen1999/athena-test.git
```

After that, let's following below command:

```bash
cd athena-test
```

```bash
docker-compose build
```

Waiting for a while to finish building containers. Then start run containers.

```bash
docker-compose up -d
```

### Setup Drupal

Open the workspace container, then install Composer and import the database for the project

```bash
docker exec -it athena_web bash
```

```bash
composer install
```

```bash
zcat database/athena.sql.gz | drush sqlc
```

### Site

- Login page: [http://localhost/user/login](http://localhost/user/login)
- Articles page: [http://localhost/articles](http://localhost/articles)
- Admin account: admin/0258.tzPvh=,

### Demo

![DEMO](demo/demo.gif)
