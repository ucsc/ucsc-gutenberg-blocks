import { render, screen, fireEvent, waitFor } from '@testing-library/react';
import '@testing-library/jest-dom';

jest.mock('@wordpress/components', () => ({
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
}), { virtual: true });

import DepartmentDropdown from '../DepartmentDropdown';
import SubjectDropdown from '../SubjectDropdown';
import DivisionDropdown from '../DivisionDropdown';

const departmentOptions = [
  { label: 'Select a Department', value: '---' },
  { label: 'Computer Science and Engineering', value: 'CSE' },
];

const subjectOptions = [
  { label: 'Select a Subject', value: '---' },
  { label: 'Computer Science', value: 'CSE' },
];

const divisionOptions = [
  { label: 'Select a Division', value: '---' },
  { label: 'Baskin Engineering', value: 'BE' },
];

function mockFetchResponse(options) {
  global.fetch.mockResolvedValueOnce({
    text: () => Promise.resolve(JSON.stringify(options)),
  });
}

describe('shared dropdown components', () => {
  beforeEach(() => {
    global.fetch = jest.fn();
  });

  afterEach(() => {
    jest.clearAllMocks();
  });

  describe('DepartmentDropdown', () => {
    it('fetches department options and renders the selected department', async () => {
      mockFetchResponse(departmentOptions);

      render(
        <DepartmentDropdown
          department="CSE"
          setAttributes={jest.fn()}
          label="Department"
        />
      );

      expect(screen.getByText('Subject Dropdown Loading...')).toBeInTheDocument();
      expect(global.fetch).toHaveBeenCalledWith('/wp-json/ucscgutenbergblocks/v1/departmentcode');

      const select = await screen.findByLabelText('Department');
      expect(select).toHaveValue('CSE');
      expect(screen.getByRole('option', { name: 'Computer Science and Engineering' })).toBeInTheDocument();
    });

    it('defaults an undefined department to placeholder and updates attributes on change', async () => {
      const setAttributes = jest.fn();
      mockFetchResponse(departmentOptions);

      render(
        <DepartmentDropdown
          department={undefined}
          setAttributes={setAttributes}
          label="Department"
        />
      );

      expect(setAttributes).toHaveBeenCalledWith({ department: '---' });
      const select = await screen.findByLabelText('Department');

      fireEvent.change(select, { target: { value: 'CSE' } });

      expect(setAttributes).toHaveBeenCalledWith({ department: 'CSE' });
    });

    it('passes disabled through to the department select', async () => {
      mockFetchResponse(departmentOptions);

      render(
        <DepartmentDropdown
          department="---"
          setAttributes={jest.fn()}
          label="Department"
          disabled
        />
      );

      await waitFor(() => expect(screen.getByLabelText('Department')).toBeDisabled());
    });
  });

  describe('SubjectDropdown', () => {
    it('fetches subject options and renders the selected subject', async () => {
      mockFetchResponse(subjectOptions);

      render(
        <SubjectDropdown
          subject="CSE"
          setAttributes={jest.fn()}
          label="Subject"
        />
      );

      expect(screen.getByText('Subject Dropdown Loading...')).toBeInTheDocument();
      expect(global.fetch).toHaveBeenCalledWith('/wp-json/ucscgutenbergblocks/v1/subjectcode');

      const select = await screen.findByLabelText('Subject');
      expect(select).toHaveValue('CSE');
      expect(screen.getByRole('option', { name: 'Computer Science' })).toBeInTheDocument();
    });

    it('defaults an undefined subject to placeholder and updates attributes on change', async () => {
      const setAttributes = jest.fn();
      mockFetchResponse(subjectOptions);

      render(
        <SubjectDropdown
          subject={undefined}
          setAttributes={setAttributes}
          label="Subject"
        />
      );

      expect(setAttributes).toHaveBeenCalledWith({ subject: '---' });
      const select = await screen.findByLabelText('Subject');

      fireEvent.change(select, { target: { value: 'CSE' } });

      expect(setAttributes).toHaveBeenCalledWith({ subject: 'CSE' });
    });

    it('passes disabled through to the subject select', async () => {
      mockFetchResponse(subjectOptions);

      render(
        <SubjectDropdown
          subject="---"
          setAttributes={jest.fn()}
          label="Subject"
          disabled
        />
      );

      await waitFor(() => expect(screen.getByLabelText('Subject')).toBeDisabled());
    });
  });

  describe('DivisionDropdown', () => {
    it('fetches division options and renders the selected division', async () => {
      mockFetchResponse(divisionOptions);

      render(
        <DivisionDropdown
          division="BE"
          setAttributes={jest.fn()}
          label="Division"
        />
      );

      expect(screen.getByText('Divisions Dropdown Loading...')).toBeInTheDocument();
      expect(global.fetch).toHaveBeenCalledWith('/wp-json/ucscgutenbergblocks/v1/divisioncode');

      const select = await screen.findByLabelText('Division');
      expect(select).toHaveValue('BE');
      expect(screen.getByRole('option', { name: 'Baskin Engineering' })).toBeInTheDocument();
    });

    it('defaults an undefined division to placeholder and updates attributes on change', async () => {
      const setAttributes = jest.fn();
      mockFetchResponse(divisionOptions);

      render(
        <DivisionDropdown
          division={undefined}
          setAttributes={setAttributes}
          label="Division"
        />
      );

      expect(setAttributes).toHaveBeenCalledWith({ division: '---' });
      const select = await screen.findByLabelText('Division');

      fireEvent.change(select, { target: { value: 'BE' } });

      expect(setAttributes).toHaveBeenCalledWith({ division: 'BE' });
    });

    it('passes disabled through to the division select', async () => {
      mockFetchResponse(divisionOptions);

      render(
        <DivisionDropdown
          division="---"
          setAttributes={jest.fn()}
          label="Division"
          disabled
        />
      );

      await waitFor(() => expect(screen.getByLabelText('Division')).toBeDisabled());
    });
  });
});
