<?php

/**
 * Directory photos are kept as attachments in ONE site's Media Library (the
 * network's main site unless the ucsc_cd_photo_store_site_id filter says
 * otherwise), so pages link to plain upload URLs the CDN serves. Before this,
 * every <img> hit the directoryphoto REST route, which runs PHP and LDAP per
 * photo and drew 429s from CampusPress.
 *
 * The cache never asks LDAP itself. It is handed LDAP entries by the queries
 * the plugin already makes: a block's people query (which asks for jpegphoto
 * when the block shows photos, see CampusDirectoryAPI::getCampusDirData) and
 * the profile query. Photos come from LDAP jpegPhoto only, never
 * campusdirectory photo.php, which does not check public listing (WPM-230).
 *
 * Each LDAP entry handed in decides: no jpegPhoto (the person removed or hid
 * it) deletes the stored photo; an unchanged photo (same hash) is left alone;
 * a new or changed one is written. So a stored photo is as current as the
 * people list it appears in (10 minutes). Photos no entry has confirmed for
 * PRUNE_AFTER, i.e. people no page shows any more, are deleted by a nightly
 * WP-Cron job (pruneNightly), never during a page view.
 */
class CampusDirectoryPhotoCache
{
  const META_UID = '_ucsc_cd_photo_uid';
  const META_HASH = '_ucsc_cd_photo_md5';
  const META_LAST_CHECKED = '_ucsc_cd_photo_checked';

  // An unchanged photo's last-checked time is only rewritten this often, so
  // busy profile pages do not write post meta on every view.
  const TOUCH_EVERY = 5 * 60;
  // A person LDAP returned without a photo is shown the fallback without a
  // REST request while their block's people list is served from cache.
  const NO_PHOTO_FOR = 3600;
  const PRUNE_AFTER = 86400;
  const PRUNE_HOOK = 'ucsc_cd_photo_prune';
  const PRUNE_AT = '03:00';
  const PRUNED_TONIGHT = 'ucsc_cd_photo_pruned';
  const PRUNE_BATCH = 200;
  // Bounds the time one render spends writing photos (~27ms each), so the
  // first view of a big page stays well under CampusPress's 30s cutoff. People
  // over the limit get the REST route, which stores their photo.
  const MAX_WRITES_PER_RENDER = 100;

  const FALLBACK_SRC = '//static.ucsc.edu/images/icon-slug.jpg';

  // Internal marker: the photo is not stored yet, so the page links to the
  // REST route, which stores it on first request.
  const NOT_STORED_YET = null;

  // uid => where the last urls() answer came from: ldap (an LDAP entry was
  // in hand), none (an LDAP entry without a photo), media (stored, no entry
  // in hand), no-photo (a remembered "none"), unknown (no entry, nothing
  // stored), deferred (over the write limit), or store-failed.
  public $photoSources = [];

  private $writesThisRender = 0;

  public static function storeSiteId()
  {
    $siteId = is_multisite() ? (int) get_main_site_id() : (int) get_current_blog_id();
    return (int) apply_filters('ucsc_cd_photo_store_site_id', $siteId);
  }

  public static function noPhotoKey($uid)
  {
    return 'ucsc_cd_nophoto_' . md5($uid);
  }

  /**
   * uid (lowercase) => photo URL, or '' when there is no photo to show (the
   * caller shows the fallback image), or the REST route URL when the photo is
   * not stored yet.
   *
   * @param string[] $uids        People shown.
   * @param array    $ldapEntries Fresh LDAP entries (with jpegphoto when the
   *                              person has one), keyed by uid. Empty when the
   *                              people list came from cache.
   */
  public function urls($uids, $ldapEntries = [])
  {
    $uids = self::normalizeUids($uids);
    if (!$uids) return [];
    $ldapEntries = array_change_key_case((array) $ldapEntries, CASE_LOWER);

    $storeSiteId = self::storeSiteId();
    $mustSwitchSite = is_multisite() && $storeSiteId !== (int) get_current_blog_id();
    if ($mustSwitchSite) switch_to_blog($storeSiteId);
    try {
      $photoUrls = $this->photoUrlsOnStoreSite($uids, $ldapEntries);
    } finally {
      if ($mustSwitchSite) restore_current_blog();
    }

    // Built after switching back: the REST route belongs to the site showing
    // the page, which may not be the store site.
    foreach ($photoUrls as $uid => $photoUrl) {
      if ($photoUrl === self::NOT_STORED_YET) {
        $photoUrls[$uid] = rest_url('ucscgutenbergblocks/v1/directoryphoto/' . rawurlencode($uid));
      }
    }
    return $photoUrls;
  }

  // Lowercase, trimmed, without blanks or repeats, so "JDoe" and "jdoe" are
  // one person with one stored photo.
  private static function normalizeUids($uids)
  {
    $normalized = [];
    foreach ((array) $uids as $uid) {
      $uid = strtolower(trim((string) $uid));
      if ($uid !== '' && !in_array($uid, $normalized, true)) $normalized[] = $uid;
    }
    return $normalized;
  }

