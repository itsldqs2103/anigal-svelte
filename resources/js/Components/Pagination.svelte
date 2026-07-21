<script>
  import { router } from "@inertiajs/svelte";
  import { ArrowLeft, ArrowRight } from "@lucide/svelte";
  import { onMount } from "svelte";

  import i18n from "@/js/lib/i18n";

  export let links = [];
  export let from = 0;
  export let to = 0;
  export let total = 0;

  const decodeHTML = (html = "") => {
    const textarea = document.createElement("textarea");
    textarea.innerHTML = html;
    return textarea.value.replace(/[«»]/g, "").trim();
  };

  $: processedLinks = links.map((link, i) => ({
    ...link,
    label: decodeHTML(link.label),
    isPrev: i === 0,
    isNext: i === links.length - 1,
  }));

  $: prev = processedLinks[0];
  $: next = processedLinks[processedLinks.length - 1];
  $: middleLinks = processedLinks.slice(1, -1);

  function goToPage(url) {
    router.get(url);
  }

  let isNavigating = false;

  router.on("start", () => {
    isNavigating = true;
  });

  router.on("finish", () => {
    isNavigating = false;
  });

  onMount(() => {
    const handleKeydown = (event) => {
      if (isNavigating) return;

      const target = event.target;
      const isTyping =
        target instanceof HTMLInputElement ||
        target instanceof HTMLTextAreaElement ||
        target?.isContentEditable;

      if (isTyping) return;

      if (event.key === "ArrowLeft" && prev?.url) {
        event.preventDefault();
        goToPage(prev.url);
      }

      if (event.key === "ArrowRight" && next?.url) {
        event.preventDefault();
        goToPage(next.url);
      }
    };

    window.addEventListener("keydown", handleKeydown);

    return () => {
      window.removeEventListener("keydown", handleKeydown);
    };
  });
</script>

{#if processedLinks.length > 3}
  <div class="mt-4">
    <div class="flex items-center justify-center">
      <nav class="flex gap-2 lg:hidden">
        {#if prev}
          {#if prev.url}
            <button
              type="button"
              onclick={goToPage(prev.url)}
              class="btn btn-neutral"
            >
              <span class="flex items-center gap-1">
                <ArrowLeft class="inline aspect-square h-4 w-4" />
                {prev.label}
              </span>
            </button>
          {:else}
            <button class="btn btn-neutral" disabled>
              <span class="flex items-center gap-1">
                <ArrowLeft class="inline aspect-square h-4 w-4" />
                {prev.label}
              </span>
            </button>
          {/if}
        {/if}

        {#if next}
          {#if next.url}
            <button
              type="button"
              onclick={goToPage(next.url)}
              class="btn btn-neutral"
            >
              <span class="flex items-center gap-1">
                {next.label}
                <ArrowRight class="inline aspect-square h-4 w-4" />
              </span>
            </button>
          {:else}
            <button class="btn btn-neutral" disabled>
              <span class="flex items-center gap-1">
                {next.label}
                <ArrowRight class="inline aspect-square h-4 w-4" />
              </span>
            </button>
          {/if}
        {/if}
      </nav>

      <nav class="hidden gap-2 lg:flex">
        {#if prev}
          {#if prev.url}
            <button
              type="button"
              onclick={goToPage(prev.url)}
              class="btn btn-neutral btn-square"
            >
              <ArrowLeft class="inline aspect-square h-4 w-4" />
            </button>
          {:else}
            <button class="btn btn-neutral btn-square" disabled>
              <ArrowLeft class="inline aspect-square h-4 w-4" />
            </button>
          {/if}
        {/if}

        {#each middleLinks as link (link.label)}
          {#if link.active || !link.url}
            <button type="button" disabled class="btn btn-neutral">
              {link.label}
            </button>
          {:else}
            <button
              type="button"
              onclick={goToPage(link.url)}
              class="btn btn-neutral"
            >
              {link.label}
            </button>
          {/if}
        {/each}

        {#if next}
          {#if next.url}
            <button
              type="button"
              onclick={goToPage(next.url)}
              class="btn btn-neutral btn-square"
            >
              <ArrowRight class="inline aspect-square h-4 w-4" />
            </button>
          {:else}
            <button class="btn btn-neutral btn-square" disabled>
              <ArrowRight class="inline aspect-square h-4 w-4" />
            </button>
          {/if}
        {/if}
      </nav>
    </div>

    {#if total > 0}
      <div class="mt-2 text-center">
        <p class="text-base-content/70 text-sm">
          {$i18n.t("translate.showingfrom")} <b>{from}</b>
          {$i18n.t("translate.to")} <b>{to}</b>
          {$i18n.t("translate.of")} <b>{total}</b>
          {$i18n.t("translate.results")}
        </p>

        <p class="text-base-content/50 text-sm">
          ({$i18n.t("translate.navigateWithKeyboard")})
        </p>
      </div>
    {/if}
  </div>
{/if}
