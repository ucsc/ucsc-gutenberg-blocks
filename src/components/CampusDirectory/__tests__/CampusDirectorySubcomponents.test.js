import { render, screen, fireEvent, waitFor } from '@testing-library/react';
import '@testing-library/jest-dom';

jest.mock('@wordpress/components', () => ({
  RadioControl: ({ selected, options, onChange }) => (
    <div data-testid="radio-control" data-selected={selected}>
      {options.map((opt) => (
        <button key={String(opt.value)} onClick={() => onChange(opt.value)}>
          {opt.label}
        </button>
      ))}
    </div>
  ),
  SelectControl: ({ label, value, options, onChange, disabled }) => (
    <label>
      {label}
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
    </label>
  ),
  CheckboxControl: ({ label, checked, onChange }) => (
    <label>
      <input
        type="checkbox"
        aria-label={label}
        checked={checked}
        onChange={(e) => onChange(e.target.checked)}
      />
      {label}
    </label>
  ),
}), { virtual: true });

import PageLayout from '../PageLayout';
import AutomatedFeeds from '../AutomatedFeeds';
import CampusDirectoryDepartmentDropdown from '../CampusDirectoryDepartmentDropdown';

describe('CampusDirectory sub-components', () => {
  afterEach(() => {
    jest.clearAllMocks();
  });

  describe('PageLayout', () => {
    it('renders list, tiled, and table layout options', () => {
      render(<PageLayout pageLayout="list" setAttributes={jest.fn()} />);

      expect(screen.getByText('List Layout')).toBeInTheDocument();
      expect(screen.getByText('Tiled Layout')).toBeInTheDocument();
      expect(screen.getByText('Table Layout')).toBeInTheDocument();
      expect(screen.getByTestId('radio-control')).toHaveAttribute('data-selected', 'list');
    });

    it('defaults pageLayout to "list" when undefined', () => {
      const setAttributes = jest.fn();
      render(<PageLayout pageLayout={undefined} setAttributes={setAttributes} />);

      expect(setAttributes).toHaveBeenCalledWith({ pageLayout: 'list' });
    });

    it('does not re-default when pageLayout is already provided', () => {
      const setAttributes = jest.fn();
      render(<PageLayout pageLayout="table" setAttributes={setAttributes} />);

      expect(setAttributes).not.toHaveBeenCalled();
      expect(screen.getByTestId('radio-control')).toHaveAttribute('data-selected', 'table');
    });

    it('updates pageLayout via the radio onChange', () => {
      const setAttributes = jest.fn();
      render(<PageLayout pageLayout="list" setAttributes={setAttributes} />);

      fireEvent.click(screen.getByText('Table Layout'));

      expect(setAttributes).toHaveBeenCalledWith({ pageLayout: 'table' });
    });
  });

  describe('AutomatedFeeds', () => {
    it('renders faculty, staff, and graduate checkbox groups with expected labels', () => {
      render(
        <AutomatedFeeds
          setAttributes={jest.fn()}
          strFacultyTypes={undefined}
          strStaffTypes={undefined}
          strGradTypes={undefined}
        />
      );

      expect(screen.getByText('Faculty Types')).toBeInTheDocument();
      expect(screen.getByText('Staff Types')).toBeInTheDocument();
      expect(screen.getByText('Graduate Students')).toBeInTheDocument();

      // A representative label from each group
      expect(screen.getByLabelText('Regular Faculty')).toBeInTheDocument();
      expect(screen.getByLabelText('Postdoctoral Scholar')).toBeInTheDocument();
      expect(screen.getByLabelText('Grad Students')).toBeInTheDocument();
    });

    it('reflects checked state passed down through currentAttributes', () => {
      const strFacultyTypes = JSON.stringify({ 'Regular Faculty': true, Lecturer: false });

      render(
        <AutomatedFeeds
          setAttributes={jest.fn()}
          strFacultyTypes={strFacultyTypes}
          strStaffTypes={undefined}
          strGradTypes={undefined}
        />
      );

      expect(screen.getByLabelText('Regular Faculty')).toBeChecked();
      expect(screen.getByLabelText('Lecturer')).not.toBeChecked();
    });

    it('routes checkbox changes to setAttributes under the group attribute key', () => {
      const setAttributes = jest.fn();

      render(
        <AutomatedFeeds
          setAttributes={setAttributes}
          strFacultyTypes={JSON.stringify({ Lecturer: false })}
          strStaffTypes={JSON.stringify({ 'Regular Staff': false })}
          strGradTypes={JSON.stringify({ 'Grad Students': false })}
        />
      );
      setAttributes.mockClear();

      fireEvent.click(screen.getByLabelText('Regular Staff'));

      const call = setAttributes.mock.calls[0][0];
      expect(Object.keys(call)).toEqual(['strStaffTypes']);
    });
  });

  describe('CampusDirectoryDepartmentDropdown', () => {
    beforeEach(() => {
      global.fetch = jest.fn();
    });

    const departmentOptions = [
      { label: 'Select a Department', value: '---' },
      { label: 'History', value: 'HIS' },
    ];

    function mockFetch(options) {
      global.fetch.mockResolvedValueOnce({
        text: () => Promise.resolve(JSON.stringify(options)),
      });
    }

    it('fetches from the cddepartmentcode endpoint and renders the selected department', async () => {
      mockFetch(departmentOptions);

      render(
        <CampusDirectoryDepartmentDropdown
          department="HIS"
          setAttributes={jest.fn()}
          label="Department"
        />
      );

      expect(screen.getByText('Department Dropdown Loading...')).toBeInTheDocument();
      expect(global.fetch).toHaveBeenCalledWith('/wp-json/ucscgutenbergblocks/v1/cddepartmentcode');

      const select = await screen.findByLabelText('Department');
      expect(select).toHaveValue('HIS');
      expect(screen.getByRole('option', { name: 'History' })).toBeInTheDocument();
    });

    it('defaults an undefined department to the placeholder and updates on change', async () => {
      const setAttributes = jest.fn();
      mockFetch(departmentOptions);

      render(
        <CampusDirectoryDepartmentDropdown
          department={undefined}
          setAttributes={setAttributes}
          label="Department"
        />
      );

      expect(setAttributes).toHaveBeenCalledWith({ department: '---' });
      const select = await screen.findByLabelText('Department');

      fireEvent.change(select, { target: { value: 'HIS' } });

      expect(setAttributes).toHaveBeenCalledWith({ department: 'HIS' });
    });

    it('passes the disabled prop through to the select', async () => {
      mockFetch(departmentOptions);

      render(
        <CampusDirectoryDepartmentDropdown
          department="---"
          setAttributes={jest.fn()}
          label="Department"
          disabled
        />
      );

      await waitFor(() => expect(screen.getByLabelText('Department')).toBeDisabled());
    });
  });
});
