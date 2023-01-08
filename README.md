# e-gradebook

This project is simple e-gradebook desgined to manage students grades. It has three user types:

- admin
- instructor
- student

### Admin User

Admin can manage users, add new students and instructors. Can add new class groups and subjects to the system, but this account is not priviliged to add grade for student.

### Instructor

Instructor can view class groups data, can preview student grades and add or edit grade for particular student.

### Student

The least priviliged account in the system. Can view grades and notificiations about new grades. Can see who and when has added  grade assigned to the account in e-gradebook.

### Setup locally

First things first clone the repo:
`git clone git@github.com:M4rcinJ/e-gradebook.git`

Then we need to install dependencies
`composer install`

Also we can not forget about frontend dependencies
`npm install && npm run build`

Next up create docker container:
`docker-compose up -d`

Create database using php/console
`php bin/console doctrine:database:create`

Run migrations
`php bin/console doctrine:migration:migrate`

Load data fixtures
`php bin/console doctrine:fixtures:load`

and that is it! You should be good to go
`symfony server:start`