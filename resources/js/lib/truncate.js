export function truncate(str, maxLength = 32, suffix = "...") {
    if (str.length <= maxLength) {
        return str;
    }

    return str.slice(0, maxLength - suffix.length) + suffix;
}
