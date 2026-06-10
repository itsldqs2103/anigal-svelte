<script>
    import { Link, page, router } from "@inertiajs/svelte";
    import {
        ArrowUp,
        ChartBar,
        Copyright,
        House,
        Image,
        LogIn,
        Menu,
        Settings,
        Tag,
        User,
    } from "@lucide/svelte";
    import { onMount } from "svelte";
    import Toastify from "toastify-js";

    import infinityFreeLogoUrl from "@/images/infinityfree-logo.svg";
    import i18n from "@/js/lib/i18n";
    import { initializeTheme } from "@/js/lib/theme";
    import { tooltip } from "@/js/lib/tooltip";
    import { index as imageIndex } from "@/js/wayfinder/actions/App/Http/Controllers/ImageController";
    import {
        dmca,
        index,
        setting,
        stats,
        term,
    } from "@/js/wayfinder/actions/App/Http/Controllers/MainController";
    import { index as tagIndex } from "@/js/wayfinder/actions/App/Http/Controllers/TagController";
    import {
        getLogin,
        profile,
    } from "@/js/wayfinder/actions/App/Http/Controllers/UserController";

    let { children } = $props();

    onMount(() => {
        initializeTheme();
    });

    router.on("flash", (event) => {
        const toast = event.detail.flash.toast;

        if (!toast) return;

        if (toast.success) {
            Toastify({
                text: toast.success,
                className: "rounded-base toastify-success",
                gravity: "bottom",
                position: "center",
                duration: 5000,
                oldestFirst: false,
            }).showToast();
        }

        if (toast.error) {
            Toastify({
                text: toast.error,
                className: "rounded-base toastify-error",
                gravity: "bottom",
                position: "center",
                duration: 5000,
                oldestFirst: false,
            }).showToast();
        }
    });

    function handleImageError(event) {
        const img = event.target;
        if (img instanceof HTMLImageElement) {
            img.classList.add("hidden");
        }
    }

    onMount(() => {
        document.addEventListener("error", handleImageError, true);
        return () => {
            document.removeEventListener("error", handleImageError, true);
        };
    });

    function normalizeUrl(url) {
        const u = new URL(url, window.location.origin);

        const params = [...u.searchParams.entries()].sort(([a], [b]) =>
            a.localeCompare(b),
        );

        const search = new URLSearchParams(params).toString();

        return `${u.pathname}${search ? `?${search}` : ""}`;
    }

    router.on("before", (event) => {
        const visit = event.detail.visit;
        const method = visit.method?.toUpperCase();

        const isPost = method === "POST";
        const hasOnly = Array.isArray(visit.only) && visit.only.length > 0;
        const allowedPartialGets = method === "GET" && hasOnly;

        const isSameUrl =
            normalizeUrl(window.location.href) === normalizeUrl(visit.url);

        if (isSameUrl && !isPost && !allowedPartialGets) {
            event.preventDefault();
        }
    });

    document.addEventListener("dragstart", (e) => {
        if (e.target.closest("a, img")) {
            e.preventDefault();
        }
    });

    const toggleUI = (disabled) => {
        document.querySelectorAll("input, button, a").forEach((el) => {
            if (
                el instanceof HTMLInputElement ||
                el instanceof HTMLButtonElement
            ) {
                if (disabled) {
                    el.dataset.wasDisabled = String(el.disabled);

                    if (!el.disabled) {
                        el.disabled = true;
                    }
                } else {
                    if (el.dataset.wasDisabled === "false") {
                        el.disabled = false;
                    }

                    delete el.dataset.wasDisabled;
                }
            }

            if (el instanceof HTMLAnchorElement) {
                const lockClass = el.classList.contains("btn")
                    ? "btn-disabled"
                    : "pointer-events-none";

                if (disabled) {
                    el.dataset.wasLocked = String(
                        el.classList.contains(lockClass),
                    );

                    if (!el.classList.contains(lockClass)) {
                        el.classList.add(lockClass);
                    }
                } else {
                    if (el.dataset.wasLocked === "false") {
                        el.classList.remove(lockClass);
                    }

                    delete el.dataset.wasLocked;
                }
            }
        });
    };

    router.on("start", () => toggleUI(true));
    router.on("finish", () => toggleUI(false));

    const isAuth = $derived(page.props.currentAuth);
    const currentLocale = $derived(page.props.currentLocale);
    const lastUpdated = $derived(page.props.lastUpdated);
    const currentPHP = $derived(page.props.currentPHP);
    const currentLaravel = $derived(page.props.currentLaravel);

    const topMenuItems = $derived([
        {
            name: $i18n.t("translate.home"),
            path: index(),
            component: "Home",
            icon: House,
        },
        {
            name: $i18n.t("translate.tag"),
            path: tagIndex({
                query: {
                    sort_by: "tag_name",
                    order: "oldest",
                    per_page: 30,
                    starts_with: null,
                },
            }),
            component: "Tag",
            icon: Tag,
        },
        {
            name: $i18n.t("translate.image"),
            path: imageIndex({
                query: {
                    per_page: 30,
                    sort_by: "created_at",
                    order: "latest",
                },
            }),
            component: "Image",
            icon: Image,
        },
        {
            name: $i18n.t("translate.stats"),
            path: stats(),
            component: "Stats",
            icon: ChartBar,
        },
    ]);

    const bottomMenuItems = $derived(
        isAuth
            ? [
                  {
                      name: page.props.currentAuth.username,
                      path: profile({
                          query: { user_id: page.props.currentAuth.user_id },
                      }),
                      component: "User",
                      icon: User,
                  },
                  {
                      name: $i18n.t("translate.setting"),
                      path: setting(),
                      component: "Setting",
                      icon: Settings,
                  },
              ]
            : [
                  {
                      name: $i18n.t("translate.login"),
                      path: getLogin(),
                      component: "User",
                      icon: LogIn,
                  },
                  {
                      name: $i18n.t("translate.setting"),
                      path: setting(),
                      component: "Setting",
                      icon: Settings,
                  },
              ],
    );

    let showScrollTop = $state(false);
    let footerEl = $state(null);
    let isFooterVisible = $state(false);

    $effect(() => {
        if (!footerEl) return;

        const observer = new IntersectionObserver(
            ([entry]) => {
                isFooterVisible = entry.isIntersecting;
            },
            { threshold: 0 },
        );

        observer.observe(footerEl);

        return () => observer.disconnect();
    });

    function handleScroll() {
        showScrollTop = window.scrollY > 128;
    }

    function updateFooterHeight() {
        if (!footerEl) return;

        document.documentElement.style.setProperty(
            "--footer-height",
            `${
                footerEl.offsetHeight +
                (parseFloat(getComputedStyle(footerEl).marginTop) || 0) * 2
            }px`,
        );
    }

    function scrollToTop() {
        window.scrollTo({ top: 0, behavior: "smooth" });
    }

    $effect(() => {
        handleScroll();
        updateFooterHeight();

        window.addEventListener("scroll", handleScroll);

        return () => {
            window.removeEventListener("scroll", handleScroll);
        };
    });

    $effect(() => {
        if (!footerEl) return;

        const resizeObserver = new ResizeObserver(() => {
            updateFooterHeight();
        });

        resizeObserver.observe(footerEl);

        return () => {
            resizeObserver.disconnect();
        };
    });

    $effect(() => {
        if (currentLocale && $i18n.language !== currentLocale) {
            $i18n.changeLanguage(currentLocale).then(() => {
                document.documentElement.classList.remove(
                    "invisible",
                    "pointer-events-none",
                );
            });
        } else {
            document.documentElement.classList.remove(
                "invisible",
                "pointer-events-none",
            );
        }
    });

    onMount(() => {
        updateFooterHeight();

        let saved = localStorage.getItem("currentTheme");
        if (saved === null) {
            saved = "dark";
            localStorage.setItem("currentTheme", saved);
        }

        let stored = localStorage.getItem("isUserEdit");
        if (stored === null) {
            localStorage.setItem("isUserEdit", "false");
        }
    });