  // Runs while switched to the store site.
  private function photoUrlsOnStoreSite($uids, $ldapEntries)
  {
    $now = time();
    $storedPhotos = $this->findStoredPhotos($uids);
    $photoUrls = [];

    foreach ($uids as $uid) {
      $storedPhoto = $storedPhotos[$uid] ?? null;

      if (isset($ldapEntries[$uid])) {
        $photoUrls[$uid] = $this->syncWithLdapEntry($uid, $ldapEntries[$uid], $storedPhoto, $now);
      } elseif ($storedPhoto) {
        $photoUrls[$uid] = $storedPhoto['url'];
        $this->photoSources[$uid] = 'media';
      } elseif (get_site_transient(self::noPhotoKey($uid)) !== false) {
        $photoUrls[$uid] = '';
        $this->photoSources[$uid] = 'no-photo';
      } else {
        $photoUrls[$uid] = self::NOT_STORED_YET;
        $this->photoSources[$uid] = 'unknown';
      }
    }
    return $photoUrls;
  }

  // Bring the stored photo in line with what LDAP says now, and return the
  // URL to show ('' for none, NOT_STORED_YET for the REST route).
  private function syncWithLdapEntry($uid, $ldapEntry, $storedPhoto, $now)
  {
    $photoBytes = $ldapEntry['jpegphoto'][0] ?? '';

    // No photo in LDAP: the person removed or hid it.
    if ($photoBytes === '') {
      if ($storedPhoto) $this->deletePhoto($storedPhoto['id']);
      set_site_transient(self::noPhotoKey($uid), 1, self::NO_PHOTO_FOR);
      $this->photoSources[$uid] = 'none';
      return '';
    }
    delete_site_transient(self::noPhotoKey($uid));

    // Same photo as stored: nothing to write.
    $photoHash = md5($photoBytes);
    if ($storedPhoto && hash_equals($storedPhoto['hash'], $photoHash)) {
      if ($now - $storedPhoto['lastChecked'] >= self::TOUCH_EVERY) {
        update_post_meta($storedPhoto['id'], self::META_LAST_CHECKED, $now);
      }
      $this->photoSources[$uid] = 'ldap';
      return $storedPhoto['url'];
    }

    // New or changed photo. A changed photo is not a hidden one, so the old
    // one may show a little longer if this render is out of writes.
    if ($this->writesThisRender >= self::MAX_WRITES_PER_RENDER) {
      $this->photoSources[$uid] = 'deferred';
      return $storedPhoto ? $storedPhoto['url'] : self::NOT_STORED_YET;
    }
    $this->writesThisRender++;
    $newUrl = $this->storePhoto($uid, $photoBytes, $photoHash, $now);
    if ($newUrl === '') {
      $this->photoSources[$uid] = 'store-failed';
      return '';
    }
    if ($storedPhoto) $this->deletePhoto($storedPhoto['id']);
    $this->photoSources[$uid] = 'ldap';
    return $newUrl;
  }

  // uid => ['id', 'hash', 'lastChecked', 'url'] for the stored photos of
  // $uids. Two renders storing the same new person at once (e.g. two first
  // views of a page) leave duplicates: the newest is kept, the rest deleted.
  private function findStoredPhotos($uids)
  {
    $attachments = get_posts([
      'post_type' => 'attachment',
      'post_status' => 'private',
      'posts_per_page' => -1,
      'orderby' => 'ID',
      'order' => 'ASC',
      'no_found_rows' => true,
      'meta_query' => [['key' => self::META_UID, 'value' => $uids, 'compare' => 'IN']],
    ]);

    $storedPhotos = [];
    foreach ($attachments as $attachment) {
      $uid = strtolower((string) get_post_meta($attachment->ID, self::META_UID, true));
      if (isset($storedPhotos[$uid])) $this->deletePhoto($storedPhotos[$uid]['id']);
      $storedPhotos[$uid] = [
        'id' => $attachment->ID,
        'hash' => (string) get_post_meta($attachment->ID, self::META_HASH, true),
        'lastChecked' => (int) get_post_meta($attachment->ID, self::META_LAST_CHECKED, true),
        'url' => wp_get_attachment_url($attachment->ID),
      ];
    }
    return $storedPhotos;
  }

