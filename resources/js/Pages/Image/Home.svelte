<script>
  import { Link, page, router, useForm } from "@inertiajs/svelte";
  import {
    ChevronDown,
    CircleAlert,
    Download,
    ExternalLink,
    Eye,
    Heart,
    HeartOff,
    House,
    Pencil,
    Plus,
    RefreshCw,
    Search,
    Share2,
    Trash2,
  } from "@lucide/svelte";
  import clsx from "clsx";
  import Lazy from "svelte-lazy";

  import Breadcrumb from "@/js/Components/Breadcrumb.svelte";
  import Modal from "@/js/Components/Modal.svelte";
  import Pagination from "@/js/Components/Pagination.svelte";
  import { showImage } from "@/js/lib/fancybox";
  import i18n from "@/js/lib/i18n";
  import { isUserEdit } from "@/js/lib/isEdit.svelte";
  import { title } from "@/js/lib/title";
  import { tooltip } from "@/js/lib/tooltip";
  import {
    getAddImage,
    getEditImage,
    getSearch,
    index as imageIndex,
    likeImage as postLikeImage,
    postDeleteImage,
  } from "@/js/wayfinder/actions/App/Http/Controllers/ImageController";
  import { index } from "@/js/wayfinder/actions/App/Http/Controllers/MainController";

  let { images = [], filters = {}, defaults = {} } = $props();

  let sortBy = $derived(filters.sort_by ?? defaults.sort_by);
  let order = $derived(filters.order ?? defaults.order);
  let perPage = $derived(filters.perPage ?? defaults.perPage);

  function shareImage(image) {
    const url = image?.image_source;

    if (!url || !navigator.share) {
      return Promise.resolve(false);
    }

    return navigator
      .share({ url })
      .then(() => true)
      .catch(() => false);
  }

  function applyFilters() {
    router.get(
      imageIndex({
        query: {
          sort_by: sortBy,
          order: order,
          per_page: perPage,
        },
      }),
      {},
      {
        preserveState: true,
        replace: true,
      },
    );
  }

  let showConfirm = $state(false);
  let selectedId = $state(null);

  const form = useForm({});

  const isAuth = $derived(page.props.currentAuth);

  function addImage() {
    router.visit(getAddImage());
  }

  function getSearchImage() {
    router.visit(getSearch());
  }

  function editImage(id) {
    router.visit(getEditImage({ query: { image_id: id } }));
  }

  let loadedImages = $state({});

  function handleImageLoad(imageId) {
    loadedImages = {
      ...loadedImages,
      [imageId]: true,
    };
  }

  function deleteImage(id) {
    selectedId = id;
    showConfirm = true;
  }

  function confirmDelete() {
    if (!selectedId) return;

    const imageId = selectedId;

    showConfirm = false;
    selectedId = null;

    form.delete(postDeleteImage({ query: { image_id: imageId } }));
  }

  async function likeImage(imageId) {
    try {
      router.post(postLikeImage(imageId));
    } finally {
      router.reload();
    }
  }
</script>

<svelte:head>
  <title>{$i18n.t("translate.image")} - {$title}</title>
</svelte:head>

<Modal
  open={showConfirm}
  title={$i18n.t("translate.areyousure")}
  onClose={() => (showConfirm = false)}
