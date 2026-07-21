<script>
  import { Link, page, router, useForm } from "@inertiajs/svelte";
  import {
    CircleAlert,
    Download,
    ExternalLink,
    Eye,
    Heart,
    HeartOff,
    House,
    Pencil,
    Plus,
    Share2,
    Trash2,
    X,
  } from "@lucide/svelte";

  import Breadcrumb from "@/js/Components/Breadcrumb.svelte";
  import Modal from "@/js/Components/Modal.svelte";
  import Pagination from "@/js/Components/Pagination.svelte";
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

  let { images = [], searchTag = null, suggestedTags = [] } = $props();
  let searchInput = $state("");
  let filteredTags = $state([]);
  let showSuggestions = $state(false);
  let showConfirm = $state(false);
  let selectedId = $state(null);

  $effect(() => {
    if (searchTag) {
      searchInput = searchTag;
    }
  });

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

  const form = useForm({});

  const isAuth = $derived(page.props.currentAuth);

  function addImage() {
    router.visit(getAddImage());
  }

  function editImage(id) {
    router.visit(getEditImage({ query: { image_id: id } }));
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

  function updateTagSuggestions(input) {
    if (input.length > 0) {
      const lowercaseInput = input.toLowerCase();

      filteredTags = suggestedTags.filter(
        (tag) =>
          tag.tag_name.toLowerCase().includes(lowercaseInput) ||
          tag.tag_slug_name.toLowerCase().includes(lowercaseInput),
      );

      showSuggestions = filteredTags.length > 0;
    } else {
      filteredTags = [];
      showSuggestions = false;
    }
  }

  function selectTag(tag) {
    searchInput = tag.tag_slug_name;
    showSuggestions = false;
    searchByTag();
  }

  function searchByTag() {
    if (searchInput.trim()) {
      router.visit(
        getSearch({
          query: { tag_slug_name: searchInput.trim() },
        }),
      );
    }
  }

  function clearSearch() {
    searchInput = "";
    showSuggestions = false;
    router.visit(
      imageIndex({
        query: {
          sort_by: "created_at",
          order: "latest",
          per_page: 30,
        },
      }),
    );
  }

  let loadedImages = $state({});

  function handleImageLoad(imageId) {
    loadedImages = {
      ...loadedImages,
      [imageId]: true,
    };
  }

  function handleKeydown(e) {
    if (e.key === "Enter") {
      searchByTag();
    }
  }

  function handleInputChange(e) {
    const input = e.target.value;
    searchInput = input;
    updateTagSuggestions(input);
  }

  import { showImage } from "@/js/lib/fancybox";

  function autofocus(node) {
    node.focus();
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
  <title>{searchTag ? searchTag : $i18n.t("translate.search")} - {$title}</title
  >
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
      class="btn btn-primary"
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
        class="text-base-content hover:text-primary focus:text-primary focus-visible:text-primary cursor-pointer gap-1 no-underline transition-[color] focus:outline-0 focus:outline-transparent focus-visible:outline-0 focus-visible:outline-transparent"
      >
        <House class="inline aspect-square h-4 w-4" />{$i18n.t(
          "translate.home",
        )}
      </Link>
    </li>
    <li>
      <Link
        href={imageIndex({
          query: {
            sort_by: "created_at",
            order: "latest",
            per_page: 30,
          },
        })}
        class="text-base-content hover:text-primary focus:text-primary focus-visible:text-primary cursor-pointer no-underline transition-[color] focus:outline-0 focus:outline-transparent focus-visible:outline-0 focus-visible:outline-transparent"
      >
        {$i18n.t("translate.image")}
      </Link>
    </li>
    <li>{$i18n.t("translate.search")}</li>
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
      <Plus class="inline aspect-square h-4 w-4" />
      {$i18n.t("translate.addnew")}
    </button>
  {/if}

  <div class="relative min-w-48 flex-1">
    <div class="flex flex-wrap gap-2">
      <input
        type="text"
        placeholder={$i18n.t("translate.searchtag")}
        class="input input-bordered min-w-48 flex-1"
        id="searchImageByTagInput"
        maxlength="255"
        value={searchInput}
        onchange={handleInputChange}
        oninput={handleInputChange}
        onkeydown={handleKeydown}
        onfocus={() => {
          if (searchInput.length > 0) {
            updateTagSuggestions(searchInput);
          }
        }}
        onblur={() => {
          setTimeout(() => {
            showSuggestions = false;
          }, 200);
        }}
        disabled={form.processing}
        autocomplete="off"
        use:autofocus
      />
      <button
        type="button"
        class="btn btn-primary"
        onclick={searchByTag}
        disabled={form.processing || !searchInput.trim()}
      >
        {$i18n.t("translate.search")}
      </button>
      <button
        type="button"
        class="btn btn-error"
        onclick={clearSearch}
        disabled={form.processing}
      >
        <X class="inline aspect-square h-4 w-4" />
        {$i18n.t("translate.cancel")}
      </button>
    </div>

    {#if showSuggestions && filteredTags.length > 0}
      <div
        class="bg-base-100 rounded-base absolute top-full right-0 left-0 z-50 mt-1"
      >
        {#each filteredTags.slice(0, 8) as tag (tag.tag_id)}
          <button
            type="button"
            class="hover:bg-base-200 focus-visible:bg-base-200 w-full cursor-pointer px-4 py-2 text-left transition-colors first:rounded-t-md last:rounded-b-md focus-visible:outline-0"
            onclick={() => selectTag(tag)}
          >
            <div class="font-bold">{tag.tag_name}</div>
            {#if tag.tag_desc}
              <div class="text-sm opacity-70">{tag.tag_desc}</div>
            {/if}
          </button>
        {/each}
      </div>
    {/if}
  </div>
</div>

<div class="mt-4">
  {#if searchTag}
    <div role="alert" class="alert alert-success alert-soft mb-4 gap-1">
      {$i18n.t("translate.searchingfor")}: <b>{searchTag}</b>
    </div>
  {/if}
  <div class="text-base-content/70 text-sm">
    {$i18n.t("translate.totalimage")}: <b>{images.total}</b>
  </div>
</div>

{#if images.data.length > 0}
  <div
    class="mt-4 grid grid-cols-1 place-items-center gap-4 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5"
  >
    {#each images.data as image (image.image_id)}
      <div class="relative">
        <img
          data-lazyload-src={image.thumbnail_image_path_url}
          alt={image.image_id}
          class="rounded-base lazyload object-cover"
          onload={() => handleImageLoad(image.image_id)}
        />

        {#if loadedImages[image.image_id]}
          <div
            class="rounded-base group absolute inset-0 bg-linear-to-b from-black/70 via-transparent transition-[--tw-gradient-to] hover:to-black/70"
          >
            <div class="absolute top-0 left-0 w-full p-2">
              <div class="flex items-center justify-between">
                {#if image.created_at === image.updated_at}
                  <div
                    class="btn btn-primary pointer-events-none h-6! px-1! text-sm!"
                  >
                    {$i18n.t("translate.new")}!
                  </div>
                {:else if image.created_at !== image.updated_at}
                  <div
                    class="btn btn-warning pointer-events-none h-6! px-1! text-sm!"
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
              class="absolute bottom-0 left-0 flex w-full flex-wrap gap-2 p-2 opacity-0 transition-opacity group-hover:opacity-100"
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
      <CircleAlert class="inline aspect-square h-6 w-6" />
      <div>
        <h3 class="font-bold">{$i18n.t("translate.thereareerror")}</h3>
        <div class="text-sm">
          <div>{$i18n.t("translate.noimagefound")}</div>
        </div>
      </div>
    </div>
  </div>
{/if}