  // Writes the photo as a new attachment and returns its URL, or '' on
  // failure.
  //  - Private, so the store site's public media REST API and attachment
  //    pages do not list everyone's photo and CruzID; the file itself is
  //    still served directly. Needs no hook, so it holds whether or not the
  //    plugin is active on the store site.
  //  - The file name carries part of the hash, so a changed photo gets a new
  //    URL and the CDN cannot keep serving the old one under the same name.
  //  - No intermediate sizes are generated: pages show the original at 160px.
  private function storePhoto($uid, $photoBytes, $photoHash, $now)
  {
    $fileName = 'cd-photo-' . sanitize_file_name($uid) . '-' . substr($photoHash, 0, 12) . '.jpg';
    $upload = wp_upload_bits($fileName, null, $photoBytes);
    if (!empty($upload['error'])) {
      error_log('CampusDirectory photo store failed for ' . $uid . ': ' . $upload['error']);
      return '';
    }

    $attachmentId = wp_insert_attachment([
      'post_mime_type' => 'image/jpeg',
      'post_title' => 'Campus Directory photo: ' . $uid,
      'post_content' => '',
      'post_status' => 'private',
    ], $upload['file'], 0, true);
    if (is_wp_error($attachmentId) || !$attachmentId) {
      @unlink($upload['file']);
      error_log('CampusDirectory photo attachment failed for ' . $uid);
      return '';
    }

    $imageSize = @getimagesizefromstring($photoBytes);
    wp_update_attachment_metadata($attachmentId, [
      'width' => $imageSize ? $imageSize[0] : 0,
      'height' => $imageSize ? $imageSize[1] : 0,
      'file' => _wp_relative_upload_path($upload['file']),
      'sizes' => [],
    ]);
    update_post_meta($attachmentId, self::META_UID, $uid);
    update_post_meta($attachmentId, self::META_HASH, $photoHash);
    update_post_meta($attachmentId, self::META_LAST_CHECKED, $now);

    return wp_get_attachment_url($attachmentId);
  }

  // Removes the attachment and its file. True on success.
  private function deletePhoto($attachmentId)
  {
    return (bool) wp_delete_attachment($attachmentId, true);
  }

  // Every site with the plugin schedules the nightly prune for 3am. WP-Cron
  // runs it in its own request (or from the host's system cron), so no page
  // view waits on it, and it works whether or not the plugin is active on
  // the store site.
  public static function scheduleNightlyPrune()
  {
    add_action(self::PRUNE_HOOK, [self::class, 'pruneNightly']);
    add_action('init', function () {
      if (wp_next_scheduled(self::PRUNE_HOOK)) return;
      wp_schedule_event(self::nextPruneTime(time()), 'daily', self::PRUNE_HOOK);
    });
  }

  // The next PRUNE_AT in the site's timezone: today's if still ahead,
  // otherwise tomorrow's.
  public static function nextPruneTime($now)
  {
    $next = new DateTime('@' . $now);
    $next->setTimezone(wp_timezone());
    [$hour, $minute] = array_map('intval', explode(':', self::PRUNE_AT));
    $next->setTime($hour, $minute);
    if ($next->getTimestamp() <= $now) $next->modify('+1 day');
    return $next->getTimestamp();
  }

  // On plugin deactivation, for that site.
  public static function unscheduleNightlyPrune()
  {
    wp_clear_scheduled_hook(self::PRUNE_HOOK);
  }

  // Deletes every photo no LDAP entry has confirmed for PRUNE_AFTER. Each
  // site's job fires this nightly; the first one does the work for the whole
  // network and the rest return. Returns how many photos were deleted.
  public static function pruneNightly()
  {
    if (get_site_transient(self::PRUNED_TONIGHT) !== false) return 0;
    set_site_transient(self::PRUNED_TONIGHT, 1, 20 * 3600);

    $storeSiteId = self::storeSiteId();
    $mustSwitchSite = is_multisite() && $storeSiteId !== (int) get_current_blog_id();
    if ($mustSwitchSite) switch_to_blog($storeSiteId);
    try {
      $cache = new self();
      $deletedTotal = 0;
      // A short batch means none are left; a failed delete stops the job
      // rather than retrying the same photo forever.
      do {
        $deletedInBatch = $cache->deleteStaleBatch();
        $deletedTotal += $deletedInBatch;
      } while ($deletedInBatch === self::PRUNE_BATCH);
    } finally {
      if ($mustSwitchSite) restore_current_blog();
    }
    return $deletedTotal;
  }

  // Deletes up to PRUNE_BATCH stale photos and returns how many it deleted.
  private function deleteStaleBatch()
  {
    $staleAttachmentIds = get_posts([
      'post_type' => 'attachment',
      'post_status' => 'private',
      'posts_per_page' => self::PRUNE_BATCH,
      'fields' => 'ids',
      'no_found_rows' => true,
      'meta_query' => [[
        'key' => self::META_LAST_CHECKED,
        'value' => time() - self::PRUNE_AFTER,
        'compare' => '<',
        'type' => 'NUMERIC',
      ]],
    ]);
    $deleted = 0;
    foreach ($staleAttachmentIds as $attachmentId) {
      if ($this->deletePhoto($attachmentId)) $deleted++;
    }
    return $deleted;
  }

  // Keep the cache out of the store site's Media Library screens (only takes
  // effect where this plugin is active).
  public static function hideFromMediaLibrary()
  {
    add_filter('ajax_query_attachments_args', function ($query) {
      $query['meta_query'] = (array) ($query['meta_query'] ?? []);
      $query['meta_query'][] = ['key' => self::META_UID, 'compare' => 'NOT EXISTS'];
      return $query;
    });
    add_action('pre_get_posts', function ($query) {
      if (!is_admin() || !$query->is_main_query()) return;
      global $pagenow;
      if ($pagenow !== 'upload.php') return;
      $metaQuery = (array) $query->get('meta_query');
      $metaQuery[] = ['key' => self::META_UID, 'compare' => 'NOT EXISTS'];
      $query->set('meta_query', $metaQuery);
    });
  }
}
