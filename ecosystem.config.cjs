// Load environment variables from .env
require("dotenv").config();

const PWD = process.cwd();

const env = process.env;
const namespace = env.APP_ENV || "production";

const defaultOptions = {
    cwd: PWD,
    namespace: namespace,
    autorestart: true,
    watch: false,
};

const useName = (name) => {
    return env.APP_ENV ? `${env.APP_ENV}.${name}` : name;
};

/** @type {import('pm2').StartOptions[]} */
const config = {
    apps: [
        {
            ...defaultOptions,
            name: useName("queue-worker"),
            script: "php artisan queue:work",
            args: "--sleep=3 --tries=3 --max-time=3600",
            max_memory_restart: "256M",
        },
    ],
};

module.exports = config;
