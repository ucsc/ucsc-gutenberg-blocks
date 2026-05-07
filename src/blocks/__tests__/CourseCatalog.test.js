import { render, screen } from '@testing-library/react';
import '@testing-library/jest-dom';

// Mock WordPress components
jest.mock('@wordpress/components', () => ({
  Panel: ({ children, header }) => <div data-testid="panel" data-header={header}>{children}</div>,
  PanelBody: ({ children, title }) => <div data-testid="panel-body" data-title={title}>{children}</div>,
  RadioControl: ({ selected, options, onChange }) => (
    <div data-testid="radio-control" data-selected={selected}>
      {options.map((opt) => (
        <button key={opt.value} onClick={() => onChange(opt.value)}>
          {opt.label}
        </button>
      ))}
    </div>
  ),
}), { virtual: true });

// Mock child components
jest.mock('../../components/DepartmentDropdown', () => ({ label, disabled }) => (
  <div data-testid="department-dropdown" data-label={label} data-disabled={disabled} />
));
jest.mock('../../components/SubjectDropdown', () => ({ label, disabled }) => (
  <div data-testid="subject-dropdown" data-label={label} data-disabled={disabled} />
));

// Capture the block registration config
let registeredBlock = null;
global.wp = {
  blocks: {
    registerBlockType: (name, config) => {
      registeredBlock = { name, ...config };
    },
  },
};

// Import after mocks are set up
const CourseCatalog = require('../CourseCatalog').default;

// Call the function to trigger registerBlockType
CourseCatalog();

describe('CourseCatalog block', () => {
  afterEach(() => {
    jest.clearAllMocks();
  });

  describe('registration', () => {
    it('registers with the correct block name', () => {
      expect(registeredBlock.name).toBe('ucscblocks/coursecatalog');
    });

    it('has the correct title', () => {
      expect(registeredBlock.title).toBe('Course Catalog');
    });

    it('has the correct icon', () => {
      expect(registeredBlock.icon).toBe('book-alt');
    });

    it('is in the common category', () => {
      expect(registeredBlock.category).toBe('common');
    });

    it('defines subjectOrDept, department, and subject attributes', () => {
      expect(registeredBlock.attributes).toEqual({
        subjectOrDept: { type: 'string' },
        department: { type: 'string' },
        subject: { type: 'string' },
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

    it('renders without crashing', () => {
      const setAttributes = jest.fn();
      render(
        <Edit
          setAttributes={setAttributes}
          attributes={{ subjectOrDept: 'dept', department: 'CMPS', subject: '' }}
        />
      );
      expect(screen.getByTestId('panel')).toBeInTheDocument();
    });

    it('renders the panel with correct header', () => {
      const setAttributes = jest.fn();
      render(
        <Edit
          setAttributes={setAttributes}
          attributes={{ subjectOrDept: 'dept', department: '', subject: '' }}
        />
      );
      expect(screen.getByTestId('panel')).toHaveAttribute('data-header', 'Course Catalog Block');
    });

    it('defaults subjectOrDept to "dept" when undefined', () => {
      const setAttributes = jest.fn();
      render(
        <Edit
          setAttributes={setAttributes}
          attributes={{ department: '', subject: '' }}
        />
      );
      expect(setAttributes).toHaveBeenCalledWith({ subjectOrDept: 'dept' });
    });

    it('disables SubjectDropdown when subjectOrDept is "dept"', () => {
      const setAttributes = jest.fn();
      render(
        <Edit
          setAttributes={setAttributes}
          attributes={{ subjectOrDept: 'dept', department: '', subject: '' }}
        />
      );
      expect(screen.getByTestId('department-dropdown')).toHaveAttribute('data-disabled', 'false');
      expect(screen.getByTestId('subject-dropdown')).toHaveAttribute('data-disabled', 'true');
    });

    it('disables DepartmentDropdown when subjectOrDept is "subject"', () => {
      const setAttributes = jest.fn();
      render(
        <Edit
          setAttributes={setAttributes}
          attributes={{ subjectOrDept: 'subject', department: '', subject: '' }}
        />
      );
      expect(screen.getByTestId('department-dropdown')).toHaveAttribute('data-disabled', 'true');
      expect(screen.getByTestId('subject-dropdown')).toHaveAttribute('data-disabled', 'false');
    });

    it('calls setAttributes when radio selection changes', () => {
      const setAttributes = jest.fn();
      render(
        <Edit
          setAttributes={setAttributes}
          attributes={{ subjectOrDept: 'dept', department: '', subject: '' }}
        />
      );
      screen.getByText('Subject').click();
      expect(setAttributes).toHaveBeenCalledWith({ subjectOrDept: 'subject' });
    });
  });
});
