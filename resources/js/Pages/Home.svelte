<script>
  import { Link, router } from "@inertiajs/svelte";
  import {
    ArrowRight,
    CircleAlert,
    Download,
    ExternalLink,
    Eye,
    House,
    RefreshCw,
    Share2,
  } from "@lucide/svelte";
  import { onMount } from "svelte";
  import Lazy from "svelte-lazy";

  import { api } from "@/js/lib/axios";
  import { showImage } from "@/js/lib/fancybox";
  import i18n from "@/js/lib/i18n";
  import { title } from "@/js/lib/title";
  import { tooltip } from "@/js/lib/tooltip";
  import {
    latestImages as apiLatestImages,
    latestTags as apiLatestTags,
    randomImages as apiRandomImages,
    randomTags as apiRandomTags,
  } from "@/js/wayfinder/actions/App/Http/Controllers/ApiController";
  import {
    getSearch,
    index as imageIndex,
  } from "@/js/wayfinder/actions/App/Http/Controllers/ImageController";
  import { index as tagIndex } from "@/js/wayfinder/actions/App/Http/Controllers/TagController";

  let {
    countLatestTags = [],
    countLatestImages = [],
    countRandomTags = [],
    countRandomImages = [],
  } = $props();

  let latestTags = $state([]);
  let latestImages = $state([]);
  let randomTags = $state([]);
  let randomImages = $state([]);

  let latestTagsLoading = $state(false);
  let latestImagesLoading = $state(false);
  let randomTagsLoading = $state(false);
  let randomImagesLoading = $state(false);

  function shareImage(image) {
    const shareData = {
      url: image.image_source,
    };

    if (navigator.share) {
      navigator.share(shareData).catch(() => {});
    }
  }

  async function loadLatestTags() {
    latestTagsLoading = true;

    try {
      const { data } = await api.get(apiLatestTags().url);
      latestTags = data;
    } finally {
      latestTagsLoading = false;
    }
  }

  async function loadLatestImages() {
    latestImagesLoading = true;

    try {
      const { data } = await api.get(apiLatestImages().url);
      latestImages = data;
    } finally {
      latestImagesLoading = false;
    }
  }

  async function loadRandomTags() {
    randomTagsLoading = true;

    try {
      const { data } = await api.get(apiRandomTags().url);
      randomTags = data;
    } finally {
      randomTagsLoading = false;
    }
  }

  async function loadRandomImages() {
    randomImagesLoading = true;

    try {
      const { data } = await api.get(apiRandomImages().url);
      randomImages = data;
    } finally {
      randomImagesLoading = false;
    }
  }

  onMount(() => {
    loadLatestTags();
    loadLatestImages();
    loadRandomTags();
    loadRandomImages();
  });

  let loadedImages = $state({});

  function handleImageLoad(imageId) {
    loadedImages = {
      ...loadedImages,
      [imageId]: true,
    };
  }

  function viewMoreTags() {
    router.visit(
      tagIndex({
        query: {
          per_page: 30,
          sort: "oldest",
          starts_with: null,
        },
      }),
    );
  }

  function viewMoreImages() {
    router.visit(
      imageIndex({
        query: {
          per_page: 30,
          sort: "latest",
        },
      }),
    );
  }
</script>

<svelte:head>
  <title>{$i18n.t("translate.home")} - {$title}</title>
</svelte:head>

<div class="breadcrumbs inline-flex bg-base-300 rounded-base mb-4 p-2">
  <ul>
    <li>
      <House class="pointer-events-none me-1 h-4 w-4" />{$i18n.t(
        "translate.home",
      )}
    </li>
  </ul>
</div>

<div
  class="space-y-4 sm:flex sm:items-center sm:justify-between sm:gap-4 sm:space-y-0"
