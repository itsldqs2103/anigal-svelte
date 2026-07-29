import { router } from "@inertiajs/svelte";
import NProgress from "nprogress";

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
