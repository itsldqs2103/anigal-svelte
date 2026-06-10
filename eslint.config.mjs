import path from "node:path";

import { includeIgnoreFile } from "@eslint/compat";
import js from "@eslint/js";
import { defineConfig } from "eslint/config";
import eslintPluginPrettierRecommended from "eslint-plugin-prettier/recommended";
import simpleImportSort from "eslint-plugin-simple-import-sort";
import svelte from "eslint-plugin-svelte";
import unusedImports from "eslint-plugin-unused-imports";
import globals from "globals";

import svelteConfig from "./svelte.config.js";

const gitignorePath = path.resolve(import.meta.dirname, ".gitignore");

export default defineConfig([
    includeIgnoreFile(gitignorePath),

    js.configs.recommended,
    svelte.configs.recommended,
    eslintPluginPrettierRecommended,
    {
        files: ["**/*.{js,mjs,ts,svelte}"],
        languageOptions: {
            globals: { ...globals.browser, ...globals.node },
            parserOptions: { svelteConfig },
        },
    },

    {
        plugins: {
            "simple-import-sort": simpleImportSort,
            "unused-imports": unusedImports,
        },
        rules: {
            "prettier/prettier": "warn",
            "svelte/no-at-html-tags": "warn",
            "simple-import-sort/imports": "warn",
            "simple-import-sort/exports": "warn",
            "unused-imports/no-unused-imports": "warn",
            "unused-imports/no-unused-vars": "warn",
        },
    },
]);
