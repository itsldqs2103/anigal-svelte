<script>
  import { Link } from "@inertiajs/svelte";
  import { House } from "@lucide/svelte";

  import i18n from "@/js/lib/i18n";
  import { title } from "@/js/lib/title";
  import { index } from "@/js/wayfinder/actions/App/Http/Controllers/MainController";

  let { lastUpdated } = $props();
</script>

<svelte:head>
  <title>{$i18n.t("translate.term")} - {$title}</title>
</svelte:head>

<div class="breadcrumbs bg-base-300 rounded-base mb-4 inline-flex p-2">
  <ul>
    <li>
      <Link
        href={index()}
        class="text-base-content hover:text-primary focus:text-primary focus-visible:text-primary cursor-pointer gap-1 no-underline transition-[color] focus:outline-0 focus:outline-transparent focus-visible:outline-0 focus-visible:outline-transparent"
      >
        <House class="inline aspect-square h-4 w-4" />{$i18n.t(
          "translate.home",
        )}
      </Link>
    </li>
    <li>{$i18n.t("translate.term")}</li>
  </ul>
</div>

<div class="card bg-base-200">
  <div class="card-body">
    <div>
      <h1 class="card-title text-xl">{$i18n.t("translate.term")}</h1>
      <p class="text-sm opacity-70">
        {$i18n.t("translate.lastupdated")}: {lastUpdated}
      </p>
    </div>

    <div class="divider my-0"></div>

    <div class="space-y-4">
      <section>
        <h2 class="text-lg font-bold">{$i18n.t("translate.term1")}</h2>
        <p>
          {$i18n.t("translate.term1desc", {
            appname: import.meta.env.VITE_APP_NAME,
          })}
        </p>
      </section>

      <section>
        <h2 class="text-lg font-bold">{$i18n.t("translate.term2")}</h2>
        <p>
          {$i18n.t("translate.term2desc", {
            appname: import.meta.env.VITE_APP_NAME,
          })}
        </p>
      </section>

      <section>
        <h2 class="text-lg font-bold">{$i18n.t("translate.term3")}</h2>
        <p>
          {$i18n.t("translate.term3desc", {
            appname: import.meta.env.VITE_APP_NAME,
          })}
        </p>
      </section>

      <section>
        <h2 class="text-lg font-bold">{$i18n.t("translate.term4")}</h2>
        <p>
          {$i18n.t("translate.term4desc", {
            appname: import.meta.env.VITE_APP_NAME,
          })}
        </p>
      </section>

      <section>
        <h2 class="text-lg font-bold">{$i18n.t("translate.term5")}</h2>
        <p>
          {$i18n.t("translate.term5desc", {
            appname: import.meta.env.VITE_APP_NAME,
          })}
        </p>
      </section>

      <section>
        <h2 class="text-lg font-bold">{$i18n.t("translate.term6")}</h2>
        <p>
          {$i18n.t("translate.term6desc")}
        </p>
      </section>

      <section>
        <h2 class="text-lg font-bold">{$i18n.t("translate.term7")}</h2>
        <p>
          {$i18n.t("translate.term7desc", {
            appname: import.meta.env.VITE_APP_NAME,
          })}
        </p>
      </section>

      <section>
        <h2 class="text-lg font-bold">{$i18n.t("translate.term8")}</h2>
        <p>
          {$i18n.t("translate.term8desc")}
        </p>
      </section>

      <section>
        <h2 class="text-lg font-bold">{$i18n.t("translate.term9")}</h2>
        <p>
          {$i18n.t("translate.term9desc")}
          <a
            class="text-base-content hover:text-primary focus:text-primary focus-visible:text-primary cursor-pointer font-bold underline transition-[color] focus:outline-0 focus:outline-transparent focus-visible:outline-0 focus-visible:outline-transparent"
            href="mailto:itsldqs2103@gmail.com">itsldqs2103@gmail.com</a
          >
        </p>
      </section>
    </div>
  </div>
</div>