>
  <div>
    <h1 class="text-lg font-bold">{$i18n.t("translate.randomtags")}</h1>
  </div>
  <div class="space-x-2">
    <button
      type="button"
      class="btn btn-primary btn-square"
      use:tooltip={$i18n.t("translate.refresh")}
      onclick={loadRandomTags}
      disabled={randomTagsLoading}
    >
      <RefreshCw class="inline h-4 w-4 aspect-square" />
    </button>
    <button type="button" class="btn btn-primary" onclick={viewMoreTags}>
      <span>{$i18n.t("translate.viewmore")}</span>
      <ArrowRight class="inline h-4 w-4 aspect-square" />
    </button>
  </div>
</div>

{#if randomTagsLoading}
  <div class="inline-flex items-center gap-1">
    <span class="loading loading-spinner loading-sm"></span>
    <span>{$i18n.t("translate.loading")}</span>
  </div>
{:else if countRandomTags > 0}
  <div
    class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5"
  >
    {#each randomTags as tag (tag.tag_id)}
      <div class="group bg-base-300 rounded-base p-4">
        {#if tag.created_at === tag.updated_at}
          <div class="btn btn-primary h-6! text-sm! pointer-events-none mb-1">
            {$i18n.t("translate.new")}!
          </div>
        {:else}
          <div class="btn btn-warning h-6! text-sm! pointer-events-none mb-1">
            {$i18n.t("translate.updated")}!
          </div>
        {/if}

        <div class="min-w-0 flex-1">
          <Link
            href={getSearch({
              query: {
                tag_slug_name: tag.tag_slug_name,
              },
            })}
            class="hover:text-primary focus:text-primary font-bold transition-colors focus:outline-none"
          >
            {tag.tag_name}
          </Link>

          {#if tag.tag_desc}
            <p class="text-base-content/70 mt-2 text-sm">
              {tag.tag_desc}
            </p>
          {/if}
        </div>
      </div>
    {/each}
  </div>
{:else}
  <div class="mt-4">
    <div role="alert" class="alert alert-error alert-soft inline-flex">
      <CircleAlert class="h-6 w-6 shrink-0" />
      <div>
        <h3 class="font-bold">
          {$i18n.t("translate.thereareerror")}
        </h3>
        <div class="text-sm">
          <div>{$i18n.t("translate.notagfound")}</div>
        </div>
      </div>
    </div>
  </div>
{/if}

<div
  class="my-4 space-y-4 sm:flex sm:items-center sm:justify-between sm:gap-4 sm:space-y-0"
>
  <div>
    <h1 class="text-lg font-bold">{$i18n.t("translate.randomimages")}</h1>
  </div>
  <div class="space-x-2">
    <button
      type="button"
      class="btn btn-primary btn-square"
      use:tooltip={$i18n.t("translate.refresh")}
      onclick={loadRandomImages}
      disabled={randomImagesLoading}
    >
      <RefreshCw class="inline h-4 w-4 aspect-square" />
    </button>
    <button type="button" class="btn btn-primary" onclick={viewMoreImages}>
      <span>{$i18n.t("translate.viewmore")}</span>
      <ArrowRight class="inline h-4 w-4 aspect-square" />
    </button>
  </div>
</div>

{#if randomImagesLoading}
  <div class="inline-flex items-center gap-1">
    <span class="loading loading-spinner loading-sm"></span>
    <span>{$i18n.t("translate.loading")}</span>
  </div>
{:else if countRandomImages > 0}
  <div
    class="mt-4 grid grid-cols-1 place-items-center gap-4 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5"
  >
    {#each randomImages as image (image.image_id)}
      <div class="relative">
        <Lazy keep={true} fadeOptions={{ duration: 150, delay: 0, offset: 0 }}>
          <div
            slot="placeholder"
            class="rounded-base bg-base-300 animate-pulse w-full h-full"
          ></div>

          <img
            src={image.thumbnail_image_path_url}
            alt={image.image_id}
            class="rounded-base object-cover"
            onload={() => handleImageLoad(`random-${image.image_id}`)}
          />
        </Lazy>

        {#if loadedImages[`random-${image.image_id}`]}
          <div
            class="rounded-base group absolute inset-0 bg-linear-to-b from-black/70 via-transparent hover:to-black/70 transition-[--tw-gradient-to]"
          >
            <div class="absolute top-0 left-0 w-full p-2">
              <div class="flex items-center justify-between">
                {#if image.created_at === image.updated_at}
                  <div
                    class="btn btn-primary h-6! text-sm! pointer-events-none"
                  >
                    {$i18n.t("translate.new")}!
                  </div>
                {:else if image.created_at !== image.updated_at}
                  <div
                    class="btn btn-warning p-2 h-6! text-sm! pointer-events-none"
                  >
                    {$i18n.t("translate.updated")}!
                  </div>
                {/if}
              </div>
            </div>

            <div
              class="absolute bottom-0 left-0 flex flex-wrap gap-2 p-2 opacity-0 transition-opacity group-hover:opacity-100"
            >
              {#each image.tags.slice(0, 5) as tag (tag.tag_id)}
                <div use:tooltip={tag.tag_desc}>
                  <Link
                    href={getSearch({
                      query: {
                        tag_slug_name: tag.tag_slug_name,
                      },
                    })}
                    class="btn btn-primary h-6! text-sm!"
                  >
                    {tag.tag_name}
                  </Link>
                </div>
              {/each}

              {#if image.tags.length > 5}
                <div
                  class="btn btn-primary h-6! text-sm!"
                  use:tooltip={image.tags
                    .slice(5)
                    .map((tag) => tag.tag_name)
                    .join(", ")}
                >
                  +{image.tags.length - 5}
                  {$i18n.t("translate.tag").toLowerCase()}
                </div>
              {/if}

              <div class="flex w-full items-center justify-end gap-2">
                <div use:tooltip={$i18n.t("translate.viewimage")}>
                  <button
                    type="button"
                    tabindex="-1"
                    class="btn btn-square btn-sm btn-neutral"
                    onclick={() =>
                      showImage(
                        image.preview_image_path_url,
                        image.image_path_url,
                        image.image_id,
                        $i18n,
                      )}
                  >
                    <Eye class="inline aspect-square h-4 w-4" />
                  </button>
                </div>
                <div use:tooltip={$i18n.t("translate.download")}>
                  <a
                    href={image.image_path_url}
                    download
                    class="btn btn-square btn-sm btn-neutral"
                  >
                    <Download class="inline aspect-square h-4 w-4" />
                  </a>
                </div>
                {#if image.image_source}
                  <div use:tooltip={$i18n.t("translate.openinnewtab")}>
                    <a
                      href={image.image_source}
                      class="btn btn-square btn-sm btn-neutral"
                      target="_blank"
                      rel="noopener noreferrer"
                    >
                      <ExternalLink class="inline aspect-square h-4 w-4" />
                    </a>
                  </div>

                  <div use:tooltip={$i18n.t("translate.share")}>
                    <button
                      type="button"
                      tabindex="-1"
                      class="btn btn-square btn-sm btn-neutral"
                      onclick={() => shareImage(image)}
                    >
                      <Share2 class="inline aspect-square h-4 w-4" />
                    </button>
                  </div>
                {/if}
              </div>
            </div>
          </div>
        {/if}
      </div>
    {/each}
  </div>
{:else}
  <div class="mt-4">
    <div role="alert" class="alert alert-error alert-soft inline-flex">
      <CircleAlert class="h-6 w-6 shrink-0" />
      <div>
        <h3 class="font-bold">{$i18n.t("translate.thereareerror")}</h3>
        <div class="text-sm">
          <div>{$i18n.t("translate.noimagefound")}</div>
        </div>
      </div>
    </div>
  </div>
{/if}

<div
  class="space-y-4 sm:flex sm:items-center sm:justify-between sm:gap-4 sm:space-y-0 mt-4"
>
  <div>
    <h1 class="text-lg font-bold">{$i18n.t("translate.latesttags")}</h1>
  </div>
  <div class="space-x-2">
    <button
      type="button"
      class="btn btn-primary btn-square"
      use:tooltip={$i18n.t("translate.refresh")}
      onclick={loadLatestTags}
      disabled={latestTagsLoading}
    >
      <RefreshCw class="inline h-4 w-4 aspect-square" />
    </button>
    <button type="button" class="btn btn-primary" onclick={viewMoreTags}>
      <span>{$i18n.t("translate.viewmore")}</span>
      <ArrowRight class="inline h-4 w-4 aspect-square" />
    </button>
  </div>
</div>

{#if latestTagsLoading}
  <div class="inline-flex items-center gap-1">
    <span class="loading loading-spinner loading-sm"></span>
    <span>{$i18n.t("translate.loading")}</span>
  </div>
{:else if countLatestTags > 0}
  <div
    class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5"
  >
    {#each latestTags as tag (tag.tag_id)}
      <div class="group bg-base-300 rounded-base p-4">
        {#if tag.created_at === tag.updated_at}
          <div class="btn btn-primary h-6! text-sm! pointer-events-none mb-1">
            {$i18n.t("translate.new")}!
          </div>
        {:else if tag.created_at !== tag.updated_at}
          <div class="btn btn-warning h-6! text-sm! pointer-events-none mb-1">
            {$i18n.t("translate.updated")}!
          </div>
        {/if}
        <div class="min-w-0 flex-1">
          <Link
            href={getSearch({
              query: {
                tag_slug_name: tag.tag_slug_name,
              },
            })}
            class="hover:text-primary focus:text-primary font-bold transition-colors focus:outline-none"
          >
            {tag.tag_name}
          </Link>

          {#if tag.tag_desc}
            <p class="text-base-content/70 mt-2 text-sm">
              {tag.tag_desc}
            </p>
          {/if}
        </div>
      </div>
    {/each}
  </div>
{:else}
  <div class="mt-4">
    <div role="alert" class="alert alert-error alert-soft inline-flex">
      <CircleAlert class="h-6 w-6 shrink-0" />
      <div>
        <h3 class="font-bold">{$i18n.t("translate.thereareerror")}</h3>
        <div class="text-sm">
          <div>{$i18n.t("translate.notagfound")}</div>
        </div>
      </div>
    </div>
  </div>
{/if}

<div
  class="my-4 space-y-4 sm:flex sm:items-center sm:justify-between sm:gap-4 sm:space-y-0"
>
  <div>
    <h1 class="text-lg font-bold">{$i18n.t("translate.latestimages")}</h1>
  </div>
  <div class="space-x-2">
    <button
      type="button"
      class="btn btn-primary btn-square"
      use:tooltip={$i18n.t("translate.refresh")}
      onclick={loadLatestImages}
      disabled={latestImagesLoading}
    >
      <RefreshCw class="inline h-4 w-4 aspect-square" />
    </button>
    <button type="button" class="btn btn-primary" onclick={viewMoreImages}>
      <span>{$i18n.t("translate.viewmore")}</span>
      <ArrowRight class="inline h-4 w-4 aspect-square" />
    </button>
  </div>
</div>

{#if latestImagesLoading}
  <div class="inline-flex items-center gap-1">
    <span class="loading loading-spinner loading-sm"></span>
    <span>{$i18n.t("translate.loading")}</span>
  </div>
{:else if countLatestImages > 0}
  <div
    class="mt-4 grid grid-cols-1 place-items-center gap-4 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5"
  >
    {#each latestImages as image (image.image_id)}
      <div class="relative">
        <Lazy keep={true} fadeOptions={{ duration: 150, delay: 0, offset: 0 }}>
          <div
            slot="placeholder"
            class="rounded-base bg-base-300 animate-pulse w-full h-full"
          ></div>

          <img
            src={image.thumbnail_image_path_url}
            alt={image.image_id}
            class="rounded-base object-cover"
            onload={() => handleImageLoad(`image-${image.image_id}`)}
          />
        </Lazy>

        {#if loadedImages[`image-${image.image_id}`]}
          <div
            class="rounded-base group absolute inset-0 bg-linear-to-b from-black/70 via-transparent hover:to-black/70 transition-[--tw-gradient-to]"
          >
            <div class="absolute top-0 left-0 w-full p-2">
              <div class="flex items-center justify-between">
                {#if image.created_at === image.updated_at}
                  <div
                    class="btn btn-primary h-6! text-sm! pointer-events-none"
                  >
                    {$i18n.t("translate.new")}!
                  </div>
                {:else if image.created_at !== image.updated_at}
                  <div
                    class="btn btn-warning p-2 h-6! text-sm! pointer-events-none"
                  >
                    {$i18n.t("translate.updated")}!
                  </div>
                {/if}
              </div>
            </div>

            <div
              class="absolute bottom-0 left-0 flex flex-wrap gap-2 p-2 opacity-0 transition-opacity group-hover:opacity-100"
            >
              {#each image.tags.slice(0, 5) as tag (tag.tag_id)}
                <div use:tooltip={tag.tag_desc}>
                  <Link
                    href={getSearch({
                      query: {
                        tag_slug_name: tag.tag_slug_name,
                      },
                    })}
                    class="btn btn-primary h-6! text-sm!"
                  >
                    {tag.tag_name}
                  </Link>
                </div>
              {/each}

              {#if image.tags.length > 5}
                <div
                  class="btn btn-primary h-6! text-sm!"
                  use:tooltip={image.tags
                    .slice(5)
                    .map((tag) => tag.tag_name)
                    .join(", ")}
                >
                  +{image.tags.length - 5}
                  {$i18n.t("translate.tag").toLowerCase()}
                </div>
              {/if}

              <div class="flex w-full items-center justify-end gap-2">
                <div use:tooltip={$i18n.t("translate.viewimage")}>
                  <button
                    type="button"
                    tabindex="-1"
                    class="btn btn-square btn-sm btn-neutral"
                    onclick={() =>
                      showImage(
                        image.preview_image_path_url,
                        image.image_path_url,
                        image.image_id,
                        $i18n,
                      )}
                  >
                    <Eye class="inline aspect-square h-4 w-4" />
                  </button>
                </div>
                <div use:tooltip={$i18n.t("translate.download")}>
                  <a
                    href={image.image_path_url}
                    download
                    class="btn btn-square btn-sm btn-neutral"
                  >
                    <Download class="inline aspect-square h-4 w-4" />
                  </a>
                </div>
                {#if image.image_source}
                  <div use:tooltip={$i18n.t("translate.openinnewtab")}>
                    <a
                      href={image.image_source}
                      class="btn btn-square btn-sm btn-neutral"
                      target="_blank"
                      rel="noopener noreferrer"
                    >
                      <ExternalLink class="inline aspect-square h-4 w-4" />
                    </a>
                  </div>

                  <div use:tooltip={$i18n.t("translate.share")}>
                    <button
                      type="button"
                      tabindex="-1"
                      class="btn btn-square btn-sm btn-neutral"
                      onclick={() => shareImage(image)}
                    >
                      <Share2 class="inline aspect-square h-4 w-4" />
                    </button>
                  </div>
                {/if}
              </div>
            </div>
          </div>
        {/if}
      </div>
    {/each}
  </div>
{:else}
  <div class="mt-4">
    <div role="alert" class="alert alert-error alert-soft inline-flex">
      <CircleAlert class="h-6 w-6 shrink-0" />
      <div>
        <h3 class="font-bold">{$i18n.t("translate.thereareerror")}</h3>
        <div class="text-sm">
          <div>{$i18n.t("translate.noimagefound")}</div>
        </div>
      </div>
    </div>
  </div>
{/if}
