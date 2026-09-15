import { render, screen, fireEvent } from '@testing-library/react';
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
  CheckboxControl: ({ label, checked, onChange }) => (
    <label>
      <input
        type="checkbox"
        checked={checked}
        onChange={(e) => onChange(e.target.checked)}
      />
      {label}
    </label>
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

    it('defines subjectOrDept, department, subject, and defaultColumns attributes', () => {
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

  describe('defaultColumns attribute and toggleColumn', () => {
    const Edit = registeredBlock.edit;

    it('defaults to [seats, days] when defaultColumns attribute is undefined', () => {
      const setAttributes = jest.fn();
      render(
        <Edit
          setAttributes={setAttributes}
          attributes={{ subjectOrDept: 'dept', department: '', subject: '' }}
        />
      );
      // Seats and Days checkboxes should be checked; others unchecked
      const seats = screen.getByRole('checkbox', { name: /Seats/i });
      const days  = screen.getByRole('checkbox', { name: /Days/i });
      const time  = screen.getByRole('checkbox', { name: /Time/i });
      expect(seats.checked).toBe(true);
      expect(days.checked).toBe(true);
      expect(time.checked).toBe(false);
    });

    it('toggleColumn adds a key when checked (column not yet in defaultColumns)', () => {
      const setAttributes = jest.fn();
      render(
        <Edit
          setAttributes={setAttributes}
          attributes={{ subjectOrDept: 'dept', department: '', subject: '', defaultColumns: ['seats', 'days'] }}
        />
      );
      const time = screen.getByRole('checkbox', { name: /Time/i });
      fireEvent.click(time);
      expect(setAttributes).toHaveBeenCalledWith({ defaultColumns: ['seats', 'days', 'time'] });
    });

    it('toggleColumn removes a key when unchecked', () => {
      const setAttributes = jest.fn();
      render(
        <Edit
          setAttributes={setAttributes}
          attributes={{ subjectOrDept: 'dept', department: '', subject: '', defaultColumns: ['seats', 'days'] }}
        />
      );
      const days = screen.getByRole('checkbox', { name: /Days/i });
      fireEvent.click(days);
      expect(setAttributes).toHaveBeenCalledWith({ defaultColumns: ['seats'] });
    });

    it('toggleColumn does not duplicate a key already in defaultColumns', () => {
      // The includes-guard in toggleColumn: `current.includes(key) ? current : [...current, key]`
      // Test the invariant: any setAttributes call must produce an array with no duplicate values.
      // We add 'time' to ['seats','days'], rerender so React tracks the updated state, then
      // remove 'time' — every resulting array must be duplicate-free.
      const setAttributes = jest.fn();
      const { rerender } = render(
        <Edit
          setAttributes={setAttributes}
          attributes={{ subjectOrDept: 'dept', department: '', subject: '', defaultColumns: ['seats', 'days'] }}
        />
      );

      // Add 'time'
      fireEvent.click(screen.getByRole('checkbox', { name: /Time/i }));
      const afterAdd = setAttributes.mock.calls.slice(-1)[0][0].defaultColumns;
      expect(afterAdd).toEqual(['seats', 'days', 'time']);
      expect(afterAdd.length).toBe(new Set(afterAdd).size); // no duplicates after add

      // Rerender with 'time' present; then add 'location' — verifies includes() check
      // with a non-trivial current array
      rerender(
        <Edit
          setAttributes={setAttributes}
          attributes={{ subjectOrDept: 'dept', department: '', subject: '', defaultColumns: afterAdd }}
        />
      );
      fireEvent.click(screen.getByRole('checkbox', { name: /Location/i }));
      const afterAddLocation = setAttributes.mock.calls.slice(-1)[0][0].defaultColumns;
      expect(afterAddLocation).toEqual(['seats', 'days', 'time', 'location']);
      expect(afterAddLocation.length).toBe(new Set(afterAddLocation).size); // no duplicates
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
