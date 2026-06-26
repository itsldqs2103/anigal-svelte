<script>
  import { Link, router, useForm } from "@inertiajs/svelte";
  import {
    CircleAlert,
    EllipsisVertical,
    House,
    KeyRound,
    LogOut,
    Mail,
    User,
  } from "@lucide/svelte";
  import Lazy from "svelte-lazy";

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

  let { user, likedImages, uploadedImages } = $props();

  let activeTab = $state("uploaded");

  const currentImages = $derived(
    activeTab === "uploaded" ? uploadedImages : likedImages,
  );

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

  let loadedImages = $state({});

  function handleImageLoad(imageId) {
    loadedImages = {
      ...loadedImages,
      [imageId]: true,
    };
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

      {#if isUserEdit.value}
        <div class="-mt-4 flex justify-end">
          <div class="dropdown dropdown-end select-none">
            <button type="button" class="btn btn-square btn-primary">
              <EllipsisVertical class="w-4 h-4 aspect-square inline" />
            </button>
            <ul
              class="dropdown-content mt-1 menu bg-base-100 rounded-box z-1 w-42 space-y-1 p-2"
            >
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
      {/if}

      <div class="mt-4 text-center sm:text-left">
        <h1 class="text-lg font-bold">
          {user.fullname}
        </h1>

        <div class="text-base-content/50">
          @{user.username}
        </div>

        <div
          class="flex items-center justify-center gap-1 text-base-content/50 sm:justify-start"
        >
          <Mail class="h-4 w-4 shrink-0 inline aspect-square" />
          <span>{user.email}</span>
        </div>
      </div>
    </div>
  </div>

  <div class="mt-4">
    <div role="tablist" class="tabs tabs-border">
      <button
        role="tab"
        class:tab-active={activeTab === "uploaded"}
        class="tab"
        onclick={() => (activeTab = "uploaded")}
      >
        {$i18n.t("translate.uploaded")} ({uploadedImages.length})
      </button>

      <button
        role="tab"
        class:tab-active={activeTab === "liked"}
        class="tab"
        onclick={() => (activeTab = "liked")}
      >
        {$i18n.t("translate.liked")} ({likedImages.length})
      </button>
    </div>

    {#if currentImages.length}
      {#key activeTab}
        <div
          class="mt-4 grid grid-cols-1 gap-4 place-items-center sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5"
        >
          {#each currentImages as image (image.image_id)}
            <Lazy fadeOptions={{ duration: 150, delay: 0, offset: 0 }}>
              <div
                slot="placeholder"
                class="rounded-base bg-base-300 animate-pulse w-full h-full"
              ></div>

              <img
                src={image.thumbnail_image_path_url}
                alt={image.image_id}
                onload={() => handleImageLoad(image.image_id)}
                class="rounded-base object-cover"
              />
            </Lazy>
          {/each}
        </div>
      {/key}
    {:else}
      <div class="mt-4">
        <div role="alert" class="alert alert-error alert-soft inline-flex">
          <CircleAlert class="h-6 w-6 shrink-0" />
          <div>
            <h3 class="font-bold">{$i18n.t("translate.thereareerror")}</h3>
            <div class="text-sm">
              <div>{$i18n.t("translate.noimagefound")}</div>
            </div>
          </div>
        </div>
      </div>
    {/if}
  </div>
</div>
