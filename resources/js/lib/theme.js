import { writable } from "svelte/store";

export const themes = [
  {
    key: "dark",
    label: "dark",
    color: "#15191e",
    default: true,
  },
  {
    key: "dracula",
    label: "dracula",
    color: "#ff79c6",
  },
  {
    key: "light",
    label: "light",
    color: "#eeeeee",
  },
  {
    key: "night",
    label: "night",
    color: "#0f172a",
  },
];

export const theme = writable("dark");

export function applyTheme(themeKey) {
  document.documentElement.setAttribute("data-theme", themeKey);
}

export function initializeTheme() {
  const saved = localStorage.getItem("currentTheme") || "dark";

  theme.set(saved);
  applyTheme(saved);
}

export function setTheme(themeKey) {
  localStorage.setItem("currentTheme", themeKey);

  theme.set(themeKey);
  applyTheme(themeKey);
}

export function getThemeLabel(themeKey) {
  const theme = themes.find((t) => t.key === themeKey);

  if (!theme) return themeKey;

  return theme.label;
}
