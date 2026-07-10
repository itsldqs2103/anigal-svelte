<script>
  import { Link, router, useForm } from "@inertiajs/svelte";
  import { CircleAlert, House, Save } from "@lucide/svelte";
  import clsx from "clsx";
  import { onMount } from "svelte";

  import Modal from "@/js/Components/Modal.svelte";
  import { api } from "@/js/lib/axios";
  import i18n from "@/js/lib/i18n";
  import { title } from "@/js/lib/title";
  import { fetchTag } from "@/js/wayfinder/actions/App/Http/Controllers/ApiController";
  import { index } from "@/js/wayfinder/actions/App/Http/Controllers/MainController";
  import {
    index as tagIndex,
    postEditTag,
  } from "@/js/wayfinder/actions/App/Http/Controllers/TagController";

  let { errors = {}, tag_id } = $props();

  let showConfirm = $state(false);

  const form = useForm(() => ({
    tag_name: [],
    tag_desc: [],
  }));

  async function loadTag() {
    const { data } = await api.get(fetchTag({ tag: tag_id }).url);

    form.tag_name = data.tag_name;
    form.tag_desc = data.tag_desc;
  }

  onMount(() => {
    loadTag();
  });

  function submit(e) {
    e.preventDefault();
    showConfirm = true;
  }

  function confirmSubmit() {
    showConfirm = false;

    form.post(postEditTag({ query: { tag_id: tag_id } }));
  }

  function autofocus(node) {
    node.focus();
  }
</script>

<svelte:head>
  <title>{$i18n.t("translate.edittag")} - {$title}</title>
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

<div class="breadcrumbs bg-base-300 rounded-base mb-4 p-2">
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
        href={tagIndex({
          query: {
            starts_with: null,
            per_page: 30,
            sort_by: "tag_name",
            order: "oldest",
          },
        })}
        class="text-base-content hover:text-primary focus:text-primary focus-visible:text-primary cursor-pointer no-underline transition-[color] focus:outline-0 focus:outline-transparent focus-visible:outline-0 focus-visible:outline-transparent"
        >{$i18n.t("translate.tag")}</Link
      >
    </li>
    <li>{$i18n.t("translate.edit")}</li>
  </ul>
</div>

<h1 class="mb-4 text-lg font-bold">{$i18n.t("translate.edittag")}</h1>

<form class="space-y-4" onsubmit={submit}>
  {#if errors && Object.keys(errors).length > 0}
    <div role="alert" class="alert alert-error alert-soft inline-flex">
      <CircleAlert class="h-6 w-6 inline aspect-square" />
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
      type="text"
      class={clsx(
        "input w-full",
        errors.tag_name && !form.processing && "input-error",
      )}
      id="tagNameInput"
      maxlength="255"
      bind:value={form.tag_name}
      placeholder={$i18n.t("translate.tagname")}
      disabled={form.processing}
      autocomplete="off"
      use:autofocus
    />
  </div>
  <div>
    <input
      type="text"
      class={clsx(
        "input w-full",
        errors.tag_desc && !form.processing && "input-error",
      )}
      id="tagDescriptionInput"
      maxlength="255"
      bind:value={form.tag_desc}
      placeholder={$i18n.t("translate.tagdesc")}
      disabled={form.processing}
      autocomplete="off"
    />
  </div>
  <div>
    <button type="submit" class="btn btn-primary" disabled={form.processing}>
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
  <div>
    <button
      class="btn btn-success"
      type="button"
      onclick={() => router.get(tagIndex())}
      >{$i18n.t("translate.cancel")}</button
    >
  </div>
</form>
