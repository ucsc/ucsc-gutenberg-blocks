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
        console.log(attributes)
    //   const { siteid, postType } = attributes;


    //   let selectedSite, setSelectedSite;
    //   if (typeof siteid === 'undefined') {
    //     setAttributes({
    //       siteid: 1
    //     });
    //     [selectedSite, setSelectedSite] = useState(1);
    //   } else {
    //     [selectedSite, setSelectedSite] = useState(siteid);
    //   }

    //   let selectedPostType, setSelectedPostType;
    //   if (typeof postType === 'undefined') {
    //     setAttributes({
    //       postType: ""
    //     });
    //     [selectedPostType, setSelectedPostType] = useState("");
    //   } else {
    //     [selectedPostType, setSelectedPostType] = useState(postType);
    //   }

    //   const [sites, setSites] = useState([]);
    //   const [postTypes, setPostTypes] = useState([]);

    //   const fetchPostTypes = (siteid) => {
    //     fetch('/wp-json/ucscgutenbergblocks/v1/posttypes?siteid=' + siteid)
    //       .then(res => res.text())
    //       .then((text) => {
    //         const postTypes = JSON.parse(text);
    //         const pTypes = [];
    //         Object.keys(postTypes).map(type => {
    //           pTypes.push({
    //             label: type,
    //             value: type
    //           })
    //         })
    //         setPostTypes(pTypes);
    //       })
    //   }

    //   useEffect(() => {
    //     fetch('/wp-json/ucscgutenbergblocks/v1/sites')
    //       .then(res => res.text())
    //       .then((text) => {
    //         setSites(JSON.parse(text));
    //       });
    //     fetchPostTypes(selectedSite);
    //   }, []);

    //   const options = sites.map(site => {
    //     return {
    //       label: site.domain,
    //       value: site.blog_id
    //     }
    //   })

    //   const userSelectedASite = (e) => {
    //     setSelectedSite(e.target.value);
    //     setAttributes({
    //       siteid: e.target.value
    //     });
    //     fetchPostTypes(e.target.value);
    //   }

    //   const userSelectedAPostType = (e) => {
    //     setSelectedPostType(e.target.value);
    //     setAttributes({
    //       postType: e.target.value
    //     });
    //   }

      return (
        <>
          <h2>Course Catalog</h2>
          <h4>subject</h4>
         
        </>
      );
    },
    save: (props) => {
      return null;
    }
  })
}

export default CourseCatalog;
