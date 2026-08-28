import { render, screen, fireEvent } from '@testing-library/react';
import '@testing-library/jest-dom';

// Mock @wordpress/components CheckboxControl
jest.mock('@wordpress/components', () => ({
  CheckboxControl: ({ label, checked, onChange }) => (
    <label>
      <input
        type="checkbox"
        aria-label={label}
        checked={checked}
        onChange={e => onChange(e.target.checked)}
      />
      {label}
    </label>
  ),
}), { virtual: true });

import CheckboxGroupControl from '../CheckboxGroupControl';

const arrOfLabels = ['Option A', 'Option B', 'Option C'];

// Helper: build a JSON-stringified currentAttributes for given checked state
const makeAttrs = (checked = {}) => {
  const obj = {};
  arrOfLabels.forEach(l => { obj[l] = checked[l] ?? false; });
  return JSON.stringify(obj);
};

describe('CheckboxGroupControl', () => {
  describe('rendering', () => {
    it('renders one CheckboxControl per label', () => {
      // Suppress the React "missing key prop" warning — CheckboxGroupControl's
      // arrRender.map() does not pass key; pre-existing component issue, not test-introduced.
      const spy = jest.spyOn(console, 'error').mockImplementation(() => {});
      const currentAttributes = makeAttrs();
      render(
        <CheckboxGroupControl
          setAttributes={jest.fn()}
          currentAttributes={currentAttributes}
          arrOfLabels={arrOfLabels}
          attributeStr="strFacultyTypes"
        />
      );
      arrOfLabels.forEach(label => {
        expect(screen.getByLabelText(label)).toBeInTheDocument();
      });
      spy.mockRestore();
    });

    it('wraps in a plain div when flexCheckboxes is falsy', () => {
      const { container } = render(
        <CheckboxGroupControl
          setAttributes={jest.fn()}
          currentAttributes={makeAttrs()}
          arrOfLabels={arrOfLabels}
          attributeStr="strFacultyTypes"
        />
      );
      const wrapper = container.firstChild;
      expect(wrapper.className).toBe('');
    });

    it('applies flex-checkboxes class when flexCheckboxes is true', () => {
      const { container } = render(
        <CheckboxGroupControl
          setAttributes={jest.fn()}
          currentAttributes={makeAttrs()}
          arrOfLabels={arrOfLabels}
          attributeStr="strFacultyTypes"
          flexCheckboxes
        />
      );
      const wrapper = container.firstChild;
      expect(wrapper).toHaveClass('flex-checkboxes');
    });
  });

  describe('checked state from currentAttributes', () => {
    it('renders checkboxes unchecked when all values are false', () => {
      render(
        <CheckboxGroupControl
          setAttributes={jest.fn()}
          currentAttributes={makeAttrs({ 'Option A': false, 'Option B': false, 'Option C': false })}
          arrOfLabels={arrOfLabels}
          attributeStr="strFacultyTypes"
        />
      );
      arrOfLabels.forEach(label => {
        expect(screen.getByLabelText(label)).not.toBeChecked();
      });
    });

    it('renders checked state from currentAttributes', () => {
      render(
        <CheckboxGroupControl
          setAttributes={jest.fn()}
          currentAttributes={makeAttrs({ 'Option A': true, 'Option B': false, 'Option C': true })}
          arrOfLabels={arrOfLabels}
          attributeStr="strFacultyTypes"
        />
      );
      expect(screen.getByLabelText('Option A')).toBeChecked();
      expect(screen.getByLabelText('Option B')).not.toBeChecked();
      expect(screen.getByLabelText('Option C')).toBeChecked();
    });
  });

  describe('undefined currentAttributes (new block)', () => {
    it('initializes all checkboxes unchecked when checkedByDefault is not provided', () => {
      const setAttributes = jest.fn();
      render(
        <CheckboxGroupControl
          setAttributes={setAttributes}
          currentAttributes={undefined}
          arrOfLabels={arrOfLabels}
          attributeStr="strFacultyTypes"
        />
      );
      // Should call setAttributes with all-false stringified object
      expect(setAttributes).toHaveBeenCalledWith({
        strFacultyTypes: JSON.stringify({ 'Option A': false, 'Option B': false, 'Option C': false }),
      });
      arrOfLabels.forEach(label => {
        expect(screen.getByLabelText(label)).not.toBeChecked();
      });
    });

    it('initializes checkboxes from checkedByDefault when currentAttributes is undefined', () => {
      const setAttributes = jest.fn();
      render(
        <CheckboxGroupControl
          setAttributes={setAttributes}
          currentAttributes={undefined}
          arrOfLabels={arrOfLabels}
          attributeStr="strFacultyTypes"
          checkedByDefault={['Option A', 'Option C']}
        />
      );
      expect(setAttributes).toHaveBeenCalledWith({
        strFacultyTypes: JSON.stringify({ 'Option A': true, 'Option B': false, 'Option C': true }),
      });
      expect(screen.getByLabelText('Option A')).toBeChecked();
      expect(screen.getByLabelText('Option B')).not.toBeChecked();
      expect(screen.getByLabelText('Option C')).toBeChecked();
    });
  });

  describe('onChange callback', () => {
    it('calls setAttributes with updated JSON when a checkbox is toggled on', () => {
      const setAttributes = jest.fn();
      render(
        <CheckboxGroupControl
          setAttributes={setAttributes}
          currentAttributes={makeAttrs()}
          arrOfLabels={arrOfLabels}
          attributeStr="strFacultyTypes"
        />
      );
      setAttributes.mockClear();

      fireEvent.click(screen.getByLabelText('Option B'));

      expect(setAttributes).toHaveBeenCalledWith({
        strFacultyTypes: JSON.stringify({ 'Option A': false, 'Option B': true, 'Option C': false }),
      });
    });

    it('calls setAttributes with updated JSON when a checked box is toggled off', () => {
      const setAttributes = jest.fn();
      render(
        <CheckboxGroupControl
          setAttributes={setAttributes}
          currentAttributes={makeAttrs({ 'Option A': true, 'Option B': false, 'Option C': false })}
          arrOfLabels={arrOfLabels}
          attributeStr="strFacultyTypes"
        />
      );
      setAttributes.mockClear();

      fireEvent.click(screen.getByLabelText('Option A'));

      expect(setAttributes).toHaveBeenCalledWith({
        strFacultyTypes: JSON.stringify({ 'Option A': false, 'Option B': false, 'Option C': false }),
      });
    });

    it('uses the attributeStr prop as the key in the setAttributes call', () => {
      const setAttributes = jest.fn();
      render(
        <CheckboxGroupControl
          setAttributes={setAttributes}
          currentAttributes={makeAttrs()}
          arrOfLabels={arrOfLabels}
          attributeStr="strStaffTypes"
        />
      );
      setAttributes.mockClear();

      fireEvent.click(screen.getByLabelText('Option A'));

      const call = setAttributes.mock.calls[0][0];
      expect(Object.keys(call)).toEqual(['strStaffTypes']);
    });
  });
});
