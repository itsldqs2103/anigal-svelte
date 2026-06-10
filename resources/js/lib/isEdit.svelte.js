import { persistedState } from "@/js/lib/persisted.svelte";

export const isUserEdit = persistedState("isUserEdit", false);
