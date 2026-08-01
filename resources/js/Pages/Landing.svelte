<script>
  import { Link, page, router, useForm } from "@inertiajs/svelte";
  import { ChevronDown, Image, Search, Tags } from "@lucide/svelte";
  import { onMount } from "svelte";

  import i18n from "@/js/lib/i18n";
  import { initializeTheme } from "@/js/lib/theme";
  import { title } from "@/js/lib/title";
  import { index as imageIndex } from "@/js/wayfinder/actions/App/Http/Controllers/ImageController";
  import { locale as postLocale } from "@/js/wayfinder/actions/App/Http/Controllers/MainController";
  import { index as tagIndex } from "@/js/wayfinder/actions/App/Http/Controllers/TagController";

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

  let { supportedLocales } = $props();

  const form = useForm(() => ({
    locale: currentLocale,
  }));

  function changeLocale(locale) {
    form.locale = locale;
    form.post(postLocale());
  }
</script>

<svelte:head>
  <title>{$i18n.t("translate.home")} - {$title}</title>
</svelte:head>

<section
  class="mx-auto flex min-h-screen max-w-6xl flex-col items-center justify-center"
>
  <div class="mx-auto max-w-3xl text-center">
    <div class="badge badge-primary badge-outline mb-6">
      Free Image Tag Database
    </div>

    <h1 class="text-5xl leading-tight font-black md:text-6xl">
      Discover Images by
      <span class="text-primary">Tags</span>
    </h1>

    <p
      class="text-base-content/70 mx-auto mt-6 max-w-2xl text-lg leading-relaxed"
    >
      Explore a growing collection of tagged images. Search by keywords,
      discover related content, and find exactly what you're looking for in
      seconds.
    </p>

    <div class="mt-10 flex flex-col justify-center gap-4 sm:flex-row">
      <Link href={imageIndex()} class="btn btn-primary btn-lg">
        <Image class="inline aspect-square h-5 w-5" />
        Browse Images
      </Link>

      <Link href={tagIndex()} class="btn btn-primary btn-lg">
        <Tags class="inline aspect-square h-5 w-5" />
        Browse Tags
      </Link>
    </div>
  </div>

  <div class="mt-20 grid gap-6 md:grid-cols-3">
    <div class="card bg-base-200 border-base-300 border">
      <div class="card-body items-center text-center">
        <Image class="text-primary inline aspect-square h-10 w-10" />
        <h2 class="card-title">Image Collection</h2>
        <p class="text-base-content/70">
          Browse a curated library of images from various categories.
        </p>
      </div>
    </div>

    <div class="card bg-base-200 border-base-300 border">
      <div class="card-body items-center text-center">
        <Tags class="text-primary inline aspect-square h-10 w-10" />
        <h2 class="card-title">Organized Tags</h2>
        <p class="text-base-content/70">
          Every image is categorized with descriptive tags for fast searching.
        </p>
      </div>
    </div>

    <div class="card bg-base-200 border-base-300 border">
      <div class="card-body items-center text-center">
        <Search class="text-primary inline aspect-square h-10 w-10" />
        <h2 class="card-title">Fast Discovery</h2>
        <p class="text-base-content/70">
          Quickly discover similar images and related tags with powerful search.
        </p>
      </div>
    </div>
  </div>

  <div class="stats stats-vertical md:stats-horizontal mt-20 w-full shadow">
    <div class="stat">
      <div class="stat-title">Images</div>
      <div class="stat-value text-primary">10K+</div>
      <div class="stat-desc">Growing collection</div>
    </div>

    <div class="stat">
      <div class="stat-title">Tags</div>
      <div class="stat-value text-primary">2K+</div>
      <div class="stat-desc">Well organized</div>
    </div>

    <div class="stat">
      <div class="stat-title">Search</div>
      <div class="stat-value text-primary">Fast</div>
      <div class="stat-desc">Find images instantly</div>
    </div>
  </div>

  <div class="mt-10">
    <div class="dropdown dropdown-end">
      <button class="btn btn-primary btn-soft">
        {supportedLocales.find((l) => l.code === currentLocale)?.name}
        <ChevronDown class="inline aspect-square h-4 w-4" />
      </button>

      <ul
        class="dropdown-content menu bg-base-300 rounded-box mt-2 w-40 space-y-1 p-2"
      >
        {#each supportedLocales as locale (locale.code)}
          <li>
            <button
              disabled={currentLocale === locale.code || form.processing}
              class:btn-disabled={currentLocale === locale.code}
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
