<script>
  import { page } from "@inertiajs/svelte";
  import { Pencil, PencilOff } from "@lucide/svelte";
  import clsx from "clsx";

  import i18n from "@/js/lib/i18n";
  import { isUserEdit } from "@/js/lib/isEdit.svelte";
  import { tooltip } from "@/js/lib/tooltip";

  let { children } = $props();

  const isAuth = $derived(page.props.currentAuth);

  let breadcrumbEl = $state(null);
  let isBreadcrumbVisible = $state(false);

  function toggleEdit() {
    isUserEdit.value = !isUserEdit.value;
  }

  $effect(() => {
    if (!breadcrumbEl) return;

    const observer = new IntersectionObserver(
      ([entry]) => {
        isBreadcrumbVisible = entry.isIntersecting;
      },
      {
        threshold: 0,
      },
    );

    observer.observe(breadcrumbEl);

    return () => observer.disconnect();
  });

  const isFloating = $derived(!isBreadcrumbVisible);

  const buttonColor = $derived(isUserEdit.value ? "btn-primary" : "btn-error");
</script>

<div
  class="mb-4 flex items-center justify-between gap-4"
  bind:this={breadcrumbEl}
>
  <div class="breadcrumbs bg-base-300 rounded-base p-2">
    {#if children}
      {@render children()}
    {/if}
  </div>

  {#if isAuth}
    <div use:tooltip={$i18n.t("translate.toggleeditmode")}>
      <button
        class={clsx("btn btn-square btn-sm", buttonColor)}
        onclick={toggleEdit}
      >
        {#if isUserEdit.value}
          <Pencil class="inline h-4 w-4 aspect-square" />
        {:else}
          <PencilOff class="inline h-4 w-4 aspect-square" />
        {/if}
      </button>
    </div>
  {/if}
</div>

{#if isFloating}
  <div
    class="bg-base-300 fixed top-0 left-0 z-50 flex w-full items-center justify-between gap-4 p-4 lg:left-64 lg:w-[calc(100%-16rem)]"
  >
    <div class="breadcrumbs py-0">
      {#if children}
        {@render children()}
      {/if}
    </div>

    {#if isAuth}
      <div use:tooltip={$i18n.t("translate.toggleeditmode")}>
        <button
          class={clsx("btn btn-square btn-sm", buttonColor)}
          onclick={toggleEdit}
        >
          {#if isUserEdit.value}
            <Pencil class="inline h-4 w-4 aspect-square" />
          {:else}
            <PencilOff class="inline h-4 w-4 aspect-square" />
          {/if}
        </button>
      </div>
    {/if}
  </div>
{/if}
