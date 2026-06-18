import { render, screen } from '@testing-library/react';
import '@testing-library/jest-dom';

// Mock WordPress components
jest.mock('@wordpress/components', () => ({
  Panel: ({ children, header }) => <div data-testid="panel" data-header={header}>{children}</div>,
  PanelBody: ({ children, title }) => <div data-testid="panel-body" data-title={title}>{children}</div>,
  CheckboxControl: ({ checked, label, onChange }) => (
    <label data-testid="checkbox-control">
      <input
        type="checkbox"
        checked={checked}
        onChange={(e) => onChange(e.target.checked)}
      />
      {label}
    </label>
  ),
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

// Mock window.location
delete window.location;
window.location = { href: 'http://localhost' };

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
const ClassSchedule = require('../ClassSchedule').default;

// Call the function to trigger registerBlockType
ClassSchedule();

describe('ClassSchedule block', () => {
  afterEach(() => {
    jest.clearAllMocks();
  });

  describe('registration', () => {
    it('registers with the correct block name', () => {
      expect(registeredBlock.name).toBe('ucscblocks/classschedule');
    });

    it('has the correct title', () => {
      expect(registeredBlock.title).toBe('Class Schedule');
    });

    it('has the correct icon', () => {
      expect(registeredBlock.icon).toBe('schedule');
    });

    it('is in the common category', () => {
      expect(registeredBlock.category).toBe('common');
    });

    it('defines subjectOrDept, department, and subject attributes', () => {
      expect(registeredBlock.attributes).toEqual({
        subjectOrDept: { type: 'string' },
        department: { type: 'string' },
        subject: { type: 'string' },
        defaultColumns: { type: 'array' },
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
      expect(screen.getByTestId('panel')).toHaveAttribute('data-header', 'Class Schedule Block');
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
      // Click the "Subject" radio option
      screen.getByText('Subject').click();
      expect(setAttributes).toHaveBeenCalledWith({ subjectOrDept: 'subject' });
    });
  });

  describe('deprecated', () => {
    it('has one deprecation entry', () => {
      expect(registeredBlock.deprecated).toHaveLength(1);
    });

    it('migration removes useNewServer attribute', () => {
      const oldAttributes = {
        subjectOrDept: 'dept',
        department: 'CMPS',
        subject: '',
        useNewServer: true,
      };
      const migrated = registeredBlock.deprecated[0].migrate(oldAttributes);
      expect(migrated).toEqual({
        subjectOrDept: 'dept',
        department: 'CMPS',
        subject: '',
      });
      expect(migrated).not.toHaveProperty('useNewServer');
    });

    it('deprecated save returns null', () => {
      expect(registeredBlock.deprecated[0].save()).toBeNull();
    });
  });
});
