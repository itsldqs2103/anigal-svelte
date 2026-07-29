import { router } from "@inertiajs/svelte";

router.on("httpException", (e) => {
  e.preventDefault();
  location.reload();
});

router.on("networkError", (e) => {
  e.preventDefault();
  location.reload();
});
