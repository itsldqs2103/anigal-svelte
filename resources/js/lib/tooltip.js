import {
  autoUpdate,
  computePosition,
  flip,
  offset,
  shift,
} from "@floating-ui/dom";

export function tooltip(node, options) {
  const config =
    typeof options === "string" ? { text: options } : (options ?? {});

  let { text = "", root, placement = "top" } = config;

  root ??= document.querySelector("main#main-content");

  let cleanup;
  let currentPlacement = placement.split("-")[0];

  const tooltip = document.createElement("div");

  tooltip.className =
    "fixed z-50 max-w-64 pointer-events-none rounded-base bg-base-300 px-2 py-1 text-xs text-base-content transition-[opacity,transform]";

  Object.assign(tooltip.style, {
    opacity: "0",
    transform: "translateY(4px)",
  });

  tooltip.textContent = text;

  function hiddenTransform() {
    switch (currentPlacement) {
      case "top":
        return "translateY(4px)";
      case "bottom":
        return "translateY(-4px)";
      case "left":
        return "translateX(4px)";
      case "right":
        return "translateX(-4px)";
      default:
        return "translateY(4px)";
    }
  }

  async function updatePosition() {
    if (!tooltip.isConnected) return;

    const {
      x,
      y,
      placement: resolvedPlacement,
    } = await computePosition(node, tooltip, {
      strategy: "fixed",
      placement,
      middleware: [
        offset(6),
        flip({
          boundary: root,
          padding: 4,
        }),
        shift({
          boundary: root,
          padding: 4,
        }),
      ],
    });

    currentPlacement = resolvedPlacement.split("-")[0];

    Object.assign(tooltip.style, {
      position: "fixed",
      left: `${x}px`,
      top: `${y}px`,
    });
  }

  function show() {
    if (!text?.trim()) return;

    if (!tooltip.isConnected) {
      root.appendChild(tooltip);

      cleanup = autoUpdate(node, tooltip, updatePosition);

      updatePosition();
    }

    requestAnimationFrame(() => {
      tooltip.style.opacity = "1";
      tooltip.style.transform = "translate(0, 0)";
    });
  }

  function hide() {
    if (!tooltip.isConnected) return;

    tooltip.style.opacity = "0";
    tooltip.style.transform = hiddenTransform();

    const currentCleanup = cleanup;
    cleanup = null;

    const remove = (event) => {
      if (event.target !== tooltip) return;

      currentCleanup?.();
      tooltip.remove();
      tooltip.removeEventListener("transitionend", remove);
    };

    tooltip.addEventListener("transitionend", remove);
  }

  node.addEventListener("mouseenter", show);
  node.addEventListener("mouseleave", hide);
  node.addEventListener("focus", show);
  node.addEventListener("blur", hide);

  return {
    update(newOptions) {
      const config =
        typeof newOptions === "string"
          ? { text: newOptions }
          : (newOptions ?? {});

      text = config.text ?? text;
      root = config.root ?? root;
      placement = config.placement ?? placement;

      tooltip.textContent = text;

      if (!text?.trim()) {
        hide();
        return;
      }

      if (tooltip.isConnected) {
        updatePosition();
      }
    },

    destroy() {
      cleanup?.();
      cleanup = null;

      tooltip.remove();

      node.removeEventListener("mouseenter", show);
      node.removeEventListener("mouseleave", hide);
      node.removeEventListener("focus", show);
      node.removeEventListener("blur", hide);
    },
  };
}
