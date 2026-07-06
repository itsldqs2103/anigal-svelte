<script>
  import {
    InfiniteScroll,
    Link,
    page,
    router,
    useForm,
  } from "@inertiajs/svelte";
  import {
    CircleAlert,
    Download,
    ExternalLink,
    Eye,
    Heart,
    HeartOff,
    Pencil,
    Share2,
    Trash2,
  } from "@lucide/svelte";
  import Lazy from "svelte-lazy";

  import Modal from "@/js/Components/Modal.svelte";
  import { showImage } from "@/js/lib/fancybox";
  import i18n from "@/js/lib/i18n";
  import { isUserEdit } from "@/js/lib/isEdit.svelte";
  import { tooltip } from "@/js/lib/tooltip";
  import {
    getEditImage,
    getSearch,
    likeImage as postLikeImage,
    postDeleteImage,
  } from "@/js/wayfinder/actions/App/Http/Controllers/ImageController";

  import Profile from "../../Layouts/Profile.svelte";

  let { user, likedImages, countUploaded, countLiked } = $props();
  let loadedImages = $state({});

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

  let showConfirm = $state(false);
  let selectedId = $state(null);

  const form = useForm({});

  const isAuth = $derived(page.props.currentAuth);

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

  function handleImageLoad(imageId) {
    loadedImages = {
      ...loadedImages,
      [imageId]: true,
    };
  }

  async function likeImage(imageId) {
    try {
      router.post(postLikeImage(imageId));
    } finally {
      router.reload();
    }
  }
</script>

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

<Profile {user} {countUploaded} {countLiked}>
  {#if likedImages.data.length > 0}
    <InfiniteScroll data="likedImages" manual>
      <div
        class="mt-4 grid grid-cols-1 place-items-center gap-4 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5"
      >
        {#each likedImages.data as image (image.image_id)}
          <div class="relative">
            <Lazy keep={true} fadeOptions={{ duration: 150, delay: 0 }}>
              <div
                slot="placeholder"
                class="rounded-base bg-base-300 animate-pulse w-full h-full"
              ></div>

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

      {#snippet next({ hasMore, fetch, loading })}
        {#if hasMore}
          <div class="text-center mt-4">
            <button class="btn btn-primary" onclick={fetch} disabled={loading}>
              {loading
                ? $i18n.t("translate.loading") + "..."
                : $i18n.t("translate.loadmore")}
            </button>
          </div>
        {/if}
      {/snippet}
    </InfiniteScroll>
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
</Profile>
