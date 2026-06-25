import { render, screen, act, fireEvent } from '@testing-library/react';
import '@testing-library/jest-dom';

// Use the real @wordpress/element (it backs the hooks AND the JSX
// createElement pragma the wp-scripts Babel preset compiles to). Only the
// imported-but-unused SelectControl is stubbed, matching the sibling suites.
jest.mock('@wordpress/components', () => ({ SelectControl: () => null }), {
  virtual: true,
});

// Capture the block registration config.
let registeredBlock = null;
global.wp = {
  blocks: {
    registerBlockType: (name, config) => {
      registeredBlock = { name, ...config };
    },
  },
};

// The edit component fetches the site list and the post-type list on mount.
// Respond per-endpoint so the site <select> actually has selectable options.
global.fetch = jest.fn((url) => {
  const body = url.includes('/sites')
    ? JSON.stringify([
        { domain: 'a.ucsc.edu', blog_id: '1' },
        { domain: 'b.ucsc.edu', blog_id: '5' },
      ])
    : JSON.stringify({ post: 'post', page: 'page' });
  return Promise.resolve({ text: () => Promise.resolve(body) });
});

const ContentSharer = require('../ContentSharer').default;
ContentSharer();

describe('ContentSharer block', () => {
  afterEach(() => {
    jest.clearAllMocks();
  });

  describe('registration', () => {
    it('registers under the contentsharer block name', () => {
      expect(registeredBlock.name).toBe('ucscblocks/contentsharer');
    });

    it('has the correct title', () => {
      expect(registeredBlock.title).toBe('Content Sharer');
    });

    it('has the correct icon', () => {
      expect(registeredBlock.icon).toBe('admin-site-alt3');
    });

    it('is in the common category', () => {
      expect(registeredBlock.category).toBe('common');
    });

    it('declares the siteid and postType attributes', () => {
      expect(registeredBlock.attributes).toEqual({
        siteid: { type: 'string' },
        postType: { type: 'string' },
      });
    });
  });

  describe('save', () => {
    it('returns null (server-rendered)', () => {
      expect(registeredBlock.save({})).toBeNull();
    });
  });

  describe('edit', () => {
    const Edit = registeredBlock.edit;

    const renderEdit = async (attributes, setAttributes = jest.fn()) => {
      await act(async () => {
        render(<Edit setAttributes={setAttributes} attributes={attributes} />);
      });
      return setAttributes;
    };

    it('renders the site and post-type selectors', async () => {
      await renderEdit({ siteid: '1', postType: 'post' });
      expect(screen.getByText('Site List')).toBeInTheDocument();
      expect(screen.getByText('Post Types')).toBeInTheDocument();
    });

    it('fetches the site list on mount', async () => {
      await renderEdit({ siteid: '1', postType: 'post' });
      expect(global.fetch).toHaveBeenCalledWith(
        '/wp-json/ucscgutenbergblocks/v1/sites'
      );
    });

    it('fetches post types for the selected site on mount', async () => {
      await renderEdit({ siteid: '3', postType: 'post' });
      expect(global.fetch).toHaveBeenCalledWith(
        '/wp-json/ucscgutenbergblocks/v1/posttypes?siteid=3'
      );
    });

    it('seeds default attributes when siteid and postType are undefined', async () => {
      const setAttributes = await renderEdit({});
      expect(setAttributes).toHaveBeenCalledWith({ siteid: 1 });
      expect(setAttributes).toHaveBeenCalledWith({ postType: '' });
    });

    it('updates siteid and refetches post types when a site is chosen', async () => {
      const setAttributes = await renderEdit({ siteid: '1', postType: 'post' });
      fireEvent.change(screen.getAllByRole('combobox')[0], {
        target: { value: '5' },
      });
      expect(setAttributes).toHaveBeenCalledWith({ siteid: '5' });
      expect(global.fetch).toHaveBeenCalledWith(
        '/wp-json/ucscgutenbergblocks/v1/posttypes?siteid=5'
      );
    });
  });
});
