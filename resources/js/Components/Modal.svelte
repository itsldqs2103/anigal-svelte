<script>
    let { open = false, title = "", backdrop, children, footer } = $props();
</script>

<dialog class="modal" class:modal-open={open}>
    <div class="modal-box max-w-96">
        <div class="mb-2 flex items-center justify-between gap-4">
            <h3 class="text-lg font-bold">{title}</h3>
        </div>

        {#if children}
            {@render children()}
        {/if}

        <div class="modal-action mt-4">
            {#if footer}
                {@render footer()}
            {/if}
        </div>
    </div>

    <form method="dialog" class="modal-backdrop">
        {#if backdrop}
            {@render backdrop()}
        {/if}
    </form>
</dialog>
