#!/bin/sh

set -e
set -u

# Database precontest
psql -v ON_ERROR_STOP=1 --username "$POSTGRES_USER" "$POSTGRES_DB" <<-EOSQL
create database precontest;
EOSQL

psql -v ON_ERROR_STOP=1 --username "$POSTGRES_USER" --dbname precontest <<-EOSQL
create schema if not exists public;
EOSQL
