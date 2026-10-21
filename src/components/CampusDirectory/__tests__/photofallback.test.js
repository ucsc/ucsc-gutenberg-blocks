/**
 * Tests for the Campus Directory photo fallback: when no directory photo has
 * loaded after the stall timeout, stalled photos are swapped to the fallback
 * image so an unreachable photo source does not keep the page loading.
 */

const PHOTO_URL = 'https://example.test/wp-json/ucscgutenbergblocks/v1/directoryphoto/';
const FALLBACK_SRC = '//static.ucsc.edu/images/icon-slug.jpg';

// jsdom never loads images, so set each photo's load state explicitly.
function addPhoto(uid, { complete = false, naturalWidth = 0 } = {}) {
  const img = document.createElement('img');
  img.setAttribute('src', PHOTO_URL + uid);
  img.onerror = jest.fn();
  Object.defineProperty(img, 'complete', { configurable: true, get: () => complete });
  Object.defineProperty(img, 'naturalWidth', { configurable: true, get: () => naturalWidth });
  document.body.appendChild(img);
  return img;
}

function loadScript() {
  jest.isolateModules(() => {
    require('../photofallback');
  });
}

describe('Campus Directory photo fallback', () => {
  beforeEach(() => {
    jest.useFakeTimers();
    document.body.innerHTML = '';
  });

  afterEach(() => {
    jest.useRealTimers();
  });

  it('swaps every stalled photo to the fallback when none have loaded', () => {
    const first = addPhoto('jdoe');
    const second = addPhoto('asmith');
    loadScript();

    jest.advanceTimersByTime(4000);

    expect(first.getAttribute('src')).toBe(FALLBACK_SRC);
    expect(second.getAttribute('src')).toBe(FALLBACK_SRC);
    expect(first.onerror).toBeNull();
  });

  it('leaves photos alone before the stall timeout', () => {
    const photo = addPhoto('jdoe');
    loadScript();

    jest.advanceTimersByTime(3999);

    expect(photo.getAttribute('src')).toBe(PHOTO_URL + 'jdoe');
  });

  it('leaves slow photos alone when another photo loaded (host is up)', () => {
    addPhoto('jdoe', { complete: true, naturalWidth: 120 });
    const slow = addPhoto('asmith');
    loadScript();

    jest.advanceTimersByTime(4000);

    expect(slow.getAttribute('src')).toBe(PHOTO_URL + 'asmith');
  });

  it('does not touch photos that already finished (e.g. already fell back)', () => {
    const failed = addPhoto('jdoe', { complete: true, naturalWidth: 0 });
    loadScript();

    jest.advanceTimersByTime(4000);

    expect(failed.getAttribute('src')).toBe(PHOTO_URL + 'jdoe');
  });

  it('does not treat a failed photo as proof the host is up', () => {
    addPhoto('jdoe', { complete: true, naturalWidth: 0 });
    const stalled = addPhoto('asmith');
    loadScript();

    jest.advanceTimersByTime(4000);

    expect(stalled.getAttribute('src')).toBe(FALLBACK_SRC);
  });

  it('ignores images that are not directory photos', () => {
    const other = document.createElement('img');
    other.setAttribute('src', 'https://example.com/logo.png');
    document.body.appendChild(other);
    loadScript();

    jest.advanceTimersByTime(4000);

    expect(other.getAttribute('src')).toBe('https://example.com/logo.png');
  });
});
