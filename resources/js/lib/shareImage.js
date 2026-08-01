export async function share(data) {
  if (!navigator.share || !data?.url) {
    return false;
  }

  try {
    await navigator.share(data);
    return true;
  } catch {
    return false;
  }
}

export function shareImage(image) {
  return share({
    url: image?.image_source,
  });
}
