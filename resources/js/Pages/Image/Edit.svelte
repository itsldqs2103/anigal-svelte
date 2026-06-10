<script>
  import { Link, useForm } from "@inertiajs/svelte";
  import {
    Check,
    ChevronDown,
    CircleAlert,
    House,
    Plus,
    RefreshCw,
    Save,
    Upload,
  } from "@lucide/svelte";
  import { filesize } from "filesize";
  import { onMount } from "svelte";
  import Lazy from "svelte-lazy";

  import Modal from "@/js/Components/Modal.svelte";
  import { api } from "@/js/lib/axios";
  import i18n from "@/js/lib/i18n";
  import { title } from "@/js/lib/title";
  import { tooltip } from "@/js/lib/tooltip";
  import { addTag as apiAddTag } from "@/js/wayfinder/actions/App/Http/Controllers/ApiController";
  import {
    index as imageIndex,
    postEditImage,
  } from "@/js/wayfinder/actions/App/Http/Controllers/ImageController";
  import { index } from "@/js/wayfinder/actions/App/Http/Controllers/MainController";

  let isCollapsed = $state(false);

  let {
    errors = {},
    image_id = {},
    countTags = [],
    maxUploadFilesize,
  } = $props();

  let startsWith = $state(null);

  let tags = $state([]);
  let tagsLoading = $state(false);

  async function loadTags() {
    tagsLoading = true;

    try {
      const { data } = await api.get("/api/tags/all");
      tags = data;
    } finally {
      tagsLoading = false;
    }
  }

  async function loadSelectedTags() {
    const { data } = await api.get(`/api/tags/selected/${image_id}`);

    form.tag = data.selectedTags;
  }

  let image = $state(null);

  async function loadImage() {
    const { data } = await api.get(`/api/image/fetch/${image_id}`);

    image = data;

    form.image_source = image.image_source;
  }

  onMount(() => {
    loadTags();
    loadSelectedTags();
    loadImage();
  });

  let filteredTags = $derived(
    startsWith
      ? tags.filter((t) => t.tag_name.toUpperCase().startsWith(startsWith))
      : tags,
  );

  let showConfirm = $state(false);

  const form = useForm(() => ({
    image: null,
    tag: [],
    image_source: [],
  }));

  const tagForm = useForm({
    tag_name: "",
    tag_desc: "",
  });

  let objectUrl = $state(null);
  let newTagErrors = $state({});
  let newTagProcessing = $state(false);

  let preview = $derived(objectUrl ?? image?.preview_image_path_url ?? null);

  async function addTag() {
    if (newTagProcessing || !tagForm.tag_name) {
      return;
    }

    newTagErrors = {};
    newTagProcessing = true;

    try {
      const { data: newTag } = await api.post(apiAddTag().url, {
        tag_name: tagForm.tag_name.trim(),
        tag_desc: tagForm.tag_desc.trim(),
      });

      tags = [...tags, newTag].sort((a, b) =>
        a.tag_name.localeCompare(b.tag_name),
      );

      form.tag = [...new Set([...form.tag, newTag.tag_id])];

      tagForm.reset();
    } finally {
      newTagProcessing = false;
    }
  }

  function setPreview(file) {
    if (objectUrl) {
      URL.revokeObjectURL(objectUrl);
    }

    objectUrl = file ? URL.createObjectURL(file) : null;
  }

  function submit(e) {
    e.preventDefault();
    showConfirm = true;
  }

  function confirmSubmit() {
    showConfirm = false;

    form.post(postEditImage({ query: { image_id: image_id } }), {
      onSuccess: () => {
        setPreview(null);
      },
    });
  }

  let fileInput = $state(null);
  let files = $state();

  let file = $derived(files?.[0]);

  $effect(() => {
    if (!file) {
      if (preview) URL.revokeObjectURL(preview);
      preview = image?.preview_image_path_url ?? null;
      return;
    }

    form.image = file;

    const url = URL.createObjectURL(file);
    preview = url;

    return () => {
      URL.revokeObjectURL(url);
    };
  });

  function truncateName(name, maxLength = 24) {
    if (name.length <= maxLength) return name;

    const extIndex = name.lastIndexOf(".");
    const ext = extIndex !== -1 ? name.slice(extIndex) : "";
    const base = extIndex !== -1 ? name.slice(0, extIndex) : name;

    const keep = Math.floor((maxLength - ext.length - 1) / 2);

    return `${base.slice(0, keep)}…${base.slice(-keep)}${ext}`;
  }

  let fileLabel = $derived(
    file
      ? `${truncateName(file.name)} (${filesize(file.size, { standard: "jedec" })})`
      : $i18n.t("translate.chooseimage"),
  );