>
  <p class="text-sm opacity-70">
    {$i18n.t("translate.thisactioncannotbeundone")}
  </p>

  {#snippet footer()}
    <button
      class="btn btn-neutral"
      onclick={() => (showConfirm = false)}
      disabled={form.processing}
    >
      {$i18n.t("translate.cancel")}
    </button>

    <button
      class="btn btn-error"
      onclick={confirmDelete}
      disabled={form.processing}
    >
      {$i18n.t("translate.confirm")}
    </button>
  {/snippet}

  {#snippet backdrop()}
    <button onclick={() => (showConfirm = false)} disabled={form.processing}>
      {$i18n.t("translate.close")}
    </button>
  {/snippet}
</Modal>

<Breadcrumb>
  <ul>
    <li>
      <Link
        href={index()}
        class="text-base-content hover:text-primary focus:text-primary focus-visible:text-primary cursor-pointer no-underline transition-[color] focus:outline-0 focus:outline-transparent focus-visible:outline-0 focus-visible:outline-transparent gap-1"
      >
        <House class="inline h-4 w-4 aspect-square" />{$i18n.t(
          "translate.home",
        )}
      </Link>
    </li>
    <li>{$i18n.t("translate.image")}</li>
  </ul>
</Breadcrumb>

<h1 class="mb-4 text-lg font-bold">{$i18n.t("translate.image")}</h1>

<div class="mb-4 flex flex-wrap gap-2">
  {#if isAuth && isUserEdit.value === true}
    <button
      type="button"
      class="btn btn-primary"
      onclick={addImage}
      disabled={form.processing}
    >
      <Plus class="inline h-4 w-4 aspect-square" />
      {$i18n.t("translate.addnew")}
    </button>
  {/if}

  <button
    type="button"
    class="btn btn-success"
    onclick={getSearchImage}
    disabled={form.processing}
  >
    <Search class="inline h-4 w-4 aspect-square" />
    {$i18n.t("translate.search")}
  </button>
</div>

<div class="mt-4 text-base-content/70 text-sm">
  {$i18n.t("translate.totalimage")}: <b>{images.total}</b>
</div>

<div class="my-4 flex flex-wrap items-center justify-end gap-2">
  <button
    type="button"
    class="btn btn-primary btn-square"
    use:tooltip={$i18n.t("translate.refresh")}
    onclick={() =>
      router.reload({
        only: ["images"],
      })}
  >
    <RefreshCw class="inline h-4 w-4 aspect-square" />
  </button>
  <div class="dropdown dropdown-end select-none">
    <button
      class="btn btn-primary"
      disabled={images.total === 0 || form.processing}
    >
      {order === "latest"
        ? $i18n.t("translate.latest")
        : $i18n.t("translate.oldest")}
      <ChevronDown class="inline aspect-square h-4 w-4" />
    </button>

    <ul class="dropdown-content menu bg-base-200 mt-1 rounded-box z-1 w-32 p-2">
      <li>
        <button
          class={clsx(order === "latest" && "bg-primary text-primary-content")}
          onclick={(e) => {
            order = "latest";
            applyFilters();
            e.currentTarget.blur();
          }}
          disabled={form.processing || order === "latest"}
        >
          {$i18n.t("translate.latest")}
        </button>
      </li>
      <li>
        <button
          class={clsx(order === "oldest" && "bg-primary text-primary-content")}
          onclick={(e) => {
            order = "oldest";
            applyFilters();
            e.currentTarget.blur();
          }}
          disabled={form.processing || order === "oldest"}
        >
          {$i18n.t("translate.oldest")}
        </button>
      </li>
    </ul>
  </div>

  <div class="dropdown dropdown-end select-none">
    <button
      class="btn btn-primary"
      disabled={images.total <= 1 || form.processing}
    >
      {perPage}
      <ChevronDown class="inline aspect-square h-4 w-4" />
    </button>

    <ul
      class="dropdown-content menu bg-base-200 rounded-box mt-1 z-1 w-42 space-y-1 p-2"
    >
      {#each filters.allowedLimits as limit (limit)}
        <li>
          <button
            class={clsx(perPage === limit && "bg-primary text-primary-content")}
            onclick={(e) => {
              perPage = limit;
              applyFilters();
              e.currentTarget.blur();
            }}
            disabled={form.processing || perPage === limit}
          >
            {limit}
            {limit === defaults.perPage
              ? `(${$i18n.t("translate.default").toLowerCase()})`
              : ""}
          </button>
        </li>
      {/each}
    </ul>
  </div>
</div>

{#if images.data.length > 0}
  <div
    class="mt-4 grid grid-cols-1 place-items-center gap-4 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5"
  >
    {#each images.data as image (image.image_id)}
      <div class="relative">
        <Lazy keep={true} fadeOptions={{ duration: 150, delay: 0 }}>
          <img
            src={image.thumbnail_image_path_url}
            alt={image.image_id}
            class="rounded-base object-cover"
            onload={() => handleImageLoad(image.image_id)}
          />
        </Lazy>

        {#if loadedImages[image.image_id]}
          <div
            class="rounded-base group absolute inset-0 bg-linear-to-b from-black/70 via-transparent hover:to-black/70 transition-[--tw-gradient-to]"
          >
            <div class="absolute top-0 left-0 w-full p-2">
              <div class="flex items-center justify-between">
                {#if image.created_at === image.updated_at}
                  <div
                    class="btn btn-primary h-6! px-1! text-sm! pointer-events-none"
                  >
                    {$i18n.t("translate.new")}!
                  </div>
                {:else if image.created_at !== image.updated_at}
                  <div
                    class="btn btn-warning h-6! px-1! text-sm! pointer-events-none"
                  >
                    {$i18n.t("translate.updated")}!
                  </div>
                {/if}

                {#if isAuth && isUserEdit.value === true}
                  <div
                    class="flex items-center gap-2 opacity-0 transition-opacity group-hover:opacity-100"
                  >
                    <div use:tooltip={$i18n.t("translate.edit")}>
                      <button
                        type="button"
                        class="btn btn-square btn-sm btn-warning"
                        disabled={form.processing}
                        onclick={() => editImage(image.image_id)}
                      >
                        <Pencil class="inline h-4 w-4" />
                      </button>
                    </div>

                    <div use:tooltip={$i18n.t("translate.delete")}>
                      <button
                        type="button"
                        disabled={form.processing}
                        onclick={() => deleteImage(image.image_id)}
                        class="btn btn-square btn-sm btn-error"
                      >
                        <Trash2 class="inline h-4 w-4" />
                      </button>
                    </div>
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
                    class="btn btn-primary h-6! px-1! text-sm!"
                  >
                    {tag.tag_name}
                  </Link>
                </div>
              {/each}

              {#if image.tags.length > 5}
                <div>
                  <button
                    type="button"
                    class="btn btn-primary h-6! px-1! text-sm!"
                    use:tooltip={image.tags
                      .slice(5)
                      .map((tag) => tag.tag_name)
                      .join(", ")}
                  >
                    +{image.tags.length - 5}
                    {$i18n.t("translate.tag").toLowerCase()}
                  </button>
                </div>
              {/if}

              <div class="flex w-full items-center justify-end gap-2">
                {#if isAuth}
                  <div use:tooltip={$i18n.t("translate.like")}>
                    <button
                      type="button"
                      class="btn btn-sm btn-square btn-neutral"
                      disabled={form.processing}
                      onclick={() => likeImage(image.image_id)}
                    >
                      {#if image.liked}
                        <HeartOff class="inline aspect-square h-4 w-4" />
                      {:else}
                        <Heart class="inline aspect-square h-4 w-4" />
                      {/if}
                    </button>
                  </div>
                {/if}
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
                    disabled={form.processing}
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

  <Pagination
    links={images.links}
    from={images.from}
    to={images.to}
    total={images.total}
  />
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
