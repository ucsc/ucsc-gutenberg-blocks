import { render, screen, fireEvent } from '@testing-library/react';
import '@testing-library/jest-dom';

// Capture the block registration config.
let registeredBlock = null;
global.wp = {
  blocks: {
    registerBlockType: (name, config) => {
      registeredBlock = { name, ...config };
    },
  },
};

// Import after the registry shim is in place.
const FeedbackForm = require('../FeedbackForm').default;
FeedbackForm();

describe('FeedbackForm block', () => {
  afterEach(() => {
    jest.clearAllMocks();
  });

  describe('registration', () => {
    it('registers under the feedback block name', () => {
      expect(registeredBlock.name).toBe('ucscblocks/feedback');
    });

    it('has the correct title', () => {
      expect(registeredBlock.title).toBe('Feedback Form');
    });

    it('has the correct icon', () => {
      expect(registeredBlock.icon).toBe('smiley');
    });

    it('is in the common category', () => {
      expect(registeredBlock.category).toBe('common');
    });

    it('declares every configurable label/placeholder attribute', () => {
      expect(registeredBlock.attributes).toEqual({
        name: { type: 'string' },
        namePlaceholder: { type: 'string' },
        email: { type: 'string' },
        emailPlaceholder: { type: 'string' },
        affiliation: { type: 'string' },
        affiliationPlaceholder: { type: 'string' },
        topic: { type: 'string' },
        topicPlaceholder: { type: 'string' },
        message: { type: 'string' },
        messagePlaceholder: { type: 'string' },
        to: { type: 'string' },
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

    const attributes = {
      name: 'Full name',
      namePlaceholder: 'Enter your name',
      email: 'Email address',
      emailPlaceholder: 'you@ucsc.edu',
      affiliation: 'Affiliation',
      topic: 'Topic',
      message: 'Message',
      messagePlaceholder: 'Your feedback',
      to: 'team@ucsc.edu',
    };

    it('renders the editor controls', () => {
      render(<Edit setAttributes={jest.fn()} attributes={attributes} />);
      expect(
        screen.getByText('Change the wording of the text field titles')
      ).toBeInTheDocument();
    });

    it('reflects the current attribute values in the inputs', () => {
      render(<Edit setAttributes={jest.fn()} attributes={attributes} />);
      expect(screen.getByDisplayValue('Full name')).toBeInTheDocument();
      expect(screen.getByDisplayValue('team@ucsc.edu')).toBeInTheDocument();
    });

    it('updates the name attribute on input change', () => {
      const setAttributes = jest.fn();
      render(<Edit setAttributes={setAttributes} attributes={attributes} />);
      fireEvent.change(screen.getByDisplayValue('Full name'), {
        target: { value: 'Your Name' },
      });
      expect(setAttributes).toHaveBeenCalledWith({ name: 'Your Name' });
    });

    it('updates the recipient (to) attribute on input change', () => {
      const setAttributes = jest.fn();
      render(<Edit setAttributes={setAttributes} attributes={attributes} />);
      fireEvent.change(screen.getByDisplayValue('team@ucsc.edu'), {
        target: { value: 'a@ucsc.edu, b@ucsc.edu' },
      });
      expect(setAttributes).toHaveBeenCalledWith({ to: 'a@ucsc.edu, b@ucsc.edu' });
    });
  });
});
