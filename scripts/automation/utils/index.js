
import readline from 'readline';

export const delay = (ms) => new Promise((res) => setTimeout(res, ms));

export const rl = readline.createInterface({
    input: process.stdin,
    output: process.stdout
});
