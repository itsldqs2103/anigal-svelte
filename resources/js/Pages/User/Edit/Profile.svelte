<script>
  import { Link, router, useForm } from "@inertiajs/svelte";
  import { CircleAlert, House, Save } from "@lucide/svelte";
  import clsx from "clsx";

  import Modal from "@/js/Components/Modal.svelte";
  import i18n from "@/js/lib/i18n";
  import { title } from "@/js/lib/title";
  import { index } from "@/js/wayfinder/actions/App/Http/Controllers/MainController";
  import {
    postEditProfile,
    profile as userIndex,
  } from "@/js/wayfinder/actions/App/Http/Controllers/UserController";

  let { user, errors = {} } = $props();

  let showConfirm = $state(false);

  function profile() {
    router.visit(userIndex({ query: { user_id: user.user_id } }));
  }

  const form = useForm(() => ({
    email: user.email,
    username: user.username,
    fullname: user.fullname,
  }));

  function submit(e) {
    e.preventDefault();
    showConfirm = true;
  }

  function confirmSubmit() {
    showConfirm = false;

    form.post(postEditProfile({ query: { user_id: user.user_id } }));
  }

  function autofocus(node) {
    node.focus();
  }
</script>

<svelte:head>
  <title>{$i18n.t("translate.editprofile")} - {$title}</title>
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
        href={userIndex({
          query: {
            user_id: user.user_id,
          },
        })}
        class="text-base-content hover:text-primary focus:text-primary focus-visible:text-primary cursor-pointer no-underline transition-[color] focus:outline-0 focus:outline-transparent focus-visible:outline-0 focus-visible:outline-transparent"
        >{$i18n.t("translate.profile")}</Link
      >
    </li>
    <li>{$i18n.t("translate.editprofile")}</li>
  </ul>
</div>

<h1 class="mb-4 text-lg font-bold">{$i18n.t("translate.editprofile")}</h1>

<div class="space-y-4">
  <form onsubmit={submit} class="space-y-4">
    {#if errors && Object.keys(errors).length > 0}
      <div role="alert" class="alert alert-error alert-soft inline-flex">
        <CircleAlert class="h-6 w-6 inline aspect-square" />
        <div>
          <h3 class="font-bold">
            {$i18n.t("translate.thereareerror")}
          </h3>
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
        placeholder={$i18n.t("translate.email")}
        class={clsx(
          "input w-full",
          errors.email && !form.processing && "input-error",
        )}
        id="emailInput"
        maxlength="255"
        bind:value={form.email}
        disabled={form.processing}
        autocomplete="off"
        use:autofocus
      />
    </div>

    <div>
      <input
        type="text"
        placeholder={$i18n.t("translate.username")}
        class={clsx(
          "input w-full",
          errors.username && !form.processing && "input-error",
        )}
        id="usernameInput"
        maxlength="255"
        bind:value={form.username}
        disabled={form.processing}
        autocomplete="off"
      />
    </div>

    <div>
      <input
        type="text"
        placeholder={$i18n.t("translate.fullname")}
        class={clsx(
          "input w-full",
          errors.fullname && !form.processing && "input-error",
        )}
        id="fullnameInput"
        maxlength="255"
        bind:value={form.fullname}
        disabled={form.processing}
        autocomplete="off"
      />
    </div>

    <div>
      <button class="btn btn-primary" type="submit" disabled={form.processing}>
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

  <div>
    <button
      class="btn btn-success"
      type="button"
      onclick={profile}
      disabled={form.processing}
    >
      {$i18n.t("translate.cancel")}
    </button>
  </div>
</div>
