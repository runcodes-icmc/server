# run.codes Server

This project houses the run.codes server (both the front-end and back-end), processing of
the submissions are made in another project. It currently is using PHP 5.6 with the CakePHP
2.4 framework. Both of those are already unmaintained since a long time, but upgrading it
isn't as simple of a task...

## Build & Run

One of the news in relationship to the old "website" project is the usage of composer to
manage the dependencies, instead of the static Vendors folder. Since running PHP locally
can be quite finicky, with a lot of dependencies and configurations needed, the main suggested
way of running and building the project is by using Docker Compose.

Because of that, be sure to have both **Docker** and **Docker Compose** installed. If you have
any doubts on how to do it, follow the [official guide for Docker](https://docs.docker.com/engine/install/)
and the [official guide for Docker Compose](https://docs.docker.com/compose/install/).

### Configuration

For easier deployments, the project was adapted to support configuration based on environment variables,
for more info check the `docs/configuration.md` file.

### Example Commands

All of those commands, unless explicitly said otherwise, are meant to be ran inside the repository
root (the same at which this file is into).

#### Starting the cluster

```sh
docker compose up --build -d
```

This command brings up all the containers defined in the `docker-compose.yml` file. The `-d` argument
runs the containers in _detached_ mode (without locking the terminal / in the background), the `--build`
command triggers the rebuild of applicable containers.

#### Stopping the cluster

```sh
docker compose down
```

This command brings down all the containers defined in the `docker-compose.yml`. Additionally, if
you also want to delete associated volumes (like the one which holds the database info), you can
add the `-v` argument.

#### Accessing the logs

```sh
docker compose logs
```

This command list the logs of all the containers defined in the `docker-compose.yml`. You can add
some useful flags to control the output, like the `--tail n` which will only show the last `n` log
lines and the `--follow`, which will keep the command running, showing all new logs (those can
be combined, since the `--follow` without the `--tail` will first print all the logs, then follow).

#### Running commands inside the containers

```sh
docker compose exec {container} {command with args and all}

# Example (for accessing the psql CLI):
docker compose exec database psql -U postgres
```

This command executes a command inside the target container (if you want a shell, you can run `bash`
or `sh`, for example).

#### Additional

There are lots of additional commands and possibilities with Docker Compose, feel free to take a look
at the docs or the help command (`docker compose --help` or `docker compose {command} --help`).

One **very useful** tip is the possibility of running most of the "general" commands on only one container.
A very common example is rebuilding and restarting only one container, such as the `app` one (which holds
the PHP application itself), for doing such you could run `docker compose up app -d --build`, which will
rebuild and recreate only the `app` container (for reloading changes, for example).

## Future

There are two main paths forward: first, we should try and update the current project's
dependencies, mainly CakePHP (migrate it from 2.4 to the latest 2.X) and PHP (from 5.6 to 7.X).
Apart from that, we should also look towards modernizing the code and separate the front-end from
the back-end, but this requires a deeper planning to ensure choices that make the project easier
to maintain in the long run.

## Known Issues

During internal testing, we found some issues with the current project, which are listed below:

### The server goes down after some hours running

This is something that we are still investigating the cause. My current hypothesis is that there
is some kind of memory leakage in the current code. The server overall takes a long time to stop
working, more than 24 hours in our testing (your millage may vary), but it does stop working
eventually (the login page renders but doesn't work...). The **very temporary** solution for this
is to restart the server every day or so The restarts are usually very fast. If you are running a
Dockerized deployment, which is recommended, you can simply create a cron job to restart the container
every few hours or at fixed times. It is also possible to configure Caddy to load balance between two
servers and ensure zero downtime, but IMO it is overkill for most use cases.

We hope to fix this soon, but it might not be worth it since we are planning on migrating the
project to a newer version of PHP and CakePHP, and later even to a different language/framework.

### Batch file operations

Some batch operations, like uploading multiple test cases at the same time and downloading all the submissions
are currently not working. It seems to be caused by some API incompatibilities between the originally used
AWS S3 and the now used SeaweedFS. We are currently working on fixing this, and soon should be back to normal.

## License

For information on the license of this project, please see our [license file](LICENSE.md).

## Contributors

For information of the contributors of this project, please see our [contributors file](CONTRIBUTORS.md).

## Contributing

For information on contributing to this project, please see our [contribution guidelines](CONTRIBUTING.md).
