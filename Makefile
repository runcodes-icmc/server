build-server:
	docker build -t ghcr.io/runcodes-icmc/server:latest -f ./Dockerfile .

build-caddy:
	docker build -t ghcr.io/runcodes-icmc/server-caddy:latest -f ./Dockerfile.caddy .

all: build-server build-caddy

.PHONY: all
