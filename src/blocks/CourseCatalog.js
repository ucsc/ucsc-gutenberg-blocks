import { useEffect, useState } from '@wordpress/element'
import { SelectControl } from '@wordpress/components';

const CourseCatalog = () => {
  wp.blocks.registerBlockType("ucscblocks/coursecatalog", {
    title: "Course Catalog",
    icon: "book-alt",
    category: "common",
    attributes: {
      subject: {
        type: "string"
      },
    },
    edit: ({ setAttributes, attributes }) => {
      const [dept, setDept] = useState("");

      useEffect(() => {
        fetch('/wp-json/ucscgutenbergblocks/v1/coursecatalogdept')
          .then(res => res.text())
          .then((text) => {
            const resp = JSON.parse(text);
            setDept(resp.dept);
          });
      }, []);

      return (
        <>
          <h2>Course Catalog Block</h2>
          {dept === "" && (
            <>
              <h4>
                The Course Catalog Dept needs to be set here <a target="_blank" href="/wp-admin/options-general.php?page=ucsc_gutenberg_blocks_settings_page">here.</a>
              </h4>
            </>
          )}
        </>
      );
    },
    save: (props) => {
      return null;
    }
  })
}

export default CourseCatalog;
