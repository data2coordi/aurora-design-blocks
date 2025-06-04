import { __ } from '@wordpress/i18n';


const { Fragment, useState, useEffect } = wp.element;
const { registerFormatType, insert } = wp.richText;
const { RichTextToolbarButton } = wp.blockEditor;
const { Modal, Button, TextControl, Spinner } = wp.components;

const FontAwesomeSearchButton = ({ value, onChange }) => {
    const [isModalOpen, setIsModalOpen] = useState(false);
    const [searchTerm, setSearchTerm] = useState('');
    // JSON 全体（各カテゴリごとのオブジェクト）を保持する変数
    const [iconsData, setIconsData] = useState(null);
    const [loading, setLoading] = useState(false);

    useEffect(() => {
        if (isModalOpen && !iconsData) {
            setLoading(true);
            fetch('/wp-content/plugins/aurora-design-blocks/blocks/gfontawesome/fontawesome-icons.json')
                .then(response => response.json())
                .then(data => {
                    setIconsData(data);
                    setLoading(false);
                })
                .catch(error => {
                    console.error('icon get error:', error);
                    setLoading(false);
                });
        }
    }, [isModalOpen, iconsData]);

    const insertIcon = (icon) => {
        const shortcode = `[fontawesome icon=${icon}]`;
        const newValue = insert(value, shortcode);
        onChange(newValue);
        setIsModalOpen(false);
    };

    return (
        <Fragment>
            <RichTextToolbarButton
                icon="search"
                title="Font Awesome Icon Search"
                onClick={() => setIsModalOpen(true)}
            />
            {isModalOpen && (
                <Modal
                    title="Font Awesome Icon Search"
                    onRequestClose={() => setIsModalOpen(false)}
                    className="gfontawesome-modal"
                >
                    <TextControl
                        label="Search"
                        value={searchTerm}
                        onChange={(newValue) => setSearchTerm(newValue)}
                        placeholder="ex): home, user, cog..."
                    />
                    {loading ? (
                        <Spinner />
                    ) : (
                        <div className="gfontawesome-categories">
                            {iconsData ? (
                                Object.entries(iconsData).map(([category, iconList]) => {
                                    // 入力された検索語句でフィルタ
                                    const filteredList = iconList.filter(icon =>
                                        icon.toLowerCase().includes(searchTerm.toLowerCase())
                                    );
                                    return (
                                        <div key={category} className="gfontawesome-category">
                                            {/* カテゴリ名（アンダースコアをスペースに変換し、先頭大文字に変換） */}
                                            <h3 style={{ marginTop: '20px', textTransform: 'capitalize' }}>
                                                {category.replace(/_/g, ' ')}
                                            </h3>
                                            {filteredList.length > 0 ? (
                                                <div
                                                    className="fa-icons-grid"
                                                    style={{
                                                        display: 'flex',
                                                        flexWrap: 'wrap',
                                                        gap: '10px',
                                                        marginBottom: '20px'
                                                    }}
                                                >
                                                    {filteredList.map((icon) => (
                                                        <Button
                                                            key={icon}
                                                            onClick={() => insertIcon(icon)}
                                                            style={{ padding: '10px' }}
                                                        >
                                                            <i className={`fas ${icon}`} style={{ fontSize: '24px' }}></i>
                                                        </Button>
                                                    ))}
                                                </div>
                                            ) : (
                                                <p style={{ marginLeft: '10px' }}>{__("Icon not found", "aurora-design-blocks")}</p>
                                            )}
                                        </div>
                                    );
                                })
                            ) : (
                                <p>{__("Icon not found", "aurora-design-blocks")}</p>
                            )}
                        </div>
                    )}
                </Modal>
            )}
        </Fragment>
    );
};

registerFormatType('fontawesome/icon', {
    title: 'Font Awesome',
    tagName: 'span',
    className: 'gfontawesome-shortcode',
    edit: (props) => <FontAwesomeSearchButton {...props} />,
});

export { FontAwesomeSearchButton };
