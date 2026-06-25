# e2e runner: Node + headless Chromium, so the e2e suite needs no local
# Node, Chrome, PHP, or Python on the host. Built and driven by run-e2e.sh.
FROM node:20-bookworm-slim

RUN apt-get update \
 && apt-get install -y --no-install-recommends \
      chromium \
      ca-certificates \
      fonts-liberation \
 && rm -rf /var/lib/apt/lists/*

# puppeteer-core (the only puppeteer that @wordpress/scripts ships) has no
# bundled browser; point it at the distro Chromium.
ENV PUPPETEER_EXECUTABLE_PATH=/usr/bin/chromium

WORKDIR /app
