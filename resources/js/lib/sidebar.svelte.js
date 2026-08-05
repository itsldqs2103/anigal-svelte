export const sidebarCollapseState = $state({
  collapsed: localStorage.getItem("sidebarCollapsed") === "true",
});

export function collapseSidebar() {
  sidebarCollapseState.collapsed = !sidebarCollapseState.collapsed;

  localStorage.setItem(
    "sidebarCollapsed",
    String(sidebarCollapseState.collapsed),
  );
}
