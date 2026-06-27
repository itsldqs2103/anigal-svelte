<script>
  import { Link, page, router, useForm } from "@inertiajs/svelte";
  import {
    AtSign,
    EllipsisVertical,
    House,
    KeyRound,
    LogOut,
    Mail,
    User,
  } from "@lucide/svelte";

  import Breadcrumb from "@/js/Components/Breadcrumb.svelte";
  import i18n from "@/js/lib/i18n";
  import { isUserEdit } from "@/js/lib/isEdit.svelte";
  import { title } from "@/js/lib/title";
  import { index } from "@/js/wayfinder/actions/App/Http/Controllers/MainController";
  import {
    getEditPassword as editPassword,
    getEditProfile as editProfile,
    postLogout as logout,
    profile,
  } from "@/js/wayfinder/actions/App/Http/Controllers/UserController";

  let { user, children, countUploaded, countLiked } = $props();

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

  const avatarLetter = $derived(user.fullname?.charAt(0)?.toUpperCase());
</script>

<svelte:head>
  <title>{$i18n.t("translate.profile")} - {$title}</title>
</svelte:head>

<Breadcrumb>
  <ul>
    <li>
      <Link
        href={index()}
        class="text-base-content hover:text-primary flex items-center gap-1 no-underline"
      >
        <House class="h-4 w-4" />
        {$i18n.t("translate.home")}
      </Link>
    </li>

    <li>{$i18n.t("translate.profile")}</li>
  </ul>
</Breadcrumb>

<div>
  <div class="rounded-base bg-base-300">
    <div class="h-32 bg-primary rounded-t-base"></div>

    <div class="relative px-4 pb-4">
      <div class="-mt-16 flex justify-center sm:justify-start">
        <div class="avatar">
          <div
            class="flex h-24 w-24 items-center justify-center rounded-full border-4 border-base-100 bg-primary text-5xl font-bold text-primary-content select-none"
          >
            {avatarLetter}
          </div>
        </div>
      </div>

      <div class="-mt-4 flex justify-end">
        <div class="dropdown dropdown-end select-none">
          <button type="button" class="btn btn-square btn-primary">
            <EllipsisVertical class="w-4 h-4 aspect-square inline" />
          </button>
          <ul
            class="dropdown-content mt-1 menu bg-base-100 rounded-box z-1 w-42 space-y-1 p-2"
          >
            {#if isUserEdit.value}
              <li>
                <button
                  onclick={getEditProfile}
                  disabled={form.processing}
                  type="button"
                >
                  <User class="h-4 w-4 inline aspect-square" />
                  {$i18n.t("translate.editprofile")}
                </button>
              </li>
              <li>
                <button
                  onclick={getEditPassword}
                  disabled={form.processing}
                  type="button"
                >
                  <KeyRound class="h-4 w-4 inline aspect-square" />
                  {$i18n.t("translate.editpassword")}
                </button>
              </li>
            {/if}
            <li>
              <button
                type="button"
                onclick={postLogout}
                disabled={form.processing}
              >
                <LogOut class="h-4 w-4 inline aspect-square" />
                {$i18n.t("translate.logout")}
              </button>
            </li>
          </ul>
        </div>
      </div>

      <div class="mt-2 text-center sm:text-left">
        <h1 class="text-lg font-bold">
          {user.fullname}
        </h1>

        <div class="text-base-content/50">
          <span><AtSign class="w-4 h-4 inline aspect-square" /></span>
          <span>{user.username}</span>
        </div>

        <div class="text-base-content/50">
          <Mail class="h-4 w-4 shrink-0 inline aspect-square" />
          <span>{user.email}</span>
        </div>
      </div>
    </div>
  </div>

  <div class="mt-4">
    <div role="tablist" class="tabs tabs-border">
      <Link
        href={profile({
          query: {
            tab: "uploaded",
            user_id: user.user_id,
          },
        })}
        role="tab"
        class={`tab ${page.component === "User/Uploaded" ? "tab-active" : ""}`}
        >{$i18n.t("translate.uploaded")} ({countUploaded})</Link
      >
      <Link
        href={profile({
          query: {
            tab: "liked",
            user_id: user.user_id,
          },
        })}
        role="tab"
        class={`tab ${page.component === "User/Liked" ? "tab-active" : ""}`}
        >{$i18n.t("translate.liked")} ({countLiked})</Link
      >
    </div>
    {@render children?.()}
  </div>
</div>
