<script>
  import { Link, page, router, useForm } from "@inertiajs/svelte";
  import {
    ChevronDown,
    House,
    Pencil,
    Plus,
    RefreshCw,
    Trash2,
  } from "@lucide/svelte";
  import { CircleAlert } from "@lucide/svelte";
  import clsx from "clsx";

  import Breadcrumb from "@/js/Components/Breadcrumb.svelte";
  import Modal from "@/js/Components/Modal.svelte";
  import Pagination from "@/js/Components/Pagination.svelte";
  import i18n from "@/js/lib/i18n";
  import { isUserEdit } from "@/js/lib/isEdit.svelte";
  import { title } from "@/js/lib/title";
  import { tooltip } from "@/js/lib/tooltip";
  import { getSearch } from "@/js/wayfinder/actions/App/Http/Controllers/ImageController";
  import { index } from "@/js/wayfinder/actions/App/Http/Controllers/MainController";
  import {
    getAddTag,
    getEditTag,
    index as tagIndex,
    postDeleteTag,
  } from "@/js/wayfinder/actions/App/Http/Controllers/TagController";

  const form = useForm({});

  const isAuth = $derived(page.props.currentAuth);

  let { tags, filters = {}, defaults = {} } = $props();
  let startsWith = $derived(filters.startsWith ?? null);

  let sortBy = $derived(filters.sort_by ?? defaults.sort_by);
  let order = $derived(filters.order ?? defaults.order);
  let perPage = $derived(filters.perPage ?? defaults.perPage);

  function applyFilters() {
    router.get(
      tagIndex({
        query: {
          sort_by: sortBy,
          order: order,
          per_page: perPage,
          starts_with: startsWith,
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

  function addTag() {
    router.visit(getAddTag());
  }

  function editTag(id) {
    router.visit(
      getEditTag({
        query: {
          tag_id: id,
        },
      }),
    );
  }

  function deleteTag(id) {
    selectedId = id;
    showConfirm = true;
  }

  function confirmDelete() {
    if (!selectedId) return;

    const tagId = selectedId;

    showConfirm = false;
    selectedId = null;

    form.delete(postDeleteTag({ query: { tag_id: tagId } }));
  }
</script>

<svelte:head>
  <title>{$i18n.t("translate.tag")} - {$title}</title>
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
    <li>{$i18n.t("translate.tag")}</li>
  </ul>
</Breadcrumb>

<h1 class="mb-4 text-lg font-bold">{$i18n.t("translate.tag")}</h1>

{#if isAuth && isUserEdit.value === true}
  <button
    type="button"
    class="btn btn-primary mb-4"
    onclick={addTag}
    disabled={form.processing}
  >
    <Plus class="inline h-4 w-4 aspect-square" />
    {$i18n.t("translate.addnew")}
  </button>
{/if}

<div class="mb-4 flex flex-wrap gap-2">
  <button
    class={clsx("btn btn-neutral", !startsWith && "btn-disabled")}
    onclick={() => {
      startsWith = null;
      applyFilters();
    }}
    disabled={!startsWith}
  >
    {$i18n.t("translate.all")}
  </button>

  {#each "ABCDEFGHIJKLMNOPQRSTUVWXYZ".split("") as letter (letter)}
    <button
      class={clsx(
        "btn btn-neutral btn-square",
        startsWith === letter && "btn-disabled",
      )}
      onclick={() => {
        startsWith = letter.toLowerCase();
        applyFilters();
      }}
      disabled={startsWith === letter}
    >
      {letter}
    </button>
  {/each}
</div>

<div class="text-base-content/70 text-sm">
  {$i18n.t("translate.totaltag")}: <b>{tags.total}</b>
</div>

<div class="my-4 flex flex-wrap items-center justify-end gap-2">
  <button
    type="button"
    class="btn btn-primary btn-square"
    use:tooltip={$i18n.t("translate.refresh")}
    onclick={() =>
      router.reload({
        only: ["tags"],
      })}
  >
    <RefreshCw class="inline h-4 w-4 aspect-square" />
  </button>
  <div class="dropdown dropdown-end select-none">
    <button
      class="btn btn-primary"
      disabled={form.processing || tags.total <= 1}
    >
      {#if sortBy === "created_at"}
        {order === "latest"
          ? $i18n.t("translate.newest")
          : $i18n.t("translate.oldest")}
      {:else}
        {order === "latest"
          ? $i18n.t("translate.ztoa")
          : $i18n.t("translate.atoz")}
      {/if}
      <ChevronDown class="inline aspect-square h-4 w-4" />
    </button>

    <ul class="dropdown-content mt-1 menu bg-base-300 rounded-box z-1 w-24 p-2">
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
          {$i18n.t("translate.atoz")}
        </button>
      </li>
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
          {$i18n.t("translate.ztoa")}
        </button>
      </li>
    </ul>
  </div>

  <div class="dropdown dropdown-end select-none">
    <button
      class="btn btn-primary"
      disabled={form.processing || tags.total <= 1}
    >
      {perPage}
      <ChevronDown class="inline aspect-square h-4 w-4" />
    </button>

    <ul
      class="dropdown-content menu bg-base-300 rounded-box mt-1 z-1 w-42 space-y-1 p-2"
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

{#if tags.data.length > 0}
  <div
    class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5"
  >
    {#each tags.data as tag (tag.tag_id)}
      <div class="group bg-base-300 rounded-base p-4">
        {#if tag.created_at === tag.updated_at}
          <div
            class="btn btn-primary h-6! px-1! text-sm! pointer-events-none mb-1"
          >
            {$i18n.t("translate.new")}!
          </div>
        {:else if tag.created_at !== tag.updated_at}
          <div
            class="btn btn-warning h-6! px-1! text-sm! pointer-events-none mb-1"
          >
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
            class="hover:text-primary font-bold transition-colors"
          >
            {tag.tag_name}
          </Link>
        </div>

        {#if tag.tag_desc}
          <p class="text-base-content/70 mt-2 text-sm">
            {tag.tag_desc}
          </p>
        {/if}

        {#if isAuth && isUserEdit.value}
          <div
            class="mt-4 flex items-center justify-end gap-2 opacity-100 md:opacity-0 md:group-hover:opacity-100 transition-opacity"
          >
            <button
              use:tooltip={$i18n.t("translate.edit")}
              class="btn btn-warning btn-square btn-sm"
              onclick={() => editTag(tag.tag_id)}
              aria-label={$i18n.t("translate.edit")}
            >
              <Pencil class="h-4 w-4" />
            </button>

            <button
              use:tooltip={$i18n.t("translate.delete")}
              class="btn btn-error btn-square btn-sm"
              onclick={() => deleteTag(tag.tag_id)}
              aria-label={$i18n.t("translate.delete")}
            >
              <Trash2 class="h-4 w-4" />
            </button>
          </div>
        {/if}
      </div>
    {/each}
  </div>

  <Pagination
    links={tags.links}
    from={tags.from}
    to={tags.to}
    total={tags.total}
  />
{:else}
  <div class="mt-4">
    <div role="alert" class="alert alert-error alert-soft inline-flex">
      <CircleAlert class="h-6 w-6 inline aspect-square" />
      <div>
        <h3 class="font-bold">{$i18n.t("translate.thereareerror")}</h3>
        <div class="text-sm">
          <div>{$i18n.t("translate.notagfound")}</div>
        </div>
      </div>
    </div>
  </div>
{/if}
