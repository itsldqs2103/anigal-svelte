export function persistedState(key, initialValue) {
  const stored = localStorage.getItem(key);

  const state = $state({
    value: stored === null ? initialValue : JSON.parse(stored),
  });

  return {
    get value() {
      return state.value;
    },

    set value(v) {
      state.value = v;
      localStorage.setItem(key, JSON.stringify(v));
    },
  };
}
