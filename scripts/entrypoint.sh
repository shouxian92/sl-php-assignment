#!/bin/bash
if [[ -z "${PORT}" ]]; then
  PORT=80
fi

php -S $APP_URL:$PORT -t public