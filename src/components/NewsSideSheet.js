import React, { useState, useEffect, Suspense  } from 'react';

import { __ } from "@wordpress/i18n";
import apiFetch from "@wordpress/api-fetch";
import { Layout, Typography, Banner, Space, Badge, Button, SideSheet, Col, Row, Tag, Modal, Card} from '@douyinfe/semi-ui';
import { IconStar, IconSetting, IconHome, IconMember, IconBookStroked, IconHelpCircleStroked, IconBellStroked, IconSun, IconMoon, IconTemplate,IconCustomerSupport, IconFile, } from '@douyinfe/semi-icons';
import { LocaleProvider } from '@douyinfe/semi-ui';

const { Title, Text, Paragraph } = Typography;
export default function NewsSideSheet({newsVisible, handleNewsVisible, newsCurrentPage, setNewsCurrentPage}) {
    const [newsItems, setNewsItems] = useState([]);
    const [modalVisible, setModalVisible] = useState(false);
    const [activeNews, setActiveNews] = useState(null);
    const [readNewsIds, setReadNewsIds] = useState([]);
    const itemsPerPage = 5;

    const truncateText = (text, wordLimit = 15) => {
        const words = text.split(/\s+/);
        if (words.length <= wordLimit) return text;
        return words.slice(0, wordLimit).join(' ') + '...';
    };

    const markNewsAsRead = async (newsId) => {
        if (!readNewsIds.includes(newsId)) {
            const updatedReadIds = [...readNewsIds, newsId];
            setReadNewsIds(updatedReadIds);
            try {
                await apiFetch({
                    path: '/plugin-starter/v1/set-option',
                    method: 'POST',
                    data: {
                        option_name: 'mospress_read_news',
                        option_value: updatedReadIds
                    }
                });
            } catch (error) {
                console.error("Error saving read news:", error);
            }
        }
    };

    useEffect(() => {
        const fetchNews = async () => {
            try {
                const response = await fetch('https://raw.githubusercontent.com/mostak-shahid/update/refs/heads/master/plugin-news.json');
                const data = await response.json();
                setNewsItems(data);
            } catch (error) {
                console.error("Error fetching news:", error);
            }
        };
        fetchNews();

        const fetchReadNews = async () => {
            try {
                const response = await apiFetch({
                    path: '/plugin-starter/v1/get-option?option_name=mospress_read_news',
                    method: 'GET'
                });
                if (response && Array.isArray(response)) {
                    setReadNewsIds(response);
                }
            } catch (error) {
                console.error("Error fetching read news:", error);
            }
        };
        fetchReadNews();
    }, []); 
    return (
        <>
            {/* --- What's New SideSheet --- */}
            <SideSheet
                placement="right"
                visible={newsVisible}
                onCancel={() => handleNewsVisible(false)}
                title={__("What's New?", "plugin-starter")}
                closeOnEsc={true}
            >
                {newsItems.length === 0 ? (
                    <p>{__("Loading news...", "plugin-starter")}</p>
                ) : (
                    <div style={{ display: 'flex', flexDirection: 'column', height: '100%' }}>
                        <div style={{ flex: 1, overflowY: 'auto', paddingRight: '10px' }}>
                            {newsItems
                                .slice((newsCurrentPage - 1) * itemsPerPage, newsCurrentPage * itemsPerPage)
                                .map((item) => (
                                <Card key={item.id} style={{ marginBottom: 12, backgroundColor: !readNewsIds.includes(item.id) ? 'var(--semi-color-primary-light-default)' : '' }} bodyStyle={{ padding: 10 }}>
                                    <Text strong style={{ fontSize: '16px' }}>{!readNewsIds.includes(item.id) ? '• ' : ''}{item.title}</Text>
                                    {item?.tags && item.tags.length > 0 && (
                                        <div className='mt-2'>
                                            <Space>
                                                {item.tags.map((tag, index) => (
                                                    <Tag key={index} size="small" shape='circle' color='amber'>{tag}</Tag>
                                                ))}
                                            </Space>
                                        </div>
                                    )}
                                    <div className='mt-2'>
                                        <Paragraph type="secondary">
                                            {truncateText(item.news)}
                                        </Paragraph>
                                        <br />
                                        <Button
                                            type="link"
                                            size="small"

                                            onClick={() => {
                                                markNewsAsRead(item.id);
                                                setActiveNews(item);
                                                setModalVisible(true);
                                            }}
                                        >
                                            {__("Read more", "plugin-starter")}
                                        </Button>
                                    </div>
                                </Card>
                            ))}
                        </div>
                        {Math.ceil(newsItems.length / itemsPerPage) > 1 && (
                            <div style={{ borderTop: '1px solid var(--semi-color-border)', flexShrink: 0 }} className='flex justify-between items-center py-4 mt-4'>
                                <Button
                                    size="small"
                                    onClick={() => setNewsCurrentPage(newsCurrentPage - 1)}
                                    disabled={newsCurrentPage === 1}
                                >
                                    {__("Previous", "plugin-starter")}
                                </Button>
                                <Text>
                                    {__("Page", "plugin-starter")} {newsCurrentPage} {__("of", "plugin-starter")} {Math.ceil(newsItems.length / itemsPerPage)}
                                </Text>
                                <Button
                                    size="small"
                                    onClick={() => setNewsCurrentPage(newsCurrentPage + 1)}
                                    disabled={newsCurrentPage === Math.ceil(newsItems.length / itemsPerPage)}
                                >
                                    {__("Next", "plugin-starter")}
                                </Button>
                            </div>
                        )}
                    </div>
                )}
            </SideSheet>

            
            <Modal
                title={activeNews?.title}
                visible={modalVisible}
                onCancel={() => setModalVisible(false)}
                footer={null}
                style={{ maxWidth: 700 }}
            >
                <div style={{ maxHeight: 400, overflowY: 'auto' }} className='pb-6'>
                    {activeNews?.tags?.length > 0 && (
                        <Space style={{ marginBottom: 12 }}>
                            {activeNews.tags.map((tag, index) => (
                                <Tag key={index} size="small" shape="circle" color="amber">
                                    {tag}
                                </Tag>
                            ))}
                        </Space>
                    )}

                    <Paragraph>
                        {activeNews?.news}
                    </Paragraph>
                </div>
            </Modal>
        </>
    )
}
