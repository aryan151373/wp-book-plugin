const { registerBlockType } = wp.blocks;
const { useState, useEffect } = wp.element;
const { InspectorControls } = wp.blockEditor;
const { SelectControl, PanelBody } = wp.components;
const { withSelect } = wp.data;

// Register the block
registerBlockType('wp-book/books-by-category', {
    title: 'Books by Category',
    icon: 'book',
    category: 'widgets',
    attributes: {
        category: {
            type: 'string',
            default: ''
        },
    },
    edit: withSelect((select) => {
        const { getEntityRecords } = select('core');
        const categories = getEntityRecords('taxonomy', 'book_category', { per_page: -1 });
        return {
            categories: categories || [],
        };
    })((props) => {
        const { attributes, setAttributes, categories } = props;
        const [books, setBooks] = useState([]);

        useEffect(() => {
            if (attributes.category) {
                wp.apiFetch({ path: `/wp/v2/book?book_category=${attributes.category}` })
                    .then((data) => setBooks(data))
                    .catch((err) => console.error(err));
            }
        }, [attributes.category]);

        return (
            <>
                <InspectorControls>
                    <PanelBody title="Book Settings">
                        <SelectControl
                            label="Select Book Category"
                            value={attributes.category}
                            options={[
                                { label: 'Select a category', value: '' },
                                ...categories.map((category) => ({
                                    label: category.name,
                                    value: category.id,
                                }))
                            ]}
                            onChange={(newCategory) => setAttributes({ category: newCategory })}
                        />
                    </PanelBody>
                </InspectorControls>
                <div className="wp-book-block">
                    {books.length > 0 ? (
                        <ul>
                            {books.map((book) => (
                                <li key={book.id}>
                                    <h4>{book.title.rendered}</h4>
                                    <p>{book.author_name}</p>
                                    <p>{book.price}</p>
                                    <a href={book.url} target="_blank" rel="noopener noreferrer">
                                        More Info
                                    </a>
                                </li>
                            ))}
                        </ul>
                    ) : (
                        <p>No books found in this category.</p>
                    )}
                </div>
            </>
        );
    }),
    save() {
        return null;
    },
});