</script>

<svelte:head>
  <title>{$i18n.t("translate.editimage")} - {$title}</title>
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
      onclick={confirmSubmit}
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

<div class="breadcrumbs inline-flex bg-base-300 rounded-base mb-4 p-2">
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
    <li>
      <Link
        href={imageIndex({
          query: {
            per_page: 30,
            sort_by: "created_at",
            order: "latest",
          },
        })}
        class="text-base-content hover:text-primary focus:text-primary focus-visible:text-primary cursor-pointer no-underline transition-[color] focus:outline-0 focus:outline-transparent focus-visible:outline-0 focus-visible:outline-transparent"
        >{$i18n.t("translate.image")}</Link
      >
    </li>
    <li>{$i18n.t("translate.edit")}</li>
  </ul>
</div>

<h1 class="mb-4 text-lg font-bold">{$i18n.t("translate.editimage")}</h1>

<form class="space-y-4" onsubmit={submit}>
  {#if errors && Object.keys(errors).length > 0}
    <div role="alert" class="alert alert-error alert-soft inline-flex">
      <CircleAlert class="h-6 w-6 shrink-0" />
      <div>
        <h3 class="font-bold">{$i18n.t("translate.thereareerror")}</h3>
        <div class="text-sm">
          {#each Object.values(errors) as error, i (i)}
            <div>
              {Array.isArray(error) ? error.join(", ") : error}
            </div>
          {/each}
        </div>
      </div>
    </div>
  {/if}

  <div>
    <input
      type="file"
      accept=".png, .jpg, .jpeg, .webp"
      class="hidden"
      hidden
      bind:this={fileInput}
      bind:files
      disabled={form.processing || file || newTagProcessing}
    />

    <button
      use:tooltip={!form.processing || !file
        ? $i18n.t("translate.fileuploadmax") + ": " + maxUploadFilesize
        : null}
      type="button"
      class={`btn ${
        file
          ? "btn-success"
          : errors.image && !form.processing
            ? "btn-error"
            : "btn-primary"
      }`}
      disabled={form.processing || file || newTagProcessing}
      onclick={() => fileInput?.click()}
    >
      {#if !file}
        <Upload class="inline aspect-square h-4 w-4" />
      {:else}
        <Check class="inline aspect-square h-4 w-4" />
      {/if}
      <span class="block truncate">{fileLabel}</span>
    </button>

    {#if file}
      <button
        type="button"
        class="btn btn-error ms-2"
        disabled={form.processing || newTagProcessing}
        onclick={() => {
          files = null;
          fileInput.value = null;
        }}
      >
        {$i18n.t("translate.cancel")}
      </button>
    {/if}

    {#if form.progress}
      <div>
        <progress
          value={form.progress.percentage}
          max="100"
          class="progress progress-primary w-full"
        >
          {form.progress.percentage}%
        </progress>
      </div>

      <div class="inline-flex items-center gap-1">
        <span class="loading loading-spinner loading-sm"></span>
        {#if form.processing && form.progress?.percentage < 100}
          <span class="text-sm">{$i18n.t("translate.uploadingimage")}</span>
        {/if}
        {#if form.processing && form.progress?.percentage === 100}
          <span class="text-sm">{$i18n.t("translate.processingimage")}</span>
        {/if}
      </div>
    {/if}

    {#if preview}
      <div class="mt-4">
        <Lazy keep={true} fadeOptions={{ duration: 150, delay: 0 }}>
          <img
            src={preview}
            alt={$i18n.t("translate.selectedimagepreview")}
            class="rounded-base max-w-64"
          />
        </Lazy>
      </div>
    {/if}
  </div>

  <div class="mt-4">
    <input
      type="text"
      class={`input w-full ${errors.image_source ? "input-error" : ""}`}
      id="imageSourceInput"
      maxlength="255"
      bind:value={form.image_source}
      placeholder={$i18n.t("translate.image_source")}
      disabled={form.processing || newTagProcessing}
      autocomplete="off"
    />
  </div>

  <div class="bg-base-300 rounded-base collapse">
    <input type="checkbox" bind:checked={isCollapsed} />
    <div
      class="collapse-title flex items-center justify-between pe-4 font-bold"
    >
      <span class="text-sm">{$i18n.t("translate.addnewtag")}</span>
      <ChevronDown
        class={`w-5 h-5 inline aspect-square ${isCollapsed ? "rotate-180" : ""}`}
      />
    </div>
    <div class="collapse-content space-y-0">
      <div>
        <input
          type="text"
          class={`input w-full ${newTagErrors.tag_name ? "input-error" : ""}`}
          id="newTagNameInput"
          maxlength="255"
          bind:value={tagForm.tag_name}
          placeholder={$i18n.t("translate.tagname")}
          disabled={newTagProcessing || form.processing}
          autocomplete="off"
        />
      </div>
      <div>
        <input
          type="text"
          class={`input mt-4 w-full ${newTagErrors.tag_desc ? "input-error" : ""}`}
          id="newTagDescriptionInput"
          maxlength="255"
          bind:value={tagForm.tag_desc}
          placeholder={$i18n.t("translate.tagdesc")}
          disabled={newTagProcessing || form.processing}
          autocomplete="off"
        />
      </div>
      {#if Object.keys(newTagErrors).length > 0}
        <div class="text-error mt-1 text-sm">
          {#each Object.values(newTagErrors) as error, i (i)}
            <div>
              {Array.isArray(error) ? error.join(", ") : error}
            </div>
          {/each}
        </div>
      {/if}
      <button
        type="button"
        class="btn btn-primary mt-4"
        disabled={newTagProcessing ||
          form.processing ||
          tagsLoading ||
          !tagForm.tag_name}
        onclick={addTag}
      >
        <Plus class="inline h-4 w-4 aspect-square" />
        {$i18n.t("translate.add")}
      </button>
    </div>
  </div>

  <div class="text-end">
    <button
      type="button"
      class="btn btn-primary btn-square"
      use:tooltip={$i18n.t("translate.refresh")}
      onclick={loadTags}
      disabled={tagsLoading || form.processing}
    >
      <RefreshCw class="inline h-4 w-4 aspect-square" />
    </button>
  </div>

  {#if countTags > 0}
    <div class="flex flex-wrap gap-2">
      <button
        class={`btn btn-neutral ${!startsWith || form.processing ? "btn-disabled" : ""}`}
        onclick={() => (startsWith = null)}
        disabled={!startsWith || form.processing}
      >
        {$i18n.t("translate.all")}
      </button>

      {#each "ABCDEFGHIJKLMNOPQRSTUVWXYZ".split("") as letter (letter)}
        <button
          class={`btn btn-neutral btn-square ${startsWith === letter || form.processing ? "btn-disabled" : ""}`}
          onclick={() => (startsWith = letter)}
          disabled={startsWith === letter || form.processing}
        >
          {letter}
        </button>
      {/each}
    </div>
  {/if}

  <div>
    {#if tagsLoading}
      <div class="inline-flex items-center gap-1">
        <span class="loading loading-spinner loading-sm"></span>
        <span class="text-sm">{$i18n.t("translate.loading")}</span>
      </div>
    {:else if countTags > 0}
      <div class="inline-flex items-center flex-wrap gap-4">
        {#each filteredTags as tag (tag.tag_id)}
          <label
            class="label text-sm select-none"
            use:tooltip={!form.processing ? tag.tag_desc : null}
          >
            <input
              type="checkbox"
              class={`checkbox checkbox-sm ${errors.tag ? "checkbox-error" : ""}`}
              id={"tag" + tag.tag_id + "Input"}
              checked={Array.isArray(form.tag) && form.tag.includes(tag.tag_id)}
              disabled={form.processing || newTagProcessing}
              onchange={(e) => {
                const id = tag.tag_id;

                if (e.target.checked) {
                  form.tag = [...new Set([...form.tag, id])];
                } else {
                  form.tag = form.tag.filter((t) => t !== id);
                }
              }}
            />
            {tag.tag_name}
          </label>
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
  </div>

  <div>
    <button
      type="submit"
      class="btn btn-primary"
      disabled={form.processing || newTagProcessing}
    >
      {#if form.processing}
        <div class="flex items-center gap-2">
          <span class="loading loading-spinner loading-xs"></span>{$i18n.t(
            "translate.saving",
          )}
        </div>
      {:else}
        <div class="flex items-center gap-1">
          <Save class="inline h-4 w-4 aspect-square" />
          {$i18n.t("translate.save")}
        </div>
      {/if}
    </button>
  </div>
</form>
