<script>
  import { Link, page, router, useForm } from "@inertiajs/svelte";
  import { ChevronDown, Image, Search, Tags } from "@lucide/svelte";
  import { onMount } from "svelte";
  import Toastify from "toastify-js";

  import { api } from "@/js/lib/axios";
  import i18n from "@/js/lib/i18n";
  import { initializeTheme } from "@/js/lib/theme";
  import { title } from "@/js/lib/title";
  import { index as imageIndex } from "@/js/wayfinder/actions/App/Http/Controllers/ImageController";
  import { locale as postLocale } from "@/js/wayfinder/actions/App/Http/Controllers/MainController";
  import { index as tagIndex } from "@/js/wayfinder/actions/App/Http/Controllers/TagController";

  let { totalTags, totalImages, supportedLocales } = $props();

  const currentLocale = $derived(page.props.currentLocale);

  $effect(() => {
    if (currentLocale && $i18n.language !== currentLocale) {
      $i18n.changeLanguage(currentLocale).then(() => {
        document.documentElement.classList.remove(
          "invisible",
          "pointer-events-none",
        );
      });
    } else {
      document.documentElement.classList.remove(
        "invisible",
        "pointer-events-none",
      );
    }
  });

  onMount(() => {
    initializeTheme();
  });

  function handleImageError(event) {
    const img = event.target;
    if (img instanceof HTMLImageElement) {
      img.classList.add("hidden");
    }
  }

  onMount(() => {
    document.addEventListener("error", handleImageError, true);
    return () => {
      document.removeEventListener("error", handleImageError, true);
    };
  });

  function normalizeUrl(url) {
    const u = new URL(url, window.location.origin);

    const params = [...u.searchParams.entries()].sort(([a], [b]) =>
      a.localeCompare(b),
    );

    const search = new URLSearchParams(params).toString();

    return `${u.pathname}${search ? `?${search}` : ""}`;
  }

  router.on("before", (event) => {
    const visit = event.detail.visit;
    const method = visit.method?.toUpperCase();

    const isPost = method === "POST";
    const hasOnly = Array.isArray(visit.only) && visit.only.length > 0;
    const allowedPartialGets = method === "GET" && hasOnly;

    const isSameUrl =
      normalizeUrl(window.location.href) === normalizeUrl(visit.url);

    if (isSameUrl && !isPost && !allowedPartialGets) {
      event.preventDefault();
    }
  });

  document.addEventListener("dragstart", (e) => {
    if (e.target.closest("a, img")) {
      e.preventDefault();
    }
  });

  const toggleUI = (disabled) => {
    document.querySelectorAll("input, button, a").forEach((el) => {
      if (el instanceof HTMLInputElement || el instanceof HTMLButtonElement) {
        if (disabled) {
          el.dataset.wasDisabled = String(el.disabled);

          if (!el.disabled) {
            el.disabled = true;
          }
        } else {
          if (el.dataset.wasDisabled === "false") {
            el.disabled = false;
          }

          delete el.dataset.wasDisabled;
        }
      }

      if (el instanceof HTMLAnchorElement) {
        const lockClass = el.classList.contains("btn")
          ? "btn-disabled"
          : "pointer-events-none";

        if (disabled) {
          el.dataset.wasLocked = String(el.classList.contains(lockClass));

          if (!el.classList.contains(lockClass)) {
            el.classList.add(lockClass);
          }
        } else {
          if (el.dataset.wasLocked === "false") {
            el.classList.remove(lockClass);
          }

          delete el.dataset.wasLocked;
        }
      }
    });
  };

  router.on("start", () => toggleUI(true));
  router.on("finish", () => toggleUI(false));

  const form = useForm(() => ({
    locale: currentLocale,
  }));

  async function changeLocale(locale) {
    await api.post(postLocale().url, {
      locale,
    });

    location.reload();
  }

  router.on("flash", (event) => {
    const toast = event.detail.flash.toast;

    if (!toast) return;

    if (toast.success) {
      Toastify({
        text: toast.success,
        className: "rounded-base toastify-success",
        gravity: "bottom",
        position: "center",
        duration: 5000,
        oldestFirst: false,
      }).showToast();
    }

    if (toast.error) {
      Toastify({
        text: toast.error,
        className: "rounded-base toastify-error",
        gravity: "bottom",
        position: "center",
        duration: 5000,
        oldestFirst: false,
      }).showToast();
    }
  });

  function formatCount(count) {
    if (count < 100) return count.toString();

    const rounded = Math.floor(count / 100) * 100;
    return `${rounded}+`;
  }
</script>

<svelte:head>
  <title>{$title}</title>
</svelte:head>

<section
  class="mx-auto flex min-h-screen w-full max-w-6xl flex-col items-center justify-center px-4 py-10 sm:px-6 lg:px-8"
