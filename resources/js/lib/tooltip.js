import {
  arrow,
  autoUpdate,
  computePosition,
  flip,
  offset,
  shift,
} from "@floating-ui/dom";

export function tooltip(node, options) {
  const config =
    typeof options === "string" ? { text: options } : (options ?? {});

  let { text = "", root } = config;

  root ??= document.querySelector("main#main-content");

  let cleanup;
  let currentPlacement = "top";

  const tooltip = document.createElement("div");

  tooltip.className =
    "fixed z-50 max-w-64 overflow-visible pointer-events-none rounded-base bg-base-300 px-2 py-1 text-sm text-base-content transition-[opacity,transform]";

  Object.assign(tooltip.style, {
    opacity: "0",
    transform: "translateY(4px)",
  });

  const arrowEl = document.createElement("div");

  arrowEl.className = "absolute h-2 w-2 rotate-45 bg-base-300";

  const content = document.createElement("div");

  content.textContent = text;

  tooltip.append(content, arrowEl);

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

    const { x, y, placement, middlewareData } = await computePosition(
      node,
      tooltip,
      {
        strategy: "fixed",
        placement: "top",
        middleware: [
          offset(12),
          flip({
            boundary: root,
            padding: 8,
          }),
          shift({
            boundary: root,
            padding: 8,
          }),
          arrow({
            element: arrowEl,
          }),
        ],
      },
    );

    currentPlacement = placement.split("-")[0];

    Object.assign(tooltip.style, {
      position: "fixed",
      left: `${x}px`,
      top: `${y}px`,
    });

    const { x: arrowX, y: arrowY } = middlewareData.arrow ?? {};

    const staticSide = {
      top: "bottom",
      right: "left",
      bottom: "top",
      left: "right",
    }[currentPlacement];

    Object.assign(arrowEl.style, {
      left: arrowX != null ? `${arrowX}px` : "",
      top: arrowY != null ? `${arrowY}px` : "",
      right: "",
      bottom: "",
    });

    arrowEl.style[staticSide] = "-4px";
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

      content.textContent = text;

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
