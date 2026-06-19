<script>
  import { Link, useForm } from "@inertiajs/svelte";
  import { CircleAlert, House, Save } from "@lucide/svelte";
  import clsx from "clsx";

  import i18n from "@/js/lib/i18n";
  import { title } from "@/js/lib/title";
  import { index } from "@/js/wayfinder/actions/App/Http/Controllers/MainController";
  import {
    index as tagIndex,
    postAddTag,
  } from "@/js/wayfinder/actions/App/Http/Controllers/TagController";

  let { errors = {} } = $props();

  const form = useForm({
    tag_name: "",
    tag_desc: "",
  });

  function submit(e) {
    e.preventDefault();
    form.post(postAddTag());
  }

  function autofocus(node) {
    node.focus();
  }
</script>

<svelte:head>
  <title>Add tag - {$title}</title>
</svelte:head>

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
    <li>{$i18n.t("translate.addnew")}</li>
  </ul>
</div>

<h1 class="mb-4 text-lg font-bold">{$i18n.t("translate.addnewtag")}</h1>

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
            "translate.adding",
          )}
        </div>
      {:else}
        <div class="flex items-center gap-1">
          <Save class="inline h-4 w-4 aspect-square" />
          {$i18n.t("translate.add")}
        </div>
      {/if}
    </button>
  </div>
</form>