>
  <div class="mx-auto w-full max-w-3xl text-center">
    <h1 class="text-3xl leading-tight font-black sm:text-4xl md:text-5xl">
      {$i18n.t("translate.discoverimagesby")}
      <span class="text-primary">{$i18n.t("translate.tags")}</span>
    </h1>

    <p
      class="text-base-content/70 mx-auto mt-5 max-w-2xl text-base leading-relaxed sm:text-lg"
    >
      {$i18n.t("translate.landingdesc")}
    </p>

    <div
      class="mt-8 flex w-full flex-col gap-3 sm:mt-10 sm:w-auto sm:flex-row sm:justify-center"
    >
      <Link
        href={imageIndex({
          query: {
            per_page: 30,
            sort_by: "created_at",
            order: "latest",
          },
        })}
        class="btn btn-primary btn-md w-full sm:w-auto"
      >
        <Image class="inline aspect-square h-5 w-5" />
        {$i18n.t("translate.browse") +
          " " +
          $i18n.t("translate.images").toLowerCase()}
      </Link>

      <Link
        href={tagIndex({
          query: {
            sort_by: "tag_name",
            order: "oldest",
            per_page: 30,
            starts_with: null,
          },
        })}
        class="btn btn-primary btn-md w-full sm:w-auto"
      >
        <Tags class="inline aspect-square h-5 w-5" />
        {$i18n.t("translate.browse") +
          " " +
          $i18n.t("translate.tags").toLowerCase()}
      </Link>
    </div>
  </div>

  <div class="mt-14 grid w-full gap-5 sm:grid-cols-2 lg:mt-20 lg:grid-cols-3">
    <div class="card bg-base-200 border-base-300 h-full border">
      <div class="card-body items-center text-center">
        <Image class="text-primary inline aspect-square h-10 w-10" />
        <h2 class="card-title">{$i18n.t("translate.imagecollection")}</h2>
        <p class="text-base-content/70">
          {$i18n.t("translate.imagecollectiondesc")}
        </p>
      </div>
    </div>

    <div class="card bg-base-200 border-base-300 h-full border">
      <div class="card-body items-center text-center">
        <Tags class="text-primary inline aspect-square h-10 w-10" />
        <h2 class="card-title">{$i18n.t("translate.organizedtags")}</h2>
        <p class="text-base-content/70">
          {$i18n.t("translate.organizedtagsdesc")}
        </p>
      </div>
    </div>

    <div class="card bg-base-200 border-base-300 h-full border">
      <div class="card-body items-center text-center">
        <Search class="text-primary inline aspect-square h-10 w-10" />
        <h2 class="card-title">{$i18n.t("translate.fastdiscovery")}</h2>
        <p class="text-base-content/70">
          {$i18n.t("translate.fastdiscoverydesc")}
        </p>
      </div>
    </div>
  </div>

  <div
    class="stats stats-vertical md:stats-horizontal mt-14 w-full overflow-hidden shadow lg:mt-20"
  >
    <div class="stat">
      <div class="stat-title">{$i18n.t("translate.images")}</div>
      <div class="stat-value text-primary">{formatCount(totalImages)}</div>
      <div class="stat-desc">{$i18n.t("translate.growingcollection")}</div>
    </div>

    <div class="stat">
      <div class="stat-title">{$i18n.t("translate.tags")}</div>
      <div class="stat-value text-primary">{formatCount(totalTags)}</div>
      <div class="stat-desc">{$i18n.t("translate.wellorganized")}</div>
    </div>

    <div class="stat">
      <div class="stat-title">{$i18n.t("translate.search")}</div>
      <div class="stat-value text-primary">{$i18n.t("translate.fast")}</div>
      <div class="stat-desc">{$i18n.t("translate.findimagesinstantly")}</div>
    </div>
  </div>

  <div class="mt-8 flex w-full justify-center">
    <div class="dropdown dropdown-start">
      <button class="btn btn-primary btn-soft w-full sm:w-auto">
        {supportedLocales.find((l) => l.code === currentLocale)?.name}
        <ChevronDown class="inline aspect-square h-4 w-4" />
      </button>

      <ul
        class="dropdown-content menu bg-base-200 rounded-box mt-2 w-40 space-y-1 p-2"
      >
        {#each supportedLocales as locale (locale.code)}
          <li>
            <button
              disabled={currentLocale === locale.code || form.processing}
              class={currentLocale === locale.code
                ? "bg-primary text-primary-content"
                : ""}
              onclick={(e) => {
                changeLocale(locale.code);
                e.currentTarget.blur();
              }}
              type="button"
            >
              {locale.name}
            </button>
          </li>
        {/each}
      </ul>
    </div>
  </div>
</section>
