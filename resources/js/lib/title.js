import { writable } from "svelte/store";

export const title = writable(import.meta.env.VITE_APP_NAME);
