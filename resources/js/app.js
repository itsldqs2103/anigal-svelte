import "@/css/app.css";
import "./lib/i18n";

import { createInertiaApp } from "@inertiajs/svelte";
import { router } from "@inertiajs/svelte";
import NProgress from "nprogress";

import Main from "@/js/Layouts/Main.svelte";

NProgress.configure({
  parent: "#nprogress-container",
  showSpinner: false,
  template: `<div class="bar" role="bar"><div class="peg"></div></div>`,
});

let timeout = null;

router.on("start", () => {
  timeout = setTimeout(() => NProgress.start(), 250);
});

router.on("progress", (event) => {
  if (NProgress.isStarted() && event.detail.progress.percentage) {
    NProgress.set((event.detail.progress.percentage / 100) * 0.9);
  }
});

router.on("finish", (event) => {
  clearTimeout(timeout);

  if (!NProgress.isStarted()) {
    return;
  }

  if (event.detail.visit.completed) {
    NProgress.done();
  } else if (event.detail.visit.interrupted) {
    NProgress.set(0);
  } else if (event.detail.visit.cancelled) {
    NProgress.done();
    NProgress.remove();
  }
});

router.on("httpException", (e) => {
  e.preventDefault();
  location.reload();
});

router.on("networkError", (e) => {
  e.preventDefault();
  location.reload();
});

createInertiaApp({
  defaults: {
    visitOptions: () => {
      return { viewTransition: true };
    },
  },
  progress: false,
  layout: () => Main,
});
