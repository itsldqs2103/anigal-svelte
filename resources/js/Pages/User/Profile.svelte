<script>
  import { Link, router, useForm } from "@inertiajs/svelte";
  import { House, LogOut } from "@lucide/svelte";

  import Breadcrumb from "@/js/Components/Breadcrumb.svelte";
  import i18n from "@/js/lib/i18n";
  import { isUserEdit } from "@/js/lib/isEdit.svelte";
  import { title } from "@/js/lib/title";
  import { index } from "@/js/wayfinder/actions/App/Http/Controllers/MainController";
  import {
    getEditPassword as editPassword,
    getEditProfile as editProfile,
    postLogout as logout,
  } from "@/js/wayfinder/actions/App/Http/Controllers/UserController";

  let { user } = $props();

  const form = useForm({});

  function getEditProfile() {
    router.visit(
      editProfile({
        query: {
          user_id: user.user_id,
        },
      }),
    );
  }

  function getEditPassword() {
    router.visit(
      editPassword({
        query: {
          user_id: user.user_id,
        },
      }),
    );
  }

  function postLogout(e) {
    e.preventDefault();

    form.post(logout());
  }
</script>

<svelte:head>
  <title>{$i18n.t("translate.profile")} - {$title}</title>
</svelte:head>

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
    <li>{$i18n.t("translate.profile")}</li>
  </ul>
</Breadcrumb>

<h1 class="mb-4 text-lg font-bold">{$i18n.t("translate.profile")}</h1>

<div class="space-y-4">
  <div>
    <input
      type="text"
      placeholder={$i18n.t("translate.email")}
      class="input w-full"
      value={user.email}
      name="emailInput"
      autocomplete="off"
      disabled
    />
  </div>

  <div>
    <input
      type="text"
      placeholder={$i18n.t("translate.username")}
      class="input w-full"
      value={user.username}
      name="usernameInput"
      autocomplete="off"
      disabled
    />
  </div>

  <div>
    <input
      type="text"
      placeholder={$i18n.t("translate.fullname")}
      class="input w-full"
      value={user.fullname}
      name="nameInput"
      autocomplete="off"
      disabled
    />
  </div>

  {#if isUserEdit.value === true}
    <div>
      <button
        class="btn btn-success"
        type="button"
        onclick={getEditProfile}
        disabled={form.processing}
      >
        {$i18n.t("translate.editprofile")}
      </button>
    </div>

    <div>
      <button
        class="btn btn-success"
        type="button"
        onclick={getEditPassword}
        disabled={form.processing}
      >
        {$i18n.t("translate.editpassword")}
      </button>
    </div>
  {/if}

  <form onsubmit={postLogout}>
    <button class="btn btn-error" type="submit" disabled={form.processing}>
      {#if form.processing}
        <div class="flex items-center gap-2">
          <span class="loading loading-spinner loading-xs"></span>{$i18n.t(
            "translate.loggingout",
          )}
        </div>
      {:else}
        <div class="flex items-center gap-1">
          <LogOut class="inline h-4 w-4 aspect-square" />
          {$i18n.t("translate.logout")}
        </div>
      {/if}
    </button>
  </form>
</div>
