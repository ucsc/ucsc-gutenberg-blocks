// WPM-207: GET a plugin REST endpoint as JSON. Rejects on a network error, a
// non-OK status (a WP REST error object, an HTML rate-limit page) or a body
// that is not JSON, so callers can show an error instead of hanging.
export function fetchJson(url) {
  return fetch(url)
    .then((res) => {
      if (!res.ok) throw new Error(`${url} returned HTTP ${res.status}`);
      return res.text();
    })
    .then((text) => JSON.parse(text));
}

// Dropdown options must be a non-empty array of { label, value }.
export function fetchOptions(url) {
  return fetchJson(url).then((options) => {
    if (!Array.isArray(options) || !options.length) throw new Error(`${url} returned no options`);
    return options;
  });
}