</script>

<div class="drawer lg:drawer-open">
    <input id="my-drawer" type="checkbox" class="drawer-toggle" />

    <div
        class="drawer-content min-h-screen overflow-hidden"
        id="nprogress-container"
    >
        <div class="navbar bg-base-300 w-full gap-4 lg:hidden">
            <div class="flex-none">
                <label for="my-drawer" class="btn btn-square btn-ghost">
                    <Menu class="inline h-4 w-4 aspect-square" />
                </label>
            </div>
            <Link
                href={index()}
                class="text-2xl font-bold uppercase transition-opacity hover:opacity-70 focus-visible:opacity-70 focus-visible:outline-0 focus-visible:outline-transparent flex-1"
            >
                Ani<span class="text-primary">Gal</span>
            </Link>
        </div>

        <main class="p-4" id="main-content">
            <section
                id="content"
                class="min-h-[calc(100vh-var(--footer-height)-1rem)]"
            >
                {@render children()}
            </section>
            <footer
                bind:this={footerEl}
                class="rounded-base bg-base-300 text-base-content mt-4"
            >
                <div class="footer sm:footer-horizontal p-4">
                    <nav>
                        <h6
                            class="footer-title mb-0 font-bold normal-case opacity-100"
                        >
                            {$i18n.t("translate.navigation")}
                        </h6>
                        <Link
                            href={index()}
                            class="text-sm text-base-content hover:text-primary focus:text-primary focus-visible:text-primary cursor-pointer no-underline transition-[color] focus:outline-0 focus:outline-transparent focus-visible:outline-0 focus-visible:outline-transparent"
                            >{$i18n.t("translate.home")}</Link
                        >
                        <Link
                            href={tagIndex({
                                query: {
                                    sort_by: "tag_name",
                                    order: "oldest",
                                    per_page: 30,
                                    starts_with: null,
                                },
                            })}
                            class="text-sm text-base-content hover:text-primary focus:text-primary focus-visible:text-primary cursor-pointer no-underline transition-[color] focus:outline-0 focus:outline-transparent focus-visible:outline-0 focus-visible:outline-transparent"
                            >{$i18n.t("translate.tag")}</Link
                        >
                        <Link
                            href={imageIndex({
                                query: {
                                    sort_by: "created_at",
                                    order: "latest",
                                    per_page: 30,
                                },
                            })}
                            class="text-sm text-base-content hover:text-primary focus:text-primary focus-visible:text-primary cursor-pointer no-underline transition-[color] focus:outline-0 focus:outline-transparent focus-visible:outline-0 focus-visible:outline-transparent"
                            >{$i18n.t("translate.image")}</Link
                        >
                        <Link
                            href={stats()}
                            class="text-sm text-base-content hover:text-primary focus:text-primary focus-visible:text-primary cursor-pointer no-underline transition-[color] focus:outline-0 focus:outline-transparent focus-visible:outline-0 focus-visible:outline-transparent"
                            >{$i18n.t("translate.stats")}</Link
                        >
                        <Link
                            href={setting()}
                            class="text-sm text-base-content hover:text-primary focus:text-primary focus-visible:text-primary cursor-pointer no-underline transition-[color] focus:outline-0 focus:outline-transparent focus-visible:outline-0 focus-visible:outline-transparent"
                            >{$i18n.t("translate.setting")}</Link
                        >
                    </nav>
                    <nav>
                        <h6
                            class="footer-title mb-0 font-bold normal-case opacity-100"
                        >
                            {$i18n.t("translate.account")}
                        </h6>
                        {#if isAuth}
                            <Link
                                href={profile({
                                    query: {
                                        user_id: page.props.currentAuth.user_id,
                                    },
                                })}
                                class="text-sm text-base-content hover:text-primary focus:text-primary focus-visible:text-primary cursor-pointer no-underline transition-[color] focus:outline-0 focus:outline-transparent focus-visible:outline-0 focus-visible:outline-transparent"
                                >{page.props.currentAuth.username}</Link
                            >
                        {:else}
                            <Link
                                href={getLogin()}
                                class="text-sm text-base-content hover:text-primary focus:text-primary focus-visible:text-primary cursor-pointer no-underline transition-[color] focus:outline-0 focus:outline-transparent focus-visible:outline-0 focus-visible:outline-transparent"
                                >{$i18n.t("translate.login")}</Link
                            >
                        {/if}
                    </nav>
                    <nav>
                        <h6
                            class="footer-title mb-0 font-bold normal-case opacity-100"
                        >
                            {$i18n.t("translate.legal")}
                        </h6>
                        <Link
                            href={dmca()}
                            class="text-sm text-base-content hover:text-primary focus:text-primary focus-visible:text-primary cursor-pointer no-underline transition-[color] focus:outline-0 focus:outline-transparent focus-visible:outline-0 focus-visible:outline-transparent"
                            >{$i18n.t("translate.dmca")}</Link
                        >
                        <Link
                            href={term()}
                            class="text-sm text-base-content hover:text-primary focus:text-primary focus-visible:text-primary cursor-pointer no-underline transition-[color] focus:outline-0 focus:outline-transparent focus-visible:outline-0 focus-visible:outline-transparent"
                            >{$i18n.t("translate.term")}</Link
                        >
                    </nav>
                </div>

                <div
                    class="footer flex md:justify-end gap-4 px-4 pt-0 pb-4 opacity-70"
                >
                    {$i18n.t("translate.currentlaravel") +
                        ": " +
                        currentLaravel}. {$i18n.t("translate.currentphp") +
                        ": " +
                        currentPHP}
                </div>

                <div class="footer flex md:justify-end px-4 pt-0 pb-4">
                    <div>
                        <a
                            href="https://www.infinityfree.com/"
                            target="_blank"
                            class="transition-opacity hover:opacity-70 focus:opacity-70 focus:outline-0 focus:outline-transparent focus-visible:opacity-70 focus-visible:outline-0 focus-visible:outline-transparent"
                            rel="noopener noreferrer"
                        >
                            <img
                                src={infinityFreeLogoUrl}
                                alt="InfinityFree's logo"
                                class="h-5 w-auto inline"
                            />
                        </a>
                    </div>
                </div>

                <div class="footer px-4 pt-0 pb-4">
                    <div
                        class="flex w-full flex-col opacity-70 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <div class="flex items-center gap-1">
                            <Copyright class="inline h-4 w-4" />
                            2026 AniGal. {$i18n.t(
                                "translate.allrightsreserved",
                            )}
                        </div>
                        <div>
                            {$i18n.t("translate.lastupdated") +
                                ": " +
                                lastUpdated}
                        </div>
                    </div>
                </div>
            </footer>
        </main>

        <div
            use:tooltip={$i18n.t("translate.gototop")}
            class={`fixed right-4 z-50 transition-[opacity,visibility,bottom] ${showScrollTop ? "visible opacity-100" : "pointer-events-none invisible opacity-0"} ${showScrollTop && isFooterVisible ? "bottom-[calc(var(--footer-height))]" : "bottom-4"}`}
        >
            <button
                onclick={scrollToTop}
                class="btn btn-primary btn-sm btn-square"
            >
                <ArrowUp class="inline h-4 w-4 aspect-square" />
            </button>
        </div>
    </div>

    <div class="drawer-side">
        <label for="my-drawer" class="drawer-overlay"></label>

        <aside
            class="menu text-base-content bg-base-300 flex min-h-full w-64 flex-col p-4"
        >
            <div class="mb-4">
                <Link
                    href={index()}
                    class="text-2xl font-bold uppercase transition-opacity hover:opacity-70 focus-visible:opacity-70 focus-visible:outline-0 focus-visible:outline-transparent"
                >
                    Ani<span class="text-primary">Gal</span>
                </Link>
            </div>

            <ul class="space-y-2">
                {#each topMenuItems as item (item.path)}
                    <li>
                        <Link
                            href={item.path}
                            class={`rounded-base gap-1 ${
                                page.component.startsWith(item.component)
                                    ? "bg-primary text-primary-content"
                                    : ""
                            }`}
                        >
                            <item.icon size={20} />
                            {item.name}
                        </Link>
                    </li>
                {/each}
            </ul>

            <ul class="mt-auto space-y-2">
                {#each bottomMenuItems as item (item.path)}
                    <li>
                        <Link
                            href={item.path}
                            class={`rounded-base gap-1 ${
                                page.component.startsWith(item.component)
                                    ? "bg-primary text-primary-content"
                                    : ""
                            }`}
                        >
                            <item.icon size={24} />
                            {item.name}
                        </Link>
                    </li>
                {/each}
            </ul>
        </aside>
    </div>
</div>
