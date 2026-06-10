<script>
    import { Link, page, useForm } from "@inertiajs/svelte";
    import { ChevronDown, House } from "@lucide/svelte";

    import i18n from "@/js/lib/i18n";
    import { getThemeLabel, setTheme, theme, themes } from "@/js/lib/theme";
    import { title } from "@/js/lib/title";
    import {
        index,
        locale as postLocale,
    } from "@/js/wayfinder/actions/App/Http/Controllers/MainController";

    let { supportedLocales } = $props();

    const currentLocale = $derived(page.props.currentLocale);

    const form = useForm(() => ({
        locale: currentLocale,
    }));

    function changeLocale(locale) {
        form.locale = locale;
        form.post(postLocale());
    }
</script>

<svelte:head>
    <title>{$i18n.t("translate.setting")} - {$title}</title>
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
        <li>{$i18n.t("translate.setting")}</li>
    </ul>
</div>

<div class="space-y-2">
    <h1 class="text-lg font-bold">{$i18n.t("translate.setting")}</h1>

    <section class="space-y-2">
        <h2 class="font-bold">{$i18n.t("translate.appearance")}</h2>

        <div
            class="rounded-base bg-base-300 flex items-center justify-between p-4"
        >
            <div>
                <p class="font-bold text-sm">{$i18n.t("translate.theme")}</p>
                <p class="text-sm opacity-70">
                    {$i18n.t("translate.switchtheme")}
                </p>
            </div>

            <div class="dropdown dropdown-end select-none">
                <button class="btn btn-primary">
                    {getThemeLabel($i18n.t(`translate.${$theme}`))}
                    <ChevronDown class="inline aspect-square h-4 w-4" />
                </button>

                <ul
                    class="dropdown-content menu bg-base-300 rounded-box w-40 space-y-1 p-2"
                >
                    {#each themes as t (t.key)}
                        <li>
                            <button
                                onclick={(e) => {
                                    setTheme(t.key);
                                    e.currentTarget.blur();
                                }}
                                class:btn-disabled={$theme === t.key}
                                disabled={form.processing}
                                type="button"
                            >
                                {$i18n.t(`translate.${t.label}`)}

                                {#if t.default}
                                    ({$i18n
                                        .t("translate.default")
                                        .toLowerCase()})
                                {/if}
                            </button>
                        </li>
                    {/each}
                </ul>
            </div>
        </div>
    </section>

    <section class="space-y-2">
        <h2 class="font-bold">{$i18n.t("translate.system")}</h2>

        <div
            class="rounded-base bg-base-300 flex items-center justify-between p-4"
        >
            <div>
                <p class="font-bold text-sm">{$i18n.t("translate.language")}</p>
                <p class="text-sm opacity-70">
                    {$i18n.t("translate.chooselanguage")}
                </p>
            </div>

            <div class="dropdown dropdown-end">
                <button class="btn btn-primary">
                    {supportedLocales.find((l) => l.code === currentLocale)
                        ?.name}
                    <ChevronDown class="inline aspect-square h-4 w-4" />
                </button>

                <ul
                    class="dropdown-content menu bg-base-300 rounded-box w-40 space-y-1 p-2"
                >
                    {#each supportedLocales as locale (locale.code)}
                        <li>
                            <button
                                disabled={currentLocale === locale.code ||
                                    form.processing}
                                class:btn-disabled={currentLocale ===
                                    locale.code}
                                onclick={(e) => {
                                    changeLocale(locale.code);
                                    e.currentTarget.blur();
                                }}
                                type="button"
                            >
                                {locale.name}
                            </button>
                        </li>
                    {/each}
                </ul>
            </div>
        </div>
    </section>
</div>
