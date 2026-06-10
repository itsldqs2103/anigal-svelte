import inertia from "@inertiajs/vite";
import { wayfinder } from "@laravel/vite-plugin-wayfinder";
import { svelte } from "@sveltejs/vite-plugin-svelte";
import tailwindcss from "@tailwindcss/vite";
import laravel from "laravel-vite-plugin";
import { defineConfig } from "vite";
import manifestSRI from "vite-plugin-manifest-sri";

export default defineConfig({
  plugins: [
    laravel({
      input: ["resources/css/app.css", "resources/js/app.js"],
      assets: ["resources/images/**", "resources/fonts/**"],
      refresh: true,
    }),
    wayfinder({
      path: "resources/js/wayfinder",
      routes: false,
      actions: true,
    }),
    inertia({
      ssr: false,
    }),
    svelte(),
    tailwindcss(),
    manifestSRI(),
  ],
  server: {
    watch: {
      ignored: ["**/storage/framework/views/**"],
    },
  },

  resolve: {
    alias: {
      "@": "/resources",
    },
  },

  build: {
    cssMinify: "lightningcss",
    minify: "terser",
    target: "baseline-widely-available",
    license: false,
    chunkSizeWarningLimit: 1024,
    sourcemap: false,
    rolldownOptions: {
      output: {
        hashCharacters: "base64",
        assetFileNames: "assets/[name].[hash][extname]",
        chunkFileNames: "assets/[name].[hash].js",
        entryFileNames: "assets/[name].[hash].js",
      },
    },
  },
});
