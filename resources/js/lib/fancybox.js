import { Fancybox } from "@fancyapps/ui";

import { tooltip } from "@/js/lib/tooltip";

const tooltipMap = {
    "Zoom in": "zoomin",
    "Zoom out": "zoomout",
    "Toggle zoom level": "togglezoom",
    "Rotate counterclockwise": "rotateccw",
    "Rotate clockwise": "rotatecw",
    "Flip horizontally": "fliphorizontally",
    "Flip vertically": "flipvertically",
    Reset: "reset",
    Download: "download",
    "Toggle full-screen mode": "togglefullscreen",
    Close: "close",
};

function decorateToolbar(i18n) {
    const buttons = document.querySelectorAll(
        "div.f-carousel__toolbar__column > button.f-button",
    );

    buttons.forEach((button) => {
        let text = button.getAttribute("title");

        text = tooltipMap[text ?? ""] || text;
        text = i18n.t(`translate.${text}`);

        button.removeAttribute("title");

        tooltip(button, {
            text,
            root: document.querySelector(".fancybox__container"),
        });
    });
}

export function createFancyboxOptions(i18n) {
    return {
        dragToClose: false,
        keyboard: {
            Escape: "close",
            Delete: null,
            Backspace: null,
            PageUp: null,
            PageDown: null,
            ArrowUp: null,
            ArrowDown: null,
            ArrowRight: null,
            ArrowLeft: null,
        },
        Carousel: {
            gestures: false,
            Zoomable: {
                Panzoom: {
                    protected: true,
                },
            },
            Toolbar: {
                display: {
                    middle: [
                        "zoomIn",
                        "zoomOut",
                        "toggle1to1",
                        "rotateCCW",
                        "rotateCW",
                        "flipX",
                        "flipY",
                        "reset",
                    ],
                    right: ["download", "fullscreen", "close"],
                },
            },
        },
        on: {
            ready: () => {
                requestAnimationFrame(() => {
                    decorateToolbar(i18n);
                });
            },
        },
    };
}

export function showImage(previewUrl, url, filename, i18n) {
    Fancybox.show(
        [
            {
                src: previewUrl,
                type: "image",
                downloadSrc: url,
                downloadFilename: filename,
            },
        ],
        createFancyboxOptions(i18n),
    );
}
