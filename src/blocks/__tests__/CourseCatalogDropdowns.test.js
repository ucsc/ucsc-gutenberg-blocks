import { render, screen, fireEvent } from '@testing-library/react';
import '@testing-library/jest-dom';

// WPM-165: CourseCatalog.test.js mocks DepartmentDropdown/SubjectDropdown away,
// so this file renders the Course Catalog editor with the real dropdowns and
// mocks only the WordPress UI primitives and the network.
jest.mock('@wordpress/components', () => ({
  Panel: ({ children }) => <div>{children}</div>,
  PanelBody: ({ children }) => <div>{children}</div>,
  RadioControl: () => null,
  SelectControl: ({ label, value, options, onChange, disabled }) => (
    <select
      aria-label={label}
      value={value}
      disabled={disabled}
      onChange={(event) => onChange(event.target.value)}
    >
      {options.map((option) => (
        <option key={option.value} value={option.value}>
          {option.label}
        </option>
      ))}
    </select>
  ),
}), { virtual: true });

let registeredBlock = null;
global.wp = {
  blocks: {
    registerBlockType: (name, config) => {
      registeredBlock = { name, ...config };
    },
  },
};

require('../CourseCatalog').default();

const ENDPOINT_OPTIONS = {
  '/wp-json/ucscgutenbergblocks/v1/departmentcode': [
    { label: '---', value: '---' },
    { label: 'Computer Science and Engineering', value: 'CSE' },
    { label: 'Mathematics', value: 'MATH' },
  ],
  '/wp-json/ucscgutenbergblocks/v1/subjectcode': [
    { label: '---', value: '---' },
    { label: 'Computer Science', value: 'CSE' },
  ],
};

describe('CourseCatalog editor with real department/subject dropdowns (WPM-165)', () => {
  beforeEach(() => {
    global.fetch = jest.fn((url) =>
      Promise.resolve({ text: () => Promise.resolve(JSON.stringify(ENDPOINT_OPTIONS[url] || [])) })
    );
  });

  afterEach(() => {
    jest.clearAllMocks();
  });

  it('fetches departments from the shared departmentcode endpoint and shows the saved department', async () => {
    const Edit = registeredBlock.edit;

    render(
      <Edit
        setAttributes={jest.fn()}
        attributes={{ subjectOrDept: 'dept', department: 'MATH', subject: '---' }}
      />
    );

    expect(global.fetch).toHaveBeenCalledWith('/wp-json/ucscgutenbergblocks/v1/departmentcode');
    const departments = await screen.findByLabelText('Departments');
    expect(departments).toHaveValue('MATH');
    expect(departments).toBeEnabled();
    expect(screen.getByRole('option', { name: 'Computer Science and Engineering' })).toBeInTheDocument();
  });

  it('writes a department picked from the fetched list back to the block attributes', async () => {
    const setAttributes = jest.fn();
    const Edit = registeredBlock.edit;

    render(
      <Edit
        setAttributes={setAttributes}
        attributes={{ subjectOrDept: 'dept', department: '---', subject: '---' }}
      />
    );

    fireEvent.change(await screen.findByLabelText('Departments'), { target: { value: 'CSE' } });

    expect(setAttributes).toHaveBeenCalledWith({ department: 'CSE' });
  });

  it('disables the fetched department list when the block is set to subject mode', async () => {
    const Edit = registeredBlock.edit;

    render(
      <Edit
        setAttributes={jest.fn()}
        attributes={{ subjectOrDept: 'subject', department: '---', subject: 'CSE' }}
      />
    );

    expect(await screen.findByLabelText('Departments')).toBeDisabled();
    expect(await screen.findByLabelText('Subjects')).toBeEnabled();
  });
});
