<script>
  import { Link, page, router, useForm } from "@inertiajs/svelte";
  import {
    AtSign,
    CalendarClock,
    Check,
    EllipsisVertical,
    House,
    KeyRound,
    LogOut,
    Mail,
    Pencil,
    Upload,
    User,
  } from "@lucide/svelte";
  import clsx from "clsx";
  import { filesize } from "filesize";

  import Breadcrumb from "@/js/Components/Breadcrumb.svelte";
  import Modal from "@/js/Components/Modal.svelte";
  import i18n from "@/js/lib/i18n";
  import { isUserEdit } from "@/js/lib/isEdit.svelte";
  import { title } from "@/js/lib/title";
  import { tooltip } from "@/js/lib/tooltip";
  import { truncate } from "@/js/lib/truncate";
  import { index } from "@/js/wayfinder/actions/App/Http/Controllers/MainController";
  import {
    getEditPassword as editPassword,
    getEditProfile as editProfile,
    postDeleteAvatar,
    postEditAvatar,
    postLogout as logout,
    profile,
  } from "@/js/wayfinder/actions/App/Http/Controllers/UserController";

  let {
    user,
    children,
    countUploaded,
    countLiked,
    errors = {},
    maxUploadFilesize,
  } = $props();

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

  let showAvatarModal = $state(false);

  const avatarForm = useForm({
    avatar: null,
  });

  function openAvatarModal() {
    if (!isUserEdit.value) return;

    showAvatarModal = true;
  }

  let fileInput = $state(null);
  let files = $state();

  let file = $derived(files?.[0]);

  $effect(() => {
    avatarForm.avatar = file ?? null;
  });

  function uploadAvatar() {
    showAvatarModal = false;
    avatarForm.post(
      postEditAvatar({
        query: {
          user_id: user.user_id,
        },
      }),
      {
        onSuccess: () => {
          files = null;
          avatarForm.reset("avatar");

          if (fileInput) {
            fileInput.value = "";
          }
        },
      },
    );
  }

  function removeAvatar() {
    showAvatarModal = false;
    avatarForm.post(
      postDeleteAvatar({
        query: {
          user_id: user.user_id,
        },
      }),
    );
  }

  let fileLabel = $derived(
    file
      ? `${truncate(file.name, 24)} (${filesize(file.size, { standard: "jedec" })})`
      : $i18n.t("translate.chooseimage"),
  );
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

<Modal open={showAvatarModal} title={$i18n.t("translate.changeavatar")}>
  <div class="space-y-2">
    <div>
      <input
        type="file"
        accept=".png, .jpg, .jpeg, .webp"
        class="hidden"
        hidden
        bind:this={fileInput}
        bind:files
        disabled={avatarForm.processing || file}
      />

      <button
        use:tooltip={!avatarForm.processing || !file
          ? $i18n.t("translate.fileuploadmax") + ": " + maxUploadFilesize
          : null}
        type="button"
        class={clsx(
          "btn",
          file
            ? "btn-success"
            : errors.image && !avatarForm.processing
              ? "btn-error"
              : "btn-primary",
        )}
        disabled={avatarForm.processing || file}
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
          disabled={form.processing}
          onclick={() => {
            files = null;
            fileInput.value = null;
          }}
        >
          {$i18n.t("translate.cancel")}
        </button>
      {/if}
    </div>

    {#if user.avatar}
      <button
        type="button"
        class="btn btn-error w-full"
        onclick={removeAvatar}
        disabled={avatarForm.processing}
      >
        {$i18n.t("translate.deleteavatar")}
      </button>
    {/if}
  </div>

  {#snippet footer()}
    <button
      class="btn btn-neutral"
      onclick={() => (showAvatarModal = false)}
      disabled={avatarForm.processing}
    >
      {$i18n.t("translate.cancel")}
    </button>

    <button
      class="btn btn-primary"
      onclick={uploadAvatar}
      disabled={!avatarForm.avatar || avatarForm.processing}
    >
      {$i18n.t("translate.upload")}
    </button>
  {/snippet}

  {#snippet backdrop()}
    <button
      onclick={() => (showAvatarModal = false)}
      disabled={avatarForm.processing}
    >
      {$i18n.t("translate.close")}
    </button>
  {/snippet}
</Modal>

<div>
  <div class="rounded-base bg-base-300">
    <div class="bg-primary rounded-t-base h-24"></div>

    <div class="relative px-4 pb-4">
      <div class="-mt-16 flex justify-center sm:justify-start">
        <button
          type="button"
          class="avatar group cursor-pointer"
          onclick={openAvatarModal}
        >
          <div
            class="border-base-100 relative h-24 w-24 overflow-hidden rounded-full border-4"
          >
            {#if user.avatar}
              <img
                src={user.avatar_path}
                alt={user.fullname}
                class="h-full w-full object-cover"
              />
            {:else}
              <div
                class="bg-primary text-primary-content flex h-full w-full items-center justify-center text-5xl font-bold"
              >
                {avatarLetter}
              </div>
            {/if}

            <div
              class="group-hover:bg-base-100/70 absolute inset-0 flex items-center justify-center rounded-full opacity-0 transition-[opacity,background-color] group-hover:opacity-100"
            >
              <Pencil class="inline aspect-square h-6 w-6" />
            </div>
          </div>
        </button>
      </div>

      <div class="-mt-4 flex justify-end">
        <div class="dropdown dropdown-end select-none">
          <button type="button" class="btn btn-square btn-primary">
            <EllipsisVertical class="inline aspect-square h-4 w-4" />
          </button>
          <ul
            class="dropdown-content menu bg-base-100 rounded-box z-1 mt-1 w-42 space-y-1 p-2"
          >
            {#if isUserEdit.value}
              <li>
                <button
                  onclick={getEditProfile}
                  disabled={form.processing}
                  type="button"
                >
                  <User class="inline aspect-square h-4 w-4" />
                  {$i18n.t("translate.editprofile")}
                </button>
              </li>
              <li>
                <button
                  onclick={getEditPassword}
                  disabled={form.processing}
                  type="button"
                >
                  <KeyRound class="inline aspect-square h-4 w-4" />
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
                <LogOut class="inline aspect-square h-4 w-4" />
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

        <div class="text-base-content/50 text-sm">
          <span><AtSign class="inline aspect-square h-4 w-4" /></span>
          <span>{user.username}</span>
        </div>

        <div class="text-base-content/50 text-sm">
          <Mail class="inline aspect-square h-4 w-4" />
          <span>{user.email}</span>
        </div>

        <div class="text-base-content/50 text-sm">
          <CalendarClock class="inline aspect-square h-4 w-4" />
          <span>{$i18n.t("translate.joined")} {user.created_at_diff}</span>
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
