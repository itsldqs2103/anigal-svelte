import axios from "axios";

const csrfToken = document
    .querySelector('meta[name="csrf-token"]')
    ?.getAttribute("content");

export const api = axios.create({
    headers: {
        "X-CSRF-Token": csrfToken,
    },
});
