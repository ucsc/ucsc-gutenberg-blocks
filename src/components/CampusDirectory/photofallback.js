// Directory photos are stored files (img.cd-photo), but a photo can still
// come from the directoryphoto REST route (people over a render's LDAP limit,
// or HTML cached from before the photo cache), and that request can hang for
// up to the LDAP timeouts before its onerror fallback fires, keeping the page
// "loading". If no directory photo has loaded after a few seconds, treat the
// photo source as down and swap the stalled photos to the fallback image. If
// any photo did load, the source is up and slow photos are left alone.
const PHOTO_SELECTOR = 'img.cd-photo, img[src*="/ucscgutenbergblocks/v1/directoryphoto/"]';
const FALLBACK_SRC = '//static.ucsc.edu/images/icon-slug.jpg';
const STALL_TIMEOUT_MS = 4000;

function swapStalledPhotos() {
  const photos = Array.from(document.querySelectorAll(PHOTO_SELECTOR));
  const anyLoaded = photos.some((img) => img.complete && img.naturalWidth > 0);
  if (anyLoaded) return;

  photos.forEach((img) => {
    if (img.complete) return;
    img.onerror = null;
    img.src = FALLBACK_SRC;
  });
}

function startPhotoFallbackTimer() {
  setTimeout(swapStalledPhotos, STALL_TIMEOUT_MS);
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', startPhotoFallbackTimer);
} else {
  startPhotoFallbackTimer();
}
