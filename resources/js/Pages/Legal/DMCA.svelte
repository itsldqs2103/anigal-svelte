<script>
  import { Link } from "@inertiajs/svelte";
  import { House } from "@lucide/svelte";

  import i18n from "@/js/lib/i18n";
  import { title } from "@/js/lib/title";
  import { index } from "@/js/wayfinder/actions/App/Http/Controllers/MainController";

  let { lastUpdated } = $props();
</script>

<svelte:head>
  <title>{$i18n.t("translate.dmca")} - {$title}</title>
</svelte:head>

<div class="breadcrumbs inline-flex bg-base-300 rounded-base mb-4 p-2">
  <ul>
    <li>
      <Link
        href={index()}
        class="text-base-content hover:text-primary focus:text-primary focus-visible:text-primary cursor-pointer no-underline transition-[color] focus:outline-0 focus:outline-transparent focus-visible:outline-0 focus-visible:outline-transparent gap-1"
      >
        <House class="inline h-4 w-4 aspect-square" />
        {$i18n.t("translate.home")}
      </Link>
    </li>
    <li>{$i18n.t("translate.dmca")}</li>
  </ul>
</div>

<div class="card bg-base-200">
  <div class="card-body">
    <div>
      <h1 class="card-title text-xl">
        {$i18n.t("translate.dmca")}
      </h1>
      <p class="text-sm opacity-70">
        {$i18n.t("translate.lastupdated")}: {lastUpdated}
      </p>
    </div>

    <div class="divider my-0"></div>

    <div class="space-y-4">
      <section>
        <h2 class="text-lg font-bold">
          {$i18n.t("translate.dmca1")}
        </h2>
        <p>{$i18n.t("translate.dmca1desc")}</p>
      </section>

      <section>
        <h2 class="text-lg font-bold">
          {$i18n.t("translate.dmca2")}
        </h2>
        <p>{$i18n.t("translate.dmca2desc")}</p>
      </section>

      <section>
        <h2 class="text-lg font-bold">
          {$i18n.t("translate.dmca3")}
        </h2>
        <p>{$i18n.t("translate.dmca3desc")}</p>
      </section>

      <section>
        <h2 class="text-lg font-bold">
          {$i18n.t("translate.dmca4")}
        </h2>
        <p>{$i18n.t("translate.dmca4desc")}</p>
      </section>

      <section>
        <h2 class="text-lg font-bold">
          {$i18n.t("translate.dmca5")}
        </h2>
        <p>{$i18n.t("translate.dmca5desc")}</p>
      </section>

      <section>
        <h2 class="text-lg font-bold">
          {$i18n.t("translate.dmca6")}
        </h2>
        <p>
          {$i18n.t("translate.dmca6desc")}
          <a
            class="underline font-bold text-base-content hover:text-primary focus:text-primary focus-visible:text-primary cursor-pointer transition-[color] focus:outline-0 focus:outline-transparent focus-visible:outline-0 focus-visible:outline-transparent"
            href="mailto:itsldqs2103@gmail.com"
          >
            itsldqs2103@gmail.com
          </a>
        </p>
      </section>
    </div>
  </div>
</div>
