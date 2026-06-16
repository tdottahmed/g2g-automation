/**
 * HTTP client for the Laravel automation API.
 * Reads LARAVEL_API_URL and API_KEY from environment.
 */

const BASE_URL = (process.env.LARAVEL_API_URL ?? "").replace(/\/$/, "");
const API_KEY  = process.env.API_KEY ?? "";

if (!BASE_URL || !API_KEY) {
    throw new Error(
        "LARAVEL_API_URL and API_KEY must be set in your .env file."
    );
}

async function request(method, path, body = null) {
    const url  = `${BASE_URL}/api${path}`;
    const opts = {
        method,
        headers: {
            "Content-Type": "application/json",
            "Accept":        "application/json",
            "X-Api-Key":     API_KEY,
        },
    };

    if (body) opts.body = JSON.stringify(body);

    const res = await fetch(url, opts);

    if (!res.ok) {
        const text = await res.text().catch(() => "");
        throw new Error(`API ${method} ${url} → ${res.status}: ${text}`);
    }

    return res.json();
}

/** Check connectivity and auth. */
export async function heartbeat() {
    return request("GET", "/automation/heartbeat");
}

/**
 * Fetch users and their templates that are ready to be posted now.
 * @returns {{ users: Array, schedule_interval_minutes: number, server_time: string }}
 */
export async function fetchPending() {
    return request("GET", "/automation/pending");
}

/**
 * Report a successful posting for one template.
 * @param {number} templateId
 * @param {object} details  - arbitrary execution metadata to store in logs
 */
export async function reportSuccess(templateId, details = {}) {
    return request("POST", `/automation/${templateId}/success`, { details });
}

/**
 * Report a failed posting for one template.
 * @param {number} templateId
 * @param {string} error
 * @param {object} details
 */
export async function reportFailed(templateId, error, details = {}) {
    return request("POST", `/automation/${templateId}/failed`, { error, details });
}
