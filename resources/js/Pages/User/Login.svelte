<script>
  import { Link, useForm } from "@inertiajs/svelte";
  import { CircleAlert, House, LogIn } from "@lucide/svelte";
  import clsx from "clsx";

  import i18n from "@/js/lib/i18n";
  import { title } from "@/js/lib/title";
  import { index } from "@/js/wayfinder/actions/App/Http/Controllers/MainController";
  import { postLogin } from "@/js/wayfinder/actions/App/Http/Controllers/UserController";

  let { errors = {} } = $props();

  let showPassword = $state(false);

  function togglePassword() {
    showPassword = !showPassword;
  }

  const form = useForm({
    login: null,
    password: null,
    remember: false,
  });

  function submit(e) {
    e.preventDefault();

    form.post(postLogin());
  }

  function autofocus(node) {
    node.focus();
  }
</script>

<svelte:head>
  <title>{$i18n.t("translate.login")} - {$title}</title>
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
    <li>{$i18n.t("translate.login")}</li>
  </ul>
</div>

<div class="bg-base-200 rounded-base p-4">
  <h1 class="mb-4 text-lg font-bold">{$i18n.t("translate.login")}</h1>

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
        placeholder={$i18n.t("translate.email") +
          " " +
          $i18n.t("translate.or").toLowerCase() +
          " " +
          $i18n.t("translate.username").toLowerCase()}
        id="loginInput"
        maxlength="255"
        bind:value={form.login}
        class={clsx(
          "input w-full",
          errors.login && !form.processing && "input-error",
        )}
        autocomplete="off"
        disabled={form.processing}
        use:autofocus
      />
    </div>

    <div>
      <input
        type={showPassword ? "text" : "password"}
        id="passwordInput"
        maxlength="255"
        bind:value={form.password}
        class={clsx(
          "input w-full",
          errors.password && !form.processing && "input-error",
        )}
        placeholder={$i18n.t("translate.password")}
        disabled={form.processing}
      />
    </div>

    <div class="space-y-2">
      <div>
        <label class="label text-sm select-none">
          <input
            type="checkbox"
            class="checkbox checkbox-sm"
            id="showPasswordInput"
            onchange={togglePassword}
            disabled={form.processing}
          />
          {$i18n.t("translate.showpassword")}
        </label>
      </div>

      <div>
        <label class="label text-sm select-none">
          <input
            type="checkbox"
            class="checkbox checkbox-sm"
            id="rememberInput"
            bind:checked={form.remember}
            disabled={form.processing}
          />
          {$i18n.t("translate.rememberlogin")}
        </label>
      </div>
    </div>

    <div>
      <button type="submit" class="btn btn-primary" disabled={form.processing}>
        {#if form.processing}
          <div class="flex items-center gap-2">
            <span class="loading loading-spinner loading-xs"></span>{$i18n.t(
              "translate.loggingin",
            )}
          </div>
        {:else}
          <div class="flex items-center gap-1">
            <LogIn class="inline h-4 w-4 aspect-square" />
            {$i18n.t("translate.login")}
          </div>
        {/if}
      </button>
    </div>
  </form>
</div>
