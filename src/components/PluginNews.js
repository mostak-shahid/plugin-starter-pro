import { __ } from "@wordpress/i18n";
import { useEffect, useState } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import {Offcanvas, Button, Stack, Badge, Modal} from 'react-bootstrap';
export default function PluginNews({showOffcanvas, setShowOffcanvas}) {
    // const [showOffcanvas, setShowOffcanvas] = useState(false);

    const OffcanvasClose = () => setShowOffcanvas(false);
    const OffcanvasShow = () => setShowOffcanvas(true);

    const [showModal, setShowModal] = useState(false);
    const [newsReload, setNewsReload] = useState(0);

    const ModalClose = () => {
        setNewsReload(Math.random());
        setShowModal(false);
    }
    const ModalShow = () => setShowModal(true);


    const [loading, setLoading] = useState(false);
    const [newsItems, setNewsItems] = useState([]);
    const [activeNews, setActiveNews] = useState(null);
    const truncateText = (text, wordLimit = 15) => {
        const words = text.split(/\s+/);
        if (words.length <= wordLimit) return text;
        return words.slice(0, wordLimit).join(' ') + '...';
    };
    useEffect(() => {
        const fetchNews = async () => {
            setLoading(true);
            try {
                // Sends data directly to the native WordPress custom REST API endpoint
                const response = await apiFetch({
                    path: '/plugin-starter-pro/v1/news',
                    method: 'GET'
                });
                console.log(response);
                if (response.success) {                    
                    setNewsItems(response.data);
                }
            } catch (error) {
                console.error("Error fetching news:", error);
            } finally {
                setLoading(false);
            }
        };
        fetchNews();
    }, [newsReload]); 

    const readMessage = async(item) => {
        try {
            const params = new URLSearchParams({
                id: item?.id,
            });
            // Sends data directly to the native WordPress custom REST API endpoint
            const response = await apiFetch({
                path: `/plugin-starter-pro/v1/news/read?${params.toString()}`,
                method: 'POST'
            });            
            setActiveNews(item);
            ModalShow();
            OffcanvasClose();
        } catch (error) {
            console.error("Error fetching news:", error);
        } finally {
            setLoading(false);
        }
    }

    return (
        <>
            <Offcanvas className="mos-press-news-container" show={showOffcanvas} onHide={OffcanvasClose} placement="end">
                <Offcanvas.Header closeButton className="border-bottom">
                    <Offcanvas.Title>{__("What's New?", "plugin-starter-pro")}</Offcanvas.Title>
                </Offcanvas.Header>
                <Offcanvas.Body>

                    {loading ? (
                        <p>{__("Loading news...", "plugin-starter-pro")}</p>
                    ) : newsItems.length === 0 ? (
                        <p>{__("No news available.", "plugin-starter-pro")}</p>
                    ) : (
                        <div style={{ overflowY: 'auto' }}>
                            {newsItems.map((item) => (
                                <div 
                                    key={item.id} 
                                    className={`mos-press-news-item border rounded-3 p-2 mb-3 ${item.is_read?'news-read':'news-unread'}`}
                                    // style={{ marginBottom: '20px', paddingBottom: '20px', borderBottom: '1px solid var(--semi-color-border)' }}
                                >
                                    <strong style={{ fontSize: '16px' }}>{item.title}</strong>
                                    {item?.tags && item.tags.length > 0 && (
                                        <div className='mt-2'>
                                            <Stack direction="horizontal" gap={2}>
                                                {item.tags.map((tag, index) => (
                                                    <Badge key={index}>{tag}</Badge>
                                                ))}
                                            </Stack>
                                        </div>
                                    )}
                                    <div className='mt-2'>
                                        <p>
                                            {truncateText(item.news)}
                                        </p>
                                        <Button
                                            size="sm"
                                            onClick={() => {
                                                readMessage(item);
                                            }}
                                            // style={{ padding: 0, marginLeft: '5px' }}
                                        >
                                            {__("Read more", "plugin-starter-pro")}
                                        </Button>
                                    </div>
                                </div>
                            ))}
                        </div>
                    )}

                </Offcanvas.Body>
            </Offcanvas>



            <Modal show={showModal} onHide={ModalClose}>
                <Modal.Header closeButton>
                <Modal.Title>{activeNews ? activeNews.title : __("Modal heading", 'plugin-starter-pro')}</Modal.Title>
                </Modal.Header>
                <Modal.Body>
                    {activeNews?.tags?.length > 0 && (
                        <Stack direction="horizontal" gap={2}>
                            {activeNews?.tags?.map((tag, index) => (
                                <Badge key={index}>{tag}</Badge>
                            ))}
                        </Stack>
                    )}
                    <br />
                    {activeNews?.news}
                </Modal.Body>
                <Modal.Footer>
                <Button variant="secondary" onClick={ModalClose}>
                    {__("Close", "plugin-starter-pro")}
                </Button>
                </Modal.Footer>
            </Modal>
        </>
    );
}
