# Secure Todo Web Application

## Introduction

This app is a secure todo list web application developed in php, it aims to provide a secure platform for users to create and manage their tasks or "todo"s

The application aims to demonstrate the understanding of secure **programming**, **integration and deployment pipelines**.

## Features

- User login and registration
- Display upcoming user tasks
- Allow users to create new tasks
- Allow users to "pop" tasks to delete them

&nbsp;

- The app presents the user with an easy to learn interface
- Inputs are validated and sanitized to optimizes security

## Installation

### Without Docker

After downloading/cloning the files in the repo make sure to
**create the .env file and add the environmental variables with their values** then proceed with the following steps

1. Access your local SQL server and import the .sql file that can be found here `./db/todo.sql `
2. After importing make sure you **do not change the database name** in the code files
3. Start your local HTTP server and navigate to the right directory in your browser `./src/index.php`

### With Docker

After downloading/cloning the files in the repo make sure to
**create the .env file and add the environmental variables with their values** then proceed with the following steps

1. Navigate to the program directory in your docker terminal `../secure-todo-app`
2. Ensure that the **dockerfile** and **docker-compose** are present and in the root directory `./dockerfile` `./dockery-compose.yml`
3. Build and run with Docker using

> docker compose up --build 4. After starting the application can be accessed on `localhost:5000` in your browser

## Testing

The application comes with a number of tests out of the box that can be found in `./tests/` the docker file already copies the necessary files and installs the plugins to be able to run the tests

While the application is running locally using Docker, in the terminal:

1. Enter the web container using
   > \secure-todo-app> docker compose exec web bash
2. Inside the web container install composer
   > /var/www/html# composer install
3. Run the phpunit
   > /var/www/html# vendor/bin/phpunit

&nbsp;

It is also possible to see the tests being ran through the GitHub Actions tab. The workflow file runs those tests and many other checkups every time a commit is pushed to make sure the pushed version is safe to be merged with the Main branch.

## CI & CD Pipelines

### Continuous Integration

1. Code is written locally and tests are ran using Docker
2. Pipeline is triggered automatically every time code is pushed to Main using GitHub Actions
3. The workflow runner then clones the source code and runs it in a virtual machine to make sure it runs using `checkout`
4. The `setup-php` plugin then installs php and enables the required extensions such as mysqli and composer
5. A temporary instance of the database is ran in parallel to be used in the tests on port 3306, the SQL dump is then imported
6. Dependencies are installed and environmental variables are set
7. PHPUnit tests are ran
8. Docker image is built and scanned using Trivy
9. Docker image is pushed to Docker Hub using username and token

### Continuous Deployment

From here there are a number of IaaS, and cloud application platforms that are able to connect to the repo and automatically be configured to deploy the application every time a success push and merge is done.
