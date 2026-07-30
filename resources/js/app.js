import "@/css/app.css";
import "@/js/lib/i18n";
import "@/js/lib/lazysizes";
import "@/js/lib/nprogress";
import "@/js/lib/script";

import { createInertiaApp } from "@inertiajs/svelte";

import Main from "@/js/Layouts/Main.svelte";

createInertiaApp({
  defaults: {
    visitOptions: () => {
      return { viewTransition: false };
    },
  },
  progress: false,
  layout: () => Main,
});
