export const sidebarState = $state({
  collapsed: localStorage.getItem("sidebarCollapsed") === "true",
});

export function toggleSidebar() {
  sidebarState.collapsed = !sidebarState.collapsed;

  localStorage.setItem("sidebarCollapsed", String(sidebarState.collapsed));
}
