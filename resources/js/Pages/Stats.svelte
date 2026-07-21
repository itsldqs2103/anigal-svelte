<script>
  import { Link, router } from "@inertiajs/svelte";
  import { House, RefreshCw } from "@lucide/svelte";
  import { filesize } from "filesize";

  import i18n from "@/js/lib/i18n";
  import { title } from "@/js/lib/title";
  import { tooltip } from "@/js/lib/tooltip";
  import { index } from "@/js/wayfinder/actions/App/Http/Controllers/MainController";

  let { totalTags, totalImages, totalImagesFilesize } = $props();

  const refreshStats = () => {
    router.reload({
      only: ["totalTags", "totalImages", "totalImagesFilesize"],
    });
  };
</script>

<svelte:head>
  <title>{$i18n.t("translate.stats")} - {$title}</title>
</svelte:head>

<div class="breadcrumbs bg-base-300 rounded-base mb-4 inline-flex p-2">
  <ul>
    <li>
      <Link
        href={index()}
        class="text-base-content hover:text-primary focus:text-primary focus-visible:text-primary cursor-pointer gap-1 no-underline transition-[color] focus:outline-0 focus:outline-transparent focus-visible:outline-0 focus-visible:outline-transparent"
      >
        <House class="inline aspect-square h-4 w-4" />
        {$i18n.t("translate.home")}
      </Link>
    </li>
    <li>{$i18n.t("translate.stats")}</li>
  </ul>
</div>

<div>
  <h1 class="mb-4 text-lg font-bold">{$i18n.t("translate.stats")}</h1>
</div>

<div class="mb-4 text-end">
  <button
    type="button"
    class="btn btn-primary btn-square"
    onclick={refreshStats}
    use:tooltip={$i18n.t("translate.refresh")}
  >
    <RefreshCw class="inline aspect-square h-4 w-4" />
  </button>
</div>

<div
  class="stats stats-vertical sm:stats-horizontal bg-base-300 rounded-base w-full"
>
  <div class="stat p-4">
    <div class="stat-title text-sm">{$i18n.t("translate.totaltag")}</div>
    <div class="stat-value text-primary text-2xl">{totalTags}</div>
  </div>

  <div class="stat p-4">
    <div class="stat-title text-sm">
      {$i18n.t("translate.totalimage")}
    </div>
    <div class="stat-value text-secondary text-2xl">{totalImages}</div>
  </div>

  <div class="stat p-4">
    <div class="stat-title text-sm">
      {$i18n.t("translate.totalimagefilesize")}
    </div>
    <div class="stat-value text-info text-2xl">
      {filesize(totalImagesFilesize, { standard: "jedec" })}
    </div>
  </div>
</div>
